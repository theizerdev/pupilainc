<div class="card shadow-sm">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">
            <i class="ri-injection-line me-2"></i>
            Registrar Tratamiento
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
                <!-- Tipo de Tratamiento -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">
                        <i class="ri-list-check me-1"></i>
                        Tipo de Tratamiento
                    </label>
                    <select class="form-select" wire:model.defer="tipo_tratamiento" required>
                        <option value="">Seleccione...</option>
                        <option value="inyeccion">Inyección</option>
                        <option value="intravenoso">Vía Intravenosa (IV)</option>
                        <option value="subcutaneo">Subcutáneo (SC)</option>
                        <option value="intramuscular">Intramuscular (IM)</option>
                        <option value="oral">Medicación Oral</option>
                        <option value="topico">Tópico</option>
                        <option value="nebulizacion">Nebulización</option>
                        <option value="otro">Otro</option>
                    </select>
                    @error('tipo_tratamiento')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Medicamento/Tratamiento -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">
                        <i class="ri-capsule-line me-1"></i>
                        Medicamento / Tratamiento
                    </label>
                    <input type="text"
                           class="form-control"
                           wire:model.defer="medicamento"
                           placeholder="Ej: Amoxicilina 250mg"
                           required>
                    @error('medicamento')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="row">
                <!-- Dosis -->
                <div class="col-md-4 mb-3">
                    <label class="form-label">
                        <i class="ri-drop-line me-1"></i>
                        Dosis
                    </label>
                    <input type="text"
                           class="form-control"
                           wire:model.defer="dosis"
                           placeholder="Ej: 1 ml, 250 mg, 1 tableta">
                    @error('dosis')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Vía de Administración -->
                <div class="col-md-4 mb-3">
                    <label class="form-label">
                        <i class="ri-syringe-line me-1"></i>
                        Vía de Administración
                    </label>
                    <select class="form-select" wire:model.defer="via_administracion">
                        <option value="">Seleccione...</option>
                        <option value="oral">Oral</option>
                        <option value="iv">Intravenosa (IV)</option>
                        <option value="im">Intramuscular (IM)</option>
                        <option value="sc">Subcutánea (SC)</option>
                        <option value="topica">Tópica</option>
                        <option value="oftalmica">Oftálmica</option>
                        <option value="otica">Ótica</option>
                        <option value="rectal">Rectal</option>
                    </select>
                    @error('via_administracion')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Frecuencia -->
                <div class="col-md-4 mb-3">
                    <label class="form-label">
                        <i class="ri-time-line me-1"></i>
                        Frecuencia
                    </label>
                    <input type="text"
                           class="form-control"
                           wire:model.defer="frecuencia"
                           placeholder="Ej: Cada 8 horas, BID, SID">
                    @error('frecuencia')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="row">
                <!-- Sitio de Aplicación -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">
                        <i class="ri-map-pin-line me-1"></i>
                        Sitio de Aplicación
                    </label>
                    <input type="text"
                           class="form-control"
                           wire:model.defer="sitio_aplicacion"
                           placeholder="Ej: Miembro anterior derecho, región lumbar">
                    @error('sitio_aplicacion')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Hora de Administración -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">
                        <i class="ri-alarm-line me-1"></i>
                        Hora de Administración
                    </label>
                    <input type="time"
                           class="form-control"
                           wire:model.defer="hora_administracion">
                    @error('hora_administracion')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Reacción Adversa -->
            <div class="mb-3">
                <label class="form-label">
                    <i class="ri-alert-line me-1"></i>
                    ¿Presentó Reacción Adversa?
                </label>
                <div class="form-check">
                    <input class="form-check-input"
                           type="radio"
                           id="reaccion_si"
                           value="1"
                           wire:model.defer="reaccion_adversa">
                    <label class="form-check-label" for="reaccion_si">
                        Sí
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input"
                           type="radio"
                           id="reaccion_no"
                           value="0"
                           wire:model.defer="reaccion_adversa"
                           checked>
                    <label class="form-check-label" for="reaccion_no">
                        No
                    </label>
                </div>
                @error('reaccion_adversa')
                    <span class="text-danger small">{{ $message }}</span>
                @enderror
            </div>

            <!-- Observaciones -->
            <div class="mb-3">
                <label class="form-label">
                    <i class="ri-file-text-line me-1"></i>
                    Observaciones del Tratamiento
                </label>
                <textarea class="form-control"
                          wire:model.defer="observaciones"
                          rows="3"
                          placeholder="Notas sobre la aplicación, respuesta del paciente, etc."></textarea>
                @error('observaciones')
                    <span class="text-danger small">{{ $message }}</span>
                @enderror
            </div>

            <!-- Botones -->
            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('admin.gestion.consultas.en-tratamiento') }}"
                   class="btn btn-secondary">
                    <i class="ri-close-line me-1"></i>
                    Cancelar
                </a>
                <button type="submit"
                        class="btn btn-primary"
                        wire:loading.attr="disabled">
                    <span wire:loading.remove>
                        <i class="ri-save-line me-1"></i>
                        Guardar Tratamiento
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
