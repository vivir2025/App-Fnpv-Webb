<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class ShareUserData
{
    public function handle(Request $request, Closure $next)
    {
        // Compartir los datos del usuario con todas las vistas
        if (session('usuario')) {
            View::share('currentUser', session('usuario'));
        }

        // Ejecutar el siguiente middleware y obtener la respuesta
        $response = $next($request);

        // Verificar si es una descarga de Excel de brigadas
        if ($request->is('brigadas/export/excel') && 
            $request->cookie('brigada_download_started') && 
            $response->headers->get('content-type') === 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') {
            
            $response->cookie('brigada_download_complete', '1', 1);
        }
                // Verificar si es una descarga de Excel de encuestas
        if ($request->is('encuestas/export/excel') && 
            $request->cookie('encuesta_download_started') && 
            $response->headers->get('content-type') === 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') {
            
            $response->cookie('encuesta_download_complete', '1', 1);
        }

        return $response;
    }
}
