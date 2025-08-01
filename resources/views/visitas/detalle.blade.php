@extends('layouts.app')

@section('title', 'Detalle de Visita')

@section('styles')
<style>
    
    .info-card {
        margin-bottom: 20px;
    }
    .info-title {
        background-color: #f8f9fa;
        padding: 10px;
        border-bottom: 1px solid #dee2e6;
        font-weight: bold;
    }
    .info-content {
        padding: 15px;
    }
    .firma-img, .foto-riesgo-img {
        max-width: 100%;
        height: auto;
        border: 1px solid #dee2e6;
        border-radius: 4px;
    }
    .badge-hta, .badge-dm {
        font-size: 0.9rem;
        padding: 5px 10px;
        border-radius: 20px;
    }
    .badge-hta {
        background-color: #dc3545;
        color: white;
    }
    .badge-dm {
        background-color: #0d6efd;
        color: white;
    }
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-12 mb-3">
        <div class="d-flex justify-content-between align-items-center">
            <h4>Detalle de Visita Domiciliaria</h4>
            <div class="d-flex gap-2">
                <a href="{{ route('visitas.print', $visita['id']) }}" class="btn btn-danger btn-sm">
                    <i class="fas fa-file-pdf"></i> PDF
                </a>
                <a href="{{ url()->previous() }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
        </div>
    </div>


    <div class="col-md-6">
        <div class="card shadow-sm info-card">
            <div class="info-title">
                <i class="fas fa-user-circle"></i> Información del Paciente
            </div>
            <div class="info-content">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Nombre:</strong> {{ $visita['nombre_apellido'] }}</p>
                        <p><strong>Identificación:</strong> {{ $visita['identificacion'] }}</p>
                        <p><strong>Teléfono:</strong> {{ $visita['telefono'] ?? 'No registrado' }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Fecha de visita:</strong> {{ \Carbon\Carbon::parse($visita['fecha'])->format('d/m/Y') }}</p>
                        <p><strong>Zona:</strong> {{ $visita['zona'] ?? 'No especificada' }}</p>
                        <p><strong>Familiar:</strong> {{ $visita['familiar'] ?? 'No registrado' }}</p>
                    </div>
                </div>
                
                <div class="mt-2">
                    @if(isset($visita['hta']) && $visita['hta'] == 'Si')
                        <span class="badge badge-hta">HTA</span>
                    @endif
                    
                    @if(isset($visita['dm']) && $visita['dm'] == 'Si')
                        <span class="badge badge-dm">DM</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="card shadow-sm info-card">
            <div class="info-title">
                <i class="fas fa-heartbeat"></i> Signos Vitales
            </div>
            <div class="info-content">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Peso:</strong> {{ $visita['peso'] ?? 'No registrado' }} kg</p>
                        <p><strong>Talla:</strong> {{ $visita['talla'] ?? 'No registrado' }} cm</p>
                        <p><strong>IMC:</strong> {{ $visita['imc'] ?? 'No registrado' }}</p>
                        <p><strong>Perímetro Abdominal:</strong> {{ $visita['perimetro_abdominal'] ?? 'No registrado' }} cm</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Frecuencia Cardíaca:</strong> {{ $visita['frecuencia_cardiaca'] ?? 'No registrado' }}</p>
                        <p><strong>Frecuencia Respiratoria:</strong> {{ $visita['frecuencia_respiratoria'] ?? 'No registrado' }}</p>
                        <p><strong>Tensión Arterial:</strong> {{ $visita['tension_arterial'] ?? 'No registrado' }}</p>
                        <p><strong>Temperatura:</strong> {{ $visita['temperatura'] ?? 'No registrado' }} °C</p>
                        <p><strong>Glucometría:</strong> {{ $visita['glucometria'] ?? 'No registrado' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card shadow-sm info-card">
            <div class="info-title">
                <i class="fas fa-notes-medical"></i> Condiciones Médicas
            </div>
            <div class="info-content">
                <p><strong>Abandono Social:</strong> {{ $visita['abandono_social'] ?? 'No registrado' }}</p>
                <p><strong>Motivo:</strong> {{ $visita['motivo'] ?? 'No registrado' }}</p>
                <p><strong>Factores:</strong> {{ $visita['factores'] ?? 'No registrado' }}</p>
                <p><strong>Conductas:</strong> {{ $visita['conductas'] ?? 'No registrado' }}</p>
                <p><strong>Novedades:</strong> {{ $visita['novedades'] ?? 'No registrado' }}</p>
                <p><strong>Próximo Control:</strong> 
                    @if(isset($visita['proximo_control']))
                        {{ \Carbon\Carbon::parse($visita['proximo_control'])->format('d/m/Y') }}
                    @else
                        No registrado
                    @endif
                </p>
            </div>
        </div>

       @if(isset($visita['medicamentos']) && is_array($visita['medicamentos']) && count($visita['medicamentos']) > 0)
        <div class="card shadow-sm info-card">
            <div class="info-title">
                <i class="fas fa-pills"></i> Medicamentos
            </div>
            <div class="info-content">
                <ul class="list-group">
                    @foreach($visita['medicamentos'] as $medicamento)
                    <li class="list-group-item">
                        <strong>{{ is_array($medicamento) && isset($medicamento['nombmedicamento']) ? $medicamento['nombmedicamento'] : 'Medicamento sin nombre' }}</strong>
                        @if(is_array($medicamento) && isset($medicamento['pivot']) && is_array($medicamento['pivot']) && isset($medicamento['pivot']['indicaciones']))
                            <p class="mb-0 text-muted">{{ $medicamento['pivot']['indicaciones'] }}</p>
                        @endif
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif
    </div>

    <div class="col-12 mt-3">
        <div class="row">
            @if(isset($visita['firma_url']) && $visita['firma_url'])
            <div class="col-md-6 mb-3">
                <div class="card shadow-sm info-card">
                    <div class="info-title">
                        <i class="fas fa-signature"></i> Firma del Paciente
                    </div>
                    <div class="info-content text-center">
                        <img src="{{ $visita['firma_url'] }}" alt="Firma del paciente" class="firma-img">
                    </div>
                </div>
            </div>
            @endif

            @if(isset($visita['riesgo_fotografico_url']) && $visita['riesgo_fotografico_url'])
            <div class="col-md-6 mb-3">
                <div class="card shadow-sm info-card">
                    <div class="info-title">
                        <i class="fas fa-camera"></i> Foto de Riesgo
                    </div>
                    <div class="info-content text-center">
                        <div class="text-center mb-2">
                            <img src="{{ $visita['riesgo_fotografico_url'] }}" alt="Foto de riesgo" 
                                class="img-fluid rounded" 
                                style="max-height: 250px; object-fit: contain;">
                        </div>
                        
                        <!-- Controles para la imagen -->
                        <div class="d-flex justify-content-center mt-2">
                            <a href="{{ $visita['riesgo_fotografico_url'] }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-external-link-alt"></i> Ver original
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endif


        </div>
    </div>
</div>
@endsection