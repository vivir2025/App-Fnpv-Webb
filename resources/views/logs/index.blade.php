@extends('layouts.app')

@section('title', 'Logs del Sistema')

@section('content')
<style>
    .logs-view {
        --logs-primary-color: #269e3cff;
        --logs-primary-dark: #269e3cff;
        --logs-success-color: #2ba06fff;
        --logs-error-color: #dc3545;
        --logs-warning-color: #fd7e14;
        --logs-shadow-lg: 0 1rem 3rem rgba(0,0,0,0.175);
    }

    .logs-view .modern-card {
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(10px);
        border: none;
        border-radius: 20px;
        box-shadow: var(--logs-shadow-lg);
        overflow: hidden;
        transition: all 0.3s ease;
        border: 1px solid rgba(0,0,0,0.1);
    }

    .logs-view .modern-header {
        background: linear-gradient(135deg, var(--logs-primary-color) 0%, var(--logs-primary-dark) 100%);
        padding: 2rem;
        position: relative;
        overflow: hidden;
    }

    .logs-view .modern-header h5 {
        margin: 0;
        font-weight: 600;
        font-size: 1.5rem;
        position: relative;
        z-index: 2;
    }

    .logs-view .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .logs-view .stat-card {
        background: white;
        border-radius: 15px;
        padding: 1.5rem;
        text-align: center;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        transition: transform 0.3s ease;
    }

    .logs-view .stat-card:hover {
        transform: translateY(-5px);
    }

    .logs-view .stat-number {
        font-size: 2rem;
        font-weight: bold;
        margin-bottom: 0.5rem;
    }

    .logs-view .stat-label {
        color: #6c757d;
        font-size: 0.9rem;
    }

    .logs-view .filters-section {
        background: #f8f9fa;
        border-radius: 15px;
        padding: 1.5rem;
        margin-bottom: 2rem;
    }

    .logs-view .log-item {
        background: white;
        border-radius: 12px;
        margin-bottom: 1rem;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .logs-view .log-item:hover {
        box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        transform: translateY(-2px);
    }

    .logs-view .log-header {
        padding: 1rem 1.5rem;
        border-bottom: 1px solid #e9ecef;
        display: flex;
        justify-content: space-between;
        align-items: center;
        cursor: pointer;
    }

    .logs-view .log-content {
        padding: 1.5rem;
        display: none;
    }

    .logs-view .log-content.show {
        display: block;
    }

    .logs-view .status-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 50px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .logs-view .status-success {
        background: #d1e7dd;
        color: #0f5132;
    }

    .logs-view .status-error {
        background: #f8d7da;
        color: #842029;
    }

    .logs-view .status-processing {
        background: #fff3cd;
        color: #664d03;
    }

    .logs-view .type-badge {
        padding: 0.25rem 0.5rem;
        border-radius: 8px;
        font-size: 0.75rem;
        font-weight: 500;
        margin-right: 0.5rem;
    }

    .logs-view .type-visita { background: #e7f3ff; color: #0066cc; }
    .logs-view .type-brigada { background: #f0fff4; color: #00cc44; }
    .logs-view .type-afinamiento { background: #fff0f5; color: #cc0066; }
    .logs-view .type-paciente { background: #f5f0ff; color: #6600cc; }
    .logs-view .type-general { background: #f8f9fa; color: #495057; }
    .logs-view .type-unknown { background: #e9ecef; color: #6c757d; }

    .logs-view .pagination-wrapper {
        display: flex;
        justify-content: center;
        margin-top: 2rem;
    }

    .logs-view .loading-spinner {
        text-align: center;
        padding: 3rem;
    }

    .logs-view .spinner-border {
        width: 3rem;
        height: 3rem;
        border-width: 0.3em;
        color: var(--logs-primary-color);
    }

    .logs-view .error-details {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 1rem;
        margin-top: 1rem;
        font-family: 'Courier New', monospace;
        font-size: 0.85rem;
    }

    .logs-view .btn-refresh {
        background: linear-gradient(135deg, var(--logs-primary-color) 0%, var(--logs-primary-dark) 100%);
        border: none;
        border-radius: 10px;
        padding: 0.5rem 1rem;
        color: white;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .logs-view .btn-refresh:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(111, 66, 193, 0.3);
        color: white;
    }

    @media (max-width: 768px) {
        .logs-view .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .logs-view .log-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.5rem;
        }
    }
</style>

<div class="container-fluid logs-view">
    <!-- Estadísticas -->
    <div class="stats-grid" id="statsGrid">
        <div class="stat-card">
            <div class="stat-number text-primary" id="totalLogs">-</div>
            <div class="stat-label">Total de Logs</div>
        </div>
        <div class="stat-card">
            <div class="stat-number text-success" id="successLogs">-</div>
            <div class="stat-label">Exitosos</div>
        </div>
        <div class="stat-card">
            <div class="stat-number text-danger" id="errorLogs">-</div>
            <div class="stat-label">Errores</div>
        </div>
        <div class="stat-card">
            <div class="stat-number text-warning" id="todayLogs">-</div>
            <div class="stat-label">Hoy</div>
        </div>
    </div>

    <!-- Tarjeta principal -->
    <div class="card modern-card">
        <div class="card-header modern-header text-white">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-list-alt me-2"></i>Logs del Sistema</h5>
                <button class="btn btn-light btn-sm btn-refresh" onclick="refreshData()">
                    <i class="fas fa-sync-alt me-1"></i>Actualizar
                </button>
            </div>
        </div>
        
        <div class="card-body">
            <!-- Filtros -->
            <div class="filters-section">
                <div class="row">
                    <div class="col-md-3">
                        <label class="form-label">Tipo</label>
                        <select class="form-select" id="filterType">
                            <option value="">Todos los tipos</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Estado</label>
                        <select class="form-select" id="filterStatus">
                            <option value="">Todos los estados</option>
                            <option value="success">Exitoso</option>
                            <option value="error">Error</option>
                            <option value="processing">Procesando</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Buscar</label>
                        <input type="text" class="form-control" id="filterSearch" placeholder="Buscar en mensajes o IDs...">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <button class="btn btn-primary w-100" onclick="loadLogs(1)">
                            <i class="fas fa-search me-1"></i>Filtrar
                        </button>
                    </div>
                </div>
            </div>

            <!-- Lista de logs -->
            <div id="logsContainer">
                <div class="loading-spinner">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-2">Cargando logs...</p>
                </div>
            </div>

            <!-- Paginación -->
            <div class="pagination-wrapper" id="paginationWrapper" style="display: none;">
                <nav>
                    <ul class="pagination" id="pagination">
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let currentPage = 1;
let totalPages = 1;

document.addEventListener('DOMContentLoaded', function() {
    loadStats();
    loadLogs();
    
    // Event listeners para filtros
    const filterType = document.getElementById('filterType');
    const filterStatus = document.getElementById('filterStatus');
    const filterSearch = document.getElementById('filterSearch');
    
    if (filterType) filterType.addEventListener('change', () => loadLogs(1));
    if (filterStatus) filterStatus.addEventListener('change', () => loadLogs(1));
    if (filterSearch) filterSearch.addEventListener('input', debounce(() => loadLogs(1), 500));
});

async function makeRequest(url, params = {}) {
    try {
        const urlObj = new URL(url, window.location.origin);
        
        Object.keys(params).forEach(key => {
            if (params[key] !== null && params[key] !== '' && params[key] !== undefined) {
                urlObj.searchParams.append(key, params[key]);
            }
        });

        const response = await fetch(urlObj.toString(), {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        });

        if (!response.ok) {
            const errorText = await response.text();
            throw new Error(`HTTP error! status: ${response.status} - ${errorText}`);
        }

        return await response.json();
    } catch (error) {
        throw error;
    }
}

async function loadStats() {
    try {
        const statsUrl = "{{ route('logs.stats') }}";
        const data = await makeRequest(statsUrl);
        
        if (data && data.success) {
            const stats = data.data || data;
            
            updateStatElement('totalLogs', stats.total || 0);
            updateStatElement('successLogs', stats.by_status?.success || 0);
            updateStatElement('errorLogs', stats.by_status?.error || 0);
            updateStatElement('todayLogs', stats.today_activity || 0);
        } else {
            showStatsError();
        }
    } catch (error) {
        showStatsError();
    }
}

function updateStatElement(id, value) {
    const element = document.getElementById(id);
    if (element) {
        element.textContent = value;
    }
}

async function loadLogs(page = 1) {
    currentPage = page;
    
    const params = {
        page: page,
        per_page: 20,
        type: document.getElementById('filterType')?.value || '',
        status: document.getElementById('filterStatus')?.value || '',
        search: document.getElementById('filterSearch')?.value || ''
    };

    showLoading();

    try {
        const logsUrl = "{{ route('logs.data') }}";
        const data = await makeRequest(logsUrl, params);
        
        if (data && data.success) {
            renderLogs(data.data || []);
            renderPagination(data.pagination || {});
            updateFilters(data.filters || {});
        } else {
            showError(data?.message || 'Error al cargar los logs');
        }
    } catch (error) {
        showError('Error de conexión al cargar los logs. Por favor, inténtelo de nuevo.');
    } finally {
        hideLoading();
    }
}

function refreshData() {
    loadStats();
    loadLogs(currentPage);
}

function showLoading() {
    const container = document.getElementById('logsContainer');
    if (container) {
        container.innerHTML = `
            <div class="loading-spinner text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
                <p class="mt-3 text-muted">Cargando logs...</p>
            </div>
        `;
    }
}

function hideLoading() {
    // Se ejecuta después de renderLogs
}

function showError(message) {
    const container = document.getElementById('logsContainer');
    if (container) {
        container.innerHTML = `
            <div class="alert alert-danger">
                <i class="fas fa-times-circle me-2"></i>
                ${message}
            </div>
        `;
    }
}

function showStatsError() {
    const elements = ['totalLogs', 'successLogs', 'errorLogs', 'todayLogs'];
    elements.forEach(id => updateStatElement(id, '0'));
}

function renderLogs(logs) {
    const container = document.getElementById('logsContainer');
    if (!container) return;
    
    if (!logs || logs.length === 0) {
        container.innerHTML = `
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                No se encontraron logs con los filtros seleccionados.
            </div>
        `;
        return;
    }

    try {
        const logsHtml = logs.map(log => {
            return `
                <div class="log-item">
                    <div class="log-header" onclick="toggleLogContent('${log.id}')">
                        <div class="d-flex align-items-center flex-grow-1">
                            <span class="type-badge type-${log.type || 'unknown'}">${log.type || 'N/A'}</span>
                            <span class="status-badge status-${log.status || 'unknown'}">${getStatusText(log.status)}</span>
                            <span class="ms-2 text-muted">${log.formatted_date || log.created_at || 'N/A'}</span>
                            ${log.entity_id ? `<span class="ms-2 badge bg-secondary">${log.entity_id}</span>` : ''}
                        </div>
                        <div>
                            <i class="fas fa-chevron-down toggle-icon" id="icon-${log.id}"></i>
                        </div>
                    </div>
                    <div class="log-content" id="content-${log.id}">
                        <div class="row">
                            <div class="col-md-8">
                                <h6>Mensaje:</h6>
                                <p class="mb-2">${log.message || 'Sin mensaje'}</p>
                                
                                ${log.operation ? `<p><strong>Operación:</strong> ${log.operation}</p>` : ''}
                                <p><strong>Nivel:</strong> ${log.level || 'N/A'}</p>
                                <p><strong>Entorno:</strong> ${log.environment || 'N/A'}</p>
                            </div>
                            <div class="col-md-4">
                                ${log.error_details ? renderErrorDetails(log.error_details) : ''}
                            </div>
                        </div>
                        
                        ${log.full_content && log.full_content !== log.message ? `
                            <div class="mt-3">
                                <h6>Contenido completo:</h6>
                                <div class="error-details">
                                    <pre style="white-space: pre-wrap; margin: 0;">${log.full_content}</pre>
                                </div>
                            </div>
                        ` : ''}
                    </div>
                </div>
            `;
        }).join('');

        container.innerHTML = logsHtml;
        
    } catch (error) {
        container.innerHTML = `
            <div class="alert alert-danger">
                <i class="fas fa-times-circle me-2"></i>
                Error al mostrar los logs.
            </div>
        `;
    }
}

function renderErrorDetails(errorDetails) {
    if (!errorDetails) return '';
    
    try {
        if (typeof errorDetails === 'string') {
            errorDetails = JSON.parse(errorDetails);
        }
        
        return `
            <div class="error-details">
                <h6 class="text-danger">Detalles del Error:</h6>
                ${errorDetails.error ? `<p><strong>Error:</strong> ${errorDetails.error}</p>` : ''}
                ${errorDetails.file ? `<p><strong>Archivo:</strong> ${errorDetails.file}</p>` : ''}
                ${errorDetails.line ? `<p><strong>Línea:</strong> ${errorDetails.line}</p>` : ''}
            </div>
        `;
    } catch (e) {
        return `<div class="error-details"><p>Error al mostrar detalles</p></div>`;
    }
}

function renderPagination(pagination) {
    const wrapper = document.getElementById('paginationWrapper');
    const paginationEl = document.getElementById('pagination');
    
    if (!wrapper || !paginationEl) return;
    
    if (!pagination || pagination.last_page <= 1) {
        wrapper.style.display = 'none';
        return;
    }
    
    wrapper.style.display = 'block';
    totalPages = pagination.last_page;
    
    let paginationHtml = '';
    
    // Botón anterior
    paginationHtml += `
        <li class="page-item ${pagination.current_page === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="event.preventDefault(); loadLogs(${pagination.current_page - 1})">Anterior</a>
        </li>
    `;
    
    // Números de página
    for (let i = 1; i <= pagination.last_page; i++) {
        if (i === pagination.current_page || 
            i === 1 || 
            i === pagination.last_page || 
            (i >= pagination.current_page - 2 && i <= pagination.current_page + 2)) {
            paginationHtml += `
                <li class="page-item ${i === pagination.current_page ? 'active' : ''}">
                    <a class="page-link" href="#" onclick="event.preventDefault(); loadLogs(${i})">${i}</a>
                </li>
            `;
        } else if (i === pagination.current_page - 3 || i === pagination.current_page + 3) {
            paginationHtml += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        }
    }
    
    // Botón siguiente
    paginationHtml += `
        <li class="page-item ${pagination.current_page === pagination.last_page ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="event.preventDefault(); loadLogs(${pagination.current_page + 1})">Siguiente</a>
        </li>
    `;
    
    paginationEl.innerHTML = paginationHtml;
}

function updateFilters(filters) {
    if (!filters || !filters.types) return;
    
    const typeSelect = document.getElementById('filterType');
    if (!typeSelect) return;
    
    const currentType = typeSelect.value;
    
    typeSelect.innerHTML = '<option value="">Todos los tipos</option>';
    filters.types.forEach(type => {
        const option = document.createElement('option');
        option.value = type;
        option.textContent = type.charAt(0).toUpperCase() + type.slice(1);
        if (type === currentType) option.selected = true;
        typeSelect.appendChild(option);
    });
}

function toggleLogContent(logId) {
    const content = document.getElementById(`content-${logId}`);
    const icon = document.getElementById(`icon-${logId}`);
    
    if (!content || !icon) return;
    
    if (content.classList.contains('show')) {
        content.classList.remove('show');
        icon.classList.remove('fa-chevron-up');
        icon.classList.add('fa-chevron-down');
    } else {
        content.classList.add('show');
        icon.classList.remove('fa-chevron-down');
        icon.classList.add('fa-chevron-up');
    }
}

function getStatusText(status) {
    const statusMap = {
        'success': 'Exitoso',
        'error': 'Error',
        'processing': 'Procesando'
    };
    return statusMap[status] || (status || 'N/A');
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}
</script>
@endsection
