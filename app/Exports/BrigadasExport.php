<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class BrigadasExport implements FromCollection, WithHeadings, WithMapping, WithStyles
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
            'Lugar del Evento',
            'Fecha de Brigada',
            'Identificación Paciente',
            'Nombre Paciente',
            'Nombre Medicamento',
            'Cantidad',
            'Dosis',
            'Indicaciones'
        ];
    }

    public function map($item): array
    {
        $brigada = $item['brigada'];
        $paciente = $item['paciente'];
        $medicamento = $item['medicamento'];
        $relacion = $item['relacion'];

        // Formatear nombre completo del paciente
        $nombrePaciente = '';
        if ($paciente) {
            if (isset($paciente['nombre']) && isset($paciente['apellido'])) {
                $nombrePaciente = $paciente['nombre'] . ' ' . $paciente['apellido'];
            } elseif (isset($paciente['nombre_apellido'])) {
                $nombrePaciente = $paciente['nombre_apellido'];
            }
        }

        // Obtener nombre del medicamento según la estructura
        $nombreMedicamento = '';
        if ($medicamento) {
            if (isset($medicamento['nombmedicamento'])) {
                $nombreMedicamento = $medicamento['nombmedicamento'];
            } elseif (isset($medicamento['nombre'])) {
                $nombreMedicamento = $medicamento['nombre'];
            }
        }

        return [
            $brigada['lugar_evento'] ?? '',
            isset($brigada['fecha_brigada']) ? Carbon::parse($brigada['fecha_brigada'])->format('d/m/Y') : '',
            $paciente ? ($paciente['identificacion'] ?? '') : '',
            $nombrePaciente,
            $nombreMedicamento,
            $relacion ? ($relacion['cantidad'] ?? '') : '',
            $relacion ? ($relacion['dosis'] ?? '') : '',
            $relacion ? ($relacion['indicaciones'] ?? '') : ''
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
            'A' => ['width' => 25],
            'B' => ['width' => 15],
            'C' => ['width' => 20],
            'D' => ['width' => 30],
            'E' => ['width' => 30],
            'F' => ['width' => 10],
            'G' => ['width' => 20],
            'H' => ['width' => 40],
        ];
    }
}
