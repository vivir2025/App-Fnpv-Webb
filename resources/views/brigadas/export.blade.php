@extends('layouts.app')

@section('title', 'Exportar Brigadas y Medicamentos')

@section('content')
<style>
    /* Estilos SOLO para la vista de exportar - con prefijo brigada-export */
    .brigada-export {
        --export-primary-color: #0d6efd;
        --export-primary-dark: #0b5ed7;
        --export-success-color: #198754;
        --export-success-dark: #146c43;
        --export-shadow-lg: 0 1rem 3rem rgba(0,0,0,0.175);
        --export-shadow-sm: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
    }

    .brigada-export .modern-card {
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(10px);
        border: none;
        border-radius: 20px;
        box-shadow: var(--export-shadow-lg);
        overflow: hidden;
        transition: all 0.3s ease;
        border: 1px solid rgba(0,0,0,0.1);
    }

    .brigada-export .modern-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 2rem 4rem rgba(0,0,0,0.15);
    }

    .brigada-export .modern-header {
        background: linear-gradient(135deg, var(--export-primary-color) 0%, var(--export-primary-dark) 100%);
        padding: 2rem;
        position: relative;
        overflow: hidden;
    }

    .brigada-export .modern-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        animation: brigada-export-float 6s ease-in-out infinite;
    }

    @keyframes brigada-export-float {
        0%, 100% { transform: translateY(0px) rotate(0deg); }
        50% { transform: translateY(-20px) rotate(180deg); }
    }

    .brigada-export .modern-header h5 {
        margin: 0;
        font-weight: 600;
        font-size: 1.5rem;
        position: relative;
        z-index: 2;
    }

    .brigada-export .modern-header i {
        margin-right: 0.75rem;
        font-size: 1.75rem;
    }

    .brigada-export .modern-body {
        padding: 2.5rem;
    }

    .brigada-export .form-floating {
        margin-bottom: 1.5rem;
    }

    .brigada-export .form-floating input,
    .brigada-export .form-floating select {
        border: 2px solid #e9ecef;
        border-radius: 12px;
        transition: all 0.3s ease;
        font-size: 1rem;
        padding: 1rem 0.75rem;
        height: calc(3.5rem + 2px);
    }

    .brigada-export .form-floating input:focus,
    .brigada-export .form-floating select:focus {
        border-color: var(--export-primary-color);
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        transform: translateY(-2px);
    }

    .brigada-export .form-floating label {
        font-weight: 500;
        color: #6c757d;
        padding: 1rem 0.75rem;
    }

    .brigada-export .modern-btn {
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

    .brigada-export .modern-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(25, 135, 84, 0.3);
        background: linear-gradient(135deg, var(--export-success-dark) 0%, var(--export-success-color) 100%);
    }

    .brigada-export .modern-btn:active {
        transform: translateY(0);
    }

    .brigada-export .modern-btn:disabled {
        opacity: 0.7;
        transform: none;
        box-shadow: none;
    }

    .brigada-export .modern-btn i {
        margin-right: 0.75rem;
        font-size: 1.2rem;
    }

    .brigada-export .modern-alert {
        border: none;
        border-radius: 12px;
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        color: white;
        margin-bottom: 2rem;
        padding: 1.25rem;
        box-shadow: var(--export-shadow-sm);
    }

    .brigada-export .modern-alert ul {
        margin: 0;
        padding-left: 1.5rem;
    }

    /* Loading Overlay */
    .brigada-export-loading-overlay {
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
        animation: brigada-export-fadeIn 0.3s ease;
    }

    @keyframes brigada-export-fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    .brigada-export-loading-content {
        background: white;
        padding: 3rem;
        border-radius: 20px;
        text-align: center;
        box-shadow: var(--export-shadow-lg);
        animation: brigada-export-slideUp 0.3s ease;
        max-width: 400px;
        margin: 2rem;
    }

    @keyframes brigada-export-slideUp {
        from { 
            opacity: 0;
            transform: translateY(30px);
        }
        to { 
            opacity: 1;
            transform: translateY(0);
        }
    }

    .brigada-export-loading-spinner {
        width: 60px;
        height: 60px;
        border: 4px solid #f3f3f3;
        border-top: 4px solid var(--export-success-color);
        border-radius: 50%;
        animation: brigada-export-spin 1s linear infinite;
        margin: 0 auto 1.5rem;
    }

    @keyframes brigada-export-spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .brigada-export-loading-text {
        font-size: 1.25rem;
        font-weight: 600;
        color: #333;
        margin: 0;
    }

    .brigada-export-loading-subtext {
        font-size: 0.95rem;
        color: #6c757d;
        margin-top: 0.5rem;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .brigada-export .modern-header {
            padding: 1.5rem;
        }
        
        .brigada-export .modern-body {
            padding: 2rem;
        }
        
        .brigada-export-loading-content {
            margin: 1rem;
            padding: 2rem;
        }
    }

    /* Animation for success */
    .brigada-export .success-animation {
        animation: brigada-export-successPulse 0.6s ease;
    }

    @keyframes brigada-export-successPulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }

    /* Mantener compatibilidad con Bootstrap existente */
    .brigada-export .form-group {
        margin-bottom: 0;
    }
</style>

<div class="container brigada-export">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow modern-card">
                <div class="card-header bg-primary text-white modern-header">
                    <h5 class="mb-0"><i class="fas fa-file-medical"></i> Exportar Brigadas y Medicamentos</h5>
                </div>
                <div class="card-body modern-body">
                    <form action="{{ route('brigadas.export.excel') }}" method="POST" id="exportForm">
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
                        
                        @if (session('warning'))
                        <div class="alert alert-warning modern-alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            {{ session('warning') }}
                        </div>
                        @endif
                        
                        @if (session('success'))
                        <div class="alert alert-success modern-alert">
                            <i class="fas fa-check-circle me-2"></i>
                            {{ session('success') }}
                        </div>
                        @endif

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

                        {{-- ✅ FILTRO POR SEDE CON PERMISOS --}}
                        <div class="row mb-3">
                            <div class="col-12">
                                @if(isset($permisos) && $permisos['es_jefe'])
                                    {{-- Jefe solo ve su sede (no puede cambiar) --}}
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i>
                                        Exportando datos de: <strong>{{ $usuario['sede']['nombresede'] ?? 'Sede Asignada' }}</strong>
                                    </div>
                                    <input type="hidden" name="sede_id" value="{{ $permisos['sede_id'] }}">
                                @else
                                    {{-- Admin puede seleccionar sede --}}
                                    <div class="form-group">
                                        <div class="form-floating">
                                            <select name="sede_id" id="sede_id" class="form-control">
                                                <option value="todas">Todas las sedes</option>
                                                @if(isset($sedes) && count($sedes) > 0)
                                                    @foreach($sedes as $sede)
                                                        <option value="{{ $sede['id'] ?? $sede['idsede'] ?? '' }}">
                                                            {{ $sede['nombresede'] ?? $sede['nombre'] ?? 'Sede sin nombre' }}
                                                        </option>
                                                    @endforeach
                                                @endif
                                            </select>
                                            <label for="sede_id"><i class="fas fa-building me-2"></i>Sede</label>
                                        </div>
                                    </div>
                                @endif
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
<div class="brigada-export-loading-overlay" id="loadingOverlay">
    <div class="brigada-export-loading-content">
        <div class="brigada-export-loading-spinner"></div>
        <p class="brigada-export-loading-text">Descargando...</p>
        <p class="brigada-export-loading-subtext">Generando archivo Excel, por favor espere</p>
    </div>
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
            setCookie('brigada_download_started', '1', 1);
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

        // Función para detectar cuando termine la descarga
        function checkDownloadComplete() {
            const checkInterval = setInterval(function() {
                if (getCookie('brigada_download_complete') === '1') {
                    clearInterval(checkInterval);
                    deleteCookie('brigada_download_complete');
                    deleteCookie('brigada_download_started');
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

        // ✅ NUEVA FUNCIONALIDAD: Efectos visuales para el selector de sede
        const sedeSelect = document.getElementById('sede_id');
        if (sedeSelect) {
            sedeSelect.addEventListener('change', function() {
                // Efecto visual al cambiar sede
                this.closest('.form-floating').style.transform = 'scale(1.02)';
                setTimeout(() => {
                    this.closest('.form-floating').style.transform = 'scale(1)';
                }, 200);
                
                // Log para depuración
                console.log('Sede seleccionada:', this.value, this.options[this.selectedIndex].text);
            });
        }

        // Efectos de hover para inputs y selects
        const formElements = document.querySelectorAll('.form-floating input, .form-floating select');
        formElements.forEach(element => {
            element.addEventListener('focus', function() {
                this.closest('.form-floating').style.transform = 'translateY(-2px)';
            });
            
            element.addEventListener('blur', function() {
                this.closest('.form-floating').style.transform = 'translateY(0)';
            });
        });

        // ✅ NUEVA FUNCIONALIDAD: Validación adicional para sede
        form.addEventListener('submit', function(e) {
            const sedeId = document.getElementById('sede_id').value;
            
            // Mostrar información adicional en el loading según la sede seleccionada
            if (sedeId && sedeId !== 'todas') {
                const sedeNombre = document.getElementById('sede_id').options[document.getElementById('sede_id').selectedIndex].text;
                document.querySelector('.brigada-export-loading-subtext').textContent = 
                    `Generando reporte para: ${sedeNombre}`;
            } else {
                document.querySelector('.brigada-export-loading-subtext').textContent = 
                    'Generando reporte para todas las sedes';
            }
        });
    });
</script>
@endsection
