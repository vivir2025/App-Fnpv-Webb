<?php

namespace App\Http\Controllers;

use App\Services\ApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cookie;
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
            // Log::error('Error al buscar visitas: ' . $e->getMessage());
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
            // Obtener información del usuario
            $usuario = session('usuario');
            $rol = strtolower($usuario['rol'] ?? '');
            $sedeUsuario = $usuario['idsede'] ?? null;
            
            // Obtener sedes para el filtro
            $sedesResponse = $this->apiService->get('sedes');
            $todasLasSedes = [];
            
            if ($sedesResponse->successful()) {
                $todasLasSedes = $sedesResponse->json();
                // Log::info('Sedes obtenidas para filtro: ' . count($todasLasSedes));
            } else {
                // Log::warning('No se pudieron obtener las sedes para el filtro');
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
            
            return view('visitas.export', compact('sedes', 'permisos', 'usuario'));
        } catch (\Exception $e) {
            Log::error('Error al obtener sedes: ' . $e->getMessage());
            return view('visitas.export', ['sedes' => [], 'permisos' => [], 'usuario' => []]);
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
                // Otros roles no tienen acceso (aunque el middleware debería evitar esto)
                return back()->withErrors(['error' => 'No tiene permisos para exportar datos']);
            }

            // Registrar en log para depuración
            // Log::info('Exportando visitas con filtros:', [
            //     'fecha_inicio' => $fechaInicio,
            //     'fecha_fin' => $fechaFin,
            //     'sede_id' => $sedeId
            // ]);

            // ✅ Construir URL con filtros para optimizar la petición
            $queryParams = [
                'include' => 'usuario.sede,medicamentos',
                'fecha_desde' => $fechaInicio,
                'fecha_hasta' => $fechaFin
            ];
            
            // Agregar filtro de sede si está especificado
            if ($sedeId && $sedeId !== 'todas') {
                $queryParams['sede_id'] = $sedeId;
            }
            
            $queryString = http_build_query($queryParams);
            
            // Intentar obtener visitas con filtros de la API
            $response = $this->apiService->get('visitas?' . $queryString, [], 60); // Timeout de 60 segundos
            
            if ($response->successful()) {
                $allVisitas = $response->json();
                
                // Si la API no soporta filtros, filtrar manualmente
                if (is_array($allVisitas) && count($allVisitas) > 0) {
                    // Verificar si necesitamos filtrar manualmente
                    $primeraVisita = $allVisitas[0];
                    $necesitaFiltrado = false;
                    
                    // Si la primera visita está fuera del rango, la API no filtró
                    if (isset($primeraVisita['fecha'])) {
                        $fechaPrimeraVisita = Carbon::parse($primeraVisita['fecha'])->format('Y-m-d');
                        if ($fechaPrimeraVisita < $fechaInicio || $fechaPrimeraVisita > $fechaFin) {
                            $necesitaFiltrado = true;
                        }
                    }
                    
                    if ($necesitaFiltrado) {
                        // Log::info('API no filtró, aplicando filtros manualmente');
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
                    } else {
                        $visitas = $allVisitas;
                    }
                } else {
                    $visitas = [];
                }
                
                // Registrar en log para depuración
                // Log::info('Visitas filtradas: ' . count($visitas));
                
                if (count($visitas) > 0) {
                    // ✅ Generar nombre de archivo con información de sede
                    $nombreArchivo = 'visitas_domiciliarias_' . $fechaInicio . '_' . $fechaFin;
                    if ($sedeId && $sedeId !== 'todas') {
                        $nombreSede = $this->obtenerNombreSede($sedeId);
                        $nombreArchivo .= '_' . str_replace(' ', '_', $nombreSede);
                    }
                    $nombreArchivo .= '.xlsx';
                    
                    // ✅ Descargar archivo y establecer cookie para indicar que la descarga comenzó
                    Cookie::queue('download_started', '1', 1);
                    return Excel::download(new VisitasExport($visitas), $nombreArchivo);
                } else {
                    // Log::warning('No se encontraron visitas con los filtros especificados');
                    return back()->with('warning', 'No se encontraron visitas en el rango de fechas especificado. Por favor, intente con otro rango de fechas.');
                }
            } else {
                // Log::error('La API no respondió exitosamente: ' . $response->status());
                return back()->withErrors(['error' => 'Error al obtener datos de la API. Por favor, intente nuevamente.']);
            }
            
        } catch (\Exception $e) {
            // Log::error('Error al exportar visitas: ' . $e->getMessage());
            
            // Mensaje más específico para timeout
            if (str_contains($e->getMessage(), 'timeout') || str_contains($e->getMessage(), 'timed out')) {
                return back()->withErrors(['error' => 'La solicitud tardó demasiado tiempo. Por favor, intente con un rango de fechas más pequeño (por ejemplo, 1 mes en lugar de varios meses).']);
            }
            
            return back()->withErrors(['error' => 'Error al generar el archivo Excel. Por favor, intente nuevamente con un rango de fechas más pequeño.']);
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
            // Log::error('Error al obtener nombre de sede: ' . $e->getMessage());
        }
        
        return 'sede_' . $sedeId;
    }
}
