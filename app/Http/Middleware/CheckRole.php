<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class CheckRole {
    public function handle($request, Closure $next, ...$roles) {
        \Log::info('Role user: ' . Auth::user()->role);
        \Log::info('Roles allowed: ' . implode(', ', $roles)); 
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $userRole = Auth::user()->role;

        if (!in_array($userRole, $roles)) {
            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
}
