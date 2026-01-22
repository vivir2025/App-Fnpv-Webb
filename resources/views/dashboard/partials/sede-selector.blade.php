<div class="sede-selector">
    <h6 class="text-success mb-3">
        <i class="fas fa-map-marker-alt me-2"></i>
        @if($permisos['puede_ver_todas_sedes'])
            Seleccione una sede
        @else
            Sede asignada
        @endif
    </h6>
    <div id="sede-buttons">
        @if($permisos['puede_ver_todas_sedes'])
            <button class="btn btn-outline-success sede-btn active" data-sede-id="todas">
                Todas las sedes
            </button>
        @endif
        
        @foreach($sedes as $sede)
            <button class="btn btn-outline-success sede-btn {{ $permisos['es_jefe'] ? 'active' : '' }}" 
                    data-sede-id="{{ $sede['id'] }}"
                    @if($permisos['es_jefe']) disabled @endif>
                {{ $sede['nombresede'] }}
                @if($permisos['es_jefe'])
                    <i class="fas fa-lock ms-1" style="font-size: 10px;"></i>
                @endif
            </button>
        @endforeach
    </div>
    
    @if($permisos['es_jefe'])
        <small class="text-muted mt-2 d-block">
            <i class="fas fa-info-circle me-1"></i>
            Solo puede ver información de su sede asignada
        </small>
    @endif
</div>
