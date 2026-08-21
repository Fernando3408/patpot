<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CanManageMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (! $request->user() || ! $request->user()->canManage()) {
            abort(403, 'No tienes permiso.');
        }

        return $next($request);
    }
}
