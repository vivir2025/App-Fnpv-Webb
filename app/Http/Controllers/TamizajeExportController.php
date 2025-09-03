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

    /**
     * Mostrar formulario de exportación con filtro de sedes
     */
    public function exportForm()
    {
        try {
            // Obtener sedes para el filtro
            $sedesResponse = $this->apiService->get('sedes');
            $sedes = [];
            
            if ($sedesResponse->successful()) {
                $sedes = $sedesResponse->json();
                Log::info('Sedes obtenidas para filtro de tamizajes: ' . count($sedes));
            } else {
                Log::warning('No se pudieron obtener las sedes para el filtro de tamizajes');
            }
            
            return view('tamizajes.export', compact('sedes'));
        } catch (\Exception $e) {
            Log::error('Error al obtener sedes para tamizajes: ' . $e->getMessage());
            return view('tamizajes.export', ['sedes' => []]);
        }
    }

    public function exportExcel(Request $request)
    {
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'paciente_id' => 'nullable|string',
            'usuario_id' => 'nullable|string',
            'sede_id' => 'nullable|string', // Añadido filtro de sede
        ]);

        try {
            $fechaInicio = Carbon::parse($request->fecha_inicio)->format('Y-m-d');
            $fechaFin = Carbon::parse($request->fecha_fin)->format('Y-m-d');

            // Registrar en log para depuración
            Log::info('Exportando tamizajes con filtros:', [
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'paciente_id' => $request->paciente_id,
                'usuario_id' => $request->usuario_id,
                'sede_id' => $request->sede_id
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

            if ($request->filled('sede_id')) {
                $queryParams['sede_id'] = $request->sede_id;
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
            
            // Filtrar por sede si es necesario y no se pudo filtrar en la API
            if ($request->filled('sede_id') && $request->sede_id !== '') {
                $sedeId = $request->sede_id;
                $tamizajes = collect($tamizajes)->filter(function ($tamizaje) use ($sedeId) {
                    return (isset($tamizaje['sede_id']) && $tamizaje['sede_id'] == $sedeId) ||
                           (isset($tamizaje['idsede']) && $tamizaje['idsede'] == $sedeId) ||
                           (isset($tamizaje['sede_paciente']) && strpos($tamizaje['sede_paciente'], $sedeId) !== false);
                })->values()->all();
            }
            
            if (count($tamizajes) > 0) {
                // Generar nombre de archivo con información de sede
                $nombreArchivo = 'tamizajes_' . $fechaInicio . '_' . $fechaFin;
                
                if ($request->filled('sede_id') && $request->sede_id !== '') {
                    $nombreSede = $this->obtenerNombreSede($request->sede_id);
                    $nombreArchivo .= '_' . str_replace(' ', '_', strtolower($nombreSede));
                }
                
                $nombreArchivo .= '.xlsx';
                
                Log::info('Generando archivo Excel de tamizajes: ' . $nombreArchivo);
                
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
            Log::error('Error al obtener nombre de sede para tamizajes: ' . $e->getMessage());
        }
        
        return 'sede_' . $sedeId;
    }
}