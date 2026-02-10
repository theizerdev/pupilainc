<div>
    <!-- Stats Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar avatar-lg">
                                <span class="avatar-initial rounded-circle bg-label-{{ $statusColor }}">
                                    <i class="{{ $statusIcon }} ri-24px"></i>
                                </span>
                            </div>
                            <div>
                                <div class="d-flex align-items-center gap-2">
                                    <h4 class="mb-0">Estadísticas WhatsApp</h4>
                                    <span class="badge bg-{{ $statusColor }}">{{ $statusText }}</span>
                                </div>
                                @if($user)
                                    <p class="mb-0 text-muted">
                                        <i class="ri ri-phone-line me-1"></i>{{ $user['id'] ?? 'N/A' }}
                                        <span class="mx-2">•</span>
                                        <i class="ri ri-user-line me-1"></i>{{ $user['name'] ?? 'Usuario' }}
                                    </p>
                                @elseif($connectionError)
                                    <p class="mb-0 text-danger small">
                                        <i class="ri ri-error-warning-line me-1"></i>{{ $connectionError }}
                                    </p>
                                @else
                                    <p class="mb-0 text-muted">No hay sesión activa de WhatsApp</p>
                                @endif
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <button wire:click="refresh" class="btn btn-label-primary" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="refresh">
                                    <i class="ri ri-refresh-line me-1"></i>Actualizar
                                </span>
                                <span wire:loading wire:target="refresh">
                                    <span class="spinner-border spinner-border-sm me-1"></span>Actualizando...
                                </span>
                            </button>
                            @if($status === 'connected')
                                <button wire:click="testConnection" class="btn btn-label-success" wire:loading.attr="disabled">
                                    <span wire:loading.remove wire:target="testConnection">
                                        <i class="ri ri-pulse-line me-1"></i>Test
                                    </span>
                                    <span wire:loading wire:target="testConnection">
                                        <span class="spinner-border spinner-border-sm me-1"></span>Probando...
                                    </span>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Real Time Stats Cards -->
    @php
        $totalMsgs = max($stats['total'], 1);
        $sentPercent = round(($stats['sent'] / $totalMsgs) * 100, 1);
        $deliveredPercent = round(($stats['delivered'] / $totalMsgs) * 100, 1);
        $readPercent = round(($stats['read'] / $totalMsgs) * 100, 1);
        $failedPercent = round(($stats['failed'] / $totalMsgs) * 100, 1);
        $successRate = $totalMsgs > 0 ? round((($stats['delivered'] + $stats['read']) / $totalMsgs) * 100, 1) : 0;
    @endphp
    
    <div class="row g-4 mb-4">
        <!-- Success Rate Card -->
        <div class="col-12">
            <div class="card border-0 shadow-sm bg-gradient-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-1 text-white">Tasa de Éxito en Tiempo Real</h4>
                            <p class="mb-0 opacity-75">Mensajes entregados y leídos</p>
                        </div>
                        <div class="text-end">
                            <h2 class="mb-0 fw-bold">{{ $successRate }}%</h2>
                            <div class="progress mt-2" style="height: 8px; max-width: 150px;">
                                <div class="progress-bar bg-white" style="width: {{ $successRate }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Individual Stat Cards -->
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-primary">
                                <i class="ri ri-send-plane-fill ri-24px"></i>
                            </span>
                        </div>
                        <div class="text-end">
                            <h4 class="mb-0">{{ number_format($stats['sent']) }}</h4>
                            <small class="text-muted">Enviados</small>
                        </div>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-primary" style="width: {{ $sentPercent }}%"></div>
                    </div>
                    <div class="mt-2 text-end">
                        <small class="text-muted">{{ $sentPercent }}%</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-success">
                                <i class="ri ri-check-double-fill ri-24px"></i>
                            </span>
                        </div>
                        <div class="text-end">
                            <h4 class="mb-0">{{ number_format($stats['delivered']) }}</h4>
                            <small class="text-muted">Entregados</small>
                        </div>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-success" style="width: {{ $deliveredPercent }}%"></div>
                    </div>
                    <div class="mt-2 text-end">
                        <small class="text-muted">{{ $deliveredPercent }}%</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-info">
                                <i class="ri ri-eye-fill ri-24px"></i>
                            </span>
                        </div>
                        <div class="text-end">
                            <h4 class="mb-0">{{ number_format($stats['read']) }}</h4>
                            <small class="text-muted">Leídos</small>
                        </div>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-info" style="width: {{ $readPercent }}%"></div>
                    </div>
                    <div class="mt-2 text-end">
                        <small class="text-muted">{{ $readPercent }}%</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-danger">
                                <i class="ri ri-error-warning-fill ri-24px"></i>
                            </span>
                        </div>
                        <div class="text-end">
                            <h4 class="mb-0">{{ number_format($stats['failed']) }}</h4>
                            <small class="text-muted">Fallidos</small>
                        </div>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-danger" style="width: {{ $failedPercent }}%"></div>
                    </div>
                    <div class="mt-2 text-end">
                        <small class="text-muted">{{ $failedPercent }}%</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity Section -->
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="ri ri-chat-3-line me-2 text-primary"></i>Actividad Reciente
                    </h5>
                    <button class="btn btn-sm btn-outline-primary" wire:click="getRecentMessages">
                        <i class="ri ri-refresh-line me-1"></i>Actualizar
                    </button>
                </div>
                <div class="card-body p-0">
                    @if(count($messages) > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-nowrap">Fecha</th>
                                        <th class="text-nowrap">Contacto</th>
                                        <th>Mensaje</th>
                                        <th class="text-center">Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($messages as $message)
                                        <tr>
                                            <td class="text-nowrap">
                                                <small class="text-muted">
                                                    {{ \Carbon\Carbon::parse($message['timestamp'] ?? now())->format('d/m H:i') }}
                                                </small>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @if(($message['direction'] ?? 'outgoing') === 'outgoing')
                                                        <span class="badge bg-label-primary me-2">
                                                            <i class="ri ri-arrow-right-up-line"></i>
                                                        </span>
                                                    @else
                                                        <span class="badge bg-label-success me-2">
                                                            <i class="ri ri-arrow-left-down-line"></i>
                                                        </span>
                                                    @endif
                                                    <span>{{ $message['to'] ?? $message['from'] ?? 'Desconocido' }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="text-truncate" style="max-width: 200px;">
                                                    {{ Str::limit($message['text'] ?? $message['body'] ?? '', 50) }}
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                @php
                                                    $msgStatus = $message['status'] ?? 'unknown';
                                                    $badgeConfig = match($msgStatus) {
                                                        'sent' => ['class' => 'bg-primary', 'icon' => 'ri-check-line', 'label' => 'Enviado'],
                                                        'delivered' => ['class' => 'bg-success', 'icon' => 'ri-check-double-line', 'label' => 'Entregado'],
                                                        'read' => ['class' => 'bg-info', 'icon' => 'ri-eye-line', 'label' => 'Leído'],
                                                        'failed' => ['class' => 'bg-danger', 'icon' => 'ri-close-line', 'label' => 'Fallido'],
                                                        'pending' => ['class' => 'bg-warning', 'icon' => 'ri-time-line', 'label' => 'Pendiente'],
                                                        default => ['class' => 'bg-secondary', 'icon' => 'ri-question-line', 'label' => ucfirst($msgStatus)]
                                                    };
                                                @endphp
                                                <span class="badge {{ $badgeConfig['class'] }}" title="{{ $badgeConfig['label'] }}">
                                                    <i class="{{ $badgeConfig['icon'] }} me-1"></i>{{ $badgeConfig['label'] }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <div class="avatar avatar-lg mb-3">
                                <span class="avatar-initial rounded-circle bg-label-secondary">
                                    <i class="ri ri-chat-off-line ri-24px"></i>
                                </span>
                            </div>
                            <h6 class="mb-1">No hay actividad reciente</h6>
                            <p class="text-muted mb-3">Los mensajes aparecerán aquí en tiempo real</p>
                            <button class="btn btn-primary btn-sm" wire:click="getRecentMessages">
                                <i class="ri ri-refresh-line me-1"></i>Cargar mensajes
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent">
                    <h5 class="card-title mb-0">
                        <i class="ri ri-bar-chart-line me-2 text-primary"></i>Resumen General
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-3">
                        <div class="border rounded p-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted">Conversaciones activas</span>
                                <h4 class="mb-0">{{ count($conversations) }}</h4>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-primary" style="width: {{ min(100, count($conversations) * 10) }}%"></div>
                            </div>
                        </div>
                        
                        <div class="border rounded p-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted">Mensajes no leídos</span>
                                <h4 class="mb-0">{{ $unreadMessagesCount }}</h4>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-warning" style="width: {{ min(100, $unreadMessagesCount * 5) }}%"></div>
                            </div>
                        </div>
                        
                        <div class="border rounded p-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted">Última sincronización</span>
                                <span class="badge bg-label-success">
                                    <i class="ri ri-time-line me-1"></i>{{ now()->diffForHumans() }}
                                </span>
                            </div>
                        </div>
                        
                        <div class="border rounded p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">Estado API</span>
                                <span class="badge bg-{{ $statusColor }}">
                                    <i class="{{ $statusIcon }} me-1"></i>{{ $statusText }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Auto-refresh indicator -->
    <div class="position-fixed bottom-0 end-0 m-3">
        <div class="badge bg-label-info">
            <i class="ri ri-refresh-line me-1"></i>Actualización automática cada 5 minutos
        </div>
    </div>
</div>

@push('styles')
<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #666cff 0%, #7367f0 100%) !important;
}

.card {
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

.progress {
    border-radius: 10px;
}

.table th {
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 1px;
}

.badge {
    font-weight: 500;
}

.text-truncate {
    display: inline-block;
}
</style>
@endpush