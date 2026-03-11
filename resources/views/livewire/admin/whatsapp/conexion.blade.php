<div>
    <!-- Enhanced Alerts -->
    @if($error)
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <div class="d-flex align-items-center">
                <div class="flex-shrink-0 me-3">
                    <div class="avatar avatar-sm bg-danger rounded-circle">
                        <i class="ri ri-error-warning-line ri-16px"></i>
                    </div>
                </div>
                <div class="flex-grow-1">
                    <h6 class="alert-heading mb-1">Error de Conexión</h6>
                    <p class="mb-0">{{ $error }}</p>
                </div>
                <button type="button" class="btn-close" wire:click="clearMessages"></button>
            </div>
        </div>
    @endif

    @if($success)
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <div class="d-flex align-items-center">
                <div class="flex-shrink-0 me-3">
                    <div class="avatar avatar-sm bg-success rounded-circle">
                        <i class="ri ri-checkbox-circle-line ri-16px"></i>
                    </div>
                </div>
                <div class="flex-grow-1">
                    <h6 class="alert-heading mb-1">¡Éxito!</h6>
                    <p class="mb-0">{{ $success }}</p>
                </div>
                <button type="button" class="btn-close" wire:click="clearMessages"></button>
            </div>
        </div>
    @endif

    <div class="row g-4">
        <!-- Connection Status Card -->
        <div class="col-xl-4 col-lg-6">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-header bg-gradient-primary text-white rounded-top">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0 text-white">
                            <i class="ri ri-signal-wifi-line me-2"></i>Estado de Conexión
                        </h5>
                        <span class="badge bg-white text-primary">
                            <i class="ri ri-refresh-line me-1"></i>Live
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Status Indicator -->
                    <div class="text-center mb-4">
                        <div class="position-relative d-inline-block">
                            <div class="avatar avatar-lg mb-3">
                               
                               <div class="avatar avatar-online me-3">
                                    @if(Auth::check() && Auth::user()->initials)
                                        <span class="avatar-initials bg-label-success"><i class="ri ri-check-double-line ri-24px"></i></span>
                                    @else
                                        <img src="{{ asset('materialize/assets/img/avatars/1.png') }}" alt="avatar" class="w-px-40 h-auto rounded-circle" />
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <h3 class="mb-2 text-{{ $statusColor }}">{{ $statusText }}</h3>

                        @if($user && $status === 'connected')
                            <div class="bg-light rounded p-3 mb-3">
                                <div class="d-flex align-items-center justify-content-center">
                                    <div class="avatar avatar-online me-3">
                                    @if(Auth::check() && Auth::user()->initials)
                                        <span class="avatar-initials bg-primary text-white">{{ Auth::user()->initials }}</span>
                                    @else
                                        <img src="{{ asset('materialize/assets/img/avatars/1.png') }}" alt="avatar" class="w-px-40 h-auto rounded-circle" />
                                    @endif
                                    </div>
                                    <div class="text-start">
                                        <p class="fw-medium mb-0">{{ $user['name'] ?? 'Usuario WhatsApp' }}</p>
                                        <small class="text-muted">{{ $user['id'] ?? 'N/A' }}</small>
                                    </div>
                                </div>
                            </div>
                        @elseif($status === 'service_unavailable')
                            <div class="alert alert-warning">
                                <i class="ri ri-alert-line me-1"></i>Servidor no disponible
                            </div>
                        @elseif($status === 'disconnected')
                            <p class="text-muted">No hay sesión activa</p>
                        @endif
                    </div>

                    <!-- Connection Timeline -->
                    @if($status === 'connected' && $lastSeen)
                        <div class="border-top pt-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <small class="text-muted">
                                    <i class="ri ri-time-line me-1"></i>Última actividad
                                </small>
                                <span class="badge bg-label-info">{{ \Carbon\Carbon::parse($lastSeen)->diffForHumans() }}</span>
                            </div>
                            
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-success" 
                                     role="progressbar" 
                                     style="width: {{ $uptimePercentage ?? 95 }}%" 
                                     aria-valuenow="{{ $uptimePercentage ?? 95 }}" 
                                     aria-valuemin="0" 
                                     aria-valuemax="100"></div>
                            </div>
                            <small class="text-muted mt-1 d-block">Uptime: {{ $uptimePercentage ?? 95 }}%</small>
                        </div>
                    @endif

                    <!-- Action Buttons -->
                    <div class="d-grid gap-2 mt-4">
                       
                            <button wire:click="connect"
                                    class="btn btn-success btn-lg"
                                    wire:loading.attr="disabled"
                                    wire:target="connect">
                                <span wire:loading.remove wire:target="connect">
                                    <i class="ri ri-link me-2"></i>Conectar WhatsApp
                                </span>
                                <span wire:loading wire:target="connect">
                                    <span class="spinner-border spinner-border-sm me-2"></span>Conectando...
                                </span>
                            </button>
                    

                        @if($status === 'connected')
                            <button wire:click="disconnect"
                                    class="btn btn-outline-danger"
                                    wire:loading.attr="disabled"
                                    wire:target="disconnect"
                                    onclick="return confirm('¿Está seguro de desconectar WhatsApp? Esta acción afectará todos los servicios.')">
                                <span wire:loading.remove wire:target="disconnect">
                                    <i class="ri ri-logout-circle-line me-2"></i>Desconectar
                                </span>
                                <span wire:loading wire:target="disconnect">
                                    <span class="spinner-border spinner-border-sm me-2"></span>Desconectando...
                                </span>
                            </button>
                        @endif

                        <button wire:click="checkStatus"
                                class="btn btn-outline-primary"
                                wire:loading.attr="disabled"
                                wire:target="checkStatus">
                            <span wire:loading.remove wire:target="checkStatus">
                                <i class="ri ri-refresh-line me-2"></i>Actualizar Estado
                            </span>
                            <span wire:loading wire:target="checkStatus">
                                <span class="spinner-border spinner-border-sm me-2"></span>Verificando...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- QR Code / Instructions Card -->
        <div class="col-xl-4 col-lg-6">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-header bg-gradient-info text-white">
                    <h5 class="card-title mb-0 text-white">
                        @if($qrCode && ($status === 'qr_ready' || $status === 'connecting'))
                            <i class="ri ri-qr-code-line me-2"></i>Escanear Código QR
                        @else
                            <i class="ri ri-information-line me-2"></i>Instrucciones de Conexión
                        @endif
                    </h5>
                </div>
                <div class="card-body">
                    @if($qrCode && ($status === 'qr_ready' || $status === 'connecting'))
                        <!-- QR Code Display -->
                        <div class="text-center">
                            <div class="qr-container d-inline-block p-4 bg-white rounded shadow-lg mb-4 position-relative">
                                <img src="{{ $qrCode }}" alt="Código QR WhatsApp" class="img-fluid rounded" style="max-width: 200px;">
                                <div class="position-absolute top-0 start-100 translate-middle badge bg-danger rounded-pill">
                                    <i class="ri ri-timer-flash-line"></i>
                                </div>
                            </div>

                            <div class="alert alert-warning border-0">
                                <div class="d-flex align-items-start">
                                    <div class="flex-shrink-0 me-3">
                                        <i class="ri ri-timer-line ri-20px text-warning"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="alert-heading mb-1">Tiempo Limitado</h6>
                                        <p class="mb-0 small">El código QR expira en 60 segundos. Escanéalo inmediatamente.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-3">
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-warning" 
                                         role="progressbar" 
                                         style="width: 75%"></div>
                                </div>
                                <small class="text-muted">Expira en: 60 segundos</small>
                            </div>
                        </div>
                    @elseif($status === 'connecting')
                        <!-- Loading State -->
                        <div class="text-center py-5">
                            <div class="mb-4">
                                <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                            <h5>Generando conexión segura...</h5>
                            <p class="text-muted">Preparando código QR para vinculación</p>
                            
                            <div class="progress mt-3" style="height: 6px;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                     role="progressbar" 
                                     style="width: 45%"></div>
                            </div>
                        </div>
                    @else
                        <!-- Connection Instructions -->
                        <div class="instruction-guide">
                            <div class="d-flex mb-4">
                                <div class="flex-shrink-0 me-3">
                                    <div class="avatar avatar-sm bg-primary rounded-circle">
                                        <span class="text-white">1</span>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">Abre WhatsApp en tu móvil</h6>
                                    <p class="text-muted mb-0 small">Asegúrate de tener buena conexión a internet</p>
                                </div>
                            </div>

                            <div class="d-flex mb-4">
                                <div class="flex-shrink-0 me-3">
                                    <div class="avatar avatar-sm bg-primary rounded-circle">
                                        <span class="text-white">2</span>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">Accede a Configuración</h6>
                                    <p class="text-muted mb-0 small">
                                        <strong>Android:</strong> Menú (⋮) → Dispositivos vinculados<br>
                                        <strong>iPhone:</strong> Ajustes → Dispositivos vinculados
                                    </p>
                                </div>
                            </div>

                            <div class="d-flex mb-4">
                                <div class="flex-shrink-0 me-3">
                                    <div class="avatar avatar-sm bg-primary rounded-circle">
                                        <span class="text-white">3</span>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">Vincula un nuevo dispositivo</h6>
                                    <p class="text-muted mb-0 small">Toca "Vincular un dispositivo" y escanea el código QR</p>
                                </div>
                            </div>

                            <div class="d-flex">
                                <div class="flex-shrink-0 me-3">
                                    <div class="avatar avatar-sm bg-success rounded-circle">
                                        <i class="ri ri-check-line text-white"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">¡Conexión establecida!</h6>
                                    <p class="text-muted mb-0 small">WhatsApp Business estará listo para enviar mensajes</p>
                                </div>
                            </div>
                        </div>

                        @if($status === 'connected')
                            <div class="alert alert-success border-0 mt-4">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0 me-3">
                                        <i class="ri ri-checkbox-circle-fill ri-20px text-success"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="alert-heading mb-1">Conexión Activa</h6>
                                        <p class="mb-0">WhatsApp está conectado y funcionando correctamente</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>

        <!-- Connection Statistics Card -->
        <div class="col-xl-4 col-lg-12">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-header bg-gradient-dark text-white">
                    <h5 class="card-title mb-0 text-white">
                        <i class="ri ri-bar-chart-line me-2"></i>Estadísticas de Conexión
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="border rounded p-3 text-center">
                                <div class="avatar avatar-md bg-label-primary mb-2 mx-auto">
                                    <i class="ri ri-message-2-line ri-24px"></i>
                                </div>
                                <h4 class="mb-0">{{ number_format($mensajesHoy) }}</h4>
                                <small class="text-muted">Mensajes hoy</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-3 text-center">
                                <div class="avatar avatar-md bg-label-success mb-2 mx-auto">
                                    <i class="ri ri-check-double-line ri-24px"></i>
                                </div>
                                <h4 class="mb-0">{{ $tasaExito }}%</h4>
                                <small class="text-muted">Tasa éxito</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-3 text-center">
                                <div class="avatar avatar-md bg-label-warning mb-2 mx-auto">
                                    <i class="ri ri-time-line ri-24px"></i>
                                </div>
                                <h4 class="mb-0">{{ $latenciaPromedio }}ms</h4>
                                <small class="text-muted">Latencia</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-3 text-center">
                                <div class="avatar avatar-md bg-label-info mb-2 mx-auto">
                                    <i class="ri ri-calendar-line ri-24px"></i>
                                </div>
                                <h4 class="mb-0">{{ $this->diasActivoFormateado }}</h4>
                                <small class="text-muted">Tiempo activo</small>
                            </div>
                        </div>
                    </div>

                    <!-- Connection Health -->
                    <div class="mt-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">Salud de conexión</span>
                            <span class="badge bg-label-{{ $status === 'connected' ? 'success' : ($status === 'disconnected' ? 'danger' : 'warning') }}">
                                {{ $status === 'connected' ? 'Excelente' : ($status === 'disconnected' ? 'Desconectado' : 'Problemas') }}
                            </span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-{{ $status === 'connected' ? 'success' : ($status === 'disconnected' ? 'danger' : 'warning') }}" 
                                 role="progressbar" 
                                 style="width: {{ $saludConexion }}%"></div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="mt-4 pt-3 border-top">
                        <h6 class="mb-3">Acciones Rápidas</h6>
                        <div class="d-grid gap-2">
                            <button class="btn btn-outline-secondary btn-sm">
                                <i class="ri ri-download-line me-1"></i>Exportar Logs
                            </button>
                            <button class="btn btn-outline-secondary btn-sm">
                                <i class="ri ri-settings-3-line me-1"></i>Configuración Avanzada
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Technical Information Banner -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card bg-gradient-light border-0">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-1 text-center mb-3 mb-md-0">
                            <div class="avatar avatar-lg bg-primary rounded-circle">
                                <i class="ri ri-information-line ri-24px text-white"></i>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <h6 class="mb-1">Información Técnica Importante</h6>
                            <p class="text-muted mb-0 small">
                                La conexión se mantiene activa mientras el servidor esté funcionando. 
                                Si cierra esta página, la conexión continuará activa. 
                                WhatsApp puede desconectarse si el teléfono está sin internet por mucho tiempo.
                            </p>
                        </div>
                        <div class="col-md-3 text-md-end mt-2 mt-md-0">
                            <span class="badge bg-{{ $statusColor }} fs-6 px-3 py-2">
                                <i class="{{ $statusIcon }} me-1"></i>{{ $statusText }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #666cff 0%, #7367f0 100%) !important;
}

.bg-gradient-info {
    background: linear-gradient(135deg, #28c76f 0%, #28c76f 100%) !important;
}

.bg-gradient-dark {
    background: linear-gradient(135deg, #4b4b4b 0%, #2c2c2c 100%) !important;
}

.bg-gradient-light {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.spin-animation {
    animation: spin 1s linear infinite;
}

.qr-container {
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    border: 2px dashed #dee2e6;
    position: relative;
    transition: all 0.3s ease;
}

.qr-container:hover {
    border-color: #666cff;
    box-shadow: 0 0.5rem 1rem rgba(102, 108, 255, 0.15);
}

.instruction-guide .avatar {
    min-width: 32px;
    min-height: 32px;
}

.alert .avatar {
    min-width: 24px;
    min-height: 24px;
}

.progress-bar-animated {
    animation: progress-bar-stripes 1s linear infinite;
}

@keyframes progress-bar-stripes {
    0% { background-position-x: 1rem; }
}

.card {
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

.btn {
    transition: all 0.2s ease;
}

.btn:hover {
    transform: translateY(-1px);
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('livewire:init', () => {
    @if($pollingActive && ($status === 'connecting' || $status === 'qr_ready'))
        console.log('🔄 Iniciando monitoreo de conexión...');
        
        const statusInterval = setInterval(() => {
            @this.checkStatus()
                .then(() => {
                    console.log('✅ Estado actualizado');
                })
                .catch(error => {
                    console.error('❌ Error al actualizar estado:', error);
                });
        }, 5000);

        // Cleanup on navigation
        document.addEventListener('livewire:navigating', () => {
            console.log('🧹 Limpiando intervalo de monitoreo');
            clearInterval(statusInterval);
        });

        // Cleanup on tab close
        window.addEventListener('beforeunload', () => {
            clearInterval(statusInterval);
        });
    @endif

    // Connection status notifications
    Livewire.on('connection-status-updated', (event) => {
        const { status, message } = event;
        console.log(`📡 Estado de conexión actualizado: ${status}`);
        
        // Show toast notification
        if (typeof toastr !== 'undefined') {
            if (status === 'connected') {
                toastr.success(message || '¡Conexión establecida exitosamente!');
            } else if (status === 'disconnected') {
                toastr.warning(message || 'Conexión perdida');
            }
        }
    });
});
</script>
@endpush