<div>
    @section('title', 'Nueva Marca')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="ri ri-bookmark-line me-2"></i>Nueva Marca</h2>
        <a href="{{ route('admin.inventario.marcas.index') }}" class="btn btn-secondary">
            <i class="ri ri-arrow-left-line me-1"></i>Volver
        </a>
    </div>

    <form wire:submit.prevent="store">
        <div class="row">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="ri ri-information-line me-2"></i>Información de la Marca</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group mb-3">
                            <label for="nombre">Nombre *</label>
                            <input type="text" class="form-control @error('nombre') is-invalid @enderror"
                                   id="nombre" wire:model="nombre" placeholder="Nombre de la marca">
                            @error('nombre') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="descripcion">Descripción</label>
                            <textarea class="form-control @error('descripcion') is-invalid @enderror"
                                      id="descripcion" wire:model="descripcion" rows="3"
                                      placeholder="Descripción opcional"></textarea>
                            @error('descripcion') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="sitio_web">Sitio Web</label>
                            <input type="url" class="form-control @error('sitio_web') is-invalid @enderror"
                                   id="sitio_web" wire:model="sitio_web" placeholder="https://ejemplo.com">
                            @error('sitio_web') <span class="invalid-feedback">{{ $message }}</span> @enderror
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
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="ri ri-save-line me-1"></i>Guardar Marca
                            </button>
                            <a href="{{ route('admin.inventario.marcas.index') }}" class="btn btn-secondary">
                                <i class="ri ri-close-line me-1"></i>Cancelar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
