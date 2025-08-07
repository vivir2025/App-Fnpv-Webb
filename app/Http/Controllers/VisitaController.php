<?php

namespace App\Http\Controllers;

use App\Services\ApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Exports\VisitasExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class VisitaController extends Controller
{
    protected $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    public function buscarForm()
    {
        return view('visitas.buscar');
    }

    public function buscar(Request $request)
    {
        $request->validate([
            'identificacion' => 'required|string'
        ]);

        try {
            // Primero intentamos usar un endpoint específico si existe
            $response = $this->apiService->get('visitas/buscar/' . $request->identificacion);
            
            // Si no existe, obtenemos todas las visitas y filtramos
            if (!$response->successful()) {
                $allVisitas = $this->apiService->get('visitas');
                
                if ($allVisitas->successful()) {
                    $data = $allVisitas->json();
                    $visitas = collect($data)->filter(function ($visita) use ($request) {
                        return $visita['identificacion'] == $request->identificacion;
                    })->sortByDesc('fecha')->values()->all();
                    
                    return view('visitas.resultados', compact('visitas'));
                }
            } else {
                $visitas = $response->json()['data'] ?? [];
                return view('visitas.resultados', compact('visitas'));
            }
            
            // Si llegamos aquí, hubo un problema
            return back()->withErrors(['error' => 'No se encontraron visitas con esa identificación.']);
            
        } catch (\Exception $e) {
            Log::error('Error al buscar visitas: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Error al buscar visitas. Intente nuevamente.']);
        }
    }

    public function show($id)
    {
        try {
            // Consumir la API para obtener detalles de una visita
            $response = $this->apiService->get('visitas/' . $id);
            
            if ($response->successful()) {
                $visita = $response->json()['data'] ?? null;
                
                if ($visita) {
                    return view('visitas.detalle', compact('visita'));
                }
            }
            
            return redirect()->route('visitas.buscar')
                ->withErrors(['error' => 'No se encontró la visita solicitada.']);
        } catch (\Exception $e) {
            Log::error('Error al obtener visita: ' . $e->getMessage());
            return redirect()->route('visitas.buscar')
                ->withErrors(['error' => 'Error al obtener detalles de la visita.']);
        }
    }

    public function printView($id)
    {
        try {
            // Consumir la API para obtener detalles de una visita
            $response = $this->apiService->get('visitas/' . $id);
            
            if ($response->successful()) {
                $visita = $response->json()['data'] ?? null;
                
                if ($visita) {
                    return view('pdf_visita', ['visita' => $visita]);
                }
            }
            
            return redirect()->route('visitas.buscar')
                ->withErrors(['error' => 'No se encontró la visita solicitada para imprimir.']);
        } catch (\Exception $e) {
            Log::error('Error al obtener visita para impresión: ' . $e->getMessage());
            return redirect()->route('visitas.buscar')
                ->withErrors(['error' => 'Error al preparar la vista de impresión.']);
        }
    }

   public function generarPDF($id)
    {
        // Obtén los datos de la visita
        $visita = Visita::with('medicamentos')->findOrFail($id);
        
        // Convierte el modelo a un array para mantener consistencia con tu vista actual
        $visitaArray = $visita->toArray();
        
        // Genera el PDF
        $pdf = PDF::loadView('pdf_visita', ['visita' => $visitaArray]);
        
        // Opcional: configura el tamaño del papel
        $pdf->setPaper('a4', 'portrait');
        
        // Descarga el PDF con un nombre específico
        return $pdf->download('visita_domiciliaria_' . $visita->id . '.pdf');
        
        // Alternativa: muestra el PDF en el navegador
        // return $pdf->stream('visita_domiciliaria_' . $visita->id . '.pdf');
    }

    public function exportForm()
    {
        return view('visitas.export');
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

            // Consumir la API para obtener todas las visitas
            $response = $this->apiService->get('visitas');
            
            if ($response->successful()) {
                $allVisitas = $response->json();
                
                // Filtrar por rango de fechas
                $visitas = collect($allVisitas)->filter(function ($visita) use ($fechaInicio, $fechaFin) {
                    if (!isset($visita['fecha'])) {
                        return false;
                    }
                    $fechaVisita = Carbon::parse($visita['fecha'])->format('Y-m-d');
                    return $fechaVisita >= $fechaInicio && $fechaVisita <= $fechaFin;
                })->values()->all();
                
                if (count($visitas) > 0) {
                    $nombreArchivo = 'visitas_domiciliarias_' . $fechaInicio . '_' . $fechaFin . '.xlsx';
                    return Excel::download(new VisitasExport($visitas), $nombreArchivo);
                }
                
                return back()->withErrors(['error' => 'No se encontraron visitas en el rango de fechas seleccionado.']);
            }
            
            return back()->withErrors(['error' => 'Error al obtener datos de visitas.']);
            
        } catch (\Exception $e) {
            Log::error('Error al exportar visitas: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Error al generar el archivo Excel: ' . $e->getMessage()]);
        }
    }
}