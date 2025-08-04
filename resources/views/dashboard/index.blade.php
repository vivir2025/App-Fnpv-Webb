@extends('layouts.app')

@section('title', 'Dashboard')

@section('styles')
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<!-- Leaflet Heat CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet.heat@0.2.0/dist/leaflet-heat.css" />
<!-- Chart.js -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.min.css">
<meta name="csrf-token" content="{{ csrf_token() }}">
<style>
    #map-fullscreen {
        width: 100%;
        border-radius: 0;
        height: calc(100vh - 120px);
    }

    .modal-fullscreen .modal-body {
        padding: 0 !important;
    }

    .modal-fullscreen .modal-header {
        border-radius: 0;
        padding: 15px 20px;
    }

    /* Animación para el botón */
    #btn-expandir-mapa {
        transition: all 0.3s ease;
        border: 1px solid rgba(255,255,255,0.3);
    }

    #btn-expandir-mapa:hover {
        background-color: rgba(255,255,255,0.1);
        transform: scale(1.05);
    }

    /* Mejorar la apariencia del modal */
    .modal-fullscreen .modal-content {
        border: none;
        border-radius: 0;
    }

    /* Indicador de carga para el mapa completo */
    .loading-fullscreen {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100%;
        background: #f8f9fa;
        color: #6c757d;
    }
    
    /* Estilos para el control de capas de Leaflet */
    .leaflet-control-layers {
        background: rgba(255, 255, 255, 0.95) !important;
        border-radius: 8px !important;
        box-shadow: 0 2px 10px rgba(0,0,0,0.2) !important;
    }

    .leaflet-control-layers-expanded {
        padding: 10px !important;
    }

    /* Mejorar la apariencia de los controles del mapa */
    .leaflet-control-zoom {
        border-radius: 8px !important;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0,0,0,0.2) !important;
    }

    .leaflet-control-zoom a {
        background: rgba(255, 255, 255, 0.95) !important;
        color: #333 !important;
        border: none !important;
    }

    .leaflet-control-zoom a:hover {
        background: rgba(25, 135, 84, 0.9) !important;
        color: white !important;
    }

    /* Personalizar el contenedor del mapa */
    #map {
        height: 500px;
        width: 100%;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        border: 2px solid #e9ecef;
    }

    /* Mensaje cuando no hay datos */
    .no-data-message {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100%;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 8px;
    }
        
    .sede-selector {
        background-color: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        margin-bottom: 20px;
    }
    
    .sede-btn {
        margin-right: 10px;
        margin-bottom: 10px;
        transition: all 0.2s ease;
    }
    
    .sede-btn:hover {
        transform: translateY(-2px);
    }
    
    .stats-card {
        transition: all 0.3s;
        border-radius: 10px;
        overflow: hidden;
    }
    
    .stats-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    
    .sede-btn.active {
        background-color: #198754;
        border-color: #198754;
        color: white;
        box-shadow: 0 4px 8px rgba(25, 135, 84, 0.3);
    }
    
    .progress-bar {
        min-width: 2em;
        transition: width 1s ease;
    }
    
    .card {
        transition: all 0.3s ease;
        border-radius: 10px;
        overflow: hidden;
    }
    
    .card:hover {
        box-shadow: 0 8px 16px rgba(0,0,0,0.1);
    }
    
    .card-header {
        padding: 15px 20px;
    }
    
    .display-4 {
        font-weight: 600;
        color: #198754;
    }
    
    /* Mejoras para la leyenda del mapa */
    .custom-legend {
        background: rgba(255, 255, 255, 0.95) !important;
        padding: 12px !important;
        border-radius: 10px !important;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important;
        border: 1px solid rgba(0,0,0,0.1);
    }
    
    /* Animación de carga */
    @keyframes pulse {
        0% { opacity: 0.6; }
        50% { opacity: 1; }
        100% { opacity: 0.6; }
    }
    
    .loading-indicator {
        animation: pulse 1.5s infinite ease-in-out;
    }
    
    /* Mejoras para tablas */
    .table {
        border-collapse: separate;
        border-spacing: 0;
    }
    
    .table thead th {
        background-color: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
        padding: 12px;
    }
    
    .table tbody td {
        padding: 12px;
        vertical-align: middle;
    }
    
    /* Mejoras para gráficos */
    canvas {
        min-height: 300px;
    }
</style>
@endsection

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow border-0">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-tachometer-alt me-2"></i>Dashboard de Visitas Domiciliarias</h5>
            </div>
            <div class="card-body">
                <h6 class="text-muted mb-4">Bienvenido, {{ session('usuario')['nombre'] ?? session('usuario') }}</h6>
                
                <!-- Selector de Sedes -->
                <div class="sede-selector mb-4 shadow-sm">
                    <h6 class="text-success mb-3"><i class="fas fa-map-marker-alt me-2"></i>Seleccione una sede para visualizar datos:</h6>
                    <div class="d-flex flex-wrap" id="sede-buttons">
                        <button class="btn btn-outline-success sede-btn active" data-sede-id="todas">Todas las sedes</button>
                        @foreach($sedes as $sede)
                            <button class="btn btn-outline-success sede-btn" data-sede-id="{{ $sede['id'] }}">
                                {{ $sede['nombresede'] }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Mapa de Calor con botón de expandir -->
    <div class="col-lg-8 mb-4">
        <div class="card shadow border-0 h-100" id="mapa-card">
            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-map me-2"></i>Mapa de Calor de Pacientes</h5>
                <button class="btn btn-sm btn-outline-light" id="btn-expandir-mapa" onclick="toggleMapaCompleto()">
                    <i class="fas fa-expand" id="icono-expandir"></i>
                    <span id="texto-expandir">Expandir</span>
                </button>
            </div>
            <div class="card-body p-0">
                <div id="map"></div>
            </div>
        </div>
    </div>

    <!-- Estadísticas Generales -->
    <div class="col-lg-4 mb-4">
        <div class="card shadow border-0 h-100 stats-card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Resumen</h5>
            </div>
            <div class="card-body">
                <div class="text-center mb-4">
                    <h2 class="display-4 text-success" id="total-pacientes">0</h2>
                    <p class="text-muted">Total de Pacientes</p>
                </div>
                <div class="text-center mb-4">
                    <h2 class="display-4 text-success" id="total-visitas-mes">0</h2>
                    <p class="text-muted">Visitas este mes</p>
                </div>
                <div class="text-center">
                    <h2 class="display-4 text-success" id="promedio-visitas">0</h2>
                    <p class="text-muted">Promedio mensual (meta: 80 visitas)</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para pantalla completa -->
<div class="modal fade" id="modalMapaCompleto" tabindex="-1" aria-labelledby="modalMapaCompletoLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalMapaCompletoLabel">
                    <i class="fas fa-map me-2"></i>Mapa de Calor de Pacientes - Vista Completa
                </h5>
                <button type="button" class="btn btn-outline-light btn-sm" onclick="cerrarMapaCompleto()">
                    <i class="fas fa-compress me-1"></i>Cerrar Pantalla Completa
                </button>
            </div>
            <div class="modal-body p-0">
                <div id="map-fullscreen"></div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Visitas por Auxiliar -->
    <div class="col-lg-12 mb-4">
        <div class="card shadow border-0">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-user-nurse me-2"></i>Visitas por Auxiliar (Mes Actual)</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="tabla-auxiliares">
                        <thead class="table-light">
                            <tr>
                                <th>Auxiliar</th>
                                <th>Sede</th>
                                <th>Visitas Realizadas</th>
                                <th>Progreso (meta: 80/mes)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Se llenará con JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Gráfico de Visitas por Día -->
    <div class="col-lg-6 mb-4">
        <div class="card shadow border-0 h-100">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Visitas por Día</h5>
            </div>
            <div class="card-body">
                <canvas id="visitas-diarias-chart"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Gráfico de Visitas por Sede -->
    <div class="col-lg-6 mb-4">
        <div class="card shadow border-0 h-100">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Distribución por Sede</h5>
            </div>
            <div class="card-body">
                <canvas id="visitas-sede-chart"></canvas>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script src="https://unpkg.com/leaflet.heat@0.2.0/dist/leaflet-heat.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <script>
    // Variables globales para gestionar el mapa y gráficos
    let map = null;
    let mapFullscreen = null;
    let visitasDiariasChart = null;
    let visitasSedeChart = null;
    let sedeActual = 'todas';
    let isFullscreenOpen = false;
    let heatLayer = null;
    let heatLayerFullscreen = null;
    let currentHeatData = [];

    // Función para cargar datos según la sede seleccionada
    function cargarDatosPorSede(sedeId) {
        console.log(`Cargando datos para sede: ${sedeId}`);
        sedeActual = sedeId;
        
        // Actualizar botones activos
        document.querySelectorAll('.sede-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        
        const botonActivo = document.querySelector(`[data-sede-id="${sedeId}"]`);
        if (botonActivo) {
            botonActivo.classList.add('active');
        }
        
        // Mostrar indicador de carga
        mostrarIndicadorCarga();
        
        // Obtener el token de sesión
        const token = '{{ $token }}';
        
        // Hacer petición AJAX para obtener datos
        fetch(`/api/dashboard/datos?sede_id=${sedeId}`, {
            method: 'GET',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => {
            console.log('Respuesta recibida:', response.status);
            if (!response.ok) {
                throw new Error(`Error HTTP: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Datos recibidos:', data);
            depurarDatosRecibidos(data); // Función de depuración
            ocultarIndicadorCarga();
            
            // Verificar estructura de datos
            if (!data.pacientes) data.pacientes = [];
            if (!data.estadisticas) data.estadisticas = { total_pacientes: 0, visitas_mes: 0, promedio_diario: 0 };
            if (!data.grafico_diario) data.grafico_diario = [];
            if (!data.grafico_sedes) data.grafico_sedes = [];
            if (!data.auxiliares) data.auxiliares = [];
            
            // Filtrar pacientes con coordenadas válidas
            const pacientesConCoordenadas = filtrarPacientesConCoordenadas(data.pacientes);
            console.log(`Pacientes con coordenadas válidas: ${pacientesConCoordenadas.length} de ${data.pacientes.length}`);
            
            // Guardar datos para uso en mapa expandido
            window.datosMapaActuales = pacientesConCoordenadas;
            
            // Actualizar componentes
            actualizarMapaCalor(pacientesConCoordenadas);
            actualizarEstadisticas(data.estadisticas);
            actualizarTablaAuxiliares(data.auxiliares);
            actualizarGraficos(data.grafico_diario, data.grafico_sedes);
            
            // Si el mapa expandido está abierto, actualizarlo también
            if (isFullscreenOpen && mapFullscreen) {
                actualizarMapaCalorFullscreen(pacientesConCoordenadas);
            }
        })
        .catch(error => {
            console.error('Error al cargar datos:', error);
            ocultarIndicadorCarga();
            mostrarError(`Error al cargar datos: ${error.message}. Por favor, intente nuevamente.`);
        });
    }

    // Función de depuración para verificar los datos recibidos
    function depurarDatosRecibidos(data) {
        console.group('Depuración de datos recibidos');
        console.log('Estructura completa:', data);
        
        // Verificar estadísticas
        if (data.estadisticas) {
            console.log('Estadísticas:', {
                total_pacientes: data.estadisticas.total_pacientes,
                visitas_mes: data.estadisticas.visitas_mes,
                promedio_diario: data.estadisticas.promedio_diario
            });
        } else {
            console.warn('No hay datos de estadísticas');
        }
        
        // Verificar auxiliares
        if (data.auxiliares && Array.isArray(data.auxiliares)) {
            console.log(`Auxiliares (${data.auxiliares.length}):`);
            data.auxiliares.forEach((aux, i) => {
                console.log(`Auxiliar ${i+1}:`, {
                    id: aux.id,
                    nombre: aux.nombre,
                    sede: aux.sede,
                    visitas_realizadas: aux.visitas_realizadas,
                });
            });
        } else {
            console.warn('No hay datos de auxiliares o no es un array');
        }
        
        console.groupEnd();
    }

    // Función para mostrar indicador de carga
    function mostrarIndicadorCarga() {
        // Remover indicador existente si existe
        const indicadorExistente = document.getElementById('loading-indicator');
        if (indicadorExistente) {
            indicadorExistente.remove();
        }
        
        const loadingIndicator = document.createElement('div');
        loadingIndicator.className = 'alert alert-info text-center loading-indicator';
        loadingIndicator.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Cargando datos...';
        loadingIndicator.id = 'loading-indicator';
        
        // Insertar al inicio del contenido principal
        const contenidoPrincipal = document.querySelector('.row.mb-4 .card-body');
        if (contenidoPrincipal) {
            contenidoPrincipal.appendChild(loadingIndicator);
        }
    }

    // Función para ocultar indicador de carga
    function ocultarIndicadorCarga() {
        const indicator = document.getElementById('loading-indicator');
        if (indicator) {
            indicator.remove();
        }
    }

    // Función para mostrar errores
    function mostrarError(mensaje) {
        // Remover alertas existentes
        const alertaExistente = document.getElementById('error-alert');
        if (alertaExistente) {
            alertaExistente.remove();
        }
        
        const errorAlert = document.createElement('div');
        errorAlert.className = 'alert alert-danger alert-dismissible fade show';
        errorAlert.innerHTML = `
            <i class="fas fa-exclamation-triangle me-2"></i>${mensaje}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        errorAlert.id = 'error-alert';
        
        const contenidoPrincipal = document.querySelector('.row.mb-4 .card-body');
        if (contenidoPrincipal) {
            contenidoPrincipal.appendChild(errorAlert);
        }
        
        // Eliminar automáticamente después de 8 segundos
        setTimeout(() => {
            const alert = document.getElementById('error-alert');
            if (alert) {
                alert.remove();
            }
        }, 8000);
    }

    // Función para filtrar pacientes con coordenadas válidas
    function filtrarPacientesConCoordenadas(pacientes) {
        if (!Array.isArray(pacientes)) {
            console.warn('Los datos de pacientes no son un array válido');
            return [];
        }
        
        return pacientes.filter(paciente => {
            if (!paciente.latitud || !paciente.longitud) {
                return false;
            }
            
            // Limpiar y convertir coordenadas
            const lat = parseFloat(String(paciente.latitud).replace(',', '.'));
            const lng = parseFloat(String(paciente.longitud).replace(',', '.'));
            
            // Validar que sean números válidos y no sean 0.0 o coordenadas inválidas
            const coordenadasValidas = !isNaN(lat) && !isNaN(lng) && 
                                    lat !== 0 && lng !== 0 && 
                                    Math.abs(lat) > 0.001 && Math.abs(lng) > 0.001 &&
                                    lat >= -90 && lat <= 90 && 
                                    lng >= -180 && lng <= 180;
            
            if (!coordenadasValidas) {
                console.log(`Paciente ${paciente.identificacion || 'desconocido'} tiene coordenadas inválidas: ${lat}, ${lng}`);
            }
            
            return coordenadasValidas;
        });
    }

    function actualizarMapaCalor(pacientes) {
        console.log("=== INICIANDO ACTUALIZACIÓN DEL MAPA DE CALOR ===");
        
        // Verificar que el contenedor del mapa existe
        const mapContainer = document.getElementById('map');
        if (!mapContainer) {
            console.error('CRÍTICO: Contenedor del mapa (#map) no encontrado en el DOM');
            mostrarError('Error: No se pudo encontrar el contenedor del mapa');
            return;
        }
        
        console.log('Contenedor del mapa encontrado:', mapContainer);
        
        try {
            // Limpiar mapa existente
            if (map && map.remove) {
                console.log("Removiendo mapa existente");
                map.remove();
                map = null;
            }
            
            // Limpiar contenedor completamente
            mapContainer.innerHTML = '';
            
            // Esperar un momento para que el DOM se actualice
            setTimeout(() => {
                try {
                    console.log("Inicializando nuevo mapa...");
                    
                    // Crear nuevo mapa con coordenadas de Colombia/Bogotá
                    map = L.map('map', {
                        center: [4.6097, -74.0817], // Bogotá
                        zoom: 11,
                        zoomControl: true,
                        attributionControl: true
                    });
                    
                    console.log("Mapa inicializado correctamente");
                    
                    // Agregar capa satelital como base
                    const satelitalLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                        attribution: 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community',
                        maxZoom: 19
                    });
                    
                    // Capa de calles para comparación (opcional)
                    const callesLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                        maxZoom: 19
                    });
                    
                    // Agregar capa satelital por defecto
                    satelitalLayer.addTo(map);
                    
                    // Control de capas para alternar entre satelital y calles
                    const baseMaps = {
                        "Vista Satelital": satelitalLayer,
                        "Calles": callesLayer
                    };
                    
                    L.control.layers(baseMaps).addTo(map);
                    
                    console.log("Capas base agregadas");
                    
                    // Procesar pacientes
                    if (!Array.isArray(pacientes) || pacientes.length === 0) {
                        console.warn('No hay pacientes válidos para mostrar en el mapa');
                        mostrarMensajeEnMapa('No hay pacientes con coordenadas válidas para mostrar');
                        return;
                    }
                    
                    console.log(`Procesando ${pacientes.length} pacientes para el mapa de calor`);
                    
                    const heatData = [];
                    const bounds = [];
                    
                    pacientes.forEach((paciente, index) => {
                        const lat = parseFloat(String(paciente.latitud).replace(',', '.'));
                        const lng = parseFloat(String(paciente.longitud).replace(',', '.'));
                        
                        console.log(`Paciente ${index + 1}: ${paciente.nombre || 'Sin nombre'} - Lat: ${lat}, Lng: ${lng}`);
                        
                        // Datos para heatmap (sin marcadores individuales)
                        heatData.push([lat, lng, 1.0]); // Intensidad 1.0
                        bounds.push([lat, lng]);
                    });
                    
                    // Guardar datos de calor para uso en mapa expandido
                    currentHeatData = heatData;
                    
                    console.log(`Se procesaron ${heatData.length} puntos para el mapa de calor`);
                    
                    if (heatData.length > 0) {
                        // Agregar SOLO la capa de calor (sin marcadores)
                        console.log("Agregando capa de calor con", heatData.length, "puntos");
                        
                        heatLayer = L.heatLayer(heatData, {
                            radius: 30,          // Radio más grande para mejor visibilidad
                            blur: 20,            // Más difuminado
                            maxZoom: 17,
                            max: 1.0,
                            minOpacity: 0.4,     // Opacidad mínima
                            gradient: {          // Gradiente de colores mejorado
                                0.0: '#0000ff',  // Azul (menor densidad)
                                0.2: '#00ffff',  // Cian
                                0.4: '#00ff00',  // Verde
                                0.6: '#ffff00',  // Amarillo
                                0.8: '#ff8000',  // Naranja
                                1.0: '#ff0000'   // Rojo (mayor densidad)
                            }
                        }).addTo(map);
                        
                        // Ajustar vista del mapa a todos los puntos
                        if (bounds.length === 1) {
                            console.log("Un solo punto - centrando vista");
                            map.setView(bounds[0], 16);
                        } else {
                            console.log("Múltiples puntos - ajustando vista con fitBounds");
                            const group = new L.featureGroup();
                            bounds.forEach(coord => {
                                L.marker(coord).addTo(group);
                            });
                            map.fitBounds(group.getBounds(), {
                                padding: [30, 30],
                                maxZoom: 15
                            });
                            // Remover los marcadores temporales del grupo
                            map.removeLayer(group);
                        }
                        
                        // Agregar leyenda del mapa de calor
                        agregarLeyendaCalor();
                        
                        // Mensaje de éxito
                        console.log(`✓ Mapa de calor actualizado exitosamente con ${heatData.length} puntos`);
                                        } else {
                        console.warn('No se pudieron procesar puntos válidos para el mapa de calor');
                        mostrarMensajeEnMapa('No hay pacientes con coordenadas válidas para mostrar');
                    }
                    
                    // Forzar redimensionamiento del mapa
                    setTimeout(() => {
                        if (map) {
                            console.log("Invalidando tamaño del mapa");
                            map.invalidateSize();
                        }
                    }, 500);
                    
                } catch (innerError) {
                    console.error('Error al crear el mapa:', innerError);
                    mostrarError(`Error al inicializar el mapa: ${innerError.message}`);
                }
            }, 100);
            
        } catch (error) {
            console.error('Error fatal en actualizarMapaCalor:', error);
            mostrarError(`Error crítico en el mapa: ${error.message}`);
        }
    }

    function agregarLeyendaCalor() {
        const legend = L.control({ position: 'bottomright' });
        
        legend.onAdd = function(map) {
            const div = L.DomUtil.create('div', 'info legend custom-legend');
            
            div.innerHTML = `
                <h6 style="margin: 0 0 8px 0; color: #333; font-weight: 600;">
                    <i class="fas fa-thermometer-half"></i> Densidad de Pacientes
                </h6>
                <div style="display: flex; align-items: center; margin-bottom: 5px;">
                    <div style="width: 15px; height: 15px; background: #0000ff; margin-right: 8px; border-radius: 2px;"></div>
                    <span>Baja</span>
                </div>
                <div style="display: flex; align-items: center; margin-bottom: 5px;">
                    <div style="width: 15px; height: 15px; background: #ffff00; margin-right: 8px; border-radius: 2px;"></div>
                    <span>Media</span>
                </div>
                <div style="display: flex; align-items: center;">
                    <div style="width: 15px; height: 15px; background: #ff0000; margin-right: 8px; border-radius: 2px;"></div>
                    <span>Alta</span>
                </div>
            `;
            
            return div;
        };
        
        legend.addTo(map);
    }

    // Función para mostrar mensaje en el mapa cuando no hay datos
    function mostrarMensajeEnMapa(mensaje) {
        const mapContainer = document.getElementById('map');
        if (mapContainer) {
            mapContainer.innerHTML = `
                <div class="d-flex align-items-center justify-content-center h-100 bg-light rounded">
                    <div class="text-center text-muted p-4">
                        <i class="fas fa-map-marker-alt fa-3x mb-3 text-secondary"></i>
                        <h5>${mensaje}</h5>
                        <p>Los pacientes aparecerán aquí cuando tengan coordenadas válidas</p>
                    </div>
                </div>
            `;
        }
    }

    // Función para actualizar estadísticas
    function actualizarEstadisticas(estadisticas) {
        // Asegurar que tenemos valores válidos
        const totalPacientes = parseInt(estadisticas.total_pacientes) || 0;
        const visitasMes = parseInt(estadisticas.visitas_mes) || 0;
        
        // Actualizar los elementos en el DOM con animación
        animarContador('total-pacientes', totalPacientes);
        animarContador('total-visitas-mes', visitasMes);
        
        // Calcular porcentaje basado en meta de 80 visitas mensuales
        const porcentajeMeta = (visitasMes / 80 * 100).toFixed(1);
        animarContador('promedio-visitas', porcentajeMeta, '%');
        
        console.log(`Estadísticas actualizadas: ${totalPacientes} pacientes, ${visitasMes} visitas (${porcentajeMeta}% de la meta)`);
    }

    // Función para animar contadores
    function animarContador(elementId, valorFinal, sufijo = '') {
        const elemento = document.getElementById(elementId);
        if (!elemento) return;
        
        const valorInicial = parseInt(elemento.textContent.replace(/[^0-9.-]+/g, '')) || 0;
        const duracion = 1000; // 1 segundo
        const pasos = 20;
        const incremento = (valorFinal - valorInicial) / pasos;
        
        let paso = 0;
        const intervalo = setInterval(() => {
            paso++;
            const valorActual = valorInicial + (incremento * paso);
            elemento.textContent = Math.round(valorActual).toLocaleString() + sufijo;
            
            if (paso >= pasos) {
                clearInterval(intervalo);
                elemento.textContent = valorFinal.toLocaleString() + sufijo;
            }
        }, duracion / pasos);
    }

    function actualizarTablaAuxiliares(auxiliares) {
        console.log('Actualizando tabla de auxiliares con datos:', auxiliares);
        
        const tbody = document.querySelector('#tabla-auxiliares tbody');
        if (!tbody) {
            console.error('No se encontró el tbody de la tabla de auxiliares');
            return;
        }
        
        tbody.innerHTML = '';
        
        if (!Array.isArray(auxiliares) || auxiliares.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-4"><i class="fas fa-user-nurse fa-2x mb-3 d-block"></i>No hay datos de auxiliares disponibles para esta sede.</td></tr>';
            return;
        }
        
        auxiliares.forEach((auxiliar, index) => {
            // El backend ya nos da los datos limpios
            const nombreAuxiliar = auxiliar.nombre || `Auxiliar #${auxiliar.id}`;
            const nombreSede = auxiliar.sede || 'Sin sede';
            const visitasRealizadas = parseInt(auxiliar.visitas_realizadas || 0);
            
            // El progreso se calcula contra una meta fija de 80
            const metaMensual = 80;
            const progreso = metaMensual > 0 ? Math.round((visitasRealizadas / metaMensual) * 100) : 0;
            
            const row = document.createElement('tr');
            row.classList.add('fade-in-row');
            row.style.animationDelay = `${index * 0.1}s`;
            
            row.innerHTML = `
                <td><strong>${nombreAuxiliar}</strong></td>
                <td>${nombreSede}</td>
                <td><span class="badge bg-success rounded-pill px-3 py-2">${visitasRealizadas}</span></td>
                <td>
                    <div class="progress" style="height: 20px;" title="${visitasRealizadas} de ${metaMensual} visitas">
                        <div class="progress-bar ${progreso >= 100 ? 'bg-success' : progreso >= 70 ? 'bg-primary' : progreso >= 40 ? 'bg-warning' : 'bg-danger'}" 
                            role="progressbar" 
                            style="width: ${Math.min(progreso, 100)}%" 
                            aria-valuenow="${progreso}" 
                            aria-valuemin="0" 
                            aria-valuemax="100">
                            ${progreso}%
                        </div>
                    </div>
                </td>
            `;
            tbody.appendChild(row);
        });
    }

    // Función para actualizar gráficos
    function actualizarGraficos(datosDiarios, datosSedes) {
        // Limpiar gráficos existentes
        if (visitasDiariasChart) {
            visitasDiariasChart.destroy();
            visitasDiariasChart = null;
        }
        
        if (visitasSedeChart) {
            visitasSedeChart.destroy();
            visitasSedeChart = null;
        }
        
        // Gráfico de visitas diarias
        const ctxDiarias = document.getElementById('visitas-diarias-chart');
        if (ctxDiarias && Array.isArray(datosDiarios) && datosDiarios.length > 0) {
            visitasDiariasChart = new Chart(ctxDiarias, {
                type: 'line',
                data: {
                    labels: datosDiarios.map(item => item.fecha || 'Sin fecha'),
                    datasets: [{
                        label: 'Visitas por día',
                        data: datosDiarios.map(item => item.cantidad || 0),
                        borderColor: '#198754',
                        backgroundColor: 'rgba(25, 135, 84, 0.1)',
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#198754',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 5,
                        pointHoverRadius: 7
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                font: {
                                    size: 14
                                },
                                usePointStyle: true
                            }
                        },
                        title: {
                            display: true,
                            text: 'Tendencia de Visitas Diarias',
                            font: {
                                size: 16,
                                weight: 'bold'
                            },
                            padding: {
                                top: 10,
                                bottom: 20
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(255, 255, 255, 0.9)',
                            titleColor: '#333',
                            bodyColor: '#333',
                            borderColor: '#198754',
                            borderWidth: 1,
                            cornerRadius: 8,
                            boxPadding: 6,
                            usePointStyle: true
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0,
                                font: {
                                    size: 12
                                }
                            },
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            }
                        },
                        x: {
                            ticks: {
                                font: {
                                    size: 12
                                }
                            },
                            grid: {
                                display: false
                            }
                        }
                    },
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    animation: {
                        duration: 1000,
                        easing: 'easeOutQuart'
                    }
                }
            });
        } else if (ctxDiarias) {
            ctxDiarias.innerHTML = '<div class="text-center text-muted p-4">No hay datos suficientes para mostrar la tendencia diaria</div>';
        }

        // Gráfico de visitas por sede
        const ctxSedes = document.getElementById('visitas-sede-chart');
        if (ctxSedes && Array.isArray(datosSedes) && datosSedes.length > 0) {
            visitasSedeChart = new Chart(ctxSedes, {
                type: 'doughnut',
                data: {
                    labels: datosSedes.map(item => item.sede || 'Sin sede'),
                    datasets: [{
                        label: 'Visitas por sede',
                        data: datosSedes.map(item => item.cantidad || 0),
                        backgroundColor: [
                            'rgba(25, 135, 84, 0.8)',
                            'rgba(13, 110, 253, 0.8)',
                            'rgba(255, 193, 7, 0.8)',
                            'rgba(220, 53, 69, 0.8)',
                            'rgba(111, 66, 193, 0.8)',
                            'rgba(32, 201, 151, 0.8)'
                        ],
                        borderWidth: 2,
                        borderColor: '#ffffff',
                        hoverOffset: 15
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                font: {
                                    size: 13
                                },
                                padding: 15
                            }
                        },
                        title: {
                            display: true,
                            text: 'Distribución por Sede',
                            font: {
                                size: 16,
                                weight: 'bold'
                            },
                            padding: {
                                top: 10,
                                bottom: 20
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.raw || 0;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = Math.round((value / total) * 100);
                                    return `${label}: ${value} visitas (${percentage}%)`;
                                }
                            },
                            backgroundColor: 'rgba(255, 255, 255, 0.9)',
                            titleColor: '#333',
                            bodyColor: '#333',
                            borderColor: '#ddd',
                            borderWidth: 1,
                            cornerRadius: 8,
                            boxPadding: 6
                        }
                    },
                    cutout: '65%',
                    animation: {
                        animateRotate: true,
                        animateScale: true,
                        duration: 1200,
                        easing: 'easeOutQuart'
                    }
                }
            });
        } else if (ctxSedes) {
            ctxSedes.innerHTML = '<div class="text-center text-muted p-4">No hay datos suficientes para mostrar la distribución por sede</div>';
        }
    }

    // Funciones para manejar el mapa en pantalla completa
    function toggleMapaCompleto() {
        const modal = document.getElementById('modalMapaCompleto');
        const bootstrapModal = new bootstrap.Modal(modal);
        
        // Mostrar modal
        bootstrapModal.show();
        
        // Crear el mapa en pantalla completa después de que se muestre el modal
        modal.addEventListener('shown.bs.modal', function() {
            crearMapaCompleto();
            isFullscreenOpen = true;
        });
        
        // Limpiar cuando se cierre el modal
        modal.addEventListener('hidden.bs.modal', function() {
            if (mapFullscreen) {
                mapFullscreen.remove();
                mapFullscreen = null;
            }
            isFullscreenOpen = false;
        });
    }

    // Función para cerrar el mapa de pantalla completa
    function cerrarMapaCompleto() {
        const modal = document.getElementById('modalMapaCompleto');
        const bootstrapModal = bootstrap.Modal.getInstance(modal);
        if (bootstrapModal) {
            bootstrapModal.hide();
        }
    }

    // Función para crear el mapa en pantalla completa
    function crearMapaCompleto() {
        console.log("Creando mapa en pantalla completa...");
        
        try {
            // Verificar si ya existe y limpiarlo
            if (mapFullscreen) {
                mapFullscreen.remove();
                mapFullscreen = null;
            }
            
            // Crear nuevo mapa para pantalla completa
            mapFullscreen = L.map('map-fullscreen', {
                center: [4.6097, -74.0817], // Bogotá
                zoom: 12,
                zoomControl: true,
                attributionControl: true
            });
            
            // Agregar capa satelital
            const satelitalLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                attribution: 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community',
                maxZoom: 19
            });
            
            // Capa de calles
            const callesLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                maxZoom: 19
            });
            
            // Agregar capa satelital por defecto
            satelitalLayer.addTo(mapFullscreen);
            
            // Control de capas
            const baseMaps = {
                "Vista Satelital": satelitalLayer,
                "Calles": callesLayer
            };
            
            L.control.layers(baseMaps).addTo(mapFullscreen);
            
            // Usar los datos actuales del mapa para el mapa de pantalla completa
            if (window.datosMapaActuales && window.datosMapaActuales.length > 0) {
                actualizarMapaCalorFullscreen(window.datosMapaActuales);
            } else if (currentHeatData && currentHeatData.length > 0) {
                // Si no hay datos de pacientes pero sí hay datos de calor, usar esos
                agregarCapaCalorDirectaFullscreen(currentHeatData);
            } else {
                mostrarMensajeEnMapaCompleto('No hay datos disponibles para mostrar en el mapa');
            }
            
            // Forzar redimensionamiento
            setTimeout(() => {
                if (mapFullscreen) {
                    mapFullscreen.invalidateSize();
                }
            }, 300);
            
            console.log("Mapa de pantalla completa creado exitosamente");
            
        } catch (error) {
            console.error('Error al crear mapa de pantalla completa:', error);
            mostrarMensajeEnMapaCompleto('Error al cargar el mapa en pantalla completa');
        }
    }

    // Función para actualizar mapa de calor en pantalla completa
    function actualizarMapaCalorFullscreen(pacientes) {
        if (!mapFullscreen || !Array.isArray(pacientes)) {
            return;
        }
        
        try {
            // Limpiar capa de calor existente
            if (heatLayerFullscreen) {
                mapFullscreen.removeLayer(heatLayerFullscreen);
                heatLayerFullscreen = null;
            }
            
            const heatData = [];
            const bounds = [];
            
            pacientes.forEach((paciente) => {
                const lat = parseFloat(String(paciente.latitud).replace(',', '.'));
                const lng = parseFloat(String(paciente.longitud).replace(',', '.'));
                
                if (!isNaN(lat) && !isNaN(lng) && lat !== 0 && lng !== 0 &&
                    Math.abs(lat) > 0.001 && Math.abs(lng) > 0.001 &&
                    lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180) {
                    
                    heatData.push([lat, lng, 1.0]);
                    bounds.push([lat, lng]);
                }
            });
            
            if (heatData.length > 0) {
                // Agregar capa de calor con parámetros optimizados para pantalla completa
                heatLayerFullscreen = L.heatLayer(heatData, {
                    radius: 35,          // Radio más grande para pantalla completa
                    blur: 25,            // Mayor difuminado
                    maxZoom: 17,
                    max: 1.0,
                    minOpacity: 0.4,
                    gradient: {
                        0.0: '#0000ff',  // Azul
                        0.2: '#00ffff',  // Cian
                        0.4: '#00ff00',  // Verde
                        0.6: '#ffff00',  // Amarillo
                        0.8: '#ff8000',  // Naranja
                        1.0: '#ff0000'   // Rojo
                    }
                }).addTo(mapFullscreen);
                
                // Guardar datos para posible reutilización
                currentHeatData = heatData;
                
                // Ajustar vista
                if (bounds.length > 1) {
                    const group = new L.featureGroup();
                    bounds.forEach(coord => {
                        L.marker(coord).addTo(group);
                    });
                    mapFullscreen.fitBounds(group.getBounds(), {
                        padding: [50, 50],
                        maxZoom: 14
                    });
                    mapFullscreen.removeLayer(group);
                } else if (bounds.length === 1) {
                    mapFullscreen.setView(bounds[0], 15);
                }
                
                // Agregar leyenda
                agregarLeyendaCalorFullscreen();
            } else {
                mostrarMensajeEnMapaCompleto('No hay pacientes con coordenadas válidas para mostrar');
            }
        } catch (error) {
            console.error('Error al actualizar mapa de calor en pantalla completa:', error);
            mostrarMensajeEnMapaCompleto('Error al cargar el mapa de calor');
        }
    }

    // Función para agregar capa de calor directamente desde los datos de calor
    function agregarCapaCalorDirectaFullscreen(heatData) {
        if (!mapFullscreen || !Array.isArray(heatData) || heatData.length === 0) {
            return;
        }
        
        try {
            // Limpiar capa existente
            if (heatLayerFullscreen) {
                mapFullscreen.removeLayer(heatLayerFullscreen);
                heatLayerFullscreen = null;
            }
            
            // Agregar nueva capa de calor
            heatLayerFullscreen = L.heatLayer(heatData, {
                radius: 35,
                blur: 25,
                maxZoom: 17,
                max: 1.0,
                minOpacity: 0.4,
                gradient: {
                    0.0: '#0000ff',
                    0.2: '#00ffff',
                    0.4: '#00ff00',
                    0.6: '#ffff00',
                    0.8: '#ff8000',
                    1.0: '#ff0000'
                }
            }).addTo(mapFullscreen);
            
            // Ajustar vista
            const bounds = heatData.map(point => [point[0], point[1]]);
            if (bounds.length > 1) {
                const group = new L.featureGroup();
                bounds.forEach(coord => {
                    L.marker(coord).addTo(group);
                });
                mapFullscreen.fitBounds(group.getBounds(), {
                    padding: [50, 50],
                    maxZoom: 14
                });
                mapFullscreen.removeLayer(group);
            } else if (bounds.length === 1) {
                mapFullscreen.setView(bounds[0], 15);
            }
            
            // Agregar leyenda
            agregarLeyendaCalorFullscreen();
            
        } catch (error) {
            console.error('Error al agregar capa de calor directa en pantalla completa:', error);
        }
    }

    // Función para agregar leyenda al mapa de pantalla completa
    function agregarLeyendaCalorFullscreen() {
        if (!mapFullscreen) return;
        
        const legend = L.control({ position: 'bottomright' });
        
        legend.onAdd = function(map) {
            const div = L.DomUtil.create('div', 'info legend custom-legend');
            
            div.innerHTML = `
                <h6 style="margin: 0 0 10px 0; color: #333; font-weight: bold;">
                    <i class="fas fa-thermometer-half"></i> Densidad de Pacientes
                </h6>
                <div style="display: flex; align-items: center; margin-bottom: 8px;">
                    <div style="width: 20px; height: 15px; background: #0000ff; margin-right: 10px; border-radius: 3px;"></div>
                    <span>Baja densidad</span>
                </div>
                <div style="display: flex; align-items: center; margin-bottom: 8px;">
                    <div style="width: 20px; height: 15px; background: #ffff00; margin-right: 10px; border-radius: 3px;"></div>
                    <span>Media densidad</span>
                </div>
                <div style="display: flex; align-items: center;">
                    <div style="width: 20px; height: 15px; background: #ff0000; margin-right: 10px; border-radius: 3px;"></div>
                    <span>Alta densidad</span>
                </div>
            `;
            
            return div;
        };
        
        legend.addTo(mapFullscreen);
    }

    // Función para mostrar mensaje en el mapa de pantalla completa
    function mostrarMensajeEnMapaCompleto(mensaje) {
        const mapContainer = document.getElementById('map-fullscreen');
        if (mapContainer) {
            mapContainer.innerHTML = `
                <div class="loading-fullscreen">
                    <div class="text-center">
                        <i class="fas fa-map fa-4x mb-4" style="color: #198754;"></i>
                        <h4>${mensaje}</h4>
                        <p class="text-muted">El mapa de calor se mostrará cuando haya datos disponibles</p>
                    </div>
                </div>
            `;
        }
    }

    // Inicialización mejorada del dashboard
    document.addEventListener('DOMContentLoaded', function() {
        console.log("=== INICIALIZANDO DASHBOARD ===");
        
        // Verificar que los elementos necesarios existen
        const elementosRequeridos = [
            '#map',
            '#total-pacientes', 
            '#total-visitas-mes', 
            '#promedio-visitas',
            '#tabla-auxiliares',
            '.sede-btn'
        ];
        
        let elementosFaltantes = [];
        elementosRequeridos.forEach(selector => {
            if (!document.querySelector(selector)) {
                elementosFaltantes.push(selector);
            }
        });
        
        if (elementosFaltantes.length > 0) {
            console.error('Elementos faltantes en el DOM:', elementosFaltantes);
            mostrarError(`Error: Faltan elementos en la página: ${elementosFaltantes.join(', ')}`);
            return;
        }
        
        console.log("Todos los elementos requeridos están presentes");
        
        // Agregar estilos CSS dinámicos para animaciones
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(10px); }
                to { opacity: 1; transform: translateY(0); }
            }
            
            .fade-in-row {
                animation: fadeIn 0.5s ease-out forwards;
                opacity: 0;
            }
        `;
        document.head.appendChild(style);
        
        // Configurar eventos de los botones de sede
        document.querySelectorAll('.sede-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const sedeId = this.getAttribute('data-sede-id');
                console.log(`Usuario seleccionó sede: ${sedeId}`);
                cargarDatosPorSede(sedeId);
            });
        });
        
        // Cargar datos iniciales con un pequeño retraso para asegurar que el DOM esté listo
        setTimeout(() => {
            console.log("Cargando datos iniciales para 'todas las sedes'");
            cargarDatosPorSede('todas');
        }, 500);
        
        // Agregar evento para redimensionar el mapa cuando cambia el tamaño de la ventana
        window.addEventListener('resize', function() {
            if (map) {
                setTimeout(() => {
                    map.invalidateSize();
                }, 200);
            }
            
            if (mapFullscreen && isFullscreenOpen) {
                setTimeout(() => {
                    mapFullscreen.invalidateSize();
                }, 200);
            }
        });
        
        // Agregar evento de teclado para cerrar con ESC
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape' && isFullscreenOpen) {
                cerrarMapaCompleto();
            }
        });
        
        console.log("=== DASHBOARD INICIALIZADO CORRECTAMENTE ===");
    });
    </script>
    @endsection
