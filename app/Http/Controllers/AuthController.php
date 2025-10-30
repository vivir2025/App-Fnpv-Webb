<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Services\ApiService;

class AuthController extends Controller
{
    protected $apiService;

    public function __construct(ApiService $apiService = null)
    {
        $this->apiService = $apiService ?? new ApiService();
    }

    public function login(Request $request)
    {
        try {
            $request->validate([
                'usuario' => 'required',
                'contrasena' => 'required',
            ]);

            // Hacer la petición a la API externa
            $response = Http::post('http://fnpvi.nacerparavivir.org/api/login', [
                'usuario' => $request->usuario,
                'contrasena' => $request->contrasena,
            ]);

            if (!$response->successful()) {
                return back()->withErrors([
                    'usuario' => 'Las credenciales proporcionadas son incorrectas.',
                ])->withInput($request->except('contrasena'));
            }

            $data = $response->json();
            
            // ✅ GUARDAR INFORMACIÓN COMPLETA EN LA SESIÓN
            session(['token' => $data['token']]);
            session(['usuario' => $data['usuario']]);
            session(['sede' => $data['sede'] ?? null]);

            // ✅ OBTENER EL ROL DEL USUARIO
            $rol = strtolower($data['usuario']['rol'] ?? '');

            // ✅ LOG PARA VERIFICAR EL ROL
            Log::info('Usuario autenticado', [
                'usuario' => $data['usuario']['nombre'] ?? 'Sin nombre',
                'rol' => $rol,
                'sede' => $data['sede']['nombre'] ?? 'Sin sede'
            ]);

            // Autenticar al usuario en Laravel
            Auth::loginUsingId($data['usuario']['id'] ?? 1);
            
            // ✅ REDIRIGIR SEGÚN EL ROL
            if (in_array($rol, ['aux', 'auxiliar'])) {
                // Auxiliares van directo a visitas
                Log::info('Redirigiendo auxiliar a visitas');
                return redirect()->route('visitas.buscar')
                    ->with('success', '¡Bienvenido! ' . ($data['usuario']['nombre'] ?? 'Usuario'));
            } else {
                // Otros roles (admin, administrador, etc.) van al dashboard
                Log::info('Redirigiendo ' . $rol . ' a dashboard');
                return redirect()->route('dashboard')
                    ->with('success', '¡Bienvenido! ' . ($data['usuario']['nombre'] ?? 'Usuario'));
            }
            
        } catch (\Exception $e) {
            Log::error('Error en login: ' . $e->getMessage());
            return back()->withErrors([
                'error' => 'Ha ocurrido un error al intentar iniciar sesión. Por favor, intente nuevamente.',
            ])->withInput($request->except('contrasena'));
        }
    }

    public function logout(Request $request)
    {
        try {
            // Si tienes un endpoint de logout en la API, puedes llamarlo aquí
            if (session('token')) {
                Http::withToken(session('token'))->post('http://fnpvi.nacerparavivir.org/api/logout');
            }
            
            session()->flush();
            Auth::logout();
            
            return redirect()->route('login')
                ->with('success', 'Sesión cerrada correctamente.');
        } catch (\Exception $e) {
            Log::error('Error en logout: ' . $e->getMessage());
            session()->flush();
            Auth::logout();
            return redirect()->route('login');
        }
    }
}
