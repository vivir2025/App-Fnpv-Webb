<!-- resources/views/visitas/resultados.blade.php -->
@extends('layouts.app')

@section('title', 'Resultados de Búsqueda')

@section('content')
<div class="row">
    <div class="col-12 mb-4">
        <div class="card shadow-sm border-0 bg-light">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><i class="fas fa-clipboard-list me-2 text-success"></i>Resultados de Búsqueda</h4>
                    <a href="{{ route('visitas.buscar') }}" class="btn btn-success">
                        <i class="fas fa-search me-1"></i> Nueva Búsqueda
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card shadow border-0">
            <div class="card-body p-0">
                @if(empty($visitas))
                    <div class="alert alert-info m-4 border-0 shadow-sm">
                        <i class="fas fa-info-circle me-2"></i>No se encontraron visitas para esta identificación.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-success text-white">
                                <tr>
                                    <th class="py-3">Nombre del Paciente</th>
                                    <th class="py-3">Identificación</th>
                                    <th class="py-3">Fecha</th>
                                    <th class="py-3">Zona</th>
                                    <th class="text-center py-3">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($visitas as $visita)
                                <tr>
                                    <td class="py-3">{{ $visita['nombre_apellido'] }}</td>
                                    <td class="py-3">{{ $visita['identificacion'] }}</td>
                                    <td class="py-3">{{ \Carbon\Carbon::parse($visita['fecha'])->format('d/m/Y') }}</td>
                                    <td class="py-3">{{ $visita['zona'] ?? 'No especificada' }}</td>
                                    <td class="text-center py-3">
                                        <a href="{{ route('visitas.show', $visita['id']) }}" class="btn btn-success btn-sm">
                                            <i class="fas fa-eye me-1"></i> Ver Detalles
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
