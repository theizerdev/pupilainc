<div>
    @section('title', 'Canales de Notificación por Módulo')

    @push('styles')
    <style>
        .channels-hero { background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); color:#fff; border-radius:.75rem; padding:1.4rem 1.6rem; }
        .channels-hero h2 { color:#fff; margin:0; }
        .channels-hero p { opacity:.9; margin:0; }

        .nav-tabs-messaging .nav-link { border:none; color:#6b7280; font-weight:500; padding:.75rem 1rem; transition:all .2s; }
        .nav-tabs-messaging .nav-link:hover { color:#4f46e5; }
        .nav-tabs-messaging .nav-link.active { color:#4f46e5; background:#fff; border-bottom:2px solid #4f46e5; border-radius:.5rem .5rem 0 0; }

        .channel-row { display:grid; grid-template-columns:180px 1fr 200px 80px; gap:1rem; align-items:center; padding:.75rem; border-bottom:1px solid #f3f4f6; }
        .channel-row:hover { background:#f9fafb; }
        .channel-row:last-child { border-bottom:none; }

        @media (max-width: 992px) {
            .channel-row { grid-template-columns:160px 1fr 160px 72px; }
        }

        @media (max-width: 768px) {
            .channel-row { grid-template-columns:1fr 1fr; grid-auto-rows:auto; }
            .channel-row > :nth-child(1) { grid-column: 1 / span 2; }
            .channel-row > :nth-child(2) { grid-column: 1 / span 1; }
            .channel-row > :nth-child(3) { grid-column: 2 / span 1; }
            .channel-row > :nth-child(4) { grid-column: 1 / span 2; text-align:right; }
        }

        .channel-toggle { position:relative; width:44px; height:24px; }
        .channel-toggle input { opacity:0; width:0; height:0; }
        .channel-slider { position:absolute; cursor:pointer; inset:0; background:#d1d5db; border-radius:24px; transition:.3s; }
        .channel-slider:before { content:""; position:absolute; height:18px; width:18px; left:3px; bottom:3px; background:white; border-radius:50%; transition:.3s; }
        .channel-toggle input:checked + .channel-slider { background:#4f46e5; }
        .channel-toggle input:checked + .channel-slider:before { transform:translateX(20px); }

        .connection-badge { display:inline-flex; align-items:center; gap:.35rem; padding:.25rem .6rem; border-radius:20px; font-size:.75rem; font-weight:500; }
        .connection-badge.active { background:#dcfce7; color:#16a34a; }
        .connection-badge.inactive { background:#f3f4f6; color:#6b7280; }

        .edit-btn { opacity:0; transition:opacity .2s; }
        .channel-row:hover .edit-btn { opacity:1; }
    </style>
    @endpush

    {{-- Hero Section --}}
    <div class="channels-hero mb-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <h2 class="fw-semibold"><i class="ri ri-notification-3-line me-2"></i>Canales de Notificación</h2>
            <p class="mt-1">Configure qué conexión usar para cada tipo de notificación</p>
        </div>
        <a href="{{ route('admin.messaging.connections') }}" class="btn btn-light btn-sm">
            <i class="ri ri-link me-1"></i>Gestionar Conexiones
        </a>
    </div>

    {{-- No Connections Warning --}}
    @if(count($connections) === 0)
        <div class="alert alert-warning d-flex align-items-center">
            <i class="ri ri-alert-line me-2"></i>
            <div>
                <strong>No hay conexiones activas.</strong> Configure al menos una conexión de mensajería primero.
                <a href="{{ route('admin.messaging.connections') }}" class="alert-link ms-1">Crear conexión</a>
            </div>
        </div>
    @else
        {{-- Tabs --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent border-0 p-0">
                <ul class="nav nav-tabs nav-tabs-messaging" role="tablist">
                    @foreach($modules as $key => $module)
                        <li class="nav-item">
                            <button class="nav-link {{ $activeTab === $key ? 'active' : '' }}"
                                    wire:click="setActiveTab('{{ $key }}')">
                                <i class="ri ri-{{ $key === 'citas' ? 'calendar-check' : ($key === 'usuarios' ? 'user-settings' : ($key === 'consultas' ? 'stethoscope' : 'folder')) }} me-1"></i>
                                {{ $module['label'] }}
                            </button>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="card-body p-0">
                {{-- Header Row --}}
                <div class="channel-row bg-light fw-semibold small text-muted">
                    <div>Acción</div>
                    <div>Destinatario</div>
                    <div>Conexión</div>
                    <div class="text-center">Activo</div>
                </div>

                {{-- Channel Rows --}}
                @foreach($modules[$activeTab]['actions'] as $action)
                    @foreach($recipientTypes as $recipientKey => $recipientLabel)
                        @php
                            $channel = $this->getChannelValue($activeTab, $action, $recipientKey);
                            $selectedConnection = $this->getConnectionForChannel($activeTab, $action, $recipientKey);
                            $channelEnabled = $channel && !empty($channel['enabled']);
                            $connectionStatus = $selectedConnection['status'] ?? 'inactive';
                            $badgeClass = match ($connectionStatus) {
                                'active' => 'active',
                                default => 'inactive',
                            };
                        @endphp

                        <div class="channel-row">
                            <div>
                                <span class="badge bg-secondary">{{ ucfirst($action) }}</span>
                            </div>

                            <div>
                                <span class="text-dark">{{ $recipientLabel }}</span>
                            </div>

                            <div>
                                @if($selectedConnection)
                                    <span class="connection-badge {{ $badgeClass }}">
                                        <i class="ri {{ $badgeClass === 'active' ? 'ri-check-circle-fill' : 'ri-error-warning-fill' }}"></i>
                                        {{ $selectedConnection['name'] }}
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif

                                @if($channelEnabled)
                                    <span class="ms-2 badge bg-success-subtle text-success-emphasis border border-success-subtle">Activo</span>
                                @else
                                    <span class="ms-2 badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle">Inactivo</span>
                                @endif

                                @if(!$selectedConnection)
                                    <button class="btn btn-sm btn-outline-primary edit-btn" wire:click="openEditModal('{{ $activeTab }}', '{{ $action }}', '{{ $recipientKey }}')">
                                        <i class="ri ri-add-line"></i> Asignar
                                    </button>
                                @else
                                    <button class="btn btn-sm btn-link edit-btn" wire:click="openEditModal('{{ $activeTab }}', '{{ $action }}', '{{ $recipientKey }}')">
                                        <i class="ri ri-pencil-line"></i>
                                    </button>
                                @endif
                            </div>

                            <div class="text-center">
                                <label class="channel-toggle">
                                    <input type="checkbox"
                                           {{ $channel && $channel['enabled'] ? 'checked' : '' }}
                                           wire:change="toggleChannel('{{ $activeTab }}', '{{ $action }}', '{{ $recipientKey }}')">
                                    <span class="channel-slider"></span>
                                </label>
                            </div>
                        </div>
                    @endforeach
                @endforeach
            </div>
        </div>

        {{-- Edit Modal --}}
        @if($showEditModal)
            <div class="modal fade show" style="display:block;" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="ri ri-settings-3-line me-2"></i>
                                Configurar Canal
                            </h5>
                            <button type="button" class="btn-close" wire:click="closeEditModal"></button>
                        </div>

                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Módulo / Acción</label>
                                <input type="text" class="form-control"
                                       value="{{ $modules[$editingModule]['label'] ?? $editingModule }} - {{ ucfirst($editingAction) }}" disabled>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Destinatario</label>
                                <input type="text" class="form-control"
                                       value="{{ $recipientTypes[$editingRecipient] ?? $editingRecipient }}" disabled>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Conexión de Mensajería *</label>
                                <select class="form-select" wire:model="selectedConnectionId">
                                    <option value="">Seleccionar conexión...</option>
                                    @foreach($connections as $conn)
                                        <option value="{{ $conn['id'] }}">{{ $conn['name'] }} ({{ $conn['provider']['name'] ?? 'N/A' }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Prioridad</label>
                                <input type="number" class="form-control" wire:model="selectedPriority" min="1" max="10">
                                <small class="text-muted">1 = mayor prioridad</small>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="closeEditModal">Cancelar</button>
                            <button type="button" class="btn btn-primary" wire:click="saveChannelFromModal">
                                <i class="ri ri-save-line me-1"></i>Guardar
                            </button>
                        </div>
                    </div>
                </div>
                <div class="modal-backdrop fade show"></div>
            </div>
        @endif

        {{-- Info Box --}}
        <div class="alert alert-info d-flex align-items-start">
            <i class="ri ri-information-line me-2 mt-1"></i>
            <div>
                <strong>¿Cómo funciona?</strong>
                <ul class="mb-0 mt-1 small">
                    <li>Seleccione qué conexión usar para cada combinación de módulo, acción y destinatario</li>
                    <li>Active o desactive canales específicos con el toggle</li>
                    <li>Si no hay conexión configurada, el sistema usará la conexión por defecto de la empresa</li>
                </ul>
            </div>
        </div>
    @endif
</div>

