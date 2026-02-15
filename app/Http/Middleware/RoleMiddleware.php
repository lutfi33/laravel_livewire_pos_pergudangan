<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // 1. Cek apakah sudah login
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // 2. Cek apakah role user ada di dalam list roles yang diizinkan
        // Auth::user()->role adalah string dari DB (misal: 'user')
        // $roles adalah array dari middleware (misal: ['user'])
        if (!in_array(Auth::user()->role, $roles)) {
            abort(403, 'Unauthorized action. Your role is: ' . Auth::user()->role);
        }

        return $next($request);
    }
}
