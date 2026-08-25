<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('admin.users.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique(User::class)],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['nullable', 'string', Rule::in(['administrativo', 'produccion', 'ventas'])],
        ]);

        $user = User::query()->create($validated);

        $roleName = $request->input('role', 'administrativo');
        $role = \App\Models\Role::where('name', $roleName)->first();
        if ($role) {
            $user->roles()->attach($role);
        }

        AuditService::log('CREACIÓN DE USUARIO', "Creó usuario: {$user->name} ({$user->email})", $user);

        return redirect()->route('admin.index')->with('success', 'Usuario creado correctamente.');
    }
}
