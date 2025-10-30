<div class="filtro-fecha-container">
    <h6 class="mb-3">
        <i class="fas fa-calendar-alt me-2"></i>Filtrar por Fechas
    </h6>
    <div class="row g-2">
        <div class="col-md-5">
            <label for="fecha-inicio" class="form-label">
                <i class="fas fa-calendar-day me-1"></i>Fecha Inicio
            </label>
            <div class="date-input-group">
                <input type="date" 
                       class="form-control form-control-sm" 
                       id="fecha-inicio" 
                       name="fecha_inicio"
                       max="{{ date('Y-m-d') }}">
            </div>
        </div>
        <div class="col-md-5">
            <label for="fecha-fin" class="form-label">
                <i class="fas fa-calendar-check me-1"></i>Fecha Fin
            </label>
            <div class="date-input-group">
                <input type="date" 
                       class="form-control form-control-sm" 
                       id="fecha-fin" 
                       name="fecha_fin"
                       max="{{ date('Y-m-d') }}">
            </div>
        </div>
        <div class="col-md-2">
            <label class="form-label d-block">&nbsp;</label>
            <div class="d-flex flex-column gap-1">
                <button type="button" 
                        class="btn btn-success btn-sm btn-filtrar w-100" 
                        id="btn-filtrar-fecha">
                    <i class="fas fa-filter me-1"></i>Filtrar
                </button>
                <button type="button" 
                        class="btn btn-outline-secondary btn-sm btn-limpiar w-100" 
                        id="btn-limpiar-fecha">
                    <i class="fas fa-times me-1"></i>Limpiar
                </button>
            </div>
        </div>
    </div>
    <div class="mt-2">
        <small class="text-muted">
            <i class="fas fa-info-circle me-1"></i>
            Filtre por rango de fechas. Por defecto: mes actual.
        </small>
    </div>
</div>
