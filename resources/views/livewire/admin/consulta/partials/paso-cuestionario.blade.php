{{-- Cuestionario --}}
<div>
    <h6 class="mb-3">
        <i class="ri ri-questionnaire-line me-2 text-primary"></i>
        Cuestionario Pre-consulta
    </h6>

    @if(count($preguntas_cuestionario) > 0)
        <div class="alert alert-info mb-3">
            <i class="ri ri-information-line me-2"></i>
            Complete las preguntas del cuestionario. Las respuestas se guardan automáticamente.
        </div>

        @foreach($preguntas_cuestionario as $pregunta)
        <div class="card mb-3">
            <div class="card-body">
                <h6 class="card-title">
                    {{ $pregunta['titulo'] }}
                    @if($pregunta['obligatorio'])
                        <span class="text-danger">*</span>
                    @endif
                </h6>

                @php
                    $respuesta = $respuestasPreconsulta->firstWhere('pregunta_id', $pregunta['id']);
                @endphp

                @if($pregunta['tipo'] === 'texto_largo')
                    <div class="form-floating form-floating-outline mt-2">
                        <textarea class="form-control"
                            wire:model.blur="cuestionario_respuesta_{{ $pregunta['id'] }}"
                            style="height: 100px"
                            placeholder="Su respuesta"
                            value="{{ $respuesta?->respuesta ?? '' }}"></textarea>
                        <label>Su respuesta</label>
                    </div>
                    <button class="btn btn-sm btn-primary mt-2"
                        wire:click="guardarRespuestaCuestionario({{ $pregunta['id'] }}, '{{ addslashes($respuesta?->respuesta ?? '') }}')">
                        <i class="ri ri-save-line me-1"></i>Guardar
                    </button>

                @elseif($pregunta['tipo'] === 'seleccion_multiple')
                    <div class="mt-2">
                        @foreach(($pregunta['opciones'] ?? []) as $opcion)
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox"
                                value="{{ $opcion }}"
                                wire:click="toggleRespuestaMultiple({{ $pregunta['id'] }}, '{{ $opcion }}')"
                                {{ in_array($opcion, $respuesta?->respuesta_multiple ?? []) ? 'checked' : '' }}>
                            <label class="form-check-label">{{ $opcion }}</label>
                        </div>
                        @endforeach
                    </div>

                @elseif($pregunta['tipo'] === 'booleano')
                    <div class="mt-2">
                        <div class="btn-group" role="group">
                            <input type="radio" class="btn-check"
                                name="pregunta_{{ $pregunta['id'] }}"
                                id="pregunta_{{ $pregunta['id'] }}_si"
                                value="si"
                                wire:click="guardarRespuestaCuestionario({{ $pregunta['id'] }}, 'si')"
                                {{ ($respuesta?->respuesta ?? '') === 'si' ? 'checked' : '' }}>
                            <label class="btn btn-outline-success" for="pregunta_{{ $pregunta['id'] }}_si">
                                <i class="ri ri-check-line me-1"></i>Sí
                            </label>

                            <input type="radio" class="btn-check"
                                name="pregunta_{{ $pregunta['id'] }}"
                                id="pregunta_{{ $pregunta['id'] }}_no"
                                value="no"
                                wire:click="guardarRespuestaCuestionario({{ $pregunta['id'] }}, 'no')"
                                {{ ($respuesta?->respuesta ?? '') === 'no' ? 'checked' : '' }}>
                            <label class="btn btn-outline-danger" for="pregunta_{{ $pregunta['id'] }}_no">
                                <i class="ri ri-close-line me-1"></i>No
                            </label>
                        </div>
                    </div>

                @else
                    <div class="form-floating form-floating-outline mt-2">
                        <input type="text" class="form-control"
                            wire:model.blur="cuestionario_respuesta_{{ $pregunta['id'] }}"
                            placeholder="Su respuesta"
                            value="{{ $respuesta?->respuesta ?? '' }}">
                        <label>Su respuesta</label>
                    </div>
                    <button class="btn btn-sm btn-primary mt-2"
                        wire:click="guardarRespuestaCuestionario({{ $pregunta['id'] }}, '{{ addslashes($respuesta?->respuesta ?? '') }}')">
                        <i class="ri ri-save-line me-1"></i>Guardar
                    </button>
                @endif


            </div>
        </div>
        @endforeach

        @if($cuestionarioCompleto)
        <div class="alert alert-success">
            <i class="ri ri-checkbox-circle-line me-2"></i>
            ¡Cuestionario completado! Todas las preguntas obligatorias han sido respondidas.
        </div>
        @endif
    @else
        <div class="alert alert-warning">
            <i class="ri ri-warning-line me-2"></i>
            No hay cuestionario configurado para esta especialidad.
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
