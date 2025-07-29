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
            
            // Guardar información en la sesión
            session(['token' => $data['token']]);
            session(['usuario' => $data['usuario']]);
            session(['sede' => $data['sede'] ?? null]);

            // Autenticar al usuario en Laravel
            // Esto permite usar Auth::user() en toda la aplicación
            Auth::loginUsingId($data['usuario']['id'] ?? 1); // Usa el ID del usuario si está disponible
            
            // Si no tienes un ID, puedes crear un objeto de usuario y autenticarlo
            // $user = new \stdClass();
            // $user->nombre = $data['usuario']['nombre'] ?? $data['usuario'];
            // $user->id = 1; // Un ID ficticio
            // Auth::login($user);
            return redirect()->route('dashboard');
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
            Auth::logout(); // Cerrar sesión en Laravel
            
            return redirect()->route('login');
        } catch (\Exception $e) {
            Log::error('Error en logout: ' . $e->getMessage());
            session()->flush();
            Auth::logout(); // Cerrar sesión en Laravel
            return redirect()->route('login');
        }
    }
}
