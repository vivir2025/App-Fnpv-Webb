/**
 * ============================================
 * DASHBOARD DE VISITAS DOMICILIARIAS
 * Gestión de mapas, gráficos y estadísticas
 * ============================================
 */

// Variables globales
let map = null;
let mapFullscreen = null;
let visitasDiariasChart = null;
let visitasSedeChart = null;
let sedeActual = 'todas';
let isFullscreenOpen = false;
let heatLayer = null;
let heatLayerFullscreen = null;
let currentHeatData = [];

// Filtros de fecha
let fechaInicio = null;
let fechaFin = null;

/**
 * ============================================
 * FUNCIONES DE CARGA DE DATOS
 * ============================================
 */

/**
 * Cargar datos según la sede y rango de fechas seleccionados
 */
function cargarDatosPorSede(sedeId, aplicarFiltroFecha = false) {
    // Validar permisos antes de cargar
    if (window.permisos && window.permisos.esJefe) {
        // Si es jefe, solo puede ver su sede
        if (sedeId !== window.permisos.sedeId && sedeId !== 'todas') {
            mostrarError('No tiene permisos para ver esta sede');
            return;
        }
        sedeId = window.permisos.sedeId;
    }
    
    sedeActual = sedeId;
    
    // Actualizar botones activos
    document.querySelectorAll('.sede-btn').forEach(btn => {
        btn.classList.remove('active');
        // Si es jefe, mantener deshabilitados los otros botones
        if (window.permisos && window.permisos.esJefe && btn.getAttribute('data-sede-id') !== window.permisos.sedeId) {
            btn.disabled = true;
        }
    });
    
    const botonActivo = document.querySelector(`[data-sede-id="${sedeId}"]`);
    if (botonActivo) {
        botonActivo.classList.add('active');
    }
    
    mostrarIndicadorCarga();
    
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    // Construir URL con parámetros
    let url = `/api/dashboard/datos?sede_id=${sedeId}`;
    
    // Agregar filtros de fecha si están activos
    if (aplicarFiltroFecha && fechaInicio && fechaFin) {
        url += `&fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}`;
    }
    
    fetch(url, {
        method: 'GET',
        headers: {
            'Authorization': `Bearer ${window.apiToken}`,
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': token
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`Error HTTP: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        ocultarIndicadorCarga();
        
        // Verificar estructura de datos
        if (!data.pacientes) data.pacientes = [];
        if (!data.estadisticas) data.estadisticas = { total_pacientes: 0, visitas_mes: 0, promedio_diario: 0 };
        if (!data.grafico_diario) data.grafico_diario = [];
        if (!data.grafico_sedes) data.grafico_sedes = [];
        if (!data.auxiliares) data.auxiliares = [];
        
        // Filtrar pacientes con coordenadas válidas
        const pacientesConCoordenadas = filtrarPacientesConCoordenadas(data.pacientes);
        
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

/**
 * Aplicar filtro de fechas
 */
function aplicarFiltroFecha() {
    const inputFechaInicio = document.getElementById('fecha-inicio');
    const inputFechaFin = document.getElementById('fecha-fin');
    
    if (!inputFechaInicio || !inputFechaFin) {
        console.error('Inputs de fecha no encontrados');
        return;
    }
    
    fechaInicio = inputFechaInicio.value;
    fechaFin = inputFechaFin.value;
    
    if (!fechaInicio || !fechaFin) {
        mostrarError('Por favor seleccione ambas fechas');
        return;
    }
    
    if (new Date(fechaInicio) > new Date(fechaFin)) {
        mostrarError('La fecha de inicio no puede ser mayor que la fecha fin');
        return;
    }
    
    cargarDatosPorSede(sedeActual, true);
}

/**
 * Limpiar filtros de fecha
 */
function limpiarFiltroFecha() {
    const inputFechaInicio = document.getElementById('fecha-inicio');
    const inputFechaFin = document.getElementById('fecha-fin');
    
    if (inputFechaInicio) inputFechaInicio.value = '';
    if (inputFechaFin) inputFechaFin.value = '';
    
    fechaInicio = null;
    fechaFin = null;
    
    cargarDatosPorSede(sedeActual, false);
}

/**
 * ============================================
 * FUNCIONES DE MAPA
 * ============================================
 */

/**
 * Filtrar pacientes con coordenadas válidas
 */
function filtrarPacientesConCoordenadas(pacientes) {
    if (!Array.isArray(pacientes)) {
        return [];
    }
    
    return pacientes.filter(paciente => {
        if (!paciente.latitud || !paciente.longitud) {
            return false;
        }
        
        const lat = parseFloat(String(paciente.latitud).replace(',', '.'));
        const lng = parseFloat(String(paciente.longitud).replace(',', '.'));
        
        return !isNaN(lat) && !isNaN(lng) && 
               lat !== 0 && lng !== 0 && 
               Math.abs(lat) > 0.001 && Math.abs(lng) > 0.001 &&
               lat >= -90 && lat <= 90 && 
               lng >= -180 && lng <= 180;
    });
}

/**
 * Actualizar mapa de calor principal
 */
function actualizarMapaCalor(pacientes) {
    const mapContainer = document.getElementById('map');
    if (!mapContainer) {
        console.error('Contenedor del mapa (#map) no encontrado');
        mostrarError('Error: No se pudo encontrar el contenedor del mapa');
        return;
    }
    
    try {
        if (map && map.remove) {
            map.remove();
            map = null;
        }
        
        mapContainer.innerHTML = '';
        
        setTimeout(() => {
            try {
                map = L.map('map', {
                    center: [4.6097, -74.0817],
                    zoom: 11,
                    zoomControl: true,
                    attributionControl: true
                });
                
                const satelitalLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                    attribution: 'Tiles &copy; Esri',
                    maxZoom: 19
                });
                
                const callesLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap contributors',
                    maxZoom: 19
                });
                
                satelitalLayer.addTo(map);
                
                const baseMaps = {
                    "Vista Satelital": satelitalLayer,
                    "Calles": callesLayer
                };
                
                L.control.layers(baseMaps).addTo(map);
                
                if (!Array.isArray(pacientes) || pacientes.length === 0) {
                    mostrarMensajeEnMapa('No hay pacientes con coordenadas válidas para mostrar');
                    return;
                }
                
                const heatData = [];
                const bounds = [];
                
                pacientes.forEach((paciente) => {
                    const lat = parseFloat(String(paciente.latitud).replace(',', '.'));
                    const lng = parseFloat(String(paciente.longitud).replace(',', '.'));
                    
                    heatData.push([lat, lng, 1.0]);
                    bounds.push([lat, lng]);
                });
                
                currentHeatData = heatData;
                
                if (heatData.length > 0) {
                    heatLayer = L.heatLayer(heatData, {
                        radius: 30,
                        blur: 20,
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
                    }).addTo(map);
                    
                    if (bounds.length === 1) {
                        map.setView(bounds[0], 16);
                    } else {
                        const group = new L.featureGroup();
                        bounds.forEach(coord => {
                            L.marker(coord).addTo(group);
                        });
                        map.fitBounds(group.getBounds(), {
                            padding: [30, 30],
                            maxZoom: 15
                        });
                        map.removeLayer(group);
                    }
                    
                    agregarLeyendaCalor();
                } else {
                    mostrarMensajeEnMapa('No hay pacientes con coordenadas válidas');
                }
                
                setTimeout(() => {
                    if (map) {
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

/**
 * Agregar leyenda al mapa
 */
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

/**
 * Mostrar mensaje en el mapa
 */
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

/**
 * ============================================
 * MAPA EN PANTALLA COMPLETA
 * ============================================
 */

/**
 * Toggle mapa completo
 */
function toggleMapaCompleto() {
    const modal = document.getElementById('modalMapaCompleto');
    const bootstrapModal = new bootstrap.Modal(modal);
    
    bootstrapModal.show();
    
    modal.addEventListener('shown.bs.modal', function() {
        crearMapaCompleto();
        isFullscreenOpen = true;
    });
    
    modal.addEventListener('hidden.bs.modal', function() {
        if (mapFullscreen) {
            mapFullscreen.remove();
            mapFullscreen = null;
        }
        isFullscreenOpen = false;
    });
}

/**
 * Cerrar mapa completo
 */
function cerrarMapaCompleto() {
    const modal = document.getElementById('modalMapaCompleto');
    const bootstrapModal = bootstrap.Modal.getInstance(modal);
    if (bootstrapModal) {
        bootstrapModal.hide();
    }
}

/**
 * Crear mapa en pantalla completa
 */
function crearMapaCompleto() {
    try {
        if (mapFullscreen) {
            mapFullscreen.remove();
            mapFullscreen = null;
        }
        
        mapFullscreen = L.map('map-fullscreen', {
            center: [4.6097, -74.0817],
            zoom: 12,
            zoomControl: true,
            attributionControl: true
        });
        
        const satelitalLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
            attribution: 'Tiles &copy; Esri',
            maxZoom: 19
        });
        
        const callesLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors',
            maxZoom: 19
        });
        
        satelitalLayer.addTo(mapFullscreen);
        
        const baseMaps = {
            "Vista Satelital": satelitalLayer,
            "Calles": callesLayer
        };
        
        L.control.layers(baseMaps).addTo(mapFullscreen);
        
        if (window.datosMapaActuales && window.datosMapaActuales.length > 0) {
            actualizarMapaCalorFullscreen(window.datosMapaActuales);
        } else if (currentHeatData && currentHeatData.length > 0) {
            agregarCapaCalorDirectaFullscreen(currentHeatData);
        } else {
            mostrarMensajeEnMapaCompleto('No hay datos disponibles');
        }
        
        setTimeout(() => {
            if (mapFullscreen) {
                mapFullscreen.invalidateSize();
            }
        }, 300);
        
    } catch (error) {
        console.error('Error al crear mapa de pantalla completa:', error);
        mostrarMensajeEnMapaCompleto('Error al cargar el mapa');
    }
}

/**
 * Actualizar mapa de calor en pantalla completa
 */
function actualizarMapaCalorFullscreen(pacientes) {
    if (!mapFullscreen || !Array.isArray(pacientes)) {
        return;
    }
    
    try {
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
            
            currentHeatData = heatData;
            
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
            
            agregarLeyendaCalorFullscreen();
        } else {
            mostrarMensajeEnMapaCompleto('No hay pacientes con coordenadas válidas');
        }
    } catch (error) {
        console.error('Error al actualizar mapa fullscreen:', error);
        mostrarMensajeEnMapaCompleto('Error al cargar el mapa de calor');
    }
}

/**
 * Agregar capa de calor directa en fullscreen
 */
function agregarCapaCalorDirectaFullscreen(heatData) {
    if (!mapFullscreen || !Array.isArray(heatData) || heatData.length === 0) {
        return;
    }
    
    try {
        if (heatLayerFullscreen) {
            mapFullscreen.removeLayer(heatLayerFullscreen);
            heatLayerFullscreen = null;
        }
        
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
        
        agregarLeyendaCalorFullscreen();
        
    } catch (error) {
        console.error('Error al agregar capa de calor directa:', error);
    }
}

/**
 * Agregar leyenda al mapa fullscreen
 */
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

/**
 * Mostrar mensaje en mapa completo
 */
function mostrarMensajeEnMapaCompleto(mensaje) {
    const mapContainer = document.getElementById('map-fullscreen');
    if (mapContainer) {
        mapContainer.innerHTML = `
            <div class="loading-fullscreen">
                <div class="text-center">
                    <i class="fas fa-map fa-4x mb-4" style="color: #198754;"></i>
                    <h4>${mensaje}</h4>
                    <p class="text-muted">El mapa se mostrará cuando haya datos disponibles</p>
                </div>
            </div>
        `;
    }
}

/**
 * ============================================
 * FUNCIONES DE ACTUALIZACIÓN DE UI
 * ============================================
 */

/**
 * Actualizar estadísticas
 */
function actualizarEstadisticas(estadisticas) {
    const totalPacientes = parseInt(estadisticas.total_pacientes) || 0;
    const visitasMes = parseInt(estadisticas.visitas_mes) || 0;
    
    animarContador('total-pacientes', totalPacientes);
    animarContador('total-visitas-mes', visitasMes);
    
    const porcentajeMeta = (visitasMes / 80 * 100).toFixed(1);
    animarContador('promedio-visitas', porcentajeMeta, '%');
}

/**
 * Animar contadores
 */
function animarContador(elementId, valorFinal, sufijo = '') {
    const elemento = document.getElementById(elementId);
    if (!elemento) return;
    
    const valorInicial = parseInt(elemento.textContent.replace(/[^0-9.-]+/g, '')) || 0;
    const duracion = 1000;
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

/**
 * Actualizar tabla de auxiliares
 */
function actualizarTablaAuxiliares(auxiliares) {
    const tbody = document.querySelector('#tabla-auxiliares tbody');
    if (!tbody) {
        console.error('No se encontró el tbody de la tabla');
        return;
    }
    
    tbody.innerHTML = '';
    
    if (!Array.isArray(auxiliares) || auxiliares.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-4"><i class="fas fa-user-nurse fa-2x mb-3 d-block"></i>No hay datos de auxiliares disponibles.</td></tr>';
        return;
    }
    
    auxiliares.forEach((auxiliar, index) => {
        const nombreAuxiliar = auxiliar.nombre || `Auxiliar #${auxiliar.id}`;
        const nombreSede = auxiliar.sede || 'Sin sede';
        const visitasRealizadas = parseInt(auxiliar.visitas_realizadas || 0);
        
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

/**
 * Actualizar gráficos
 */
function actualizarGraficos(datosDiarios, datosSedes) {
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
                            font: { size: 14 },
                            usePointStyle: true
                        }
                    },
                    title: {
                        display: true,
                        text: 'Tendencia de Visitas Diarias',
                        font: { size: 16, weight: 'bold' },
                        padding: { top: 10, bottom: 20 }
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
                            font: { size: 12 }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        ticks: {
                            font: { size: 12 }
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
                            font: { size: 13 },
                            padding: 15
                        }
                    },
                    title: {
                        display: true,
                        text: 'Distribución por Sede',
                        font: { size: 16, weight: 'bold' },
                        padding: { top: 10, bottom: 20 }
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
    }
}

/**
 * ============================================
 * FUNCIONES DE UI - INDICADORES
 * ============================================
 */

/**
 * Mostrar indicador de carga (Skeleton Loader)
 */
function mostrarIndicadorCarga() {
    const skeletonLoader = document.getElementById('skeleton-loader');
    const dashboardContent = document.getElementById('dashboard-content');
    
    if (skeletonLoader) {
        skeletonLoader.style.display = 'block';
    }
    if (dashboardContent) {
        dashboardContent.style.display = 'none';
    }
}

/**
 * Ocultar indicador de carga (Skeleton Loader)
 */
function ocultarIndicadorCarga() {
    const skeletonLoader = document.getElementById('skeleton-loader');
    const dashboardContent = document.getElementById('dashboard-content');
    
    if (skeletonLoader && dashboardContent) {
        // Primero mostrar el contenido real
        dashboardContent.style.display = 'block';
        dashboardContent.style.opacity = '0';
        
        // Animar la transición
        requestAnimationFrame(() => {
            skeletonLoader.style.opacity = '0';
            dashboardContent.style.transition = 'opacity 0.3s ease-in';
            dashboardContent.style.opacity = '1';
            
            // Ocultar skeleton después de la transición
            setTimeout(() => {
                skeletonLoader.style.display = 'none';
                skeletonLoader.style.opacity = '1';
            }, 300);
        });
    }
}

/**
 * Mostrar errores
 */
function mostrarError(mensaje) {
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
    
    setTimeout(() => {
        const alert = document.getElementById('error-alert');
        if (alert) {
            alert.remove();
        }
    }, 8000);
}

/**
 * ============================================
 * INICIALIZACIÓN
 * ============================================
 */

document.addEventListener('DOMContentLoaded', function() {
    // Verificar elementos requeridos
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
        console.error('Elementos faltantes:', elementosFaltantes);
        mostrarError(`Error: Faltan elementos: ${elementosFaltantes.join(', ')}`);
        return;
    }
    
    // Agregar estilos para animaciones
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
    
    // Configurar eventos de botones de sede
    document.querySelectorAll('.sede-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const sedeId = this.getAttribute('data-sede-id');
            cargarDatosPorSede(sedeId, fechaInicio && fechaFin);
        });
    });
    
    // Configurar eventos de filtros de fecha
    const btnFiltrar = document.getElementById('btn-filtrar-fecha');
    if (btnFiltrar) {
        btnFiltrar.addEventListener('click', aplicarFiltroFecha);
    }
    
    const btnLimpiar = document.getElementById('btn-limpiar-fecha');
    if (btnLimpiar) {
        btnLimpiar.addEventListener('click', limpiarFiltroFecha);
    }
    
    // Cargar datos iniciales
    setTimeout(() => {
        cargarDatosPorSede('todas');
    }, 500);
    
    // Eventos de redimensionamiento
    window.addEventListener('resize', function() {
        if (map) {
            setTimeout(() => map.invalidateSize(), 200);
        }
        
        if (mapFullscreen && isFullscreenOpen) {
            setTimeout(() => mapFullscreen.invalidateSize(), 200);
        }
    });
    
    // Evento ESC para cerrar modal
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape' && isFullscreenOpen) {
            cerrarMapaCompleto();
        }
    });
});


/**
 * ============================================
 * BÚSQUEDA POR TERRITORIO (SOLO COORDENADAS)
 * ============================================
 */

let territorioActual = null;
let marcadoresPacientes = [];
let circuloBusqueda = null;
let todosLosPacientes = []; // Cache de todos los pacientes

/**
 * Inicializar buscador de territorios
 */
function inicializarBuscadorTerritorio() {
    const inputBuscar = document.getElementById('buscar-territorio');
    const btnBuscar = document.getElementById('btn-buscar-territorio');
    const btnLimpiar = document.getElementById('limpiar-busqueda');
    const btnLimpiarTerritorio = document.getElementById('limpiar-territorio');
    
    if (!inputBuscar) return;
    
    // Buscar al hacer clic
    if (btnBuscar) {
        btnBuscar.addEventListener('click', function() {
            const termino = inputBuscar.value.trim();
            if (termino.length >= 3) {
                buscarPorTerritorio(termino);
            } else {
                mostrarError('Por favor ingrese al menos 3 caracteres');
            }
        });
    }
    
    // Buscar al presionar Enter
    inputBuscar.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            const termino = this.value.trim();
            if (termino.length >= 3) {
                buscarPorTerritorio(termino);
            }
        }
    });
    
    // Limpiar búsqueda
    if (btnLimpiar) {
        btnLimpiar.addEventListener('click', function() {
            inputBuscar.value = '';
            limpiarTerritorioSeleccionado();
        });
    }
    
    // Limpiar territorio seleccionado
    if (btnLimpiarTerritorio) {
        btnLimpiarTerritorio.addEventListener('click', limpiarTerritorioSeleccionado);
    }
}

/**
 * Buscar por territorio usando Nominatim
 */
function buscarPorTerritorio(termino) {
    mostrarLoadingTerritorio();
    
    // Agregar "Cauca, Colombia" para mejorar la búsqueda
    const query = `${termino}, Cauca, Colombia`;
    
    // Usar Nominatim de OpenStreetMap
    fetch(`https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(query)}&format=json&limit=1&countrycodes=co`, {
        headers: {
            'User-Agent': 'VisitasDomiciliarias/1.0'
        }
    })
    .then(response => response.json())
    .then(data => {
        ocultarLoadingTerritorio();
        
        if (data && data.length > 0) {
            const lugar = data[0];
            const lat = parseFloat(lugar.lat);
            const lon = parseFloat(lugar.lon);
            const nombre = lugar.display_name;
            
            territorioActual = {
                nombre: nombre,
                lat: lat,
                lon: lon,
                radio: 5 // Radio en kilómetros
            };
            
            mostrarTerritorioSeleccionado(nombre);
            filtrarPacientesPorCoordenadas(lat, lon, 5);
            
        } else {
            mostrarError('No se encontró la ubicación. Intente con otro nombre.');
        }
    })
    .catch(error => {
        console.error('Error al buscar territorio:', error);
        ocultarLoadingTerritorio();
        mostrarError('Error al buscar la ubicación. Intente nuevamente.');
    });
}

/**
 * Filtrar pacientes por coordenadas (radio en km)
 */
function filtrarPacientesPorCoordenadas(latCentro, lonCentro, radioKm) {
    if (!window.datosMapaActuales || window.datosMapaActuales.length === 0) {
        mostrarError('No hay datos de pacientes disponibles');
        return;
    }
    
    // Filtrar pacientes dentro del radio
    const pacientesFiltrados = window.datosMapaActuales.filter(paciente => {
        const lat = parseFloat(String(paciente.latitud).replace(',', '.'));
        const lon = parseFloat(String(paciente.longitud).replace(',', '.'));
        
        if (isNaN(lat) || isNaN(lon)) return false;
        
        const distancia = calcularDistancia(latCentro, lonCentro, lat, lon);
        return distancia <= radioKm;
    });
    
    // Actualizar contador
    const badge = document.getElementById('total-pacientes-territorio');
    if (badge) {
        badge.innerHTML = `<i class="fas fa-users me-1"></i>${pacientesFiltrados.length} paciente${pacientesFiltrados.length !== 1 ? 's' : ''}`;
    }
    
    // Limpiar mapa
    limpiarMarcadoresPacientes();
    
    if (pacientesFiltrados.length === 0) {
        mostrarError('No se encontraron pacientes en esta área. Intente ampliar el radio de búsqueda.');
        return;
    }
    
    // Dibujar círculo de búsqueda
    dibujarCirculoBusqueda(latCentro, lonCentro, radioKm);
    
    // Agregar marcadores
    agregarMarcadoresPacientes(pacientesFiltrados);
    
    // Centrar mapa
    map.setView([latCentro, lonCentro], 13);
}

/**
 * Calcular distancia entre dos puntos (Haversine)
 */
function calcularDistancia(lat1, lon1, lat2, lon2) {
    const R = 6371; // Radio de la Tierra en km
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    
    const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
              Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
              Math.sin(dLon/2) * Math.sin(dLon/2);
    
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    const distancia = R * c;
    
    return distancia;
}

/**
 * Dibujar círculo de búsqueda en el mapa
 */
function dibujarCirculoBusqueda(lat, lon, radioKm) {
    if (!map) return;
    
    // Limpiar círculo anterior
    if (circuloBusqueda) {
        map.removeLayer(circuloBusqueda);
    }
    
    // Crear nuevo círculo
    circuloBusqueda = L.circle([lat, lon], {
        color: '#667eea',
        fillColor: '#667eea',
        fillOpacity: 0.1,
        radius: radioKm * 1000, // Convertir km a metros
        weight: 2,
        dashArray: '10, 5'
    }).addTo(map);
    
    // Agregar popup al círculo
    circuloBusqueda.bindPopup(`
        <div class="text-center p-2">
            <i class="fas fa-bullseye text-primary fa-2x mb-2"></i>
            <h6>Área de búsqueda</h6>
            <p class="mb-0 small">Radio: ${radioKm} km</p>
        </div>
    `);
    
    // Agregar marcador central
    L.marker([lat, lon], {
        icon: L.divIcon({
            className: 'custom-div-icon',
            html: `<div style="background-color: #667eea; width: 30px; height: 30px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.3);"></div>`,
            iconSize: [30, 30],
            iconAnchor: [15, 15]
        })
    }).addTo(map).bindPopup(`
        <div class="text-center p-2">
            <i class="fas fa-map-marker-alt text-danger fa-2x mb-2"></i>
            <h6>Centro de búsqueda</h6>
            <p class="mb-0 small">${territorioActual.nombre}</p>
        </div>
    `);
}

/**
 * Agregar marcadores de pacientes
 */
function agregarMarcadoresPacientes(pacientes) {
    if (!map) return;
    
    // Ocultar capa de calor si existe
    if (heatLayer) {
        map.removeLayer(heatLayer);
    }
    
    pacientes.forEach((paciente, index) => {
        const lat = parseFloat(String(paciente.latitud).replace(',', '.'));
        const lon = parseFloat(String(paciente.longitud).replace(',', '.'));
        
        if (isNaN(lat) || isNaN(lon)) return;
        
        // Calcular distancia al centro
        const distancia = territorioActual ? 
            calcularDistancia(territorioActual.lat, territorioActual.lon, lat, lon).toFixed(2) : 
            null;
        
        // Crear marcador
        const marker = L.circleMarker([lat, lon], {
            radius: 8,
            fillColor: '#198754',
            color: '#ffffff',
            weight: 2,
            opacity: 1,
            fillOpacity: 0.8
        });
        
        // Popup del marcador
        const popupContent = `
            <div class="p-2" style="min-width: 220px;">
                <h6 class="mb-2 pb-2" style="border-bottom: 2px solid #198754;">
                    <i class="fas fa-user-injured text-success me-1"></i>
                    ${paciente.primer_nombre || ''} ${paciente.primer_apellido || ''}
                </h6>
                <p class="mb-1 small">
                    <strong><i class="fas fa-id-card me-1"></i>ID:</strong> ${paciente.identificacion || 'N/A'}
                </p>
                <p class="mb-1 small">
                    <strong><i class="fas fa-hospital me-1"></i>Sede:</strong> ${paciente.sede?.nombre || 'Sin sede'}
                </p>
                ${distancia ? `
                <p class="mb-0 small text-muted">
                    <i class="fas fa-route me-1"></i>
                    <strong>Distancia:</strong> ${distancia} km del centro
                </p>
                ` : ''}
                <p class="mb-0 small text-muted mt-1">
                    <i class="fas fa-map-marker-alt me-1"></i>
                    ${lat.toFixed(6)}, ${lon.toFixed(6)}
                </p>
            </div>
        `;
        
        marker.bindPopup(popupContent);
        
        // Animación de entrada
        setTimeout(() => {
            marker.addTo(map);
        }, index * 50);
        
        marcadoresPacientes.push(marker);
    });
}

/**
 * Limpiar marcadores de pacientes
 */
function limpiarMarcadoresPacientes() {
    if (!map) return;
    
    marcadoresPacientes.forEach(marker => {
        map.removeLayer(marker);
    });
    marcadoresPacientes = [];
    
    // Limpiar círculo de búsqueda
    if (circuloBusqueda) {
        map.removeLayer(circuloBusqueda);
        circuloBusqueda = null;
    }
}

/**
 * Mostrar territorio seleccionado
 */
function mostrarTerritorioSeleccionado(nombre) {
    const div = document.getElementById('territorio-seleccionado');
    const spanNombre = document.getElementById('nombre-territorio-seleccionado');
    
    if (div && spanNombre) {
        spanNombre.textContent = nombre;
        div.style.display = 'block';
    }
}

/**
 * Limpiar territorio seleccionado
 */
function limpiarTerritorioSeleccionado() {
    territorioActual = null;
    
    const div = document.getElementById('territorio-seleccionado');
    if (div) {
        div.style.display = 'none';
    }
    
    const input = document.getElementById('buscar-territorio');
    if (input) {
        input.value = '';
    }
    
    // Limpiar marcadores
    limpiarMarcadoresPacientes();
    
    // Restaurar vista original
    if (window.datosMapaActuales && window.datosMapaActuales.length > 0) {
        actualizarMapaCalor(window.datosMapaActuales);
    }
}

/**
 * Mostrar loading
 */
function mostrarLoadingTerritorio() {
    const loading = document.getElementById('loading-territorio');
    if (loading) {
        loading.style.display = 'block';
    }
}

/**
 * Ocultar loading
 */
function ocultarLoadingTerritorio() {
    const loading = document.getElementById('loading-territorio');
    if (loading) {
        loading.style.display = 'none';
    }
}

/**
 * ============================================
 * INICIALIZACIÓN
 * ============================================
 */

document.addEventListener('DOMContentLoaded', function() {
    // Inicializar buscador de territorio
    inicializarBuscadorTerritorio();
    
    // Determinar sede inicial según permisos
    let sedeInicial = 'todas';
    if (window.permisos && window.permisos.esJefe) {
        // Si es jefe, cargar automáticamente su sede
        sedeInicial = window.permisos.sedeId;
    }
    
    // Mostrar skeleton loader y cargar datos inmediatamente
    mostrarIndicadorCarga();
    cargarDatosPorSede(sedeInicial);
});
