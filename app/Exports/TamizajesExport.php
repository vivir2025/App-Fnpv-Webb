<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class TamizajesExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $datos;

    public function __construct($datos)
    {
        $this->datos = collect($datos);
    }

    public function collection()
    {
        return $this->datos;
    }

    public function headings(): array
    {
        return [
            'Fecha Tamizaje',
            'Procedencia',
            'Identificación Paciente',
            'Nombre Paciente',
            'Edad',
            'Sexo',
            'Sede',
            'Vereda Residencia',
            'Teléfono',
            'Brazo de Toma',
            'Posición de la Persona',
            'Reposo 5 Minutos',
            'Presión Arterial',
            'Presión Sistólica',
            'Presión Diastólica',
            'Conducta',
            'Promotor de Vida'
        ];
    }

    public function map($tamizaje): array
    {
        return [
            isset($tamizaje['fecha_primera_toma']) ? Carbon::parse($tamizaje['fecha_primera_toma'])->format('d/m/Y') : '',
            $tamizaje['vereda_residencia'] ?? '',
            $tamizaje['identificacion_paciente'] ?? '',
            $tamizaje['nombre_paciente'] ?? '',
            $tamizaje['edad_paciente'] ?? '',
            $tamizaje['sexo_paciente'] ?? '',
            $tamizaje['sede_paciente'] ?? '',
            $tamizaje['vereda_residencia'] ?? '',
            $tamizaje['telefono'] ?? '',
            $tamizaje['brazo_toma'] ?? '',
            $tamizaje['posicion_persona'] ?? '',
            $tamizaje['reposo_cinco_minutos'] ?? '',
            $tamizaje['presion_arterial'] ?? '',
            $tamizaje['pa_sistolica'] ?? '',
            $tamizaje['pa_diastolica'] ?? '',
            $tamizaje['conducta'] ?? '',
            $tamizaje['promotor_vida'] ?? ''
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4']
                ],
                'font' => [
                    'color' => ['rgb' => 'FFFFFF']
                ]
            ],
            'A' => ['width' => 15],
            'B' => ['width' => 20],
            'C' => ['width' => 20],
            'D' => ['width' => 30],
            'E' => ['width' => 10],
            'F' => ['width' => 10],
            'G' => ['width' => 20],
            'H' => ['width' => 20],
            'I' => ['width' => 15],
            'J' => ['width' => 15],
            'K' => ['width' => 20],
            'L' => ['width' => 15],
            'M' => ['width' => 15],
            'N' => ['width' => 15],
            'O' => ['width' => 15],
            'P' => ['width' => 30],
            'Q' => ['width' => 25],
        ];
    }
}
