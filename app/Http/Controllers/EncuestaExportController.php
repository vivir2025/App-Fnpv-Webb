<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ApiService;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class EncuestaExportController extends Controller
{
    protected $apiService;
    
    // Mapeo de identificadores de preguntas a sus nombres reales
    protected $preguntasCalificacionMap = [
        'pregunta_0' => 'El trato que recibió fue',
        'pregunta_1' => 'Cómo definiría el tiempo en espera para la atención',
        'pregunta_2' => 'Durante la consulta, el profesional le permitió expresar sus dudas o inquietudes con respecto a su enfermedad, a los exámenes y al tratamiento',
        'pregunta_3' => 'Oportunidad en la asignación de citas',
        'pregunta_4' => 'Cómo es el trato por parte del personal de la IPS',
        'pregunta_5' => 'Cómo califica la limpieza y aseo de las instalaciones',
        'pregunta_6' => 'Fue claro el personal administrativo en la información brindada',
        'pregunta_7' => 'Cómo calificaría su experiencia global respecto a los servicios de salud en la Fundación Nacer para Vivir'
    ];
    
    protected $preguntasAdicionalesMap = [
        'pregunta_0' => 'Entendió la información recibida con respecto al tratamiento',
        'pregunta_1' => 'Cuál de estas características encontró en el profesional que lo atendió',
        'pregunta_2' => 'Recomendaría a sus familiares y amigos esta IPS',
        'pregunta_3' => 'Durante la atención se ha sentido usted discriminado'
    ];

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

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
                // Log::info('Sedes obtenidas para filtro de encuestas: ' . count($todasLasSedes));
            } else {
                // Log::warning('No se pudieron obtener las sedes para el filtro de encuestas');
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
            
            return view('encuestas.export', compact('sedes', 'permisos', 'usuario'));
        } catch (\Exception $e) {
            // Log::error('Error al obtener sedes para encuestas: ' . $e->getMessage());
            return view('encuestas.export', ['sedes' => [], 'permisos' => [], 'usuario' => []]);
        }
    }
        
    public function exportExcel(Request $request)
    {
        // Validar fechas y sede
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'sede_id' => 'nullable|string',
        ], [
            'fecha_inicio.required' => 'La fecha inicial es obligatoria',
            'fecha_fin.required' => 'La fecha final es obligatoria',
            'fecha_fin.after_or_equal' => 'La fecha final debe ser posterior o igual a la fecha inicial',
            'sede_id.string' => 'La sede seleccionada no es válida',
        ]);
        
        try {
            // Validar permisos del usuario
            $usuario = session('usuario');
            $rol = strtolower($usuario['rol'] ?? '');
            $sedeUsuario = $usuario['idsede'] ?? null;
            
            // Obtener filtros del request
            $fechaInicio = $request->input('fecha_inicio');
            $fechaFin = $request->input('fecha_fin');
            $sedeId = $request->input('sede_id');
            
            // Aplicar restricciones según el rol
            if (in_array($rol, ['jefe', 'coordinador'])) {
                // Jefe solo puede exportar su sede
                $sedeId = $sedeUsuario;
            } elseif (!in_array($rol, ['admin', 'administrador'])) {
                return back()->withErrors(['error' => 'No tiene permisos para exportar datos']);
            }
            
            // Registrar en log para depuración
            Log::info('Exportando encuestas con filtros:', [
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'sede_id' => $sedeId,
                'rol' => $rol
            ]);
            
            // Preparar parámetros para la API
            $params = [
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
            ];
            
            // Añadir el filtro de sede si se seleccionó una
            if (!empty($sedeId)) {
                $params['sede_id'] = $sedeId;
            }
            
            // Consumir la API para obtener las encuestas usando el ApiService
            $response = $this->apiService->get('encuestas', $params, 60);
                
            if (!$response->successful()) {
                return back()->withErrors(['api_error' => 'Error al obtener datos de la API']);
            }
            
            $todasEncuestas = $response->json()['data'] ?? [];
            
            // Filtrar manualmente por sede si es necesario (en caso de que la API no filtre)
            if (!empty($sedeId) && $sedeId !== 'todas') {
                $encuestas = array_filter($todasEncuestas, function($encuesta) use ($sedeId) {
                    // Verificar diferentes estructuras posibles para la sede
                    if (isset($encuesta['sede']['id'])) {
                        return $encuesta['sede']['id'] == $sedeId;
                    } elseif (isset($encuesta['sede']['idsede'])) {
                        return $encuesta['sede']['idsede'] == $sedeId;
                    } elseif (isset($encuesta['sede_id'])) {
                        return $encuesta['sede_id'] == $sedeId;
                    } elseif (isset($encuesta['idsede'])) {
                        return $encuesta['idsede'] == $sedeId;
                    }
                    return false;
                });
                $encuestas = array_values($encuestas); // Reindexar
            } else {
                $encuestas = $todasEncuestas;
            }
            
            // Log::info('Encuestas después de filtrar:', [
            //     'total_api' => $totalEncuestas,
            //     'total_filtrado' => count($encuestas),
            //     'sede_filtro' => $sedeId
            // ]);
            
            if (empty($encuestas)) {
                return back()->with('warning', 'No se encontraron encuestas para los filtros seleccionados.');
            }
            
            // Crear el archivo Excel
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Encuestas');
            
            // Definir encabezados
            $headers = [
                'ID Encuesta',
                'Fecha',
                'Documento Paciente',
                'Nombre Paciente',
                'Sede',
                'Domicilio',
                'Entidad Afiliada',
                'Usuario Registro',
                'Sugerencias'
            ];
            
            // Obtener las preguntas de calificación y adicionales
            $preguntasCalificacion = [];
            $preguntasAdicionales = [];
            
            if (count($encuestas) > 0) {
                $primeraEncuesta = $encuestas[0];
                
                // Obtener las preguntas de calificación
                $respuestasCalificacion = json_decode($primeraEncuesta['respuestas_calificacion'] ?? '{}', true);
                if (is_array($respuestasCalificacion)) {
                    foreach ($respuestasCalificacion as $pregunta => $respuesta) {
                        $preguntasCalificacion[] = $pregunta;
                        // Usar el nombre real de la pregunta si existe en el mapeo, si no usar el identificador
                        $nombrePregunta = $this->preguntasCalificacionMap[$pregunta] ?? "Calificación: $pregunta";
                        $headers[] = $nombrePregunta;
                    }
                }
                
                // Obtener las preguntas adicionales
                $respuestasAdicionales = json_decode($primeraEncuesta['respuestas_adicionales'] ?? '{}', true);
                if (is_array($respuestasAdicionales)) {
                    foreach ($respuestasAdicionales as $pregunta => $respuesta) {
                        $preguntasAdicionales[] = $pregunta;
                        // Usar el nombre real de la pregunta si existe en el mapeo, si no usar el identificador
                        $nombrePregunta = $this->preguntasAdicionalesMap[$pregunta] ?? "Pregunta: $pregunta";
                        $headers[] = $nombrePregunta;
                    }
                }
            }
            
            // Estilo para encabezados
            $headerStyle = [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                ],
            ];
            
            // Escribir encabezados
            $column = 1;
            foreach ($headers as $header) {
                $sheet->setCellValueByColumnAndRow($column, 1, $header);
                $column++;
            }
            
            // Aplicar estilo a encabezados
            $sheet->getStyle('A1:' . $this->getColumnLetter(count($headers)) . '1')->applyFromArray($headerStyle);
            
            // Escribir datos
            $row = 2;
            foreach ($encuestas as $encuesta) {
                $column = 1;
                
                // Datos básicos
                $sheet->setCellValueByColumnAndRow($column++, $row, $encuesta['id'] ?? '');
                $sheet->setCellValueByColumnAndRow($column++, $row, isset($encuesta['fecha']) ? Carbon::parse($encuesta['fecha'])->format('d/m/Y') : '');
                $sheet->setCellValueByColumnAndRow($column++, $row, $encuesta['paciente']['identificacion'] ?? '');
                $sheet->setCellValueByColumnAndRow($column++, $row, isset($encuesta['paciente']) ? ($encuesta['paciente']['nombre'] ?? '') . ' ' . ($encuesta['paciente']['apellido'] ?? '') : '');
                $sheet->setCellValueByColumnAndRow($column++, $row, $encuesta['sede']['nombresede'] ?? '');
                $sheet->setCellValueByColumnAndRow($column++, $row, $encuesta['domicilio'] ?? '');
                $sheet->setCellValueByColumnAndRow($column++, $row, $encuesta['entidad_afiliada'] ?? '');
                $sheet->setCellValueByColumnAndRow($column++, $row, $encuesta['usuario']['nombre'] ?? '');
                $sheet->setCellValueByColumnAndRow($column++, $row, $encuesta['sugerencias'] ?? '');
                
                // Respuestas de calificación
                $respuestasCalificacion = json_decode($encuesta['respuestas_calificacion'] ?? '{}', true);
                if (is_array($respuestasCalificacion)) {
                    foreach ($preguntasCalificacion as $pregunta) {
                        $respuesta = isset($respuestasCalificacion[$pregunta]) ? $respuestasCalificacion[$pregunta] : '';
                        $sheet->setCellValueByColumnAndRow($column++, $row, $respuesta);
                    }
                } else {
                    // Si no hay respuestas, avanzar las columnas
                    $column += count($preguntasCalificacion);
                }
                
                // Respuestas adicionales
                $respuestasAdicionales = json_decode($encuesta['respuestas_adicionales'] ?? '{}', true);
                if (is_array($respuestasAdicionales)) {
                    foreach ($preguntasAdicionales as $pregunta) {
                        $respuesta = isset($respuestasAdicionales[$pregunta]) ? $respuestasAdicionales[$pregunta] : '';
                        $sheet->setCellValueByColumnAndRow($column++, $row, $respuesta);
                    }
                }
                
                $row++;
            }
            
            // Ajustar ancho de columnas automáticamente
            foreach (range('A', $this->getColumnLetter(count($headers))) as $columnID) {
                $sheet->getColumnDimension($columnID)->setAutoSize(true);
            }
            
            // Crear el archivo
            $writer = new Xlsx($spreadsheet);
            $fileName = 'Encuestas_' . Carbon::now()->format('Y-m-d_His') . '.xlsx';
            
            // Configurar headers para la descarga
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $fileName . '"');
            header('Cache-Control: max-age=0');
            
            // Guardar el archivo al output
            $writer->save('php://output');
            exit;
        } catch (\Exception $e) {
            // Log::error('Error al exportar encuestas: ' . $e->getMessage());
            // Log::error('Stack trace: ' . $e->getTraceAsString());
            return back()->withErrors(['error' => 'Error al generar el archivo Excel: ' . $e->getMessage()]);
        }
    }
    
    // Función auxiliar para obtener la letra de la columna
    private function getColumnLetter($columnNumber)
    {
        $dividend = $columnNumber;
        $columnLetter = '';
        
        while ($dividend > 0) {
            $modulo = ($dividend - 1) % 26;
            $columnLetter = chr(65 + $modulo) . $columnLetter;
            $dividend = floor(($dividend - $modulo) / 26);
        }
        
        return $columnLetter;
    }
}