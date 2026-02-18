<div class="modal fade" id="registrarSignosVitalesModal{{ $consultaId }}" tabindex="-1" aria-hidden="true" wire:ignore.self>
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Registrar Signos Vitales</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Paciente:</strong> {{ $paciente->nombre_completo }}
                    </div>
                    <div class="col-md-6">
                        <strong>Especialidad:</strong> {{ $especialidad->nombre ?? 'Sin especialidad' }}
                    </div>
                </div>

                <form wire:submit.prevent="guardar">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-floating form-floating-outline">
                                <input type="number" step="0.1" class="form-control @error('presion_arterial_sistolica') is-invalid @enderror" 
                                       id="presion_arterial_sistolica" wire:model="presion_arterial_sistolica" placeholder="120">
                                <label for="presion_arterial_sistolica">Presión Arterial Sistólica (mmHg)</label>
                                @error('presion_arterial_sistolica') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <div class="form-floating form-floating-outline">
                                <input type="number" step="0.1" class="form-control @error('presion_arterial_diastolica') is-invalid @enderror" 
                                       id="presion_arterial_diastolica" wire:model="presion_arterial_diastolica" placeholder="80">
                                <label for="presion_arterial_diastolica">Presión Arterial Diastólica (mmHg)</label>
                                @error('presion_arterial_diastolica') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-floating form-floating-outline">
                                <input type="number" class="form-control @error('frecuencia_cardiaca') is-invalid @enderror" 
                                       id="frecuencia_cardiaca" wire:model="frecuencia_cardiaca" placeholder="72">
                                <label for="frecuencia_cardiaca">Frecuencia Cardíaca (lpm)</label>
                                @error('frecuencia_cardiaca') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <div class="form-floating form-floating-outline">
                                <input type="number" class="form-control @error('frecuencia_respiratoria') is-invalid @enderror" 
                                       id="frecuencia_respiratoria" wire:model="frecuencia_respiratoria" placeholder="16">
                                <label for="frecuencia_respiratoria">Frecuencia Respiratoria (rpm)</label>
                                @error('frecuencia_respiratoria') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-floating form-floating-outline">
                                <input type="number" step="0.1" class="form-control @error('temperatura') is-invalid @enderror" 
                                       id="temperatura" wire:model="temperatura" placeholder="36.5">
                                <label for="temperatura">Temperatura (°C)</label>
                                @error('temperatura') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <div class="form-floating form-floating-outline">
                                <input type="number" step="0.1" class="form-control @error('saturacion_oxigeno') is-invalid @enderror" 
                                       id="saturacion_oxigeno" wire:model="saturacion_oxigeno" placeholder="98">
                                <label for="saturacion_oxigeno">Saturación de Oxígeno (%)</label>
                                @error('saturacion_oxigeno') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-floating form-floating-outline">
                                <input type="number" step="0.01" class="form-control @error('peso') is-invalid @enderror" 
                                       id="peso" wire:model="peso" placeholder="70.5" wire:input="calcularIMC">
                                <label for="peso">Peso (kg)</label>
                                @error('peso') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <div class="form-floating form-floating-outline">
                                <input type="number" step="0.1" class="form-control @error('talla') is-invalid @enderror" 
                                       id="talla" wire:model="talla" placeholder="170.0" wire:input="calcularIMC">
                                <label for="talla">Talla (cm)</label>
                                @error('talla') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                        </div>
                    </div>

                    @if($imc)
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="alert alert-info">
                                <strong>IMC:</strong> {{ $imc }} kg/m²
                                @if($imc < 18.5)
                                    <span class="badge bg-warning">Bajo peso</span>
                                @elseif($imc < 25)
                                    <span class="badge bg-success">Normal</span>
                                @elseif($imc < 30)
                                    <span class="badge bg-warning">Sobrepeso</span>
                                @else
                                    <span class="badge bg-danger">Obesidad</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <div class="form-floating form-floating-outline">
                                <textarea class="form-control @error('observaciones') is-invalid @enderror" 
                                          id="observaciones" wire:model="observaciones" placeholder="Observaciones" rows="3"></textarea>
                                <label for="observaciones">Observaciones</label>
                                @error('observaciones') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" wire:click="guardar">
                    <i class="ri ri-save-line me-1"></i> Guardar Signos Vitales
                </button>
            </div>
        </div>
    </div>
</div>