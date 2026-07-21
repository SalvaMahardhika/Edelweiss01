<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserStatus
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();

            // Jika akun dinonaktifkan (status == 0) atau tidak aktif
            if (! $user->status) {
                Auth::logout();

                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->withErrors([
                    'email' => 'Akun Anda telah dinonaktifkan oleh Administrator.',
                ]);
            }
        }

        return $next($request);
    }
}
