<?php

namespace App\Http\Controllers;

use App\Services\ApiService;
use Illuminate\Http\Request;
use App\Exports\AfinamientosExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class AfinamientoExportController extends Controller
{
    protected $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    public function exportForm()
    {
        return view('afinamientos.export');
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

            // Intentar con diferentes formatos de URL
            $endpoints = [
                'afinamientos',
                'api/afinamientos',
                'v1/afinamientos',
                'public/api/afinamientos',
                'afinamiento'
            ];
            
            $response = null;
            $endpointUsado = null;
            
            foreach ($endpoints as $endpoint) {
                $tempResponse = $this->apiService->get($endpoint, $queryParams);
                
                if ($tempResponse->successful()) {
                    $response = $tempResponse;
                    $endpointUsado = $endpoint;
                    break;
                }
            }
            
            if (!$response || !$response->successful()) {
                return back()->withErrors(['error' => 'No se pudo conectar con ninguna ruta de la API. Verifique la configuración.']);
            }
            
            $responseData = $response->json();
            
            // Determinar si los datos están en una clave 'data' o directamente en la raíz
            $afinamientos = is_array($responseData) && isset($responseData['data']) 
                ? $responseData['data'] 
                : $responseData;
                
            if (!is_array($afinamientos)) {
                return back()->withErrors(['error' => 'Formato de respuesta inesperado de la API.']);
            }
            
            if (count($afinamientos) > 0) {
                $nombreArchivo = 'afinamientos_' . $fechaInicio . '_' . $fechaFin . '.xlsx';
                return Excel::download(new \App\Exports\AfinamientosExport($afinamientos), $nombreArchivo);

            }
            
            return back()->withErrors(['error' => 'No se encontraron afinamientos en el rango de fechas seleccionado.']);
            
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al generar el archivo Excel: ' . $e->getMessage()]);
        }
    }

}
