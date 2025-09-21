<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware {
    public function handle($request, Closure $next, ...$roles) {
        if (!Auth::check()) {
            abort(403, 'Unauthorized.');
        }

        $userRole = Auth::user()->role;

        // Owner bisa akses semua
        if ($userRole === 'Owner') {
            return $next($request);
        }

        // Cek role biasa
        if (!in_array($userRole, $roles)) {
            abort(403, 'Unauthorized.');
        }

        return $next($request);
    }
}
