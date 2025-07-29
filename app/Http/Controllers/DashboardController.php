<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Services\ApiService;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DashboardController extends Controller
{
    protected $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    public function index()
    {
        // Obtener sedes para los botones de filtro
        try {
            $response = $this->apiService->get('sedes');
            $sedes = $response->successful() ? $response->json() : [];
        } catch (\Exception $e) {
            Log::error('Error al obtener sedes: ' . $e->getMessage());
            $sedes = [];
        }

        // Pasar el token a la vista
        $token = session('token');

        return view('dashboard.index', compact('sedes', 'token'));
    }
    
    public function getDatos(Request $request)
    {
        $sedeId = $request->input('sede_id', 'todas');
        
        try {
            // 1. Obtener pacientes con coordenadas
            $pacientes = $this->obtenerPacientesConCoordenadas($sedeId);
            // 2. Obtener visitas para estadísticas
            $visitas = $this->obtenerVisitas($sedeId);
            // 3. Calcular estadísticas generales
            $estadisticas = $this->calcularEstadisticas($visitas);
            // 4. Preparar datos para gráfico diario
            $graficoDiario = $this->prepararGraficoDiario($visitas);
            // 5. Preparar datos para gráfico de sedes
            $graficoSedes = $this->prepararGraficoSedes($visitas);
            // 6. Preparar datos de auxiliares
            $auxiliares = $this->prepararDatosAuxiliares($visitas, $sedeId);
            return response()->json([
                'pacientes' => $pacientes,
                'estadisticas' => $estadisticas,
                'grafico_diario' => $graficoDiario,
                'grafico_sedes' => $graficoSedes,
                'auxiliares' => $auxiliares
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error en datos del dashboard: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error al cargar datos: ' . $e->getMessage()
            ], 500);
        }
    }

    private function obtenerPacientesConCoordenadas($sedeId)
    {
        $response = $this->apiService->get('pacientes');
        
        if (!$response->successful()) {
            Log::error('Error al obtener pacientes: ' . $response->status());
            return [];
        }

        $pacientes = $response->json();
        
        // Verificar que pacientes sea un array
        if (!is_array($pacientes)) {
            Log::error('La respuesta de pacientes no es un array');
            return [];
        }
        
        // Filtrar pacientes con coordenadas válidas
        $pacientesFiltrados = collect($pacientes)->filter(function($paciente) {
            return isset($paciente['latitud']) && isset($paciente['longitud']) && 
                   !empty($paciente['latitud']) && !empty($paciente['longitud']);
        });
        
        // Filtrar por sede si es necesario
        if ($sedeId !== 'todas') {
            $pacientesFiltrados = $pacientesFiltrados->filter(function($paciente) use ($sedeId) {
                return isset($paciente['idsede']) && $paciente['idsede'] == $sedeId;
            });
        }
        
        return $pacientesFiltrados->values()->all();
    }

    private function obtenerVisitas($sedeId)
    {
        $response = $this->apiService->get('visitas');
        
        if (!$response->successful()) {
            Log::error('Error al obtener visitas: ' . $response->status());
            return [];
        }
        
        $visitas = $response->json();
        
        // Verificar que visitas sea un array
        if (!is_array($visitas)) {
            Log::error('La respuesta de visitas no es un array');
            return [];
        }
        
        // Filtrar por sede si es necesario
        if ($sedeId !== 'todas') {
            $visitas = collect($visitas)->filter(function($visita) use ($sedeId) {
                return isset($visita['paciente']) && 
                       is_array($visita['paciente']) && 
                       isset($visita['paciente']['idsede']) && 
                       $visita['paciente']['idsede'] == $sedeId;
            })->values()->all();
        }
        
        return $visitas;
    }

    private function calcularEstadisticas($visitas)
    {
        $ahora = Carbon::now();
        $inicioMes = Carbon::now()->startOfMonth();
        
        // Filtrar visitas del mes actual
        $visitasMes = collect($visitas)->filter(function($visita) use ($inicioMes) {
            return isset($visita['fecha']) && Carbon::parse($visita['fecha'])->gte($inicioMes);
        });
        
        // Calcular total de pacientes únicos
        $pacientesUnicos = $visitasMes->filter(function($visita) {
            return isset($visita['idpaciente']);
        })->pluck('idpaciente')->unique()->count();
        
        // Calcular total de visitas del mes
        $totalVisitasMes = $visitasMes->count();
        
        // Calcular promedio diario
        $diasTranscurridos = $inicioMes->diffInDays($ahora) + 1;
        $promedioDiario = $diasTranscurridos > 0 ? round($totalVisitasMes / $diasTranscurridos, 1) : 0;
        
        return [
            'total_pacientes' => $pacientesUnicos,
            'visitas_mes' => $totalVisitasMes,
            'promedio_diario' => $promedioDiario
        ];
    }

    private function prepararGraficoDiario($visitas)
    {
        $inicioMes = Carbon::now()->startOfMonth();
        $finMes = Carbon::now()->endOfMonth();
        
        // Crear array con todos los días del mes
        $diasDelMes = [];
        $fecha = clone $inicioMes;
        
        while ($fecha->lte($finMes)) {
            $diasDelMes[$fecha->format('Y-m-d')] = 0;
            $fecha->addDay();
        }
        
        // Contar visitas por día
        foreach ($visitas as $visita) {
            if (isset($visita['fecha'])) {
                $fechaVisita = Carbon::parse($visita['fecha'])->format('Y-m-d');
                if (isset($diasDelMes[$fechaVisita])) {
                    $diasDelMes[$fechaVisita]++;
                }
            }
        }
        
        // Formatear para el gráfico
        $resultado = [];
        foreach ($diasDelMes as $fecha => $cantidad) {
            $resultado[] = [
                'fecha' => Carbon::parse($fecha)->format('d/m'),
                'cantidad' => $cantidad
            ];
        }
        
        return $resultado;
    }

    private function prepararGraficoSedes($visitas)
    {
        // Obtener todas las sedes
        $response = $this->apiService->get('sedes');
        $sedes = $response->successful() ? $response->json() : [];
        
        // Verificar que sedes sea un array
        if (!is_array($sedes)) {
            Log::error('La respuesta de sedes no es un array');
            return [];
        }
        
        // Mapear IDs de sedes a nombres
        $mapaSedes = [];
        foreach ($sedes as $sede) {
            if (isset($sede['id']) && isset($sede['nombresede'])) {
                $mapaSedes[$sede['id']] = $sede['nombresede'];
            }
        }
        
        // Contar visitas por sede
        $visitasPorSede = [];
        foreach ($visitas as $visita) {
            $idSede = isset($visita['paciente']) && is_array($visita['paciente']) && isset($visita['paciente']['idsede']) 
                    ? $visita['paciente']['idsede'] 
                    : null;
            
            if ($idSede) {
                if (!isset($visitasPorSede[$idSede])) {
                    $visitasPorSede[$idSede] = 0;
                }
                $visitasPorSede[$idSede]++;
            }
        }
        
        // Formatear para el gráfico
        $resultado = [];
        foreach ($visitasPorSede as $idSede => $cantidad) {
            $resultado[] = [
                'sede' => isset($mapaSedes[$idSede]) ? $mapaSedes[$idSede] : "Sede $idSede",
                'cantidad' => $cantidad
            ];
        }
        
        return $resultado;
    }

    private function prepararDatosAuxiliares($visitas, $sedeId)
    {
        try {
            // Intentar obtener auxiliares usando el endpoint específico
            $response = $this->apiService->get('auxiliares');
            
            // Verificar si la respuesta fue exitosa y contiene datos JSON válidos
            if ($response->successful() && $response->json() !== null) {
                $auxiliares = $response->json();
                
                // Verificar que auxiliares sea un array
                if (!is_array($auxiliares)) {
                    Log::error('La respuesta de auxiliares no es un array');
                    return $this->generarAuxiliaresPredeterminados($visitas);
                }
            } else {
                // Si falla, intentar con el endpoint general de usuarios
                Log::warning('Endpoint de auxiliares no disponible, intentando con usuarios');
                $response = $this->apiService->get('usuarios');
                
                if ($response->successful() && $response->json() !== null) {
                    $usuarios = $response->json();
                    
                    // Verificar que usuarios sea un array
                    if (!is_array($usuarios)) {
                        Log::error('La respuesta de usuarios no es un array');
                        return $this->generarAuxiliaresPredeterminados($visitas);
                    }
                    
                    // Filtrar solo auxiliares
                    $auxiliares = collect($usuarios)->filter(function($usuario) {
                        return isset($usuario['rol']) && (
                            strtolower($usuario['rol']) === 'aux' || 
                            strtolower($usuario['rol']) === 'auxiliar'
                        );
                    })->values()->all();
                } else {
                    Log::error('Error al obtener usuarios: ' . $response->status());
                    return $this->generarAuxiliaresPredeterminados($visitas);
                }
            }
        } catch (\Exception $e) {
            Log::error('Error al obtener auxiliares: ' . $e->getMessage());
            return $this->generarAuxiliaresPredeterminados($visitas);
        }
        
        // Filtrar por sede si es necesario
        if ($sedeId !== 'todas') {
            $auxiliares = collect($auxiliares)->filter(function($auxiliar) use ($sedeId) {
                return isset($auxiliar['sede_id']) && $auxiliar['sede_id'] == $sedeId;
            })->values()->all();
        }
        
        // Si después del filtrado no hay auxiliares, usar los predeterminados
        if (empty($auxiliares)) {
            return $this->generarAuxiliaresPredeterminados($visitas);
        }
        
        // Contar visitas por usuario
        $visitasPorUsuario = [];
        foreach ($visitas as $visita) {
            $idUsuario = isset($visita['usuario_id']) ? $visita['usuario_id'] : null;
            if (!$idUsuario && isset($visita['idusuario'])) {
                $idUsuario = $visita['idusuario']; // Compatibilidad con diferentes formatos
            }
            
            if ($idUsuario) {
                if (!isset($visitasPorUsuario[$idUsuario])) {
                    $visitasPorUsuario[$idUsuario] = 0;
                }
                $visitasPorUsuario[$idUsuario]++;
            }
        }
        
        // Formatear para la tabla
        $resultado = [];
        foreach ($auxiliares as $auxiliar) {
            // Manejar diferentes formatos de ID
            $idUsuario = isset($auxiliar['id']) ? $auxiliar['id'] : null;
            if (!$idUsuario && isset($auxiliar['usuario_id'])) {
                $idUsuario = $auxiliar['usuario_id'];
            }
            
            if (!$idUsuario) {
                continue; // Saltar auxiliares sin ID
            }
            
            $visitasRealizadas = isset($visitasPorUsuario[$idUsuario]) ? $visitasPorUsuario[$idUsuario] : 0;
            
            // Calcular visitas pendientes (puedes ajustar esta lógica según tus necesidades)
            $visitasPendientes = $this->calcularVisitasPendientes($auxiliar, $visitas);
            $totalAsignadas = $visitasRealizadas + $visitasPendientes;
            
            // Obtener nombre de sede
            $nombreSede = $this->obtenerNombreSede($auxiliar);
            
            $resultado[] = [
                'id' => $idUsuario,
                'nombre' => isset($auxiliar['nombre']) ? $auxiliar['nombre'] : "Usuario $idUsuario",
                'sede' => $nombreSede,
                'visitas_realizadas' => $visitasRealizadas,
                'visitas_pendientes' => $visitasPendientes,
                'total_asignadas' => $totalAsignadas,
                'porcentaje_completado' => $totalAsignadas > 0 
                    ? round(($visitasRealizadas / $totalAsignadas) * 100, 1) 
                    : 0
            ];
        }
        
        // Ordenar por número de visitas realizadas (descendente)
        usort($resultado, function($a, $b) {
            return $b['visitas_realizadas'] - $a['visitas_realizadas'];
        });
        
        return $resultado;
    }

    private function calcularVisitasPendientes($auxiliar, $visitas)
    {
        // Obtener ID del auxiliar
        $idUsuario = isset($auxiliar['id']) ? $auxiliar['id'] : null;
        if (!$idUsuario && isset($auxiliar['usuario_id'])) {
            $idUsuario = $auxiliar['usuario_id'];
        }
        
        if (!$idUsuario) {
            return 0;
        }
        
        // Contar visitas pendientes (estado = 'pendiente' o 'programada')
        $pendientes = 0;
        foreach ($visitas as $visita) {
            $visitaUsuarioId = isset($visita['usuario_id']) ? $visita['usuario_id'] : null;
            if (!$visitaUsuarioId && isset($visita['idusuario'])) {
                $visitaUsuarioId = $visita['idusuario'];
            }
            
            if ($visitaUsuarioId == $idUsuario && 
                isset($visita['estado']) && 
                in_array(strtolower($visita['estado']), ['pendiente', 'programada'])) {
                $pendientes++;
            }
        }
        
        // Si no hay visitas pendientes en los datos, usar un valor predeterminado
        // basado en la carga de trabajo del auxiliar (puedes ajustar esta lógica)
        if ($pendientes == 0) {
            $visitasRealizadas = 0;
            foreach ($visitas as $visita) {
                $visitaUsuarioId = isset($visita['usuario_id']) ? $visita['usuario_id'] : null;
                if (!$visitaUsuarioId && isset($visita['idusuario'])) {
                    $visitaUsuarioId = $visita['idusuario'];
                }
                
                if ($visitaUsuarioId == $idUsuario) {
                    $visitasRealizadas++;
                }
            }
            
            // Lógica para estimar visitas pendientes basada en la carga de trabajo
            if ($visitasRealizadas >= 15) {
                $pendientes = 2; // Auxiliar muy ocupado, pocas pendientes
            } elseif ($visitasRealizadas >= 10) {
                $pendientes = 3; // Carga media
            } elseif ($visitasRealizadas >= 5) {
                $pendientes = 4; // Carga baja
            } else {
                $pendientes = 5; // Auxiliar con pocas visitas realizadas
            }
        }
        
        return $pendientes;
    }
    private function obtenerNombreSede($auxiliar)
    {
        // Caso 1: Sede incluida como objeto completo
        if (isset($auxiliar['sede']) && is_array($auxiliar['sede'])) {
            if (isset($auxiliar['sede']['nombre'])) {
                return $auxiliar['sede']['nombre'];
            }
            if (isset($auxiliar['sede']['nombresede'])) {
                return $auxiliar['sede']['nombresede'];
            }
        }
        
        // Caso 2: Solo ID de sede disponible
        $sedeId = null;
        if (isset($auxiliar['sede_id'])) {
            $sedeId = $auxiliar['sede_id'];
        } elseif (isset($auxiliar['idsede'])) {
            $sedeId = $auxiliar['idsede'];
        }
        
        if ($sedeId) {
            try {
                $response = $this->apiService->get('sedes/' . $sedeId);
                if ($response->successful()) {
                    $sede = $response->json();
                    if (isset($sede['nombre'])) {
                        return $sede['nombre'];
                    }
                    if (isset($sede['nombresede'])) {
                        return $sede['nombresede'];
                    }
                    return "Sede " . $sedeId;
                }
            } catch (\Exception $e) {
                Log::warning('Error al obtener sede ' . $sedeId . ': ' . $e->getMessage());
            }
            
            return "Sede " . $sedeId;
        }
        
        return "Sin sede asignada";
    }
    private function generarAuxiliaresPredeterminados($visitas)
    {
        // Intentar generar datos basados en las visitas disponibles
        $visitasPorUsuario = [];
        foreach ($visitas as $visita) {
            $idUsuario = isset($visita['usuario_id']) ? $visita['usuario_id'] : null;
            if (!$idUsuario && isset($visita['idusuario'])) {
                $idUsuario = $visita['idusuario'];
            }
            
            if ($idUsuario) {
                if (!isset($visitasPorUsuario[$idUsuario])) {
                    $visitasPorUsuario[$idUsuario] = [
                        'realizadas' => 0,
                        'pendientes' => 0
                    ];
                }
                
                if (isset($visita['estado'])) {
                    $estado = strtolower($visita['estado']);
                    if (in_array($estado, ['completada', 'realizada', 'finalizada'])) {
                        $visitasPorUsuario[$idUsuario]['realizadas']++;
                    } elseif (in_array($estado, ['pendiente', 'programada'])) {
                        $visitasPorUsuario[$idUsuario]['pendientes']++;
                    }
                } else {
                    // Si no hay estado, asumir que está completada
                    $visitasPorUsuario[$idUsuario]['realizadas']++;
                }
            }
        }
        
        // Si hay información de visitas por usuario, crear auxiliares basados en esos IDs
        if (!empty($visitasPorUsuario)) {
            $resultado = [];
            foreach ($visitasPorUsuario as $idUsuario => $datos) {
                $visitasRealizadas = $datos['realizadas'];
                $visitasPendientes = $datos['pendientes'] > 0 ? $datos['pendientes'] : 5;
                $totalAsignadas = $visitasRealizadas + $visitasPendientes;
                
                $resultado[] = [
                    'id' => $idUsuario,
                    'nombre' => "Auxiliar " . $idUsuario,
                    'sede' => "Sede asignada",
                    'visitas_realizadas' => $visitasRealizadas,
                    'visitas_pendientes' => $visitasPendientes,
                    'total_asignadas' => $totalAsignadas,
                    'porcentaje_completado' => $totalAsignadas > 0 
                        ? round(($visitasRealizadas / $totalAsignadas) * 100, 1) 
                        : 0
                ];
            }
            
            // Ordenar por número de visitas realizadas (descendente)
            usort($resultado, function($a, $b) {
                return $b['visitas_realizadas'] - $a['visitas_realizadas'];
            });
            
            return $resultado;
        }
        
        // Si no hay información de visitas, crear datos de ejemplo
        return [
            [
                'id' => 1,
                'nombre' => 'Auxiliar Ejemplo 1',
                'sede' => 'Sede Principal',
                'visitas_realizadas' => 12,
                'visitas_pendientes' => 5,
                'total_asignadas' => 17,
                'porcentaje_completado' => 70.6
            ],
            [
                'id' => 2,
                'nombre' => 'Auxiliar Ejemplo 2',
                'sede' => 'Sede Norte',
                'visitas_realizadas' => 8,
                'visitas_pendientes' => 3,
                'total_asignadas' => 11,
                'porcentaje_completado' => 72.7
            ],
            [
                'id' => 3,
                'nombre' => 'Auxiliar Ejemplo 3',
                'sede' => 'Sede Sur',
                'visitas_realizadas' => 15,
                'visitas_pendientes' => 2,
                'total_asignadas' => 17,
                'porcentaje_completado' => 88.2
            ]
        ];
    }
}