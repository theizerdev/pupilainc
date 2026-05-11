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

        /* Estilos para el modal de notas */
        .notas-modal {
            animation: fadeIn 0.3s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }
        .nota-history-item {
            border-left: 3px solid #3B82F6;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            transition: all 0.2s ease;
        }
        .nota-history-item:hover {
            transform: translateX(5px);
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .btn-notas {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            transition: all 0.3s ease;
        }
        .btn-notas:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
            color: white;
        }
        .nota-indicator {
            position: relative;
        }
        .nota-indicator::after {
            content: '';
            position: absolute;
            top: -5px;
            right: -5px;
            width: 10px;
            height: 10px;
            background: #ff6b6b;
            border-radius: 50%;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        /* Estilos para el timer de gotas */
        .timer-gotas-container {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 6px 12px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 8px;
            border: 2px solid #28a745;
            transition: all 0.3s ease;
        }
        .timer-gotas-container.timer-urgent {
            border-color: #dc3545;
            background: linear-gradient(135deg, #fff5f5 0%, #ffe5e5 100%);
            animation: pulse-border 1s infinite;
        }
        @keyframes pulse-border {
            0%, 100% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.4); }
            50% { box-shadow: 0 0 0 6px rgba(220, 53, 69, 0); }
        }
        .timer-gotas-container i {
            font-size: 1.2rem;
            color: #28a745;
        }
        .timer-gotas-container.timer-urgent i {
            color: #dc3545;
        }
        .timer-label {
            font-size: 0.8rem;
            font-weight: 600;
            color: #495057;
        }
        .timer-value {
            font-size: 1.1rem;
            font-weight: 700;
            font-family: 'Courier New', monospace;
            color: #28a745;
        }
        .timer-gotas-container.timer-urgent .timer-value {
            color: #dc3545;
        }
        .timer-progress-mini {
            width: 80px;
            height: 4px;
            background: #e9ecef;
            border-radius: 2px;
            overflow: hidden;
            margin-left: 4px;
        }
        .timer-progress-bar-mini {
            height: 100%;
            transition: width 0.3s ease, background 0.3s ease;
        }
    </style>
    @endpush

    <div class="proceso-shell" wire:poll.10s>

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
                    <select class="form-select" id="nuevoEstado" wire:model.change="nuevoEstado">
                        <option value="">Cambiar estado...</option>
                        @foreach($estadosConfig as $estado)
                            @if($estado['activo'])
                            <option value="{{ $estado['key'] }}">
                                {{ $estado['nombre'] }}
                            </option>
                            @endif
                        @endforeach
                    </select>
                    <label for="nuevoEstado">Estado</label>
                </div>
                <button class="btn btn-primary" wire:click="cambiarEstadoConsulta"
                    @if(empty($nuevoEstado)) disabled @endif>
                    <i class="ri ri-check-line me-1"></i>Actualizar
                </button>
                <button class="btn btn-notas {{ count($notasExistentes) > 0 ? 'nota-indicator' : '' }}"
                    wire:click="abrirModalNotas"
                    data-bs-toggle="modal"
                    data-bs-target="#modalNotasConsulta">
                    <i class="ri ri-sticky-note-line me-1"></i>
                    Notas
                    @if(count($notasExistentes) > 0)
                        <span class="badge bg-danger ms-1">{{ count($notasExistentes) }}</span>
                    @endif
                </button>
                <a href="{{ route('admin.gestion.consultas.index') }}" class="btn btn-outline-secondary">
                    <i class="ri ri-arrow-left-line me-1"></i>Volver
                </a>
            </div>
        </div>

        {{-- ── WIZARD POR PASOS (solo si la plantilla tiene usar_wizard_en_consultorio = true) ── --}}
        @if(($plantilla['usar_wizard_en_consultorio'] ?? false) && $consulta->estado === 'en_consultorio')
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent border-0">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="ri ri-steps-line me-2 text-primary"></i>
                        <strong>Wizard de Consulta</strong>
                    </h6>
                    <span class="badge bg-primary">
                        Paso {{ $pasoActualIndex + 1 }} de {{ $totalPasos }}
                    </span>
                </div>

                {{-- Barra de progreso del wizard --}}
                <div class="mt-3">
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-primary"
                             role="progressbar"
                             style="width: {{ $totalPasos > 0 ? (($pasoActualIndex + 1) / $totalPasos * 100) : 0 }}%"
                             aria-valuenow="{{ $pasoActualIndex + 1 }}"
                             aria-valuemin="0"
                             aria-valuemax="{{ $totalPasos }}">
                        </div>
                    </div>
                </div>

                {{-- Pasos del wizard --}}
                <div class="mt-3">
                    <div class="d-flex justify-content-between">
                        @foreach($pasosActivos as $index => $paso)
                        <div class="text-center" style="flex: 1;">
                            <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-2"
                                 style="width: 40px; height: 40px;
                                        background: {{ $index <= $pasoActualIndex ? 'var(--bs-primary)' : '#e9ecef' }};
                                        color: {{ $index <= $pasoActualIndex ? 'white' : '#6c757d' }};
                                        font-weight: bold; font-size: 0.9rem;">
                                @if($index < $pasoActualIndex)
                                    <i class="ri ri-check-line"></i>
                                @else
                                    {{ $index + 1 }}
                                @endif
                            </div>
                            <div class="small" style="font-size: 0.75rem; color: {{ $index <= $pasoActualIndex ? 'var(--bs-primary)' : '#6c757d' }};">
                                {{ $paso['nombre'] }}
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="card-body">
                {{-- Contenido del paso actual --}}
                @if($pasoActualData)
                @php
                    $pasoKey = $pasoActualData['key'] ?? '';
                @endphp
                <div class="mb-3">
                    <h5 class="mb-3">
                        <i class="{{ $pasoActualData['icono'] ?? 'ri ri-file-line' }} me-2" style="color: var(--bs-primary)"></i>
                        {{ $pasoActualData['nombre'] }}
                    </h5>

                    {{-- Renderizar contenido del paso actual --}}
                    @if($pasoKey === 'signos_vitales')
                        @includeWhen($historial_signos && count($historial_signos) > 0, 'livewire.admin.consulta.partials.paso-signos-vitales', ['historial_signos' => $historial_signos])
                        @if(!isset($historial_signos) || count($historial_signos ?? []) == 0)
                            <div class="alert alert-info">
                                <i class="ri ri-information-line me-2"></i>
                                No hay registros de signos vitales. Registre los signos vitales del paciente.
                            </div>
                        @endif
                    @elseif($pasoKey === 'cuestionario')
                        @include('livewire.admin.consulta.partials.paso-cuestionario')
                    @elseif($pasoKey === 'evaluacion')
                        @include('livewire.admin.consulta.partials.paso-evaluacion')
                    @elseif($pasoKey === 'estudios')
                        <div class="alert alert-info">
                            <i class="ri ri-information-line me-2"></i>
                            Sección de estudios en desarrollo.
                        </div>
                    @elseif($pasoKey === 'tratamiento')
                        <div class="alert alert-info">
                            <i class="ri ri-information-line me-2"></i>
                            Sección de tratamiento en desarrollo.
                        </div>
                    @elseif($pasoKey === 'reposo')
                        <div class="alert alert-info">
                            <i class="ri ri-information-line me-2"></i>
                            Sección de reposo en desarrollo.
                        </div>
                    @endif
                </div>
                @endif

                {{-- Botones de navegación del wizard --}}
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
                        @else
                            <button class="btn btn-success" wire:click="finalizarConsulta">
                                <i class="ri ri-check-line me-1"></i>Finalizar Consulta
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- ── FORMULARIO DEL ESTADO ACTUAL (cuando usar_wizard_en_consultorio = false) ────────────────────────────────── --}}
        @if(!($plantilla['usar_wizard_en_consultorio'] ?? false) || $consulta->estado !== 'en_consultorio')



        @if($formularioEstadoActual && count($formularioEstadoActual['secciones']) > 0)
        <div class="card border-0 shadow-sm mb-4 border-start border-4"
             style="border-color: {{ \App\Models\Consulta::ESTADO_COLORES[$consulta->estado] ?? '#3B82F6' }} !important">
            <div class="card-header bg-transparent border-0 d-flex align-items-center justify-content-between">
                <h6 class="mb-0">
                    <span class="badge me-2" style="background-color: {{ \App\Models\Consulta::ESTADO_COLORES[$consulta->estado] ?? '#3B82F6' }}">
                        {{ $consulta->estado_label }}
                    </span>
                    Formulario del Estado Actual
                </h6>
                <div class="d-flex align-items-center gap-2">
                    @if($consulta->estado === \App\Models\Consulta::ESTADO_EN_GOTAS)
                        @php
                            $gotaAplicada = $consulta->gotasAplicadas()
                                ->where('estado', 'aplicada')
                                ->latest('updated_at')
                                ->first();
                        @endphp

                        @if($gotaAplicada && $gotaAplicada->tiempo_espera)
                            @php
                                $tiempoEspera = $gotaAplicada->tiempo_espera;
                                $fechaInicio = $gotaAplicada->updated_at ?? $gotaAplicada->created_at;
                                $segundosTotales = $tiempoEspera * 60;
                                $segundosTranscurridos = 0;
                                if ($fechaInicio) {
                                    $fechaInicioCarbon = \Carbon\Carbon::parse($fechaInicio);
                                    $segundosTranscurridos = $fechaInicioCarbon->gt(now())
                                        ? 0
                                        : $fechaInicioCarbon->diffInSeconds(now());
                                }
                                $segundosRestantes = max(0, $segundosTotales - $segundosTranscurridos);
                                $minutosRestantes = floor($segundosRestantes / 60);
                                $segundosFormato = $segundosRestantes % 60;
                                $porcentaje = $segundosTotales > 0 ? min(100, ($segundosTranscurridos / $segundosTotales) * 100) : 100;
                                $urgente = $segundosRestantes <= 300;
                            @endphp

                            <div class="timer-gotas-container {{ $urgente ? 'timer-urgent' : '' }}"
                                 wire:poll.10s="verificarDilatacionExpirada">
                                <i class="ri ri-timer-flash-line"></i>
                                <span class="timer-label">Dilatación:</span>
                                <span class="timer-value">
                                    {{ $minutosRestantes }}:{{ str_pad($segundosFormato, 2, '0', STR_PAD_LEFT) }}
                                </span>
                                <div class="timer-progress-mini">
                                    <div class="timer-progress-bar-mini"
                                         style="width: {{ $porcentaje }}%;
                                                background: {{ $urgente ? '#dc3545' : ($porcentaje > 75 ? '#ffc107' : '#28a745') }}">
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-info mb-0 py-2 px-3" style="font-size: 0.85rem;">
                                <i class="ri ri-information-line me-1"></i>
                                <strong>Sin gotas aplicadas:</strong> Seleccione el tipo de gota y tiempo de espera en el formulario y guarde para iniciar el timer.
                            </div>
                        @endif
                    @endif

                    <button class="btn btn-sm btn-primary" wire:click="guardarDatosEstado">
                        <i class="ri ri-save-line me-1"></i>Guardar
                    </button>
                </div>
            </div>

            <div class="card-body">
                {{-- Renderizar secciones y campos del formulario --}}
                @foreach($formularioEstadoActual['secciones'] as $seccion)
                <div class="mb-4">
                    {{-- Header de la sección --}}
                    <div class="seccion-header mb-3" style="--seccion-color: {{ $seccion['color'] ?? '#3B82F6' }}">
                        <h6 class="mb-1">
                            <i class="{{ $seccion['icono'] ?? 'ri ri-folder-line' }} me-2" style="color: {{ $seccion['color'] ?? '#3B82F6' }}"></i>
                            {{ $seccion['nombre'] }}
                        </h6>
                    </div>

                    {{-- Campos de la sección --}}
                    @if(count($seccion['campos']) > 0)
                    <div class="row g-3">
                        @foreach($seccion['campos'] as $campo)
                        <div class="col-md-{{ $campo['ancho_columnas'] ?? 12 }}">
                            <label class="form-label">
                                {{ $campo['etiqueta'] }}
                                @if($campo['obligatorio'])
                                    <span class="text-danger">*</span>
                                @endif
                                @if($campo['unidad'])
                                    <small class="text-muted">({{ $campo['unidad'] }})</small>
                                @endif
                            </label>

                            {{-- Input según tipo de campo --}}
                            @switch($campo['tipo'])
                                @case('text')
                                    <input type="text" class="form-control"
                                           wire:model="datos_estado_actual.{{ $campo['nombre_campo'] }}"
                                           placeholder="{{ $campo['placeholder'] ?? '' }}">
                                    @break

                                @case('textarea')
                                    <textarea class="form-control" rows="3"
                                              wire:model="datos_estado_actual.{{ $campo['nombre_campo'] }}"
                                              placeholder="{{ $campo['placeholder'] ?? '' }}"></textarea>
                                    @break

                                @case('number')
                                    <input type="number" class="form-control"
                                           wire:model="datos_estado_actual.{{ $campo['nombre_campo'] }}"
                                           min="{{ $campo['min'] }}"
                                           max="{{ $campo['max'] }}"
                                           step="0.01"
                                           placeholder="{{ $campo['placeholder'] ?? '' }}">
                                    @break

                                @case('select')
                                    <select class="form-select" wire:model="datos_estado_actual.{{ $campo['nombre_campo'] }}">
                                        <option value="">Seleccionar...</option>
                                        @foreach(($campo['opciones'] ?? []) as $opcion)
                                            <option value="{{ $opcion }}">{{ $opcion }}</option>
                                        @endforeach
                                    </select>
                                    @break

                                @case('checkbox')
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input"
                                               wire:model="datos_estado_actual.{{ $campo['nombre_campo'] }}"
                                               id="campo_{{ $campo['id'] }}">
                                        <label class="form-check-label" for="campo_{{ $campo['id'] }}">
                                            {{ $campo['etiqueta'] }}
                                        </label>
                                    </div>
                                    @break

                                @case('date')
                                    <input type="date" class="form-control"
                                           wire:model="datos_estado_actual.{{ $campo['nombre_campo'] }}">
                                    @break

                                @default
                                    <input type="text" class="form-control"
                                           wire:model="datos_estado_actual.{{ $campo['nombre_campo'] }}"
                                           placeholder="{{ $campo['placeholder'] ?? '' }}">
                            @endswitch

                            @error('datos_estado_actual.' . $campo['nombre_campo'])
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="alert alert-light">
                        <i class="ri ri-information-line me-2"></i>
                        No hay campos configurados para esta sección.
                    </div>
                    @endif
                </div>
                @endforeach
            </div>

        </div>

        @endif
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

    {{-- Modal de Notas de Consulta --}}
    <div wire:ignore.self class="modal fade" id="modalNotasConsulta" tabindex="-1" aria-labelledby="modalNotasLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg notas-modal">
                {{-- Header con gradiente --}}
                <div class="modal-header border-0" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 0.5rem 0.5rem 0 0;">
                    <div class="d-flex align-items-center">
                        <div class="me-3" style="font-size: 2rem;">
                            <i class="ri ri-sticky-note-2-line"></i>
                        </div>
                        <div>
                            <h5 class="modal-title mb-1" id="modalNotasLabel">
                                <strong>Notas de Consulta</strong>
                            </h5>
                            <small class="opacity-75">
                                <i class="ri ri-user-line me-1"></i>{{ $consulta->paciente->nombre_completo }}
                            </small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" wire:click="cerrarModalNotas"></button>
                </div>

                <div class="modal-body p-4">
                    {{-- Historial de notas existentes --}}
                    @if(count($notasExistentes) > 0)
                    <div class="mb-4">
                        <h6 class="mb-3">
                            <i class="ri ri-history-line me-2 text-primary"></i>
                            <strong>Historial de Notas</strong>
                            <span class="badge bg-primary ms-2">{{ count($notasExistentes) }}</span>
                        </h6>
                        <div class="vstack gap-3" style="max-height: 300px; overflow-y: auto;">
                            @foreach($notasExistentes as $index => $nota)
                            <div class="nota-history-item p-3 rounded">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-primary bg-opacity-10 p-2 me-2">
                                            <i class="ri ri-user-3-fill text-primary"></i>
                                        </div>
                                        <div>
                                            <small class="fw-bold d-block">{{ $nota['usuario'] }}</small>
                                            <small class="text-muted">
                                                <i class="ri ri-time-line me-1"></i>{{ $nota['fecha'] }}
                                            </small>
                                        </div>
                                    </div>
                                    @if($index === 0)
                                    <span class="badge bg-success">
                                        <i class="ri ri-star-fill me-1"></i>Más reciente
                                    </span>
                                    @endif
                                </div>
                                <p class="mb-0 text-dark" style="white-space: pre-wrap;">{{ $nota['nota'] }}</p>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    <hr class="my-4">
                    @endif

                    {{-- Formulario para nueva nota --}}
                    <div>
                        <h6 class="mb-3">
                            <i class="ri ri-edit-line me-2 text-success"></i>
                            <strong>Agregar Nueva Nota</strong>
                        </h6>
                        <div class="mb-3">
                            <textarea class="form-control"
                                      wire:model="notaConsulta"
                                      rows="5"
                                      placeholder="Escribe aquí tu nota sobre la consulta..."
                                      style="resize: vertical; border: 2px solid #e9ecef; transition: all 0.3s;"
                                      onfocus="this.style.borderColor='#667eea'"
                                      onblur="this.style.borderColor='#e9ecef'"></textarea>
                            <div class="form-text">
                                <i class="ri ri-information-line me-1"></i>
                                Máximo 2000 caracteres. La nota se guardará con fecha y hora actual.
                            </div>
                            @error('notaConsulta')
                                <span class="text-danger small">
                                    <i class="ri ri-error-warning-line me-1"></i>{{ $message }}
                                </span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0 bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" wire:click="cerrarModalNotas">
                        <i class="ri ri-close-line me-1"></i>Cancelar
                    </button>
                    <button type="button" class="btn text-white"
                            style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);"
                            wire:click="guardarNotaConsulta"
                            wire:loading.attr="disabled">
                        <span wire:loading.remove>
                            <i class="ri ri-save-line me-1"></i>Guardar Nota
                        </span>
                        <span wire:loading>
                            <i class="ri ri-loader-4-line me-1" wire:loading.class="ri-spin"></i>Guardando...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
