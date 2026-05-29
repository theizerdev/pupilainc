<div>
    @section('title', 'Conexiones de Mensajería')

    @push('styles')
    <style>
        .messaging-hero { background: linear-gradient(135deg, #25D366 0%, #128C7E 100%); color:#fff; border-radius:.75rem; padding:1.4rem 1.6rem; }
        .messaging-hero h2 { color:#fff; margin:0; }
        .messaging-hero p { opacity:.9; margin:0; }

        .stat-card { border:1px solid rgba(0,0,0,.06); border-radius:.65rem; padding:.9rem 1rem;
                     transition:all .2s; display:flex; align-items:center; gap:.85rem; height:100%; background:#fff; }
        .stat-card:hover { box-shadow:0 6px 18px rgba(0,0,0,.07); transform:translateY(-1px); }
        .stat-card .stat-icon { width:44px; height:44px; border-radius:11px; flex:0 0 44px;
                                display:flex; align-items:center; justify-content:center; font-size:1.15rem; }
        .stat-card .stat-value { font-size:1.35rem; font-weight:600; line-height:1; }
        .stat-card .stat-label { font-size:.72rem; color:var(--bs-secondary-color);
                                 text-transform:uppercase; letter-spacing:.4px; font-weight:600; }

        .status-badge { padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
        .status-active { background: #dcfce7; color: #16a34a; }
        .status-inactive { background: #f3f4f6; color: #6b7280; }
        .status-testing { background: #fef3c7; color: #d97706; }
        .status-error { background: #fee2e2; color: #dc2626; }
    </style>
    @endpush

    {{-- Hero Section --}}
    <div class="messaging-hero mb-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <h2 class="fw-semibold"><i class="ri ri-message-2-line me-2"></i>Conexiones de Mensajería</h2>
            <p class="mt-1">Gestione los proveedores de mensajería para enviar notificaciones</p>
        </div>
        <button wire:click="openCreateModal" class="btn btn-light btn-sm">
            <i class="ri ri-add-line me-1"></i>Nueva Conexión
        </button>
    </div>

    {{-- Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#dbeafe;color:#2563eb;"><i class="ri ri-link"></i></div>
                <div>
                    <div class="stat-label">Total conexiones</div>
                    <div class="stat-value">{{ count($connections) }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#dcfce7;color:#16a34a;"><i class="ri ri-check-line"></i></div>
                <div>
                    <div class="stat-label">Activas</div>
                    <div class="stat-value">{{ collect($connections)->where('status', 'active')->count() }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#fef3c7;color:#d97706;"><i class="ri ri-time-line"></i></div>
                <div>
                    <div class="stat-label">Inactivas</div>
                    <div class="stat-value">{{ collect($connections)->where('status', 'inactive')->count() }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#fee2e2;color:#dc2626;"><i class="ri ri-error-warning-line"></i></div>
                <div>
                    <div class="stat-label">Con errores</div>
                    <div class="stat-value">{{ collect($connections)->where('status', 'error')->count() }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-6">
                    <label class="form-label small fw-semibold"><i class="ri ri-search-line me-1"></i>Buscar</label>
                    <input type="text" class="form-control form-control-sm" wire:model.debounce.300ms="search" placeholder="Nombre de conexión...">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Estado</label>
                    <select class="form-select form-select-sm" wire:model="statusFilter">
                        <option value="">Todos</option>
                        <option value="active">Activas</option>
                        <option value="inactive">Inactivas</option>
                        <option value="testing">Prueba</option>
                        <option value="error">Error</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-sm btn-label-secondary w-100" wire:click="clearFilters" wire:loading.attr="disabled">
                        <i class="ri ri-refresh-line me-1"></i>Limpiar
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Connections List --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent border-0 pb-0">
            <h6 class="mb-0"><i class="ri ri-links-line me-2 text-primary"></i>Conexiones configuradas</h6>
        </div>
        <div class="card-body">
            @if(count($connections) > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th>Nombre</th>
                                <th>Proveedor</th>
                                <th>Estado</th>
                                <th>Última prueba</th>
                                <th>Módulos</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($connections as $connection)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm me-2" style="background:#e8f5e9;">
                                                <i class="ri ri-link text-success"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0">{{ $connection['name'] }}</h6>
                                                <small class="text-muted">ID: {{ $connection['id'] }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark">
                                            {{ $connection['provider']['name'] ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="status-badge status-{{ $connection['status'] }}">
                                            @switch($connection['status'])
                                                @case('active') Activa @break
                                                @case('inactive') Inactiva @break
                                                @case('testing') Prueba @break
                                                @case('error') Error @break
                                                @default {{ $connection['status'] }}
                                            @endswitch
                                        </span>
                                    </td>
                                    <td>
                                        @if($connection['last_test_at'])
                                            {{ \Carbon\Carbon::parse($connection['last_test_at'])->format('d/m/Y H:i') }}
                                        @else
                                            <span class="text-muted">Nunca</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($connection['is_default_for'])
                                            @foreach($connection['is_default_for'] as $module)
                                                <span class="badge bg-info me-1">{{ $module }}</span>
                                            @endforeach
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                                <i class="ri ri-more-2-line"></i>
                                            </button>
                                            <div class="dropdown-menu">
                                                <button class="dropdown-item" wire:click="openEditModal({{ $connection['id'] }})">
                                                    <i class="ri ri-pencil-line me-1"></i> Editar
                                                </button>
                                                <button class="dropdown-item" wire:click="testConnection({{ $connection['id'] }})" wire:loading.attr="disabled">
                                                    <i class="ri ri-flashlight-line me-1"></i> Probar
                                                </button>
                                                <button class="dropdown-item text-danger" wire:click="deleteConnection({{ $connection['id'] }})" wire:loading.attr="disabled" wire:confirm="¿Eliminar esta conexión?">
                                                    <i class="ri ri-delete-bin-line me-1"></i> Eliminar
                                                </button>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5 text-muted">
                    <i class="ri ri-link-unlink" style="font-size:2rem;opacity:.3;"></i>
                    <p class="mb-0 mt-2">No hay conexiones configuradas</p>
                    <button class="btn btn-sm btn-primary mt-3" wire:click="openCreateModal">
                        <i class="ri ri-add-line me-1"></i>Crear primera conexión
                    </button>
                </div>
            @endif
        </div>
    </div>

    {{-- Modal --}}
    @if($showModal)
    {{-- Twilio SMS fields --}}
    @php
        $selectedProvider = collect($providers)->firstWhere('id', $provider_id);
        $selectedSlug = $selectedProvider['slug'] ?? null;
    @endphp
        <div class="modal fade show" style="display:block;" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="ri ri-link me-2"></i>
                            {{ $editingConnection ? 'Editar Conexión' : 'Nueva Conexión' }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Proveedor *</label>
                                <select class="form-select" wire:model="provider_id">
                                    <option value="">Seleccionar proveedor</option>
                                    @foreach($providers as $provider)
                                        <option value="{{ $provider['id'] }}">{{ $provider['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nombre *</label>
                                <input type="text" class="form-control" wire:model="name" placeholder="WhatsApp Principal">
                            </div>
                             @if($selectedSlug != 'twilio')
                            <div class="col-md-8">
                                <label class="form-label">URL del API *</label>
                                <input type="url" class="form-control" wire:model="api_url" placeholder="http://82.165.213.124:8092">
                            </div>
                            @endif
                             @if($selectedSlug != 'twilio')        
                            <div class="col-md-4">
                                <label class="form-label">Timeout (seg)</label>
                                <input type="number" class="form-control" wire:model="timeout" min="5" max="300">
                            </div>

                            <div class="col-12">
                                <label class="form-label">API Key *</label>
                                <input type="password" class="form-control" wire:model="api_key" placeholder="Ingrese la API Key">
                            </div>
                            @endif

                            

                            @if($selectedSlug === 'twilio')
                                <div class="col-md-4 col-12 mt-3">
                                    <label class="form-label">Canal *</label>
                                    <select class="form-select" wire:model="channel">
                                        <option value="sms">SMS</option>
                                        <option value="whatsapp">WhatsApp</option>
                                    </select>
                                </div>
                                <div class="col-md-8 col-12 mt-3">
                                    <label class="form-label">Account SID *</label>
                                    <input type="text" class="form-control" wire:model="account_sid" placeholder="AC...">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Auth Token *</label>
                                    <input type="password" class="form-control" wire:model="auth_token" placeholder="Auth Token">
                                </div>
                                <div class="col-md-6 col-12">
                                    <label class="form-label">From para SMS (Twilio verificado) *</label>
                                    <input type="text" class="form-control" wire:model="from" placeholder="+1415...">
                                </div>
                                <div class="col-md-6 col-12">
                                    <label class="form-label">From para WhatsApp</label>
                                    <input type="text" class="form-control" wire:model="from_whatsapp" placeholder="+1415...">
                                </div>
                            @endif

                            <div class="col-12">
                                <label class="form-label">Módulos por defecto</label>
                                <div class="d-flex flex-wrap gap-2">
                                    @php $modules = ['citas', 'usuarios', 'consultas', 'doctores', 'enfermeros', 'pedidos']; @endphp
                                    @foreach($modules as $module)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="{{ $module }}" id="mod_{{ $module }}" wire:model="is_default_for">
                                            <label class="form-check-label" for="mod_{{ $module }}">{{ ucfirst($module) }}</label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @if($testResult)
                            <div class="alert mt-3 {{ $testResult['success'] ? 'alert-success' : 'alert-danger' }}">
                                {{ $testResult['message'] }}
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModal">Cancelar</button>
                        <button type="button" class="btn btn-primary" wire:click="saveConnection" wire:loading.attr="disabled">
                            <i class="ri ri-save-line me-1"></i>Guardar
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    @endif
</div>