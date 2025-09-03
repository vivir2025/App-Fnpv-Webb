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
use App\Services\ApiService;

class BrigadasExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $datos;
    protected $sedeCache = [];
    protected $apiService;

    public function __construct($datos, ApiService $apiService = null)
    {
        $this->datos = collect($datos);
        $this->apiService = $apiService ?? app(ApiService::class);
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
            'Sede', // ✅ Simplificado el nombre de la columna
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

        // ✅ Obtener solo el nombre de la sede
        $nombreSede = '';
        $sedeId = null;
        
        if (isset($brigada['usuario']) && is_array($brigada['usuario'])) {
            // Obtener sede del usuario - verificar diferentes estructuras posibles
            if (isset($brigada['usuario']['sede']) && is_array($brigada['usuario']['sede'])) {
                $nombreSede = $brigada['usuario']['sede']['nombresede'] ?? '';
                
                // Si no se encuentra con nombresede, intentar con nombre
                if (empty($nombreSede) && isset($brigada['usuario']['sede']['nombre'])) {
                    $nombreSede = $brigada['usuario']['sede']['nombre'];
            }
                
                // Guardar el ID de la sede si está disponible
                $sedeId = $brigada['usuario']['sede']['id'] ?? $brigada['usuario']['sede']['idsede'] ?? null;
        }

            // Si aún no tenemos sede, intentar otras posibles ubicaciones
            if (empty($nombreSede)) {
                if (isset($brigada['usuario']['nombresede'])) {
                    $nombreSede = $brigada['usuario']['nombresede'];
                } elseif (isset($brigada['sede']['nombresede'])) {
                    $nombreSede = $brigada['sede']['nombresede'];
                } elseif (isset($brigada['sede']['nombre'])) {
                    $nombreSede = $brigada['sede']['nombre'];
                } elseif (isset($brigada['idsede'])) {
                    $sedeId = $brigada['idsede'];
                } elseif (isset($brigada['sede_id'])) {
                    $sedeId = $brigada['sede_id'];
                }
            }
        }
        
        // Si no encontramos la sede en el usuario, intentar buscarla directamente en la brigada
        if (empty($nombreSede) && isset($brigada['sede']) && is_array($brigada['sede'])) {
            $nombreSede = $brigada['sede']['nombresede'] ?? $brigada['sede']['nombre'] ?? '';
            if (empty($sedeId)) {
                $sedeId = $brigada['sede']['id'] ?? $brigada['sede']['idsede'] ?? null;
        }
        }
        
        // ✅ MODIFICADO: Si aún no tenemos sede, intentar buscarla en el paciente y obtener el nombre real
        if (empty($nombreSede) && $paciente) {
            if (isset($paciente['idsede'])) {
                $sedeId = $paciente['idsede'];
            } elseif (isset($paciente['sede_id'])) {
                $sedeId = $paciente['sede_id'];
        }
        }

        // Si tenemos un ID de sede pero no el nombre, intentar obtener el nombre real de la sede
        if (empty($nombreSede) && $sedeId) {
            $nombreSede = $this->obtenerNombreSede($sedeId);
        }
        
        // Si aún no tenemos sede, usar un valor por defecto
        if (empty($nombreSede)) {
            $nombreSede = 'No especificada';
        }

        return [
            $brigada['lugar_evento'] ?? '',
            isset($brigada['fecha_brigada']) ? Carbon::parse($brigada['fecha_brigada'])->format('d/m/Y') : '',
            $nombreSede, // ✅ Solo el nombre de la sede
            $paciente ? ($paciente['identificacion'] ?? '') : '',
            $nombrePaciente,
            $nombreMedicamento,
            $relacion ? ($relacion['cantidad'] ?? '') : '',
            $relacion ? ($relacion['dosis'] ?? '') : '',
            $relacion ? ($relacion['indicaciones'] ?? '') : ''
        ];
    }

    /**
     * Obtener el nombre real de la sede a partir de su ID
     */
    private function obtenerNombreSede($sedeId)
    {
        // Usar caché para evitar consultas repetidas
        if (isset($this->sedeCache[$sedeId])) {
            return $this->sedeCache[$sedeId];
        }
        
        try {
            // Intentar obtener el nombre de la sede desde la API
            $response = $this->apiService->get('sedes/' . $sedeId);
            if ($response->successful()) {
                $sede = $response->json();
                $nombreSede = $sede['nombresede'] ?? $sede['nombre'] ?? 'Sede ' . substr($sedeId, 0, 8);
                $this->sedeCache[$sedeId] = $nombreSede;
                return $nombreSede;
    }
        } catch (\Exception $e) {
            Log::error('Error al obtener nombre de sede: ' . $e->getMessage());
        }

        // Si no se puede obtener el nombre, devolver un valor genérico
        $nombreGenerico = 'Sede ' . substr($sedeId, 0, 8);
        $this->sedeCache[$sedeId] = $nombreGenerico;
        return $nombreGenerico;
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
            'C' => ['width' => 20], // Sede
            'D' => ['width' => 20], // Identificación
            'E' => ['width' => 30], // Nombre Paciente
            'F' => ['width' => 30], // Medicamento
            'G' => ['width' => 10], // Cantidad
            'H' => ['width' => 20], // Dosis
            'I' => ['width' => 40], // Indicaciones
        ];
}
}
