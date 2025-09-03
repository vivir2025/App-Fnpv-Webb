@extends('layouts.app')

@section('title', 'Exportar Visitas Domiciliarias')

@section('content')
<style>
    /* Estilos SOLO para la vista de exportar - con prefijo export-page */
    .export-page {
        --export-primary-color: #0d6efd;
        --export-primary-dark: #0b5ed7;
        --export-success-color: #198754;
        --export-success-dark: #146c43;
        --export-shadow-lg: 0 1rem 3rem rgba(0,0,0,0.175);
        --export-shadow-sm: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
    }

    .export-page .modern-card {
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(10px);
        border: none;
        border-radius: 20px;
        box-shadow: var(--export-shadow-lg);
        overflow: hidden;
        transition: all 0.3s ease;
        border: 1px solid rgba(0,0,0,0.1);
    }
        /* ✅ Estilos adicionales para el select de sede */
    .export-page .form-floating select {
        border: 2px solid #e9ecef;
        border-radius: 12px;
        transition: all 0.3s ease;
        font-size: 1rem;
        padding: 1rem 0.75rem;
        height: calc(3.5rem + 2px);
        background-color: white;
    }

    .export-page .form-floating select:focus {
        border-color: var(--export-primary-color);
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        transform: translateY(-2px);
    }

    .export-page .modern-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 2rem 4rem rgba(0,0,0,0.15);
    }

    .export-page .modern-header {
        background: linear-gradient(135deg, var(--export-primary-color) 0%, var(--export-primary-dark) 100%);
        padding: 2rem;
        position: relative;
        overflow: hidden;
    }

    .export-page .modern-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        animation: export-float 6s ease-in-out infinite;
    }

    @keyframes export-float {
        0%, 100% { transform: translateY(0px) rotate(0deg); }
        50% { transform: translateY(-20px) rotate(180deg); }
    }

    .export-page .modern-header h5 {
        margin: 0;
        font-weight: 600;
        font-size: 1.5rem;
        position: relative;
        z-index: 2;
    }

    .export-page .modern-header i {
        margin-right: 0.75rem;
        font-size: 1.75rem;
    }

    .export-page .modern-body {
        padding: 2.5rem;
    }

    .export-page .form-floating {
        margin-bottom: 1.5rem;
    }

    .export-page .form-floating input {
        border: 2px solid #e9ecef;
        border-radius: 12px;
        transition: all 0.3s ease;
        font-size: 1rem;
        padding: 1rem 0.75rem;
        height: calc(3.5rem + 2px);
    }

    .export-page .form-floating input:focus {
        border-color: var(--export-primary-color);
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        transform: translateY(-2px);
    }

    .export-page .form-floating label {
        font-weight: 500;
        color: #6c757d;
        padding: 1rem 0.75rem;
    }

    .export-page .modern-btn {
        background: linear-gradient(135deg, var(--export-success-color) 0%, var(--export-success-dark) 100%);
        border: none;
        border-radius: 12px;
        padding: 1rem 2rem;
        font-weight: 600;
        font-size: 1.1rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        width: 100%;
    }

    .export-page .modern-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(25, 135, 84, 0.3);
        background: linear-gradient(135deg, var(--export-success-dark) 0%, var(--export-success-color) 100%);
    }

    .export-page .modern-btn:active {
        transform: translateY(0);
    }

    .export-page .modern-btn:disabled {
        opacity: 0.7;
        transform: none;
        box-shadow: none;
    }

    .export-page .modern-btn i {
        margin-right: 0.75rem;
        font-size: 1.2rem;
    }

    .export-page .modern-alert {
        border: none;
        border-radius: 12px;
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        color: white;
        margin-bottom: 2rem;
        padding: 1.25rem;
        box-shadow: var(--export-shadow-sm);
    }

    .export-page .modern-alert ul {
        margin: 0;
        padding-left: 1.5rem;
    }

    /* Loading Overlay */
    .export-loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(108, 117, 125, 0.8);
        backdrop-filter: blur(5px);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 9999;
        animation: export-fadeIn 0.3s ease;
    }

    @keyframes export-fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    .export-loading-content {
        background: white;
        padding: 3rem;
        border-radius: 20px;
        text-align: center;
        box-shadow: var(--export-shadow-lg);
        animation: export-slideUp 0.3s ease;
        max-width: 400px;
        margin: 2rem;
    }

    @keyframes export-slideUp {
        from { 
            opacity: 0;
            transform: translateY(30px);
        }
        to { 
            opacity: 1;
            transform: translateY(0);
        }
    }

    .export-loading-spinner {
        width: 60px;
        height: 60px;
        border: 4px solid #f3f3f3;
        border-top: 4px solid var(--export-success-color);
        border-radius: 50%;
        animation: export-spin 1s linear infinite;
        margin: 0 auto 1.5rem;
    }

    @keyframes export-spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .export-loading-text {
        font-size: 1.25rem;
        font-weight: 600;
        color: #333;
        margin: 0;
    }

    .export-loading-subtext {
        font-size: 0.95rem;
        color: #6c757d;
        margin-top: 0.5rem;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .export-page .modern-header {
            padding: 1.5rem;
        }
        
        .export-page .modern-body {
            padding: 2rem;
        }
        
        .export-loading-content {
            margin: 1rem;
            padding: 2rem;
        }
    }

    /* Animation for success */
    .export-page .success-animation {
        animation: export-successPulse 0.6s ease;
    }

    @keyframes export-successPulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }

    /* Mantener compatibilidad con Bootstrap existente */
    .export-page .form-group {
        margin-bottom: 0;
    }
</style>

<div class="container export-page">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow modern-card">
                <div class="card-header bg-primary text-white modern-header">
                    <h5 class="mb-0"><i class="fas fa-file-excel"></i> Exportar Visitas Domiciliarias</h5>
                </div>
                <div class="card-body modern-body">
                    <form action="{{ route('visitas.export.excel') }}" method="POST" id="exportForm">
                        @csrf
                        
                        @if ($errors->any())
                        <div class="alert alert-danger modern-alert">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        @endif

                        <!-- ✅ Filtro por sede -->
                        <div class="row mb-3">
                            <div class="col-12">
                                <div class="form-group">
                                    <div class="form-floating">
                                        <select name="sede_id" id="sede_id" class="form-control">
                                            <option value="todas">Todas las sedes</option>
                                            @foreach($sedes as $sede)
                                                <option value="{{ $sede['id'] ?? $sede['idsede'] }}">
                                                    {{ $sede['nombresede'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <label for="sede_id"><i class="fas fa-building me-2"></i>Sede</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="form-floating">
                                        <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control" required>
                                        <label for="fecha_inicio"><i class="fas fa-calendar-alt me-2"></i>Fecha Inicial</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="form-floating">
                                        <input type="date" name="fecha_fin" id="fecha_fin" class="form-control" required>
                                        <label for="fecha_fin"><i class="fas fa-calendar-alt me-2"></i>Fecha Final</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-success modern-btn" id="generateBtn">
                                <i class="fas fa-download"></i> Generar Excel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div class="export-loading-overlay" id="loadingOverlay">
    <div class="export-loading-content">
        <div class="export-loading-spinner"></div>
        <p class="export-loading-text">Descargando...</p>
        <p class="export-loading-subtext">Generando archivo Excel, por favor espere</p>
    </div>

@endsection

@section('scripts')
<script>
    // Establecer fecha actual como valor predeterminado para fecha final
    document.addEventListener('DOMContentLoaded', function() {
        const today = new Date();
        const formattedDate = today.toISOString().substr(0, 10);
        document.getElementById('fecha_fin').value = formattedDate;
        
        // Establecer fecha hace un mes como valor predeterminado para fecha inicial
        const lastMonth = new Date();
        lastMonth.setMonth(lastMonth.getMonth() - 1);
        document.getElementById('fecha_inicio').value = lastMonth.toISOString().substr(0, 10);

        // Manejar envío del formulario
        const form = document.getElementById('exportForm');
        const loadingOverlay = document.getElementById('loadingOverlay');
        const generateBtn = document.getElementById('generateBtn');

        form.addEventListener('submit', function(e) {
            // Validar fechas antes de enviar
            const fechaInicio = new Date(document.getElementById('fecha_inicio').value);
            const fechaFin = new Date(document.getElementById('fecha_fin').value);
            
            if (fechaInicio > fechaFin) {
                e.preventDefault();
                alert('La fecha inicial no puede ser mayor que la fecha final');
                return;
            }

            // Mostrar loading
            showLoading();
            
            // El formulario se enviará normalmente al servidor
            // El loading se ocultará cuando la página se recargue o cuando detectemos la descarga
            
            // Para detectar cuando termine la descarga, usamos un método con cookie
            setCookie('download_started', '1', 1);
            checkDownloadComplete();
        });

        function showLoading() {
            loadingOverlay.style.display = 'flex';
            generateBtn.disabled = true;
        }

        function hideLoading() {
            loadingOverlay.style.display = 'none';
            generateBtn.disabled = false;
            
            // Animación de éxito
            generateBtn.classList.add('success-animation');
            setTimeout(() => {
                generateBtn.classList.remove('success-animation');
            }, 600);
        }
  // ✅ Efectos de hover para select también
        const selects = document.querySelectorAll('.form-floating select');
        selects.forEach(select => {
            select.addEventListener('focus', function() {
                this.closest('.form-floating').style.transform = 'translateY(-2px)';
            });
            
            select.addEventListener('blur', function() {
                this.closest('.form-floating').style.transform = 'translateY(0)';
            });
        });
    });
        // Función para detectar cuando termine la descarga
        function checkDownloadComplete() {
            const checkInterval = setInterval(function() {
                if (getCookie('download_complete') === '1') {
                    clearInterval(checkInterval);
                    deleteCookie('download_complete');
                    deleteCookie('download_started');
                    hideLoading();
                }
            }, 1000);

            // Timeout de seguridad (30 segundos)
            setTimeout(function() {
                clearInterval(checkInterval);
                hideLoading();
            }, 30000);
        }

        // Funciones auxiliares para manejar cookies
        function setCookie(name, value, seconds) {
            const d = new Date();
            d.setTime(d.getTime() + (seconds * 1000));
            const expires = "expires=" + d.toUTCString();
            document.cookie = name + "=" + value + ";" + expires + ";path=/";
        }

        function getCookie(name) {
            const nameEQ = name + "=";
            const ca = document.cookie.split(';');
            for(let i = 0; i < ca.length; i++) {
                let c = ca[i];
                while (c.charAt(0) == ' ') c = c.substring(1, c.length);
                if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
            }
            return null;
        }

        function deleteCookie(name) {
            document.cookie = name + "=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
        }

        // Efectos de hover para inputs
        const inputs = document.querySelectorAll('.form-floating input');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.closest('.form-floating').style.transform = 'translateY(-2px)';
            });
            
            input.addEventListener('blur', function() {
                this.closest('.form-floating').style.transform = 'translateY(0)';
            });
        });
    });
</script>
@endsection