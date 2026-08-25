<?php

namespace App\Http\Controllers;

use App\Models\LoginLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if ($user && !$user->status) {
            return back()
                ->withErrors(['email' => 'Tu usuario ha sido deshabilitado. Contacta al administrador.'])
                ->onlyInput('email');
        }

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            $failedUser = User::where('email', $credentials['email'])->first();
            LoginLog::create([
                'user_id' => $failedUser?->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'success' => false,
                'type' => 'login',
            ]);
            return back()
                ->withErrors(['email' => 'Las credenciales proporcionadas no son válidas.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        LoginLog::create([
            'user_id' => auth()->id(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'success' => true,
            'type' => 'login',
        ]);

        return redirect()->intended('/');
    }

    public function destroy(Request $request): RedirectResponse
    {
        LoginLog::create([
            'user_id' => auth()->id(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'success' => true,
            'type' => 'logout',
        ]);

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
