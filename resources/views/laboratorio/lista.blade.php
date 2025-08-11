    @extends('layouts.app')
    
    @section('title', 'Lista de Envíos de Muestras')
    
    @section('content')
    <div class="container-fluid">
        <div class="row mb-3">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <h4>
                        <i class="fas fa-vial text-primary me-2"></i>
                        @if(isset($sede))
                            Envíos de Muestras - {{ $sede['nombre'] ?? $sede['nombresede'] ?? 'Sede' }}
                        @else
                            Todos los Envíos de Muestras
                        @endif
                    </h4>
                    <div>
                        <a href="{{ route('laboratorio.index') }}" class="btn btn-outline-secondary me-2">
                            <i class="fas fa-arrow-left me-1"></i> Volver
                        </a>
                    </div>
                </div>
            </div>
        </div>
    
        <!-- Filtros -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <label for="filtroFecha" class="form-label">Buscar por fecha específica (opcional):</label>
                                <input type="date" id="filtroFecha" class="form-control" placeholder="Seleccionar fecha">
                                <small class="text-muted">Deja vacío para filtrar solo por mes</small>
                            </div>
                            <div class="col-md-4">
                                <label for="filtroMes" class="form-label">Filtrar por mes:</label>
                                <select id="filtroMes" class="form-select">
                                    <option value="actual">Mes actual</option>
                                    <option value="todos">Todos los meses</option>
                                </select>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button id="limpiarFiltros" class="btn btn-outline-secondary">
                                    <i class="fas fa-eraser me-1"></i> Limpiar filtros
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        @if(count($envios) > 0)
                            <div class="table-responsive">
                                <table class="table table-hover" id="tablaEnvios">
                                    <thead>
                                        <tr>
                                            <th>Código</th>
                                            <th>Fecha</th>
                                            <th>Sede</th>
                                            <th>Estado</th>
                                            <th>Fecha Salida</th>
                                            <th>Fecha Llegada</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($envios as $envio)
                                            <tr data-fecha="{{ $envio['fecha'] ?? '' }}" data-envio-id="{{ $envio['id'] }}">
                                                <td>{{ $envio['codigo'] ?? 'N/A' }}</td>
                                                <td>{{ isset($envio['fecha']) ? \Carbon\Carbon::parse($envio['fecha'])->format('d/m/Y') : 'N/A' }}</td>
                                                <td>{{ $envio['sede']['nombre'] ?? $envio['sede']['nombresede'] ?? 'N/A' }}</td>
                                                <td class="estado-column">
                                                    @php
                                                        $enviadoPorCorreo = isset($envio['enviado_por_correo']) && $envio['enviado_por_correo'];
                                                        $fechaLlegada = isset($envio['fecha_llegada']) && !empty($envio['fecha_llegada']);
                                                        $fechaSalida = isset($envio['fecha_salida']) && !empty($envio['fecha_salida']);
                                                    @endphp
    
                                                    @if($enviadoPorCorreo || $fechaLlegada)
                                                        <span class="badge bg-success">Completado</span>
                                                    @elseif($fechaSalida)
                                                        <span class="badge bg-warning">En tránsito</span>
                                                    @else
                                                        <span class="badge bg-secondary">Pendiente</span>
                                                    @endif
                                                </td>
                                                <td>{{ isset($envio['fecha_salida']) ? \Carbon\Carbon::parse($envio['fecha_salida'])->format('d/m/Y') : 'N/A' }}</td>
                                                <td>{{ isset($envio['fecha_llegada']) ? \Carbon\Carbon::parse($envio['fecha_llegada'])->format('d/m/Y') : 'N/A' }}</td>
                                                <td>
                                                    <a href="{{ route('laboratorio.ver', $envio['id']) }}" class="btn btn-sm btn-info me-1">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    
                                                    @php
                                                        $enviadoPorCorreo = isset($envio['enviado_por_correo']) && $envio['enviado_por_correo'];
                                                    @endphp
                                                    
                                                    <button class="btn btn-sm me-1 btn-enviar-correo 
                                                            {{ $enviadoPorCorreo ? 'btn-success' : 'btn-primary' }}" 
                                                            title="{{ $enviadoPorCorreo ? 'Email ya enviado - Click para reenviar' : 'Enviar por email' }}"
                                                            data-envio-id="{{ $envio['id'] }}"
                                                            data-ya-enviado="{{ $enviadoPorCorreo ? 'true' : 'false' }}">
                                                        @if($enviadoPorCorreo)
                                                            <i class="fas fa-check me-1"></i> Enviado
                                                        @else
                                                            <i class="fas fa-envelope me-1"></i> Enviar
                                                        @endif
                                                    </button>
                                                    
                                                    <form action="{{ route('laboratorio.eliminar', $envio['id']) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Está seguro que desea eliminar este envío?')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            
                             <!-- Mensaje cuando no hay resultados visibles -->
                            <div id="noResultados" class="alert alert-info" style="display: none;">
                                <i class="fas fa-info-circle me-2"></i>
                                No se encontraron envíos para los filtros seleccionados.
                            </div>
    
                            <!-- Contador de registros -->
                            <div class="mt-3">
                                <small class="text-muted">
                                    Mostrando <span id="contadorRegistros">0</span> envío(s)
                                </small>
                            </div>
                        @else
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                No hay envíos de muestras registrados.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endsection

    @section('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const filtroFecha = document.getElementById('filtroFecha');
        const filtroMes = document.getElementById('filtroMes');
        const limpiarFiltros = document.getElementById('limpiarFiltros');
        const tabla = document.getElementById('tablaEnvios');
        const noResultados = document.getElementById('noResultados');
        const contadorRegistros = document.getElementById('contadorRegistros');
        
        // Función para filtrar la tabla
        function filtrarTabla() {
            const fechaBusqueda = filtroFecha.value;
            const mesFiltro = filtroMes.value;
            
            // Verificar si la tabla existe
            if (!tabla) return;
            
            const tbody = tabla.getElementsByTagName('tbody')[0];
            if (!tbody) return;
            
            const filas = tbody.getElementsByTagName('tr');
            const fechaActual = new Date();
            const mesActual = fechaActual.getMonth(); // 0-11
            const añoActual = fechaActual.getFullYear();
            
            let filasVisibles = 0;
            
            Array.from(filas).forEach(fila => {
                const fechaEnvio = fila.getAttribute('data-fecha');
                let mostrarFila = true;
                
                if (fechaEnvio && fechaEnvio !== '') {
                    // Crear fecha desde string YYYY-MM-DD sin problemas de zona horaria
                    const partesFecha = fechaEnvio.split('-');
                    if (partesFecha.length === 3) {
                        const año = parseInt(partesFecha[0]);
                        const mes = parseInt(partesFecha[1]) - 1; // Mes en JS es 0-11
                        const dia = parseInt(partesFecha[2]);
                        const fecha = new Date(año, mes, dia);
                        
                        // Si hay una fecha específica seleccionada, solo filtrar por esa fecha
                        if (fechaBusqueda) {
                            const partesBusqueda = fechaBusqueda.split('-');
                            const añoBusqueda = parseInt(partesBusqueda[0]);
                            const mesBusqueda = parseInt(partesBusqueda[1]) - 1;
                            const diaBusqueda = parseInt(partesBusqueda[2]);
                            
                            if (año !== añoBusqueda || mes !== mesBusqueda || dia !== diaBusqueda) {
                                mostrarFila = false;
                            }
                        } 
                        // Si no hay fecha específica, aplicar filtro de mes
                        else {
                            if (mesFiltro === 'actual') {
                                if (mes !== mesActual || año !== añoActual) {
                                    mostrarFila = false;
                                }
                            }
                            // Si mesFiltro es 'todos', mostrar todas las filas (no filtrar por mes)
                        }
                    } else {
                        mostrarFila = false;
                    }
                } else {
                    // Si no hay fecha en el envío
                    if (fechaBusqueda) {
                        // Si se busca una fecha específica y no hay fecha, ocultar
                        mostrarFila = false;
                    } else if (mesFiltro === 'actual') {
                        // Si se filtra por mes actual y no hay fecha, ocultar
                        mostrarFila = false;
                    }
                }
                
                if (mostrarFila) {
                    fila.style.display = '';
                    filasVisibles++;
                } else {
                    fila.style.display = 'none';
                }
            });
            
            // Actualizar contador
            if (contadorRegistros) {
                contadorRegistros.textContent = filasVisibles;
            }
            
            // Mostrar mensaje si no hay resultados
            if (filasVisibles === 0) {
                if (noResultados) noResultados.style.display = 'block';
                if (tabla) tabla.style.display = 'none';
            } else {
                if (noResultados) noResultados.style.display = 'none';
                if (tabla) tabla.style.display = '';
            }
        }
        
        // Event listeners para los filtros
        if (filtroFecha) filtroFecha.addEventListener('change', filtrarTabla);
        if (filtroMes) filtroMes.addEventListener('change', filtrarTabla);
        
        // Limpiar filtros - volver al estado inicial (mes actual)
        if (limpiarFiltros) {
            limpiarFiltros.addEventListener('click', function() {
                if (filtroFecha) filtroFecha.value = '';
                if (filtroMes) filtroMes.value = 'actual';
                filtrarTabla();
            });
        }
        
        // Aplicar filtro inicial (mes actual) al cargar la página
        filtrarTabla();
        
        // Función para mostrar alertas elegantes
        function mostrarAlerta(mensaje, tipo = 'info') {
            // Crear elemento de alerta
            const alerta = document.createElement('div');
            alerta.className = `alert alert-${tipo} alert-dismissible fade show position-fixed`;
            alerta.style.cssText = `
                top: 20px;
                right: 20px;
                z-index: 9999;
                min-width: 300px;
                max-width: 500px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.2);
                border-radius: 8px;
            `;
            
            // Iconos según el tipo
            const iconos = {
                success: 'fas fa-check-circle',
                danger: 'fas fa-exclamation-circle',
                warning: 'fas fa-exclamation-triangle',
                info: 'fas fa-info-circle'
            };
            
            alerta.innerHTML = `
                <i class="${iconos[tipo]} me-2"></i>
                ${mensaje}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
            
            // Agregar al documento
            document.body.appendChild(alerta);
            
            // Auto-remover después de 5 segundos
            setTimeout(() => {
                if (document.body.contains(alerta)) {
                    alerta.classList.remove('show');
                    setTimeout(() => {
                        if (document.body.contains(alerta)) {
                            document.body.removeChild(alerta);
                        }
                    }, 300);
                }
            }, 5000);
        }
        
        // Función para enviar por correo y actualizar estado - ACTUALIZADA PARA USAR GET
      // Función para enviar por correo y actualizar estado
        function enviarPorCorreo(envioId, boton) {
            const fila = boton.closest('tr');
            const estadoColumn = fila.querySelector('.estado-column');
            const yaEnviado = boton.getAttribute('data-ya-enviado') === 'true';
            
            // Guardar el contenido original del botón
            const contenidoOriginal = boton.innerHTML;
            const clasesOriginales = boton.className;
            
            // Deshabilitar botón temporalmente y mostrar estado de carga
            boton.disabled = true;
            boton.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Enviando...';
            boton.className = 'btn btn-sm me-1 btn-secondary';
            
            // Usar fetch con método GET
            fetch(`{{ url('laboratorio/enviar-email') }}/${envioId}`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                // En lugar de esperar JSON, simplemente consideramos que la solicitud fue exitosa
                // si llegamos hasta aquí (código 200 OK)
                return { success: true };
            })
            .then(data => {
                // Actualizar estado a completado
                estadoColumn.innerHTML = '<span class="badge bg-success">Completado</span>';
                
                // Cambiar el botón a estado "Completado"
                boton.disabled = false;
                boton.innerHTML = '<i class="fas fa-check me-1"></i> Completado';
                boton.className = 'btn btn-sm me-1 btn-success';
                boton.title = 'Email enviado exitosamente';
                boton.setAttribute('data-ya-enviado', 'true');
                
                // Mostrar mensaje de éxito
                const mensajeExito = yaEnviado ? 
                    '¡Reenvío exitoso! El correo ha sido reenviado correctamente.' : 
                    '¡Envío exitoso! El correo ha sido enviado correctamente.';
                mostrarAlerta(mensajeExito, 'success');
                
                // Después de 3 segundos, cambiar el botón para permitir reenvío
                setTimeout(() => {
                    boton.innerHTML = '<i class="fas fa-envelope me-1"></i> Reenviar';
                    boton.className = 'btn btn-sm me-1 btn-outline-primary';
                    boton.title = 'Reenviar por email';
                }, 3000);
            })
            .catch(error => {
                console.error('Error:', error);
                
                // Restaurar botón al estado original
                boton.disabled = false;
                boton.innerHTML = contenidoOriginal;
                boton.className = clasesOriginales;
                
                // Mostrar mensaje de error
                mostrarAlerta('Error al enviar el correo: ' + error.message, 'danger');
            });
        }

        
        // Configurar los botones de enviar correo con delegación de eventos
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('btn-enviar-correo') || 
                e.target.closest('.btn-enviar-correo')) {
                
                e.preventDefault();
                const boton = e.target.classList.contains('btn-enviar-correo') ? 
                             e.target : e.target.closest('.btn-enviar-correo');
                const envioId = boton.getAttribute('data-envio-id');
                const yaEnviado = boton.getAttribute('data-ya-enviado') === 'true';
                
                if (envioId) {
                    // Mensaje de confirmación diferente según si ya fue enviado
                    const mensajeConfirmacion = yaEnviado ? 
                        '¿Desea reenviar este reporte por correo electrónico?' : 
                        '¿Desea enviar este reporte por correo electrónico?';
                    
                    if (confirm(mensajeConfirmacion)) {
                        enviarPorCorreo(envioId, boton);
                    }
                }
            }
        });
    });
    </script>
    
    <!-- Estilos adicionales para las alertas -->
    <style>
    /* Animación para las alertas */
    .alert.position-fixed {
        animation: slideInRight 0.3s ease-out;
    }
    
    .alert.position-fixed.fade:not(.show) {
        animation: slideOutRight 0.3s ease-out;
    }
    
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
    
    /* Mejoras visuales para los botones */
    .btn-enviar-correo {
        transition: all 0.3s ease;
    }
    
    .btn-enviar-correo:hover:not(:disabled) {
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .btn-enviar-correo:disabled {
        cursor: not-allowed;
    }
    
    /* Spinner personalizado */
    .fa-spinner.fa-spin {
        animation: fa-spin 1s infinite linear;
    }
    
    @keyframes fa-spin {
        0% {
            transform: rotate(0deg);
        }
        100% {
            transform: rotate(360deg);
        }
    }
    </style>
    @endsection
