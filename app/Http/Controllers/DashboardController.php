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
        // Obtener usuarios (auxiliares)
        $response = $this->apiService->get('usuarios');
        $usuarios = $response->successful() ? $response->json() : [];
        
        // Verificar que usuarios sea un array
        if (!is_array($usuarios)) {
            Log::error('La respuesta de usuarios no es un array');
            return [];
        }
        
        // Filtrar solo auxiliares si hay un campo de rol
        $auxiliares = collect($usuarios);
        if (count($auxiliares) > 0 && isset($auxiliares->first()['rol'])) {
            $auxiliares = $auxiliares->filter(function($usuario) {
                return isset($usuario['rol']) && $usuario['rol'] === 'administrador';
            });
        }
        
        // Filtrar por sede si es necesario
        if ($sedeId !== 'todas') {
            $auxiliares = $auxiliares->filter(function($usuario) use ($sedeId) {
                return isset($usuario['idsede']) && $usuario['idsede'] == $sedeId;
            });
        }
        
        // Contar visitas por usuario
        $visitasPorUsuario = [];
        foreach ($visitas as $visita) {
            $idUsuario = isset($visita['idusuario']) ? $visita['idusuario'] : null;
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
            $idUsuario = isset($auxiliar['id']) ? $auxiliar['id'] : 0;
            $visitasRealizadas = isset($visitasPorUsuario[$idUsuario]) ? $visitasPorUsuario[$idUsuario] : 0;
            $visitasPendientes = 5; // Valor fijo para ejemplo, ajustar según lógica real
            $totalAsignadas = $visitasRealizadas + $visitasPendientes;
            
            $nombreSede = "Sin sede";
            if (isset($auxiliar['sede']) && is_array($auxiliar['sede']) && isset($auxiliar['sede']['nombresede'])) {
                $nombreSede = $auxiliar['sede']['nombresede'];
            } elseif (isset($auxiliar['idsede'])) {
                // Buscar el nombre de la sede en el mapa de sedes
                $response = $this->apiService->get('sedes/' . $auxiliar['idsede']);
                if ($response->successful()) {
                    $sede = $response->json();
                    if (isset($sede['nombresede'])) {
                        $nombreSede = $sede['nombresede'];
                    }
                }
            }
            
            $resultado[] = [
                'nombre' => isset($auxiliar['nombre']) ? $auxiliar['nombre'] : "Usuario $idUsuario",
                'sede' => $nombreSede,
                'visitas_realizadas' => $visitasRealizadas,
                'visitas_pendientes' => $visitasPendientes,
                'total_asignadas' => $totalAsignadas
            ];
        }
        
        return $resultado;
    }
}