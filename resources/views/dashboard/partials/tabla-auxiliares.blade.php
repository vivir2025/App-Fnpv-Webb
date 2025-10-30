<div class="row">
    <div class="col-lg-12 mb-4">
        <div class="card shadow border-0">
            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-user-nurse me-2"></i>Visitas por Auxiliar (Mes Actual)
                </h5>
                <span class="badge bg-light text-success" id="total-auxiliares-badge">
                    <i class="fas fa-users me-1"></i>
                    <span id="contador-auxiliares">0</span> auxiliares
                </span>
            </div>
            <div class="card-body">
                <!-- Indicador de carga -->
                <div id="tabla-auxiliares-loading" class="text-center py-5" style="display: none;">
                    <div class="spinner-border text-success mb-3" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="text-muted">Cargando datos de auxiliares...</p>
                </div>

                <!-- Mensaje sin datos -->
                <div id="tabla-auxiliares-empty" class="text-center py-5" style="display: none;">
                    <i class="fas fa-user-nurse fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No hay datos de auxiliares disponibles</h5>
                    <p class="text-muted">Los datos aparecerán cuando haya visitas registradas en el período seleccionado.</p>
                </div>

                <!-- Tabla de datos -->
                <div class="table-responsive" id="tabla-auxiliares-container">
                    <table class="table table-hover table-striped align-middle" id="tabla-auxiliares">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">
                                    <i class="fas fa-user me-1"></i>Auxiliar
                                </th>
                                <th scope="col">
                                    <i class="fas fa-map-marker-alt me-1"></i>Sede
                                </th>
                                <th scope="col" class="text-center">
                                    <i class="fas fa-check-circle me-1"></i>Visitas Realizadas
                                </th>
                                <th scope="col">
                                    <i class="fas fa-chart-line me-1"></i>Progreso (meta: 80/mes)
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Se llenará dinámicamente con JavaScript -->
                        </tbody>
                        <tfoot class="table-light" id="tabla-auxiliares-footer" style="display: none;">
                            <tr>
                                <td colspan="2" class="text-end fw-bold">TOTALES:</td>
                                <td class="text-center">
                                    <span class="badge bg-success fs-6" id="total-visitas-auxiliares">0</span>
                                </td>
                                <td>
                                    <span class="text-muted" id="promedio-cumplimiento">Promedio: 0%</span>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
