<div>
    <div>
    @if($showModal)
    <!-- Modal para crear subespecialidad -->
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-plus-circle me-2"></i>Crear Nueva Subespecialidad
                    </h5>
                    <button type="button" class="btn-close" wire:click="closeModal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="subNombre" class="fw-bold">Nombre *</label>
                                <input type="text"
                                       class="form-control @error('nombre') is-invalid @enderror"
                                       id="subNombre"
                                       wire:model.live="nombre"
                                       placeholder="Ej: Cirugía Refractiva, Retina...">
                                @error('nombre')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="subCodigo" class="fw-bold">Código</label>
                                <input type="text"
                                       class="form-control @error('codigo') is-invalid @enderror"
                                       id="subCodigo"
                                       wire:model.live="codigo"
                                       placeholder="Auto-generado">
                                @error('codigo')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                                <small class="text-muted">Deje vacío para generar automáticamente</small>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="subCosto" class="fw-bold">Costo de Consulta *</label>
                                <input type="number"
                                       class="form-control @error('costo_consulta') is-invalid @enderror"
                                       id="subCosto"
                                       wire:model="costo_consulta"
                                       step="0.01" min="0">
                                @error('costo_consulta')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="subDuracion" class="fw-bold">Duración de Consulta (minutos) *</label>
                                <input type="number"
                                       class="form-control @error('duracion_consulta') is-invalid @enderror"
                                       id="subDuracion"
                                       wire:model="duracion_consulta"
                                       min="15" max="240">
                                @error('duracion_consulta')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="subColor" class="fw-bold">Color de Identificación *</label>
                                <div class="input-group">
                                    <input type="color"
                                           class="form-control form-control-color"
                                           id="subColor"
                                           wire:model="color"
                                           style="width: 60px; height: 38px;">
                                    <input type="text"
                                           class="form-control"
                                           wire:model="color"
                                           placeholder="#3B82F6"
                                           readonly>
                                </div>
                                @error('color')
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="subIcono" class="fw-bold">Icono Representativo *</label>
                                <input type="text"
                                       class="form-control @error('icono') is-invalid @enderror"
                                       id="subIcono"
                                       wire:model="icono"
                                       placeholder="fa-stethoscope">
                                @error('icono')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                                <small class="text-muted">Usa clases de Font Awesome</small>
                            </div>
                        </div>
                    </div>
                    <div class="form-group mb-3">
                        <label for="subDescripcion" class="fw-bold">Descripción</label>
                        <textarea class="form-control @error('descripcion') is-invalid @enderror"
                                  id="subDescripcion"
                                  wire:model="descripcion"
                                  rows="3"
                                  placeholder="Descripción de la subespecialidad"></textarea>
                        @error('descripcion')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox"
                               id="subRequiereCita"
                               wire:model="requiere_cita_previa">
                        <label class="form-check-label" for="subRequiereCita">
                            Requiere cita previa
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeModal">
                        <i class="fas fa-times me-1"></i>Cancelar
                    </button>
                    <button type="button" class="btn btn-primary" wire:click="store">
                        <i class="fas fa-save me-1"></i>Crear Subespecialidad
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

</div>
