<div>
    @section('title', 'WhatsApp Business')

    @push('styles')
    <style>
        .whatsapp-hero { background: linear-gradient(135deg, #25D366 0%, #128C7E 100%); color:#fff; border-radius:.75rem; padding:1.4rem 1.6rem; }
        .whatsapp-hero h2 { color:#fff; margin:0; }
        .whatsapp-hero p { opacity:.9; margin:0; }

        .stat-card { border:1px solid rgba(0,0,0,.06); border-radius:.65rem; padding:.9rem 1rem;
                     transition:all .2s; display:flex; align-items:center; gap:.85rem; height:100%; background:#fff; }
        .stat-card:hover { box-shadow:0 6px 18px rgba(0,0,0,.07); transform:translateY(-1px); }
        .stat-card .stat-icon { width:44px; height:44px; border-radius:11px; flex:0 0 44px;
                                display:flex; align-items:center; justify-content:center; font-size:1.15rem; }
        .stat-card .stat-value { font-size:1.35rem; font-weight:600; line-height:1; }
        .stat-card .stat-label { font-size:.72rem; color:var(--bs-secondary-color);
                                 text-transform:uppercase; letter-spacing:.4px; font-weight:600; }
    </style>
    @endpush

    {{-- Breadcrumb --}}
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">WhatsApp</li>
        </ol>
    </nav>

    {{-- Hero Section --}}
    <div class="whatsapp-hero mb-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="position-relative">
                <div class="avatar avatar-lg">
                    <span class="avatar-initial rounded-circle bg-white text-success p-2">
                        <i class="ri ri-whatsapp-line ri-24px"></i>
                    </span>
                    @if($status === 'connected')
                        <span class="position-absolute top-0 start-100 translate-middle p-1 bg-success border border-light rounded-circle">
                            <span class="visually-hidden">Connected</span>
                        </span>
                    @endif
                </div>
            </div>
            <div>
                <h2 class="fw-semibold mb-1"><i class="ri ri-whatsapp-line me-2"></i>WhatsApp Business</h2>
                <p class="mb-0 opacity-75">
                    @if($status === 'connected')
                        <span class="badge bg-white text-success me-2"><i class="ri ri-checkbox-circle-fill me-1"></i>Conectado</span>
                        @if($user)
                            {{ $user['name'] ?? 'Usuario' }} • {{ $user['id'] ?? 'N/A' }}
                        @endif
                    @elseif($connectionError)
                        <span class="badge bg-white text-danger me-2"><i class="ri ri-error-warning-fill me-1"></i>Error</span>
                        {{ $connectionError }}
                    @else
                        <span class="badge bg-white text-warning me-2"><i class="ri ri-time-fill me-1"></i>Desconectado</span>
                        No hay sesión activa de WhatsApp
                    @endif
                </p>
            </div>
        </div>
        <div class="d-flex gap-2">
            <button wire:click="refresh" class="btn btn-light btn-sm" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="refresh">
                    <i class="ri ri-refresh-line me-1"></i>Actualizar
                </span>
                <span wire:loading wire:target="refresh">
                    <span class="spinner-border spinner-border-sm me-1"></span>Cargando...
                </span>
            </button>
        </div>
    </div>

    <!-- Enhanced Stats Cards with Progress Bars -->
    @php
        $totalMsgs = max($stats['total'], 1);
        $sentPercent = round(($stats['sent'] / $totalMsgs) * 100, 1);
        $deliveredPercent = round(($stats['delivered'] / $totalMsgs) * 100, 1);
        $readPercent = round(($stats['read'] / $totalMsgs) * 100, 1);
        $failedPercent = round(($stats['failed'] / $totalMsgs) * 100, 1);
        $pendingPercent = round(($stats['pending'] / $totalMsgs) * 100, 1);
        $successRate = $totalMsgs > 0 ? round((($stats['delivered'] + $stats['read']) / $totalMsgs) * 100, 1) : 0;
    @endphp

    <div class="row g-4 mb-4">
        <!-- Summary Row -->
        <div class="col-md-8">
            <div class="card border-0 shadow-sm bg-gradient-primary text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-1 text-white">Tasa de Éxito General</h4>
                            <p class="mb-0 opacity-75">Mensajes entregados y leídos</p>
                        </div>
                        <div class="text-end">
                            <h2 class="mb-0 fw-bold">{{ $successRate }}%</h2>
                            <div class="progress mt-2" style="height: 8px; max-width: 150px;">
                                <div class="progress-bar bg-white" style="width: {{ $successRate }}%"></div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex gap-4 mt-3 pt-3 border-top border-white border-opacity-25">
                        <div>
                            <h5 class="text-white mb-0">{{ $stats['total'] }}</h5>
                            <small class="opacity-75">Total</small>
                        </div>
                        <div>
                            <h5 class="text-white mb-0">{{ $todayMessages }}</h5>
                            <small class="opacity-75">Hoy</small>
                        </div>
                        <div>
                            <h5 class="text-white mb-0">{{ $stats['pending'] }}</h5>
                            <small class="opacity-75">Pendientes</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center d-flex flex-column justify-content-center">
                    <div class="avatar avatar-lg mx-auto mb-2">
                        <span class="avatar-initial rounded-circle bg-label-warning">
                            <i class="ri ri-calendar-check-line ri-24px"></i>
                        </span>
                    </div>
                    <h2 class="mb-1 fw-bold">{{ $todayMessages }}</h2>
                    <p class="text-muted mb-0">Mensajes Hoy</p>
                    <small class="text-muted">{{ now()->format('d/m/Y') }}</small>
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
                            <h4 class="mb-0">{{ $stats['sent'] }}</h4>
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
                            <h4 class="mb-0">{{ $stats['delivered'] }}</h4>
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
                            <h4 class="mb-0">{{ $stats['read'] }}</h4>
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
                            <h4 class="mb-0">{{ $stats['failed'] }}</h4>
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

    <!-- Daily Messages Chart -->
    @if(count($dailyMessages) > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent">
                    <h5 class="card-title mb-0">
                        <i class="ri ri-bar-chart-2-line me-2 text-primary"></i>Mensajes Últimos 7 Días
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-end justify-content-between" style="height: 150px;">
                        @php
                            $maxCount = max(array_column($dailyMessages, 'count'));
                            $maxCount = max($maxCount, 1);
                        @endphp
                        @foreach($dailyMessages as $day)
                            @php
                                $barHeight = round(($day['count'] / $maxCount) * 120);
                                $dayLabel = \Carbon\Carbon::parse($day['date'])->format('D');
                                $dateLabel = \Carbon\Carbon::parse($day['date'])->format('d/m');
                            @endphp
                            <div class="text-center flex-fill px-1">
                                <small class="d-block mb-1 fw-medium">{{ $day['count'] }}</small>
                                <div class="bg-primary rounded-top mx-auto"
                                     style="width: 60%; height: {{ max($barHeight, 4) }}px; min-height: 4px;"
                                     title="{{ $dateLabel }}: {{ $day['count'] }} mensajes"></div>
                                <small class="d-block mt-1 text-muted">{{ $dayLabel }}</small>
                                <small class="d-block text-muted" style="font-size: 0.65rem;">{{ $dateLabel }}</small>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Enhanced Tabs Navigation -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white pb-0">
            <ul class="nav nav-pills nav-fill flex-column flex-sm-row mb-0" role="tablist">
                <li class="nav-item" role="presentation">
                    <button type="button"
                            class="nav-link {{ $activeTab === 'dashboard' ? 'active' : '' }} py-3"
                            wire:click="setActiveTab('dashboard')">
                        <i class="ri ri-dashboard-line me-2 ri-20px"></i>
                        <span class="d-none d-sm-inline">Dashboard</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button type="button"
                            class="nav-link {{ $activeTab === 'conexion' ? 'active' : '' }} py-3"
                            wire:click="setActiveTab('conexion')">
                        <i class="ri ri-link me-2 ri-20px"></i>
                        <span class="d-none d-sm-inline">Conexión</span>
                        @if($status !== 'connected')
                            <span class="badge bg-danger ms-2">!</span>
                        @endif
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button type="button"
                            class="nav-link {{ $activeTab === 'mensajes' ? 'active' : '' }} py-3 position-relative"
                            wire:click="setActiveTab('mensajes')"
                            @if($status !== 'connected') disabled @endif>
                        <i class="ri ri-message-2-line me-2 ri-20px"></i>
                        <span class="d-none d-sm-inline">Mensajes</span>

                        @if($status !== 'connected')
                            <i class="ri ri-lock-line ms-1 text-muted"></i>
                        @endif
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button type="button"
                            class="nav-link {{ $activeTab === 'conversaciones' ? 'active' : '' }} py-3"
                            wire:click="setActiveTab('conversaciones')"
                            @if($status !== 'connected') disabled @endif>
                        <i class="ri ri-chat-3-line me-2 ri-20px"></i>
                        <span class="d-none d-sm-inline">Conversaciones</span>
                        @if($status !== 'connected')
                            <i class="ri ri-lock-line ms-1 text-muted"></i>
                        @endif
                    </button>
                </li>
            </ul>
        </div>

        <!-- Tab Content -->
        <div class="tab-content p-0">
            <!-- Dashboard Tab -->
            @if($activeTab === 'dashboard')
                <div class="tab-pane fade show active">
                    <div class="card-body">
                        <div class="row g-4">
                            <!-- Enhanced Account Info -->
                            @if($user && $status === 'connected')
                                <div class="col-lg-4">
                                    <div class="card border-0 shadow-sm h-100">
                                        <div class="card-header bg-transparent pb-0">
                                            <h5 class="card-title mb-0">
                                                <i class="ri ri-account-circle-line me-2 text-primary"></i>Cuenta Conectada
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="text-center mb-4">
                                                <div class="avatar avatar-lg mb-3">
                                                    <span class="avatar-initial rounded-circle bg-success">
                                                        <i class="ri ri-whatsapp-line ri-36px"></i>
                                                    </span>
                                                </div>
                                                <h4 class="mb-1">{{ $user['name'] ?? 'Usuario' }}</h4>
                                                <p class="text-muted mb-0">{{ $user['id'] ?? 'N/A' }}</p>
                                            </div>

                                            <div class="info-container">
                                                <ul class="list-unstyled mb-0">
                                                    <li class="py-2 border-bottom">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <span class="fw-medium">Estado</span>
                                                            <span class="badge bg-success">
                                                                <i class="ri ri-checkbox-circle-fill me-1"></i>Activo
                                                            </span>
                                                        </div>
                                                    </li>
                                                    @if($lastSeen)
                                                    <li class="py-2 border-bottom">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <span class="fw-medium">Última actividad</span>
                                                            <span class="text-muted">{{ \Carbon\Carbon::parse($lastSeen)->diffForHumans() }}</span>
                                                        </div>
                                                    </li>
                                                    @endif

                                                    <li class="py-2">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <span class="fw-medium">Plataforma</span>
                                                            <span class="badge bg-label-info">WhatsApp Business</span>
                                                        </div>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- Enhanced Recent Messages -->
                            <div class="{{ $user && $status === 'connected' ? 'col-lg-8' : 'col-12' }}">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                                        <h5 class="card-title mb-0">
                                            <i class="ri ri-chat-3-line me-2 text-primary"></i>Mensajes Recientes
                                        </h5>
                                        <div class="d-flex gap-2">
                                            @if($status === 'connected')
                                                <button class="btn btn-sm btn-outline-primary" wire:click="setActiveTab('mensajes')">
                                                    <i class="ri ri-add-line me-1"></i>Nuevo Mensaje
                                                </button>
                                            @endif
                                            <button class="btn btn-sm btn-outline-secondary" wire:click="loadMoreMessages">
                                                <i class="ri ri-refresh-line me-1"></i>Actualizar
                                            </button>
                                        </div>
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
                                                            <th class="text-center">Tipo</th>
                                                            <th class="text-center">Estado</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($messages as $message)
                                                            <tr>
                                                                <td class="text-nowrap">
                                                                    <small class="text-muted">
                                                                        {{ \Carbon\Carbon::parse($message['createdAt'] ?? now())->format('d/m/Y H:i') }}
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
                                                                    <div class="text-truncate" style="max-width: 250px;">
                                                                        {{ Str::limit($message['message'] ?? $message['body'] ?? '', 60) }}
                                                                    </div>
                                                                </td>
                                                                <td class="text-center">
                                                                    @php
                                                                        $msgType = $message['type'] ?? 'text';
                                                                        $typeConfig = match($msgType) {
                                                                            'text' => ['class' => 'bg-label-primary', 'icon' => 'ri-message-line', 'label' => 'Texto'],
                                                                            'image' => ['class' => 'bg-label-success', 'icon' => 'ri-image-line', 'label' => 'Imagen'],
                                                                            'document' => ['class' => 'bg-label-warning', 'icon' => 'ri-file-line', 'label' => 'Documento'],
                                                                            'audio' => ['class' => 'bg-label-info', 'icon' => 'ri-volume-up-line', 'label' => 'Audio'],
                                                                            default => ['class' => 'bg-label-secondary', 'icon' => 'ri-question-line', 'label' => ucfirst($msgType)]
                                                                        };
                                                                    @endphp
                                                                    <span class="badge {{ $typeConfig['class'] }}" title="{{ $typeConfig['label'] }}">
                                                                        <i class="{{ $typeConfig['icon'] }} me-1"></i>{{ $typeConfig['label'] }}
                                                                    </span>
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
                                                                            'queued' => ['class' => 'bg-secondary', 'icon' => 'ri-inbox-line', 'label' => 'En cola'],
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
                                                <h5 class="mb-2">No hay mensajes</h5>
                                                <p class="text-muted mb-3">Los mensajes recientes aparecerán aquí</p>
                                                <div class="d-flex justify-content-center gap-2">
                                                    @if($status === 'connected')
                                                        <button class="btn btn-primary" wire:click="setActiveTab('mensajes')">
                                                            <i class="ri ri-send-plane-line me-1"></i>Enviar mensaje
                                                        </button>
                                                    @elseif($status !== 'connected')
                                                        <button class="btn btn-success" wire:click="setActiveTab('conexion')">
                                                            <i class="ri ri-link me-1"></i>Conectar WhatsApp
                                                        </button>
                                                    @endif
                                                    <button class="btn btn-outline-secondary" wire:click="refresh">
                                                        <i class="ri ri-refresh-line me-1"></i>Actualizar
                                                    </button>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Enhanced Quick Actions -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card border-0 bg-light-subtle">
                                    <div class="card-body">
                                        <h6 class="mb-3">
                                            <i class="ri ri-flashlight-line me-2 text-primary"></i>Acciones Rápidas
                                        </h6>
                                        <div class="row g-3">
                                            <div class="col-6 col-md-3">
                                                <button class="btn btn-outline-primary w-100 py-3" wire:click="setActiveTab('conexion')">
                                                    <i class="ri ri-link d-block ri-24px mb-2"></i>
                                                    <span class="d-block small fw-medium">Conexión</span>
                                                    @if($status !== 'connected')
                                                        <span class="badge bg-danger mt-1">Desconectado</span>
                                                    @endif
                                                </button>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <button class="btn btn-outline-success w-100 py-3"
                                                        wire:click="setActiveTab('mensajes')"
                                                        @if($status !== 'connected') disabled @endif>
                                                    <i class="ri ri-send-plane-line d-block ri-24px mb-2"></i>
                                                    <span class="d-block small fw-medium">Enviar</span>
                                                    @if($status !== 'connected')
                                                        <span class="badge bg-secondary mt-1">Bloqueado</span>
                                                    @endif
                                                </button>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <button class="btn btn-outline-info w-100 py-3" wire:click="exportStats">
                                                    <i class="ri ri-file-download-line d-block ri-24px mb-2"></i>
                                                    <span class="d-block small fw-medium">Exportar</span>
                                                </button>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary w-100 py-3">
                                                    <i class="ri ri-arrow-left-line d-block ri-24px mb-2"></i>
                                                    <span class="d-block small fw-medium">Volver</span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Conexión Tab -->
            @if($activeTab === 'conexion')
                <div class="tab-pane fade show active">
                    <div class="card-body p-4">
                        <livewire:admin.whatsapp.conexion :key="'conexion-'.now()" />
                    </div>
                </div>
            @endif

            <!-- Mensajes Tab -->
            @if($activeTab === 'mensajes')
                <div class="tab-pane fade show active">
                    <div class="card-body p-4">
                        @if($status === 'connected')
                            <livewire:admin.whatsapp.envio-mensajes :key="'envio-'.now()" />
                        @else
                            <div class="text-center py-5">
                                <div class="avatar avatar-xl mb-4">
                                    <span class="avatar-initial rounded-circle bg-label-warning">
                                        <i class="ri ri-wifi-off-line ri-36px"></i>
                                    </span>
                                </div>
                                <h4 class="mb-3">WhatsApp no conectado</h4>
                                <p class="text-muted mb-4 lead">
                                    Necesitas conectar WhatsApp antes de enviar mensajes.<br>
                                    La conexión es segura y se realiza mediante código QR.
                                </p>
                                <div class="d-flex justify-content-center gap-3">
                                    <button class="btn btn-success btn-lg" wire:click="setActiveTab('conexion')">
                                        <i class="ri ri-link me-2"></i>Ir a Conexión
                                    </button>
                                    <button class="btn btn-outline-secondary" wire:click="refresh">
                                        <i class="ri ri-refresh-line me-1"></i>Reintentar
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif


        </div>
    </div>

    <!-- Enhanced Loading Overlay -->

</div>

@push('scripts')
<script>
    document.addEventListener('livewire:init', () => {
        // Handle notifications
        Livewire.on('notify', (data) => {
            if (typeof toastr !== 'undefined') {
                toastr[data.type](data.message);
            } else {
                // Fallback to browser notification
                console.log(`${data.type}: ${data.message}`);
            }
        });

        // Auto-refresh dashboard every 30 seconds when active
        let refreshInterval;
        const startAutoRefresh = () => {
            refreshInterval = setInterval(() => {
                if (typeof $wire !== 'undefined') {
                    $wire.refresh();
                }
            }, 30000);
        };

        const stopAutoRefresh = () => {
            if (refreshInterval) {
                clearInterval(refreshInterval);
                refreshInterval = null;
            }
        };

        // Start auto-refresh when dashboard is active
        if ('{{ $activeTab }}' === 'dashboard') {
            startAutoRefresh();
        }

        // Listen for tab changes
        Livewire.on('tabChanged', (event) => {
            const tab = Array.isArray(event) ? event[0] : event;
            if (tab === 'dashboard') {
                startAutoRefresh();
            } else {
                stopAutoRefresh();
            }
        });

        // Cleanup on page unload
        window.addEventListener('beforeunload', stopAutoRefresh);
    });
</script>
@endpush

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

.nav-pills .nav-link.active {
    background-color: #666cff !important;
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

@media (max-width: 768px) {
    .nav-pills .nav-link span:not(.d-none) {
        font-size: 0.875rem;
    }
}
</style>
@endpush
