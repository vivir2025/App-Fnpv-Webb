@extends('layouts.app')

@section('title', 'Envío de Muestras')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Seleccionar Sede</h3>
                </div>
                <div class="card-body">
                    @if(is_array($sedes) && count($sedes) > 0)
                        <div class="row">
                            @foreach($sedes as $sede)
                                <div class="col-md-4 mb-3">
                                    <a href="{{ route('laboratorio.sede', $sede['id'] ?? $sede['idsede'] ?? '') }}" 
                                       class="btn btn-outline-primary btn-lg w-100">
                                        <i class="fas fa-building mb-2"></i><br>
                                        {{ $sede['nombresede'] ?? $sede['nombre'] ?? 'Sede sin nombre' }}
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            No se pudieron cargar las sedes. Por favor, intenta nuevamente.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
