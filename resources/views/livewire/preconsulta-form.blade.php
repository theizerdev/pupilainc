<div>
    <div class="container py-4">
        {{-- Encabezado --}}
        <div class="row mb-4">
            <div class="col-12">
                <div class="card bg-gradient-primary shadow-primary">
                    <div class="card-body text-center text-white">
                        <h2 class="mb-0">📋 Cuestionario Pre-consulta</h2>
                        <p class="mb-0">{{ $empresa->razon_social ?? config('app.name') }}</p>
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('cerrar-ventana', () => {
                setTimeout(() => {
                    window.close();
                }, 3000);
            });
        });
    </script>
</div>
        </div>

        {{-- Información del Paciente --}}
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h5 class="mb-1">{{ $paciente->nombres }} {{ $paciente->apellidos }}</h5>
                                <p class="text-muted mb-0">
                                    <i class="fas fa-id-card"></i> {{ $paciente->documento_identidad ?? 'Sin documento' }}
                                    <span class="mx-2">|</span>
                                    <i class="fas fa-phone"></i> {{ $paciente->telefono ?? 'Sin teléfono' }}
                                </p>
                            </div>
                            <div class="col-md-4 text-end">
                                <div class="progress mb-2" style="height: 8px;">
                                    <div class="progress-bar bg-primary"
                                         role="progressbar"
                                         style="width: {{ ($paso / $totalPasos) * 100 }}%"
                                         aria-valuenow="{{ $paso }}"
                                         aria-valuemin="1"
                                         aria-valuemax="{{ $totalPasos }}">
                                    </div>
                                </div>
                                <small class="text-muted">Paso {{ $paso }} de {{ $totalPasos }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Alertas --}}
        @if (session()->has('success'))
            <div class="row mb-4">
                <div class="col-12">
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <strong>✅ ¡Éxito!</strong> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            </div>
        @endif

        {{-- Cuestionario Completado --}}
        @if($respuestasPreconsulta->first()->completado)
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card bg-success text-white text-center">
                        <div class="card-body">
                            <div class="mb-3">
                                <i class="fas fa-check-circle fa-3x"></i>
                            </div>
                            <h4>¡Cuestionario Completado!</h4>
                            <p class="mb-0">Gracias por completar el cuestionario. Su información ha sido registrada exitosamente.</p>
                            <small class="d-block mt-2">Puede cerrar esta ventana.</small>
                        </div>
                    </div>
                </div>
            </div>
        @else
            {{-- Formulario --}}
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">{{ $cuestionario->titulo }}</h5>
                            @if($cuestionario->descripcion)
                                <small>{{ $cuestionario->descripcion }}</small>
                            @endif
                        </div>
                        <div class="card-body">
                            <form wire:submit.prevent="finalizarCuestionario">
                                @foreach($preguntasActuales as $respuesta)
                                    <div class="mb-4" wire:key="pregunta_{{ $respuesta->id }}">
                                        <label class="form-label fw-bold">
                                            {{ $respuesta->pregunta->titulo }}
                                            @if($respuesta->pregunta->obligatorio)
                                                <span class="text-danger">*</span>
                                            @endif
                                        </label>

                                        @if($respuesta->pregunta->descripcion)
                                            <small class="form-text text-muted d-block mb-2">{{ $respuesta->pregunta->descripcion }}</small>
                                        @endif

                                        @switch($respuesta->pregunta->tipo)
                                            @case('texto')
                                                <textarea
                                                    class="form-control @error('respuestas.' . $respuesta->id) is-invalid @enderror"
                                                    rows="3"
                                                    placeholder="Escriba su respuesta..."
                                                    wire:model="respuestas.{{ $respuesta->id }}"
                                                    @if($respuesta->pregunta->obligatorio) required @endif
                                                ></textarea>
                                                @break

                                            @case('si_no')
                                                <div class="btn-group" role="group">
                                                    <input type="radio"
                                                           class="btn-check"
                                                           name="respuesta_{{ $respuesta->id }}"
                                                           id="si_{{ $respuesta->id }}"
                                                           value="Sí"
                                                           wire:model="respuestas.{{ $respuesta->id }}"
                                                           @if($respuesta->pregunta->obligatorio) required @endif>
                                                    <label class="btn btn-outline-primary" for="si_{{ $respuesta->id }}">Sí</label>

                                                    <input type="radio"
                                                           class="btn-check"
                                                           name="respuesta_{{ $respuesta->id }}"
                                                           id="no_{{ $respuesta->id }}"
                                                           value="No"
                                                           wire:model="respuestas.{{ $respuesta->id }}"
                                                           @if($respuesta->pregunta->obligatorio) required @endif>
                                                    <label class="btn btn-outline-primary" for="no_{{ $respuesta->id }}">No</label>
                                                </div>
                                                @break

                                            @case('opcion')
                                                <select
                                                    class="form-select @error('respuestas.' . $respuesta->id) is-invalid @enderror"
                                                    wire:model="respuestas.{{ $respuesta->id }}"
                                                    @if($respuesta->pregunta->obligatorio) required @endif
                                                >
                                                    <option value="">Seleccione una opción...</option>
                                                    @foreach($respuesta->pregunta->opciones ?? [] as $opcion)
                                                        <option value="{{ $opcion }}">{{ $opcion }}</option>
                                                    @endforeach
                                                </select>
                                                @break

                                            @case('multiple')
                                                <div class="border rounded p-3">
                                                    @foreach($respuesta->pregunta->opciones ?? [] as $opcion)
                                                        <div class="form-check mb-2" wire:key="opcion_{{ $respuesta->id }}_{{ $loop->index }}">
                                                            <input class="form-check-input"
                                                                   type="checkbox"
                                                                   id="opcion_{{ $respuesta->id }}_{{ $loop->index }}"
                                                                   value="{{ $opcion }}"
                                                                   wire:model.live="respuestas.{{ $respuesta->id }}">
                                                            <label class="form-check-label" for="opcion_{{ $respuesta->id }}_{{ $loop->index }}">
                                                                {{ $opcion }}
                                                            </label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                                @break

                                            @case('escala')
                                                <div class="d-flex justify-content-between align-items-center">
                                                    @foreach(range(1, 10) as $valor)
                                                        <div class="text-center">
                                                            <input type="radio"
                                                                   class="btn-check"
                                                                   name="respuesta_{{ $respuesta->id }}"
                                                                   id="escala_{{ $respuesta->id }}_{{ $valor }}"
                                                                   value="{{ $valor }}"
                                                                   wire:model="respuestas.{{ $respuesta->id }}"
                                                                   @if($respuesta->pregunta->obligatorio && $valor === 1) required @endif>
                                                            <label class="btn btn-outline-primary btn-sm" for="escala_{{ $respuesta->id }}_{{ $valor }}">
                                                                {{ $valor }}
                                                            </label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                                <div class="d-flex justify-content-between mt-2">
                                                    <small class="text-muted">Muy bajo</small>
                                                    <small class="text-muted">Muy alto</small>
                                                </div>
                                                @break

                                            @default
                                                <input type="text"
                                                       class="form-control @error('respuestas.' . $respuesta->id) is-invalid @enderror"
                                                       placeholder="Escriba su respuesta..."
                                                       wire:model="respuestas.{{ $respuesta->id }}"
                                                       @if($respuesta->pregunta->obligatorio) required @endif>
                                        @endswitch

                                        @error('respuestas.' . $respuesta->id)
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                @endforeach

                                {{-- Navegación --}}
                                <div class="d-flex justify-content-between align-items-center mt-4">
                                    @if($paso > 1)
                                        <button type="button" class="btn btn-secondary" wire:click="pasoAnterior">
                                            <i class="fas fa-arrow-left"></i> Anterior
                                        </button>
                                    @else
                                        <div></div>
                                    @endif

                                    @if($paso < $totalPasos)
                                        <button type="button" class="btn btn-primary" wire:click="siguientePaso">
                                            Siguiente <i class="fas fa-arrow-right"></i>
                                        </button>
                                    @else
                                        <button type="submit" class="btn btn-success" wire:loading.attr="disabled">
                                            <span wire:loading.remove>
                                                <i class="fas fa-check"></i> Finalizar Cuestionario
                                            </span>
                                            <span wire:loading>
                                                <i class="fas fa-spinner fa-spin"></i> Guardando...
                                            </span>
                                        </button>
                                    @endif
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
