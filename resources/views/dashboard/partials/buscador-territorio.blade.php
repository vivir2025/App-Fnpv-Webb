<div class="buscador-territorio-simple">
    <h6 class="mb-3">
        <i class="fas fa-search-location me-2"></i>Buscar por Territorio
    </h6>
    
    <div class="row g-3">
        <!-- Buscador -->
        <div class="col-md-12">
            <label class="form-label">
                <i class="fas fa-map-marked-alt me-1 text-primary"></i>
                Buscar por ubicación (municipio, corregimiento, vereda)
            </label>
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0">
                    <i class="fas fa-search text-primary"></i>
                </span>
                <input 
                    type="text" 
                    class="form-control border-start-0" 
                    id="buscar-territorio" 
                    placeholder="Ej: Rosario, Cajibío, Popayán..."
                    autocomplete="off">
                <button class="btn btn-primary btn-sm" type="button" id="btn-buscar-territorio">
                    <i class="fas fa-search me-1"></i>Buscar
                </button>
                <button class="btn btn-outline-secondary btn-sm" type="button" id="limpiar-busqueda">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <small class="text-muted">
                <i class="fas fa-info-circle me-1"></i>
                Se buscarán pacientes cercanos a la ubicación ingresada
            </small>
        </div>
    </div>
    
    <!-- Territorio seleccionado -->
    <div id="territorio-seleccionado" class="alert alert-success mt-3" style="display: none;">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <i class="fas fa-map-pin me-2"></i>
                <strong>Mostrando:</strong>
                <span id="nombre-territorio-seleccionado" class="ms-1"></span>
                <span class="badge bg-white text-success ms-2" id="total-pacientes-territorio">
                    <i class="fas fa-users me-1"></i>0 pacientes
                </span>
                <span class="badge bg-white text-info ms-1" id="radio-busqueda">
                    <i class="fas fa-bullseye me-1"></i>Radio: 5 km
                </span>
            </div>
            <button type="button" class="btn btn-sm btn-outline-success" id="limpiar-territorio">
                <i class="fas fa-redo me-1"></i>Ver todos
            </button>
        </div>
    </div>
    
    <!-- Loading -->
    <div id="loading-territorio" class="text-center py-3" style="display: none;">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Buscando...</span>
        </div>
        <p class="mt-2 text-muted">Buscando ubicación y pacientes cercanos...</p>
    </div>
</div>
