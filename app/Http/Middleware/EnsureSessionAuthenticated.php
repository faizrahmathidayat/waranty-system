<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureSessionAuthenticated
{
    public function handle(Request $request, Closure $next)
    {
        // Login and the public digital-warranty page must remain reachable.
        if ($request->is('login') || $request->is('login/*') || $request->routeIs('warranty.digital', 'warranty.check')) {
            return $next($request);
        }

        if (!Auth::check()) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['message' => 'Sesi login telah berakhir.'], 401);
            }

            return redirect('/login')->with('warning', 'Sesi login telah berakhir. Silakan login kembali.');
        }

        return $next($request);
    }
}
