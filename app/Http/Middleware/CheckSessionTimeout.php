<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckSessionTimeout
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $lastActivity = session('last_activity_timestamp');
            $timeoutSeconds = 5 * 60; // 5 minutos = 300 segundos

            if ($lastActivity && (time() - $lastActivity > $timeoutSeconds)) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->with('error', 'Su sesión se cerró automáticamente por 5 minutos de inactividad.');
            }

            session(['last_activity_timestamp' => time()]);
        }

        return $next($request);
    }
}
