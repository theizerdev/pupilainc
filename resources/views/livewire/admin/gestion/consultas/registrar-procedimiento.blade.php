<div class="card shadow-sm">
    <div class="card-header bg-info text-white">
        <h5 class="mb-0">
            <i class="ri-scissors-cut-line me-2"></i>
            Registrar Procedimiento / Cura
        </h5>
    </div>
    <div class="card-body">
        @if($consulta->mascota)
            <div class="alert alert-info d-flex align-items-center mb-4">
                <i class="ri-paw-line fs-4 me-3"></i>
                <div>
                    <strong>{{ $consulta->mascota->nombre }}</strong><br>
                    <small>{{ $consulta->mascota->especie?->nombre }} - {{ $consulta->mascota->raza?->nombre }}</small>
                </div>
            </div>
        @endif

        <form wire:submit.prevent="guardar">
            <div class="row">
                <!-- Tipo de Procedimiento -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">
                        <i class="ri-list-check me-1"></i>
                        Tipo de Procedimiento
                    </label>
                    <select class="form-select" wire:model.defer="tipo_procedimiento" required>
                        <option value="">Seleccione...</option>
                        <option value="cura_herida">Cura de Herida</option>
                        <option value="cambio_vendaje">Cambio de Vendaje</option>
                        <option value="limpieza_otorrinolaringologica">Limpieza Otorrinolaringológica</option>
                        <option value="limpieza_dental">Limpieza Dental</option>
                        <option value="extraccion_sutura">Extracción de Suturas</option>
                        <option value="aplicacion_puntos">Aplicación de Puntos</option>
                        <option value="drenaje_absceso">Drenaje de Absceso</option>
                        <option value="colocacion_cateter">Colocación de Catéter</option>
                        <option value="retirada_cateter">Retirada de Catéter</option>
                        <option value="higiene_general">Higiene General</option>
                        <option value="otro">Otro</option>
                    </select>
                    @error('tipo_procedimiento')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Área Afectada -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">
                        <i class="ri-map-pin-line me-1"></i>
                        Área Afectada / Localización
                    </label>
                    <input type="text"
                           class="form-control"
                           wire:model.defer="area_afectada"
                           placeholder="Ej: Miembro posterior izquierdo, región cervical">
                    @error('area_afectada')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="row">
                <!-- Descripción de la Herida -->
                <div class="col-md-12 mb-3">
                    <label class="form-label">
                        <i class="ri-file-text-line me-1"></i>
                        Descripción de la Herida / Lesión
                    </label>
                    <textarea class="form-control"
                              wire:model.defer="descripcion_herida"
                              rows="3"
                              placeholder="Describa el estado de la herida: tamaño, profundidad, exudado, tejido de granulación, etc."></textarea>
                    @error('descripcion_herida')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="row">
                <!-- Tipo de Vendaje -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">
                        <i class="ri-bandage-line me-1"></i>
                        Tipo de Vendaje Aplicado
                    </label>
                    <select class="form-select" wire:model.defer="tipo_vendaje">
                        <option value="">Ninguno</option>
                        <option value="compresivo">Compresivo</option>
                        <option value="almohadillado">Almohadillado</option>
                        <option value="yeso">Yeso/Escayola</option>
                        <option value="ferula">Férula</option>
                        <option value="elastico">Elástico</option>
                        <option value="adhesivo">Adhesivo</option>
                        <option value="tubular">Tubular</option>
                    </select>
                    @error('tipo_vendaje')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Material Utilizado -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">
                        <i class="ri-tools-line me-1"></i>
                        Material Utilizado
                    </label>
                    <input type="text"
                           class="form-control"
                           wire:model.defer="material_utilizado"
                           placeholder="Ej: Gasas estériles, solución salina, yodo povidona">
                    @error('material_utilizado')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="row">
                <!-- Estado Post-Procedimiento -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">
                        <i class="ri-heart-pulse-line me-1"></i>
                        Estado Post-Procedimiento
                    </label>
                    <select class="form-select" wire:model.defer="estado_post_procedimiento">
                        <option value="">Seleccione...</option>
                        <option value="estable">Estable</option>
                        <option value="mejora">En Mejora</option>
                        <option value="sin_cambios">Sin Cambios</option>
                        <option value="empeora">Empeora</option>
                        <option value="requiere_revision">Requiere Revisión Urgente</option>
                    </select>
                    @error('estado_post_procedimiento')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Próxima Cura -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">
                        <i class="ri-calendar-event-line me-1"></i>
                        Próxima Cura Programada
                    </label>
                    <input type="date"
                           class="form-control"
                           wire:model.defer="proxima_cura">
                    @error('proxima_cura')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Control de Peso (si aplica) -->
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">
                        <i class="ri-weight-line me-1"></i>
                        Peso Actual (kg)
                    </label>
                    <input type="number"
                           class="form-control"
                           wire:model.defer="peso_actual_kg"
                           step="0.01"
                           min="0.1"
                           max="100"
                           placeholder="Control de peso">
                    @error('peso_actual_kg')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">
                        <i class="ri-arrow-up-down-line me-1"></i>
                        Variación de Peso
                    </label>
                    <input type="text"
                           class="form-control"
                           wire:model.defer="variacion_peso"
                           placeholder="Ej: +0.5 kg, -0.3 kg"
                           readonly>
                    <small class="text-muted">Se calculará automáticamente si hay registro previo</small>
                </div>
            </div>

            <!-- Observaciones -->
            <div class="mb-3">
                <label class="form-label">
                    <i class="ri-sticky-note-line me-1"></i>
                    Observaciones del Procedimiento
                </label>
                <textarea class="form-control"
                          wire:model.defer="observaciones"
                          rows="3"
                          placeholder="Notas sobre tolerancia al procedimiento, complicaciones, recomendaciones, etc."></textarea>
                @error('observaciones')
                    <span class="text-danger small">{{ $message }}</span>
                @enderror
            </div>

            <!-- Botones -->
            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('admin.gestion.consultas.en-procedimiento') }}"
                   class="btn btn-secondary">
                    <i class="ri-close-line me-1"></i>
                    Cancelar
                </a>
                <button type="submit"
                        class="btn btn-info text-white"
                        wire:loading.attr="disabled">
                    <span wire:loading.remove>
                        <i class="ri-save-line me-1"></i>
                        Guardar Procedimiento
                    </span>
                    <span wire:loading>
                        <i class="ri-loader-4-line me-1"></i>
                        Guardando...
                    </span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal de confirmación -->
@if(session()->has('success'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toast = document.createElement('div');
            toast.className = 'position-fixed top-0 end-0 p-3';
            toast.style.zIndex = '9999';
            toast.innerHTML = `
                <div class="toast show" role="alert">
                    <div class="toast-header bg-success text-white">
                        <i class="ri-checkbox-circle-line me-2"></i>
                        <strong class="me-auto">Éxito</strong>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
                    </div>
                    <div class="toast-body">
                        {{ session('success') }}
                    </div>
                </div>
            `;
            document.body.appendChild(toast);

            setTimeout(() => {
                toast.remove();
            }, 3000);
        });
    </script>
@endif
