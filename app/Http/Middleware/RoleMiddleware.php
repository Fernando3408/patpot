<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        $user = $request->user();
        if (! $user) {
            abort(403, 'No tienes permiso para este módulo.');
        }
        if ($user->roles()->exists() && ! $user->isAdmin() && ! $user->roles()->whereIn('name', $roles)->exists()) {
            abort(403, 'No tienes permiso para este módulo.');
        }
        return $next($request);
    }
}
