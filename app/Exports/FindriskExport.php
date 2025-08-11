<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class FindriskExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return new Collection($this->data);
    }

    public function headings(): array
    {
        return [
            'Fecha',
            'Identificación',
            'Paciente',
            'Edad',
            'Sede',
            'IMC',
            'Perímetro Abdominal',
            'Actividad Física',
            'Frutas/Verduras',
            'Medicamentos HTA',
            'Azúcar Alto',
            'Antecedentes',
            'Puntaje Final',
            'Nivel de Riesgo',
            'Porcentaje de Riesgo',
            'Promotor de Vida'
        ];
    }

    public function map($row): array
    {
        return [
            Carbon::parse($row['created_at'])->format('d/m/Y'),
            $row['paciente']['identificacion'],
            $row['paciente']['nombre'] . ' ' . $row['paciente']['apellido'],
            $row['edad_calculada'],
            $row['sede']['nombresede'],
            $row['imc'],
            $row['perimetro_abdominal'],
            $row['actividad_fisica'] == 'si' ? 'Sí' : 'No',
            $row['frecuencia_frutas_verduras'] == 'diariamente' ? 'Diariamente' : 'No diariamente',
            $row['medicamentos_hipertension'] == 'si' ? 'Sí' : 'No',
            $row['azucar_alto_detectado'] == 'si' ? 'Sí' : 'No',
            $this->formatearAntecedentes($row['antecedentes_familiares']),
            $row['puntaje_final'],
            $row['interpretacion']['nivel'],
            $row['interpretacion']['riesgo'],
            $row['promotor_vida'] ?? ''
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    private function formatearAntecedentes($antecedentes)
    {
        switch ($antecedentes) {
            case 'no':
                return 'No';
            case 'abuelos_tios_primos':
                return 'Abuelos, tíos, primos';
            case 'padres_hermanos_hijos':
                return 'Padres, hermanos, hijos';
            default:
                return $antecedentes;
        }
    }
}
