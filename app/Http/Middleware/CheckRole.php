<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles  Los roles permitidos para acceder a la ruta
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!$request->user()) {
            return redirect()->route('login')
                ->with('error', 'Debe iniciar sesión para acceder a esta página.');
        }

        if (!$request->user()->activo) {
            auth()->logout();
            return redirect()->route('login')
                ->with('error', 'Su cuenta ha sido desactivada. Contacte al administrador.');
        }

        // Verificar si el usuario tiene alguno de los roles permitidos
        if (!empty($roles) && !$request->user()->hasAnyRole($roles)) {
            abort(403, 'No tiene permisos para acceder a esta sección.');
        }

        // Actualizar último acceso
        $request->user()->update(['ultimo_acceso' => now()]);

        return $next($request);
    }
}
