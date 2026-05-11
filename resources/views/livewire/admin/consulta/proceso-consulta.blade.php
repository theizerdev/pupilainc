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
                <a href="{{ route('admin.gestion.consultas.index') }}" class="btn btn-outline-secondary">
                    <i class="ri ri-arrow-left-line me-1"></i>Volver
                </a>
            </div>
        </div>

        {{-- ── FORMULARIO DEL ESTADO ACTUAL ─────────────────────────────────── --}}
        {{-- Solo mostrar si NO estamos usando wizard o no estamos en consultorio --}}
        @if($formularioEstadoActual && count($formularioEstadoActual['secciones']) > 0 && !$mostrarPorPasos)
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
            {{-- ── PASOS DE CONSULTA (En Consultorio) ────────────────────────── --}}
            @if($mostrarPorPasos && count($pasosActivos) > 0)
            <div class="mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent border-0">
                        <h6 class="mb-0">
                            <i class="ri ri-list-check me-2 text-primary"></i>
                            Pasos de la Consulta
                        </h6>
                    </div>
                    <div class="card-body">
                        {{-- Navegación de pasos --}}
                        <div class="d-flex flex-wrap align-items-center justify-content-center gap-3 mb-4">
                            @foreach($pasosActivos as $index => $paso)
                            <div class="text-center">
                                <button class="proceso-step @if($index < $pasoActualIndex) is-done @elseif($index == $pasoActualIndex) is-current @endif"
                                    wire:click="irPaso({{ $index }})">
                                    <i class="ri {{ $paso['icono'] ?? 'ri ri-checkbox-circle-line' }}"></i>
                                </button>
                                <div class="proceso-step-label mt-1">{{ $paso['nombre'] }}</div>
                            </div>
                            @if($index < $pasosActivos->count() - 1)
                                <div class="text-muted">→</div>
                            @endif
                            @endforeach
                        </div>

                        {{-- Contenido del paso actual --}}
                        @if($pasoActualData)
                        <div>

                            <div>
                                @include('livewire.admin.consulta.partials.paso-' . $pasoActualData['key'], [
                                    'haySiguientePaso' => $haySiguientePaso,
                                    'esUltimoPaso' => $esUltimoPaso,
                                    'pasoSiguiente' => $pasoSiguiente,
                                ])
                            </div>
                        </div>
                        @endif
                    </div>
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
</div>
