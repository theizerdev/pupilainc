<div>
    @section('title', 'Editar Categoría de Producto')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="ri ri-price-tag-3-line me-2"></i>Editar Categoría</h2>
        <a href="{{ route('admin.inventario.categorias.index') }}" class="btn btn-secondary">
            <i class="ri ri-arrow-left-line me-1"></i>Volver
        </a>
    </div>

    <form wire:submit.prevent="update">
        <div class="row">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="ri ri-information-line me-2"></i>Información de la Categoría</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group mb-3">
                            <label for="nombre">Nombre *</label>
                            <input type="text" class="form-control @error('nombre') is-invalid @enderror"
                                   id="nombre" wire:model="nombre" placeholder="Nombre de la categoría">
                            @error('nombre') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="descripcion">Descripción</label>
                            <textarea class="form-control @error('descripcion') is-invalid @enderror"
                                      id="descripcion" wire:model="descripcion" rows="3"
                                      placeholder="Descripción opcional"></textarea>
                            @error('descripcion') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="color">Color</label>
                                    <div class="d-flex gap-2 align-items-center">
                                        <input type="color" class="form-control form-control-color @error('color') is-invalid @enderror"
                                               id="color" wire:model="color" style="width:50px;height:38px;">
                                        <input type="text" class="form-control @error('color') is-invalid @enderror"
                                               wire:model="color" placeholder="#3B82F6" maxlength="7">
                                    </div>
                                    @error('color') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                @include('livewire.admin.inventario.partials.icon-picker')
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="ri ri-settings-3-line me-2"></i>Opciones</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="status" wire:model="status">
                            <label class="form-check-label" for="status">Activo</label>
                        </div>

                        <div class="mb-3">
                            <label class="d-block mb-1">Vista previa:</label>
                            <span class="badge" style="background:{{ $color ?? '#3B82F6' }};font-size:0.9rem;">
                                @if($icono) <i class="{{ $icono }} me-1"></i> @endif
                                {{ $nombre ?: 'Nombre categoría' }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="ri ri-save-line me-1"></i>Actualizar Categoría
                            </button>
                            <a href="{{ route('admin.inventario.categorias.index') }}" class="btn btn-secondary">
                                <i class="ri ri-close-line me-1"></i>Cancelar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
