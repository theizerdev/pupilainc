<div>
    @section('title', 'Proceso de Consulta')

    @push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/apexcharts@3.45.1/dist/apexcharts.css" />
    <style>
        .proceso-step {
            width: 50px; height: 50px; border-radius: 50%;
            display: inline-flex; align-items: center; justify-content: center;
            border: 2px solid var(--bs-border-color);
            background: var(--bs-body-bg); color: var(--bs-secondary);
            transition: all .2s ease; font-weight: 600;
        }
        .proceso-step.is-current {
            background: var(--bs-primary); border-color: var(--bs-primary);
            color: #fff; box-shadow: 0 0 0 6px rgba(var(--bs-primary-rgb),.12);
        }
        .proceso-step.is-done {
            background: var(--bs-success); border-color: var(--bs-success);
            color: #fff; cursor: pointer;
        }
        .proceso-step.is-done:hover { transform: translateY(-1px); }
        .proceso-step-label { font-size: .9rem; font-weight: 500; }
        .proceso-step-hint  { font-size: .75rem; color: var(--bs-secondary-color); }
        .vital-card { transition: all .2s ease; }
        .vital-card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,.1); }
        .vital-value { font-size: 1.5rem; font-weight: 700; }
        .vital-label { font-size: .75rem; text-transform: uppercase; letter-spacing: .5px; }
        .status-normal  { color: #28a745; }
        .status-warning { color: #ffc107; }
        .status-danger  { color: #dc3545; }
        .seccion-header {
            border-left: 4px solid var(--seccion-color, #3B82F6);
            padding-left: 12px; margin-bottom: 1rem;
        }
        .estudio-item, .tratamiento-item { border-left: 4px solid var(--bs-primary); }
    </style>
    @endpush

    <div class="proceso-shell">

        {{-- ── HEADER ──────────────────────────────────────────────────────── --}}
        <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
            <div>
                <h4 class="mb-1">
                    <i class="ri ri-stethoscope-line me-2 text-primary"></i>Proceso de Consulta
                </h4>
                <div class="text-muted small">
                    <strong>Paciente:</strong> {{ $consulta->paciente->nombre_completo }} Edad: {{ $consulta->paciente->edad_formateada ?? 'N/A' }} |
                    <strong>Médico:</strong> Dr(a). {{ $consulta->medico->nombre_completo }}
                    @if($consulta->especialidad)
                        | <strong>Especialidad:</strong> {{ $consulta->especialidad->nombre }}
                    @endif
                </div>
                <div class="mt-1">
                    <span class="badge" style="background-color:{{ $consulta->estado_color }};color:#fff;">
                        {{ $consulta->estado_label }}
                    </span>
                </div>
            </div>

            <div class="d-flex align-items-center gap-2">
                {{-- Selector de estado dinámico según la plantilla --}}
                <div class="form-floating form-floating-outline" style="min-width:200px;">
                    <select class="form-select" id="nuevoEstado" wire:model="nuevoEstado">
                        <option value="">Cambiar estado...</option>
                        @foreach($estadosFlujo as $estadoKey)
                            <option value="{{ $estadoKey }}">
                                {{ $estadosDisponibles[$estadoKey] ?? ucfirst($estadoKey) }}
                            </option>
                        @endforeach
                    </select>
                    <label for="nuevoEstado">Estado</label>
                </div>
                <button class="btn btn-primary" wire:click="cambiarEstadoConsulta"
                    @if(empty($nuevoEstado)) disabled @endif>
                    <i class="ri ri-check-line me-1"></i>Actualizar
                </button>
                <a href="{{ route('admin.gestion.consultas.index') }}" class="btn btn-outline-secondary">
                    <i class="ri ri-arrow-left-line me-1"></i>Volver
                </a>
            </div>
        </div>

        {{-- ── FORMULARIO DEL ESTADO ACTUAL ─────────────────────────────────── --}}
        @if($formularioEstadoActual && count($formularioEstadoActual['secciones']) > 0 && !in_array($consulta->estado, ['en_consultorio', 'en_consultorio_optometrista']))
        <div class="card border-0 shadow-sm mb-4 border-start border-4"
             style="border-color: {{ \App\Models\Consulta::ESTADO_COLORES[$consulta->estado] ?? '#3B82F6' }} !important">
            <div class="card-header bg-transparent border-0 d-flex align-items-center justify-content-between">
                <h6 class="mb-0">
                    <span class="badge me-2" style="background-color: {{ \App\Models\Consulta::ESTADO_COLORES[$consulta->estado] ?? '#3B82F6' }}">
                        {{ $consulta->estado_label }}
                    </span>
                    Formulario del Estado Actual
                </h6>
                <button class="btn btn-sm btn-primary" wire:click="guardarDatosEstado">
                    <i class="ri ri-save-line me-1"></i>Guardar
                </button>
            </div>
            <div class="card-body">
                @foreach($formularioEstadoActual['secciones'] as $seccion)
                <div class="mb-4">
                    <div class="seccion-header" style="--seccion-color: {{ $seccion['color'] ?? '#3B82F6' }}">
                        <h6 class="mb-0">
                            @if($seccion['icono'])
                                <i class="fas {{ $seccion['icono'] }} me-2" style="color:{{ $seccion['color'] ?? '#3B82F6' }}"></i>
                            @endif
                            {{ $seccion['nombre'] }}
                        </h6>
                    </div>
                    <div class="row g-3">
                        @foreach($seccion['campos'] as $campo)
                        <div class="col-md-{{ $campo['ancho_columnas'] }}" wire:key="est-campo-{{ $campo['id'] }}">
                            @php $fieldName = "datos_estado_actual.{$campo['nombre_campo']}"; @endphp

                            @if($campo['tipo'] === 'text')
                            <div class="form-floating form-floating-outline">
                                <input type="text" class="form-control" id="ec_{{ $campo['id'] }}"
                                    wire:model.blur="{{ $fieldName }}"
                                    placeholder="{{ $campo['placeholder'] ?? $campo['etiqueta'] }}">
                                <label for="ec_{{ $campo['id'] }}">
                                    {{ $campo['etiqueta'] }}
                                    @if($campo['unidad']) <small class="text-muted">({{ $campo['unidad'] }})</small> @endif
                                    @if($campo['obligatorio']) <span class="text-danger">*</span> @endif
                                </label>
                            </div>

                            @elseif($campo['tipo'] === 'number')
                            <div class="form-floating form-floating-outline">
                                <input type="number" class="form-control" id="ec_{{ $campo['id'] }}"
                                    wire:model.blur="{{ $fieldName }}"
                                    @if($campo['min'] !== null) min="{{ $campo['min'] }}" @endif
                                    @if($campo['max'] !== null) max="{{ $campo['max'] }}" @endif>
                                <label for="ec_{{ $campo['id'] }}">
                                    {{ $campo['etiqueta'] }}
                                    @if($campo['unidad']) <small class="text-muted">({{ $campo['unidad'] }})</small> @endif
                                    @if($campo['obligatorio']) <span class="text-danger">*</span> @endif
                                </label>
                            </div>

                            @elseif($campo['tipo'] === 'textarea')
                            <div class="form-floating form-floating-outline">
                                <textarea class="form-control" id="ec_{{ $campo['id'] }}"
                                    wire:model.blur="{{ $fieldName }}"
                                    style="height:100px"
                                    placeholder="{{ $campo['placeholder'] ?? '' }}"></textarea>
                                <label for="ec_{{ $campo['id'] }}">
                                    {{ $campo['etiqueta'] }}
                                    @if($campo['obligatorio']) <span class="text-danger">*</span> @endif
                                </label>
                            </div>

                            @elseif($campo['tipo'] === 'select')
                            <div class="form-floating form-floating-outline">
                                <select class="form-select" id="ec_{{ $campo['id'] }}"
                                    wire:model="{{ $fieldName }}">
                                    <option value="">— Seleccionar —</option>
                                    @foreach($campo['opciones'] as $op)
                                        <option value="{{ $op }}">{{ $op }}</option>
                                    @endforeach
                                </select>
                                <label for="ec_{{ $campo['id'] }}">
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
                                @foreach($campo['opciones'] as $op)
                                <div class="form-check">
                                    <input class="form-check-input" type="radio"
                                        name="ec_radio_{{ $campo['id'] }}"
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
                                @foreach($campo['opciones'] as $op)
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
                                        {{ $datos_estado_actual[$campo['nombre_campo']] ?? ($campo['min'] ?? 0) }}
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
                                <input type="date" class="form-control" id="ec_{{ $campo['id'] }}"
                                    wire:model="{{ $fieldName }}">
                                <label for="ec_{{ $campo['id'] }}">
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
            </div>
        </div>
        @else
        
        @endif

        {{-- ── HISTORIAL DE GOTAS APLICADAS ──────────────────────────────────── --}}
        @if($consulta->gotasAplicadas->count() > 0)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent border-0">
                <h6 class="mb-0">
                    <i class="ri ri-drop-line me-2 text-info"></i>Historial de Gotas Aplicadas
                </h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>Tipo de Gota</th>
                                <th class="text-center">OD</th>
                                <th class="text-center">OI</th>
                                <th>Hora Aplicación</th>
                                <th>Tiempo Espera</th>
                                <th>Aplicado por</th>
                                <th>Observaciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($consulta->gotasAplicadas as $gota)
                            <tr>
                                <td><span class="badge bg-label-info">{{ $gota->tipo_gota }}</span></td>
                                <td class="text-center"><strong>{{ $gota->gotas_od }}</strong></td>
                                <td class="text-center"><strong>{{ $gota->gotas_oi }}</strong></td>
                                <td><small>{{ $gota->hora_aplicacion }}</small></td>
                                <td><small>{{ $gota->tiempo_espera }} min</small></td>
                                <td><small>{{ $gota->aplicadoPor?->name ?? '-' }}</small></td>
                                <td><small class="text-muted">{{ $gota->observaciones ?? '-' }}</small></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif


        {{-- ── STEPPER DINÁMICO (solo en consultorio) ─────────────────────────── --}}
        @if(in_array($consulta->estado, ['en_consultorio', 'en_consultorio_optometrista']))
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <div class="progress mb-4" style="height:8px;">
                    <div class="progress-bar bg-primary" role="progressbar"
                         style="width:{{ $totalPasos > 1 ? ($pasoActual / ($totalPasos - 1)) * 100 : 100 }}%">
                    </div>
                </div>

                <div class="row g-3">
                    @php
                        $pasoLabels = [
                            'signos_vitales' => ['label' => 'Signos Vitales',  'hint' => 'Enfermería',   'icon' => 'ri ri-heart-pulse-line'],
                            'cuestionario'   => ['label' => 'Cuestionario',    'hint' => 'Preconsulta',  'icon' => 'ri ri-questionnaire-line'],
                            'evaluacion'     => ['label' => 'Evaluación',      'hint' => 'Diagnóstico',  'icon' => 'ri ri-file-text-line'],
                            'estudios'       => ['label' => 'Estudios',        'hint' => 'Lab/Imágenes', 'icon' => 'ri ri-test-tube-line'],
                            'tratamientos'   => ['label' => 'Tratamiento',     'hint' => 'Medicamentos', 'icon' => 'ri ri-capsule-line'],
                            'reposo'         => ['label' => 'Reposo',          'hint' => 'Médico',       'icon' => 'ri ri-hotel-bed-line'],
                        ];
                        $colSize = max(2, intval(12 / count($pasosHabilitados)));
                    @endphp

                    @foreach($pasosHabilitados as $i => $key)
                        @php $meta = $pasoLabels[$key] ?? ['label' => ucfirst($key), 'hint' => '', 'icon' => 'ri ri-circle-line']; @endphp
                        <div class="col-6 col-md-{{ $colSize }}">
                            <div class="d-flex align-items-start gap-2">
                                <div class="proceso-step {{ $pasoActual === $i ? 'is-current' : ($pasoActual > $i ? 'is-done' : '') }}"
                                     @if($pasoActual > $i) wire:click="irPaso({{ $i }})" role="button" @endif>
                                    @if($pasoActual > $i)
                                        <i class="ri ri-check-line ri-lg"></i>
                                    @else
                                        <i class="{{ $meta['icon'] }}"></i>
                                    @endif
                                </div>
                                <div class="flex-grow-1">
                                    <div class="proceso-step-label {{ $pasoActual === $i ? 'text-primary' : '' }}">
                                        {{ $meta['label'] }}
                                    </div>
                                    <div class="proceso-step-hint">{{ $meta['hint'] }}</div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        {{-- ── CONTENIDO POR PASO (solo en consultorio) ───────────────────────────────────────────── --}}
        @if(in_array($consulta->estado, ['en_consultorio', 'en_consultorio_optometrista']))
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">

                {{-- PASO: SIGNOS VITALES --}}
                @if($pasoKey === 'signos_vitales')
                <div>
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h5 class="mb-0"><i class="ri ri-heart-pulse-line me-2 text-primary"></i>Signos Vitales</h5>
                        <button class="btn btn-sm btn-primary" wire:click="guardarSignosVitales">
                            <i class="ri ri-save-line me-1"></i>Guardar
                        </button>
                    </div>

                    {{-- Tarjetas resumen --}}
                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <div class="card vital-card h-100 text-center p-3">
                                <div class="vital-label text-muted mb-1">Presión Arterial</div>
                                <div class="vital-value {{ ($presion_sistolica && ($presion_sistolica > 140 || $presion_sistolica < 90)) ? 'status-warning' : 'status-normal' }}">
                                    {{ $presion_sistolica ?? '--' }}/{{ $presion_diastolica ?? '--' }}
                                </div>
                                <small class="text-muted">mmHg</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card vital-card h-100 text-center p-3">
                                <div class="vital-label text-muted mb-1">Frec. Cardíaca</div>
                                <div class="vital-value {{ ($frecuencia_cardiaca && ($frecuencia_cardiaca > 100 || $frecuencia_cardiaca < 60)) ? 'status-warning' : 'status-normal' }}">
                                    {{ $frecuencia_cardiaca ?? '--' }}
                                </div>
                                <small class="text-muted">lpm</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card vital-card h-100 text-center p-3">
                                <div class="vital-label text-muted mb-1">Temperatura</div>
                                <div class="vital-value {{ ($temperatura && ($temperatura > 37.5 || $temperatura < 36)) ? 'status-warning' : 'status-normal' }}">
                                    {{ $temperatura ?? '--' }}
                                </div>
                                <small class="text-muted">°C</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card vital-card h-100 text-center p-3">
                                <div class="vital-label text-muted mb-1">IMC</div>
                                <div class="vital-value {{ ($imc_calculado && ($imc_calculado > 25 || $imc_calculado < 18.5)) ? 'status-warning' : 'status-normal' }}">
                                    {{ $imc_calculado ?? '--' }}
                                </div>
                                <small class="text-muted">kg/m²</small>
                            </div>
                        </div>
                    </div>

                    {{-- Formulario --}}
                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <div class="form-floating form-floating-outline">
                                <input type="number" step="0.1" class="form-control" wire:model.blur="presion_sistolica" id="ps">
                                <label for="ps">Presión Sistólica (mmHg)</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-floating form-floating-outline">
                                <input type="number" step="0.1" class="form-control" wire:model.blur="presion_diastolica" id="pd">
                                <label for="pd">Presión Diastólica (mmHg)</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-floating form-floating-outline">
                                <input type="number" class="form-control" wire:model.blur="frecuencia_cardiaca" id="fc">
                                <label for="fc">Frec. Cardíaca (lpm)</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-floating form-floating-outline">
                                <input type="number" class="form-control" wire:model.blur="frecuencia_respiratoria" id="fr">
                                <label for="fr">Frec. Respiratoria (rpm)</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-floating form-floating-outline">
                                <input type="number" step="0.1" class="form-control" wire:model.blur="temperatura" id="temp">
                                <label for="temp">Temperatura (°C)</label>
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
                                <input type="number" step="0.1" class="form-control" wire:model.blur="saturacion_oxigeno" id="spo2">
                                <label for="spo2">Saturación O₂ (%)</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-floating form-floating-outline">
                                <textarea class="form-control" wire:model.blur="observaciones_signos" id="obs_sv" style="height:80px"></textarea>
                                <label for="obs_sv">Observaciones</label>
                            </div>
                        </div>
                    </div>

                    {{-- Gráficos historial --}}
                    @if(count($historial_signos) > 0)
                    <h6 class="mb-3"><i class="ri ri-line-chart-line me-2 text-primary"></i>Historial</h6>
                    <div class="row g-3">
                        <div class="col-md-4"><div class="card"><div class="card-body"><h6>Temperatura</h6><div id="chartTemperatura"></div></div></div></div>
                        <div class="col-md-4"><div class="card"><div class="card-body"><h6>Presión Arterial</h6><div id="chartPresion"></div></div></div></div>
                        <div class="col-md-4"><div class="card"><div class="card-body"><h6>IMC</h6><div id="chartIMC"></div></div></div></div>
                    </div>
                    @endif
                </div>
                @endif

                {{-- PASO: CUESTIONARIO --}}
                @if($pasoKey === 'cuestionario')
                <div>
                    <h5 class="mb-3"><i class="ri ri-questionnaire-line me-2 text-primary"></i>Cuestionario de Preconsulta</h5>

                    @if($cuestionarioCompleto)
                        <div class="alert alert-info mb-3">
                            <i class="ri ri-check-circle-line me-2"></i>El paciente ya completó el cuestionario.
                        </div>
                        @foreach($respuestasPreconsulta as $resp)
                        <div class="card mb-2 border">
                            <div class="card-body py-2">
                                <strong class="small">{{ $resp->pregunta->titulo }}</strong>
                                <p class="mb-0 small text-muted">
                                    {{ $resp->respuesta ?? ($resp->respuesta_multiple ? implode(', ', $resp->respuesta_multiple) : '—') }}
                                </p>
                                @if($resp->detalle)
                                    <small class="text-muted fst-italic">{{ $resp->detalle }}</small>
                                @endif
                            </div>
                        </div>
                        @endforeach

                    @elseif(count($preguntas_cuestionario) > 0)
                        <div class="alert alert-warning mb-3">
                            <i class="ri ri-alert-line me-2"></i>El paciente no completó el cuestionario. Puede llenarlo ahora.
                        </div>
                        @foreach($preguntas_cuestionario as $pregunta)
                        <div class="card mb-3 border" wire:key="preg-{{ $pregunta['id'] }}">
                            <div class="card-body">
                                <h6 class="mb-2">
                                    {{ $pregunta['titulo'] }}
                                    @if($pregunta['obligatorio'])
                                        <span class="badge bg-label-danger ms-1">Obligatorio</span>
                                    @endif
                                </h6>
                                @if($pregunta['descripcion'])
                                    <p class="text-muted small mb-2">{{ $pregunta['descripcion'] }}</p>
                                @endif

                                @if($pregunta['tipo'] === 'texto')
                                    <input type="text" class="form-control"
                                        wire:blur="guardarRespuestaCuestionario({{ $pregunta['id'] }}, $event.target.value)">

                                @elseif($pregunta['tipo'] === 'si_no')
                                    <div class="btn-group">
                                        <button class="btn btn-outline-success" wire:click="guardarRespuestaCuestionario({{ $pregunta['id'] }}, 'Sí')">Sí</button>
                                        <button class="btn btn-outline-danger"  wire:click="guardarRespuestaCuestionario({{ $pregunta['id'] }}, 'No')">No</button>
                                    </div>

                                @elseif(in_array($pregunta['tipo'], ['opcion', 'opcion_multiple']) && $pregunta['opciones'])
                                    @foreach($pregunta['opciones'] as $op)
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio"
                                            name="preg_{{ $pregunta['id'] }}"
                                            wire:click="guardarRespuestaCuestionario({{ $pregunta['id'] }}, '{{ $op }}')">
                                        <label class="form-check-label">{{ $op }}</label>
                                    </div>
                                    @endforeach

                                @elseif($pregunta['tipo'] === 'multiple' && $pregunta['opciones'])
                                    @php
                                        $respuestaExistente = $respuestasPreconsulta->where('pregunta_id', $pregunta['id'])->first();
                                        $seleccionadas = $respuestaExistente?->respuesta_multiple ?? [];
                                    @endphp
                                    @foreach($pregunta['opciones'] as $op)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox"
                                            value="{{ $op }}"
                                            {{ in_array($op, $seleccionadas) ? 'checked' : '' }}
                                            wire:click="toggleRespuestaMultiple({{ $pregunta['id'] }}, '{{ $op }}')">
                                        <label class="form-check-label">{{ $op }}</label>
                                    </div>
                                    @endforeach

                                @elseif($pregunta['tipo'] === 'escala')
                                    <input type="range" class="form-range" min="1" max="10"
                                        wire:change="guardarRespuestaCuestionario({{ $pregunta['id'] }}, $event.target.value)">
                                @endif
                            </div>
                        </div>
                        @endforeach
                    @else
                        <div class="alert alert-info">
                            <i class="ri ri-information-line me-2"></i>No hay cuestionario configurado para esta especialidad.
                        </div>
                    @endif
                </div>
                @endif

                {{-- PASO: EVALUACIÓN DINÁMICA --}}
                @if($pasoKey === 'evaluacion')
                <div>
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h5 class="mb-0"><i class="ri ri-file-text-line me-2 text-primary"></i>Evaluación Clínica</h5>
                        <div class="d-flex gap-2">
                            <span class="badge bg-label-success"><i class="ri ri-save-line me-1"></i>Autoguardado</span>
                            <button class="btn btn-sm btn-primary" wire:click="guardarDatosEstado">
                                <i class="ri ri-save-line me-1"></i>Guardar
                            </button>
                        </div>
                    </div>

                    @if($formularioEstadoActual && count($formularioEstadoActual['secciones']) > 0)
                        {{-- Formulario del estado en_consultorio --}}
                        @foreach($formularioEstadoActual['secciones'] as $seccion)
                        <div class="mb-4">
                            <div class="seccion-header" style="--seccion-color: {{ $seccion['color'] ?? '#3B82F6' }}">
                                <h6 class="mb-0">
                                    @if($seccion['icono'])
                                        <i class="fas {{ $seccion['icono'] }} me-2" style="color:{{ $seccion['color'] ?? '#3B82F6' }}"></i>
                                    @endif
                                    {{ $seccion['nombre'] }}
                                </h6>
                            </div>
                            <div class="row g-3">
                                @foreach($seccion['campos'] as $campo)
                                <div class="col-md-{{ $campo['ancho_columnas'] }}" wire:key="est-campo-{{ $campo['id'] }}">
                                    @php $fieldName = "datos_estado_actual.{$campo['nombre_campo']}"; @endphp

                                    @if($campo['tipo'] === 'text')
                                    <div class="form-floating form-floating-outline">
                                        <input type="text" class="form-control" id="ec_{{ $campo['id'] }}"
                                            wire:model.blur="{{ $fieldName }}"
                                            placeholder="{{ $campo['placeholder'] ?? $campo['etiqueta'] }}">
                                        <label for="ec_{{ $campo['id'] }}">
                                            {{ $campo['etiqueta'] }}
                                            @if($campo['unidad']) <small class="text-muted">({{ $campo['unidad'] }})</small> @endif
                                            @if($campo['obligatorio']) <span class="text-danger">*</span> @endif
                                        </label>
                                    </div>

                                    @elseif($campo['tipo'] === 'number')
                                    <div class="form-floating form-floating-outline">
                                        <input type="number" class="form-control" id="ec_{{ $campo['id'] }}"
                                            wire:model.blur="{{ $fieldName }}"
                                            @if($campo['min'] !== null) min="{{ $campo['min'] }}" @endif
                                            @if($campo['max'] !== null) max="{{ $campo['max'] }}" @endif>
                                        <label for="ec_{{ $campo['id'] }}">
                                            {{ $campo['etiqueta'] }}
                                            @if($campo['unidad']) <small class="text-muted">({{ $campo['unidad'] }})</small> @endif
                                            @if($campo['obligatorio']) <span class="text-danger">*</span> @endif
                                        </label>
                                    </div>

                                    @elseif($campo['tipo'] === 'textarea')
                                    <div class="form-floating form-floating-outline">
                                        <textarea class="form-control" id="ec_{{ $campo['id'] }}"
                                            wire:model.blur="{{ $fieldName }}"
                                            style="height:100px"
                                            placeholder="{{ $campo['placeholder'] ?? '' }}"></textarea>
                                        <label for="ec_{{ $campo['id'] }}">
                                            {{ $campo['etiqueta'] }}
                                            @if($campo['obligatorio']) <span class="text-danger">*</span> @endif
                                        </label>
                                    </div>

                                    @elseif($campo['tipo'] === 'select')
                                    <div class="form-floating form-floating-outline">
                                        <select class="form-select" id="ec_{{ $campo['id'] }}"
                                            wire:model="{{ $fieldName }}">
                                            <option value="">— Seleccionar —</option>
                                            @foreach($campo['opciones'] as $op)
                                                <option value="{{ $op }}">{{ $op }}</option>
                                            @endforeach
                                        </select>
                                        <label for="ec_{{ $campo['id'] }}">
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
                                        @foreach($campo['opciones'] as $op)
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio"
                                                name="ec_radio_{{ $campo['id'] }}"
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
                                        @foreach($campo['opciones'] as $op)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox"
                                                value="{{ $op }}"
                                                wire:model="{{ $fieldName }}">
                                            <label class="form-check-label">{{ $op }}</label>
                                        </div>
                                        @endforeach
                                    </div>

                                    @elseif($campo['tipo'] === 'date')
                                    <div class="form-floating form-floating-outline">
                                        <input type="date" class="form-control" id="ec_{{ $campo['id'] }}"
                                            wire:model="{{ $fieldName }}">
                                        <label for="ec_{{ $campo['id'] }}">
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

                    @elseif(count($secciones) > 0)
                        {{-- Secciones dinámicas de la plantilla --}}
                        @foreach($secciones as $seccion)
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
                                <div class="col-md-{{ $campo['ancho_columnas'] }}" wire:key="campo-{{ $campo['id'] }}">

                                    @php $fieldName = "datos_dinamicos.{$campo['nombre_campo']}"; @endphp

                                    {{-- TEXT --}}
                                    @if($campo['tipo'] === 'text')
                                    <div class="form-floating form-floating-outline">
                                        <input type="text" class="form-control"
                                            id="campo_{{ $campo['id'] }}"
                                            wire:model.blur="{{ $fieldName }}"
                                            wire:change="guardarEvaluacion"
                                            placeholder="{{ $campo['placeholder'] ?? $campo['etiqueta'] }}">
                                        <label for="campo_{{ $campo['id'] }}">
                                            {{ $campo['etiqueta'] }}
                                            @if($campo['unidad']) <small class="text-muted">({{ $campo['unidad'] }})</small> @endif
                                            @if($campo['obligatorio']) <span class="text-danger">*</span> @endif
                                        </label>
                                    </div>

                                    {{-- NUMBER --}}
                                    @elseif($campo['tipo'] === 'number')
                                    <div class="form-floating form-floating-outline">
                                        <input type="number" class="form-control"
                                            id="campo_{{ $campo['id'] }}"
                                            wire:model.blur="{{ $fieldName }}"
                                            wire:change="guardarEvaluacion"
                                            @if($campo['min'] !== null) min="{{ $campo['min'] }}" @endif
                                            @if($campo['max'] !== null) max="{{ $campo['max'] }}" @endif>
                                        <label for="campo_{{ $campo['id'] }}">
                                            {{ $campo['etiqueta'] }}
                                            @if($campo['unidad']) <small class="text-muted">({{ $campo['unidad'] }})</small> @endif
                                            @if($campo['obligatorio']) <span class="text-danger">*</span> @endif
                                        </label>
                                    </div>

                                    {{-- TEXTAREA --}}
                                    @elseif($campo['tipo'] === 'textarea')
                                    <div class="form-floating form-floating-outline">
                                        <textarea class="form-control"
                                            id="campo_{{ $campo['id'] }}"
                                            wire:model.blur="{{ $fieldName }}"
                                            wire:change="guardarEvaluacion"
                                            style="height:100px"
                                            placeholder="{{ $campo['placeholder'] ?? '' }}"></textarea>
                                        <label for="campo_{{ $campo['id'] }}">
                                            {{ $campo['etiqueta'] }}
                                            @if($campo['obligatorio']) <span class="text-danger">*</span> @endif
                                        </label>
                                    </div>

                                    {{-- SELECT --}}
                                    @elseif($campo['tipo'] === 'select')
                                    <div class="form-floating form-floating-outline">
                                        <select class="form-select"
                                            id="campo_{{ $campo['id'] }}"
                                            wire:model="{{ $fieldName }}"
                                            wire:change="guardarEvaluacion">
                                            <option value="">— Seleccionar —</option>
                                            @foreach($campo['opciones'] as $op)
                                                <option value="{{ $op }}">{{ $op }}</option>
                                            @endforeach
                                        </select>
                                        <label for="campo_{{ $campo['id'] }}">
                                            {{ $campo['etiqueta'] }}
                                            @if($campo['obligatorio']) <span class="text-danger">*</span> @endif
                                        </label>
                                    </div>

                                    {{-- RADIO --}}
                                    @elseif($campo['tipo'] === 'radio')
                                    <div>
                                        <label class="form-label small fw-semibold">
                                            {{ $campo['etiqueta'] }}
                                            @if($campo['obligatorio']) <span class="text-danger">*</span> @endif
                                        </label>
                                        @foreach($campo['opciones'] as $op)
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio"
                                                name="radio_{{ $campo['id'] }}"
                                                value="{{ $op }}"
                                                wire:model="{{ $fieldName }}"
                                                wire:change="guardarEvaluacion">
                                            <label class="form-check-label">{{ $op }}</label>
                                        </div>
                                        @endforeach
                                    </div>

                                    {{-- CHECKBOX --}}
                                    @elseif($campo['tipo'] === 'checkbox')
                                    <div>
                                        <label class="form-label small fw-semibold">
                                            {{ $campo['etiqueta'] }}
                                            @if($campo['obligatorio']) <span class="text-danger">*</span> @endif
                                        </label>
                                        @foreach($campo['opciones'] as $op)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox"
                                                value="{{ $op }}"
                                                wire:model="{{ $fieldName }}"
                                                wire:change="guardarEvaluacion">
                                            <label class="form-check-label">{{ $op }}</label>
                                        </div>
                                        @endforeach
                                    </div>

                                    {{-- RANGE --}}
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
                                            wire:model="{{ $fieldName }}"
                                            wire:change="guardarEvaluacion">
                                        <div class="d-flex justify-content-between">
                                            <small class="text-muted">{{ $campo['min'] ?? 0 }}</small>
                                            <small class="text-muted">{{ $campo['max'] ?? 10 }}</small>
                                        </div>
                                    </div>

                                    {{-- DATE --}}
                                    @elseif($campo['tipo'] === 'date')
                                    <div class="form-floating form-floating-outline">
                                        <input type="date" class="form-control"
                                            id="campo_{{ $campo['id'] }}"
                                            wire:model="{{ $fieldName }}"
                                            wire:change="guardarEvaluacion">
                                        <label for="campo_{{ $campo['id'] }}">
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

                    @else
                        {{-- Fallback: sin plantilla, campos genéricos --}}
                        <div class="alert alert-warning">
                            <i class="ri ri-alert-line me-2"></i>
                            Esta especialidad no tiene plantilla configurada. Se muestran campos genéricos.
                        </div>
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="form-floating form-floating-outline">
                                    <textarea class="form-control" wire:model.blur="datos_dinamicos.enfermedad_actual"
                                        wire:change="guardarEvaluacion" id="ea" style="height:120px"></textarea>
                                    <label for="ea">Enfermedad Actual</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-floating form-floating-outline">
                                    <textarea class="form-control" wire:model.blur="datos_dinamicos.examen_fisico"
                                        wire:change="guardarEvaluacion" id="ef" style="height:120px"></textarea>
                                    <label for="ef">Examen Físico</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-floating form-floating-outline">
                                    <textarea class="form-control" wire:model.blur="datos_dinamicos.conclusion"
                                        wire:change="guardarEvaluacion" id="conc" style="height:100px"></textarea>
                                    <label for="conc">Conclusión</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-floating form-floating-outline">
                                    <textarea class="form-control" wire:model.blur="datos_dinamicos.observaciones"
                                        wire:change="guardarEvaluacion" id="obs" style="height:100px"></textarea>
                                    <label for="obs">Observaciones</label>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Diagnósticos CIE-10 --}}
                    <hr class="my-4">
                    <h6 class="mb-3"><i class="ri ri-stethoscope-line me-2 text-primary"></i>Diagnósticos CIE-10</h6>
                    <div class="row g-3 mb-3">
                        <div class="col-12">
                            <div class="form-floating form-floating-outline">
                                <input type="text" class="form-control"
                                    wire:model.live.debounce.300ms="busqueda_diagnostico"
                                    id="busq_diag" placeholder="Buscar por código o nombre...">
                                <label for="busq_diag">Buscar Diagnóstico</label>
                            </div>
                            @if(count($diagnosticos_disponibles) > 0)
                            <div class="list-group mt-2">
                                @foreach($diagnosticos_disponibles as $diag)
                                <button type="button" class="list-group-item list-group-item-action"
                                    wire:click="agregarDiagnostico({{ $diag['id'] }}, 'secundario')">
                                    <strong>{{ $diag['codigo'] }}</strong> — {{ $diag['nombre'] }}
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
                                                <input type="text" class="form-control" wire:model="nuevo_diagnostico_codigo" placeholder="Código (ej: J00)">
                                            </div>
                                            <div class="col-md-7">
                                                <input type="text" class="form-control" wire:model="nuevo_diagnostico_nombre" placeholder="Nombre del diagnóstico">
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
                    <div class="row g-2">
                        @foreach($diagnosticos_agregados as $index => $diag)
                        <div class="col-12">
                            <div class="card border-start border-primary border-3">
                                <div class="card-body p-3 d-flex justify-content-between align-items-start">
                                    <div>
                                        <span class="badge bg-label-primary me-2">{{ $diag['codigo'] }}</span>
                                        <strong>{{ $diag['nombre'] }}</strong>
                                        <div class="btn-group btn-group-sm mt-2">
                                            <button class="btn {{ $diag['tipo'] === 'principal' ? 'btn-primary' : 'btn-outline-primary' }}"
                                                wire:click="cambiarTipoDiagnostico({{ $index }}, 'principal')">Principal</button>
                                            <button class="btn {{ $diag['tipo'] === 'secundario' ? 'btn-secondary' : 'btn-outline-secondary' }}"
                                                wire:click="cambiarTipoDiagnostico({{ $index }}, 'secundario')">Secundario</button>
                                        </div>
                                    </div>
                                    <button class="btn btn-sm btn-icon btn-outline-danger"
                                        wire:click="eliminarDiagnostico({{ $index }})">
                                        <i class="ri ri-delete-bin-line"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
                @endif

                {{-- PASO: ESTUDIOS --}}
                @if($pasoKey === 'estudios')
                <div>
                    <h5 class="mb-3"><i class="ri ri-test-tube-line me-2 text-primary"></i>Laboratorios y Estudios</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="form-floating form-floating-outline">
                                <select class="form-select" wire:model="tipo_estudio" id="tipo_est">
                                    <option value="imagen">Imagen</option>
                                    <option value="laboratorio">Laboratorio</option>
                                    <option value="otros">Otros</option>
                                </select>
                                <label for="tipo_est">Tipo</label>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-floating form-floating-outline">
                                <input type="text" class="form-control" wire:model="nombre_estudio" id="nom_est">
                                <label for="nom_est">Nombre del Estudio</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-floating form-floating-outline">
                                <textarea class="form-control" wire:model="indicaciones_estudio" id="ind_est" style="height:80px"></textarea>
                                <label for="ind_est">Indicaciones</label>
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
                    <div class="row g-2">
                        @foreach($estudios as $index => $estudio)
                        <div class="col-12">
                            <div class="card estudio-item">
                                <div class="card-body p-3 d-flex justify-content-between align-items-start">
                                    <div>
                                        <span class="badge bg-label-primary me-2">{{ ucfirst($estudio['tipo_estudio']) }}</span>
                                        <strong>{{ $estudio['nombre_estudio'] }}</strong>
                                        @if($estudio['indicaciones'])
                                            <p class="mb-0 small text-muted mt-1">{{ $estudio['indicaciones'] }}</p>
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
                        @endforeach
                    </div>
                    @endif
                </div>
                @endif

                {{-- PASO: TRATAMIENTOS --}}
                @if($pasoKey === 'tratamientos')
                <div>
                    <h5 class="mb-3"><i class="ri ri-capsule-line me-2 text-primary"></i>Tratamiento</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="form-floating form-floating-outline">
                                <input type="text" class="form-control" wire:model="medicamento" id="med">
                                <label for="med">Medicamento</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating form-floating-outline">
                                <textarea class="form-control" wire:model="indicaciones_tratamiento" id="ind_trat" style="height:56px"></textarea>
                                <label for="ind_trat">Indicaciones</label>
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
                    <div class="row g-2">
                        @foreach($tratamientos as $index => $trat)
                        <div class="col-12">
                            <div class="card tratamiento-item">
                                <div class="card-body p-3 d-flex justify-content-between align-items-start">
                                    <div>
                                        <strong>{{ $trat['medicamento'] }}</strong>
                                        <p class="mb-0 small text-muted">{{ $trat['indicaciones'] }}</p>
                                    </div>
                                    <button class="btn btn-sm btn-icon btn-outline-danger"
                                        wire:click="eliminarTratamiento({{ $index }})"
                                        wire:confirm="¿Eliminar este tratamiento?">
                                        <i class="ri ri-delete-bin-line"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
                @endif

                {{-- PASO: REPOSO --}}
                @if($pasoKey === 'reposo')
                <div>
                    <h5 class="mb-3"><i class="ri ri-hotel-bed-line me-2 text-primary"></i>Reposo Médico</h5>
                    <div class="form-check form-switch mb-4">
                        <input class="form-check-input" type="checkbox" wire:model.live="requiere_reposo" id="req_reposo">
                        <label class="form-check-label" for="req_reposo">¿Requiere reposo médico?</label>
                    </div>

                    @if($requiere_reposo)
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="form-floating form-floating-outline">
                                <textarea class="form-control" wire:model="motivo_reposo" id="mot_rep" style="height:100px"></textarea>
                                <label for="mot_rep">Motivo del Reposo <span class="text-danger">*</span></label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating form-floating-outline">
                                <input type="number" class="form-control" wire:model.live="dias_reposo" id="dias_rep" min="1" max="365">
                                <label for="dias_rep">Días de Reposo <span class="text-danger">*</span></label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating form-floating-outline">
                                <input type="date" class="form-control" wire:model.live="fecha_inicio_reposo" id="fi_rep">
                                <label for="fi_rep">Fecha Inicio <span class="text-danger">*</span></label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating form-floating-outline">
                                <input type="date" class="form-control" wire:model="fecha_fin_reposo" id="ff_rep" readonly>
                                <label for="ff_rep">Fecha Fin (calculada)</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-floating form-floating-outline">
                                <textarea class="form-control" wire:model="observaciones_reposo" id="obs_rep" style="height:80px"></textarea>
                                <label for="obs_rep">Observaciones</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <button class="btn btn-primary" wire:click="guardarReposo">
                                <i class="ri ri-save-line me-1"></i>Guardar Reposo
                            </button>
                        </div>
                    </div>
                    @endif
                </div>
                @endif

            </div>{{-- /card-body --}}

            {{-- ── FOOTER NAVEGACIÓN ────────────────────────────────────────── --}}
            <div class="card-footer bg-transparent border-0 p-4 pt-0">
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

                    @if($pasoActual < $totalPasos - 1)
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
        </div>{{-- /card --}}
        @endif
    </div>{{-- /proceso-shell --}}

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.45.1/dist/apexcharts.min.js"></script>
    <script>
        let chartT, chartP, chartI;

        document.addEventListener('livewire:initialized', () => {
            @if(count($historial_signos) > 0)
            renderCharts(@json($historial_signos));
            @endif

            Livewire.on('chartsUpdated', (e) => {
                const d = e[0] || e;
                [chartT, chartP, chartI].forEach(c => c?.destroy());
                renderCharts(d.historial);
            });

            Livewire.on('notify', (e) => {
                const d = e[0] || e;
                const type = d.type === 'success' ? 'success' : 'danger';
                const container = document.querySelector('.toast-container') || (() => {
                    const el = document.createElement('div');
                    el.className = 'toast-container position-fixed top-0 end-0 p-3';
                    document.body.appendChild(el);
                    return el;
                })();
                container.insertAdjacentHTML('beforeend', `
                    <div class="toast show bg-${type} text-white" role="alert">
                        <div class="toast-body d-flex justify-content-between align-items-center">
                            ${d.message}
                            <button type="button" class="btn-close btn-close-white ms-2" data-bs-dismiss="toast"></button>
                        </div>
                    </div>`);
                setTimeout(() => container.lastElementChild?.remove(), 3000);
            });
        });

        function renderCharts(h) {
            if (!h?.length) return;
            const labels = h.map(r => new Date(r.created_at).toLocaleDateString('es-ES', {day:'2-digit',month:'2-digit'}));
            const base = { chart: { height: 220, toolbar: { show: false } }, xaxis: { categories: labels } };

            chartT = new ApexCharts(document.querySelector('#chartTemperatura'), {
                ...base, chart: { ...base.chart, type: 'area' },
                series: [{ name: 'Temp °C', data: h.map(r => parseFloat(r.temperatura) || 0) }],
                colors: ['#ff6b6b'], stroke: { curve: 'smooth', width: 2 },
                fill: { type: 'gradient', gradient: { opacityFrom: .6, opacityTo: .1 } },
                yaxis: { min: 34, max: 41 },
            });
            chartT.render();

            chartP = new ApexCharts(document.querySelector('#chartPresion'), {
                ...base, chart: { ...base.chart, type: 'line' },
                series: [
                    { name: 'Sistólica',  data: h.map(r => parseFloat(r.presion_arterial_sistolica) || 0) },
                    { name: 'Diastólica', data: h.map(r => parseFloat(r.presion_arterial_diastolica) || 0) },
                ],
                colors: ['#4c6ef5', '#15aabf'], stroke: { curve: 'smooth', width: 2 },
            });
            chartP.render();

            chartI = new ApexCharts(document.querySelector('#chartIMC'), {
                ...base, chart: { ...base.chart, type: 'area' },
                series: [{ name: 'IMC', data: h.map(r => parseFloat(r.imc) || 0) }],
                colors: ['#fab005'], stroke: { curve: 'smooth', width: 2 },
                fill: { type: 'gradient', gradient: { opacityFrom: .5, opacityTo: .1 } },
            });
            chartI.render();
        }
    </script>
    @endpush
</div>
