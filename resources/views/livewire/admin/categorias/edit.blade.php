<div>
<div class="py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-edit me-2"></i>Editar Categoría</h1>
                <p class="text-muted">Modifique la información de la categoría</p>
            </div>
            <div>
                <a href="{{ route('admin.categorias.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Volver al Listado
                </a>
            </div>
        </div>

        <!-- Form -->
        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-edit me-2"></i>Información Básica</h6>
                    </div>
                    <div class="card-body">
                        <form wire:submit.prevent="update">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="nombre" class="fw-bold">
                                            <i class="fas fa-font me-1"></i>Nombre *
                                        </label>
                                        <input type="text" 
                                               class="form-control form-control-lg @error('nombre') is-invalid @enderror" 
                                               id="nombre" 
                                               wire:model.live="nombre" 
                                               placeholder="Ej: Consulta General"
                                               autocomplete="off">
                                        @error('nombre')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                        <small class="form-text text-muted">Nombre descriptivo de la categoría</small>
                                    </div>
                                </div>
                            </div>

                            @if(auth()->user()->hasRole('Super Administrador'))
                            <hr class="my-4">
                            <h6 class="mb-3"><i class="fas fa-building me-2"></i>Asignación (Solo Super Admin)</h6>
                            <div class="row">
                                <div class="col-md-6">
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

                                <div class="col-md-6">
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
                            <h6 class="mb-3"><i class="fas fa-palette me-2"></i>Apariencia</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="color" class="fw-bold">Color de Identificación *</label>
                                        <div class="input-group">
                                            <input type="color" 
                                                   class="form-control form-control-color" 
                                                   id="color" 
                                                   wire:model.live="color"
                                                   style="width: 60px; height: 46px;">
                                            <input type="text" 
                                                   class="form-control form-control-lg" 
                                                   wire:model.live="color" 
                                                   placeholder="#3B82F6"
                                                   readonly>
                                        </div>
                                        @error('color')
                                            <span class="invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
                                        <small class="form-text text-muted">Color distintivo para esta categoría</small>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="icono" class="fw-bold">Icono Representativo</label>
                                        <select class="form-control @error('icono') is-invalid @enderror" 
                                                id="icono" 
                                                wire:model.live="icono">
                                            <optgroup label="General">
                                                <option value="ri-price-tag-3-line">Etiqueta</option>
                                                <option value="ri-folder-line">Carpeta</option>
                                                <option value="ri-archive-line">Archivo</option>
                                                <option value="ri-booklet-line">Libreta</option>
                                            </optgroup>
                                            <optgroup label="Médicos">
                                                <option value="ri-stethoscope-line">Estetoscopio</option>
                                                <option value="ri-heart-pulse-line">Corazón</option>
                                                <option value="ri-hospital-line">Hospital</option>
                                            </optgroup>
                                        </select>
                                        @error('icono')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                        <small class="form-text text-muted">Icono Remix Icon para la categoría</small>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">
                            <h6 class="mb-3"><i class="fas fa-align-left me-2"></i>Detalles Adicionales</h6>
                            <div class="form-group">
                                <label for="descripcion" class="fw-bold">Descripción</label>
                                <textarea class="form-control @error('descripcion') is-invalid @enderror" 
                                          id="descripcion" 
                                          wire:model.live="descripcion" 
                                          rows="4" 
                                          placeholder="Describa las características y particularidades de esta categoría..."></textarea>
                                @error('descripcion')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                                <small class="form-text text-muted">Descripción opcional pero recomendada</small>
                            </div>

                            <hr class="my-4">
                            <h6 class="mb-3"><i class="fas fa-cog me-2"></i>Configuración</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group p-3 border rounded">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" 
                                                   type="checkbox" 
                                                   id="orden" 
                                                   wire:model.live="orden">
                                            <label class="form-check-label fw-bold" for="orden">
                                                <i class="ri ri-sort-desc me-1"></i>Orden
                                            </label>
                                        </div>
                                        <input type="number" 
                                               class="form-control mt-2 @error('orden') is-invalid @enderror" 
                                               wire:model.live="orden" 
                                               placeholder="0">
                                        <small class="text-muted d-block mt-2">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Número para ordenar las categorías (menor = primero)
                                        </small>
                                    </div>
                                </div>

                                <div class="col-md-6">
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
                                </div>
                            </div>

                            <hr class="my-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <a href="{{ route('admin.categorias.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times"></i> Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save"></i> Actualizar Categoría
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Preview Panel -->
            <div class="col-lg-4">
                <div class="card shadow-sm border-0 sticky-top" style="top: 20px; z-index: 100; border-radius: 15px; overflow: hidden;">
                    <div class="card-header text-white py-3" 
                         style="background: linear-gradient(135deg, {{ $color ?: '#3B82F6' }} 0%, #1E40AF 100%); transition: all 0.3s ease;">
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
                        <div class="text-center p-4" 
                             style="background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(59, 130, 246, 0.05) 100%);">
                            
                            <div class="mb-3" style="transition: transform 0.3s ease;" 
                                 onmouseover="this.style.transform='scale(1.1)'" 
                                 onmouseout="this.style.transform='scale(1)'">
                                <div class="badge d-inline-block px-4 py-3 shadow-lg" 
                                     style="background-color: {{ $color ?: '#3B82F6' }}; color: white; font-size: 1.2em; min-width: 220px; border-radius: 12px; transition: all 0.3s ease;">
                                    <i class="ri {{ $icono ?: 'ri-price-tag-3-line' }} fa-3x d-block mb-2" 
                                       style="transition: transform 0.3s ease;"></i>
                                    <span class="d-block fw-bold" style="letter-spacing: 1px;">{{ $nombre ?: 'Nombre de la Categoría' }}</span>
                                </div>
                            </div>
                            
                            @if(strlen($descripcion ?: '') > 0)
                            <div class="mx-3 mt-3 p-3 rounded" 
                                 style="background-color: rgba(0,0,0,0.03); min-height: 80px;">
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
                                    <small class="text-muted d-block mb-1">Orden:</small>
                                    <strong>{{ $orden ?? 0 }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
