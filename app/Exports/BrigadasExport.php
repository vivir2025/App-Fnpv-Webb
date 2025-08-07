<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class BrigadasExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $brigadas;

    public function __construct($brigadas)
    {
        $this->brigadas = $brigadas;
    }

    public function collection()
    {
        return collect($this->brigadas);
    }

    public function headings(): array
    {
        return [
            'ID',
            'Lugar del Evento',
            'Fecha de Brigada',
            'Nombre del Conductor',
            'Usuarios HTA',
            'Usuarios DN',
            'Usuarios HTA RCU',
            'Usuarios DM RCU',
            'Tema',
            'Observaciones',
            'Total Pacientes',
            'Detalle de Medicamentos'
        ];
    }

    public function map($brigada): array
    {
        // Procesar medicamentos agrupados por paciente
        $detalleMedicamentos = '';
        
        if (isset($brigada['medicamentosPacientes']) && is_array($brigada['medicamentosPacientes'])) {
            // Agrupar medicamentos por paciente
            $medicamentosPorPaciente = collect($brigada['medicamentosPacientes'])
                ->groupBy('paciente_id');
            
            foreach ($medicamentosPorPaciente as $pacienteId => $medicamentos) {
                // Obtener información del paciente
                $paciente = null;
                if (!empty($medicamentos) && isset($medicamentos[0]['paciente'])) {
                    $paciente = $medicamentos[0]['paciente'];
                }
                
                if ($paciente) {
                    $detalleMedicamentos .= "PACIENTE: " . ($paciente['nombre_apellido'] ?? 'Sin nombre') . 
                                           " (ID: " . ($paciente['identificacion'] ?? 'N/A') . ")\n";
                    
                    // Listar medicamentos de este paciente
                    foreach ($medicamentos as $med) {
                        if (isset($med['medicamento'])) {
                            $nombreMed = $med['medicamento']['nombre'] ?? 'Medicamento sin nombre';
                            $dosis = $med['dosis'] ?? 'No especificada';
                            $cantidad = $med['cantidad'] ?? 0;
                            $indicaciones = $med['indicaciones'] ?? '';
                            
                            $detalleMedicamentos .= "- " . $nombreMed . 
                                                  " | Dosis: " . $dosis . 
                                                  " | Cantidad: " . $cantidad;
                                                  
                            if (!empty($indicaciones)) {
                                $detalleMedicamentos .= " | Indicaciones: " . $indicaciones;
                            }
                            
                            $detalleMedicamentos .= "\n";
                        }
                    }
                    
                    $detalleMedicamentos .= "------------------------\n";
                }
            }
        }

        // Contar pacientes
        $totalPacientes = isset($brigada['pacientes']) ? count($brigada['pacientes']) : 0;

        return [
            $brigada['id'] ?? '',
            $brigada['lugar_evento'] ?? '',
            isset($brigada['fecha_brigada']) ? Carbon::parse($brigada['fecha_brigada'])->format('d/m/Y') : '',
            $brigada['nombre_conductor'] ?? '',
            $brigada['usuarios_hta'] ?? '',
            $brigada['usuarios_dn'] ?? '',
            $brigada['usuarios_hta_rcu'] ?? '',
            $brigada['usuarios_dm_rcu'] ?? '',
            $brigada['tema'] ?? '',
            $brigada['observaciones'] ?? '',
            $totalPacientes,
            $detalleMedicamentos
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
