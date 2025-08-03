@extends('layouts.app')

@section('title', 'Lista de Envíos de Muestras')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h4>
                    <i class="fas fa-vial text-primary me-2"></i>
                    @if(isset($sede))
                        Envíos de Muestras - {{ $sede['nombre'] ?? $sede['nombresede'] ?? 'Sede' }}
                    @else
                        Todos los Envíos de Muestras
                    @endif
                </h4>
                <div>
                    <a href="{{ route('laboratorio.index') }}" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-arrow-left me-1"></i> Volver
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    @if(count($envios) > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Código</th>
                                        <th>Fecha</th>
                                        <th>Sede</th>
                                        <th>Estado</th>
                                        <th>Fecha Salida</th>
                                        <th>Fecha Llegada</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($envios as $envio)
                                        <tr>
                                            <td>{{ $envio['codigo'] ?? 'N/A' }}</td>
                                            <td>{{ isset($envio['fecha']) ? \Carbon\Carbon::parse($envio['fecha'])->format('d/m/Y') : 'N/A' }}</td>
                                            <td>{{ $envio['sede']['nombre'] ?? $envio['sede']['nombresede'] ?? 'N/A' }}</td>
                                            <td>
                                                @if(isset($envio['fecha_llegada']) && !empty($envio['fecha_llegada']))
                                                    <span class="badge bg-success">Completado</span>
                                                @elseif(isset($envio['fecha_salida']) && !empty($envio['fecha_salida']))
                                                    <span class="badge bg-warning">En tránsito</span>
                                                @else
                                                    <span class="badge bg-secondary">Pendiente</span>
                                                @endif
                                            </td>
                                            <td>{{ isset($envio['fecha_salida']) ? \Carbon\Carbon::parse($envio['fecha_salida'])->format('d/m/Y') : 'N/A' }}</td>
                                            <td>{{ isset($envio['fecha_llegada']) ? \Carbon\Carbon::parse($envio['fecha_llegada'])->format('d/m/Y') : 'N/A' }}</td>
                                            <td>
                                                <a href="{{ route('laboratorio.ver', $envio['id']) }}" class="btn btn-sm btn-info me-1">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                 <a href="{{ route('laboratorio.enviarEmail', $envio['id']) }}" class="btn btn-sm btn-primary me-1" title="Enviar por email">
                                                    <i class="fas fa-envelope"></i>
                                                </a>
                                                <form action="{{ route('laboratorio.eliminar', $envio['id']) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Está seguro que desea eliminar este envío?')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            No hay envíos de muestras registrados.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
