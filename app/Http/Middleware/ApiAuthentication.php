<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ApiAuthentication
{
    public function handle(Request $request, Closure $next)
    {
        if (!session('token') || !session('usuario')) {
            return redirect()->route('login');
        }

        return $next($request);
    }
}
