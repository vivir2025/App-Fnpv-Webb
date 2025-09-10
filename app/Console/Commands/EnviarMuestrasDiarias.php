<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ApiService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class EnviarMuestrasDiarias extends Command
{
    protected $signature = 'muestras:enviar-diario';
    protected $description = 'Envía por correo electrónico los PDFs de las muestras con fecha de salida del día actual';

    protected $apiService;

    public function __construct(ApiService $apiService)
    {
        parent::__construct();
        $this->apiService = $apiService;
    }

    public function handle()
    {
        $this->info('Iniciando envío automático de muestras...');
        
        try {
            // Obtener la fecha actual
            $fechaActual = Carbon::now()->format('Y-m-d');
            
            // Obtener todos los envíos con fecha de salida de hoy
            $response = $this->apiService->get("envio-muestras/fecha-salida/{$fechaActual}");
            
            if (!$response->successful()) {
                $this->error('Error al obtener los envíos: ' . $response->status());
                return 1;
            }
            
            $envios = $response->json();
            
            if (empty($envios)) {
                $this->info('No hay envíos con fecha de salida para hoy.');
                return 0;
            }
            
            $this->info('Se encontraron ' . count($envios) . ' envíos para procesar.');
            
            // Procesar cada envío
            foreach ($envios as $envio) {
                $this->procesarEnvio($envio);
            }
            
            $this->info('Proceso completado con éxito.');
            return 0;
            
        } catch (\Exception $e) {
            $this->error('Error en el proceso de envío automático: ' . $e->getMessage());
            Log::error('Error en el comando de envío automático: ' . $e->getMessage());
            return 1;
        }
    }
    
    protected function procesarEnvio($envio)
    {
        try {
            $this->info('Procesando envío ID: ' . $envio['id']);
            
            // Obtener detalles completos del envío
            $response = $this->apiService->get("envio-muestras/{$envio['id']}");
            
            if (!$response->successful()) {
                $this->error('Error al obtener detalles del envío: ' . $response->status());
                return;
            }
            
            $envioCompleto = $response->json();
            
            // Generar el PDF
            $pdf = $this->generarPdf($envioCompleto);
            
            // Obtener la sede para personalizar el asunto
            $nombreSede = $envioCompleto['sede']['nombre'] ?? $envioCompleto['sede']['nombresede'] ?? 'Sede desconocida';
            
            // Obtener el correo del responsable de la toma
            $correoResponsableToma = null;
            if (!empty($envioCompleto['responsable_toma_id'])) {
                $responseUsuario = $this->apiService->get("usuarios/{$envioCompleto['responsable_toma_id']}");
                if ($responseUsuario->successful()) {
                    $usuario = $responseUsuario->json();
                    $correoResponsableToma = $usuario['correo'] ?? null;
                    $this->info('Correo del responsable de toma encontrado: ' . $correoResponsableToma);
                }
            }
            
            // Obtener el correo del usuario creador
            $correoUsuarioCreador = null;
            if (!empty($envioCompleto['usuario_creador_id'])) {
                $responseUsuario = $this->apiService->get("usuarios/{$envioCompleto['usuario_creador_id']}");
                if ($responseUsuario->successful()) {
                    $usuario = $responseUsuario->json();
                    $correoUsuarioCreador = $usuario['correo'] ?? null;
                    $this->info('Correo del usuario creador encontrado: ' . $correoUsuarioCreador);
                }
            }
            
            // Enviar el correo con el PDF adjunto
            $this->enviarEmail($pdf, $envioCompleto, $nombreSede, $correoResponsableToma, $correoUsuarioCreador);
            
            $this->info('Envío ID: ' . $envio['id'] . ' procesado correctamente.');
            
        } catch (\Exception $e) {
            $this->error('Error al procesar envío ID: ' . $envio['id'] . ' - ' . $e->getMessage());
            Log::error('Error al procesar envío automático ID: ' . $envio['id'] . ' - ' . $e->getMessage());
        }
    }
    
    protected function generarPdf($envio)
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
    
    protected function enviarEmail($pdf, $envio, $nombreSede, $correoResponsableToma = null, $correoUsuarioCreador = null)
    {
        // Lista de destinatarios (puedes configurarla en el .env o en la base de datos)
        $destinatarios = config('laboratorio.emails_destinatarios', [
            'yeiserna14@gmail.com',
            'atencionalusuario.caucalab@gmail.com',
            // 'facturacion.caucalab@gmail.com',
            'julianvillalba91@hotmail.com',
        ]);
        
        // Agregar el correo del responsable de toma si existe y no está ya en la lista
        if ($correoResponsableToma && !in_array($correoResponsableToma, $destinatarios)) {
            $destinatarios[] = $correoResponsableToma;
            Log::info("Agregando correo del responsable de toma: {$correoResponsableToma}");
        }
        
        // Agregar el correo del usuario creador si existe y no está ya en la lista
        if ($correoUsuarioCreador && !in_array($correoUsuarioCreador, $destinatarios)) {
            $destinatarios[] = $correoUsuarioCreador;
            Log::info("Agregando correo del usuario creador: {$correoUsuarioCreador}");
        }
        
        // Nombre del archivo
        $filename = 'envio_muestras_' . $envio['id'] . '.pdf';
        
        // Enviar el correo
        Mail::send('emails.envio_muestras', ['envio' => $envio], function ($message) use ($pdf, $filename, $destinatarios, $nombreSede, $envio) {
            $message->subject('Envío Planillas de Laboratorio - ' . $nombreSede . ' - ' . Carbon::parse($envio['fecha'])->format('d/m/Y'));
            
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
        
        // Log para verificar que se envió correctamente
        Log::info("Email enviado a: " . implode(', ', $destinatarios));
    }
}