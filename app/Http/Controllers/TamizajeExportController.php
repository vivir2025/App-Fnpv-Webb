<?php

namespace App\Http\Controllers;

use App\Services\ApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
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
            // Obtener información del usuario
            $usuario = session('usuario');
            $rol = strtolower($usuario['rol'] ?? '');
            $sedeUsuario = $usuario['idsede'] ?? null;
            
            // Obtener sedes para el filtro
            $sedesResponse = $this->apiService->get('sedes');
            $todasLasSedes = [];
            
            if ($sedesResponse->successful()) {
                $todasLasSedes = $sedesResponse->json();
                // Log::info('Sedes obtenidas para filtro de tamizajes: ' . count($todasLasSedes));
            } else {
                // Log::warning('No se pudieron obtener las sedes para el filtro de tamizajes');
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
            
            return view('tamizajes.export', compact('sedes', 'permisos', 'usuario'));
        } catch (\Exception $e) {
            // Log::error('Error al obtener sedes para tamizajes: ' . $e->getMessage());
            return view('tamizajes.export', ['sedes' => [], 'permisos' => [], 'usuario' => []]);
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
            // Log::info('Exportando tamizajes con filtros:', [
            //     'fecha_inicio' => $fechaInicio,
            //     'fecha_fin' => $fechaFin,
            //     'paciente_id' => $request->paciente_id,
            //     'usuario_id' => $request->usuario_id,
            //     'sede_id' => $request->sede_id
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

            if ($request->filled('sede_id')) {
                $queryParams['sede_id'] = $request->sede_id;
            }

            // Obtener datos de tamizajes desde la API con timeout aumentado
            $response = $this->apiService->get('tamizajes', $queryParams, 60);
            
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
            if ($sedeId && $sedeId !== 'todas') {
                $totalAntesFiltro = count($tamizajes);
                
                $tamizajes = array_filter($tamizajes, function($tamizaje) use ($sedeId) {
                    // Verificar múltiples posibles estructuras de datos
                    $tamizajeSedeId = null;
                    
                    if (isset($tamizaje['sede']['id'])) {
                        $tamizajeSedeId = $tamizaje['sede']['id'];
                    } elseif (isset($tamizaje['sede']['idsede'])) {
                        $tamizajeSedeId = $tamizaje['sede']['idsede'];
                    } elseif (isset($tamizaje['sede_id'])) {
                        $tamizajeSedeId = $tamizaje['sede_id'];
                    } elseif (isset($tamizaje['idsede'])) {
                        $tamizajeSedeId = $tamizaje['idsede'];
                    } elseif (isset($tamizaje['paciente']['sede']['id'])) {
                        $tamizajeSedeId = $tamizaje['paciente']['sede']['id'];
                    } elseif (isset($tamizaje['paciente']['sede']['idsede'])) {
                        $tamizajeSedeId = $tamizaje['paciente']['sede']['idsede'];
                    } elseif (isset($tamizaje['paciente']['sede_id'])) {
                        $tamizajeSedeId = $tamizaje['paciente']['sede_id'];
                    } elseif (isset($tamizaje['paciente']['idsede'])) {
                        $tamizajeSedeId = $tamizaje['paciente']['idsede'];
                    }
                    
                    return $tamizajeSedeId == $sedeId;
                });
                
                // Convertir de nuevo a array indexado
                $tamizajes = array_values($tamizajes);
                
                // Log::info('TAMIZAJES Export - Filtrado manual:', [
                //     'total_api' => $totalAntesFiltro,
                //     'total_filtrado' => count($tamizajes),
                //     'sede_filtro' => $sedeId
                // ]);
            }
            
            if (count($tamizajes) > 0) {
                // Generar nombre de archivo con información de sede
                $nombreArchivo = 'tamizajes_' . $fechaInicio . '_' . $fechaFin;
                
                if ($sedeId && $sedeId !== 'todas') {
                    $nombreSede = $this->obtenerNombreSede($sedeId);
                    $nombreArchivo .= '_' . str_replace(' ', '_', strtolower($nombreSede));
                }
                
                $nombreArchivo .= '.xlsx';
                
                // Log::info('Generando archivo Excel de tamizajes: ' . $nombreArchivo);
                
                Cookie::queue('download_started', '1', 1);
                return Excel::download(new TamizajesExport($tamizajes), $nombreArchivo);
            }
            
            return back()->with('warning', 'No se encontraron tamizajes en el rango de fechas y sede seleccionados.');
            
        } catch (\Exception $e) {
            // Log::error('Error al exportar tamizajes: ' . $e->getMessage(), [
            //     'trace' => $e->getTraceAsString()
            // ]);
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
            // Log::error('Error al obtener nombre de sede para tamizajes: ' . $e->getMessage());
        }
        
        return 'sede_' . $sedeId;
    }
}