<div class="card shadow-sm">
    <div class="card-header bg-warning text-dark">
        <h5 class="mb-0">
            <i class="ri-user-follow-line me-2"></i>
            Educación al Propietario y Alta
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
            <!-- Diagnóstico Final -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <h6 class="border-bottom pb-2">
                        <i class="ri-stethoscope-line text-warning me-2"></i>
                        Diagnóstico y Tratamiento
                    </h6>
                </div>

                <div class="col-md-12 mb-3">
                    <label class="form-label">
                        <i class="ri-file-text-line me-1"></i>
                        Diagnóstico Final *
                    </label>
                    <textarea class="form-control"
                              wire:model.defer="diagnostico_final"
                              rows="2"
                              placeholder="Diagnóstico definitivo de la consulta"
                              required></textarea>
                    @error('diagnostico_final')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-12 mb-3">
                    <label class="form-label">Tratamiento Domiciliario</label>
                    <textarea class="form-control"
                              wire:model.defer="tratamiento_domiciliario"
                              rows="3"
                              placeholder="Describa el tratamiento a seguir en casa"></textarea>
                    @error('tratamiento_domiciliario')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Medicamentos y Receta -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <h6 class="border-bottom pb-2">
                        <i class="ri-capsule-line text-warning me-2"></i>
                        Medicamentos Recetados
                    </h6>
                </div>

                <div class="col-md-12 mb-3">
                    <label class="form-label">Medicamentos</label>
                    <textarea class="form-control"
                              wire:model.defer="medicamentos_receta"
                              rows="2"
                              placeholder="Liste todos los medicamentos recetados"></textarea>
                    @error('medicamentos_receta')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Instrucciones de Dosis</label>
                    <textarea class="form-control"
                              wire:model.defer="dosis_instrucciones"
                              rows="2"
                              placeholder="Ej: 1 tableta cada 8 horas por vía oral"></textarea>
                    @error('dosis_instrucciones')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Duración del Tratamiento</label>
                    <input type="text"
                           class="form-control"
                           wire:model.defer="duracion_tratamiento"
                           placeholder="Ej: 7 días, 10 días, hasta agotar">
                    @error('duracion_tratamiento')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Cuidados Especiales -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <h6 class="border-bottom pb-2">
                        <i class="ri-heart-line text-warning me-2"></i>
                        Cuidados Post-operatorios / Especiales
                    </h6>
                </div>

                <div class="col-md-12 mb-3">
                    <label class="form-label">Cuidados de Herida (si aplica)</label>
                    <textarea class="form-control"
                              wire:model.defer="cuidados_herida"
                              rows="2"
                              placeholder="Instrucciones para cuidado de incisión/herida"></textarea>
                    @error('cuidados_herida')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Alimentación Recomendada</label>
                    <textarea class="form-control"
                              wire:model.defer="alimentacion_recomendada"
                              rows="2"
                              placeholder="Dieta especial, restricciones alimentarias"></textarea>
                    @error('alimentacion_recomendada')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Restricciones de Ejercicio</label>
                    <input type="text"
                           class="form-control"
                           wire:model.defer="ejercicio_restricciones"
                           placeholder="Ej: Reposo absoluto 7 días, no correr">
                    @error('ejercicio_restricciones')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Signos de Alerta -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <h6 class="border-bottom pb-2">
                        <i class="ri-alarm-warning-line text-warning me-2"></i>
                        Signos de Alerta
                    </h6>
                </div>

                <div class="col-md-12 mb-3">
                    <label class="form-label">Signos que Requieren Atención Inmediata</label>
                    <textarea class="form-control"
                              wire:model.defer="signos_alerta"
                              rows="3"
                              placeholder="Ej: Vómitos persistentes, sangrado activo, dificultad respiratoria, fiebre >39.5°C"></textarea>
                    @error('signos_alerta')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-12 mb-3">
                    <label class="form-label">Instrucciones de Emergencia</label>
                    <textarea class="form-control"
                              wire:model.defer="instrucciones_emergencia"
                              rows="2"
                              placeholder="Qué hacer en caso de emergencia, números de contacto"></textarea>
                    @error('instrucciones_emergencia')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Prevención y Seguimiento -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <h6 class="border-bottom pb-2">
                        <i class="ri-shield-check-line text-warning me-2"></i>
                        Prevención y Seguimiento
                    </h6>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Próxima Cita de Control</label>
                    <input type="date"
                           class="form-control"
                           wire:model.defer="proxima_cita_control">
                    @error('proxima_cita_control')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <div class="form-check form-switch mt-4">
                        <input class="form-check-input"
                               type="checkbox"
                               id="vacunacion_pendiente"
                               wire:model.defer="vacunacion_pendiente">
                        <label class="form-check-label" for="vacunacion_pendiente">
                            <strong>Vacunación Pendiente</strong>
                        </label>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input"
                               type="checkbox"
                               id="desparasitacion_pendiente"
                               wire:model.defer="desparasitacion_pendiente">
                        <label class="form-check-label" for="desparasitacion_pendiente">
                            <strong>Desparasitación Pendiente</strong>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Recomendaciones Generales -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <h6 class="border-bottom pb-2">
                        <i class="ri-book-open-line text-warning me-2"></i>
                        Recomendaciones Generales
                    </h6>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Recomendaciones Nutricionales</label>
                    <textarea class="form-control"
                              wire:model.defer="recomendaciones_nutricion"
                              rows="2"
                              placeholder="Consejos sobre alimentación, suplementos"></textarea>
                    @error('recomendaciones_nutricion')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Recomendaciones de Higiene</label>
                    <textarea class="form-control"
                              wire:model.defer="recomendaciones_higiene"
                              rows="2"
                              placeholder="Baño, cepillado, limpieza dental, etc."></textarea>
                    @error('recomendaciones_higiene')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Capacitación al Propietario -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <h6 class="border-bottom pb-2">
                        <i class="ri-graduation-cap-line text-warning me-2"></i>
                        Educación al Propietario
                    </h6>
                </div>

                <div class="col-md-12 mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input"
                               type="checkbox"
                               id="propietario_capacitado"
                               wire:model.defer="propietario_capacitado">
                        <label class="form-check-label" for="propietario_capacitado">
                            <strong>Propietario capacitado en cuidados básicos</strong>
                        </label>
                    </div>
                </div>

                <div class="col-md-12 mb-3">
                    <label class="form-label">Material Educativo Entregado</label>
                    <input type="text"
                           class="form-control"
                           wire:model.defer="material_entregado"
                           placeholder="Ej: Folleto de cuidados post-operatorios, guía nutricional">
                    @error('material_entregado')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Observaciones de Alta -->
            <div class="mb-3">
                <label class="form-label">Observaciones Adicionales de Alta</label>
                <textarea class="form-control"
                          wire:model.defer="observaciones_alta"
                          rows="2"
                          placeholder="Notas adicionales importantes para el propietario"></textarea>
                @error('observaciones_alta')
                    <span class="text-danger small">{{ $message }}</span>
                @enderror
            </div>

            <!-- Checklist de alta -->
            <div class="alert alert-warning" role="alert">
                <h6 class="alert-heading">
                    <i class="ri-checkbox-multiple-line me-2"></i>
                    Checklist antes del Alta
                </h6>
                <ul class="mb-0">
                    <li>✓ Propietario entiende el diagnóstico y tratamiento</li>
                    <li>✓ Se entregaron instrucciones escritas claras</li>
                    <li>✓ Propietario conoce signos de alerta</li>
                    <li>✓ Se programó cita de control (si es necesario)</li>
                    <li>✓ Se resolvieron todas las dudas del propietario</li>
                </ul>
            </div>

            <!-- Botones -->
            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('admin.gestion.consultas.educacion-propietario') }}"
                   class="btn btn-secondary">
                    <i class="ri-close-line me-1"></i>
                    Cancelar
                </a>
                <button type="submit"
                        class="btn btn-warning text-dark"
                        wire:loading.attr="disabled">
                    <span wire:loading.remove>
                        <i class="ri-save-line me-1"></i>
                        Guardar y Dar Alta
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
