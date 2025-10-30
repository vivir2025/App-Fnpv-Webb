<div class="modal fade" id="modalMapaCompleto" tabindex="-1" aria-labelledby="modalMapaCompletoLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalMapaCompletoLabel">
                    <i class="fas fa-map me-2"></i>Mapa de Calor de Pacientes - Vista Completa
                </h5>
                <button type="button" 
                        class="btn btn-outline-light btn-sm" 
                        onclick="cerrarMapaCompleto()">
                    <i class="fas fa-compress me-1"></i>Cerrar Pantalla Completa
                </button>
            </div>
            <div class="modal-body p-0">
                <div id="map-fullscreen"></div>
            </div>
        </div>
    </div>
</div>
