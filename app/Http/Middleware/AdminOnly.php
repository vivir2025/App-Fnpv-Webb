<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminOnly
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $usuario = session('usuario', []);
        $rol = is_array($usuario) ? ($usuario['rol'] ?? '') : '';
        
        // Solo permitir acceso a administradores
        if (!in_array($rol, ['admin', 'administrador'])) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => 'No tienes permisos para acceder a esta sección'
                ], 403);
            }
            
            return redirect()->route('dashboard')
                ->with('error', 'No tienes permisos para acceder a los logs del sistema');
        }

        return $next($request);
    }
}
