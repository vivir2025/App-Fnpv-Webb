<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AfinamientosExport implements FromCollection, WithHeadings, WithMapping, WithStyles
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
            'Presión Arterial Tamizaje',
            'Fecha 1er Afinamiento',
            'Presión Sistólica 1',
            'Presión Diastólica 1',
            'Fecha 2do Afinamiento',
            'Presión Sistólica 2',
            'Presión Diastólica 2',
            'Fecha 3er Afinamiento',
            'Presión Sistólica 3',
            'Presión Diastólica 3',
            'Promedio Sistólica',
            'Promedio Diastólica',
            'Conducta',
            'Promotor de Vida'
        ];
    }

    public function map($afinamiento): array
    {
        return [
            isset($afinamiento['fecha_tamizaje']) ? Carbon::parse($afinamiento['fecha_tamizaje'])->format('d/m/Y') : '',
            $afinamiento['procedencia'] ?? '',
            $afinamiento['identificacion_paciente'] ?? '',
            $afinamiento['nombre_paciente'] ?? '',
            $afinamiento['edad_paciente'] ?? '',
            $afinamiento['presion_arterial_tamiz'] ?? '',
            isset($afinamiento['primer_afinamiento_fecha']) ? Carbon::parse($afinamiento['primer_afinamiento_fecha'])->format('d/m/Y') : '',
            $afinamiento['presion_sistolica_1'] ?? '',
            $afinamiento['presion_diastolica_1'] ?? '',
            isset($afinamiento['segundo_afinamiento_fecha']) ? Carbon::parse($afinamiento['segundo_afinamiento_fecha'])->format('d/m/Y') : '',
            $afinamiento['presion_sistolica_2'] ?? '',
            $afinamiento['presion_diastolica_2'] ?? '',
            isset($afinamiento['tercer_afinamiento_fecha']) ? Carbon::parse($afinamiento['tercer_afinamiento_fecha'])->format('d/m/Y') : '',
            $afinamiento['presion_sistolica_3'] ?? '',
            $afinamiento['presion_diastolica_3'] ?? '',
            $afinamiento['presion_sistolica_promedio'] ?? '',
            $afinamiento['presion_diastolica_promedio'] ?? '',
            $afinamiento['conducta'] ?? '',
            $afinamiento['promotor_vida'] ?? ''
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
            'F' => ['width' => 20],
            'G' => ['width' => 15],
            'H' => ['width' => 15],
            'I' => ['width' => 15],
            'J' => ['width' => 15],
            'K' => ['width' => 15],
            'L' => ['width' => 15],
            'M' => ['width' => 15],
            'N' => ['width' => 15],
            'O' => ['width' => 15],
            'P' => ['width' => 15],
            'Q' => ['width' => 15],
            'R' => ['width' => 30],
            'S' => ['width' => 25],
        ];
    }
}