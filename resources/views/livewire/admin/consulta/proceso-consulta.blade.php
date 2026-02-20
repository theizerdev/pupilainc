<div>
    @section('title', 'Proceso de Consulta')

    @push('styles')
    <link rel="stylesheet" href="/materialize/assets/vendor/libs/bs-stepper/bs-stepper.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/apexcharts@3.45.1/dist/apexcharts.css" />
    <style>

        .proceso-card { border-radius: 14px; }
        .proceso-step {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 2px solid var(--bs-border-color);
            background: var(--bs-body-bg);
            color: var(--bs-secondary);
            transition: all .2s ease;
            font-weight: 600;
        }
        .proceso-step.is-current {
            background: var(--bs-primary);
            border-color: var(--bs-primary);
            color: #fff;
            box-shadow: 0 0 0 6px rgba(var(--bs-primary-rgb), .12);
        }
        .proceso-step.is-done {
            background: var(--bs-success);
            border-color: var(--bs-success);
            color: #fff;
            cursor: pointer;
        }
        .proceso-step.is-done:hover {
            transform: translateY(-1px);
            box-shadow: 0 0 0 6px rgba(var(--bs-success-rgb), .12);
        }
        .proceso-step-label { font-size: .9rem; font-weight: 500; }
        .proceso-step-hint { font-size: .75rem; color: var(--bs-secondary-color); }
        .estudio-item, .tratamiento-item {
            border-left: 4px solid var(--bs-primary);
            transition: all .2s ease;
        }
        .estudio-item:hover, .tratamiento-item:hover {
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .vital-card { transition: all .2s ease; }
        .vital-card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .vital-value { font-size: 1.5rem; font-weight: 700; }
        .vital-label { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; }
        .status-normal { color: #28a745; }
        .status-warning { color: #ffc107; }
        .status-danger { color: #dc3545; }
    </style>
    @endpush

    <div class="proceso-shell">
        <!-- Header -->
        <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
            <div>
                <h4 class="mb-1">
                    <i class="ri ri-stethoscope-line me-2 text-primary"></i>Proceso de Consulta
                </h4>
                <div class="text-muted small">
                    <strong>Paciente:</strong> {{ $consulta->paciente->nombre_completo }} |
                    <strong>Médico:</strong> Dr(a). {{ $consulta->medico->nombre_completo }}
                </div>
            </div>
            <a href="{{ route('admin.gestion.consultas.index') }}" class="btn btn-outline-secondary">
                <i class="ri ri-arrow-left-line me-1"></i>Volver
            </a>
        </div>

        <!-- Progress Bar -->
        <div class="card border-0 shadow-sm proceso-card mb-4">
            <div class="card-body p-4">
                <div class="progress mb-4" style="height: 8px;">
                    <div class="progress-bar bg-primary" role="progressbar"
                         style="width: {{ ($pasoActual / 4) * 100 }}%"></div>
                </div>

                <!-- Steps -->
                <div class="row g-3">
                    <div class="col-6 col-md-2">
                        <div class="d-flex align-items-start gap-3">
                            <div class="proceso-step {{ $pasoActual === 0 ? 'is-current' : ($pasoActual > 0 ? 'is-done' : '') }}"
                                 @if($pasoActual > 0) wire:click="irPaso(0)" role="button" @endif>
                                @if($pasoActual > 0)
                                    <i class="ri ri-check-line ri-lg"></i>
                                @else
                                    0
                                @endif
                            </div>
                            <div class="flex-grow-1">
                                <div class="proceso-step-label {{ $pasoActual === 0 ? 'text-primary' : '' }}">Signos Vitales</div>
                                <div class="proceso-step-hint">Enfermería</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-6 col-md-2">
                        <div class="d-flex align-items-start gap-3">
                            <div class="proceso-step {{ $pasoActual === 1 ? 'is-current' : ($pasoActual > 1 ? 'is-done' : '') }}"
                                 @if($pasoActual > 1) wire:click="irPaso(1)" role="button" @endif>
                                @if($pasoActual > 1)
                                    <i class="ri ri-check-line ri-lg"></i>
                                @else
                                    1
                                @endif
                            </div>
                            <div class="flex-grow-1">
                                <div class="proceso-step-label {{ $pasoActual === 1 ? 'text-primary' : '' }}">Cuestionario</div>
                                <div class="proceso-step-hint">Preconsulta</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-6 col-md-2">
                        <div class="d-flex align-items-start gap-3">
                            <div class="proceso-step {{ $pasoActual === 2 ? 'is-current' : ($pasoActual > 2 ? 'is-done' : '') }}"
                                 @if($pasoActual > 2) wire:click="irPaso(2)" role="button" @endif>
                                @if($pasoActual > 2)
                                    <i class="ri ri-check-line ri-lg"></i>
                                @else
                                    2
                                @endif
                            </div>
                            <div class="flex-grow-1">
                                <div class="proceso-step-label {{ $pasoActual === 2 ? 'text-primary' : '' }}">Evaluación</div>
                                <div class="proceso-step-hint">Diagnóstico</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-6 col-md-2">
                        <div class="d-flex align-items-start gap-3">
                            <div class="proceso-step {{ $pasoActual === 3 ? 'is-current' : ($pasoActual > 3 ? 'is-done' : '') }}"
                                 @if($pasoActual > 3) wire:click="irPaso(3)" role="button" @endif>
                                @if($pasoActual > 3)
                                    <i class="ri ri-check-line ri-lg"></i>
                                @else
                                    3
                                @endif
                            </div>
                            <div class="flex-grow-1">
                                <div class="proceso-step-label {{ $pasoActual === 3 ? 'text-primary' : '' }}">Estudios</div>
                                <div class="proceso-step-hint">Lab/Imágenes</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-6 col-md-2">
                        <div class="d-flex align-items-start gap-3">
                            <div class="proceso-step {{ $pasoActual === 4 ? 'is-current' : '' }}"
                                 @if($pasoActual > 4) wire:click="irPaso(4)" role="button" @endif>
                                4
                            </div>
                            <div class="flex-grow-1">
                                <div class="proceso-step-label {{ $pasoActual === 4 ? 'text-primary' : '' }}">Tratamiento</div>
                                <div class="proceso-step-hint">Medicamentos</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="card border-0 shadow-sm proceso-card">
            <div class="card-body p-4">
                <!-- Paso 0: Signos Vitales -->
                @if($pasoActual == 0)
                <div>
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h5 class="mb-0"><i class="ri ri-heart-pulse-line me-2 text-primary"></i>Signos Vitales</h5>
                        <button class="btn btn-sm btn-primary" wire:click="guardarSignosVitales">
                            <i class="ri ri-save-line me-1"></i>Guardar Ahora
                        </button>
                    </div>

                    <div class="row g-3 mb-4">
                        <!-- Presión Arterial -->
                        <div class="col-md-3">
                            <div class="card vital-card h-100">
                                <div class="card-body text-center">
                                    <div class="vital-label text-muted mb-2">Presión Arterial</div>
                                    <div class="vital-value {{ $presion_sistolica && ($presion_sistolica > 140 || $presion_sistolica < 90) ? 'status-warning' : 'status-normal' }}">
                                        {{ $presion_sistolica ?? '--' }}/{{ $presion_diastolica ?? '--' }}
                                    </div>
                                    <small class="text-muted">mmHg</small>
                                    <div class="mt-2 small text-muted">Normal: 90-140 / 60-90</div>
                                </div>
                            </div>
                        </div>

                        <!-- Frecuencia Cardíaca -->
                        <div class="col-md-3">
                            <div class="card vital-card h-100">
                                <div class="card-body text-center">
                                    <div class="vital-label text-muted mb-2">Frecuencia Cardíaca</div>
                                    <div class="vital-value {{ $frecuencia_cardiaca && ($frecuencia_cardiaca > 100 || $frecuencia_cardiaca < 60) ? 'status-warning' : 'status-normal' }}">
                                        {{ $frecuencia_cardiaca ?? '--' }}
                                    </div>
                                    <small class="text-muted">lpm</small>
                                    <div class="mt-2 small text-muted">Normal: 60-100</div>
                                </div>
                            </div>
                        </div>

                        <!-- Temperatura -->
                        <div class="col-md-3">
                            <div class="card vital-card h-100">
                                <div class="card-body text-center">
                                    <div class="vital-label text-muted mb-2">Temperatura</div>
                                    <div class="vital-value {{ $temperatura && ($temperatura > 37.5 || $temperatura < 36) ? 'status-warning' : 'status-normal' }}">
                                        {{ $temperatura ?? '--' }}
                                    </div>
                                    <small class="text-muted">°C</small>
                                    <div class="mt-2 small text-muted">Normal: 36-37.5</div>
                                </div>
                            </div>
                        </div>

                        <!-- IMC -->
                        <div class="col-md-3">
                            <div class="card vital-card h-100">
                                <div class="card-body text-center">
                                    <div class="vital-label text-muted mb-2">IMC</div>
                                    <div class="vital-value {{ $imc_calculado && ($imc_calculado > 25 || $imc_calculado < 18.5) ? 'status-warning' : 'status-normal' }}">
                                        {{ $imc_calculado ?? '--' }}
                                    </div>
                                    <small class="text-muted">kg/m²</small>
                                    <div class="mt-2 small text-muted">
                                        @if($imc_calculado)
                                            @if($imc_calculado < 18.5) Bajo peso
                                            @elseif($imc_calculado < 25) Normal
                                            @elseif($imc_calculado < 30) Sobrepeso
                                            @else Obesidad
                                            @endif
                                        @else Normal: 18.5-25
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Formulario de entrada -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <div class="form-floating form-floating-outline">
                                <input type="number" step="0.1" class="form-control" wire:model.blur="presion_sistolica" id="presion_sistolica">
                                <label for="presion_sistolica">Presión Sistólica (mmHg)</label>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-floating form-floating-outline">
                                <input type="number" step="0.1" class="form-control" wire:model.blur="presion_diastolica" id="presion_diastolica">
                                <label for="presion_diastolica">Presión Diastólica (mmHg)</label>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-floating form-floating-outline">
                                <input type="number" class="form-control" wire:model.blur="frecuencia_cardiaca" id="frecuencia_cardiaca">
                                <label for="frecuencia_cardiaca">Frecuencia Cardíaca (lpm)</label>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-floating form-floating-outline">
                                <input type="number" class="form-control" wire:model.blur="frecuencia_respiratoria" id="frecuencia_respiratoria">
                                <label for="frecuencia_respiratoria">Frecuencia Respiratoria (rpm)</label>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-floating form-floating-outline">
                                <input type="number" step="0.1" class="form-control" wire:model.blur="temperatura" id="temperatura">
                                <label for="temperatura">Temperatura (°C)</label>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-floating form-floating-outline">
                                <input type="number" step="0.01" class="form-control" wire:model.blur="peso" id="peso">
                                <label for="peso">Peso (kg)</label>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-floating form-floating-outline">
                                <input type="number" step="0.1" class="form-control" wire:model.blur="talla" id="talla">
                                <label for="talla">Talla (cm)</label>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-floating form-floating-outline">
                                <input type="number" step="0.1" class="form-control" wire:model.blur="saturacion_oxigeno" id="saturacion_oxigeno">
                                <label for="saturacion_oxigeno">Saturación O2 (%)</label>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-floating form-floating-outline">
                                <textarea class="form-control" wire:model.blur="observaciones_signos"
                                    id="observaciones_signos" style="height: 80px"></textarea>
                                <label for="observaciones_signos">Observaciones</label>
                            </div>
                        </div>
                    </div>

                    <!-- Gráficos de Historial -->
                    @if(count($historial_signos) > 0)
                    <h6 class="mb-3"><i class="ri ri-line-chart-line me-2 text-primary"></i>Historial de Signos Vitales</h6>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="card-title">Temperatura Corporal</h6>
                                    <div id="chartTemperatura"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="card-title">Presión Arterial</h6>
                                    <div id="chartPresion"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="card-title">Índice de Masa Corporal</h6>
                                    <div id="chartIMC"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
                @endif

                <!-- Paso 1: Cuestionario -->
                @if($pasoActual == 1)
                <div>
                    <h5 class="mb-3"><i class="ri ri-questionnaire-line me-2 text-primary"></i>Cuestionario de Preconsulta</h5>

                    @if($cuestionarioCompleto)
                        <div class="alert alert-info mb-3">
                            <i class="ri ri-check-circle-line me-2"></i>El paciente ya completó el cuestionario
                        </div>
                        @foreach($respuestasPreconsulta as $respuesta)
                        <div class="card mb-3 border">
                            <div class="card-body">
                                <h6 class="mb-2">{{ $respuesta->pregunta->titulo }}</h6>
                                <p class="mb-1">{{ $respuesta->respuesta ?? ($respuesta->respuesta_multiple ? implode(', ', $respuesta->respuesta_multiple) : 'Sin respuesta') }}</p>
                                @if($respuesta->detalle)
                                    <small class="text-muted"><em>Detalle: {{ $respuesta->detalle }}</em></small>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    @elseif(count($preguntas_cuestionario) > 0)
                        <div class="alert alert-warning mb-3">
                            <i class="ri ri-alert-line me-2"></i>El paciente no completó el cuestionario. Puede llenarlo con el paciente ahora.
                        </div>
                        @foreach($preguntas_cuestionario as $pregunta)
                        <div class="card mb-3 border" wire:key="pregunta-{{ $pregunta['id'] }}">
                            <div class="card-body">
                                <h6 class="mb-3">
                                    {{ $pregunta['titulo'] }}
                                    @if($pregunta['obligatorio'])
                                        <span class="badge bg-label-danger">Obligatorio</span>
                                    @endif
                                </h6>
                                @if($pregunta['descripcion'])
                                    <p class="text-muted small mb-3">{{ $pregunta['descripcion'] }}</p>
                                @endif

                                @if($pregunta['tipo'] == 'texto')
                                    <input type="text" class="form-control" 
                                        placeholder="Escriba su respuesta..."
                                        wire:blur="guardarRespuestaCuestionario({{ $pregunta['id'] }}, $event.target.value)">
                                
                                @elseif($pregunta['tipo'] == 'textarea')
                                    <textarea class="form-control" rows="3"
                                        placeholder="Escriba su respuesta..."
                                        wire:blur="guardarRespuestaCuestionario({{ $pregunta['id'] }}, $event.target.value)"></textarea>
                                
                                @elseif($pregunta['tipo'] == 'si_no')
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-outline-success"
                                            wire:click="guardarRespuestaCuestionario({{ $pregunta['id'] }}, 'Sí')">
                                            Sí
                                        </button>
                                        <button type="button" class="btn btn-outline-danger"
                                            wire:click="guardarRespuestaCuestionario({{ $pregunta['id'] }}, 'No')">
                                            No
                                        </button>
                                    </div>
                                
                                @elseif($pregunta['tipo'] == 'opcion_multiple' && $pregunta['opciones'])
                                    @foreach($pregunta['opciones'] as $opcion)
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" 
                                            name="pregunta_{{ $pregunta['id'] }}" 
                                            id="opcion_{{ $pregunta['id'] }}_{{ $loop->index }}"
                                            wire:click="guardarRespuestaCuestionario({{ $pregunta['id'] }}, '{{ $opcion }}')">
                                        <label class="form-check-label" for="opcion_{{ $pregunta['id'] }}_{{ $loop->index }}">
                                            {{ $opcion }}
                                        </label>
                                    </div>
                                    @endforeach
                                
                                @elseif($pregunta['tipo'] == 'multiple' && $pregunta['opciones'])
                                    @foreach($pregunta['opciones'] as $opcion)
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" 
                                            id="multiple_{{ $pregunta['id'] }}_{{ $loop->index }}"
                                            value="{{ $opcion }}"
                                            wire:click="guardarRespuestaCuestionario({{ $pregunta['id'] }}, '{{ $opcion }}')">
                                        <label class="form-check-label" for="multiple_{{ $pregunta['id'] }}_{{ $loop->index }}">
                                            {{ $opcion }}
                                        </label>
                                    </div>
                                    @endforeach
                                
                                @elseif($pregunta['tipo'] == 'opcion' && $pregunta['opciones'])
                                    @foreach($pregunta['opciones'] as $opcion)
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" 
                                            name="pregunta_{{ $pregunta['id'] }}" 
                                            id="opcion_{{ $pregunta['id'] }}_{{ $loop->index }}"
                                            wire:click="guardarRespuestaCuestionario({{ $pregunta['id'] }}, '{{ $opcion }}')">
                                        <label class="form-check-label" for="opcion_{{ $pregunta['id'] }}_{{ $loop->index }}">
                                            {{ $opcion }}
                                        </label>
                                    </div>
                                    @endforeach
                                
                                @elseif($pregunta['tipo'] == 'escala')
                                    <input type="range" class="form-range" min="1" max="10" 
                                        wire:change="guardarRespuestaCuestionario({{ $pregunta['id'] }}, $event.target.value)">
                                @endif

                                @if(in_array($pregunta['tipo'], ['si_no', 'opcion_multiple', 'opcion', 'multiple']))
                                <div class="mt-3">
                                    <label class="form-label small">Detalle adicional (opcional)</label>
                                    <input type="text" class="form-control form-control-sm" 
                                        placeholder="Agregar detalles..."
                                        wire:blur="guardarRespuestaCuestionario({{ $pregunta['id'] }}, null, $event.target.value)">
                                </div>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    @else
                        <div class="alert alert-info">
                            <i class="ri ri-information-line me-2"></i>No hay cuestionario de preconsulta configurado.
                        </div>
                    @endif
                </div>
                @endif

                <!-- Paso 2: Evaluación -->
                @if($pasoActual == 2)
                <div>
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h5 class="mb-0"><i class="ri ri-file-text-line me-2 text-primary"></i>Registro de Evaluación</h5>
                        <span class="badge bg-label-success"><i class="ri ri-save-line me-1"></i>Autoguardado</span>
                    </div>

                    <div class="row g-3">
                        <div class="col-12">
                            <div class="form-floating form-floating-outline">
                                <textarea class="form-control" wire:model.live.debounce.1000ms="enfermedad_actual"
                                    wire:change="guardarEvaluacion" id="enfermedad_actual" style="height: 120px"></textarea>
                                <label for="enfermedad_actual">Enfermedad Actual</label>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-floating form-floating-outline">
                                <textarea class="form-control" wire:model.live.debounce.1000ms="examen_fisico"
                                    wire:change="guardarEvaluacion" id="examen_fisico" style="height: 120px"></textarea>
                                <label for="examen_fisico">Examen Físico</label>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-floating form-floating-outline">
                                <textarea class="form-control" wire:model.live.debounce.1000ms="conclusion"
                                    wire:change="guardarEvaluacion" id="conclusion" style="height: 100px"></textarea>
                                <label for="conclusion">Conclusión</label>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-floating form-floating-outline">
                                <textarea class="form-control" wire:model.live.debounce.1000ms="observaciones_adicionales"
                                    wire:change="guardarEvaluacion" id="observaciones_adicionales" style="height: 100px"></textarea>
                                <label for="observaciones_adicionales">Observaciones Adicionales</label>
                            </div>
                        </div>
                    </div>

                    <!-- Diagnósticos -->
                    <hr class="my-4">
                    <h6 class="mb-3"><i class="ri ri-stethoscope-line me-2 text-primary"></i>Diagnósticos CIE-10</h6>

                    <div class="row g-3 mb-3">
                        <div class="col-12">
                            <div class="form-floating form-floating-outline">
                                <input type="text" class="form-control" wire:model.live.debounce.300ms="busqueda_diagnostico"
                                    id="busqueda_diagnostico" placeholder="Buscar por código o nombre...">
                                <label for="busqueda_diagnostico">Buscar Diagnóstico</label>
                            </div>

                            @if(count($diagnosticos_disponibles) > 0)
                            <div class="list-group mt-2">
                                @foreach($diagnosticos_disponibles as $diag)
                                <button type="button" class="list-group-item list-group-item-action"
                                    wire:click="agregarDiagnostico({{ $diag['id'] }}, 'secundario')">
                                    <strong>{{ $diag['codigo'] }}</strong> - {{ $diag['nombre'] }}
                                </button>
                                @endforeach
                            </div>
                            @endif
                        </div>

                        <div class="col-12">
                            @if(!$mostrar_form_nuevo)
                                <button class="btn btn-sm btn-outline-primary" wire:click="$set('mostrar_form_nuevo', true)">
                                    <i class="ri ri-add-line me-1"></i>Crear Nuevo Diagnóstico
                                </button>
                            @else
                                <div class="card border">
                                    <div class="card-body">
                                        <div class="row g-2">
                                            <div class="col-md-3">
                                                <input type="text" class="form-control" wire:model="nuevo_diagnostico_codigo"
                                                    placeholder="Código (ej: J00)">
                                            </div>
                                            <div class="col-md-7">
                                                <input type="text" class="form-control" wire:model="nuevo_diagnostico_nombre"
                                                    placeholder="Nombre del diagnóstico">
                                            </div>
                                            <div class="col-md-2">
                                                <button class="btn btn-success w-100" wire:click="crearYAgregarDiagnostico">
                                                    <i class="ri ri-check-line"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    @if(count($diagnosticos_agregados) > 0)
                    <h6 class="mb-2">Diagnósticos Agregados</h6>
                    <div class="row g-2">
                        @foreach($diagnosticos_agregados as $index => $diag)
                        <div class="col-12">
                            <div class="card border-start border-primary border-3">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center gap-2 mb-1">
                                                <span class="badge bg-label-primary">{{ $diag['codigo'] }}</span>
                                                <strong>{{ $diag['nombre'] }}</strong>
                                            </div>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <button type="button" class="btn {{ $diag['tipo'] == 'principal' ? 'btn-primary' : 'btn-outline-primary' }}"
                                                    wire:click="cambiarTipoDiagnostico({{ $index }}, 'principal')">
                                                    Principal
                                                </button>
                                                <button type="button" class="btn {{ $diag['tipo'] == 'secundario' ? 'btn-secondary' : 'btn-outline-secondary' }}"
                                                    wire:click="cambiarTipoDiagnostico({{ $index }}, 'secundario')">
                                                    Secundario
                                                </button>
                                            </div>
                                        </div>
                                        <button class="btn btn-sm btn-icon btn-outline-danger"
                                            wire:click="eliminarDiagnostico({{ $index }})">
                                            <i class="ri ri-delete-bin-line"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
                @endif

                <!-- Paso 3: Estudios -->
                @if($pasoActual == 3)
                <div>
                    <h5 class="mb-3"><i class="ri ri-test-tube-line me-2 text-primary"></i>Laboratorios y Procedimientos</h5>

                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="form-floating form-floating-outline">
                                <select class="form-select" wire:model="tipo_estudio" id="tipo_estudio">
                                    <option value="imagen">Imagen</option>
                                    <option value="laboratorio">Laboratorio</option>
                                    <option value="otros">Otros</option>
                                </select>
                                <label for="tipo_estudio">Tipo de Estudio</label>
                            </div>
                        </div>

                        <div class="col-md-8">
                            <div class="form-floating form-floating-outline">
                                <input type="text" class="form-control" wire:model="nombre_estudio" id="nombre_estudio">
                                <label for="nombre_estudio">Nombre del Estudio</label>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-floating form-floating-outline">
                                <textarea class="form-control" wire:model="indicaciones_estudio"
                                    id="indicaciones_estudio" style="height: 80px"></textarea>
                                <label for="indicaciones_estudio">Indicaciones</label>
                            </div>
                        </div>

                        <div class="col-12">
                            <button class="btn btn-primary" wire:click="agregarEstudio">
                                <i class="ri ri-add-line me-1"></i>Agregar Estudio
                            </button>
                        </div>
                    </div>

                    @if(count($estudios) > 0)
                    <h6 class="mb-3">Estudios Agregados</h6>
                    <div class="row g-3">
                        @foreach($estudios as $index => $estudio)
                        <div class="col-12">
                            <div class="card estudio-item">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center gap-2 mb-2">
                                                <span class="badge bg-label-primary">{{ ucfirst($estudio['tipo_estudio']) }}</span>
                                                <strong>{{ $estudio['nombre_estudio'] }}</strong>
                                            </div>
                                            @if($estudio['indicaciones'])
                                                <small class="text-muted">{{ $estudio['indicaciones'] }}</small>
                                            @endif
                                        </div>
                                        <button class="btn btn-sm btn-icon btn-outline-danger"
                                            wire:click="eliminarEstudio({{ $index }})"
                                            wire:confirm="¿Eliminar este estudio?">
                                            <i class="ri ri-delete-bin-line"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
                @endif

                <!-- Paso 4: Tratamiento -->
                @if($pasoActual == 4)
                <div>
                    <h5 class="mb-3"><i class="ri ri-capsule-line me-2 text-primary"></i>Tratamiento</h5>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="form-floating form-floating-outline">
                                <input type="text" class="form-control" wire:model="medicamento" id="medicamento">
                                <label for="medicamento">Medicamento</label>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-floating form-floating-outline">
                                <textarea class="form-control" wire:model="indicaciones_tratamiento"
                                    id="indicaciones_tratamiento" style="height: 56px"></textarea>
                                <label for="indicaciones_tratamiento">Indicaciones</label>
                            </div>
                        </div>

                        <div class="col-12">
                            <button class="btn btn-primary" wire:click="agregarTratamiento">
                                <i class="ri ri-add-line me-1"></i>Agregar Tratamiento
                            </button>
                        </div>
                    </div>

                    @if(count($tratamientos) > 0)
                    <h6 class="mb-3">Tratamientos Agregados</h6>
                    <div class="row g-3">
                        @foreach($tratamientos as $index => $tratamiento)
                        <div class="col-12">
                            <div class="card tratamiento-item">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">{{ $tratamiento['medicamento'] }}</h6>
                                            <small class="text-muted">{{ $tratamiento['indicaciones'] }}</small>
                                        </div>
                                        <button class="btn btn-sm btn-icon btn-outline-danger"
                                            wire:click="eliminarTratamiento({{ $index }})"
                                            wire:confirm="¿Eliminar este tratamiento?">
                                            <i class="ri ri-delete-bin-line"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
                @endif
            </div>

            <!-- Footer Navigation -->
            <div class="card-footer bg-white border-0 p-4 pt-0">
                <div class="d-flex justify-content-between">
                    @if($pasoActual > 0)
                        <button class="btn btn-outline-secondary" wire:click="irPaso({{ $pasoActual - 1 }})">
                            <i class="ri ri-arrow-left-line me-1"></i>Anterior
                        </button>
                    @else
                        <a href="{{ route('admin.gestion.consultas.index') }}" class="btn btn-outline-secondary">
                            <i class="ri ri-arrow-left-line me-1"></i>Volver
                        </a>
                    @endif

                    @if($pasoActual < 4)
                        <button class="btn btn-primary" wire:click="irPaso({{ $pasoActual + 1 }})">
                            Siguiente <i class="ri ri-arrow-right-line ms-1"></i>
                        </button>
                    @else
                        <button class="btn btn-success" wire:click="finalizarConsulta"
                            wire:confirm="¿Finalizar la consulta?">
                            <i class="ri ri-check-line me-1"></i>Finalizar Consulta
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.45.1/dist/apexcharts.min.js"></script>
    <script>
        let chartTemperatura, chartPresion, chartIMC;

        document.addEventListener('livewire:initialized', () => {
            // Renderizar gráficos si hay historial
            @if(count($historial_signos) > 0)
            renderCharts(@json($historial_signos));
            @endif

            // Actualizar gráficos cuando se guardan signos vitales
            Livewire.on('chartsUpdated', (event) => {
                const data = event[0] || event;
                if (chartTemperatura) chartTemperatura.destroy();
                if (chartPresion) chartPresion.destroy();
                if (chartIMC) chartIMC.destroy();
                renderCharts(data.historial);
            });

            Livewire.on('notify', (event) => {
                const data = event[0] || event;
                const type = data.type === 'success' ? 'success' : 'error';
                const icon = type === 'success' ? 'ri-check-line' : 'ri-error-warning-line';

                const toast = `
                    <div class="bs-toast toast toast-placement-ex m-2 fade bg-${type} top-0 end-0 show" role="alert">
                        <div class="toast-header">
                            <i class="ri ${icon} me-2"></i>
                            <div class="me-auto fw-semibold">${type === 'success' ? 'Éxito' : 'Error'}</div>
                            <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
                        </div>
                        <div class="toast-body">${data.message}</div>
                    </div>
                `;

                const container = document.querySelector('.toast-container') || (() => {
                    const div = document.createElement('div');
                    div.className = 'toast-container position-fixed top-0 end-0 p-3';
                    document.body.appendChild(div);
                    return div;
                })();

                container.insertAdjacentHTML('beforeend', toast);
                const toastEl = container.lastElementChild;
                setTimeout(() => toastEl.remove(), 3000);
            });
        });

        function renderCharts(historial) {
            if (!historial || historial.length === 0) return;

            // Gráfico de Temperatura - Termómetro estilo
            chartTemperatura = new ApexCharts(document.querySelector('#chartTemperatura'), {
                chart: { type: 'area', height: 250, toolbar: { show: false }, animations: { enabled: true, speed: 800 } },
                series: [{ name: 'Temperatura °C', data: historial.map(h => parseFloat(h.temperatura) || 0) }],
                xaxis: {
                    categories: historial.map(h => new Date(h.created_at).toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit' })),
                    labels: { style: { fontSize: '11px' } }
                },
                stroke: { curve: 'smooth', width: 3 },
                fill: {
                    type: 'gradient',
                    gradient: { shadeIntensity: 1, opacityFrom: 0.7, opacityTo: 0.2, stops: [0, 90, 100] }
                },
                colors: ['#ff6b6b'],
                markers: { size: 5, colors: ['#fff'], strokeColors: '#ff6b6b', strokeWidth: 2, hover: { size: 7 } },
                dataLabels: { enabled: true, formatter: (val) => val.toFixed(1) + '°', style: { fontSize: '10px' } },
                yaxis: {
                    min: 34,
                    max: 41,
                    labels: { formatter: (val) => val.toFixed(1) + '°C' }
                },
                annotations: {
                    yaxis: [
                        { y: 36, y2: 37.5, fillColor: '#51cf66', opacity: 0.15, borderColor: '#51cf66', label: { text: 'Normal', style: { color: '#fff', background: '#51cf66' } } },
                        { y: 37.5, y2: 38.5, fillColor: '#ffd43b', opacity: 0.15, borderColor: '#ffd43b' },
                        { y: 38.5, borderColor: '#ff6b6b', strokeDashArray: 4, label: { text: 'Fiebre', style: { color: '#fff', background: '#ff6b6b' } } }
                    ]
                },
                tooltip: { y: { formatter: (val) => val.toFixed(1) + ' °C' } }
            });
            chartTemperatura.render();

            // Gráfico de Presión Arterial - Doble línea con zonas
            chartPresion = new ApexCharts(document.querySelector('#chartPresion'), {
                chart: { type: 'line', height: 250, toolbar: { show: false }, animations: { enabled: true, speed: 800 } },
                series: [
                    { name: 'Sistólica', data: historial.map(h => parseFloat(h.presion_arterial_sistolica) || 0) },
                    { name: 'Diastólica', data: historial.map(h => parseFloat(h.presion_arterial_diastolica) || 0) }
                ],
                xaxis: {
                    categories: historial.map(h => new Date(h.created_at).toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit' })),
                    labels: { style: { fontSize: '11px' } }
                },
                stroke: { curve: 'smooth', width: 3 },
                colors: ['#4c6ef5', '#15aabf'],
                markers: { size: 5, strokeWidth: 2, hover: { size: 7 } },
                dataLabels: { enabled: false },
                yaxis: {
                    min: 50,
                    max: 180,
                    labels: { formatter: (val) => val.toFixed(0) + ' mmHg' }
                },
                annotations: {
                    yaxis: [
                        { y: 90, y2: 120, fillColor: '#51cf66', opacity: 0.1, label: { text: 'Normal Sistólica', style: { color: '#fff', background: '#51cf66', fontSize: '9px' } } },
                        { y: 120, y2: 140, fillColor: '#ffd43b', opacity: 0.1 },
                        { y: 140, borderColor: '#ff6b6b', strokeDashArray: 4, label: { text: 'Hipertensión', style: { color: '#fff', background: '#ff6b6b' } } }
                    ]
                },
                legend: { position: 'top', horizontalAlign: 'right' },
                tooltip: { shared: true, intersect: false, y: { formatter: (val) => val.toFixed(0) + ' mmHg' } }
            });
            chartPresion.render();

            // Gráfico de IMC - Gauge style con zonas de color
            chartIMC = new ApexCharts(document.querySelector('#chartIMC'), {
                chart: { type: 'area', height: 250, toolbar: { show: false }, animations: { enabled: true, speed: 800 } },
                series: [{ name: 'IMC', data: historial.map(h => parseFloat(h.imc) || 0) }],
                xaxis: {
                    categories: historial.map(h => new Date(h.created_at).toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit' })),
                    labels: { style: { fontSize: '11px' } }
                },
                stroke: { curve: 'smooth', width: 3 },
                fill: {
                    type: 'gradient',
                    gradient: { shadeIntensity: 1, opacityFrom: 0.6, opacityTo: 0.1, stops: [0, 90, 100] }
                },
                colors: ['#fab005'],
                markers: { size: 5, colors: ['#fff'], strokeColors: '#fab005', strokeWidth: 2, hover: { size: 7 } },
                dataLabels: { enabled: true, formatter: (val) => val.toFixed(1), style: { fontSize: '10px' } },
                yaxis: {
                    min: 15,
                    max: 40,
                    labels: { formatter: (val) => val.toFixed(1) }
                },
                annotations: {
                    yaxis: [
                        { y: 0, y2: 18.5, fillColor: '#74c0fc', opacity: 0.15, label: { text: 'Bajo Peso', style: { color: '#fff', background: '#74c0fc', fontSize: '9px' } } },
                        { y: 18.5, y2: 25, fillColor: '#51cf66', opacity: 0.15, label: { text: 'Normal', style: { color: '#fff', background: '#51cf66' } } },
                        { y: 25, y2: 30, fillColor: '#ffd43b', opacity: 0.15, label: { text: 'Sobrepeso', style: { color: '#000', background: '#ffd43b', fontSize: '9px' } } },
                        { y: 30, fillColor: '#ff6b6b', opacity: 0.15, label: { text: 'Obesidad', style: { color: '#fff', background: '#ff6b6b', fontSize: '9px' } } }
                    ]
                },
                tooltip: { y: { formatter: (val) => val.toFixed(1) + ' kg/m²' } }
            });
            chartIMC.render();
        }
    </script>
    @endpush
</div>
