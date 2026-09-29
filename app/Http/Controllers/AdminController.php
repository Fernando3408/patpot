<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::with('roles')
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%{$s}%")->orWhere('email', 'like', "%{$s}%"))
            ->orderBy('name')
            ->get();

        return view('admin.index', compact('users'));
    }

    public function edit(User $user): View
    {
        $roles = Role::all();

        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user): JsonResponse|RedirectResponse
    {
        try {
            // Filter empty strings from roles before validation (hidden input sends "")
            $request->merge([
                'roles' => array_values(array_filter(
                    $request->input('roles', []),
                    fn ($value): bool => filled($value)
                )),
            ]);

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
                'roles' => ['nullable', 'array'],
                'roles.*' => ['exists:roles,id'],
            ]);

            $user->update($validated);

            if ($request->filled('password')) {
                $request->validate(['password' => 'required|string|min:8']);
                $user->update(['password' => Hash::make($request->password)]);
            }

            $user->roles()->sync($validated['roles'] ?? []);

            AuditService::log('ACTUALIZACIÓN DE USUARIO', "Actualizó usuario: {$user->name}", $user);

            if ($request->ajax()) {
                return response()->json(['success' => true]);
            }

            return redirect()->route('admin.index')->with('success', 'Usuario actualizado.');
        } catch (ValidationException $e) {
            if ($request->ajax()) {
                return response()->json(['errors' => $e->errors()], 422);
            }
            throw $e;
        }
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => 'No puedes eliminar tu propio usuario.']);
        }

        $user->delete();
        AuditService::log('ELIMINACIÓN DE USUARIO', "Eliminó usuario: {$user->name}", $user);

        return redirect()->route('admin.index')->with('success', 'Usuario eliminado.');
    }

    public function toggleStatus(Request $request, User $user): JsonResponse|RedirectResponse
    {
        if ($user->id === auth()->id()) {
            if ($request->ajax()) {
                return response()->json(['errors' => ['error' => ['No puedes desactivar tu propio usuario.']]], 422);
            }
            return back()->withErrors(['error' => 'No puedes desactivar tu propio usuario.']);
        }

        $user->update(['status' => ! $user->status]);
        $estado = $user->status ? 'activado' : 'desactivado';
        AuditService::log('CAMBIO DE ESTADO USUARIO', "Usuario {$user->name} {$estado}", $user);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'status' => $user->status]);
        }

        return redirect()->route('admin.index')->with('success', "Usuario {$estado}.");
    }
}
