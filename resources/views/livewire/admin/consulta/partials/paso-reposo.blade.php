{{-- Reposo Médico --}}
<div>
    <h6 class="mb-3">
        <i class="ri ri-hotel-bed-line me-2 text-primary"></i>
        Reposo Médico
    </h6>

    <div class="card">
        <div class="card-body">
            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox"
                    id="requiere_reposo"
                    wire:model.live="requiere_reposo">
                <label class="form-check-label" for="requiere_reposo">
                    ¿Requiere reposo médico?
                </label>
            </div>

            @if($requiere_reposo)
            <div class="row g-3">
                <div class="col-12">
                    <div class="form-floating form-floating-outline">
                        <textarea class="form-control" id="motivo_reposo"
                            wire:model="motivo_reposo"
                            style="height: 80px"
                            placeholder="Motivo del reposo"></textarea>
                        <label for="motivo_reposo">
                            Motivo del Reposo
                            <span class="text-danger">*</span>
                        </label>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-floating form-floating-outline">
                        <input type="number" class="form-control" id="dias_reposo"
                            wire:model="dias_reposo" placeholder="Días" min="1" max="365">
                        <label for="dias_reposo">
                            Días de Reposo
                            <span class="text-danger">*</span>
                        </label>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-floating form-floating-outline">
                        <input type="date" class="form-control" id="fecha_inicio_reposo"
                            wire:model="fecha_inicio_reposo">
                        <label for="fecha_inicio_reposo">
                            Fecha de Inicio
                            <span class="text-danger">*</span>
                        </label>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-floating form-floating-outline">
                        <input type="date" class="form-control" id="fecha_fin_reposo"
                            wire:model="fecha_fin_reposo" readonly>
                        <label for="fecha_fin_reposo">Fecha de Fin</label>
                    </div>
                </div>

                <div class="col-12">
                    <div class="form-floating form-floating-outline">
                        <textarea class="form-control" id="observaciones_reposo"
                            wire:model="observaciones_reposo"
                            style="height: 80px"
                            placeholder="Observaciones adicionales"></textarea>
                        <label for="observaciones_reposo">Observaciones Adicionales</label>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between mt-3">
                <div>
                    @if($pasoActualIndex > 0)
                    <button class="btn btn-outline-secondary" wire:click="irPaso({{ $pasoActualIndex - 1 }})">
                        <i class="ri ri-arrow-left-line me-1"></i>Anterior
                    </button>
                    @endif
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-primary" wire:click="guardarReposo">
                        <i class="ri ri-save-line me-1"></i>Guardar Reposo
                    </button>
                    @if($esUltimoPaso)
                    <button class="btn btn-warning" wire:click="finalizarConsulta" wire:confirm="¿Está seguro de finalizar la consulta?">
                        <i class="ri ri-check-double-line me-1"></i>Finalizar Consulta
                    </button>
                    @endif
                </div>
            </div>
            @else
            <div class="alert alert-info">
                <i class="ri ri-information-line me-2"></i>
                Active el interruptor para indicar que el paciente requiere reposo médico.
            </div>
             <div class="d-flex justify-content-between mt-3">
                <div>
                    @if($pasoActualIndex > 0)
                    <button class="btn btn-outline-secondary" wire:click="irPaso({{ $pasoActualIndex - 1 }})">
                        <i class="ri ri-arrow-left-line me-1"></i>Anterior
                    </button>
                    @endif
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-primary" wire:click="guardarReposo">
                        <i class="ri ri-save-line me-1"></i>Guardar Reposo
                    </button>
                    @if($esUltimoPaso)
                    <button class="btn btn-warning" wire:click="finalizarConsulta" wire:confirm="¿Está seguro de finalizar la consulta?">
                        <i class="ri ri-check-double-line me-1"></i>Finalizar Consulta
                    </button>
                    @endif
                </div>
            </div>
            @endif

        </div>
    </div>
</div>
