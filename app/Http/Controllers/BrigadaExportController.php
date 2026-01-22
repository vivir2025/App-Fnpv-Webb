<?php

namespace App\Http\Controllers;

use App\Services\ApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cookie;
use App\Exports\BrigadasExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class BrigadaExportController extends Controller
{
    protected $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    /**
     * ✅ Mostrar formulario de exportación con filtro de sedes
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
                // Log::info('Sedes obtenidas para filtro de brigadas: ' . count($todasLasSedes));
            } else {
                // Log::warning('No se pudieron obtener las sedes para el filtro de brigadas');
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
            
            return view('brigadas.export', compact('sedes', 'permisos', 'usuario'));
        } catch (\Exception $e) {
            // Log::error('Error al obtener sedes para brigadas: ' . $e->getMessage());
            return view('brigadas.export', ['sedes' => [], 'permisos' => [], 'usuario' => []]);
        }
    }

    /**
     * ✅ Exportar brigadas a Excel con filtros de fecha y sede
     */
    public function exportExcel(Request $request)
    {
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'sede_id' => 'nullable|string', // ✅ FILTRO
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
            // Log::info('Exportando brigadas con filtros:', [
            //     'fecha_inicio' => $fechaInicio,
            //     'fecha_fin' => $fechaFin,
            //     'sede_id' => $sedeId
            // ]);

            // ✅ Inicializar variable antes de usar
            $allBrigadas = [];

            // Consumir la API para obtener brigadas con relaciones (incluyendo pacientes) con timeout aumentado
            $response = $this->apiService->get('brigadas?include=pacientes', [], 60);
            
            if ($response->successful()) {
                $allBrigadas = $response->json()['data'] ?? [];
                
                // Registrar la estructura de datos para depuración
                if (count($allBrigadas) > 0) {
                    // Log::debug('Total de brigadas obtenidas de la API: ' . count($allBrigadas));
                    
                    // Registrar más detalles de la primera brigada para depuración
                    $primeraBrigada = $allBrigadas[0];
                    // Log::debug('Estructura detallada de la primera brigada:', [
                    //     'id' => $primeraBrigada['id'] ?? 'N/A',
                    //     'fecha_brigada' => $primeraBrigada['fecha_brigada'] ?? 'N/A',
                    //     'tiene_pacientes' => isset($primeraBrigada['pacientes']) && count($primeraBrigada['pacientes']) > 0,
                    //     'num_pacientes' => isset($primeraBrigada['pacientes']) ? count($primeraBrigada['pacientes']) : 0
                    // ]);
            }
                
                // ✅ MODIFICADO: Filtrar por rango de fechas y sede
                $brigadas = collect($allBrigadas)->filter(function ($brigada) use ($fechaInicio, $fechaFin, $sedeId) {
                    // Filtro por fecha
                    if (!isset($brigada['fecha_brigada'])) {
                        return false;
                    }
                    $fechaBrigada = Carbon::parse($brigada['fecha_brigada'])->format('Y-m-d');
                    $fechaValida = $fechaBrigada >= $fechaInicio && $fechaBrigada <= $fechaFin;
        
                    // Si queremos todas las sedes, solo filtramos por fecha
                    if (!$sedeId || $sedeId === 'todas') {
                        return $fechaValida;
                    }
                    
                    // Verificar sede en la brigada directamente
                    $sedeCorrecta = false;
                    
                    // Primero verificar si la brigada tiene sede directamente
                    if (isset($brigada['sede']['id'])) {
                        $sedeCorrecta = $brigada['sede']['id'] == $sedeId;
                    } elseif (isset($brigada['sede']['idsede'])) {
                        $sedeCorrecta = $brigada['sede']['idsede'] == $sedeId;
                    } elseif (isset($brigada['sede_id'])) {
                        $sedeCorrecta = $brigada['sede_id'] == $sedeId;
                    } elseif (isset($brigada['idsede'])) {
                        $sedeCorrecta = $brigada['idsede'] == $sedeId;
                    }
                    
                    // Si no encontramos sede directamente, buscar en pacientes
                    if (!$sedeCorrecta && isset($brigada['pacientes']) && !empty($brigada['pacientes'])) {
                        foreach ($brigada['pacientes'] as $paciente) {
                            if ((isset($paciente['idsede']) && $paciente['idsede'] == $sedeId) || 
                                (isset($paciente['sede_id']) && $paciente['sede_id'] == $sedeId)) {
                                $sedeCorrecta = true;
                                break;
                            }
                        }
                    }
                    
                    return $fechaValida && $sedeCorrecta;
                })->values()->all();
                
                // Registrar en log para depuración
                // Log::info('Brigadas filtradas:', [
                //     'total_api' => count($allBrigadas),
                //     'total_filtrado' => count($brigadas),
                //     'sede_filtro' => $sedeId
                // ]);
                
                if (count($brigadas) > 0) {
                    // ✅ MODIFICADO: Registrar estructura de la primera brigada después del filtro
                    if (count($brigadas) > 0) {
                        $primeraBrigadaFiltrada = $brigadas[0];
                        // Log::debug('Estructura de la primera brigada después del filtro:', [
                        //     'id' => $primeraBrigadaFiltrada['id'] ?? 'N/A',
                        //     'fecha_brigada' => $primeraBrigadaFiltrada['fecha_brigada'] ?? 'N/A',
                        //     'num_pacientes' => isset($primeraBrigadaFiltrada['pacientes']) ? count($primeraBrigadaFiltrada['pacientes']) : 0
                        // ]);
                    }
                    
                    // Cargar detalles completos de cada brigada
                    $datosProcesados = [];
                    
                    foreach ($brigadas as $brigada) {
                        // Obtener detalle de la brigada con relaciones
                        $detailResponse = $this->apiService->get('brigadas/' . $brigada['id'] . '?include=usuario.sede,medicamentos_pacientes.medicamento,medicamentos_pacientes.paciente');
                        
                        if ($detailResponse->successful()) {
                            $brigadaDetalle = $detailResponse->json()['data'];
                            
                            // ✅ MODIFICADO: Asegurarse de que la brigada tenga la información de pacientes
                            if (!isset($brigadaDetalle['pacientes']) && isset($brigada['pacientes'])) {
                                $brigadaDetalle['pacientes'] = $brigada['pacientes'];
                            }
                            
                            // ✅ NUEVO: Asignar información de sede a la brigada basado en los pacientes
                    if ($sedeId && $sedeId !== 'todas') {
                                // Buscar la sede seleccionada para mostrarla en el Excel
                                $sedeResponse = $this->apiService->get('sedes/' . $sedeId);
                                if ($sedeResponse->successful()) {
                                    $sede = $sedeResponse->json();
                                    $nombreSede = $sede['nombresede'] ?? $sede['nombre'] ?? 'Sede ' . $sedeId;
                                    
                                    // Crear estructura de usuario y sede si no existe
                                    if (!isset($brigadaDetalle['usuario'])) {
                                        $brigadaDetalle['usuario'] = [
                                            'nombre' => 'Usuario de brigada',
                                            'sede' => [
                                                'id' => $sedeId,
                                                'nombresede' => $nombreSede
                                            ]
                                        ];
                                    } else if (!isset($brigadaDetalle['usuario']['sede'])) {
                                        $brigadaDetalle['usuario']['sede'] = [
                                            'id' => $sedeId,
                                            'nombresede' => $nombreSede
                                        ];
                    }
                                }
                            }
                            
                            // Verificar si hay medicamentos_pacientes en la respuesta
                            if (isset($brigadaDetalle['medicamentos_pacientes']) && !empty($brigadaDetalle['medicamentos_pacientes'])) {
                                $medicamentosPacientes = $brigadaDetalle['medicamentos_pacientes'];
                                
                                // Filtrar medicamentos_pacientes por sede si es necesario
                                if ($sedeId && $sedeId !== 'todas') {
                                    $medicamentosPacientes = array_filter($medicamentosPacientes, function($mp) use ($sedeId) {
                                        if (!isset($mp['paciente'])) return false;
                                        
                                        return (isset($mp['paciente']['idsede']) && $mp['paciente']['idsede'] == $sedeId) || 
                                               (isset($mp['paciente']['sede_id']) && $mp['paciente']['sede_id'] == $sedeId);
                                    });
                                }
                                
                                foreach ($medicamentosPacientes as $medPaciente) {
                                    // Verificar que tenga la información necesaria
                                    if (isset($medPaciente['medicamento']) && isset($medPaciente['paciente'])) {
                                        $datosProcesados[] = [
                                            'brigada' => $brigadaDetalle,
                                            'paciente' => $medPaciente['paciente'],
                                            'medicamento' => $medPaciente['medicamento'],
                                            'relacion' => [
                                                'cantidad' => $medPaciente['cantidad'] ?? null,
                                                'dosis' => $medPaciente['dosis'] ?? null,
                                                'indicaciones' => $medPaciente['indicaciones'] ?? null
                                            ]
                                        ];
                                    }
                                }
                            } else {
                                // Si no hay medicamentos, al menos agregar los pacientes
                                if (isset($brigadaDetalle['pacientes']) && !empty($brigadaDetalle['pacientes'])) {
                                    $pacientes = $brigadaDetalle['pacientes'];
                    
                                    // Filtrar pacientes por sede si es necesario
                                    if ($sedeId && $sedeId !== 'todas') {
                                        $pacientes = array_filter($pacientes, function($p) use ($sedeId) {
                                            return (isset($p['idsede']) && $p['idsede'] == $sedeId) || 
                                                   (isset($p['sede_id']) && $p['sede_id'] == $sedeId);
                                        });
                }
                
                                    foreach ($pacientes as $paciente) {
                                        $datosProcesados[] = [
                                            'brigada' => $brigadaDetalle,
                                            'paciente' => $paciente,
                                            'medicamento' => null,
                                            'relacion' => null
                                        ];
            }
                                } else {
                                    // Si no hay ni pacientes ni medicamentos, al menos agregar la brigada
                                    $datosProcesados[] = [
                                        'brigada' => $brigadaDetalle,
                                        'paciente' => null,
                                        'medicamento' => null,
                                        'relacion' => null
                                    ];
                                }
                            }
                        }
                    }
            
                    if (empty($datosProcesados)) {
                        return back()->withErrors(['error' => 'No se pudieron cargar los detalles de las brigadas.']);
                    }
            
                    // Generar nombre de archivo con información de sede
                    $nombreArchivo = 'brigadas_medicamentos_' . $fechaInicio . '_' . $fechaFin;
                    if ($sedeId && $sedeId !== 'todas') {
                        $nombreSede = $this->obtenerNombreSede($sedeId);
                        $nombreArchivo .= '_' . str_replace(' ', '_', strtolower($nombreSede));
        }
                    $nombreArchivo .= '.xlsx';
                    
                    // Log::info('Generando archivo Excel de brigadas: ' . $nombreArchivo);
                    
                    Cookie::queue('download_started', '1', 1);
                    return Excel::download(new BrigadasExport($datosProcesados), $nombreArchivo);
    }

                // ✅ MODIFICADO: Retornar con warning en lugar de error cuando no hay datos
                return back()->with('warning', 'No se encontraron brigadas en el rango de fechas y sede seleccionados. Total encontrado en API: ' . count($allBrigadas) . ', Total después del filtro: 0');
            }
            
            return back()->withErrors(['error' => 'Error al obtener datos de brigadas desde la API.']);
        } catch (\Exception $e) {
            // Log::error('Error al exportar brigadas: ' . $e->getMessage());
            // Log::error('Stack trace: ' . $e->getTraceAsString());
            return back()->withErrors(['error' => 'Error al generar el archivo Excel: ' . $e->getMessage()]);
        }
    }

    /**
     * ✅ Método auxiliar para obtener nombre de sede
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
            // Log::error('Error al obtener nombre de sede para brigadas: ' . $e->getMessage());
        }
        
        return 'sede_' . $sedeId;
    }
}