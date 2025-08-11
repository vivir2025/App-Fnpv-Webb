@extends('layouts.app')

@section('title', 'Exportar Tests FINDRISK')

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
    .form-group {
        margin-bottom: 15px;
    }
    .form-check {
        margin-right: 15px;
    }
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-12 mb-3">
        <div class="d-flex justify-content-between align-items-center">
            <h4>Exportar Tests FINDRISK</h4>
            <div>
                <a href="javascript:history.back()" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
        </div>
    </div>

    <div class="col-12">
        @if (session('status'))
            <div class="alert alert-success" role="alert">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('findrisk.exportar') }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-md-6">
                    <div class="card shadow-sm info-card">
                        <div class="info-title">
                            <i class="fas fa-calendar-alt"></i> Período de Tiempo
                        </div>
                        <div class="info-content">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="fecha_inicio">Fecha de inicio</label>
                                        <input type="date" class="form-control @error('fecha_inicio') is-invalid @enderror" 
                                               id="fecha_inicio" name="fecha_inicio" value="{{ old('fecha_inicio', date('Y-m-d', strtotime('-30 days'))) }}" required>
                                        @error('fecha_inicio')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="fecha_fin">Fecha de fin</label>
                                        <input type="date" class="form-control @error('fecha_fin') is-invalid @enderror" 
                                               id="fecha_fin" name="fecha_fin" value="{{ old('fecha_fin', date('Y-m-d')) }}" required>
                                        @error('fecha_fin')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card shadow-sm info-card">
                        <div class="info-title">
                            <i class="fas fa-filter"></i> Filtros
                        </div>
                        <div class="info-content">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="sede_id">Sede</label>
                                        <select class="form-control @error('sede_id') is-invalid @enderror" id="sede_id" name="sede_id">
                                            <option value="">Todas las sedes</option>
                                            @foreach ($sedes as $sede)
                                                <option value="{{ $sede['id'] }}" {{ old('sede_id') == $sede['id'] ? 'selected' : '' }}>
                                                    {{ $sede['nombresede'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('sede_id')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="nivel_riesgo">Nivel de riesgo</label>
                                        <select class="form-control @error('nivel_riesgo') is-invalid @enderror" id="nivel_riesgo" name="nivel_riesgo">
                                            <option value="todos">Todos los niveles</option>
                                            <option value="bajo" {{ old('nivel_riesgo') == 'bajo' ? 'selected' : '' }}>Bajo</option>
                                            <option value="ligeramente_elevado" {{ old('nivel_riesgo') == 'ligeramente_elevado' ? 'selected' : '' }}>Ligeramente elevado</option>
                                            <option value="moderado" {{ old('nivel_riesgo') == 'moderado' ? 'selected' : '' }}>Moderado</option>
                                            <option value="alto" {{ old('nivel_riesgo') == 'alto' ? 'selected' : '' }}>Alto</option>
                                            <option value="muy_alto" {{ old('nivel_riesgo') == 'muy_alto' ? 'selected' : '' }}>Muy alto</option>
                                        </select>
                                        @error('nivel_riesgo')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card shadow-sm info-card">
                        <div class="info-title">
                            <i class="fas fa-file-export"></i> Formato de Exportación
                        </div>
                        <div class="info-content">
                            <div class="form-group">
                                <label class="d-block mb-2">Seleccione el formato:</label>
                                <div class="d-flex">
                                    <div class="form-check me-4">
                                        <input class="form-check-input" type="radio" name="formato" id="formato_excel" value="excel" checked>
                                        <label class="form-check-label" for="formato_excel">
                                            <i class="fas fa-file-excel text-success me-1"></i> Excel
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="formato" id="formato_pdf" value="pdf">
                                        <label class="form-check-label" for="formato_pdf">
                                            <i class="fas fa-file-pdf text-danger me-1"></i> PDF
                                        </label>
                                    </div>
                                </div>
                                @error('formato')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 mt-3">
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-file-export me-1"></i> Exportar
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
