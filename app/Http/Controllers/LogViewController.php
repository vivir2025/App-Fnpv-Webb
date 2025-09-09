<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Helpers\RoleHelper;
use Illuminate\Routing\Controller as BaseController;

class LogViewController extends BaseController
{
    private $apiBaseUrl;

    public function __construct()
    {
        $this->apiBaseUrl = 'http://fnpvi.nacerparavivir.org/api';
        
        // ✅ VERIFICAR PERMISOS EN EL CONSTRUCTOR (DOBLE SEGURIDAD)
        $this->middleware(function ($request, $next) {
            if (!RoleHelper::canAccessLogs()) {
                Log::warning('Intento de acceso no autorizado a logs', [
                    'usuario' => session('usuario.nombre', 'Desconocido'),
                    'rol' => session('usuario.rol', 'Sin rol'),
                    'ip' => $request->ip(),
                    'ruta' => $request->path()
                ]);

                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'error' => 'No tienes permisos para acceder a esta sección',
                        'redirect' => route('dashboard')
                    ], 403);
                }
                
                return redirect()->route('dashboard')
                    ->with('error', 'Solo los administradores pueden acceder a los logs del sistema');
            }
            
            return $next($request);
        });
    }

    /**
     * Mostrar la vista principal de logs
     */
    public function index()
    {
        try {
            // ✅ LOG DE ACCESO AUTORIZADO
            Log::info('Acceso autorizado a logs del sistema', [
                'usuario' => session('usuario.nombre', 'Desconocido'),
                'rol' => session('usuario.rol', 'Sin rol'),
                'ip' => request()->ip()
            ]);

            return view('logs.index', [
                'usuario' => session('usuario', []),
                'pageTitle' => 'Logs del Sistema'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al cargar vista de logs', [
                'error' => $e->getMessage(),
                'usuario' => session('usuario.nombre', 'Desconocido')
            ]);

            return redirect()->route('dashboard')
                ->with('error', 'Error al cargar la página de logs');
        }
    }

    /**
     * Obtener estadísticas de logs vía AJAX
     */
    public function getStats()
    {
        try {
            $token = session('token');
            
            if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token de autenticación no encontrado'
                ], 401);
            }

            $response = Http::withToken($token)
                ->timeout(30)
                ->get($this->apiBaseUrl . '/logs/stats');

            if ($response->successful()) {
                $data = $response->json();
                
                // ✅ LOG DE CONSULTA EXITOSA
                Log::info('Estadísticas de logs obtenidas', [
                    'usuario' => session('usuario.nombre', 'Desconocido'),
                    'total_logs' => $data['data']['total'] ?? 0
                ]);

                return response()->json($data);
            }

            Log::warning('Error al obtener estadísticas de logs desde API', [
                'status' => $response->status(),
                'usuario' => session('usuario.nombre', 'Desconocido')
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas desde la API'
            ], $response->status());

        } catch (\Exception $e) {
            Log::error('Excepción al obtener estadísticas de logs', [
                'error' => $e->getMessage(),
                'usuario' => session('usuario.nombre', 'Desconocido')
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error de conexión con la API'
            ], 500);
        }
    }

    /**
     * Obtener datos de logs con filtros y paginación vía AJAX
     */
    public function getData(Request $request)
    {
        try {
            $token = session('token');
            
            if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token de autenticación no encontrado'
                ], 401);
            }

            $queryParams = array_filter([
                'page' => $request->get('page', 1),
                'per_page' => $request->get('per_page', 20),
                'type' => $request->get('type'),
                'status' => $request->get('status'),
                'operation' => $request->get('operation'),
                'search' => $request->get('search'),
                'date_from' => $request->get('date_from'),
                'date_to' => $request->get('date_to')
            ]);

            $response = Http::withToken($token)
                ->timeout(30)
                ->get($this->apiBaseUrl . '/logs', $queryParams);

            if ($response->successful()) {
                $data = $response->json();
                
                // ✅ LOG DE CONSULTA CON FILTROS
                Log::info('Logs consultados con filtros', [
                    'usuario' => session('usuario.nombre', 'Desconocido'),
                    'filtros' => $queryParams,
                    'resultados' => $data['data']['total'] ?? 0
                ]);

                return response()->json($data);
            }

            Log::warning('Error al obtener logs desde API', [
                'status' => $response->status(),
                'filtros' => $queryParams,
                'usuario' => session('usuario.nombre', 'Desconocido')
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener logs desde la API'
            ], $response->status());

        } catch (\Exception $e) {
            Log::error('Excepción al obtener datos de logs', [
                'error' => $e->getMessage(),
                'filtros' => $request->all(),
                'usuario' => session('usuario.nombre', 'Desconocido')
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error de conexión con la API'
            ], 500);
        }
    }

    /**
     * Obtener un log específico vía AJAX
     */
    public function show($id)
    {
        try {
            $token = session('token');
            
            if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token de autenticación no encontrado'
                ], 401);
            }

            $response = Http::withToken($token)
                ->timeout(30)
                ->get($this->apiBaseUrl . '/logs/' . $id);

            if ($response->successful()) {
                $data = $response->json();
                
                // ✅ LOG DE CONSULTA ESPECÍFICA
                Log::info('Log específico consultado', [
                    'log_id' => $id,
                    'usuario' => session('usuario.nombre', 'Desconocido')
                ]);

                return response()->json($data);
            }

            Log::warning('Log específico no encontrado', [
                'log_id' => $id,
                'status' => $response->status(),
                'usuario' => session('usuario.nombre', 'Desconocido')
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Log no encontrado'
            ], $response->status());

        } catch (\Exception $e) {
            Log::error('Excepción al obtener log específico', [
                'log_id' => $id,
                'error' => $e->getMessage(),
                'usuario' => session('usuario.nombre', 'Desconocido')
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error de conexión con la API'
            ], 500);
        }
    }
}