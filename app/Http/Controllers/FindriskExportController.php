<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
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
        // Obtener sedes desde la API
        $response = $this->apiService->get('sedes');
        $sedes = $response->successful() ? $response->json() : [];
        
        return view('findrisk.export', compact('sedes'));
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

        // Obtener datos desde la API
        $response = $this->apiService->get('findrisk-tests/export', $params);
        
        if (!$response->successful()) {
            return back()->withErrors(['error' => 'No se pudieron obtener los datos de la API']);
        }

        $testsData = $response->json();

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
