<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Helpers\RoleHelper;
use Illuminate\Routing\Controller as BaseController;

class NotificationWebController extends BaseController
{
    private $apiBaseUrl;

    public function __construct()
    {
        $this->apiBaseUrl = 'http://fnpvi.nacerparavivir.org/api';
        
        $this->middleware(function ($request, $next) {
            $rol = session('usuario.rol', '');
            
            if (!in_array($rol, ['admin', 'administrador'])) {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'error' => 'No tienes permisos para acceder a esta sección',
                        'redirect' => route('dashboard')
                    ], 403);
                }
                
                return redirect()->route('dashboard')
                    ->with('error', 'Solo los administradores pueden acceder al sistema de notificaciones');
            }
            
            return $next($request);
        });
    }

    /**
     * Mostrar la vista principal de notificaciones
     */
    public function index()
    {
        try {
            return view('notifications.index', [
                'usuario' => session('usuario', []),
                'pageTitle' => 'Sistema de Notificaciones Push'
            ]);
        } catch (\Exception $e) {
            return redirect()->route('dashboard')
                ->with('error', 'Error al cargar la página de notificaciones');
        }
    }

    /**
     * Obtener lista de usuarios con tokens registrados
     */
    public function getUsers(Request $request)
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
                ->get($this->apiBaseUrl . '/notifications/users-with-tokens');

            if ($response->successful()) {
                $data = $response->json();
                $usuarios = $data['usuarios'] ?? [];

                return response()->json([
                    'success' => true,
                    'data' => $usuarios
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener usuarios con dispositivos registrados'
            ], $response->status());

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de conexión con la API'
            ], 500);
        }
    }

    /**
     * Enviar notificación a un usuario específico
     */
    public function sendToUser(Request $request)
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|string',
                'title' => 'required|string|max:255',
                'body' => 'required|string',
            ]);

            $token = session('token');
            
            if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token de autenticación no encontrado'
                ], 401);
            }

            $response = Http::withToken($token)
                ->timeout(30)
                ->post($this->apiBaseUrl . '/notifications/send-to-user', [
                    'user_id' => $validated['user_id'],
                    'title' => $validated['title'],
                    'body' => $validated['body'],
                    'data' => [
                        'sent_by' => session('usuario.nombre', 'Admin'),
                        'sent_at' => now()->toIso8601String()
                    ]
                ]);

            if ($response->successful()) {
                return response()->json($response->json());
            }

            return response()->json([
                'success' => false,
                'message' => 'Error al enviar notificación'
            ], $response->status());

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Datos de validación incorrectos',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al enviar notificación'
            ], 500);
        }
    }

    /**
     * Enviar notificación masiva a todos los usuarios
     */
    public function sendToAll(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'body' => 'required|string',
            ]);

            $token = session('token');
            
            if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token de autenticación no encontrado'
                ], 401);
            }

            $response = Http::withToken($token)
                ->timeout(60)
                ->post($this->apiBaseUrl . '/notifications/send-to-all', [
                    'title' => $validated['title'],
                    'body' => $validated['body'],
                    'data' => [
                        'sent_by' => session('usuario.nombre', 'Admin'),
                        'sent_at' => now()->toIso8601String(),
                        'type' => 'broadcast'
                    ]
                ]);

            if ($response->successful()) {
                return response()->json($response->json());
            }

            return response()->json([
                'success' => false,
                'message' => 'Error al enviar notificación masiva'
            ], $response->status());

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Datos de validación incorrectos',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al enviar notificación masiva'
            ], 500);
        }
    }

    /**
     * Obtener estadísticas de dispositivos registrados
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
                ->get($this->apiBaseUrl . '/notifications/stats');

            if ($response->successful()) {
                return response()->json($response->json());
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'total_devices' => 0,
                    'active_users' => 0,
                    'notifications_sent_today' => 0
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => true,
                'data' => [
                    'total_devices' => 0,
                    'active_users' => 0,
                    'notifications_sent_today' => 0
                ]
            ]);
        }
    }

    /**
     * Listar usuarios que tienen tokens registrados
     */
    public function getUsersWithTokens(Request $request)
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
                ->get($this->apiBaseUrl . '/notifications/users-with-tokens');

            if ($response->successful()) {
                return response()->json($response->json());
            }

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener usuarios con tokens'
            ], $response->status());

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener usuarios con tokens'
            ], 500);
        }
    }

    /**
     * Obtener estadísticas detalladas de tokens
     */
    public function getTokenStats()
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
                ->get($this->apiBaseUrl . '/notifications/stats');

            if ($response->successful()) {
                return response()->json($response->json());
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'total_dispositivos' => 0,
                    'dispositivos_activos' => 0,
                    'usuarios_con_tokens' => 0,
                    'tokens_android' => 0,
                    'tokens_ios' => 0,
                    'tokens_web' => 0
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => true,
                'data' => [
                    'total_dispositivos' => 0,
                    'dispositivos_activos' => 0,
                    'usuarios_con_tokens' => 0,
                    'tokens_android' => 0,
                    'tokens_ios' => 0,
                    'tokens_web' => 0
                ]
            ]);
        }
    }

    /**
     * Obtener tokens de un usuario específico
     */
    public function getUserTokens($userId)
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
                ->get($this->apiBaseUrl . "/notifications/user/{$userId}/tokens");

            if ($response->successful()) {
                return response()->json($response->json());
            }

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener tokens del usuario'
            ], $response->status());

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener tokens del usuario'
            ], 500);
        }
    }
}
