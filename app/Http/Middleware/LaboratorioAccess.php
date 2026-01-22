<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LaboratorioAccess
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $usuario = session('usuario');
        
        if (!$usuario) {
            return redirect()->route('login')
                ->withErrors(['error' => 'Debe iniciar sesión']);
        }
        
        $rol = strtolower($usuario['rol'] ?? '');
        
        // Solo administradores y jefes pueden acceder al laboratorio
        if (!in_array($rol, ['admin', 'administrador', 'jefe', 'coordinador'])) {
            Log::warning('Acceso denegado al laboratorio', [
                'usuario' => $usuario['nombre'] ?? 'Desconocido',
                'rol' => $rol
            ]);
            
            return redirect()->route('visitas.buscar')
                ->withErrors(['error' => 'No tiene permisos para acceder al módulo de laboratorio']);
        }
        
        return $next($request);
    }
}
