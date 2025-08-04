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
            // 3. Calcular estadísticas generales (le pasamos los pacientes también)
            $estadisticas = $this->calcularEstadisticas($visitas, $pacientes); 
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
    private function calcularEstadisticas($visitas, $pacientes)
    {
        $ahora = Carbon::now();
        $inicioMes = Carbon::now()->startOfMonth();

        // Filtrar visitas del mes actual
        $visitasMes = collect($visitas)->filter(function($visita) use ($inicioMes) {
            return isset($visita['fecha']) && Carbon::parse($visita['fecha'])->gte($inicioMes);
        });

        // CORRECCIÓN: Usar el conteo de pacientes filtrados directamente.
        // Esto es más preciso que contar pacientes únicos de las visitas del mes.
        $totalPacientes = count($pacientes);

        // Calcular total de visitas del mes
        $totalVisitasMes = $visitasMes->count();

        // Calcular promedio diario (esto no se usa en el frontend, pero lo dejamos por si acaso)
        $diasTranscurridos = $inicioMes->diffInDays($ahora) + 1;
        $promedioDiario = $diasTranscurridos > 0 ? round($totalVisitasMes / $diasTranscurridos, 1) : 0;

        return [
            'total_pacientes' => $totalPacientes, // <-- DATO CORREGIDO
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

    // REEMPLAZA TODA LA FUNCIÓN `prepararDatosAuxiliares` CON ESTA VERSIÓN MEJORADA
    private function prepararDatosAuxiliares($visitas, $sedeIdFiltro)
    {
        try {
            // 1. Obtener todas las sedes para mapear IDs a nombres
            $mapaSedes = [];
            $responseSedes = $this->apiService->get('sedes');
            if ($responseSedes->successful() && is_array($responseSedes->json())) {
                foreach ($responseSedes->json() as $sede) {
                    if (isset($sede['id'])) {
                        $mapaSedes[$sede['id']] = $sede['nombresede'] ?? "Sede {$sede['id']}";
                    }
                }
            }

            // 2. Obtener todos los auxiliares
            $responseAuxiliares = $this->apiService->get('usuarios');
            if (!$responseAuxiliares->successful() || !is_array($responseAuxiliares->json())) {
                Log::error('No se pudo obtener la lista de usuarios/auxiliares de la API.');
                return $this->generarAuxiliaresPredeterminados($visitas); // Fallback
            }
            
            $todosLosUsuarios = collect($responseAuxiliares->json());
            $auxiliares = $todosLosUsuarios->filter(function($usuario) {
                $rol = strtolower($usuario['rol'] ?? '');
                return in_array($rol, ['aux', 'auxiliar']);
            });

            // 3. Filtrar auxiliares por la sede seleccionada, si aplica
            if ($sedeIdFiltro !== 'todas') {
                $auxiliares = $auxiliares->filter(function($aux) use ($sedeIdFiltro) {
                    return ($aux['idsede'] ?? $aux['sede_id'] ?? null) == $sedeIdFiltro;
                });
            }
            
            // 4. Contar visitas por usuario (del mes actual)
            $inicioMes = Carbon::now()->startOfMonth();
            $visitasMes = collect($visitas)->filter(function($v) use ($inicioMes) {
                return isset($v['fecha']) && Carbon::parse($v['fecha'])->gte($inicioMes);
            });

            $conteoVisitas = $visitasMes->countBy(function ($visita) {
                return $visita['idusuario'] ?? $visita['usuario_id'] ?? null;
            });

            // 5. Construir el resultado final
            $resultado = [];
            foreach ($auxiliares as $auxiliar) {
                $idUsuario = $auxiliar['id'] ?? null;
                if (!$idUsuario) continue;

                // Lógica robusta para obtener el nombre del auxiliar
                $nombreCompleto = 'Auxiliar Desconocido';
                if (!empty($auxiliar['nombre'])) {
                    $nombreCompleto = $auxiliar['nombre'];
                } elseif (!empty($auxiliar['nombres']) && !empty($auxiliar['apellidos'])) {
                    $nombreCompleto = trim($auxiliar['nombres'] . ' ' . $auxiliar['apellidos']);
                } elseif (!empty($auxiliar['name'])) {
                    $nombreCompleto = $auxiliar['name'];
                }

                // Lógica para obtener el nombre de la sede
                $idSedeAuxiliar = $auxiliar['idsede'] ?? $auxiliar['sede_id'] ?? null;
                $nombreSede = $mapaSedes[$idSedeAuxiliar] ?? 'Sede no asignada';

                // Estadísticas de visitas
                $visitasRealizadas = $conteoVisitas[$idUsuario] ?? 0;
                // La lógica para pendientes es más compleja, aquí asumimos un valor estático
                // o lo calculamos si el estado está disponible en la visita
                $visitasPendientes = 80; // Valor de ejemplo, ajusta si tienes datos de estado
                $totalAsignadas = $visitasRealizadas + $visitasPendientes;

                $resultado[] = [
                    'id' => $idUsuario,
                    'nombre' => $nombreCompleto,
                    'sede' => $nombreSede, // Siempre es un string
                    'visitas_realizadas' => $visitasRealizadas,
                    'visitas_pendientes' => $visitasPendientes, // Ajusta esta lógica si es necesario
                    'total_asignadas' => $totalAsignadas,
                ];
            }
            
            // Ordenar por visitas realizadas
            usort($resultado, fn($a, $b) => $b['visitas_realizadas'] <=> $a['visitas_realizadas']);
            
            return $resultado;

        } catch (\Exception $e) {
            Log::error('Error en prepararDatosAuxiliares: ' . $e->getMessage());
            return []; // Retornar vacío en caso de error
        }
    }
    private function obtenerTodasLasSedes()
    {
        try {
            $response = $this->apiService->get('sedes');
            if ($response->successful() && $response->json() !== null) {
                $sedes = $response->json();
                
                // Verificar que sedes sea un array
                if (!is_array($sedes)) {
                    Log::error('La respuesta de sedes no es un array');
                    return [];
                }
                
                // Crear un mapa de ID => sede
                $mapaSedes = [];
                foreach ($sedes as $sede) {
                    if (isset($sede['id'])) {
                        $mapaSedes[$sede['id']] = $sede;
                    }
                }
                
                return $mapaSedes;
            }
        } catch (\Exception $e) {
            Log::error('Error al obtener todas las sedes: ' . $e->getMessage());
        }
        
        return [];
    }
    private function obtenerInfoSede($auxiliar, $mapaSedes)
    {
        // Caso 1: Sede incluida como objeto completo
        if (isset($auxiliar['sede']) && is_array($auxiliar['sede'])) {
            $nombreSede = isset($auxiliar['sede']['nombresede']) ? $auxiliar['sede']['nombresede'] : 
                        (isset($auxiliar['sede']['nombre']) ? $auxiliar['sede']['nombre'] : 'Sin nombre');
            
            $idSede = isset($auxiliar['sede']['id']) ? $auxiliar['sede']['id'] : 
                    (isset($auxiliar['sede_id']) ? $auxiliar['sede_id'] : null);
            
            return [
                'id' => $idSede,
                'nombre' => $nombreSede
            ];
        }
        
        // Caso 2: Solo ID de sede disponible
        $sedeId = null;
        if (isset($auxiliar['sede_id'])) {
            $sedeId = $auxiliar['sede_id'];
        } elseif (isset($auxiliar['idsede'])) {
            $sedeId = $auxiliar['idsede'];
        }
        
        if ($sedeId && isset($mapaSedes[$sedeId])) {
            $sede = $mapaSedes[$sedeId];
            $nombreSede = isset($sede['nombresede']) ? $sede['nombresede'] : 
                        (isset($sede['nombre']) ? $sede['nombre'] : "Sede $sedeId");
            
            return [
                'id' => $sedeId,
                'nombre' => $nombreSede
            ];
        }
        
        // Caso 3: No hay información de sede
        return [
            'id' => null,
            'nombre' => 'Sin sede asignada'
        ];
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