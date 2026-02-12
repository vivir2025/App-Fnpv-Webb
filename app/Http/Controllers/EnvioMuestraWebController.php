<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ApiService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use App\Mail\EnvioMuestras;
use Illuminate\Support\Facades\Log;

class EnvioMuestraWebController extends Controller
{
    protected $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    public function index()
    {
        // Obtener información del usuario
        $usuario = session('usuario');
        $rol = strtolower($usuario['rol'] ?? '');
        $sedeUsuario = $usuario['idsede'] ?? null;
        
        try {
            // Obtener todas las sedes
            $response = $this->apiService->get('sedes');
            
            if ($response->successful()) {
                $todasLasSedes = $response->json();
            } else {
                Log::error('Error al obtener sedes: ' . $response->status());
                $todasLasSedes = [];
            }
            
            if (!is_array($todasLasSedes)) {
                Log::error('La respuesta de sedes no es un array');
                $todasLasSedes = [];
            }
            
            // Filtrar sedes según el rol
            if (in_array($rol, ['admin', 'administrador'])) {
                // Administrador ve todas las sedes
                $sedes = $todasLasSedes;
            } elseif (in_array($rol, ['jefe', 'coordinador'])) {
                // Jefe solo ve su sede
                $sedes = array_filter($todasLasSedes, function($sede) use ($sedeUsuario) {
                    return ($sede['id'] ?? null) === $sedeUsuario;
                });
            } else {
                // Otros roles no tienen acceso
                $sedes = [];
            }
            
        } catch (\Exception $e) {
            Log::error('Error en index de laboratorio: ' . $e->getMessage());
            $sedes = [];
        }
        
        // Pasar permisos a la vista
        $permisos = [
            'puede_ver_todas_sedes' => in_array($rol, ['admin', 'administrador']),
            'es_jefe' => in_array($rol, ['jefe', 'coordinador']),
            'sede_id' => $sedeUsuario
        ];
        
        return view('laboratorio.index', compact('sedes', 'permisos', 'usuario'));
    }

    public function listarPorSede(Request $request, $sedeId = null)
    {
        if (!$sedeId) {
            return redirect()->route('laboratorio.index');
        }
        
        // Validar permisos del usuario
        $usuario = session('usuario');
        $rol = strtolower($usuario['rol'] ?? '');
        $sedeUsuario = $usuario['idsede'] ?? null;
        
        // Aplicar restricciones según el rol
        if (in_array($rol, ['jefe', 'coordinador'])) {
            // Jefe solo puede ver su sede
            if ($sedeId !== $sedeUsuario) {
                return redirect()->route('laboratorio.index')
                    ->with('error', 'No tiene permisos para ver esta sede');
            }
        } elseif (!in_array($rol, ['admin', 'administrador'])) {
            // Otros roles no tienen acceso
            return redirect()->route('laboratorio.index')
                ->with('error', 'No tiene permisos para acceder');
        }
        
        try {
            // Obtener envíos de la sede
            $responseEnvios = $this->apiService->get("envio-muestras/sede/{$sedeId}");
            $envios = $responseEnvios->successful() ? $responseEnvios->json() : [];
            
            // Obtener información de la sede
            $responseSede = $this->apiService->get("sedes/{$sedeId}");
            $sede = $responseSede->successful() ? $responseSede->json() : null;
            
            return view('laboratorio.lista', compact('envios', 'sede'));
        } catch (\Exception $e) {
            Log::error('Error al listar por sede: ' . $e->getMessage());
            return redirect()->route('laboratorio.index')->with('error', 'Error al cargar los datos');
        }
    }

    public function ver($id)
    {
        try {
            // Obtener el envío
            $response = $this->apiService->get("envio-muestras/{$id}");
            $envio = $response->successful() ? $response->json() : null;

            if (!$envio) {
                return redirect()->route('laboratorio.index')->with('error', 'Envío no encontrado');
            }

            // Obtener responsable de toma
            $envio['responsable_toma_nombre'] = null;
            if (!empty($envio['responsable_toma_id'])) {
                $resUsuario = $this->apiService->get("usuarios/{$envio['responsable_toma_id']}");
                if ($resUsuario->successful()) {
                    $usuario = $resUsuario->json();
                    $envio['responsable_toma_nombre'] = $usuario['nombre'] ?? null;
                }
            }

            // Obtener usuario creador
            $envio['usuario_creador_nombre'] = null;
            if (!empty($envio['usuario_creador_id'])) {
                $resCreador = $this->apiService->get("usuarios/{$envio['usuario_creador_id']}");
                if ($resCreador->successful()) {
                    $usuario = $resCreador->json();
                    $envio['usuario_creador_nombre'] = $usuario['nombre'] ?? null;
                }
            }

            return view('laboratorio.ver', compact('envio'));
        } catch (\Exception $e) {
            Log::error('Error al ver envío: ' . $e->getMessage());
            return redirect()->route('laboratorio.index')->with('error', 'Error al cargar el envío');
        }
    }

    public function crear()
    {
        try {
            $response = $this->apiService->get('sedes');
            $sedes = $response->successful() ? $response->json() : [];
            
            return view('laboratorio.crear', compact('sedes'));
        } catch (\Exception $e) {
            Log::error('Error al cargar formulario de creación: ' . $e->getMessage());
            return view('laboratorio.crear', ['sedes' => []]);
        }
    }

    public function editar($id)
    {
        try {
            $responseEnvio = $this->apiService->get("envio-muestras/{$id}");
            $envio = $responseEnvio->successful() ? $responseEnvio->json() : null;
            
            $responseSedes = $this->apiService->get('sedes');
            $sedes = $responseSedes->successful() ? $responseSedes->json() : [];
            
            if (!$envio) {
                return redirect()->route('laboratorio.index')->with('error', 'Envío no encontrado');
            }
            
            return view('laboratorio.editar', compact('envio', 'sedes'));
        } catch (\Exception $e) {
            Log::error('Error al cargar formulario de edición: ' . $e->getMessage());
            return redirect()->route('laboratorio.index')->with('error', 'Error al cargar el formulario');
        }
    }

    public function guardar(Request $request)
    {
        $request->validate([
            'fecha' => 'required|date',
            'idsede' => 'required',
        ]);

        try {
            $data = $request->all();
            $data['usuario_creador_id'] = session('usuario.id');

            $response = $this->apiService->post('envio-muestras', $data);
            
            if ($response->successful()) {
                $responseData = $response->json();
                return redirect()->route('laboratorio.ver', $responseData['id'])
                    ->with('success', 'Envío de muestras creado correctamente');
            } else {
                return back()->with('error', 'Error al crear el envío')->withInput();
            }
        } catch (\Exception $e) {
            Log::error('Error al guardar envío: ' . $e->getMessage());
            return back()->with('error', 'Error al crear el envío')->withInput();
        }
    }

    public function actualizar(Request $request, $id)
    {
        $request->validate([
            'fecha' => 'required|date',
            'idsede' => 'required',
        ]);

        try {
            $response = $this->apiService->put("envio-muestras/{$id}", $request->all());
            
            if ($response->successful()) {
                return redirect()->route('laboratorio.ver', $id)
                    ->with('success', 'Envío de muestras actualizado correctamente');
            } else {
                return back()->with('error', 'Error al actualizar el envío')->withInput();
            }
        } catch (\Exception $e) {
            Log::error('Error al actualizar envío: ' . $e->getMessage());
            return back()->with('error', 'Error al actualizar el envío')->withInput();
        }
    }

    public function eliminar($id)
    {
        try {
            $response = $this->apiService->delete("envio-muestras/{$id}");
            
            if ($response->successful()) {
                return redirect()->route('laboratorio.index')
                    ->with('success', 'Envío de muestras eliminado correctamente');
            } else {
                return back()->with('error', 'Error al eliminar el envío');
            }
        } catch (\Exception $e) {
            Log::error('Error al eliminar envío: ' . $e->getMessage());
            return back()->with('error', 'Error al eliminar el envío');
        }
    }

    public function buscarPaciente(Request $request)
    {
        try {
            $identificacion = $request->input('identificacion');
            $response = $this->apiService->get("pacientes/buscar/{$identificacion}");
            
            if ($response->successful()) {
                return response()->json($response->json());
            } else {
                return response()->json(['error' => 'Paciente no encontrado'], 404);
            }
        } catch (\Exception $e) {
            Log::error('Error al buscar paciente: ' . $e->getMessage());
            return response()->json(['error' => 'Error en la búsqueda'], 500);
        }
    }

    public function lista(Request $request)
    {
        // Obtener fechas del request o establecer el mes actual por defecto
        $fechaDesde = $request->input('fecha_desde');
        $fechaHasta = $request->input('fecha_hasta');
        
        $titulo_filtro = 'Envíos del mes actual';
        
        // Consulta base
        $query = Envio::with('sede')->orderBy('fecha', 'desc');
        
        // Si no hay fechas en el request, filtrar por mes actual
        if (!$fechaDesde && !$fechaHasta) {
            $inicioMes = Carbon::now()->startOfMonth()->format('Y-m-d');
            $finMes = Carbon::now()->endOfMonth()->format('Y-m-d');
            
            $query->whereBetween('fecha', [$inicioMes, $finMes]);
        } else {
            // Aplicar filtros de fecha si existen
            if ($fechaDesde) {
                $query->where('fecha', '>=', $fechaDesde);
            }
            
            if ($fechaHasta) {
                $query->where('fecha', '<=', $fechaHasta);
            }
            
            $titulo_filtro = 'Resultados de búsqueda';
        }
        
        // Si hay una sede específica (opcional)
        if ($request->has('sede_id')) {
            $query->where('sede_id', $request->sede_id);
            $sede = Sede::find($request->sede_id);
        }
        
        $envios = $query->get();
        
        return view('laboratorio.lista', [
            'envios' => $envios,
            'titulo_filtro' => $titulo_filtro,
            'sede' => $sede ?? null
        ]);
    }

    public function generarPdf($id)
    {
        try {
            // Obtener los datos del envío
            $response = $this->apiService->get("envio-muestras/{$id}");
            $envio = $response->successful() ? $response->json() : null;
            
            if (!$envio) {
                return redirect()->route('laboratorio.index')->with('error', 'Envío no encontrado');
            }
            
            // Descargar y guardar el logo si no existe
            $logoPath = public_path('images/logo.png');
            if (!file_exists($logoPath)) {
                // Crear directorio si no existe
                if (!file_exists(public_path('images'))) {
                    mkdir(public_path('images'), 0755, true);
                }
                
                // Descargar el logo
                $logoUrl = 'https://nacerparavivir.org/wp-content/uploads/2023/12/Logo_Section1home-8.png';
                file_put_contents($logoPath, file_get_contents($logoUrl));
            }
            
            // Generar el PDF
            $pdf = PDF::loadView('laboratorio.detallepdf', compact('envio'));
            $pdf->setPaper('letter', 'landscape');
            $pdf->setOptions([
                'dpi' => 150,
                'defaultFont' => 'sans-serif',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true
            ]);
            
            // Nombre del archivo
            $filename = 'envio_muestras_' . $id . '.pdf';
            
            // Retornar el PDF para descarga
            return $pdf->download($filename);
        } catch (\Exception $e) {
            Log::error('Error al generar PDF: ' . $e->getMessage());
            return redirect()->route('laboratorio.index')->with('error', 'Error al generar el PDF');
        }
    }

    // Método para envío manual desde la interfaz
    public function enviarPorEmail($id, Request $request)
    {
        try {
            // Obtener los datos del envío
            $response = $this->apiService->get("envio-muestras/{$id}");
            $envio = $response->successful() ? $response->json() : null;
            
            if (!$envio) {
                return redirect()->route('laboratorio.index')->with('error', 'Envío no encontrado');
            }

            // Obtener el correo del usuario creador del laboratorio
            $correoUsuarioCreador = null;
            if (!empty($envio['usuario_creador_id'])) {
                $responseUsuario = $this->apiService->get("usuarios/{$envio['usuario_creador_id']}");
                if ($responseUsuario->successful()) {
                    $usuario = $responseUsuario->json();
                    $correoUsuarioCreador = $usuario['correo'] ?? null;
                }
            }
            
            // Obtener el correo del responsable de la toma
            $correoResponsableToma = null;
            if (!empty($envio['responsable_toma_id'])) {
                $responseUsuario = $this->apiService->get("usuarios/{$envio['responsable_toma_id']}");
                if ($responseUsuario->successful()) {
                    $usuario = $responseUsuario->json();
                    $correoResponsableToma = $usuario['correo'] ?? null;
                }
            }
            
            // Generar el PDF
            $pdf = $this->generarPdfParaEmail($envio);
            
            // Obtener la sede para personalizar el asunto
            $nombreSede = $envio['sede']['nombre'] ?? $envio['sede']['nombresede'] ?? 'Sede desconocida';
            
            // Enviar el correo con el PDF adjunto
            $this->enviarEmailConPdf($pdf, $envio, $nombreSede, $correoResponsableToma, $correoUsuarioCreador);
            
            // Actualizar el estado de enviado_por_correo en la base de datos si se solicita
            if ($request->has('actualizar_estado') && $request->actualizar_estado == 1) {
                $this->actualizarEstadoEnviado($id);
            }

            // Enviar notificación push al responsable de toma (quien creó la planilla)
            $usuarioNotificarId = $envio['responsable_toma_id'] ?? null;
            if (!empty($usuarioNotificarId)) {
                $this->enviarNotificacionPush(
                    $usuarioNotificarId,
                    'Envío exitoso',
                    'Se envió la planilla al laboratorio - ' . ($envio['codigo'] ?? 'Sin código')
                );
            } else {
                Log::warning('No se pudo enviar notificación push: no se encontró responsable_toma_id en el envío');
            }
            
            // Si es una solicitud AJAX, devolver respuesta JSON
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Email enviado correctamente']);
            }
            
            return redirect()->route('laboratorio.ver', $id)
                ->with('success', 'El PDF ha sido enviado por correo electrónico correctamente');
        } catch (\Exception $e) {
            Log::error('Error al enviar PDF por email: ' . $e->getMessage());
            
            // Si es una solicitud AJAX, devolver respuesta JSON
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Error al enviar el correo: ' . $e->getMessage()], 500);
            }
            
            return redirect()->route('laboratorio.ver', $id)
                ->with('error', 'Error al enviar el PDF por correo electrónico: ' . $e->getMessage());
        }
    }

    // Método para actualizar el estado de enviado_por_correo
    private function actualizarEstadoEnviado($id)
    {
        try {
            // Preparar los datos para actualizar
            $datos = [
                'enviado_por_correo' => true
            ];
            
            // Actualizar en la API usando el endpoint general de actualización
            $response = $this->apiService->put("envio-muestras/{$id}", $datos);
            
            if (!$response->successful()) {
                return false;
            }
            return true;
        } catch (\Exception $e) {
            Log::error('Error al actualizar estado de envío por correo: ' . $e->getMessage());
            return false;
        }
    }

    // Método para generar el PDF para enviar por email
    private function generarPdfParaEmail($envio)
    {
        // Descargar y guardar el logo si no existe
        $logoPath = public_path('images/logo.png');
        if (!file_exists($logoPath)) {
            // Crear directorio si no existe
            if (!file_exists(public_path('images'))) {
                mkdir(public_path('images'), 0755, true);
            }
            
            // Descargar el logo
            $logoUrl = 'https://nacerparavivir.org/wp-content/uploads/2023/12/Logo_Section1home-8.png';
            file_put_contents($logoPath, file_get_contents($logoUrl));
        }
        
        // Generar el PDF
        $pdf = PDF::loadView('laboratorio.detallepdf', compact('envio'));
        $pdf->setPaper('letter', 'landscape');
        $pdf->setOptions([
            'dpi' => 150,
            'defaultFont' => 'sans-serif',
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true
        ]);
        
        return $pdf;
    }

    /**
     * Enviar notificación push al usuario que creó la planilla
     */
    private function enviarNotificacionPush($userId, $title, $body)
    {
        try {
            $token = session('token');

            if (!$token) {
                Log::warning('No se pudo enviar notificación push: token de sesión no encontrado');
                return;
            }

            $apiBaseUrl = 'http://fnpvi.nacerparavivir.org/api';

            // Enviar la notificación directamente (mismo flujo que la página de notificaciones)
            $response = Http::withToken($token)
                ->timeout(30)
                ->post($apiBaseUrl . '/notifications/send-to-user', [
                    'user_id' => (string) $userId,
                    'title' => $title,
                    'body' => $body,
                    'data' => [
                        'sent_by' => session('usuario.nombre', 'Sistema'),
                        'sent_at' => now()->toIso8601String()
                    ]
                ]);
        } catch (\Exception $e) {
            Log::error('Error al enviar notificación push: ' . $e->getMessage());
        }
    }

    // Método para enviar el email con el PDF adjunto
    private function enviarEmailConPdf($pdf, $envio, $nombreSede, $correoResponsableToma = null, $correoUsuarioCreador = null)
    {
        // Lista de destinatarios base
        $destinatarios = config('laboratorio.emails_destinatarios', [
            'atencionalusuario.caucalab@gmail.com',
            // "facturacion.caucalab@gmail.com",
            "yeiserna14@gmail.com",
            'julianvillalba91@hotmail.com',
        ]);
        
        // Agregar el correo del responsable de toma si existe y no está ya en la lista
        if ($correoResponsableToma && !in_array($correoResponsableToma, $destinatarios)) {
            $destinatarios[] = $correoResponsableToma;
        }
        
        // Agregar el correo del usuario creador si existe y no está ya en la lista
        if ($correoUsuarioCreador && !in_array($correoUsuarioCreador, $destinatarios)) {
            $destinatarios[] = $correoUsuarioCreador;
        }
        
        // Nombre del archivo
        $filename = 'envio_muestras_' . $envio['id'] . '.pdf';
        
        // Enviar el correo
        Mail::send('emails.envio_muestras', ['envio' => $envio], function ($message) use ($pdf, $filename, $destinatarios, $nombreSede, $envio) {
            $message->subject('Envío de Muestras - ' . $nombreSede . ' - ' . $envio['codigo']);
            
            // Agregar el primer destinatario como principal
            $message->to($destinatarios[0]);
            
            // Si hay más destinatarios, agregarlos como CC
            for ($i = 1; $i < count($destinatarios); $i++) {
                $message->cc($destinatarios[$i]);
            }
            
            $message->attachData($pdf->output(), $filename, [
                'mime' => 'application/pdf',
            ]);
        });
    }
}