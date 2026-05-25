{{-- Evaluación Clínica --}}
<div>
    <h6 class="mb-3">
        <i class="ri ri-file-list-3-line me-2 text-primary"></i>
        Evaluación Clínica
    </h6>

    {{-- Diagnósticos --}}
    <div class="card mb-3">
        <div class="card-header bg-transparent">
            <h6 class="mb-0">
                <i class="ri ri-heart-pulse-line me-2 text-danger"></i>
                Diagnósticos
            </h6>
        </div>
        <div class="card-body">
            {{-- Búsqueda de diagnóstico --}}
            <div class="row g-2 mb-3">
                <div class="col-md-8">
                    <div class="form-floating form-floating-outline">
                        <input type="text" class="form-control"
                            wire:model.live="busqueda_diagnostico"
                            placeholder="Buscar diagnóstico...">
                        <label>Buscar diagnóstico (CIE-10)</label>
                    </div>
                    @if(count($diagnosticos_disponibles) > 0)
                    <div class="list-group mt-2">
                        @foreach($diagnosticos_disponibles as $diag)
                        <button class="list-group-item list-group-item-action"
                            wire:click="agregarDiagnostico({{ $diag['id'] }}, 'secundario')">
                            <strong>{{ $diag['codigo'] }}</strong> - {{ $diag['nombre'] }}
                        </button>
                        @endforeach
                    </div>
                    @endif
                </div>
                <div class="col-md-4">
                    <button class="btn btn-outline-primary w-100"
                        wire:click="$set('mostrar_form_nuevo', true)">
                        <i class="ri ri-add-line me-1"></i>Nuevo Diagnóstico
                    </button>
                </div>
            </div>

            {{-- Formulario nuevo diagnóstico --}}
            @if($mostrar_form_nuevo)
            <div class="card bg-light mb-3">
                <div class="card-body">
                    <h6 class="mb-3">Crear Nuevo Diagnóstico</h6>
                    <div class="row g-2">
                        <div class="col-md-4">
                            <input type="text" class="form-control"
                                wire:model="nuevo_diagnostico_codigo"
                                placeholder="Código (ej: J00)">
                        </div>
                        <div class="col-md-6">
                            <input type="text" class="form-control"
                                wire:model="nuevo_diagnostico_nombre"
                                placeholder="Nombre del diagnóstico">
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-success w-100"
                                wire:click="crearYAgregarDiagnostico">
                                <i class="ri ri-check-line"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- Lista de diagnósticos agregados --}}
            @if(count($diagnosticos_agregados) > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Diagnóstico</th>
                            <th>Tipo</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($diagnosticos_agregados as $index => $diag)
                        <tr>
                            <td><strong>{{ $diag['codigo'] }}</strong></td>
                            <td>{{ $diag['nombre'] }}</td>
                            <td>
                                <select class="form-select form-select-sm"
                                    wire:change="cambiarTipoDiagnostico({{ $index }}, $event.target.value)">
                                    <option value="principal" {{ $diag['tipo'] === 'principal' ? 'selected' : '' }}>Principal</option>
                                    <option value="secundario" {{ $diag['tipo'] === 'secundario' ? 'selected' : '' }}>Secundario</option>
                                </select>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-danger"
                                    wire:click="eliminarDiagnostico({{ $index }})">
                                    <i class="ri ri-delete-bin-line"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="alert alert-info">
                <i class="ri ri-information-line me-2"></i>
                No se han agregado diagnósticos aún.
            </div>
            @endif
        </div>
    </div>

    {{-- Campos dinámicos de la evaluación --}}
    @if(count($seccionesEvaluacion ?? $secciones) > 0)
    <div class="card mb-3">
        <div class="card-header bg-transparent">
            <h6 class="mb-0">
                <i class="ri ri-stethoscope-line me-2 text-primary"></i>
                Campos de Evaluación
            </h6>
        </div>
        <div class="card-body">
            @foreach($seccionesEvaluacion ?? $secciones as $seccion)
            <div class="mb-4">
                <div class="seccion-header" style="--seccion-color: {{ $seccion['color'] ?? '#3B82F6' }}">
                    <h6 class="mb-0">
                        @if($seccion['icono'])
                            <i class="{{ $seccion['icono'] }} me-2" style="color:{{ $seccion['color'] ?? '#3B82F6' }}"></i>
                        @endif
                        {{ $seccion['nombre'] }}
                    </h6>
                </div>
                <div class="row g-3">
                    @foreach($seccion['campos'] as $campo)
                    <div class="col-md-{{ $campo['ancho_columnas'] ?? 6 }}">
                        @php $fieldName = "datos_dinamicos.{$campo['nombre_campo']}"; @endphp

                        @if($campo['tipo'] === 'text')
                        <div class="form-floating form-floating-outline">
                            <input type="text" class="form-control"
                                wire:model.blur="{{ $fieldName }}"
                                placeholder="{{ $campo['placeholder'] ?? $campo['etiqueta'] }}">
                            <label>
                                {{ $campo['etiqueta'] }}
                                @if($campo['unidad']) <small class="text-muted">({{ $campo['unidad'] }})</small> @endif
                                @if($campo['obligatorio']) <span class="text-danger">*</span> @endif
                            </label>
                        </div>

                        @elseif($campo['tipo'] === 'number')
                        <div class="form-floating form-floating-outline">
                            <input type="number" class="form-control"
                                wire:model.blur="{{ $fieldName }}"
                                @if($campo['min'] !== null) min="{{ $campo['min'] }}" @endif
                                @if($campo['max'] !== null) max="{{ $campo['max'] }}" @endif>
                            <label>
                                {{ $campo['etiqueta'] }}
                                @if($campo['unidad']) <small class="text-muted">({{ $campo['unidad'] }})</small> @endif
                                @if($campo['obligatorio']) <span class="text-danger">*</span> @endif
                            </label>
                        </div>

                        @elseif($campo['tipo'] === 'textarea')
                        <div class="form-floating form-floating-outline">
                            <textarea class="form-control"
                                wire:model.blur="{{ $fieldName }}"
                                style="height:100px"
                                placeholder="{{ $campo['placeholder'] ?? '' }}"></textarea>
                            <label>
                                {{ $campo['etiqueta'] }}
                                @if($campo['obligatorio']) <span class="text-danger">*</span> @endif
                            </label>
                        </div>

                        @elseif($campo['tipo'] === 'select')
                        <div class="form-floating form-floating-outline">
                            <select class="form-select"
                                wire:model="{{ $fieldName }}">
                                <option value="">— Seleccionar —</option>
                                @foreach(($campo['opciones'] ?? []) as $op)
                                    <option value="{{ $op }}">{{ $op }}</option>
                                @endforeach
                            </select>
                            <label>
                                {{ $campo['etiqueta'] }}
                                @if($campo['obligatorio']) <span class="text-danger">*</span> @endif
                            </label>
                        </div>

                        @elseif($campo['tipo'] === 'radio')
                        <div>
                            <label class="form-label small fw-semibold">
                                {{ $campo['etiqueta'] }}
                                @if($campo['obligatorio']) <span class="text-danger">*</span> @endif
                            </label>
                            @foreach(($campo['opciones'] ?? []) as $op)
                            <div class="form-check">
                                <input class="form-check-input" type="radio"
                                    name="eval_radio_{{ $campo['id'] }}"
                                    value="{{ $op }}"
                                    wire:model="{{ $fieldName }}">
                                <label class="form-check-label">{{ $op }}</label>
                            </div>
                            @endforeach
                        </div>

                        @elseif($campo['tipo'] === 'checkbox')
                        <div>
                            <label class="form-label small fw-semibold">
                                {{ $campo['etiqueta'] }}
                                @if($campo['obligatorio']) <span class="text-danger">*</span> @endif
                            </label>
                            @foreach(($campo['opciones'] ?? []) as $op)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox"
                                    value="{{ $op }}"
                                    wire:model="{{ $fieldName }}">
                                <label class="form-check-label">{{ $op }}</label>
                            </div>
                            @endforeach
                        </div>

                        @elseif($campo['tipo'] === 'range')
                        <div>
                            <label class="form-label small fw-semibold">
                                {{ $campo['etiqueta'] }}
                                @if($campo['obligatorio']) <span class="text-danger">*</span> @endif
                                <span class="badge bg-label-primary ms-2">
                                    {{ $datos_dinamicos[$campo['nombre_campo']] ?? ($campo['min'] ?? 0) }}
                                </span>
                            </label>
                            <input type="range" class="form-range"
                                min="{{ $campo['min'] ?? 0 }}"
                                max="{{ $campo['max'] ?? 10 }}"
                                wire:model="{{ $fieldName }}">
                            <div class="d-flex justify-content-between">
                                <small class="text-muted">{{ $campo['min'] ?? 0 }}</small>
                                <small class="text-muted">{{ $campo['max'] ?? 10 }}</small>
                            </div>
                        </div>

                        @elseif($campo['tipo'] === 'date')
                        <div class="form-floating form-floating-outline">
                            <input type="date" class="form-control"
                                wire:model="{{ $fieldName }}">
                            <label>
                                {{ $campo['etiqueta'] }}
                                @if($campo['obligatorio']) <span class="text-danger">*</span> @endif
                            </label>
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach

            <div class="d-flex justify-content-between mt-4 pt-3 border-top">
                <div>
                    @if($pasoActualIndex > 0)
                    <button class="btn btn-outline-secondary" wire:click="irPaso({{ $pasoActualIndex - 1 }})">
                        <i class="ri ri-arrow-left-line me-1"></i>Anterior
                    </button>
                    @endif
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-primary" wire:click="guardarEvaluacion">
                        <i class="ri ri-save-line me-1"></i>Guardar Evaluación
                    </button>
                    @if($haySiguientePaso)
                    <button class="btn btn-success" wire:click="siguientePaso">
                        <i class="ri ri-save-line me-1"></i>Guardar y Siguiente
                        <span class="badge bg-light text-success ms-1">{{ $pasoSiguiente['nombre'] ?? '' }}</span>
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
