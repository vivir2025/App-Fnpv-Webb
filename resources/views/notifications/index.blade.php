@extends('layouts.app')

@section('title', 'Sistema de Notificaciones Push')

@section('content')
<style>
    .notifications-view {
        --notif-primary-color: #28a745;
        --notif-primary-dark: #218838;
        --notif-success-color: #28a745;
        --notif-warning-color: #ffc107;
        --notif-shadow-lg: 0 1rem 3rem rgba(0,0,0,0.175);
    }

    .notifications-view .modern-card {
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(10px);
        border: none;
        border-radius: 20px;
        box-shadow: var(--notif-shadow-lg);
        overflow: hidden;
        transition: all 0.3s ease;
        border: 1px solid rgba(0,0,0,0.1);
    }

    .notifications-view .modern-header {
        background: linear-gradient(135deg, var(--notif-primary-color) 0%, var(--notif-primary-dark) 100%);
        padding: 2rem;
        position: relative;
        overflow: hidden;
    }

    .notifications-view .modern-header h5 {
        margin: 0;
        font-weight: 600;
        font-size: 1.5rem;
        position: relative;
        z-index: 2;
    }

    .notifications-view .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .notifications-view .stat-card {
        background: white;
        border-radius: 15px;
        padding: 1.5rem;
        text-align: center;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        transition: transform 0.3s ease;
    }

    .notifications-view .stat-card:hover {
        transform: translateY(-5px);
    }

    .notifications-view .stat-number {
        font-size: 2rem;
        font-weight: bold;
        margin-bottom: 0.5rem;
    }

    .notifications-view .stat-label {
        color: #6c757d;
        font-size: 0.9rem;
    }

    .notifications-view .form-section {
        background: #f8f9fa;
        border-radius: 15px;
        padding: 2rem;
        margin-bottom: 2rem;
    }

    .notifications-view .btn-send {
        background: linear-gradient(135deg, var(--notif-primary-color) 0%, var(--notif-primary-dark) 100%);
        border: none;
        border-radius: 10px;
        padding: 0.75rem 2rem;
        color: white;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .notifications-view .btn-send:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(40, 167, 69, 0.4);
        color: white;
    }

    .notifications-view .btn-send:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    .notifications-view .btn-send-all {
        background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
        border: none;
        border-radius: 10px;
        padding: 0.75rem 2rem;
        color: white;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .notifications-view .btn-send-all:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(255, 107, 107, 0.4);
        color: white;
    }

    .notifications-view .btn-send-all:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    .notifications-view .alert-custom {
        border-radius: 12px;
        padding: 1rem 1.5rem;
        margin-bottom: 1rem;
        animation: slideIn 0.3s ease-out;
    }

    .notifications-view .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.85);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 9999;
    }

    .notifications-view .loading-overlay.show {
        display: flex;
    }

    .notifications-view .loading-content {
        text-align: center;
    }

    .notifications-view .loading-spinner {
        width: 50px;
        height: 50px;
        border: 4px solid #e9ecef;
        border-top: 4px solid #28a745;
        border-radius: 50%;
        animation: spinLoader 0.8s linear infinite;
        margin: 0 auto 1rem;
    }

    @keyframes spinLoader {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    .notifications-view .loading-text {
        color: #495057;
        font-size: 1rem;
        font-weight: 500;
    }

    .notifications-view .type-selector {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        border: 2px solid #e9ecef;
    }

    .notifications-view .type-option {
        display: flex;
        align-items: center;
        padding: 1rem;
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }

    .notifications-view .type-option:hover {
        background: #f8f9fa;
    }

    .notifications-view .type-option.selected {
        background: #e7f5ec;
        border-color: var(--notif-primary-color);
    }

    .notifications-view .type-option input[type="radio"] {
        width: 20px;
        height: 20px;
        margin-right: 12px;
        cursor: pointer;
    }

    .notifications-view .type-icon {
        font-size: 2rem;
        margin-right: 1rem;
        width: 50px;
        text-align: center;
    }

    .notifications-view .user-select-container {
        max-height: 300px;
        overflow-y: auto;
        border-radius: 10px;
        border: 1px solid #dee2e6;
        padding: 1rem;
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @media (max-width: 768px) {
        .notifications-view .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .notifications-view .form-section {
            padding: 1.5rem;
        }

        .notifications-view .type-icon {
            font-size: 1.5rem;
            width: 40px;
        }
    }
</style>

<div class="container-fluid notifications-view">

    <!-- Tarjeta principal -->
    <div class="card modern-card">
        <div class="card-header modern-header text-white">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-bell me-2"></i>Sistema de Notificaciones Push
                </h5>
                <span class="badge bg-light text-dark">
                    <i class="fas fa-user-shield me-1"></i>Solo Administradores
                </span>
            </div>
        </div>
        
        <div class="card-body">
            <!-- Alertas -->
            <div id="alertContainer"></div>

            <!-- Formulario de envío -->
            <div class="form-section">
                <h6 class="mb-4">
                    <i class="fas fa-paper-plane me-2"></i>Enviar Nueva Notificación
                </h6>

                <!-- Selector de tipo de envío -->
                <div class="type-selector mb-4">
                    <label class="form-label fw-bold mb-3">
                        <i class="fas fa-list-check me-2"></i>Tipo de Envío
                    </label>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="type-option" id="typeUser" onclick="selectType('user')">
                                <input type="radio" name="sendType" value="user" id="radioUser">
                                <div class="type-icon">
                                    <i class="fas fa-user text-primary"></i>
                                </div>
                                <div>
                                    <strong>Usuario Específico</strong>
                                    <div class="text-muted small">Enviar a un usuario individual</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="type-option" id="typeAll" onclick="selectType('all')">
                                <input type="radio" name="sendType" value="all" id="radioAll">
                                <div class="type-icon">
                                    <i class="fas fa-users text-danger"></i>
                                </div>
                                <div>
                                    <strong>Envío Masivo</strong>
                                    <div class="text-muted small">Enviar a todos los usuarios</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <form id="notificationForm">
                    <!-- Selector de usuario (solo visible para tipo 'user') -->
                    <div id="userSelectSection" style="display: none;">
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                <i class="fas fa-user-circle me-2"></i>Seleccionar Usuario
                            </label>
                            <select class="form-select form-select-lg" id="userId" name="user_id">
                                <option value="">Cargando usuarios...</option>
                            </select>
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>Solo se muestran usuarios con dispositivos registrados (app instalada)
                            </small>
                        </div>
                    </div>

                    <!-- Confirmación para envío masivo -->
                    <div id="massiveWarning" style="display: none;">
                        <div class="alert alert-warning d-flex align-items-center mb-4" role="alert">
                            <i class="fas fa-exclamation-triangle me-3 fs-4"></i>
                            <div>
                                <strong>¡Atención!</strong> Esta notificación se enviará a TODOS los usuarios con dispositivos registrados.
                            </div>
                        </div>
                    </div>

                    <!-- Campos del formulario -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            <i class="fas fa-heading me-2"></i>Título de la Notificación *
                        </label>
                        <input type="text" class="form-control form-control-lg" id="notifTitle" name="title" 
                               placeholder="Ej: Nueva actualización disponible" maxlength="255" required>
                        <small class="text-muted">Máximo 255 caracteres</small>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">
                            <i class="fas fa-align-left me-2"></i>Mensaje de la Notificación *
                        </label>
                        <textarea class="form-control form-control-lg" id="notifBody" name="body" 
                                  rows="4" placeholder="Escribe el mensaje de la notificación..." required></textarea>
                        <small class="text-muted">Escribe un mensaje claro y conciso</small>
                    </div>

                    <!-- Botones de acción -->
                    <div class="d-flex gap-3 justify-content-end">
                        <button type="button" class="btn btn-secondary" onclick="resetForm()">
                            <i class="fas fa-times me-2"></i>Cancelar
                        </button>
                        <button type="submit" class="btn btn-send" id="btnSend">
                            <i class="fas fa-paper-plane me-2"></i>Enviar Notificación
                        </button>
                    </div>
                </form>
            </div>

            <!-- Información adicional -->
            <div class="alert alert-info mb-0">
                <h6 class="alert-heading">
                    <i class="fas fa-info-circle me-2"></i>Información
                </h6>
                <ul class="mb-0">
                    <li>Las notificaciones se envían en tiempo real a través de Firebase Cloud Messaging (FCM)</li>
                    <li>Solo llegarán a usuarios que tengan la aplicación móvil instalada y con permisos de notificaciones activados</li>
                    <li>Todas las notificaciones enviadas quedan registradas en los logs del sistema</li>
                    <li>El envío masivo puede tardar varios segundos dependiendo del número de usuarios</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay">
    <div class="loading-content">
        <div class="loading-spinner"></div>
        <div class="loading-text" id="loadingText">Enviando notificaci&oacute;n...</div>
    </div>
</div>

<!-- Modal de resultado -->
<div class="modal fade" id="resultModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-body text-center py-5 px-4" id="resultModalBody">
                <!-- Se llena dinámicamente -->
            </div>
            <div class="modal-footer border-0 justify-content-center pb-4">
                <button type="button" class="btn btn-lg px-5" id="resultModalBtn" data-bs-dismiss="modal" style="border-radius: 10px;">Aceptar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let selectedType = null;
let users = [];

document.addEventListener('DOMContentLoaded', function() {
    loadStats();
    loadUsers();
    setupForm();
});

// ═══════════════════════════════════════════════════════════════
// CARGAR ESTADÍSTICAS
// ═══════════════════════════════════════════════════════════════
async function loadStats() {
    try {
        const response = await fetch('{{ route("notifications.stats") }}', {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        });

        if (response.ok) {
            const result = await response.json();
            const data = result.data || {};
            
            document.getElementById('totalDevices').textContent = data.total_devices || 0;
            document.getElementById('activeUsers').textContent = data.active_users || 0;
            document.getElementById('notificationsSentToday').textContent = data.notifications_sent_today || 0;
        }
    } catch (error) {
        console.error('Error cargando estadísticas:', error);
    }
}

// ═══════════════════════════════════════════════════════════════
// CARGAR USUARIOS
// ═══════════════════════════════════════════════════════════════
async function loadUsers() {
    try {
        const response = await fetch('{{ route("notifications.users") }}', {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        });

        if (response.ok) {
            const result = await response.json();
            users = result.data || [];
            
            const select = document.getElementById('userId');
            
            if (users.length === 0) {
                select.innerHTML = '<option value="">⚠️ No hay usuarios con dispositivos registrados</option>';
                showAlert('No hay usuarios con dispositivos registrados para recibir notificaciones', 'warning');
                return;
            }
            
            select.innerHTML = '<option value="">Selecciona un usuario...</option>';
            
            users.forEach(user => {
                const option = document.createElement('option');
                option.value = user.id;
                const nombre = user.nombre || user.usuario || user.name || 'Usuario';
                const rol = user.rol || 'Sin rol';
                const sede = user.sede ? ` - ${user.sede}` : '';
                const dispositivos = user.total_dispositivos || 0;
                option.textContent = `${nombre} - ${rol}${sede} (📱 ${dispositivos} disp.)`;
                select.appendChild(option);
            });
            
            console.log(`✅ ${users.length} usuario(s) con dispositivos registrados cargados`);
        }
    } catch (error) {
        console.error('Error cargando usuarios:', error);
        showAlert('Error al cargar la lista de usuarios con dispositivos', 'danger');
    }
}

// ═══════════════════════════════════════════════════════════════
// SELECCIONAR TIPO DE ENVÍO
// ═══════════════════════════════════════════════════════════════
function selectType(type) {
    selectedType = type;
    
    // Actualizar UI
    document.querySelectorAll('.type-option').forEach(el => el.classList.remove('selected'));
    document.getElementById(`type${type === 'user' ? 'User' : 'All'}`).classList.add('selected');
    document.getElementById(`radio${type === 'user' ? 'User' : 'All'}`).checked = true;
    
    // Mostrar/ocultar secciones
    document.getElementById('userSelectSection').style.display = type === 'user' ? 'block' : 'none';
    document.getElementById('massiveWarning').style.display = type === 'all' ? 'block' : 'none';
    
    // Cambiar botón
    const btnSend = document.getElementById('btnSend');
    if (type === 'all') {
        btnSend.classList.remove('btn-send');
        btnSend.classList.add('btn-send-all');
        btnSend.innerHTML = '<i class="fas fa-broadcast-tower me-2"></i>Enviar a Todos';
    } else {
        btnSend.classList.remove('btn-send-all');
        btnSend.classList.add('btn-send');
        btnSend.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Enviar Notificación';
    }
}

// ═══════════════════════════════════════════════════════════════
// CONFIGURAR FORMULARIO
// ═══════════════════════════════════════════════════════════════
function setupForm() {
    const form = document.getElementById('notificationForm');
    
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        if (!selectedType) {
            showAlert('Por favor, selecciona el tipo de envío', 'warning');
            return;
        }
        
        const title = document.getElementById('notifTitle').value.trim();
        const body = document.getElementById('notifBody').value.trim();
        
        if (!title || !body) {
            showAlert('Por favor, completa todos los campos requeridos', 'warning');
            return;
        }
        
        if (selectedType === 'user') {
            const userId = document.getElementById('userId').value;
            if (!userId) {
                showAlert('Por favor, selecciona un usuario', 'warning');
                return;
            }
            await sendToUser(userId, title, body);
        } else {
            // Confirmación adicional para envío masivo
            if (!confirm('¿Estás seguro de enviar esta notificación a TODOS los usuarios?')) {
                return;
            }
            await sendToAll(title, body);
        }
    });
}

// ═══════════════════════════════════════════════════════════════
// ENVIAR A USUARIO ESPECÍFICO
// ═══════════════════════════════════════════════════════════════
async function sendToUser(userId, title, body) {
    showLoading('user');
    
    try {
        const response = await fetch('{{ route("notifications.send.user") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                user_id: userId,
                title: title,
                body: body
            })
        });

        const result = await response.json();
        hideLoading();
        
        if (result.success) {
            showResultModal(true, `La notificación fue enviada correctamente a ${result.usuario || 'el usuario seleccionado'}`);
            resetForm();
        } else {
            showResultModal(false, result.message || 'No se pudo enviar la notificación');
        }
        
    } catch (error) {
        console.error('Error:', error);
        hideLoading();
        showResultModal(false, 'Error de conexión con el servidor');
    }
}

// ═══════════════════════════════════════════════════════════════
// ENVIAR A TODOS
// ═══════════════════════════════════════════════════════════════
async function sendToAll(title, body) {
    showLoading('all');
    
    try {
        const response = await fetch('{{ route("notifications.send.all") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                title: title,
                body: body
            })
        });

        const result = await response.json();
        hideLoading();
        
        if (result.success) {
            const dispositivos = result.total_devices || 0;
            const exitosas = result.success_count || 0;
            showResultModal(true, `Notificación enviada a ${dispositivos} dispositivo(s).<br>${exitosas} entregada(s) exitosamente.`);
            resetForm();
        } else {
            showResultModal(false, result.message || 'No se pudo enviar la notificación masiva');
        }
        
    } catch (error) {
        console.error('Error:', error);
        hideLoading();
        showResultModal(false, 'Error de conexión con el servidor');
    }
}

// ═══════════════════════════════════════════════════════════════
// UTILIDADES
// ═══════════════════════════════════════════════════════════════
function showAlert(message, type = 'info') {
    const container = document.getElementById('alertContainer');
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} alert-custom alert-dismissible fade show`;
    alert.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    container.appendChild(alert);
    
    // Auto-remover después de 5 segundos
    setTimeout(() => {
        alert.remove();
    }, 5000);
    
    // Scroll al tope para ver la alerta
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function showLoading(type = 'user') {
    const text = type === 'all' ? 'Enviando notificaci\u00f3n masiva...' : 'Enviando notificaci\u00f3n...';
    document.getElementById('loadingText').textContent = text;
    document.getElementById('loadingOverlay').classList.add('show');
    document.getElementById('btnSend').disabled = true;
}

function hideLoading(success = true) {
    document.getElementById('loadingOverlay').classList.remove('show');
    document.getElementById('btnSend').disabled = false;
}

function showResultModal(success, message) {
    const body = document.getElementById('resultModalBody');
    const btn = document.getElementById('resultModalBtn');
    
    if (success) {
        body.innerHTML = `
            <div style="width:80px;height:80px;border-radius:50%;background:#d4edda;display:flex;align-items:center;justify-content:center;margin:0 auto 1.5rem;">
                <i class="fas fa-check" style="font-size:2.5rem;color:#28a745;"></i>
            </div>
            <h4 class="fw-bold mb-2" style="color:#155724;">\u00a1Env\u00edo exitoso!</h4>
            <p class="text-muted mb-0">${message}</p>
        `;
        btn.className = 'btn btn-lg px-5 btn-success';
        btn.style.borderRadius = '10px';
    } else {
        body.innerHTML = `
            <div style="width:80px;height:80px;border-radius:50%;background:#f8d7da;display:flex;align-items:center;justify-content:center;margin:0 auto 1.5rem;">
                <i class="fas fa-times" style="font-size:2.5rem;color:#dc3545;"></i>
            </div>
            <h4 class="fw-bold mb-2" style="color:#721c24;">Error al enviar</h4>
            <p class="text-muted mb-0">${message}</p>
        `;
        btn.className = 'btn btn-lg px-5 btn-danger';
        btn.style.borderRadius = '10px';
    }
    
    const modal = new bootstrap.Modal(document.getElementById('resultModal'));
    modal.show();
}

function resetForm() {
    document.getElementById('notificationForm').reset();
    document.querySelectorAll('.type-option').forEach(el => el.classList.remove('selected'));
    document.getElementById('userSelectSection').style.display = 'none';
    document.getElementById('massiveWarning').style.display = 'none';
    selectedType = null;
    
    const btnSend = document.getElementById('btnSend');
    btnSend.classList.remove('btn-send-all');
    btnSend.classList.add('btn-send');
    btnSend.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Enviar Notificación';
}
</script>
@endsection
