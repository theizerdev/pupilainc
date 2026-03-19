<div wire:ignore.self>
    

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Page Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="h3 mb-0 text-gray-800">Configuración de Tasas de Cambio</h1>
                        <p class="text-muted">Administra las tasas de cambio por país</p>
                    </div>
                    <div>
                        @can('edit exchange-rates')
                            <button wire:click="create" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Nueva Configuración
                            </button>
                        @endcan
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Total Configuraciones
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $configs->count() }}</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-cog fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            Activas
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $configs->where('activo', true)->count() }}</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-danger shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                            Inactivas
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $configs->where('activo', false)->count() }}</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-pause-circle fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-info shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                            Con Tasa Dinámica
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $configs->where('requiere_tasa_cambio', true)->count() }}</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-sync-alt fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Table Card -->
                <div class="card shadow">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>País</th>
                                        <th>Moneda Base</th>
                                        <th>Moneda Local</th>
                                        <th>Tipo de Tasa</th>
                                        <th>Tasa Fija</th>
                                        <th>API</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($configs as $config)
                                        <tr>
                                            <td>
                                                <strong>{{ $config->pais->nombre ?? 'N/A' }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $config->pais->codigo_iso2 ?? '' }}</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary">{{ $config->moneda_base }}</span>
                                            </td>
                                            <td>
                                                @if($config->moneda_local)
                                                    <span class="badge bg-secondary">{{ $config->moneda_local }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($config->requiere_tasa_cambio)
                                                    @if($config->usar_api_bcv)
                                                        <span class="badge bg-warning text-dark">
                                                            <i class="fas fa-university me-1"></i>Dinámica (BCV)
                                                        </span>
                                                    @elseif($config->api_url)
                                                        <span class="badge bg-info">
                                                            <i class="fas fa-sync-alt me-1"></i>Dinámica (API)
                                                        </span>
                                                    @else
                                                        <span class="badge bg-success">
                                                            <i class="fas fa-lock me-1"></i>Fija Manual
                                                        </span>
                                                    @endif
                                                @else
                                                    <span class="badge bg-light text-dark">
                                                        <i class="fas fa-minus me-1"></i>Sin Conversión
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($config->tasa_fija)
                                                    <strong>{{ number_format($config->tasa_fija, 4) }}</strong>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($config->api_url)
                                                    <small class="text-muted">
                                                        <i class="fas fa-link me-1"></i>
                                                        {{ parse_url($config->api_url, PHP_URL_HOST) }}
                                                    </small>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" 
                                                           type="checkbox" 
                                                           role="switch"
                                                           {{ $config->activo ? 'checked' : '' }}
                                                           wire:click="toggleActivo({{ $config->id }})">
                                                    <label class="form-check-label">
                                                        {{ $config->activo ? 'Activo' : 'Inactivo' }}
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <button wire:click="edit({{ $config->id }})" 
                                                        class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center text-muted py-4">
                                                <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                                No hay configuraciones registradas
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Info Box -->
                <div class="alert alert-info mt-4 mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Tipos de Configuración:</strong>
                    <ul class="mb-0 mt-2">
                        <li><strong>Dinámica (BCV):</strong> Se actualiza automáticamente desde el BCV (Venezuela)</li>
                        <li><strong>Dinámica (API):</strong> Se actualiza desde una API internacional</li>
                        <li><strong>Fija Manual:</strong> Valor fijo que debes establecer manualmente</li>
                        <li><strong>Sin Conversión:</strong> El país usa directamente la moneda base (USD/EUR)</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Modal Edit/Create -->
        <div wire:ignore.self>
            @if($showEditModal)
            <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title">
                                <i class="fas fa-cog me-2"></i>
                                {{ $configId ? 'Editar Configuración' : 'Nueva Configuración' }}
                            </h5>
                            <button type="button" class="btn-close btn-close-white" wire:click="closeModal"></button>
                        </div>
                        <div class="modal-body">
                            <form wire:submit.prevent="save">
                                <div class="row">
                                    <!-- País -->
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="pais_id" class="form-label fw-bold">
                                                <i class="fas fa-flag me-1"></i>País *
                                            </label>
                                            <select class="form-select @error('pais_id') is-invalid @enderror" 
                                                    id="pais_id" 
                                                    wire:model.live="pais_id"
                                                    {{ $configId ? 'disabled' : '' }}>
                                                <option value="">Seleccionar país...</option>
                                                @foreach($paises as $pais)
                                                    <option value="{{ $pais->id }}">
                                                        {{ $pais->nombre }} ({{ $pais->codigo_iso2 }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('pais_id')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                            @if($configId)
                                                <small class="form-text text-muted">El país no se puede cambiar después de crear</small>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Moneda Base -->
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="moneda_base" class="form-label fw-bold">
                                                <i class="fas fa-dollar-sign me-1"></i>Moneda Base *
                                            </label>
                                            <select class="form-select @error('moneda_base') is-invalid @enderror" 
                                                    id="moneda_base" 
                                                    wire:model="moneda_base">
                                                <option value="USD">USD - Dólar Estadounidense</option>
                                                <option value="EUR">EUR - Euro</option>
                                            </select>
                                            @error('moneda_base')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- Moneda Local -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="moneda_local" class="form-label fw-bold">
                                                <i class="fas fa-coins me-1"></i>Moneda Local
                                            </label>
                                            <input type="text" 
                                                   class="form-control @error('moneda_local') is-invalid @enderror" 
                                                   id="moneda_local" 
                                                   wire:model="moneda_local"
                                                   placeholder="Ej: VES, COP, ARS">
                                            @error('moneda_local')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                            <small class="form-text text-muted">Moneda del país (opcional)</small>
                                        </div>
                                    </div>

                                    <!-- Requiere Tasa de Cambio -->
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="form-label fw-bold">
                                                <i class="fas fa-exchange-alt me-1"></i>¿Requiere Tasa de Cambio?
                                            </label>
                                            <div class="form-check form-switch mt-2">
                                                <input class="form-check-input" 
                                                       type="checkbox" 
                                                       role="switch"
                                                       id="requiere_tasa_cambio"
                                                       wire:model.live="requiere_tasa_cambio">
                                                <label class="form-check-label" for="requiere_tasa_cambio">
                                                    {{ $requiere_tasa_cambio ? 'Sí' : 'No' }}
                                                </label>
                                            </div>
                                            @error('requiere_tasa_cambio')
                                                <span class="invalid-feedback d-block">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                @if($requiere_tasa_cambio)
                                <hr class="my-4">
                                
                                <h6 class="mb-3"><i class="fas fa-cogs me-2"></i>Configuración de Tasa de Cambio</h6>
                                
                                <!-- Usar API BCV -->
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" 
                                                   type="checkbox" 
                                                   role="switch"
                                                   id="usar_api_bcv"
                                                   wire:model.live="usar_api_bcv">
                                            <label class="form-check-label fw-bold" for="usar_api_bcv">
                                                <i class="fas fa-university me-1"></i>Usar API del BCV (Venezuela)
                                            </label>
                                        </div>
                                        <small class="text-muted">Si está activado, se obtendrá automáticamente del BCV</small>
                                    </div>
                                </div>

                                @if(!$usar_api_bcv)
                                <!-- Tasa Fija -->
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="tasa_fija" class="form-label fw-bold">
                                                <i class="fas fa-lock me-1"></i>Tasa Fija
                                            </label>
                                            <input type="number" 
                                                   step="0.0001"
                                                   class="form-control @error('tasa_fija') is-invalid @enderror" 
                                                   id="tasa_fija" 
                                                   wire:model="tasa_fija"
                                                   placeholder="Ej: 36.50">
                                            @error('tasa_fija')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                            <small class="form-text text-muted">Dejar vacío si usa API</small>
                                        </div>
                                    </div>

                                    <!-- Frecuencia de Actualización -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="frecuencia_actualizacion_minutos" class="form-label fw-bold">
                                                <i class="fas fa-clock me-1"></i>Frecuencia de Actualización
                                            </label>
                                            <select class="form-select @error('frecuencia_actualizacion_minutos') is-invalid @enderror" 
                                                    id="frecuencia_actualizacion_minutos" 
                                                    wire:model="frecuencia_actualizacion_minutos">
                                                <option value="30">Cada 30 minutos</option>
                                                <option value="60">Cada 1 hora</option>
                                                <option value="180">Cada 3 horas</option>
                                                <option value="360">Cada 6 horas</option>
                                                <option value="1440">Una vez al día</option>
                                            </select>
                                            @error('frecuencia_actualizacion_minutos')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                @endif

                                <!-- API URL -->
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="api_url" class="form-label fw-bold">
                                                <i class="fas fa-link me-1"></i>URL de la API
                                            </label>
                                            <input type="url" 
                                                   class="form-control @error('api_url') is-invalid @enderror" 
                                                   id="api_url" 
                                                   wire:model="api_url"
                                                   placeholder="https://api.ejemplo.com/v1/latest"
                                                   {{ $usar_api_bcv ? 'readonly' : '' }}>
                                            @error('api_url')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                            @if($usar_api_bcv)
                                                <small class="form-text text-muted">URL automática del BCV configurada</small>
                                            @else
                                                <small class="form-text text-muted">URL de la API para obtener tasas (ej: ExchangeRate-API)</small>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- API Key -->
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="api_key" class="form-label fw-bold">
                                                <i class="fas fa-key me-1"></i>API Key
                                            </label>
                                            <input type="text" 
                                                   class="form-control @error('api_key') is-invalid @enderror" 
                                                   id="api_key" 
                                                   wire:model="api_key"
                                                   placeholder="Tu clave de API (si es requerida)">
                                            @error('api_key')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                            <small class="form-text text-muted">Clave de autenticación para la API (opcional)</small>
                                        </div>
                                    </div>
                                </div>
                                @else
                                <!-- No requiere tasa de cambio -->
                                <div class="alert alert-info mt-3">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Sin Conversión:</strong> Este país usará directamente la moneda base ({{ $moneda_base }}) sin conversiones.
                                </div>
                                @endif

                                <!-- Activo -->
                                <div class="row mt-4">
                                    <div class="col-md-12">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" 
                                                   type="checkbox" 
                                                   role="switch"
                                                   id="activo"
                                                   wire:model="activo">
                                            <label class="form-check-label fw-bold" for="activo">
                                                <i class="fas fa-power-off me-1"></i>Configuración Activa
                                            </label>
                                        </div>
                                        <small class="text-muted">Desactivar temporalmente sin eliminar la configuración</small>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="closeModal">Cancelar</button>
                            <button type="button" class="btn btn-primary" wire:click="save">
                                <i class="fas fa-save me-2"></i>Guardar Configuración
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    
</div>