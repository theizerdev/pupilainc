<div>
    <div>
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-0">
                    <i class="icon-base text-primary ri ri-stethoscope-line text-primary me-2"></i>
                    Editar Subespecialidad Médica
                </h4>
                <p class="text-muted mb-0">Complete el formulario para registrar una nueva subespecialidad</p>
            </div>
            <div>
                <a href="{{ route('admin.subespecialidades.index') }}" class="btn btn-label-secondary">
                    <i class="icon-base text-primary ri ri-arrow-left-line me-1"></i> Volver
                </a>
            </div>
        </div>

        <!-- Form -->
        <div class="row">
            <div class="col-lg-8">
                <!-- Información Básica -->
                <div class="card mb-4">
                    <div class="card-header d-flex align-items-center">
                        <i class="icon-base text-primary ri ri-file-text-line me-2"></i>
                        <h5 class="mb-0">Información Básica</h5>
                    </div>
                    <div class="card-body">
                        <form wire:submit.prevent="update" id="subespecialidadForm">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label" for="nombre">
                                            <i class="icon-base text-primary ri ri-edit-line me-1"></i>
                                            Nombre de la Subespecialidad
                                            <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" 
                                               class="form-control @error('nombre') is-invalid @enderror" 
                                               id="nombre" 
                                               wire:model.live="nombre" 
                                               placeholder="Ej: Cirugía Refractiva, Retina, Córnea"
                                               autocomplete="off">
                                        @error('nombre')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">Nombre descriptivo de la subespecialidad</div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label" for="codigo">
                                            <i class="icon-base text-primary ri ri-barcode-line me-1"></i>
                                            Código
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="icon-base text-primary ri ri-qr-code-line"></i>
                                            </span>
                                            <input type="text" 
                                                   class="form-control @error('codigo') is-invalid @enderror" 
                                                   id="codigo" 
                                                   wire:model.blur="codigo" 
                                                   placeholder="SUB-XXXX"
                                                   maxlength="10"
                                                   style="text-transform: uppercase;">
                                        </div>
                                        @error('codigo')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">Deje vacío para generación automática</div>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="form-label" for="descripcion">
                                            <i class="icon-base text-primary ri ri-article-line me-1"></i>
                                            Descripción
                                        </label>
                                        <textarea class="form-control @error('descripcion') is-invalid @enderror" 
                                                  id="descripcion" 
                                                  wire:model.blur="descripcion" 
                                                  rows="3"
                                                  placeholder="Descripción detallada de la subespecialidad y sus servicios..."></textarea>
                                        @error('descripcion')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                        <div class="d-flex justify-content-between">
                                            <div class="form-text">Describa los servicios principales</div>
                                            <small class="text-muted" wire:loading.remove wire:target="descripcion">
                                                {{ strlen($descripcion ?? '') }}/1000 caracteres
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Configuración Principal -->
                <div class="card mb-4">
                    <div class="card-header d-flex align-items-center">
                        <i class="icon-base text-primary ri ri-settings-4-line me-2"></i>
                        <h5 class="mb-0">Configuración Principal</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="form-label" for="especialidad_id">
                                        <i class="icon-base text-primary ri ri-stack-line me-1"></i>
                                        Especialidad
                                        <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('especialidad_id') is-invalid @enderror" 
                                            id="especialidad_id" 
                                            wire:model.live="especialidad_id">
                                        <option value="">Seleccione una especialidad</option>
                                        @foreach($especialidades as $especialidad)
                                            <option value="{{ $especialidad->id }}">
                                                {{ $especialidad->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('especialidad_id')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Especialidad médica principal</div>
                                </div>
                            </div>

                            @if(auth()->user()->hasRole('Super Administrador'))
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="empresa_id">
                                        <i class="icon-base text-primary ri ri-building-2-line me-1"></i>
                                        Empresa
                                        <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('empresa_id') is-invalid @enderror" 
                                            id="empresa_id" 
                                            wire:model.live="empresa_id">
                                        <option value="">Seleccione una empresa</option>
                                        @foreach($empresas as $empresa)
                                            <option value="{{ $empresa->id }}">{{ $empresa->razon_social }}</option>
                                        @endforeach
                                    </select>
                                    @error('empresa_id')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            @endif
                        </div>

                        @if(auth()->user()->hasRole('Super Administrador'))
                        <div class="row g-3 mt-1">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="sucursal_id">
                                        <i class="icon-base text-primary ri ri-map-pin-line me-1"></i>
                                        Sucursal
                                        <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('sucursal_id') is-invalid @enderror" 
                                            id="sucursal_id" 
                                            wire:model.live="sucursal_id" 
                                            @if(!$empresa_id) disabled @endif>
                                        <option value="">Seleccione una sucursal</option>
                                        @foreach($sucursales as $sucursal)
                                            <option value="{{ $sucursal->id }}">{{ $sucursal->nombre }}</option>
                                        @endforeach
                                    </select>
                                    @error('sucursal_id')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        @else
                        <!-- Información de empresa y sucursal para usuarios normales -->
                        <div class="alert alert-info d-flex align-items-start mt-3" role="alert">
                            <i class="icon-base text-primary ri ri-information-line me-2 fs-5"></i>
                            <div>
                                <strong>Esta subespecialidad se registrará automáticamente para:</strong>
                                <ul class="mb-0 mt-2 ps-3">
                                    <li><strong>Empresa:</strong> {{ auth()->user()->empresa->razon_social ?? 'No definida' }}</li>
                                    <li><strong>Sucursal:</strong> {{ auth()->user()->sucursal->nombre ?? 'No definida' }}</li>
                                </ul>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Costos y Duración -->
                <div class="card mb-4">
                    <div class="card-header d-flex align-items-center">
                        <i class="icon-base text-primary ri ri-money-dollar-circle-line me-2"></i>
                        <h5 class="mb-0">Costos y Duración</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="costo_consulta">
                                        <i class="icon-base text-primary ri ri-price-tag-3-line me-1"></i>
                                        Costo de Consulta
                                        <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group input-group-merge">
                                        <span class="input-group-text">
                                            <i class="icon-base text-primary ri ri-money-dollar-circle-line"></i>
                                        </span>
                                        <input type="number" 
                                               step="0.01" 
                                               min="0"
                                               class="form-control @error('costo_consulta') is-invalid @enderror" 
                                               id="costo_consulta" 
                                               wire:model.blur="costo_consulta"
                                               placeholder="0.00">
                                    </div>
                                    @error('costo_consulta')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Costo base de la consulta</div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="duracion_consulta">
                                        <i class="icon-base text-primary ri ri-time-line me-1"></i>
                                        Duración de Consulta
                                        <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group input-group-merge">
                                        <span class="input-group-text">
                                            <i class="icon-base text-primary ri ri-timer-flash-line"></i>
                                        </span>
                                        <input type="number" 
                                               class="form-control @error('duracion_consulta') is-invalid @enderror" 
                                               id="duracion_consulta" 
                                               wire:model.blur="duracion_consulta" 
                                               min="15" 
                                               max="240"
                                               placeholder="30">
                                        <span class="input-group-text">minutos</span>
                                    </div>
                                    @error('duracion_consulta')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Duración estándar (15-240 min)</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Apariencia y Opciones -->
                <div class="card mb-4">
                    <div class="card-header d-flex align-items-center">
                        <i class="icon-base text-primary ri ri-palette-line me-2"></i>
                        <h5 class="mb-0">Apariencia y Opciones</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="form-label" for="color">
                                        <i class="icon-base text-primary ri ri-color-filter-line me-1"></i>
                                        Color de Identificación
                                        <span class="text-danger">*</span>
                                    </label>
                                    <div class="d-flex gap-2 align-items-center">
                                        <input type="color" 
                                               class="form-control form-control-color w-px-100 @error('color') is-invalid @enderror" 
                                               id="color" 
                                               wire:model.live="color"
                                               title="Elige un color">
                                        <div class="flex-grow-1">
                                            <div class="input-group">
                                                <span class="input-group-text">#</span>
                                                <input type="text" 
                                                       class="form-control font-monospace" 
                                                       wire:model.blur="color" 
                                                       pattern="^#[0-9A-Fa-f]{6}$"
                                                       placeholder="3B82F6"
                                                       style="text-transform: uppercase;">
                                            </div>
                                        </div>
                                    </div>
                                    @error('color')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                    
                                    <!-- Paleta de colores predefinidos -->
                                    <div class="mt-2">
                                        <div class="form-text d-block mb-2">Colores sugeridos:</div>
                                        <div class="d-flex gap-2 flex-wrap">
                                            @php
                                                $coloresSugeridos = [
                                                    '#3B82F6' => 'Azul',
                                                    '#EF4444' => 'Rojo',
                                                    '#10B981' => 'Verde',
                                                    '#F59E0B' => 'Ámbar',
                                                    '#8B5CF6' => 'Violeta',
                                                    '#EC4899' => 'Rosa',
                                                    '#06B6D4' => 'Cian',
                                                    '#F97316' => 'Naranja'
                                                ];
                                            @endphp
                                            @foreach($coloresSugeridos as $colorHex => $colorName)
                                                <button type="button" 
                                                        class="btn border-0 p-0 rounded-circle shadow-sm" 
                                                        style="width: 32px; height: 32px; background-color: {{ $colorHex }};"
                                                        wire:click="$set('color', '{{ $colorHex }}')"
                                                        title="{{ $colorName }}"
                                                        @if($color === $colorHex) style="outline: 3px solid #000; outline-offset: 2px;" @endif>
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botones de Acción -->
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ route('admin.subespecialidades.index') }}" class="btn btn-label-secondary">
                                <i class="icon-base text-primary ri ri-close-line me-1"></i> Cancelar
                            </a>
                            <div class="d-flex gap-2">
                                <button type="button" 
                                        class="btn btn-outline-secondary" 
                                        onclick="document.getElementById('subespecialidadForm').reset()">
                                    <i class="icon-base text-primary ri ri-refresh-line me-1"></i> Limpiar
                                </button>
                                <button type="submit" 
                                        form="subespecialidadForm"
                                        class="btn btn-primary"
                                        wire:loading.attr="disabled"
                                        wire:target="store">
                                    <span wire:loading.remove wire:target="store">
                                        <i class="icon-base text-primary ri ri-save-3-line me-1"></i> Guardar Subespecialidad
                                    </span>
                                    <span wire:loading wire:target="store">
                                        <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                                        Guardando...
                                    </span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Columna Lateral - Vista Previa e Información -->
            <div class="col-lg-4">
                <!-- Vista Previa en Tiempo Real -->
                <div class="card mb-4">
                    <div class="card-header d-flex align-items-center">
                        <i class="icon-base text-primary ri ri-eye-line me-2"></i>
                        <h5 class="mb-0">Vista Previa</h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <!-- Badge con código y color -->
                            <div class="badge rounded-pill p-3 mb-3" 
                                 style="background-color: {{ $color }}; color: white; font-size: 1.1em;">
                                <i class="icon-base text-primary ri ri-award-line me-1"></i>
                                {{ $codigo ?: 'SUB-001' }}
                            </div>
                            
                            <!-- Icono representativo -->
                            <div class="avatar avatar-xl bg-soft-primary mb-3">
                                <span class="avatar-initial rounded-circle" style="color: {{ $color }};">
                                    <i class="icon-base text-primary ri ri-stethoscope-line fs-1"></i>
                                </span>
                            </div>

                            <h5 class="mb-1">{{ $nombre ?: 'Nombre de Subespecialidad' }}</h5>
                            <p class="text-muted small">{{ Str::limit($descripcion ?: 'Descripción de la subespecialidad', 80) }}</p>
                        </div>
                        
                        <hr class="my-3">
                        
                        <!-- Información detallada -->
                        <div class="d-flex align-items-center mb-2">
                            <div class="avatar avatar-xs bg-soft-primary me-2">
                                <span class="avatar-initial rounded-circle">
                                    <i class="icon-base text-primary ri ri-money-dollar-circle-line"></i>
                                </span>
                            </div>
                            <div class="flex-grow-1">
                                <small class="text-muted d-block">Costo de Consulta</small>
                                <strong>${{ number_format($costo_consulta, 2) }}</strong>
                            </div>
                        </div>
                        
                        <div class="d-flex align-items-center mb-2">
                            <div class="avatar avatar-xs bg-soft-success me-2">
                                <span class="avatar-initial rounded-circle">
                                    <i class="icon-base text-success ri ri-timer-flash-line"></i>
                                </span>
                            </div>
                            <div class="flex-grow-1">
                                <small class="text-muted d-block">Duración</small>
                                <strong>{{ $duracion_consulta }} minutos</strong>
                            </div>
                        </div>
                        
                        <div class="d-flex align-items-center mb-2">
                            <div class="avatar avatar-xs bg-soft-info me-2">
                                <span class="avatar-initial rounded-circle">
                                    <i class="icon-base text-primary ri ri-stack-line"></i>
                                </span>
                            </div>
                            <div class="flex-grow-1">
                                <small class="text-muted d-block">Especialidad</small>
                                <strong>
                                    @foreach($especialidades as $especialidad)
                                        @if($especialidad->id == $especialidad_id)
                                            {{ $especialidad->nombre }}
                                            @break
                                        @endif
                                    @endforeach
                                </strong>
                            </div>
                        </div>
                        
                        <div class="d-flex align-items-center mb-2">
                            <div class="avatar avatar-xs bg-soft-warning me-2">
                                <span class="avatar-initial rounded-circle">
                                    <i class="icon-base text-primary ri ri-calendar-check-line"></i>
                                </span>
                            </div>
                            <div class="flex-grow-1">
                                <small class="text-muted d-block">Cita Previa</small>
                                <strong>
                                    <span class="badge badge-{{ $requiere_cita_previa ? 'bg-warning' : 'bg-label-secondary' }} text-{{ $requiere_cita_previa ? 'warning' : 'secondary' }}">
                                        {{ $requiere_cita_previa ? 'Requerida' : 'Opcional' }}
                                    </span>
                                </strong>
                            </div>
                        </div>
                        
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-xs bg-{{ $status ? 'soft-success' : 'soft-secondary' }} me-2">
                                <span class="avatar-initial rounded-circle">
                                    <i class="icon-base text-primary ri ri-{{ $status ? 'checkbox-circle' : 'close-circle' }}-line"></i>
                                </span>
                            </div>
                            <div class="flex-grow-1">
                                <small class="text-muted d-block">Estado</small>
                                <strong>
                                    <span class="badge badge-{{ $status ? 'bg-success' : 'bg-label-secondary' }} text-{{ $status ? 'success' : 'secondary' }}">
                                        {{ $status ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </strong>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tarjeta de Ubicación -->
                @if($empresa_id && $sucursal_id)
                <div class="card mb-4">
                    <div class="card-header d-flex align-items-center">
                        <i class="icon-base text-primary ri ri-map-pin-2-line me-2"></i>
                        <h5 class="mb-0">Ubicación</h5>
                    </div>
                    <div class="card-body">
                        @foreach($empresas as $empresa)
                            @if($empresa->id == $empresa_id)
                                <div class="d-flex align-items-start mb-3">
                                    <div class="avatar avatar-sm bg-soft-primary me-2">
                                        <span class="avatar-initial rounded-circle">
                                            <i class="icon-base text-primary ri ri-building-2-line"></i>
                                        </span>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block">Empresa</small>
                                        <strong>{{ $empresa->razon_social }}</strong>
                                    </div>
                                </div>
                                @break
                            @endif
                        @endforeach
                        
                        @foreach($sucursales as $sucursal)
                            @if($sucursal->id == $sucursal_id)
                                <div class="d-flex align-items-start">
                                    <div class="avatar avatar-sm bg-soft-success me-2">
                                        <span class="avatar-initial rounded-circle">
                                            <i class="icon-base text-primary ri ri-store-2-line"></i>
                                        </span>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block">Sucursal</small>
                                        <strong>{{ $sucursal->nombre }}</strong>
                                    </div>
                                </div>
                                @break
                            @endif
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Tips y Ayuda -->
                <div class="card">
                    <div class="card-header d-flex align-items-center">
                        <i class="icon-base text-primary ri ri-lightbulb-line me-2"></i>
                        <h5 class="mb-0">Recomendaciones</h5>
                    </div>
                    <div class="card-body">
                        <ul class="mb-0 ps-3">
                            <li class="mb-2">
                                <i class="icon-base text-primary ri ri-check-line text-success me-1"></i>
                                <span>Use un nombre claro y descriptivo</span>
                            </li>
                            <li class="mb-2">
                                <i class="icon-base text-primary ri ri-check-line text-success me-1"></i>
                                <span>El color ayuda a identificar rápidamente la subespecialidad</span>
                            </li>
                            <li class="mb-2">
                                <i class="icon-base text-primary ri ri-check-line text-success me-1"></i>
                                <span>Ajuste la duración según el tipo de consulta</span>
                            </li>
                            <li>
                                <i class="icon-base text-primary ri ri-check-line text-success me-1"></i>
                                <span>Active "cita previa" si requiere preparación especial</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
