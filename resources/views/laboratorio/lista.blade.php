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
                                                <button onclick="enviarPorCorreo({{ $envio['id'] }})" 
                                                        class="btn btn-sm btn-primary me-1 btn-enviar-correo" 
                                                        title="Enviar por email"
                                                        data-envio-id="{{ $envio['id'] }}">
                                                    <i class="fas fa-envelope"></i>
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
});

// Función para enviar por correo y actualizar estado
function enviarPorCorreo(envioId) {
    const boton = document.querySelector(`[data-envio-id="${envioId}"]`);
    const fila = boton.closest('tr');
    const estadoColumn = fila.querySelector('.estado-column');
    
    // Deshabilitar botón temporalmente
    boton.disabled = true;
    boton.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    
    // Hacer la llamada AJAX
    fetch(`{{ url('') }}/laboratorio/enviar-email/${envioId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Actualizar estado a completado
            estadoColumn.innerHTML = '<span class="badge bg-success">Completado</span>';
            
            // Restaurar botón
            boton.disabled = false;
            boton.innerHTML = '<i class="fas fa-envelope"></i>';
            
            // Mostrar mensaje de éxito
            alert('Correo enviado exitosamente');
        } else {
            throw new Error(data.message || 'Error al enviar correo');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        
        // Restaurar botón
        boton.disabled = false;
        boton.innerHTML = '<i class="fas fa-envelope"></i>'; // Corregido el typo "botn"
        
        alert('Error al enviar el correo: ' + error.message);
    });
}
</script>

@endsection