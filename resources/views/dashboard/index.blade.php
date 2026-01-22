@extends('layouts.app')

@section('title', 'Dashboard')

@section('styles')
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" 
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<!-- Leaflet Heat CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet.heat@0.2.0/dist/leaflet-heat.css" />
<!-- Chart.js -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.min.css">
<!-- Dashboard CSS -->
<link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
<meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
    /* ========================================
    DISEÑO COMPACTO
    ======================================== */
    .dashboard-container {
        background: #f8f9fa;
        min-height: 100vh;
        padding: 20px;
    }

    /* Contenedor principal de filtros - UNA SOLA CAJA */
    .filtros-container {
        background: white;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.06);
        margin-bottom: 20px;
    }

    /* Primera fila: Sedes y Fechas (2 columnas) */
    .filtros-row-top {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
        margin-bottom: 20px;
        padding-bottom: 20px;
        border-bottom: 1px solid #e9ecef;
    }

    /* Segunda fila: Buscador (ancho completo) */
    .filtros-row-bottom {
        display: block;
    }

    /* Ajustes para botones de sede */
    #sede-buttons {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-top: 10px;
    }

    .btn-sm {
        font-size: 11px;
        padding: 4px 10px;
    }

    /* Inputs de fecha más compactos */
    .form-control-sm {
        font-size: 12px;
        padding: 4px 8px;
    }

    /* Labels más pequeños */
    .form-label {
        margin-bottom: 8px;
        font-size: 13px;
        font-weight: 600;
        color: #495057;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .form-label i {
        color: #198754;
    }

    /* ========================================
    ACORDEÓN DE AUXILIARES
    ======================================== */
    .accordion-auxiliares {
        margin-bottom: 20px;
    }

    .accordion-auxiliares .accordion-item {
        border: none;
        border-radius: 8px !important;
        margin-bottom: 10px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.06);
        overflow: hidden;
    }

    .accordion-auxiliares .accordion-button {
        background: linear-gradient(135deg, #198754 0%, #157347 100%);
        color: white;
        font-weight: 600;
        padding: 12px 20px;
        border-radius: 8px !important;
        font-size: 14px;
    }

    .accordion-auxiliares .accordion-button:not(.collapsed) {
        background: linear-gradient(135deg, #157347 0%, #0d5132 100%);
        box-shadow: none;
    }

    .accordion-auxiliares .accordion-button:focus {
        box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.25);
    }

    .accordion-auxiliares .accordion-button::after {
        filter: brightness(0) invert(1);
    }

    .accordion-auxiliares .accordion-body {
        padding: 0;
        background: white;
    }

    /* Tabla dentro del acordeón */
    .tabla-auxiliar-detalle {
        margin: 0;
        font-size: 13px;
    }

    .tabla-auxiliar-detalle thead {
        background: #f8f9fa;
    }

    .tabla-auxiliar-detalle thead th {
        font-size: 12px;
        font-weight: 600;
        color: #495057;
        padding: 10px 12px;
        border-bottom: 2px solid #dee2e6;
    }

    .tabla-auxiliar-detalle tbody td {
        padding: 10px 12px;
        vertical-align: middle;
    }

    .tabla-auxiliar-detalle tbody tr:hover {
        background: #f8f9fa;
    }

    .badge-auxiliar {
        padding: 5px 10px;
        border-radius: 15px;
        font-size: 11px;
        font-weight: 600;
    }

    /* ========================================
    BUSCADOR DE TERRITORIO
    ======================================== */
    #loading-territorio {
        background: rgba(255, 255, 255, 0.9);
        border-radius: 8px;
        padding: 10px;
    }

    #territorio-seleccionado {
        border-left: 4px solid #198754;
        animation: slideDown 0.3s ease;
        font-size: 12px;
        padding: 10px;
        background: #f8f9fa;
        border-radius: 5px;
        margin-top: 10px;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .custom-div-icon {
        background: transparent;
        border: none;
    }

    /* Animación de marcadores */
    .leaflet-marker-icon {
        animation: markerBounce 0.5s ease;
    }

    @keyframes markerBounce {
        0% {
            transform: translateY(-20px) scale(0);
            opacity: 0;
        }
        50% {
            transform: translateY(5px) scale(1.1);
        }
        100% {
            transform: translateY(0) scale(1);
            opacity: 1;
        }
    }

    /* ========================================
    RESPONSIVE
    ======================================== */
    @media (max-width: 768px) {
        .filtros-row-top {
            grid-template-columns: 1fr;
            gap: 15px;
        }
    }

    /* ========================================
    ANIMACIONES PARA CONTENIDO
    ======================================== */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    #dashboard-content {
        animation: fadeIn 0.5s ease-in;
    }
    </style>
@endsection

@section('content')
<div class="dashboard-container">

    <!-- CONTENEDOR DE FILTROS - UNA SOLA CAJA -->
    <div class="filtros-container">
        <!-- PRIMERA FILA: Sedes y Fechas (2 columnas) -->
        <div class="filtros-row-top">
            <!-- Selector de Sedes -->
            @include('dashboard.partials.sede-selector')
            
            <!-- Filtro de Fechas -->
            @include('dashboard.partials.filtro-fechas')
        </div>

        <!-- SEGUNDA FILA: Buscador de Territorio (ancho completo) -->
        <div class="filtros-row-bottom">
            @include('dashboard.partials.buscador-territorio')
        </div>
    </div>

    <!-- Skeleton Loader -->
    @include('dashboard.partials.skeleton-loader')

    <!-- Contenedor de contenido real -->
    <div id="dashboard-content" style="display: none; opacity: 0;">
        <div class="row">
            <!-- Mapa de Calor -->
            @include('dashboard.partials.mapa-calor')
            
            <!-- Estadísticas Generales -->
            @include('dashboard.partials.estadisticas')
        </div>

        <!-- Acordeón de Auxiliares (dinámico según filtros) -->
        <div class="accordion accordion-auxiliares" id="acordeon-auxiliares">
            <!-- Se llenará dinámicamente con JavaScript -->
        </div>

        <!-- Tabla Completa de Auxiliares -->
        @include('dashboard.partials.tabla-auxiliares')
        
        <!-- Gráficos -->
        @include('dashboard.partials.graficos')
    </div>

    <!-- Modal Mapa Completo -->
    @include('dashboard.partials.modal-mapa')
    
</div>
@endsection

@section('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" 
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script src="https://unpkg.com/leaflet.heat@0.2.0/dist/leaflet-heat.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    window.apiToken = '{{ $token }}';
    window.permisos = {
        puedeVerTodasSedes: {{ $permisos['puede_ver_todas_sedes'] ? 'true' : 'false' }},
        esJefe: {{ $permisos['es_jefe'] ? 'true' : 'false' }},
        sedeId: '{{ $permisos['sede_id'] ?? 'todas' }}'
    };
</script>
<script src="{{ asset('js/dashboard.js') }}"></script>
@endsection
