<?php

namespace App\Http\Controllers;

use App\Services\ApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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

    public function exportForm()
    {
        return view('brigadas.export');
    }

    public function exportExcel(Request $request)
    {
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
        ]);

        try {
            $fechaInicio = Carbon::parse($request->fecha_inicio)->format('Y-m-d');
            $fechaFin = Carbon::parse($request->fecha_fin)->format('Y-m-d');

            // Consumir la API para obtener todas las brigadas
            $response = $this->apiService->get('brigadas');
            
            if ($response->successful()) {
                $allBrigadas = $response->json()['data'] ?? [];
                
                // Filtrar por rango de fechas
                $brigadas = collect($allBrigadas)->filter(function ($brigada) use ($fechaInicio, $fechaFin) {
                    if (!isset($brigada['fecha_brigada'])) {
                        return false;
                    }
                    $fechaBrigada = Carbon::parse($brigada['fecha_brigada'])->format('Y-m-d');
                    return $fechaBrigada >= $fechaInicio && $fechaBrigada <= $fechaFin;
                })->values()->all();
                
                if (count($brigadas) > 0) {
                    // Cargar detalles completos de cada brigada
                    $brigadasCompletas = [];
                    foreach ($brigadas as $brigada) {
                        $detailResponse = $this->apiService->get('brigadas/' . $brigada['id']);
                        if ($detailResponse->successful()) {
                            $brigadasCompletas[] = $detailResponse->json()['data'];
                        }
                    }
                    
                    $nombreArchivo = 'brigadas_medicamentos_' . $fechaInicio . '_' . $fechaFin . '.xlsx';
                    return Excel::download(new BrigadasExport($brigadasCompletas), $nombreArchivo);
                }
                
                return back()->withErrors(['error' => 'No se encontraron brigadas en el rango de fechas seleccionado.']);
            }
            
            return back()->withErrors(['error' => 'Error al obtener datos de brigadas.']);
            
        } catch (\Exception $e) {
            Log::error('Error al exportar brigadas: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Error al generar el archivo Excel: ' . $e->getMessage()]);
        }
    }
}
