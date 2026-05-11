{{-- Signos Vitales --}}
<div>
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="vital-card card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <div class="vital-label text-muted">Presión Arterial</div>
                            <div class="vital-value">
                                {{ $presion_sistolica ?? '--' }}/{{ $presion_diastolica ?? '--' }}
                            </div>
                        </div>
                        <i class="ri ri-heart-pulse-line ri-2x text-danger"></i>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="form-floating form-floating-outline">
                                <input type="number" class="form-control" id="presion_sistolica"
                                    wire:model.blur="presion_sistolica" placeholder="Sistólica" min="70" max="250">
                                <label for="presion_sistolica">Sistólica</label>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-floating form-floating-outline">
                                <input type="number" class="form-control" id="presion_diastolica"
                                    wire:model.blur="presion_diastolica" placeholder="Diastólica" min="40" max="150">
                                <label for="presion_diastolica">Diastólica</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="vital-card card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <div class="vital-label text-muted">Frecuencia Cardíaca</div>
                            <div class="vital-value">{{ $frecuencia_cardiaca ?? '--' }} <small>bpm</small></div>
                        </div>
                        <i class="ri ri-heart-line ri-2x text-primary"></i>
                    </div>
                    <div class="form-floating form-floating-outline">
                        <input type="number" class="form-control" id="frecuencia_cardiaca"
                            wire:model.blur="frecuencia_cardiaca" placeholder="Frecuencia" min="30" max="250">
                        <label for="frecuencia_cardiaca">Frecuencia Cardíaca</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="vital-card card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <div class="vital-label text-muted">Temperatura</div>
                            <div class="vital-value">{{ $temperatura ?? '--' }} <small>°C</small></div>
                        </div>
                        <i class="ri ri-temp-hot-line ri-2x text-warning"></i>
                    </div>
                    <div class="form-floating form-floating-outline">
                        <input type="number" step="0.1" class="form-control" id="temperatura"
                            wire:model.blur="temperatura" placeholder="Temperatura" min="34" max="42">
                        <label for="temperatura">Temperatura (°C)</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="vital-card card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <div class="vital-label text-muted">Frecuencia Respiratoria</div>
                            <div class="vital-value">{{ $frecuencia_respiratoria ?? '--' }} <small>rpm</small></div>
                        </div>
                        <i class="ri ri-lungs-line ri-2x text-info"></i>
                    </div>
                    <div class="form-floating form-floating-outline">
                        <input type="number" class="form-control" id="frecuencia_respiratoria"
                            wire:model.blur="frecuencia_respiratoria" placeholder="Frecuencia" min="8" max="60">
                        <label for="frecuencia_respiratoria">Frecuencia Resp.</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="vital-card card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <div class="vital-label text-muted">Saturación O₂</div>
                            <div class="vital-value">{{ $saturacion_oxigeno ?? '--' }} <small>%</small></div>
                        </div>
                        <i class="ri ri-drop-line ri-2x text-success"></i>
                    </div>
                    <div class="form-floating form-floating-outline">
                        <input type="number" class="form-control" id="saturacion_oxigeno"
                            wire:model.blur="saturacion_oxigeno" placeholder="Saturación" min="50" max="100">
                        <label for="saturacion_oxigeno">Saturación O₂ (%)</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="vital-card card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <div class="vital-label text-muted">Peso</div>
                            <div class="vital-value">{{ $peso ?? '--' }} <small>kg</small></div>
                        </div>
                        <i class="ri ri-weight-line ri-2x text-secondary"></i>
                    </div>
                    <div class="form-floating form-floating-outline">
                        <input type="number" step="0.1" class="form-control" id="peso"
                            wire:model.blur="peso" placeholder="Peso" min="1" max="300">
                        <label for="peso">Peso (kg)</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="vital-card card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <div class="vital-label text-muted">Talla</div>
                            <div class="vital-value">{{ $talla ?? '--' }} <small>cm</small></div>
                        </div>
                        <i class="ri ri-ruler-line ri-2x text-secondary"></i>
                    </div>
                    <div class="form-floating form-floating-outline">
                        <input type="number" step="0.1" class="form-control" id="talla"
                            wire:model.blur="talla" placeholder="Talla" min="30" max="250">
                        <label for="talla">Talla (cm)</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="vital-card card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <div class="vital-label text-muted">IMC</div>
                            <div class="vital-value">{{ $imc_calculado ?? '--' }}</div>
                        </div>
                        <i class="ri ri-calculator-line ri-2x text-primary"></i>
                    </div>
                    @if($imc_calculado)
                    <div class="badge bg-{{ $imc_calculado < 18.5 ? 'warning' : ($imc_calculado < 25 ? 'success' : ($imc_calculado < 30 ? 'warning' : 'danger')) }}">
                        @if($imc_calculado < 18.5) Bajo peso
                        @elseif($imc_calculado < 25) Normal
                        @elseif($imc_calculado < 30) Sobrepeso
                        @else Obesidad
                        @endif
                    </div>
                    @else
                    <span class="text-muted small">Requiere peso y talla</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Observaciones --}}
    <div class="form-floating form-floating-outline mb-3">
        <textarea class="form-control" id="observaciones_signos"
            wire:model.blur="observaciones_signos" style="height: 100px"
            placeholder="Observaciones"></textarea>
        <label for="observaciones_signos">Observaciones</label>
    </div>



    {{-- Historial --}}
    @if(count($historial_signos) > 0)
    <div class="mt-4">
        <h6 class="mb-3"><i class="ri ri-history-line me-2"></i>Historial de Signos Vitales</h6>
        <div class="row g-3">
            <div class="col-md-4">
                <div id="chartTemperatura"></div>
            </div>
            <div class="col-md-4">
                <div id="chartPresion"></div>
            </div>
            <div class="col-md-4">
                <div id="chartIMC"></div>
            </div>
        </div>
    </div>
    @endif

    {{-- Navegación --}}
    <div class="d-flex justify-content-between mt-4 pt-3 border-top">
        <div>
            @if($pasoActualIndex > 0)
            <button class="btn btn-outline-secondary" wire:click="irPaso({{ $pasoActualIndex - 1 }})">
                <i class="ri ri-arrow-left-line me-1"></i>Anterior
            </button>
            @endif
        </div>
        <div class="d-flex gap-2">
            @if($haySiguientePaso)
            <button class="btn btn-success" wire:click="siguientePaso">
                <i class="ri ri-save-line me-1"></i>Guardar y Siguiente
                <span class="badge bg-light text-success ms-1">{{ $pasoSiguiente['nombre'] ?? '' }}</span>
            </button>
            @endif
        </div>
    </div>
</div>
