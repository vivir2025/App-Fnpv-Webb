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
                $allVisitas = $this->apiService->get('visitas?include=usuario.sede,medicamentos');
                
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
            // Consumir la API para obtener detalles de una visita con relaciones
            $response = $this->apiService->get('visitas/' . $id . '?include=usuario.sede,medicamentos');
            
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
            // Consumir la API para obtener detalles de una visita con relaciones
            $response = $this->apiService->get('visitas/' . $id . '?include=usuario.sede,medicamentos');
            
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
        try {
            // Obtener datos de la visita desde la API
            $response = $this->apiService->get('visitas/' . $id . '?include=usuario.sede,medicamentos');
            
            if ($response->successful()) {
                $visita = $response->json()['data'] ?? null;
                
                if ($visita) {
                    // Genera el PDF
                    $pdf = PDF::loadView('pdf_visita', ['visita' => $visita]);
                    
                    // Configura el tamaño del papel
                    $pdf->setPaper('a4', 'portrait');
                    
                    // Descarga el PDF con un nombre específico
                    return $pdf->download('visita_domiciliaria_' . $visita['id'] . '.pdf');
                }
            }
            
            return redirect()->route('visitas.buscar')
                ->withErrors(['error' => 'No se encontró la visita solicitada para generar PDF.']);
                
        } catch (\Exception $e) {
            Log::error('Error al generar PDF: ' . $e->getMessage());
            return redirect()->route('visitas.buscar')
                ->withErrors(['error' => 'Error al generar el archivo PDF.']);
        }
    }

    /**
     * ✅ Mostrar formulario de exportación con filtro de sedes
     */
    public function exportForm()
    {
        try {
            // Obtener sedes para el filtro
            $sedesResponse = $this->apiService->get('sedes');
            $sedes = [];
            
            if ($sedesResponse->successful()) {
                $sedes = $sedesResponse->json();
                Log::info('Sedes obtenidas para filtro: ' . count($sedes));
            } else {
                Log::warning('No se pudieron obtener las sedes para el filtro');
            }
            
            return view('visitas.export', compact('sedes'));
        } catch (\Exception $e) {
            Log::error('Error al obtener sedes: ' . $e->getMessage());
            return view('visitas.export', ['sedes' => []]);
        }
    }

    /**
     * ✅ Exportar visitas a Excel con filtros de fecha y sede
     */
    public function exportExcel(Request $request)
    {
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'sede_id' => 'nullable|string', // ✅ Validación para sede
        ]);

        try {
            $fechaInicio = Carbon::parse($request->fecha_inicio)->format('Y-m-d');
            $fechaFin = Carbon::parse($request->fecha_fin)->format('Y-m-d');
            $sedeId = $request->sede_id;

            // Registrar en log para depuración
            Log::info('Exportando visitas con filtros:', [
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'sede_id' => $sedeId
            ]);

            // ✅ Consumir la API para obtener visitas con relaciones
            $response = $this->apiService->get('visitas?include=usuario.sede,medicamentos');
            
            if ($response->successful()) {
                $allVisitas = $response->json();
                
                // Filtrar por rango de fechas y sede
                $visitas = collect($allVisitas)->filter(function ($visita) use ($fechaInicio, $fechaFin, $sedeId) {
                    // Filtro por fecha
                    if (!isset($visita['fecha'])) {
                        return false;
                    }
                    $fechaVisita = Carbon::parse($visita['fecha'])->format('Y-m-d');
                    $fechaValida = $fechaVisita >= $fechaInicio && $fechaVisita <= $fechaFin;
                    
                    // ✅ Filtro por sede (si se especifica)
                    if ($sedeId && $sedeId !== 'todas') {
                        $sedeValida = false;
                        
                        // Verificar diferentes posibles estructuras de datos para la sede
                        if (isset($visita['usuario']['sede']['id'])) {
                            $sedeValida = $visita['usuario']['sede']['id'] == $sedeId;
                        } elseif (isset($visita['usuario']['sede']['idsede'])) {
                            $sedeValida = $visita['usuario']['sede']['idsede'] == $sedeId;
                        } elseif (isset($visita['usuario']['idsede'])) {
                            $sedeValida = $visita['usuario']['idsede'] == $sedeId;
                        } elseif (isset($visita['sede_id'])) {
                            $sedeValida = $visita['sede_id'] == $sedeId;
                        }
                        
                        return $fechaValida && $sedeValida;
                    }
                    
                    return $fechaValida;
                })->values()->all();
                
                // Registrar en log para depuración
                Log::info('Visitas filtradas: ' . count($visitas));
                
                if (count($visitas) > 0) {
                    // ✅ Generar nombre de archivo con información de sede
                    $nombreArchivo = 'visitas_domiciliarias_' . $fechaInicio . '_' . $fechaFin;
                    if ($sedeId && $sedeId !== 'todas') {
                        $nombreSede = $this->obtenerNombreSede($sedeId);
                        $nombreArchivo .= '_' . str_replace(' ', '_', $nombreSede);
                    }
                    $nombreArchivo .= '.xlsx';
                    
                    return Excel::download(new VisitasExport($visitas), $nombreArchivo);
                }
                
                return back()->withErrors(['error' => 'No se encontraron visitas en el rango de fechas y sede seleccionados.']);
            }
            
            return back()->withErrors(['error' => 'Error al obtener datos de visitas.']);
            
        } catch (\Exception $e) {
            Log::error('Error al exportar visitas: ' . $e->getMessage());
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
                return $sede['nombresede'] ?? 'sede_' . $sedeId;
            }
        } catch (\Exception $e) {
            Log::error('Error al obtener nombre de sede: ' . $e->getMessage());
        }
        
        return 'sede_' . $sedeId;
    }
}
