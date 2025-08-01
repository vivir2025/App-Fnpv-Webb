@extends('layouts.app')

@section('title', 'Crear Envío de Muestras')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h4>
                    <i class="fas fa-vial text-primary me-2"></i>
                    Crear Nuevo Envío de Muestras
                </h4>
                <a href="{{ route('laboratorio.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Volver
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <form action="{{ route('laboratorio.guardar') }}" method="POST">
                        @csrf
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="fecha">Fecha de Envío <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('fecha') is-invalid @enderror" 
                                           id="fecha" name="fecha" value="{{ old('fecha', date('Y-m-d')) }}" required>
                                    @error('fecha')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="idsede">Sede <span class="text-danger">*</span></label>
                                    <select class="form-control @error('idsede') is-invalid @enderror" 
                                            id="idsede" name="idsede" required>
                                        <option value="">Seleccione una sede</option>
                                        @foreach($sedes as $sede)
                                            <option value="{{ $sede['id'] ?? $sede['idsede'] ?? '' }}" 
                                                {{ old('idsede') == ($sede['id'] ?? $sede['idsede'] ?? '') ? 'selected' : '' }}>
                                                {{ $sede['nombresede'] ?? $sede['nombre'] ?? 'Sede sin nombre' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('idsede')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="lugar_toma_muestras">Lugar de Toma de Muestras</label>
                                    <input type="text" class="form-control" id="lugar_toma_muestras" 
                                           name="lugar_toma_muestras" value="{{ old('lugar_toma_muestras') }}">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="responsable_transporte">Responsable de Transporte</label>
                                    <input type="text" class="form-control" id="responsable_transporte" 
                                           name="responsable_transporte" value="{{ old('responsable_transporte') }}">
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Guardar Envío
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
