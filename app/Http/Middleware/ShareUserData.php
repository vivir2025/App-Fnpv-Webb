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

        return $next($request);
    }
}