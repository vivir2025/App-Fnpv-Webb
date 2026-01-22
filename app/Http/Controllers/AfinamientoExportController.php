<?php

namespace App\Http\Controllers;

use App\Services\ApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
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
            // Obtener información del usuario
            $usuario = session('usuario');
            $rol = strtolower($usuario['rol'] ?? '');
            $sedeUsuario = $usuario['idsede'] ?? null;
            
            // Obtener sedes para el filtro
            $sedesResponse = $this->apiService->get('sedes');
            $todasLasSedes = [];
            
            if ($sedesResponse->successful()) {
                $todasLasSedes = $sedesResponse->json();
                // Log::info('Sedes obtenidas para filtro de afinamientos: ' . count($todasLasSedes));
            } else {
                // Log::warning('No se pudieron obtener las sedes para el filtro de afinamientos');
            }
            
            // Filtrar sedes según el rol
            if (in_array($rol, ['admin', 'administrador'])) {
                $sedes = $todasLasSedes;
            } elseif (in_array($rol, ['jefe', 'coordinador'])) {
                $sedes = array_filter($todasLasSedes, function($sede) use ($sedeUsuario) {
                    return ($sede['id'] ?? null) === $sedeUsuario;
                });
            } else {
                $sedes = [];
            }
            
            // Permisos para la vista
            $permisos = [
                'puede_ver_todas_sedes' => in_array($rol, ['admin', 'administrador']),
                'es_jefe' => in_array($rol, ['jefe', 'coordinador']),
                'sede_id' => $sedeUsuario
            ];
            
            return view('afinamientos.export', compact('sedes', 'permisos', 'usuario'));
        } catch (\Exception $e) {
            // Log::error('Error al obtener sedes para afinamientos: ' . $e->getMessage());
            return view('afinamientos.export', ['sedes' => [], 'permisos' => [], 'usuario' => []]);
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
            // Validar permisos del usuario
            $usuario = session('usuario');
            $rol = strtolower($usuario['rol'] ?? '');
            $sedeUsuario = $usuario['idsede'] ?? null;
            
            $fechaInicio = Carbon::parse($request->fecha_inicio)->format('Y-m-d');
            $fechaFin = Carbon::parse($request->fecha_fin)->format('Y-m-d');
            $sedeId = $request->sede_id;
            
            // Aplicar restricciones según el rol
            if (in_array($rol, ['jefe', 'coordinador'])) {
                // Jefe solo puede exportar su sede
                $sedeId = $sedeUsuario;
            } elseif (!in_array($rol, ['admin', 'administrador'])) {
                return back()->withErrors(['error' => 'No tiene permisos para exportar datos']);
            }

            // Registrar en log para depuración
            // Log::info('Exportando afinamientos con filtros:', [
            //     'fecha_inicio' => $fechaInicio,
            //     'fecha_fin' => $fechaFin,
            //     'paciente_id' => $request->paciente_id,
            //     'usuario_id' => $request->usuario_id,
            //     'sede_id' => $sedeId
            // ]);

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
                $tempResponse = $this->apiService->get($endpoint, $queryParams, 60);
                
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
                $totalAntesFiltro = count($afinamientos);
                // Log::info('Filtrando afinamientos por sede: ' . $sedeId . ', Total antes del filtro: ' . $totalAntesFiltro);
                
                $afinamientos = array_filter($afinamientos, function($afinamiento) use ($sedeId) {
                    // Buscar el ID de sede en diferentes posibles ubicaciones
                    $afinamientoSedeId = null;
                    
                    if (isset($afinamiento['sede']['id'])) {
                        $afinamientoSedeId = $afinamiento['sede']['id'];
                    } elseif (isset($afinamiento['sede']['idsede'])) {
                        $afinamientoSedeId = $afinamiento['sede']['idsede'];
                    } elseif (isset($afinamiento['sede_id'])) {
                        $afinamientoSedeId = $afinamiento['sede_id'];
                    } elseif (isset($afinamiento['idsede'])) {
                        $afinamientoSedeId = $afinamiento['idsede'];
                    } elseif (isset($afinamiento['paciente']['sede']['id'])) {
                        $afinamientoSedeId = $afinamiento['paciente']['sede']['id'];
                    } elseif (isset($afinamiento['paciente']['sede']['idsede'])) {
                        $afinamientoSedeId = $afinamiento['paciente']['sede']['idsede'];
                    } elseif (isset($afinamiento['paciente']['sede_id'])) {
                        $afinamientoSedeId = $afinamiento['paciente']['sede_id'];
                    } elseif (isset($afinamiento['paciente']['idsede'])) {
                        $afinamientoSedeId = $afinamiento['paciente']['idsede'];
                    }
                    
                    return $afinamientoSedeId == $sedeId;
                });
                
                // Convertir de nuevo a array indexado
                $afinamientos = array_values($afinamientos);
                
                // Log::info('AFINAMIENTOS Export - Filtrado manual:', [
                //     'total_api' => $totalAntesFiltro,
                //     'total_filtrado' => count($afinamientos),
                //     'sede_filtro' => $sedeId
                // ]);
            }
            
            if (count($afinamientos) > 0) {
                // Generar nombre de archivo con información de sede
                $nombreArchivo = 'afinamientos_' . $fechaInicio . '_' . $fechaFin;
                
                if ($sedeId && $sedeId !== 'todas') {
                    $nombreSede = $this->obtenerNombreSede($sedeId);
                    $nombreArchivo .= '_' . str_replace(' ', '_', strtolower($nombreSede));
                }
                
                $nombreArchivo .= '.xlsx';
                
                // Log::info('Generando archivo Excel de afinamientos: ' . $nombreArchivo);
                
                Cookie::queue('download_started', '1', 1);
                return Excel::download(new \App\Exports\AfinamientosExport($afinamientos), $nombreArchivo);
            }
            
            return back()->with('warning', 'No se encontraron afinamientos con los filtros seleccionados.');
            
        } catch (\Exception $e) {
            // Log::error('Error al exportar afinamientos: ' . $e->getMessage());
            // Log::error('Stack trace: ' . $e->getTraceAsString());
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
            // Log::error('Error al obtener nombre de sede para afinamientos: ' . $e->getMessage());
        }
        
        return 'sede_' . $sedeId;
    }
}