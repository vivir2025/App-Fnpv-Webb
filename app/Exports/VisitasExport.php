<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class VisitasExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $visitas;

    public function __construct($visitas)
    {
        $this->visitas = $visitas;
    }

    public function collection()
    {
        return collect($this->visitas);
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nombre y Apellido',
            'Identificación',
            'Teléfono',
            'Fecha de Visita',
            'Zona',
            'Familiar',
            'HTA',
            'DM',
            'Peso (kg)',
            'Talla (cm)',
            'IMC',
            'Perímetro Abdominal (cm)',
            'Frecuencia Cardíaca',
            'Frecuencia Respiratoria',
            'Tensión Arterial',
            'Temperatura (°C)',
            'Glucometría',
            'Abandono Social',
            'Motivo',
            'Factores',
            'Conductas',
            'Novedades',
            'Próximo Control',
            'Medicamentos'
        ];
    }

    public function map($visita): array
    {
        // Procesar medicamentos
        $medicamentos = '';
        if (isset($visita['medicamentos']) && is_array($visita['medicamentos'])) {
            foreach ($visita['medicamentos'] as $medicamento) {
                $nombre = is_array($medicamento) && isset($medicamento['nombmedicamento']) 
                    ? $medicamento['nombmedicamento'] 
                    : 'Medicamento sin nombre';
                
                $indicaciones = '';
                if (is_array($medicamento) && isset($medicamento['pivot']) && 
                    is_array($medicamento['pivot']) && isset($medicamento['pivot']['indicaciones'])) {
                    $indicaciones = $medicamento['pivot']['indicaciones'];
                }
                
                $medicamentos .= $nombre . ($indicaciones ? ': ' . $indicaciones : '') . "\n";
            }
        }

        return [
            $visita['id'] ?? '',
            $visita['nombre_apellido'] ?? '',
            $visita['identificacion'] ?? '',
            $visita['telefono'] ?? '',
            isset($visita['fecha']) ? Carbon::parse($visita['fecha'])->format('d/m/Y') : '',
            $visita['zona'] ?? '',
            $visita['familiar'] ?? '',
            $visita['hta'] ?? '',
            $visita['dm'] ?? '',
            $visita['peso'] ?? '',
            $visita['talla'] ?? '',
            $visita['imc'] ?? '',
            $visita['perimetro_abdominal'] ?? '',
            $visita['frecuencia_cardiaca'] ?? '',
            $visita['frecuencia_respiratoria'] ?? '',
            $visita['tension_arterial'] ?? '',
            $visita['temperatura'] ?? '',
            $visita['glucometria'] ?? '',
            $visita['abandono_social'] ?? '',
            $visita['motivo'] ?? '',
            $visita['factores'] ?? '',
            $visita['conductas'] ?? '',
            $visita['novedades'] ?? '',
            isset($visita['proximo_control']) ? Carbon::parse($visita['proximo_control'])->format('d/m/Y') : '',
            $medicamentos
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
