<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = auth()->guard('admin')->user();

        if (!$user || !$user->hasRole($role)) {
            abort(403, 'Bu işlem için yetkiniz yok.');
        }

        return $next($request);
    }
}
