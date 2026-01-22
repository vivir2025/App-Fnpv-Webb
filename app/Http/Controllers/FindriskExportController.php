<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cookie;
use Carbon\Carbon;
use PDF;
use Excel;
use App\Exports\FindriskExport;
use App\Services\ApiService;

class FindriskExportController extends Controller
{
    protected $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    public function index()
    {
        // Obtener información del usuario
        $usuario = session('usuario');
        $rol = strtolower($usuario['rol'] ?? '');
        $sedeUsuario = $usuario['idsede'] ?? null;
        
        // Obtener sedes desde la API
        $response = $this->apiService->get('sedes');
        $todasLasSedes = $response->successful() ? $response->json() : [];
        
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
        
        return view('findrisk.export', compact('sedes', 'permisos', 'usuario'));
    }

    public function exportar(Request $request)
    {
        $request->validate([
            'formato' => 'required|in:excel,pdf',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'sede_id' => 'nullable|exists:sedes,id',
            'nivel_riesgo' => 'nullable|in:todos,bajo,ligeramente_elevado,moderado,alto,muy_alto'
        ]);
        
        // Validar permisos del usuario
        $usuario = session('usuario');
        $rol = strtolower($usuario['rol'] ?? '');
        $sedeUsuario = $usuario['idsede'] ?? null;
        
        $sedeId = $request->sede_id;
        
        // Aplicar restricciones según el rol
        if (in_array($rol, ['jefe', 'coordinador'])) {
            // Jefe solo puede exportar su sede
            $sedeId = $sedeUsuario;
        } elseif (!in_array($rol, ['admin', 'administrador'])) {
            return back()->withErrors(['error' => 'No tiene permisos para exportar datos']);
        }

        $fechaInicio = Carbon::parse($request->fecha_inicio)->startOfDay()->format('Y-m-d');
        $fechaFin = Carbon::parse($request->fecha_fin)->endOfDay()->format('Y-m-d');

        // Construir parámetros para la API
        $params = [
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin
        ];

        if ($request->sede_id) {
            $params['sede_id'] = $request->sede_id;
        }

        if ($request->nivel_riesgo && $request->nivel_riesgo !== 'todos') {
            $params['nivel_riesgo'] = $request->nivel_riesgo;
        }

        // Obtener datos desde la API con timeout aumentado
        $response = $this->apiService->get('findrisk-tests/export', $params, 60);
        
        if (!$response->successful()) {
            return back()->withErrors(['error' => 'No se pudieron obtener los datos de la API']);
        }

        $testsData = $response->json();
        
        // Aplicar filtrado manual si la API no filtró correctamente
        if ($sedeId && !empty($testsData)) {
            $totalAntesFiltro = count($testsData);
            $testsData = array_filter($testsData, function($test) use ($sedeId) {
                // Verificar múltiples posibles estructuras de datos
                $testSedeId = null;
                
                if (isset($test['paciente']['idsede'])) {
                    $testSedeId = $test['paciente']['idsede'];
                } elseif (isset($test['paciente']['sede_id'])) {
                    $testSedeId = $test['paciente']['sede_id'];
                } elseif (isset($test['sede']['id'])) {
                    $testSedeId = $test['sede']['id'];
                } elseif (isset($test['sede']['idsede'])) {
                    $testSedeId = $test['sede']['idsede'];
                } elseif (isset($test['sede_id'])) {
                    $testSedeId = $test['sede_id'];
                } elseif (isset($test['idsede'])) {
                    $testSedeId = $test['idsede'];
                }
                
                return $testSedeId == $sedeId;
            });
            
            // \Log::info('FINDRISK Export - Filtrado manual:', [
            //     'total_api' => $totalAntesFiltro,
            //     'total_filtrado' => count($testsData),
            //     'sede_filtro' => $sedeId
            // ]);
        }
        
        // Verificar si hay datos después del filtro
        if (empty($testsData)) {
            return back()->with('warning', 'No se encontraron tests FINDRISK en el rango de fechas y sede seleccionados.');
        }

        // Obtener nombre de la sede si se especificó
        $nombreSede = 'Todas las sedes';
        if ($request->sede_id) {
            $sedeResponse = $this->apiService->get('sedes/' . $request->sede_id);
            if ($sedeResponse->successful()) {
                $sede = $sedeResponse->json();
                $nombreSede = $sede['nombresede'] ?? 'Sede desconocida';
            }
        }

        // Nombre del archivo
        $fileName = 'findrisk_tests_' . Carbon::now()->format('Y-m-d_H-i-s');

        // Exportar según el formato seleccionado
        if ($request->formato === 'excel') {
            Cookie::queue('download_started', '1', 1);
            return Excel::download(new FindriskExport($testsData), $fileName . '.xlsx');
        } else {
            // PDF
            $pdf = PDF::loadView('findrisk.pdf_export', [
                'tests' => $testsData,
                'fechaInicio' => Carbon::parse($fechaInicio)->format('d/m/Y'),
                'fechaFin' => Carbon::parse($fechaFin)->format('d/m/Y'),
                'sede' => $nombreSede,
                'nivel_riesgo' => $this->getNombreNivelRiesgo($request->nivel_riesgo)
            ]);
            
            Cookie::queue('download_started', '1', 1);
            return $pdf->download($fileName . '.pdf');
        }
    }

    private function getNombreNivelRiesgo($nivel)
    {
        switch ($nivel) {
            case 'bajo':
                return 'Bajo';
            case 'ligeramente_elevado':
                return 'Ligeramente elevado';
            case 'moderado':
                return 'Moderado';
            case 'alto':
                return 'Alto';
            case 'muy_alto':
                return 'Muy alto';
            default:
                return 'Todos los niveles';
        }
    }
}
