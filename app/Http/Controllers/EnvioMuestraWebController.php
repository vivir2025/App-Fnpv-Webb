<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ApiService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
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
        try {
            // Obtener todas las sedes para el selector
            $response = $this->apiService->get('sedes');
            
            // Verificar si la respuesta fue exitosa y extraer los datos
            if ($response->successful()) {
                $sedes = $response->json();
            } else {
                Log::error('Error al obtener sedes: ' . $response->status());
                $sedes = [];
            }
            
            // Verificar que sedes sea un array
            if (!is_array($sedes)) {
                Log::error('La respuesta de sedes no es un array');
                $sedes = [];
            }
            
        } catch (\Exception $e) {
            Log::error('Error en index de laboratorio: ' . $e->getMessage());
            $sedes = [];
        }
        
        return view('laboratorio.index', compact('sedes'));
    }

    public function listarPorSede(Request $request, $sedeId = null)
    {
        if ($sedeId) {
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
        } else {
            return redirect()->route('laboratorio.index');
        }
    }

    // public function ver($id)
    // {
    //     try {
    //         $response = $this->apiService->get("envio-muestras/{$id}");
    //         $envio = $response->successful() ? $response->json() : null;
            
    //         if (!$envio) {
    //             return redirect()->route('laboratorio.index')->with('error', 'Envío no encontrado');
    //         }
            
    //         return view('laboratorio.ver', compact('envio'));
    //     } catch (\Exception $e) {
    //         Log::error('Error al ver envío: ' . $e->getMessage());
    //         return redirect()->route('laboratorio.index')->with('error', 'Error al cargar el envío');
    //     }
    // }

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
            $data['usuario_creador_id'] = session('user_id');

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

    public function lista()
    {
        try {
            $response = $this->apiService->get("envio-muestras");
            $envios = $response->successful() ? $response->json() : [];
            
            return view('laboratorio.lista', compact('envios'));
        } catch (\Exception $e) {
            Log::error('Error al listar envíos: ' . $e->getMessage());
            return view('laboratorio.lista', ['envios' => []]);
        }
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
public function enviarPorEmail($id)
{
    try {
        // Obtener los datos del envío
        $response = $this->apiService->get("envio-muestras/{$id}");
        $envio = $response->successful() ? $response->json() : null;
        
        if (!$envio) {
            return redirect()->route('laboratorio.index')->with('error', 'Envío no encontrado');
        }
        
        // Generar el PDF
        $pdf = $this->generarPdfParaEmail($envio);
        
        // Obtener la sede para personalizar el asunto
        $nombreSede = $envio['sede']['nombre'] ?? $envio['sede']['nombresede'] ?? 'Sede desconocida';
        
        // Enviar el correo con el PDF adjunto
        $this->enviarEmailConPdf($pdf, $envio, $nombreSede);
        
        return redirect()->route('laboratorio.ver', $id)
            ->with('success', 'El PDF ha sido enviado por correo electrónico correctamente');
    } catch (\Exception $e) {
        Log::error('Error al enviar PDF por email: ' . $e->getMessage());
        return redirect()->route('laboratorio.ver', $id)
            ->with('error', 'Error al enviar el PDF por correo electrónico: ' . $e->getMessage());
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

// Método para enviar el email con el PDF adjunto
private function enviarEmailConPdf($pdf, $envio, $nombreSede)
{
    // Lista de destinatarios
    $destinatarios = config('laboratorio.emails_destinatarios', [
        'yeiserna14@gmail.com',
        'jamo.mosquera@gmail.com'
    ]);
    
    // Nombre del archivo
    $filename = 'envio_muestras_' . $envio['id'] . '.pdf';
    
    // Enviar el correo
    Mail::send('emails.envio_muestras', ['envio' => $envio], function ($message) use ($pdf, $filename, $destinatarios, $nombreSede, $envio) {
        $message->subject('Envío de Muestras - ' . $nombreSede . ' - ' . $envio['codigo']);
        
        // Agregar todos los destinatarios
        $message->to($destinatarios[0]); // Primer destinatario como principal
        
        // Si hay más de un destinatario, agregarlos como CC
        for ($i = 1; $i < count($destinatarios); $i++) {
            $message->cc($destinatarios[$i]);
        }
        
        $message->attachData($pdf->output(), $filename, [
            'mime' => 'application/pdf',
        ]);
    });
}



}
