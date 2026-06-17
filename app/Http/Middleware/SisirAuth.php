<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Protects SISIR customer-facing routes with session-based auth.
 * Redirects unauthenticated users to the login page.
 */
class SisirAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return redirect()->route('sisir.login')
                ->with('info', 'Silakan login terlebih dahulu.');
        }

        return $next($request);
    }
}
