<?php

namespace App\Http\Controllers;

use App\Services\ApiService;
use Illuminate\Http\Request;
use App\Exports\TamizajesExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class TamizajeExportController extends Controller
{
    protected $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    public function exportForm()
    {
        return view('tamizajes.export');
    }

    public function exportExcel(Request $request)
    {
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'paciente_id' => 'nullable|string',
            'usuario_id' => 'nullable|string',
        ]);

        try {
            $fechaInicio = Carbon::parse($request->fecha_inicio)->format('Y-m-d');
            $fechaFin = Carbon::parse($request->fecha_fin)->format('Y-m-d');

            // Construir parámetros de consulta
            $queryParams = [
                'fecha_desde' => $fechaInicio,
                'fecha_hasta' => $fechaFin
            ];

            if ($request->filled('paciente_id')) {
                $queryParams['paciente_id'] = $request->paciente_id;
            }

            if ($request->filled('usuario_id')) {
                $queryParams['usuario_id'] = $request->usuario_id;
            }

            // Obtener datos de tamizajes desde la API
            $response = $this->apiService->get('tamizajes', $queryParams);
            
            if (!$response->successful()) {
                return back()->withErrors(['error' => 'No se pudo conectar con la API. Verifique la configuración.']);
            }
            
            $responseData = $response->json();
            
            // Determinar si los datos están en una clave 'data' o directamente en la raíz
            $tamizajes = is_array($responseData) && isset($responseData['data']) 
                ? $responseData['data'] 
                : $responseData;
                
            if (!is_array($tamizajes)) {
                return back()->withErrors(['error' => 'Formato de respuesta inesperado de la API.']);
            }
            
            if (count($tamizajes) > 0) {
                $nombreArchivo = 'tamizajes_' . $fechaInicio . '_' . $fechaFin . '.xlsx';
                return Excel::download(new TamizajesExport($tamizajes), $nombreArchivo);
            }
            
            return back()->withErrors(['error' => 'No se encontraron tamizajes en el rango de fechas seleccionado.']);
            
        } catch (\Exception $e) {
            Log::error('Error al exportar tamizajes: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withErrors(['error' => 'Error al generar el archivo Excel: ' . $e->getMessage()]);
        }
    }
}
