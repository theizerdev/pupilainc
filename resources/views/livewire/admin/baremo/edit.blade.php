<div>
<div class="py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-edit me-2"></i>Editar Servicio</h1>
                <p class="text-muted">Modifique la información del servicio</p>
            </div>
            <div>
                <a href="{{ route('admin.baremos.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Volver al Listado
                </a>
            </div>
        </div>

        <!-- Form -->
        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-edit me-2"></i>Información del Servicio</h6>
                    </div>
                    <div class="card-body">
                        <form wire:submit.prevent="update">
                            <div class="row">
                                <div class="col-md-6 mt-2">
                                    <div class="form-group">
                                        <label for="categoria_id" class="fw-bold">
                                            <i class="fas fa-tag me-1"></i>Categoría *
                                        </label>
                                        <select class="form-control @error('categoria_id') is-invalid @enderror" 
                                                id="categoria_id" 
                                                wire:model.live="categoria_id">
                                            <option value="">Seleccione una categoría</option>
                                            @foreach($categorias as $categoria)
                                                <option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>
                                            @endforeach
                                        </select>
                                        @error('categoria_id')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6 mt-2">
                                    <div class="form-group">
                                        <label for="especialidad_id" class="fw-bold">
                                            <i class="fas fa-user-md me-1"></i>Especialidad *
                                        </label>
                                        <select class="form-control @error('especialidad_id') is-invalid @enderror" 
                                                id="especialidad_id" 
                                                wire:model.live="especialidad_id">
                                            <option value="">Seleccione una especialidad</option>
                                            @foreach($especialidades as $especialidad)
                                                <option value="{{ $especialidad->id }}">{{ $especialidad->nombre }}</option>
                                            @endforeach
                                        </select>
                                        @error('especialidad_id')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mt-2">
                                    <div class="form-group">
                                        <label for="codigo" class="fw-bold">
                                            <i class="fas fa-barcode me-1"></i>Código *
                                        </label>
                                        <div class="input-group">
                                            <input type="text" 
                                                   class="form-control @error('codigo') is-invalid @enderror" 
                                                   id="codigo" 
                                                   wire:model.live="codigo" 
                                                   placeholder="000000"
                                                   style="text-transform: uppercase; font-family: monospace; letter-spacing: 2px; font-weight: bold;"
                                                   readonly
                                                   autocomplete="off"
                                                   disabled>
                                           
                                        </div>
                                        @error('codigo')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                        <small class="form-text text-muted">Código único de 6 dígitos (auto-generado)</small>
                                    </div>
                                </div>

                                <div class="col-md-6 mt-2">
                                    <div class="form-group">
                                        <label for="nombre_servicio" class="fw-bold">
                                            <i class="fas fa-font me-1"></i>Nombre del Servicio *
                                        </label>
                                        <input type="text" 
                                               class="form-control @error('nombre_servicio') is-invalid @enderror" 
                                               id="nombre_servicio" 
                                               wire:model.live="nombre_servicio" 
                                               placeholder="Ej: Consulta General"
                                               autocomplete="off">
                                        @error('nombre_servicio')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            @if(auth()->user()->hasRole('Super Administrador'))
                            <hr class="my-4">
                            <h6 class="mb-3"><i class="fas fa-building me-2"></i>Asignación (Solo Super Admin)</h6>
                            <div class="row">
                                <div class="col-md-6 mt-2">
                                    <div class="form-group">
                                        <label for="empresa_id" class="fw-bold">Empresa *</label>
                                        <select class="form-control @error('empresa_id') is-invalid @enderror" 
                                                id="empresa_id" 
                                                wire:model.live="empresa_id">
                                            <option value="">Seleccione una empresa</option>
                                            @foreach($empresas as $empresa)
                                                <option value="{{ $empresa->id }}">{{ $empresa->razon_social }}</option>
                                            @endforeach
                                        </select>
                                        @error('empresa_id')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6 mt-2">
                                    <div class="form-group">
                                        <label for="sucursal_id" class="fw-bold">Sucursal *</label>
                                        <select class="form-control @error('sucursal_id') is-invalid @enderror" 
                                                id="sucursal_id" 
                                                wire:model.live="sucursal_id" 
                                                @if(!$empresa_id) disabled @endif>
                                            <option value="">Seleccione una sucursal</option>
                                            @foreach($sucursales as $sucursal)
                                                <option value="{{ $sucursal->id }}">{{ $sucursal->nombre }}</option>
                                            @endforeach
                                        </select>
                                        @error('sucursal_id')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            @endif

                            <hr class="my-4">
                            <h6 class="mb-3"><i class="fas fa-dollar-sign me-2"></i>Costos y Precios</h6>
                            <div class="row">
                                <div class="col-md-6 mt-2">
                                    <div class="form-group">
                                        <label for="costo_usd" class="fw-bold">Costo en USD *</label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" 
                                                   step="0.01" 
                                                   min="0.01"
                                                   class="form-control @error('costo_usd') is-invalid @enderror" 
                                                   id="costo_usd" 
                                                   wire:model.live="costo_usd" 
                                                   placeholder="0.00">
                                        </div>
                                        @error('costo_usd')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                        <small class="form-text text-muted">
                                            Tasa de cambio actual: <strong>Bs. {{ number_format($tasa_usd, 2) }}</strong>
                                        </small>
                                    </div>
                                </div>

                                <div class="col-md-6 mt-2">
                                    <div class="form-group">
                                        <label for="duracion_minutos" class="fw-bold">Duración Estimada (minutos)</label>
                                        <input type="number" 
                                               class="form-control @error('duracion_minutos') is-invalid @enderror" 
                                               id="duracion_minutos" 
                                               wire:model.live="duracion_minutos" 
                                               placeholder="30"
                                               min="5">
                                        @error('duracion_minutos')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                        <small class="form-text text-muted">Tiempo estimado para el servicio</small>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">
                            <h6 class="mb-3"><i class="fas fa-percent me-2"></i>Configuración de IVA</h6>
                            <div class="row">
                                <div class="col-md-6 mt-2">
                                    <div class="form-group p-3 border rounded">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" 
                                                   type="checkbox" 
                                                   id="aplica_iva" 
                                                   wire:model.live="aplica_iva">
                                            <label class="form-check-label fw-bold" for="aplica_iva">
                                                <i class="fas fa-check-circle me-1"></i>Aplica IVA
                                            </label>
                                        </div>
                                        <small class="text-muted d-block mt-2">
                                            <i class="fas fa-info-circle me-1"></i>
                                            {{ $aplica_iva ? 'Se calculará IVA sobre este servicio' : 'No se calculará IVA' }}
                                        </small>
                                    </div>
                                </div>

                                <div class="col-md-6 mt-2">
                                    <div class="form-group p-3 border rounded">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" 
                                                   type="checkbox" 
                                                   id="exento_iva" 
                                                   wire:model.live="exento_iva"
                                                   {{ !$aplica_iva ? 'disabled' : '' }}>
                                            <label class="form-check-label fw-bold" for="exento_iva">
                                                <i class="fas fa-exemption me-1"></i>Exento de IVA
                                            </label>
                                        </div>
                                        <small class="text-muted d-block mt-2">
                                            <i class="fas fa-info-circle me-1"></i>
                                            {{ $exento_iva ? 'Servicio exonerado de IVA' : 'Sujeto a retención de IVA' }}
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">
                            <h6 class="mb-3"><i class="fas fa-align-left me-2"></i>Descripción</h6>
                            <div class="form-group">
                                <label for="descripcion" class="fw-bold">Descripción del Servicio</label>
                                <textarea class="form-control @error('descripcion') is-invalid @enderror" 
                                          id="descripcion" 
                                          wire:model.live="descripcion" 
                                          rows="4" 
                                          placeholder="Describa las características, procedimientos incluidos, indicaciones y particularidades de este servicio..."></textarea>
                                @error('descripcion')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                                <small class="form-text text-muted">Descripción detallada del servicio (opcional pero recomendado)</small>
                            </div>

                            <hr class="my-4">
                            <h6 class="mb-3"><i class="fas fa-cog me-2"></i>Configuración</h6>
                            <div class="form-group p-3 border rounded">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           id="activo" 
                                           wire:model.live="activo">
                                    <label class="form-check-label fw-bold" for="activo">
                                        <i class="fas fa-power-off me-1"></i>Activo
                                    </label>
                                </div>
                                <small class="text-muted d-block mt-2">
                                    <i class="fas fa-info-circle me-1"></i>
                                    {{ $activo ? 'Visible y disponible para uso' : 'Oculto y no disponible' }}
                                </small>
                            </div>

                            <hr class="my-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <a href="{{ route('admin.baremos.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times"></i> Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save"></i> Actualizar Servicio
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Preview Panel -->
            <div class="col-lg-4">
                <div class="card shadow-sm border-0 sticky-top" style="top: 20px; z-index: 100; border-radius: 15px; overflow: hidden;">
                    <div class="card-header text-white py-3 bg-primary">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="m-0">
                                <i class="fas fa-eye me-2"></i>Vista Previa
                            </h6>
                            <span class="badge bg-white text-primary" style="font-size: 0.75rem;">
                                <i class="fas fa-bolt me-1"></i>En Vivo
                            </span>
                        </div>
                    </div>
                    
                    <div class="card-body p-0">
                        <div class="text-center p-4 bg-light">
                            <div class="mb-3">
                                <div class="badge d-inline-block px-4 py-3 shadow-lg bg-primary" 
                                     style="min-width: 220px; border-radius: 12px;">
                                    <i class="fas fa-concierge-bell fa-3x d-block mb-2"></i>
                                    <span class="d-block fw-bold" style="letter-spacing: 1px; font-size: 0.9em;">{{ $codigo ?: 'CÓDIGO' }}</span>
                                </div>
                            </div>
                            
                            <h5 class="fw-bold mb-2">{{ $nombre_servicio ?: 'Nombre del Servicio' }}</h5>
                            
                            @if($costo_usd)
                            <div class="mb-3">
                                <span class="badge bg-success" style="font-size: 1.2em;">
                                    <i class="fas fa-dollar-sign me-1"></i>${{ number_format($costo_usd, 2) }}
                                </span>
                                @if($aplica_iva && !$exento_iva)
                                    <br><small class="text-muted">+ IVA</small>
                                @endif
                            </div>
                            @endif
                            
                            @if($descripcion)
                            <div class="mt-3 p-3 rounded bg-white">
                                <p class="text-muted small mb-0 fst-italic">
                                    <i class="fas fa-quote-left me-2 opacity-50"></i>{{ Str::limit($descripcion, 100) }}
                                </p>
                            </div>
                            @endif
                        </div>
                        
                        <div class="card-footer bg-light py-3">
                            <div class="row text-center">
                                <div class="col-6">
                                    <small class="text-muted d-block mb-1">Estado:</small>
                                    <span class="badge badge-{{ $activo ? 'success' : 'secondary' }}">
                                        {{ $activo ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block mb-1">Duración:</small>
                                    <strong>{{ $duracion_minutos ?? 30 }} min</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
