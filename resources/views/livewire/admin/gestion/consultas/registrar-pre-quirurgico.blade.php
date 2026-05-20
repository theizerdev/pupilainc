<div class="card shadow-sm">
    <div class="card-header bg-warning text-dark">
        <h5 class="mb-0">
            <i class="ri-hospital-line me-2"></i>
            Evaluación Pre-Quirúrgica
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
            <!-- Ayuno -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <h6 class="border-bottom pb-2">
                        <i class="ri-time-line text-warning me-2"></i>
                        Control de Ayuno
                    </h6>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">
                        <i class="ri-restaurant-line me-1"></i>
                        Horas de Ayuno (Alimento Sólido)
                    </label>
                    <input type="number"
                           class="form-control"
                           wire:model.defer="ayuno_horas"
                           min="0"
                           max="48"
                           placeholder="Ej: 8, 12">
                    @error('ayuno_horas')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                    <small class="text-muted">Recomendado: 8-12 horas para perros/gatos</small>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">
                        <i class="ri-drop-line me-1"></i>
                        Horas de Ayuno (Agua)
                    </label>
                    <input type="number"
                           class="form-control"
                           wire:model.defer="ayuno_agua_horas"
                           min="0"
                           max="24"
                           placeholder="Ej: 2, 4">
                    @error('ayuno_agua_horas')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                    <small class="text-muted">Recomendado: 2-4 horas antes de cirugía</small>
                </div>
            </div>

            <!-- Pre-medicación -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <h6 class="border-bottom pb-2">
                        <i class="ri-capsule-line text-warning me-2"></i>
                        Pre-medicación
                    </h6>
                </div>

                <div class="col-md-12 mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input"
                               type="checkbox"
                               id="premedicacion_aplicada"
                               wire:model.defer="premedicacion_aplicada">
                        <label class="form-check-label" for="premedicacion_aplicada">
                            <strong>¿Se aplicó pre-medicación?</strong>
                        </label>
                    </div>
                </div>

                @if($premedicacion_aplicada)
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Medicamento(s) Administrado(s)</label>
                        <input type="text"
                               class="form-control"
                               wire:model.defer="premedicacion_medicamento"
                               placeholder="Ej: Acepromacina + Buprenorfina">
                        @error('premedicacion_medicamento')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Hora de Administración</label>
                        <input type="time"
                               class="form-control"
                               wire:model.defer="premedicacion_hora">
                        @error('premedicacion_hora')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </div>
                @endif
            </div>

            <!-- Exámenes Pre-operatorios -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <h6 class="border-bottom pb-2">
                        <i class="ri-flask-line text-warning me-2"></i>
                        Exámenes Pre-operatorios
                    </h6>
                </div>

                <div class="col-md-12 mb-3">
                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <div class="form-check">
                                <input class="form-check-input"
                                       type="checkbox"
                                       id="exam_hemograma"
                                       wire:model.defer="exam_hemograma">
                                <label class="form-check-label" for="exam_hemograma">
                                    Hemograma Completo
                                </label>
                            </div>
                        </div>
                        <div class="col-md-4 mb-2">
                            <div class="form-check">
                                <input class="form-check-input"
                                       type="checkbox"
                                       id="exam_bioquimica"
                                       wire:model.defer="exam_bioquimica">
                                <label class="form-check-label" for="exam_bioquimica">
                                    Bioquímica Sanguínea
                                </label>
                            </div>
                        </div>
                        <div class="col-md-4 mb-2">
                            <div class="form-check">
                                <input class="form-check-input"
                                       type="checkbox"
                                       id="exam_coagulacion"
                                       wire:model.defer="exam_coagulacion">
                                <label class="form-check-label" for="exam_coagulacion">
                                    Perfil de Coagulación
                                </label>
                            </div>
                        </div>
                        <div class="col-md-4 mb-2">
                            <div class="form-check">
                                <input class="form-check-input"
                                       type="checkbox"
                                       id="exam_electrolitos"
                                       wire:model.defer="exam_electrolitos">
                                <label class="form-check-label" for="exam_electrolitos">
                                    Electrolitos
                                </label>
                            </div>
                        </div>
                        <div class="col-md-4 mb-2">
                            <div class="form-check">
                                <input class="form-check-input"
                                       type="checkbox"
                                       id="exam_radiografia"
                                       wire:model.defer="exam_radiografia">
                                <label class="form-check-label" for="exam_radiografia">
                                    Radiografía Torácica
                                </label>
                            </div>
                        </div>
                        <div class="col-md-4 mb-2">
                            <div class="form-check">
                                <input class="form-check-input"
                                       type="checkbox"
                                       id="exam_ecocardiograma"
                                       wire:model.defer="exam_ecocardiograma">
                                <label class="form-check-label" for="exam_ecocardiograma">
                                    Ecocardiograma
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-12 mb-3">
                    <label class="form-label">Observaciones de Exámenes</label>
                    <textarea class="form-control"
                              wire:model.defer="exam_observaciones"
                              rows="2"
                              placeholder="Resultados relevantes de los exámenes realizados"></textarea>
                    @error('exam_observaciones')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Evaluación de Riesgos -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <h6 class="border-bottom pb-2">
                        <i class="ri-alert-triangle-line text-warning me-2"></i>
                        Evaluación de Riesgos Anestésicos
                    </h6>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">
                        <i class="ri-shield-line me-1"></i>
                        Clasificación ASA (Riesgo Anestésico)
                    </label>
                    <select class="form-select" wire:model.defer="clasificacion_asa" required>
                        <option value="">Seleccione...</option>
                        <option value="1">ASA I - Paciente sano normal</option>
                        <option value="2">ASA II - Enfermedad sistémica leve</option>
                        <option value="3">ASA III - Enfermedad sistémica severa</option>
                        <option value="4">ASA IV - Enfermedad que amenaza la vida</option>
                        <option value="5">ASA V - Moribundo, no se espera sobrevivir</option>
                    </select>
                    @error('clasificacion_asa')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">
                        <i class="ri-heart-pulse-line me-1"></i>
                        Estado Físico General
                    </label>
                    <select class="form-select" wire:model.defer="estado_fisico">
                        <option value="">Seleccione...</option>
                        <option value="excelente">Excelente</option>
                        <option value="bueno">Bueno</option>
                        <option value="regular">Regular</option>
                        <option value="malo">Malo</option>
                        <option value="critico">Crítico</option>
                    </select>
                    @error('estado_fisico')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-12 mb-3">
                    <label class="form-label">
                        <i class="ri-file-list-3-line me-1"></i>
                        Factores de Riesgo Identificados
                    </label>
                    <textarea class="form-control"
                              wire:model.defer="factores_riesgo"
                              rows="3"
                              placeholder="Liste factores de riesgo: edad avanzada, obesidad, enfermedades crónicas, alergias, etc."></textarea>
                    @error('factores_riesgo')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Consentimiento Informado -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <h6 class="border-bottom pb-2">
                        <i class="ri-checkbox-circle-line text-warning me-2"></i>
                        Consentimiento Informado
                    </h6>
                </div>

                <div class="col-md-12 mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input"
                               type="checkbox"
                               id="consentimiento_firmado"
                               wire:model.defer="consentimiento_firmado"
                               required>
                        <label class="form-check-label" for="consentimiento_firmado">
                            <strong>Consentimiento informado firmado por el propietario</strong>
                        </label>
                    </div>
                    @error('consentimiento_firmado')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-12 mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input"
                               type="checkbox"
                               id="explicacion_riesgos"
                               wire:model.defer="explicacion_riesgos">
                        <label class="form-check-label" for="explicacion_riesgos">
                            Se explicaron los riesgos anestésicos y quirúrgicos al propietario
                        </label>
                    </div>
                </div>
            </div>

            <!-- Observaciones Generales -->
            <div class="mb-3">
                <label class="form-label">
                    <i class="ri-sticky-note-line me-1"></i>
                    Observaciones Pre-quirúrgicas Adicionales
                </label>
                <textarea class="form-control"
                          wire:model.defer="observaciones"
                          rows="3"
                          placeholder="Notas adicionales sobre preparación, consideraciones especiales, etc."></textarea>
                @error('observaciones')
                    <span class="text-danger small">{{ $message }}</span>
                @enderror
            </div>

            <!-- Alerta informativa -->
            <div class="alert alert-warning d-flex align-items-start" role="alert">
                <i class="ri-information-line fs-5 me-2"></i>
                <div>
                    <strong>Recordatorio:</strong>
                    <ul class="mb-0 mt-2">
                        <li>Verificar que el ayuno sea adecuado según especie y edad</li>
                        <li>Confirmar que todos los exámenes pre-operatorios estén disponibles</li>
                        <li>Asegurar consentimiento informado firmado</li>
                        <li>Preparar protocolo anestésico según clasificación ASA</li>
                    </ul>
                </div>
            </div>

            <!-- Botones -->
            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('admin.gestion.consultas.pre-quirurgico') }}"
                   class="btn btn-secondary">
                    <i class="ri-close-line me-1"></i>
                    Cancelar
                </a>
                <button type="submit"
                        class="btn btn-warning text-dark"
                        wire:loading.attr="disabled">
                    <span wire:loading.remove>
                        <i class="ri-save-line me-1"></i>
                        Guardar Evaluación Pre-quirúrgica
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
