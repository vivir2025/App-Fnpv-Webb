<div class="col-lg-8 mb-4">
    <div class="card shadow border-0 h-100" id="mapa-card">
        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-map me-2"></i>Mapa de Calor de Pacientes
            </h5>
            <button class="btn btn-sm btn-outline-light" 
                    id="btn-expandir-mapa" 
                    onclick="toggleMapaCompleto()">
                <i class="fas fa-expand" id="icono-expandir"></i>
                <span id="texto-expandir">Expandir</span>
            </button>
        </div>
        <div class="card-body p-0">
            <div id="map"></div>
        </div>
    </div>
</div>
