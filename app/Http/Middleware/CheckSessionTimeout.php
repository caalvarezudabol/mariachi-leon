<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckSessionTimeout
{
    /**
     * Cierra la sesión si el usuario estuvo inactivo más de SESSION_IDLE_MINUTES minutos.
     * También se ejecuta en las peticiones de Livewire (ver AppServiceProvider), así cada
     * clic, búsqueda o guardado cuenta como actividad.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $minutos = (int) config('session.idle_timeout', 120);
            $lastActivity = session('last_activity_timestamp');

            if ($minutos > 0 && $lastActivity && (time() - $lastActivity > $minutos * 60)) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->with('error', "Su sesión se cerró automáticamente por {$minutos} minutos de inactividad.");
            }

            session(['last_activity_timestamp' => time()]);
        }

        return $next($request);
    }
}