<!-- resources/views/visitas/buscar.blade.php -->
@extends('layouts.app')

@section('title', 'Buscar Visitas Domiciliarias')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-search me-2"></i>Buscar Visitas por Número de Cédula</h5>
            </div>
            <div class="card-body p-4">
                @if ($errors->any())
                    <div class="alert alert-danger border-0 shadow-sm">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('visitas.buscar.submit') }}">
                    @csrf
                    <div class="row align-items-end">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="identificacion" class="form-label fw-bold">Número de Cédula</label>
                                <input type="text" class="form-control form-control-lg border-success-subtle" 
                                       id="identificacion" name="identificacion" 
                                       placeholder="Ingrese el número de cédula" required>
                                <small class="text-muted mt-1 d-block">
                                    <i class="fas fa-info-circle me-1"></i>Ingrese el número de cédula completo sin puntos ni guiones
                                </small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <button type="submit" class="btn btn-success btn-lg w-100 shadow-sm">
                                    <i class="fas fa-search me-2"></i> Buscar
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
