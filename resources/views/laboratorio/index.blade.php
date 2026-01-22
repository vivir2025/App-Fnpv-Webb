@extends('layouts.app')

@section('title', 'Envío de Muestras')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-flask me-2"></i>
                        @if($permisos['puede_ver_todas_sedes'])
                            Seleccionar Sede - Envío de Muestras
                        @else
                            Envío de Muestras - {{ $usuario['sede']['nombresede'] ?? 'Sede Asignada' }}
                        @endif
                    </h3>
                </div>
                <div class="card-body">
                    @if($permisos['es_jefe'])
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Acceso restringido:</strong> Solo puede ver información de su sede asignada.
                        </div>
                    @endif
                    
                    @if(is_array($sedes) && count($sedes) > 0)
                        <div class="row">
                            @foreach($sedes as $sede)
                                <div class="col-md-4 mb-3">
                                    <a href="{{ route('laboratorio.sede', $sede['id'] ?? $sede['idsede'] ?? '') }}" 
                                       class="btn btn-outline-success btn-lg w-100 shadow-sm">
                                        <i class="fas fa-building mb-2" style="font-size: 2rem;"></i><br>
                                        <strong>{{ $sede['nombresede'] ?? $sede['nombre'] ?? 'Sede sin nombre' }}</strong>
                                        @if($permisos['es_jefe'])
                                            <br><small class="text-muted"><i class="fas fa-lock"></i> Sede asignada</small>
                                        @endif
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            @if($permisos['es_jefe'])
                                No tiene una sede asignada. Contacte al administrador.
                            @else
                                No se pudieron cargar las sedes. Por favor, intenta nuevamente.
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
