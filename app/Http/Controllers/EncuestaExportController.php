<?php
// app/Http/Controllers/EncuestaExportController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ApiService;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Carbon\Carbon;

class EncuestaExportController extends Controller
{
    protected $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    public function exportForm()
    {
        return view('encuestas.export');
    }

    public function exportExcel(Request $request)
    {
        // Validar fechas
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
        ], [
            'fecha_inicio.required' => 'La fecha inicial es obligatoria',
            'fecha_fin.required' => 'La fecha final es obligatoria',
            'fecha_fin.after_or_equal' => 'La fecha final debe ser posterior o igual a la fecha inicial',
        ]);

        // Obtener filtros del request
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');
        
        // Consumir la API para obtener las encuestas usando el ApiService
        $response = $this->apiService->get('encuestas', [
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
        ]);
            
        if (!$response['success']) {
            return back()->withErrors(['api_error' => 'Error al obtener datos de la API: ' . ($response['message'] ?? 'Error desconocido')]);
        }
        
        $encuestas = $response['data'] ?? [];
        
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
                    $headers[] = "Calificación: $pregunta";
                }
            }
            
            // Obtener las preguntas adicionales
            $respuestasAdicionales = json_decode($primeraEncuesta['respuestas_adicionales'] ?? '{}', true);
            if (is_array($respuestasAdicionales)) {
                foreach ($respuestasAdicionales as $pregunta => $respuesta) {
                    $preguntasAdicionales[] = $pregunta;
                    $headers[] = "Pregunta: $pregunta";
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
            $sheet->setCellValueByColumnAndRow($column++, $row, $encuesta['paciente']['documento'] ?? '');
            $sheet->setCellValueByColumnAndRow($column++, $row, isset($encuesta['paciente']) ? ($encuesta['paciente']['nombre'] ?? '') . ' ' . ($encuesta['paciente']['apellido'] ?? '') : '');
            $sheet->setCellValueByColumnAndRow($column++, $row, $encuesta['sede']['nombre'] ?? '');
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
