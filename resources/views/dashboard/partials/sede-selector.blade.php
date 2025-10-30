<div class="sede-selector">
    <h6 class="text-success mb-3">
        <i class="fas fa-map-marker-alt me-2"></i>Seleccione una sede
    </h6>
    <div id="sede-buttons">
        <button class="btn btn-outline-success sede-btn active" data-sede-id="todas">
            Todas las sedes
        </button>
        @foreach($sedes as $sede)
            <button class="btn btn-outline-success sede-btn" data-sede-id="{{ $sede['id'] }}">
                {{ $sede['nombresede'] }}
            </button>
        @endforeach
    </div>
</div>
