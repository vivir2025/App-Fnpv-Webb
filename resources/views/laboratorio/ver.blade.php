@extends('layouts.app')

@section('title', 'Detalle de Envío de Muestras')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h4>
                    <i class="fas fa-vial text-success me-2"></i>
                    Detalle de Envío de Muestras
                </h4>
                <div>
                    <a href="{{ route('laboratorio.sede', $envio['idsede'] ?? $envio['sede']['id'] ?? '') }}" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-arrow-left me-1"></i> Volver
                    </a>
                    <a href="{{ route('laboratorio.detallePdf', $envio['id']) }}" class="btn btn-danger" target="_blank">
                        <i class="fas fa-file-pdf me-1"></i> Descargar PDF
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Información general del envío -->
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Información General</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <p class="text-muted mb-1">Código:</p>
                            <p class="fw-bold">{{ $envio['codigo'] ?? 'N/A' }} {{ isset($envio['version']) ? 'v'.$envio['version'] : '' }}</p>
                        </div>
                        <div class="col-md-3 mb-3">
                            <p class="text-muted mb-1">Fecha:</p>
                            <p class="fw-bold">{{ isset($envio['fecha']) ? \Carbon\Carbon::parse($envio['fecha'])->format('d/m/Y') : 'N/A' }}</p>
                        </div>
                        <div class="col-md-3 mb-3">
                            <p class="text-muted mb-1">Sede:</p>
                            <p class="fw-bold">
                                @if(isset($envio['sede']['nombre']))
                                    {{ $envio['sede']['nombre'] }}
                                @elseif(isset($envio['sede']['nombresede']))
                                    {{ $envio['sede']['nombresede'] }}
                                @elseif(isset($envio['nombresede']))
                                    {{ $envio['nombresede'] }}
                                @else
                                    N/A
                                @endif
                            </p>
                        </div>
                        <div class="col-md-3 mb-3">
                            <p class="text-muted mb-1">Lugar de toma:</p>
                            <p class="fw-bold">{{ $envio['lugar_toma_muestras'] ?? 'No especificado' }}</p>
                        </div>
                    </div>
                    
                    <!-- Información del usuario creador -->
                    @if(isset($envio['usuarioCreador']))
                    <div class="row">
                        <div class="col-md-12">
                            <div class="alert alert-info">
                                <i class="fas fa-user me-2"></i>
                                <strong>Creado por:</strong> {{ $envio['usuarioCreador']['nombre'] ?? 'Usuario desconocido' }} 
                                (ID: {{ $envio['usuarioCreador']['id'] ?? 'N/A' }})
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Información de salida y llegada -->
    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Información de Salida</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <p class="text-muted mb-1">Fecha de salida:</p>
                            <p class="fw-bold">{{ $envio['fecha_salida'] ? \Carbon\Carbon::parse($envio['fecha_salida'])->format('d/m/Y') : 'No registrada' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <p class="text-muted mb-1">Hora de salida:</p>
                            <p class="fw-bold">{{ $envio['hora_salida'] ? \Carbon\Carbon::parse($envio['hora_salida'])->format('H:i') : 'No registrada' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <p class="text-muted mb-1">Temperatura de salida:</p>
                            <p class="fw-bold">{{ $envio['temperatura_salida'] ? $envio['temperatura_salida'] . ' °C' : 'No registrada' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <p class="text-muted mb-1">Responsable de toma:</p>
                            <p class="fw-bold">
                                @if(!empty($envio['responsable_toma_nombre']))
                                    {{ $envio['responsable_toma_nombre'] }}
                                @elseif(!empty($envio['usuario_creador_nombre']))
                                    {{ $envio['usuario_creador_nombre'] }}
                                @else
                                    No asignado
                                @endif

                            </p>
                        </div>
                        <div class="col-md-12 mb-3">
                            <p class="text-muted mb-1">Responsable de transporte:</p>
                            <p class="fw-bold">{{ $envio['responsable_transporte_id'] ?? 'No asignado' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Información de Llegada</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <p class="text-muted mb-1">Fecha de llegada:</p>
                            <p class="fw-bold">{{ $envio['fecha_llegada'] ? \Carbon\Carbon::parse($envio['fecha_llegada'])->format('d/m/Y') : 'No registrada' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <p class="text-muted mb-1">Hora de llegada:</p>
                            <p class="fw-bold">{{ $envio['hora_llegada'] ? \Carbon\Carbon::parse($envio['hora_llegada'])->format('H:i') : 'No registrada' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <p class="text-muted mb-1">Temperatura de llegada:</p>
                            <p class="fw-bold">{{ $envio['temperatura_llegada'] ? $envio['temperatura_llegada'] . ' °C' : 'No registrada' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <p class="text-muted mb-1">Lugar de llegada:</p>
                            <p class="fw-bold">{{ $envio['lugar_llegada'] ?? 'No especificado' }}</p>
                        </div>
                        <div class="col-md-12 mb-3">
                            <p class="text-muted mb-1">Responsable de recepción:</p>
                            <p class="fw-bold">{{ $envio['responsable_recepcion'] ?? 'No asignado' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Detalles de Pacientes y Muestras</h5>
            </div>
            <div class="card-body">
                @if(isset($envio['detalles']) && count($envio['detalles']) > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm" style="font-size: 0.7rem;">
                            <thead>
                                <tr>
                                    <th rowspan="3" class="text-center align-middle bg-light" style="min-width: 30px;">N</th>
                                    <th rowspan="3" class="text-center align-middle bg-light" style="min-width: 150px;">NOMBRES Y APELLIDOS</th>
                                    <th rowspan="3" class="text-center align-middle bg-light" style="min-width: 100px;">DOCUMENTO</th>
                                    <th rowspan="3" class="text-center align-middle bg-light" style="min-width: 100px;">PROCEDENCIA</th>
                                    <th rowspan="3" class="text-center align-middle bg-light" style="min-width: 100px;">FECHA DE NACIMIENTO</th>
                                    <th colspan="2" class="text-center" style="background-color: #FFB6C1;">DIAGNÓSTICO</th>
                                    <th colspan="5" class="text-center" style="background-color: #ADD8E6;">MUESTRAS ENVIADAS</th>
                                    <th colspan="3" class="text-center" style="background-color: #DDA0DD;">TUBO LILA</th>
                                    <th colspan="5" class="text-center" style="background-color: #FFFF99;">TUBO AMARILLO</th>
                                    <th colspan="8" class="text-center" style="background-color: #90EE90;">MUESTRA DE ORINA</th>
                                    <th colspan="10" class="text-center" style="background-color: #FFFF99;">PACIENTES NEFRO</th>
                                </tr>
                                <tr>
                                    <th rowspan="2" class="text-center align-middle" style="background-color: #FFE4E1;">DM</th>
                                    <th rowspan="2" class="text-center align-middle" style="background-color: #FFE4E1;">HTA</th>
                                    <th rowspan="2" class="text-center align-middle" style="background-color: #F5F5DC;">A</th>
                                    <th rowspan="2" class="text-center align-middle" style="background-color: #F0E6FF;">M</th>
                                    <th rowspan="2" class="text-center align-middle" style="background-color: #F0FFF0;">OE</th>
                                    <th rowspan="2" class="text-center align-middle" style="background-color: #F0FFF0;">024H</th>
                                    <th rowspan="2" class="text-center align-middle" style="background-color: #FFE4E1;">PO</th>
                                    <th rowspan="2" class="text-center align-middle" style="background-color: #F0E6FF;">H3</th>
                                    <th rowspan="2" class="text-center align-middle" style="background-color: #F0E6FF;">HBA1C</th>
                                    <th rowspan="2" class="text-center align-middle" style="background-color: #F0E6FF;">PTH</th>
                                    <th rowspan="2" class="text-center align-middle" style="background-color: #FFFACD;">GLU</th>
                                    <th rowspan="2" class="text-center align-middle" style="background-color: #FFFACD;">CREA</th>
                                    <th rowspan="2" class="text-center align-middle" style="background-color: #FFFACD;">PL</th>
                                    <th rowspan="2" class="text-center align-middle" style="background-color: #FFFACD;">AU</th>
                                    <th rowspan="2" class="text-center align-middle" style="background-color: #FFFACD;">BUN</th>
                                    <th colspan="2" class="text-center" style="background-color: #F0FFF0;">ORINA ESP</th>
                                    <th colspan="6" class="text-center" style="background-color: #F0FFF0;">ORINA24H</th>
                                    <th colspan="8" class="text-center" style="background-color: #FFFACD;">TUBO AMARILLO</th>
                                    <th colspan="2" class="text-center" style="background-color: #FFE4B5;">FORRADOS</th>
                                </tr>
                                <tr>
                                    <th class="text-center" style="background-color: #F0FFF0;">RELACION CREA/ALB</th>
                                    <th class="text-center" style="background-color: #F0FFF0;">PL</th>
                                    <th class="text-center" style="background-color: #F0FFF0;">DCRE24H</th>
                                    <th class="text-center" style="background-color: #F0FFF0;">ALB24H</th>
                                    <th class="text-center" style="background-color: #F0FFF0;">BUNO24H</th>
                                    <th class="text-center" style="background-color: #F0FFF0;">PESO</th>
                                    <th class="text-center" style="background-color: #F0FFF0;">TALLA</th>
                                    <th class="text-center" style="background-color: #F0FFF0;">VOLUMEN</th>
                                    <th class="text-center" style="background-color: #FFFACD;">FER</th>
                                    <th class="text-center" style="background-color: #FFFACD;">TRA</th>
                                    <th class="text-center" style="background-color: #FFFACD;">FOSFAT</th>
                                    <th class="text-center" style="background-color: #FFFACD;">ALB</th>
                                    <th class="text-center" style="background-color: #FFFACD;">FE</th>
                                    <th class="text-center" style="background-color: #FFFACD;">TSH</th>
                                    <th class="text-center" style="background-color: #FFFACD;">P</th>
                                    <th class="text-center" style="background-color: #FFFACD;">IONOGRAMA</th>
                                    <th class="text-center" style="background-color: #FFE4B5;">B12</th>
                                    <th class="text-center" style="background-color: #FFE4B5;">ACIDO FOLICO</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($envio['detalles'] as $index => $detalle)
                                    <tr>
                                        <!-- N° -->
                                        <td class="text-center align-middle">{{ $index + 1 }}</td>
                                        
                                        <!-- Nombres y Apellidos -->
                                        <td class="align-middle">
                                            {{ $detalle['paciente']['nombre'] ?? '' }} 
                                            {{ $detalle['paciente']['apellido'] ?? '' }}
                                        </td>
                                        
                                        <!-- Documento -->
                                        <td class="text-center align-middle">{{ $detalle['paciente']['identificacion'] ?? 'N/A' }}</td>
                                        
                                        <!-- Procedencia -->
                                        <td class="text-center align-middle">
                                            @if(isset($envio['sede']['nombre']))
                                                {{ $envio['sede']['nombre'] }}
                                            @elseif(isset($envio['sede']['nombresede']))
                                                {{ $envio['sede']['nombresede'] }}
                                            @elseif(isset($envio['nombresede']))
                                                {{ $envio['nombresede'] }}
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        
                                        <!-- Fecha de Nacimiento -->
                                        <td class="text-center align-middle">
                                            @if(isset($detalle['paciente']['fecnacimiento']))
                                                {{ \Carbon\Carbon::parse($detalle['paciente']['fecnacimiento'])->format('d/m/Y') }}
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        
                                        <!-- DIAGNÓSTICO -->
                                        <!-- DM -->
                                        <td class="text-center align-middle" style="background-color: #FFE4E1;">
                                            {{ $detalle['dm'] ?? '' }}
                                        </td>
                                        <!-- HTA -->
                                        <td class="text-center align-middle" style="background-color: #FFE4E1;">
                                            {{ $detalle['hta'] ?? '' }}
                                        </td>
                                        
                                        <!-- MUESTRAS ENVIADAS -->
                                        <!-- A -->
                                        <td class="text-center align-middle" style="background-color: #F5F5DC;">
                                            {{ $detalle['a'] ?? '' }}
                                        </td>
                                        <!-- M -->
                                        <td class="text-center align-middle" style="background-color: #F0E6FF;">
                                            {{ $detalle['m'] ?? '' }}
                                        </td>
                                        <!-- OE -->
                                        <td class="text-center align-middle" style="background-color: #F0FFF0;">
                                            {{ $detalle['oe'] ?? '' }}
                                        </td>
                                        <!-- 024H -->
                                        <td class="text-center align-middle" style="background-color: #F0FFF0;">
                                            {{ $detalle['o24h'] ?? '' }}
                                        </td>
                                        <!-- PO -->
                                        <td class="text-center align-middle" style="background-color: #FFE4E1;">
                                            {{ $detalle['po'] ?? '' }}
                                        </td>
                                        
                                        <!-- TUBO LILA -->
                                        <!-- H3 -->
                                        <td class="text-center align-middle" style="background-color: #F0E6FF;">
                                            {{ $detalle['h3'] ?? '' }}
                                        </td>
                                        <!-- HBA1C -->
                                        <td class="text-center align-middle" style="background-color: #F0E6FF;">
                                            {{ $detalle['hba1c'] ?? '' }}
                                        </td>
                                        <!-- PTH -->
                                        <td class="text-center align-middle" style="background-color: #F0E6FF;">
                                            {{ $detalle['pth'] ?? '' }}
                                        </td>
                                        
                                        <!-- TUBO AMARILLO -->
                                        <!-- GLU -->
                                        <td class="text-center align-middle" style="background-color: #FFFACD;">
                                            {{ $detalle['glu'] ?? '' }}
                                        </td>
                                        <!-- CREA -->
                                        <td class="text-center align-middle" style="background-color: #FFFACD;">
                                            {{ $detalle['crea'] ?? '' }}
                                        </td>
                                        <!-- PL -->
                                        <td class="text-center align-middle" style="background-color: #FFFACD;">
                                            {{ $detalle['pl'] ?? '' }}
                                        </td>
                                        <!-- AU -->
                                        <td class="text-center align-middle" style="background-color: #FFFACD;">
                                            {{ $detalle['au'] ?? '' }}
                                        </td>
                                        <!-- BUN -->
                                        <td class="text-center align-middle" style="background-color: #FFFACD;">
                                            {{ $detalle['bun'] ?? '' }}
                                        </td>
                                        
                                        <!-- MUESTRA DE ORINA - ORINA ESP -->
                                        <!-- RELACION CREA/ALB -->
                                        <td class="text-center align-middle" style="background-color: #F0FFF0;">
                                            {{ $detalle['relacion_crea_alb'] ?? '' }}
                                        </td>
                                        <!-- PL -->
                                        <td class="text-center align-middle" style="background-color: #F0FFF0;">
                                            {{ $detalle['pl'] ?? '' }}
                                        </td>
                                        
                                        <!-- MUESTRA DE ORINA - ORINA24H -->
                                        <!-- DCRE24H -->
                                        <td class="text-center align-middle" style="background-color: #F0FFF0;">
                                            {{ $detalle['dcre24h'] ?? '' }}
                                        </td>
                                        <!-- ALB24H -->
                                        <td class="text-center align-middle" style="background-color: #F0FFF0;">
                                            {{ $detalle['alb24h'] ?? '' }}
                                        </td>
                                        <!-- BUNO24H -->
                                        <td class="text-center align-middle" style="background-color: #F0FFF0;">
                                            {{ $detalle['buno24h'] ?? '' }}
                                        </td>
                                        <!-- PESO -->
                                        <td class="text-center align-middle" style="background-color: #F0FFF0;">
                                            {{ $detalle['peso'] ?? '' }}
                                        </td>
                                        <!-- TALLA -->
                                        <td class="text-center align-middle" style="background-color: #F0FFF0;">
                                            {{ $detalle['talla'] ?? '' }}
                                        </td>
                                        <!-- VOLUMEN -->
                                        <td class="text-center align-middle" style="background-color: #F0FFF0;">
                                            {{ $detalle['volumen'] ?? '' }}
                                        </td>
                                        
                                        <!-- PACIENTES NEFRO - TUBO AMARILLO -->
                                        <!-- FER -->
                                        <td class="text-center align-middle" style="background-color: #FFFACD;">
                                            {{ $detalle['fer'] ?? '' }}
                                        </td>
                                        <!-- TRA -->
                                        <td class="text-center align-middle" style="background-color: #FFFACD;">
                                            {{ $detalle['tra'] ?? '' }}
                                        </td>
                                        <!-- FOSFAT -->
                                        <td class="text-center align-middle" style="background-color: #FFFACD;">
                                            {{ $detalle['fosfat'] ?? '' }}
                                        </td>
                                        <!-- ALB -->
                                        <td class="text-center align-middle" style="background-color: #FFFACD;">
                                            {{ $detalle['alb'] ?? '' }}
                                        </td>
                                        <!-- FE -->
                                        <td class="text-center align-middle" style="background-color: #FFFACD;">
                                            {{ $detalle['fe'] ?? '' }}
                                        </td>
                                        <!-- TSH -->
                                        <td class="text-center align-middle" style="background-color: #FFFACD;">
                                            {{ $detalle['tsh'] ?? '' }}
                                        </td>
                                        <!-- P -->
                                        <td class="text-center align-middle" style="background-color: #FFFACD;">
                                            {{ $detalle['p'] ?? '' }}
                                        </td>
                                        <!-- IONOGRAMA -->
                                        <td class="text-center align-middle" style="background-color: #FFFACD;">
                                            {{ $detalle['ionograma'] ?? '' }}
                                        </td>
                                        
                                        <!-- PACIENTES NEFRO - FORRADOS -->
                                        <!-- B12 -->
                                        <td class="text-center align-middle" style="background-color: #FFE4B5;">
                                            {{ $detalle['b12'] ?? '' }}
                                        </td>
                                        <!-- ACIDO FOLICO -->
                                        <td class="text-center align-middle" style="background-color: #FFE4B5;">
                                            {{ $detalle['acido_folico'] ?? '' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- CSS adicional para mejorar la visualización -->
                    <style>
                        .table-sm th, .table-sm td {
                            padding: 0.15rem !important;
                            vertical-align: middle !important;
                            border: 1px solid #dee2e6 !important;
                            white-space: nowrap;
                        }
                        .table th {
                            font-weight: 600 !important;
                            text-align: center !important;
                            font-size: 0.65rem !important;
                        }
                        .table td {
                            font-size: 0.7rem !important;
                        }
                    </style>
                    
                @else
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        No hay detalles de pacientes registrados para este envío.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
</div>
@endsection
