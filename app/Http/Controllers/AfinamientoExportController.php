<?php

namespace App\Http\Controllers;

use App\Services\ApiService;
use Illuminate\Http\Request;
use App\Exports\AfinamientosExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AfinamientoExportController extends Controller
{
    protected $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    public function exportForm()
    {
        try {
            // Obtener sedes para el filtro
            $sedesResponse = $this->apiService->get('sedes');
            $sedes = [];
            
            if ($sedesResponse->successful()) {
                $sedes = $sedesResponse->json();
                Log::info('Sedes obtenidas para filtro de afinamientos: ' . count($sedes));
            } else {
                Log::warning('No se pudieron obtener las sedes para el filtro de afinamientos');
            }
            
            return view('afinamientos.export', compact('sedes'));
        } catch (\Exception $e) {
            Log::error('Error al obtener sedes para afinamientos: ' . $e->getMessage());
            return view('afinamientos.export', ['sedes' => []]);
        }
    }

    public function exportExcel(Request $request)
    {
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'paciente_id' => 'nullable|string',
            'usuario_id' => 'nullable|string',
            'sede_id' => 'nullable|string', // Añadido filtro por sede
        ]);

        try {
            $fechaInicio = Carbon::parse($request->fecha_inicio)->format('Y-m-d');
            $fechaFin = Carbon::parse($request->fecha_fin)->format('Y-m-d');
            $sedeId = $request->sede_id;

            // Registrar en log para depuración
            Log::info('Exportando afinamientos con filtros:', [
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'paciente_id' => $request->paciente_id,
                'usuario_id' => $request->usuario_id,
                'sede_id' => $sedeId
            ]);

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
            
            // Filtrar por sede si se seleccionó una
            if ($sedeId && $sedeId !== 'todas') {
                Log::info('Filtrando afinamientos por sede: ' . $sedeId);
                
                $afinamientos = array_filter($afinamientos, function($afinamiento) use ($sedeId) {
                    // Buscar el ID de sede en diferentes posibles ubicaciones
                    $afinamientoSedeId = 
                        $afinamiento['sede_id'] ?? 
                        $afinamiento['idsede'] ?? 
                        $afinamiento['paciente']['sede_id'] ?? 
                        $afinamiento['paciente']['idsede'] ?? null;
                    
                    return $afinamientoSedeId == $sedeId;
                });
                
                // Convertir de nuevo a array indexado
                $afinamientos = array_values($afinamientos);
                
                Log::info('Afinamientos después de filtrar por sede: ' . count($afinamientos));
            }
            
            if (count($afinamientos) > 0) {
                // Generar nombre de archivo con información de sede
                $nombreArchivo = 'afinamientos_' . $fechaInicio . '_' . $fechaFin;
                
                if ($sedeId && $sedeId !== 'todas') {
                    $nombreSede = $this->obtenerNombreSede($sedeId);
                    $nombreArchivo .= '_' . str_replace(' ', '_', strtolower($nombreSede));
                }
                
                $nombreArchivo .= '.xlsx';
                
                Log::info('Generando archivo Excel de afinamientos: ' . $nombreArchivo);
                
                return Excel::download(new \App\Exports\AfinamientosExport($afinamientos), $nombreArchivo);
            }
            
            return back()->withErrors(['error' => 'No se encontraron afinamientos con los filtros seleccionados.']);
            
        } catch (\Exception $e) {
            Log::error('Error al exportar afinamientos: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return back()->withErrors(['error' => 'Error al generar el archivo Excel: ' . $e->getMessage()]);
        }
    }

    /**
     * Método auxiliar para obtener nombre de sede
     */
    private function obtenerNombreSede($sedeId)
    {
        try {
            $response = $this->apiService->get('sedes/' . $sedeId);
            if ($response->successful()) {
                $sede = $response->json();
                return $sede['nombresede'] ?? $sede['nombre'] ?? 'sede_' . $sedeId;
            }
        } catch (\Exception $e) {
            Log::error('Error al obtener nombre de sede para afinamientos: ' . $e->getMessage());
        }
        
        return 'sede_' . $sedeId;
    }
}