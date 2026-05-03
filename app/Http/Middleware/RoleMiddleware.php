<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // cek apakah role user ada di daftar role yg diizinkan
        if (!in_array($user->role, $roles)) {
            abort(403); // forbidden
        }

        return $next($request);
    }
}