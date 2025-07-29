<!-- resources/views/dashboard/index.blade.php -->
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
    #map {
        height: 500px;
        width: 100%;
        border-radius: 8px;
    }
    .sede-selector {
        background-color: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
    }
    .sede-btn {
        margin-right: 10px;
        margin-bottom: 10px;
    }
    .stats-card {
        transition: all 0.3s;
    }
    .stats-card:hover {
        transform: translateY(-5px);
    }
    .sede-btn.active {
        background-color: #198754;
        border-color: #198754;
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
                <!-- Modificar la sección de botones de sedes en dashboard.index.blade.php -->
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
    <!-- Mapa de Calor -->
    <div class="col-lg-8 mb-4">
        <div class="card shadow border-0 h-100">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-map me-2"></i>Mapa de Calor de Pacientes</h5>
            </div>
            <div class="card-body">
                <div id="map"></div>
            </div>
        </div>
    </div>
    
    <!-- Estadísticas Generales -->
    <div class="col-lg-4 mb-4">
        <div class="card shadow border-0 h-100">
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
                    <p class="text-muted">Promedio diario</p>
                </div>
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
                                <th>Visitas Pendientes</th>
                                <th>Total Asignadas</th>
                                <th>Progreso</th>
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
let visitasDiariasChart = null;
let visitasSedeChart = null;

// Función para cargar datos según la sede seleccionada
function cargarDatosPorSede(sedeId) {
    console.log(`Cargando datos para sede: ${sedeId}`);
    
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
        
        // Actualizar componentes
        actualizarMapaCalor(pacientesConCoordenadas);
        actualizarEstadisticas(data.estadisticas);
        actualizarTablaAuxiliares(data.auxiliares);
        actualizarGraficos(data.grafico_diario, data.grafico_sedes);
    })
    .catch(error => {
        console.error('Error al cargar datos:', error);
        ocultarIndicadorCarga();
        mostrarError(`Error al cargar datos: ${error.message}. Por favor, intente nuevamente.`);
    });
}

// Función para mostrar indicador de carga
function mostrarIndicadorCarga() {
    // Remover indicador existente si existe
    const indicadorExistente = document.getElementById('loading-indicator');
    if (indicadorExistente) {
        indicadorExistente.remove();
    }
    
    const loadingIndicator = document.createElement('div');
    loadingIndicator.className = 'alert alert-info text-center';
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

// Función mejorada para actualizar el mapa de calor
function actualizarMapaCalor(pacientes) {
    console.log("=== INICIANDO ACTUALIZACIÓN DEL MAPA ===");
    
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
                
                // Agregar capa base
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                    maxZoom: 19
                }).addTo(map);
                
                console.log("Capa base agregada");
                
                // Procesar pacientes
                if (!Array.isArray(pacientes) || pacientes.length === 0) {
                    console.warn('No hay pacientes válidos para mostrar en el mapa');
                    mostrarMensajeEnMapa('No hay pacientes con coordenadas válidas para mostrar');
                    return;
                }
                
                console.log(`Procesando ${pacientes.length} pacientes para el mapa`);
                
                const heatData = [];
                const markers = [];
                
                pacientes.forEach((paciente, index) => {
                    const lat = parseFloat(String(paciente.latitud).replace(',', '.'));
                    const lng = parseFloat(String(paciente.longitud).replace(',', '.'));
                    
                    console.log(`Paciente ${index + 1}: ${paciente.nombre || 'Sin nombre'} - Lat: ${lat}, Lng: ${lng}`);
                    
                    // Datos para heatmap
                    heatData.push([lat, lng, 1.0]); // Intensidad 1.0
                    
                    // Crear marcador
                    const marker = L.marker([lat, lng], {
                        title: `${paciente.nombre || 'Paciente'} ${paciente.apellido || ''}`
                    });
                    
                    // Popup con información del paciente
                    const popupContent = `
                        <div style="min-width: 200px;">
                            <h6 class="mb-2 text-success">
                                <i class="fas fa-user me-1"></i>
                                ${paciente.nombre || 'Sin nombre'} ${paciente.apellido || ''}
                            </h6>
                            <small class="text-muted">
                                <strong>ID:</strong> ${paciente.identificacion || 'No disponible'}<br>
                                <strong>Coordenadas:</strong> ${lat.toFixed(6)}, ${lng.toFixed(6)}
                                ${paciente.direccion ? `<br><strong>Dirección:</strong> ${paciente.direccion}` : ''}
                            </small>
                        </div>
                    `;
                    
                    marker.bindPopup(popupContent);
                    markers.push(marker);
                });
                
                console.log(`Se crearon ${markers.length} marcadores válidos`);
                
                if (markers.length > 0) {
                    // Agregar marcadores al mapa
                    const markerGroup = L.featureGroup(markers);
                    markerGroup.addTo(map);
                    
                    // Agregar capa de calor solo si hay suficientes puntos
                    if (heatData.length >= 2) {
                        console.log("Agregando capa de calor con", heatData.length, "puntos");
                        L.heatLayer(heatData, {
                            radius: 25,
                            blur: 15,
                            maxZoom: 17,
                            max: 1.0,
                            gradient: {
                                0.0: 'blue',
                                0.5: 'yellow', 
                                1.0: 'red'
                            }
                        }).addTo(map);
                    }
                    
                    // Ajustar vista del mapa
                    if (markers.length === 1) {
                        console.log("Un solo marcador - centrando vista");
                        map.setView(markers[0].getLatLng(), 16);
                    } else {
                        console.log("Múltiples marcadores - ajustando vista con fitBounds");
                        map.fitBounds(markerGroup.getBounds(), {
                            padding: [20, 20],
                            maxZoom: 16
                        });
                    }
                    
                    // Mensaje de éxito
                    console.log(`✓ Mapa actualizado exitosamente con ${markers.length} pacientes`);
                    
                } else {
                    console.warn('No se pudieron crear marcadores válidos');
                    mostrarMensajeEnMapa('No hay pacientes con coordenadas válidas');
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

// Función para mostrar mensaje en el mapa cuando no hay datos
function mostrarMensajeEnMapa(mensaje) {
    const mapContainer = document.getElementById('map');
    if (mapContainer) {
        mapContainer.innerHTML = `
            <div class="d-flex align-items-center justify-content-center h-100 bg-light">
                <div class="text-center text-muted">
                    <i class="fas fa-map-marker-alt fa-3x mb-3"></i>
                    <h5>${mensaje}</h5>
                    <p>Los pacientes aparecerán aquí cuando tengan coordenadas válidas</p>
                </div>
            </div>
        `;
    }
}

// Función para actualizar estadísticas
function actualizarEstadisticas(estadisticas) {
    document.getElementById('total-pacientes').textContent = estadisticas.total_pacientes || 0;
    document.getElementById('total-visitas-mes').textContent = estadisticas.visitas_mes || 0;
    document.getElementById('promedio-visitas').textContent = estadisticas.promedio_diario || 0;
}

// Función para actualizar tabla de auxiliares
function actualizarTablaAuxiliares(auxiliares) {
    console.log('Actualizando tabla de auxiliares con datos:', auxiliares);
    
    const tbody = document.querySelector('#tabla-auxiliares tbody');
    if (!tbody) {
        console.error('No se encontró el tbody de la tabla de auxiliares');
        return;
    }
    
    tbody.innerHTML = '';
    
    if (!Array.isArray(auxiliares) || auxiliares.length === 0) {
        console.warn('No hay auxiliares para mostrar');
        tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">No hay datos de auxiliares disponibles</td></tr>';
        return;
    }
    
    console.log(`Mostrando ${auxiliares.length} auxiliares en la tabla`);
    
    auxiliares.forEach((auxiliar, index) => {
        console.log(`Procesando auxiliar ${index}:`, auxiliar);
        
        const progreso = auxiliar.total_asignadas > 0 
            ? Math.round((auxiliar.visitas_realizadas / auxiliar.total_asignadas) * 100) 
            : 0;
        
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${auxiliar.nombre || 'Sin nombre'}</td>
            <td>${auxiliar.sede || 'Sin sede'}</td>
            <td><span class="badge bg-success">${auxiliar.visitas_realizadas || 0}</span></td>
            <td><span class="badge bg-warning">${auxiliar.visitas_pendientes || 0}</span></td>
            <td><span class="badge bg-info">${auxiliar.total_asignadas || 0}</span></td>
            <td>
                <div class="progress" style="height: 20px;">
                    <div class="progress-bar bg-success" role="progressbar" 
                         style="width: ${progreso}%" 
                         aria-valuenow="${progreso}" aria-valuemin="0" aria-valuemax="100">
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
                    pointBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Tendencia de Visitas Diarias'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
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
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                    },
                    title: {
                        display: true,
                        text: 'Distribución por Sede'
                    }
                }
            }
        });
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
    
    console.log("=== DASHBOARD INICIALIZADO CORRECTAMENTE ===");
});


</script>
@endsection
