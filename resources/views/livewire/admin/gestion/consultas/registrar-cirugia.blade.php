<div class="card shadow-sm">
    <div class="card-header bg-danger text-white">
        <h5 class="mb-0">
            <i class="ri-hospital-line me-2"></i>
            Registro Quirúrgico
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
            <!-- Información del Equipo Quirúrgico -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <h6 class="border-bottom pb-2">
                        <i class="ri-team-line text-danger me-2"></i>
                        Equipo Quirúrgico
                    </h6>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">
                        <i class="ri-user-star-line me-1"></i>
                        Cirujano Responsable *
                    </label>
                    <input type="text"
                           class="form-control"
                           wire:model.defer="cirujano_responsable"
                           placeholder="Nombre del cirujano"
                           required>
                    @error('cirujano_responsable')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">
                        <i class="ri-user-line me-1"></i>
                        Anestesiólogo
                    </label>
                    <input type="text"
                           class="form-control"
                           wire:model.defer="anestesiologo"
                           placeholder="Nombre del anestesiólogo">
                    @error('anestesiologo')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Tipo de Cirugía y Horarios -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <h6 class="border-bottom pb-2">
                        <i class="ri-scissors-cut-line text-danger me-2"></i>
                        Procedimiento Quirúrgico
                    </h6>
                </div>

                <div class="col-md-12 mb-3">
                    <label class="form-label">
                        <i class="ri-file-list-3-line me-1"></i>
                        Tipo de Cirugía *
                    </label>
                    <input type="text"
                           class="form-control"
                           wire:model.defer="tipo_cirugia"
                           placeholder="Ej: Esterilización (ovariohisterectomía), Orquiectomía, etc."
                           required>
                    @error('tipo_cirugia')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">
                        <i class="ri-time-line me-1"></i>
                        Hora de Inicio *
                    </label>
                    <input type="time"
                           class="form-control"
                           wire:model.defer="hora_inicio"
                           wire:change="calcularDuracion"
                           required>
                    @error('hora_inicio')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">
                        <i class="ri-alarm-line me-1"></i>
                        Hora de Finalización
                    </label>
                    <input type="time"
                           class="form-control"
                           wire:model.defer="hora_finalizacion"
                           wire:change="calcularDuracion">
                    @error('hora_finalizacion')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">
                        <i class="ri-timer-line me-1"></i>
                        Duración (minutos)
                    </label>
                    <input type="number"
                           class="form-control"
                           wire:model.defer="duracion_minutos"
                           readonly>
                    <small class="text-muted">Calculado automáticamente</small>
                </div>
            </div>

            <!-- Protocolo Anestésico -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <h6 class="border-bottom pb-2">
                        <i class="ri-capsule-line text-danger me-2"></i>
                        Protocolo Anestésico
                    </h6>
                </div>

                <div class="col-md-12 mb-3">
                    <label class="form-label">Protocolo Utilizado</label>
                    <textarea class="form-control"
                              wire:model.defer="protocolo_anestesico"
                              rows="2"
                              placeholder="Describa el protocolo anestésico: pre-medicación, inducción, mantenimiento"></textarea>
                    @error('protocolo_anestesico')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-12 mb-3">
                    <label class="form-label">Medicamentos Administrados</label>
                    <textarea class="form-control"
                              wire:model.defer="medicamentos_administrados"
                              rows="2"
                              placeholder="Liste todos los medicamentos con dosis y vía de administración"></textarea>
                    @error('medicamentos_administrados')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Monitoreo Intraoperatorio -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <h6 class="border-bottom pb-2">
                        <i class="ri-heart-pulse-line text-danger me-2"></i>
                        Monitoreo Intraoperatorio
                    </h6>
                </div>

                <div class="col-md-3 mb-3">
                    <label class="form-label">FC (lpm)</label>
                    <input type="number"
                           class="form-control"
                           wire:model.defer="monitoreo_fc"
                           min="30"
                           max="300"
                           placeholder="Frecuencia cardíaca">
                    @error('monitoreo_fc')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-3 mb-3">
                    <label class="form-label">FR (rpm)</label>
                    <input type="number"
                           class="form-control"
                           wire:model.defer="monitoreo_fr"
                           min="5"
                           max="120"
                           placeholder="Frecuencia respiratoria">
                    @error('monitoreo_fr')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-3 mb-3">
                    <label class="form-label">Temperatura (°C)</label>
                    <input type="number"
                           class="form-control"
                           wire:model.defer="monitoreo_temp"
                           step="0.1"
                           min="35"
                           max="43"
                           placeholder="Temp rectal">
                    @error('monitoreo_temp')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-3 mb-3">
                    <label class="form-label">SpO₂ (%)</label>
                    <input type="number"
                           class="form-control"
                           wire:model.defer="monitoreo_spo2"
                           min="80"
                           max="100"
                           placeholder="Saturación O₂">
                    @error('monitoreo_spo2')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Presión Arterial</label>
                    <input type="text"
                           class="form-control"
                           wire:model.defer="monitoreo_presion"
                           placeholder="Ej: 120/80 mmHg">
                    @error('monitoreo_presion')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Hallazgos y Material -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <h6 class="border-bottom pb-2">
                        <i class="ri-search-eye-line text-danger me-2"></i>
                        Hallazgos Quirúrgicos
                    </h6>
                </div>

                <div class="col-md-12 mb-3">
                    <label class="form-label">Descripción de Hallazgos</label>
                    <textarea class="form-control"
                              wire:model.defer="hallazgos_quirurgicos"
                              rows="3"
                              placeholder="Describa lo encontrado durante la cirugía"></textarea>
                    @error('hallazgos_quirurgicos')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Material Implantado</label>
                    <input type="text"
                           class="form-control"
                           wire:model.defer="material_implantado"
                           placeholder="Ej: Mallas, placas, tornillos, suturas absorbibles">
                    @error('material_implantado')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Tipo de Suturas</label>
                    <input type="text"
                           class="form-control"
                           wire:model.defer="suturas_utilizadas"
                           placeholder="Ej: Vicryl 3-0, Nylon 2-0">
                    @error('suturas_utilizadas')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Complicaciones -->
            <div class="mb-3">
                <label class="form-label">
                    <i class="ri-alert-triangle-line me-1"></i>
                    Complicaciones Intraoperatorias
                </label>
                <textarea class="form-control"
                          wire:model.defer="complicaciones"
                          rows="2"
                          placeholder="Describa cualquier complicación presentada durante la cirugía"></textarea>
                @error('complicaciones')
                    <span class="text-danger small">{{ $message }}</span>
                @enderror
            </div>

            <!-- Estado Post-operatorio -->
            <div class="row mb-3">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Estado Post-operatorio Inmediato</label>
                    <select class="form-select" wire:model.defer="estado_post_operatorio">
                        <option value="">Seleccione...</option>
                        <option value="estable">Estable</option>
                        <option value="inestable">Inestable</option>
                        <option value="recuperacion_normal">Recuperación Normal</option>
                        <option value="complicaciones">Con Complicaciones</option>
                    </select>
                    @error('estado_post_operatorio')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Observaciones -->
            <div class="mb-3">
                <label class="form-label">Observaciones Adicionales</label>
                <textarea class="form-control"
                          wire:model.defer="observaciones"
                          rows="2"
                          placeholder="Notas adicionales sobre el procedimiento"></textarea>
                @error('observaciones')
                    <span class="text-danger small">{{ $message }}</span>
                @enderror
            </div>

            <!-- Botones -->
            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('admin.gestion.consultas.en-cirugia') }}"
                   class="btn btn-secondary">
                    <i class="ri-close-line me-1"></i>
                    Cancelar
                </a>
                <button type="submit"
                        class="btn btn-danger"
                        wire:loading.attr="disabled">
                    <span wire:loading.remove>
                        <i class="ri-save-line me-1"></i>
                        Guardar Registro Quirúrgico
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
