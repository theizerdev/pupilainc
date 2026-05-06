<div>
    @section('title', 'Plantilla de Consulta')

    @push('styles')
    <style>
        /* ── Hero header ─────────────────────────────────────── */
        .plantilla-hero {
            background: linear-gradient(135deg, var(--hero-color, #3B82F6) 0%, var(--hero-color-2, #6366F1) 100%);
            color: #fff; border-radius: .75rem; padding: 1.5rem 1.75rem;
        }
        .plantilla-hero .hero-icon {
            width: 56px; height: 56px; border-radius: 14px;
            background: rgba(255,255,255,.18); display: flex;
            align-items: center; justify-content: center; font-size: 1.5rem;
        }
        .plantilla-hero h4, .plantilla-hero p { color: #fff; margin: 0; }
        .plantilla-hero .hero-meta { opacity: .9; font-size: .82rem; }

        /* ── KPI cards ───────────────────────────────────────── */
        .kpi-card {
            border: 1px solid rgba(0,0,0,.06); border-radius: .65rem;
            padding: .9rem 1rem; transition: all .2s;
            display: flex; align-items: center; gap: .85rem; height: 100%;
        }
        .kpi-card:hover { box-shadow: 0 4px 14px rgba(0,0,0,.07); transform: translateY(-1px); }
        .kpi-card .kpi-icon {
            width: 42px; height: 42px; border-radius: 10px; flex: 0 0 42px;
            display: flex; align-items: center; justify-content: center; font-size: 1.1rem;
        }
        .kpi-card .kpi-value { font-size: 1.35rem; font-weight: 600; line-height: 1; }
        .kpi-card .kpi-label { font-size: .75rem; color: var(--bs-secondary-color); }

        /* ── Tabs ───────────────────────────────────────────── */
        .plantilla-tabs .nav-link { font-weight: 500; padding: .75rem 1.1rem; border: 0; color: var(--bs-secondary-color); }
        .plantilla-tabs .nav-link.active { color: var(--bs-primary); background: transparent; border-bottom: 2px solid var(--bs-primary); }
        .plantilla-tabs .nav-link i { vertical-align: -2px; }
        .plantilla-tabs .nav-link .badge { font-size: .65rem; }

        /* ── Pasos / Estados ────────────────────────────────── */
        .paso-badge, .estado-badge { cursor: pointer; transition: all .15s; user-select: none; }
        .paso-badge.activo, .estado-badge.activo { opacity: 1; }
        .paso-badge.inactivo { opacity: .4; }
        .estado-badge.inactivo { opacity: .35; }
        .paso-badge:hover, .estado-badge:hover { transform: translateY(-1px); }

        /* ── Secciones / Campos ─────────────────────────────── */
        .seccion-card { border-left: 4px solid var(--seccion-color, #3B82F6); transition: all .2s; }
        .seccion-card:hover { box-shadow: 0 4px 15px rgba(0,0,0,.08); }
        .campo-row { border-left: 3px solid #e9ecef; padding-left: 10px; transition: all .2s; }
        .campo-row:hover { border-left-color: #3B82F6; background: #f8f9ff; }
        .campo-row.inactivo { opacity: .5; }
        .tipo-badge { font-size: .7rem; }
        .estado-formulario-card { border-left: 4px solid var(--ef-color, #78909C); transition: all .2s; }
        .estado-formulario-card.abierto { box-shadow: 0 4px 15px rgba(0,0,0,.08); }

        /* ── Empty state hero ───────────────────────────────── */
        .empty-hero {
            border: 2px dashed rgba(0,0,0,.1); border-radius: .75rem;
            padding: 3rem 1.5rem; text-align: center; background: rgba(0,0,0,.02);
        }
        .empty-hero .empty-icon {
            width: 84px; height: 84px; border-radius: 50%;
            background: var(--bs-primary-bg-subtle); color: var(--bs-primary);
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 2rem; margin-bottom: 1rem;
        }

        /* ── Icon picker ────────────────────────────────────── */
        .icon-picker {
            display: grid; grid-template-columns: repeat(auto-fill, minmax(46px, 1fr));
            gap: .4rem; max-height: 200px; overflow-y: auto;
            padding: .5rem; border: 1px solid var(--bs-border-color); border-radius: .5rem;
        }
        .icon-picker button {
            background: transparent; border: 1px solid transparent;
            border-radius: .4rem; padding: .55rem 0; cursor: pointer; transition: all .15s;
            color: var(--bs-secondary-color); font-size: 1rem;
        }
        .icon-picker button:hover { background: var(--bs-primary-bg-subtle); color: var(--bs-primary); }
        .icon-picker button.selected {
            background: var(--bs-primary); color: #fff; border-color: var(--bs-primary);
        }

        /* ── Color swatches ─────────────────────────────────── */
        .color-swatches { display: flex; flex-wrap: wrap; gap: .35rem; margin-top: .4rem; }
        .color-swatches .swatch {
            width: 26px; height: 26px; border-radius: 6px; cursor: pointer;
            border: 2px solid transparent; transition: all .15s;
        }
        .color-swatches .swatch:hover { transform: scale(1.1); }
        .color-swatches .swatch.selected { border-color: var(--bs-dark); box-shadow: 0 0 0 2px #fff inset; }

        /* ── Width indicator (campo ancho_columnas) ─────────── */
        .width-indicator { display: inline-flex; gap: 2px; vertical-align: middle; }
        .width-indicator .wi-cell {
            width: 6px; height: 12px; border-radius: 1px; background: var(--bs-border-color);
        }
        .width-indicator .wi-cell.filled { background: var(--bs-primary); }

        /* ── Sticky save bar ────────────────────────────────── */
        .sticky-save-bar {
            position: sticky; bottom: 0; z-index: 10;
            background: var(--bs-body-bg); padding: .75rem 0;
            border-top: 1px solid var(--bs-border-color);
            margin-top: 1rem;
        }

        /* ── Modal backdrop ─────────────────────────────────── */
        .custom-modal-backdrop {
            position: fixed; inset: 0; background: rgba(0,0,0,.5);
            z-index: 1050; overflow-y: auto;
        }
        .custom-modal-backdrop .modal-dialog { margin: 1.75rem auto; }

    </style>
    @endpush

    <div class="container-xxl flex-grow-1 container-p-y">

        {{-- ── HEADER HERO ──────────────────────────────────────────────────── --}}
        @php
            $heroColor    = $especialidad->color ?: '#3B82F6';
            $heroColor2   = $especialidad->color ?: '#6366F1';
            $totalCampos  = collect($secciones)->sum(fn($s) => count($s['campos']));
            $totalSecAct  = collect($secciones)->where('activo', true)->count();
        @endphp
        <div class="plantilla-hero mb-4 d-flex flex-wrap align-items-center justify-content-between gap-3"
             style="--hero-color: {{ $heroColor }}; --hero-color-2: {{ $heroColor2 }}">
            <div class="d-flex align-items-center gap-3">
                <div class="hero-icon">
                    <i class="fas {{ $especialidad->icono }}"></i>
                </div>
                <div>
                    <h4 class="mb-1">Plantilla de Consulta</h4>
                    <p class="mb-1">{{ $especialidad->nombre }}</p>
                    <div class="hero-meta d-flex align-items-center gap-2 flex-wrap">
                        @if($plantilla)
                            <span class="badge bg-white text-dark">
                                <i class="ri ri-check-line me-1 text-success"></i>Plantilla activa
                            </span>
                            @if($plantilla->updated_at)
                                <span><i class="ri ri-time-line me-1"></i>Actualizada {{ $plantilla->updated_at->diffForHumans() }}</span>
                            @endif
                        @else
                            <span class="badge bg-warning text-dark">
                                <i class="ri ri-alert-line me-1"></i>Sin plantilla
                            </span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.especialidades.show', $especialidad) }}"
                   class="btn btn-light">
                    <i class="ri ri-arrow-left-line me-1"></i>Volver
                </a>
            </div>
        </div>

        {{-- ── KPIs ────────────────────────────────────────────────────────── --}}
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="kpi-card bg-white">
                    <div class="kpi-icon bg-label-primary"><i class="ri ri-layout-grid-line"></i></div>
                    <div>
                        <div class="kpi-value">{{ $totalSecAct }}<small class="text-muted fw-normal" style="font-size:.85rem"> / {{ count($secciones) }}</small></div>
                        <div class="kpi-label">Secciones activas</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="kpi-card bg-white">
                    <div class="kpi-icon bg-label-info"><i class="ri ri-input-field"></i></div>
                    <div>
                        <div class="kpi-value">{{ $totalCampos }}</div>
                        <div class="kpi-label">Campos totales</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="kpi-card bg-white">
                    <div class="kpi-icon bg-label-success"><i class="ri ri-list-check"></i></div>
                    <div>
                        @php $pasosActivos = collect($pasosHabilitados)->where('activo', true)->count(); @endphp
                        <div class="kpi-value">{{ $pasosActivos }}<small class="text-muted fw-normal" style="font-size:.85rem"> / {{ count($pasosHabilitados) }}</small></div>
                        <div class="kpi-label">Pasos activos</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="kpi-card bg-white">
                    <div class="kpi-icon bg-label-warning"><i class="ri ri-flow-chart"></i></div>
                    <div>
                        @php $estadosActivos = collect($estadosFlujo)->where('activo', true)->count(); @endphp
                        <div class="kpi-value">{{ $estadosActivos }}<small class="text-muted fw-normal" style="font-size:.85rem"> / {{ count($estadosFlujo) }}</small></div>
                        <div class="kpi-label">Estados activos</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── TABS NAV ────────────────────────────────────────────────────── --}}
        <ul class="nav nav-tabs plantilla-tabs mb-3" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" data-tab-target="#tab-config" type="button" role="tab">
                    <i class="ri ri-settings-3-line me-1"></i>Configuración general
                </button>
            </li>

            <li class="nav-item" role="presentation">
                <button class="nav-link" data-tab-target="#tab-formularios" type="button" role="tab"
                        @if(!$plantilla) disabled title="Crea la plantilla primero" @endif>
                    <i class="ri ri-node-tree me-1"></i>Formularios por estado
                    <span class="badge bg-label-secondary ms-1">{{ count($estadosFlujo) }}</span>
                </button>
            </li>
        </ul>

        <div class="tab-content">

        {{-- ════════════════════════════════════════════════════════════════ --}}
        {{-- TAB 1 · CONFIGURACIÓN GENERAL                                    --}}
        {{-- ════════════════════════════════════════════════════════════════ --}}
        <div class="tab-pane fade show active" id="tab-config" role="tabpanel">

            <div class="row g-4">

                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent border-0 d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div>
                                <h6 class="mb-0"><i class="ri ri-flow-chart me-2 text-primary"></i>Estados del flujo (Kanban)</h6>
                                <small class="text-muted">Configura los estados del flujo de consultas</small>
                            </div>
                            <button class="btn btn-sm btn-primary" wire:click="abrirModalEstado()">
                                <i class="ri ri-add-line me-1"></i>Nuevo estado
                            </button>
                        </div>
                        <div class="card-body">
                            @if(count($estadosFlujo) === 0)
                                <p class="text-muted text-center mb-0">No hay estados configurados.</p>
                            @else
                            <div class="row g-2">
                                @foreach($estadosFlujo as $ei => $estado)
                                <div class="col-12" wire:key="estado-{{ $ei }}">
                                    <div class="campo-row rounded p-2 {{ !$estado['activo'] ? 'inactivo' : '' }}">
                                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                                <span class="badge" style="background-color:{{ $estado['color'] ?? '#78909C' }};font-size:.85rem;padding:.4rem .7rem">
                                                    {{ $estado['nombre'] }}
                                                </span>
                                                <code class="small text-muted">{{ $estado['key'] }}</code>
                                                @if(!$estado['activo'])
                                                    <span class="badge bg-label-warning" style="font-size:.65rem">Inactivo</span>
                                                @endif
                                            </div>
                                            <div class="d-flex gap-1 flex-wrap">
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <button class="btn btn-xs btn-icon btn-outline-secondary"
                                                            wire:click="moverEstado({{ $ei }}, 'up')"
                                                            @if($ei === 0) disabled @endif title="Subir">
                                                        <i class="ri ri-arrow-up-s-line"></i>
                                                    </button>
                                                    <button class="btn btn-xs btn-icon btn-outline-secondary"
                                                            wire:click="moverEstado({{ $ei }}, 'down')"
                                                            @if($ei === count($estadosFlujo) - 1) disabled @endif title="Bajar">
                                                        <i class="ri ri-arrow-down-s-line"></i>
                                                    </button>
                                                </div>
                                                <button class="btn btn-xs btn-icon btn-outline-primary"
                                                        wire:click="abrirModalEstado({{ $ei }})" title="Editar">
                                                    <i class="ri ri-edit-line"></i>
                                                </button>
                                                <button class="btn btn-xs btn-icon {{ $estado['activo'] ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                                        wire:click="toggleEstado({{ $ei }})"
                                                        title="{{ $estado['activo'] ? 'Desactivar' : 'Activar' }}">
                                                    <i class="ri ri-{{ $estado['activo'] ? 'eye-off' : 'eye' }}-line"></i>
                                                </button>
                                                <button class="btn btn-xs btn-icon btn-outline-danger"
                                                        wire:click="eliminarEstado({{ $ei }})"
                                                        wire:confirm="¿Eliminar este estado?" title="Eliminar">
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
                    </div>
                </div>
            </div>

            {{-- Sticky save bar --}}
            <div class="sticky-save-bar d-flex justify-content-end">
                <button class="btn btn-primary" wire:click="guardarConfiguracion"
                        wire:loading.attr="disabled" wire:target="guardarConfiguracion">
                    <span wire:loading.remove wire:target="guardarConfiguracion">
                        <i class="ri ri-save-line me-1"></i>Guardar pasos y estados
                    </span>
                    <span wire:loading wire:target="guardarConfiguracion">
                        <span class="spinner-border spinner-border-sm me-1"></span>Guardando...
                    </span>
                </button>
            </div>
        </div>



        {{-- ════════════════════════════════════════════════════════════════ --}}
        {{-- TAB 3 · FORMULARIOS POR ESTADO                                    --}}
        {{-- ════════════════════════════════════════════════════════════════ --}}
        <div class="tab-pane fade" id="tab-formularios" role="tabpanel">
        @if($plantilla)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent border-0">
                <h6 class="mb-0"><i class="ri ri-node-tree me-2 text-primary"></i>Formularios por estado</h6>
                <small class="text-muted">Configura qué campos se capturan en cada estado del flujo</small>
            </div>
            <div class="card-body pt-0">

                @if(empty($estadosFlujo))
                    <div class="alert alert-warning mb-0">
                        <i class="ri ri-alert-line me-2"></i>
                        Primero configura los estados del flujo en la pestaña <strong>Configuración general</strong>.
                    </div>
                @else
                <div class="row g-3">
                    @foreach($estadosFlujo as $ei => $estado)
                    @php
                        $estadoKey = $estado['key'];
                        $efColor  = $estado['color'] ?? '#78909C';
                        $efLabel  = $estado['nombre'];
                        $efData   = $estadoFormularios[$estadoKey] ?? null;
                        $estaAbierto = $estadoFormularioActivo === $estadoKey;
                    @endphp
                    <div class="col-12" wire:key="ef-{{ $estadoKey }}">
                        <div class="card estado-formulario-card {{ $estaAbierto ? 'abierto' : '' }}"
                             style="--ef-color: {{ $efColor }}">

                            {{-- Cabecera del estado --}}
                            <div class="card-header bg-transparent d-flex align-items-center justify-content-between py-2"
                                 wire:click="seleccionarEstadoFormulario('{{ $estadoKey }}')" style="cursor:pointer">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge" style="background-color:{{ $efColor }}">{{ $efLabel }}</span>
                                    @if($efData)
                                        <span class="badge bg-label-secondary">
                                            {{ collect($efData['secciones'])->sum(fn($s) => count($s['campos'])) }} campos
                                        </span>
                                        @if(!$efData['activo'])
                                            <span class="badge bg-label-warning">Inactivo</span>
                                        @endif
                                    @else
                                        <span class="badge bg-label-secondary">Sin formulario</span>
                                    @endif
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    @if($efData)
                                        <button class="btn btn-xs btn-icon {{ $efData['activo'] ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                                wire:click.stop="toggleEstadoFormulario('{{ $estadoKey }}')"
                                                title="{{ $efData['activo'] ? 'Desactivar' : 'Activar' }}">
                                            <i class="ri ri-{{ $efData['activo'] ? 'eye-off' : 'eye' }}-line"></i>
                                        </button>
                                    @endif
                                    <i class="ri ri-{{ $estaAbierto ? 'arrow-up' : 'arrow-down' }}-s-line text-muted"></i>
                                </div>
                            </div>

                            {{-- Contenido expandible --}}
                            @if($estaAbierto)
                            <div class="card-body">

                                {{-- Sin formulario aún --}}
                                @if(!$efData)
                                <div class="text-center py-3">
                                    <p class="text-muted small mb-2">No hay formulario configurado para este estado.</p>
                                    <button class="btn btn-sm btn-primary"
                                            wire:click="crearOAbrirFormularioEstado('{{ $estadoKey }}')">
                                        <i class="ri ri-add-line me-1"></i>Crear Formulario
                                    </button>
                                </div>

                                {{-- Formulario existente --}}
                                @else
                                <div class="d-flex justify-content-end mb-3">
                                    <button class="btn btn-sm btn-primary"
                                            wire:click="abrirModalSeccionEstado('{{ $estadoKey }}')">
                                        <i class="ri ri-add-line me-1"></i>Nueva Sección
                                    </button>
                                </div>

                                @if(count($efData['secciones']) === 0)
                                    <p class="text-muted small text-center">Sin secciones. Agrega la primera.</p>
                                @else
                                <div class="row g-3">
                                    @foreach($efData['secciones'] as $si => $seccion)
                                    <div class="col-12" wire:key="ef-sec-{{ $seccion['id'] }}">
                                        <div class="card seccion-card {{ !$seccion['activo'] ? 'opacity-50' : '' }}"
                                             style="--seccion-color: {{ $seccion['color'] ?? '#3B82F6' }}">
                                            <div class="card-header bg-transparent d-flex align-items-center justify-content-between py-2">
                                                <div class="d-flex align-items-center gap-2">
                                                    <i class="fas {{ $seccion['icono'] ?? 'fa-stethoscope' }}"
                                                       style="color:{{ $seccion['color'] ?? '#3B82F6' }}"></i>
                                                    <strong>{{ $seccion['nombre'] }}</strong>
                                                    <span class="badge bg-label-secondary">{{ count($seccion['campos']) }} campos</span>
                                                </div>
                                                <div class="d-flex gap-1">
                                                    <button class="btn btn-xs btn-icon btn-outline-primary"
                                                            wire:click="abrirModalSeccionEstado('{{ $estadoKey }}', {{ $seccion['id'] }})">
                                                        <i class="ri ri-edit-line"></i>
                                                    </button>
                                                    <button class="btn btn-xs btn-icon {{ $seccion['activo'] ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                                            wire:click="toggleSeccion({{ $seccion['id'] }})">
                                                        <i class="ri ri-{{ $seccion['activo'] ? 'eye-off' : 'eye' }}-line"></i>
                                                    </button>
                                                    <button class="btn btn-xs btn-icon btn-outline-danger"
                                                            wire:click="eliminarSeccion({{ $seccion['id'] }})"
                                                            wire:confirm="¿Eliminar esta sección y sus campos?">
                                                        <i class="ri ri-delete-bin-line"></i>
                                                    </button>
                                                    <button class="btn btn-xs btn-sm btn-primary"
                                                            wire:click="abrirModalCampoEstado({{ $seccion['id'] }})">
                                                        <i class="ri ri-add-line me-1"></i>Campo
                                                    </button>
                                                </div>
                                            </div>

                                            @if(count($seccion['campos']) > 0)
                                            <div class="card-body pt-2 pb-3">
                                                <div class="row g-2">
                                                    @foreach($seccion['campos'] as $ci => $campo)
                                                    <div class="col-12" wire:key="ef-campo-{{ $campo['id'] }}">
                                                        <div class="campo-row rounded p-2 {{ !$campo['activo'] ? 'inactivo' : '' }}">
                                                            <div class="d-flex align-items-center justify-content-between">
                                                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                                                    <span class="badge bg-label-primary tipo-badge">
                                                                        {{ $tiposCampo[$campo['tipo']] ?? $campo['tipo'] }}
                                                                    </span>
                                                                    <strong class="small">{{ $campo['etiqueta'] }}</strong>
                                                                    <code class="small text-muted">{{ $campo['nombre_campo'] }}</code>
                                                                    @if($campo['obligatorio'])
                                                                        <span class="badge bg-label-danger" style="font-size:.65rem">Obligatorio</span>
                                                                    @endif
                                                                    @if($campo['unidad'])
                                                                        <span class="badge bg-label-info" style="font-size:.65rem">{{ $campo['unidad'] }}</span>
                                                                    @endif
                                                                    <span class="width-indicator" title="Ancho {{ $campo['ancho_columnas'] }}/12">
                                                                        @for($w = 1; $w <= 12; $w++)
                                                                            <span class="wi-cell {{ $w <= $campo['ancho_columnas'] ? 'filled' : '' }}"></span>
                                                                        @endfor
                                                                    </span>
                                                                </div>
                                                                <div class="d-flex gap-1 flex-wrap">
                                                                    <button class="btn btn-xs btn-icon btn-outline-primary"
                                                                            wire:click="abrirModalCampoEstado({{ $seccion['id'] }}, {{ $campo['id'] }})" title="Editar">
                                                                        <i class="ri ri-edit-line"></i>
                                                                    </button>
                                                                    <button class="btn btn-xs btn-icon {{ $campo['activo'] ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                                                            wire:click="toggleCampo({{ $campo['id'] }})"
                                                                            title="{{ $campo['activo'] ? 'Desactivar' : 'Activar' }}">
                                                                        <i class="ri ri-{{ $campo['activo'] ? 'eye-off' : 'eye' }}-line"></i>
                                                                    </button>
                                                                    <button class="btn btn-xs btn-icon btn-outline-danger"
                                                                            wire:click="eliminarCampo({{ $campo['id'] }})"
                                                                            wire:confirm="¿Eliminar este campo?" title="Eliminar">
                                                                        <i class="ri ri-delete-bin-line"></i>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                            @else
                                            <div class="card-body py-2">
                                                <small class="text-muted fst-italic">Sin campos. Haz clic en "+ Campo".</small>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                @endif
                                @endif

                            </div>
                            @endif

                        </div>
                    </div>
                    @endforeach
                </div>
                @endif

            </div>
        </div>
        @else
        <div class="empty-hero">
            <div class="empty-icon"><i class="ri ri-file-add-line"></i></div>
            <h5 class="mb-2">Crea primero la plantilla base</h5>
            <p class="text-muted mb-0">Ve a la pestaña <strong>Secciones clínicas</strong> para crear la plantilla.</p>
        </div>
        @endif
        </div>{{-- /tab-formularios --}}

        </div>{{-- /tab-content --}}

    </div>{{-- /container --}}

    {{-- ── MODAL SECCIÓN ───────────────────────────────────────────────────── --}}
    @if($modalSeccion)
    @php
        $coloresPreset = ['#3B82F6','#6366F1','#8B5CF6','#EC4899','#EF4444','#F59E0B','#10B981','#14B8A6','#0EA5E9','#64748B'];
    @endphp
    <div class="modal fade show d-block custom-modal-backdrop" tabindex="-1"
         wire:click.self="$set('modalSeccion', false)"
         wire:keydown.escape.window="$set('modalSeccion', false)">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header" style="background:{{ $seccionColor }};color:#fff;border-bottom:0">
                    <h5 class="modal-title text-white">
                        <i class="fas {{ $seccionIcono }} me-2"></i>
                        {{ $seccionEditId ? 'Editar sección' : 'Nueva sección' }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="$set('modalSeccion', false)"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nombre de la sección <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" wire:model="seccionNombre"
                               placeholder="Ej: Agudeza Visual, Examen Cardiovascular" autofocus>
                        @error('seccionNombre') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Color</label>
                        <div class="d-flex align-items-center gap-2">
                            <input type="color" class="form-control form-control-color"
                                   wire:model.live="seccionColor" style="width:50px;height:38px">
                            <input type="text" class="form-control" wire:model.live="seccionColor"
                                   placeholder="#3B82F6" maxlength="7">
                        </div>
                        <div class="color-swatches">
                            @foreach($coloresPreset as $c)
                                <span class="swatch {{ strtoupper($seccionColor) === strtoupper($c) ? 'selected' : '' }}"
                                      style="background:{{ $c }}"
                                      wire:click="$set('seccionColor', '{{ $c }}')"
                                      title="{{ $c }}"></span>
                            @endforeach
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Ícono</label>
                        <div class="icon-picker">
                            @foreach($iconosDisponibles as $icono)
                                <button type="button"
                                        class="{{ $seccionIcono === $icono ? 'selected' : '' }}"
                                        wire:click="$set('seccionIcono', '{{ $icono }}')"
                                        title="{{ $icono }}">
                                    <i class="fas {{ $icono }}"></i>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div class="mt-3 text-center">
                        <small class="text-muted d-block mb-2">Vista previa</small>
                        <span class="badge" style="background-color:{{ $seccionColor }};font-size:1rem;padding:.6rem 1rem">
                            <i class="fas {{ $seccionIcono }} me-2"></i>{{ $seccionNombre ?: 'Nombre de la sección' }}
                        </span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-outline-secondary" wire:click="$set('modalSeccion', false)">Cancelar</button>
                    <button class="btn btn-primary"
                        wire:click="{{ $campoEstadoFormularioId ? 'guardarSeccionEstado' : 'guardarSeccion' }}"
                        wire:loading.attr="disabled" wire:target="guardarSeccion,guardarSeccionEstado">
                        <span wire:loading.remove wire:target="guardarSeccion,guardarSeccionEstado">
                            <i class="ri ri-save-line me-1"></i>Guardar
                        </span>
                        <span wire:loading wire:target="guardarSeccion,guardarSeccionEstado">
                            <span class="spinner-border spinner-border-sm me-1"></span>Guardando...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ── MODAL CAMPO ─────────────────────────────────────────────────────── --}}
    @if($modalCampo)
    @php
        $anchoOpciones = [
            2  => ['Muy estrecho', '⅙'],
            3  => ['Estrecho',     '¼'],
            4  => ['Tercio',       '⅓'],
            6  => ['Mitad',        '½'],
            8  => ['Amplio',       '⅔'],
            12 => ['Completo',     '1'],
        ];
    @endphp
    <div class="modal fade show d-block custom-modal-backdrop" tabindex="-1"
         wire:click.self="$set('modalCampo', false)"
         wire:keydown.escape.window="$set('modalCampo', false)">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="ri ri-input-field me-2 text-primary"></i>
                        {{ $campoEditId ? 'Editar campo' : 'Nuevo campo' }}
                    </h5>
                    <button type="button" class="btn-close" wire:click="$set('modalCampo', false)"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">

                        {{-- Etiqueta --}}
                        <div class="col-md-8">
                            <label class="form-label">Etiqueta (visible) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" wire:model="campoEtiqueta"
                                   placeholder="Ej: Ojo Derecho (AV), Presión Sistólica" autofocus>
                            @error('campoEtiqueta') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        {{-- Tipo --}}
                        <div class="col-md-4">
                            <label class="form-label">Tipo <span class="text-danger">*</span></label>
                            <select class="form-select" wire:model.live="campoTipo">
                                @foreach($tiposCampo as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Opciones (solo para select/radio/checkbox) --}}
                        @if(in_array($campoTipo, ['select', 'radio', 'checkbox']))
                        <div class="col-12">
                            <label class="form-label">Opciones <small class="text-muted">(una por línea)</small></label>
                            <textarea class="form-control" wire:model="campoOpciones" rows="4"
                                      placeholder="Opción 1&#10;Opción 2&#10;Opción 3"></textarea>
                        </div>
                        @endif

                        {{-- Min/Max (solo para number/range) --}}
                        @if(in_array($campoTipo, ['number', 'range']))
                        <div class="col-md-3">
                            <label class="form-label">Mínimo</label>
                            <input type="number" class="form-control" wire:model="campoMin" step="0.01">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Máximo</label>
                            <input type="number" class="form-control" wire:model="campoMax" step="0.01">
                        </div>
                        @endif

                        {{-- Unidad --}}
                        <div class="col-md-6">
                            <label class="form-label">Unidad</label>
                            <input type="text" class="form-control" wire:model="campoUnidad"
                                   placeholder="Ej: mmHg, kg, °C">
                        </div>

                        {{-- Ancho con selector visual --}}
                        <div class="col-md-6">
                            <label class="form-label">
                                Ancho (columnas) <span class="text-danger">*</span>
                                <span class="text-muted small">— {{ $campoAncho }}/12</span>
                            </label>
                            <div class="d-flex flex-wrap gap-1">
                                @foreach($anchoOpciones as $ancho => [$label, $frac])
                                    <button type="button"
                                            class="btn btn-sm {{ (int)$campoAncho === $ancho ? 'btn-primary' : 'btn-outline-secondary' }}"
                                            wire:click="$set('campoAncho', {{ $ancho }})"
                                            style="min-width:62px"
                                            title="{{ $label }}">
                                        <strong>{{ $frac }}</strong>
                                        <span class="d-block" style="font-size:.65rem">{{ $ancho }}/12</span>
                                    </button>
                                @endforeach
                            </div>
                            <div class="width-indicator mt-2" title="Ancho {{ $campoAncho }}/12">
                                @for($w = 1; $w <= 12; $w++)
                                    <span class="wi-cell {{ $w <= $campoAncho ? 'filled' : '' }}" style="width:14px;height:8px"></span>
                                @endfor
                            </div>
                        </div>

                        {{-- Placeholder --}}
                        <div class="col-md-6">
                            <label class="form-label">Placeholder</label>
                            <input type="text" class="form-control" wire:model="campoPlaceholder"
                                   placeholder="Texto de ayuda dentro del campo">
                        </div>

                        {{-- Valor por defecto --}}
                        <div class="col-md-6">
                            <label class="form-label">Valor por defecto</label>
                            <input type="text" class="form-control" wire:model="campoDefecto">
                        </div>

                        {{-- Obligatorio --}}
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox"
                                       wire:model="campoObligatorio" id="chkObligatorio">
                                <label class="form-check-label" for="chkObligatorio">Campo obligatorio</label>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-outline-secondary" wire:click="$set('modalCampo', false)">Cancelar</button>
                    <button class="btn btn-primary" wire:click="guardarCampo"
                            wire:loading.attr="disabled" wire:target="guardarCampo">
                        <span wire:loading.remove wire:target="guardarCampo">
                            <i class="ri ri-save-line me-1"></i>Guardar campo
                        </span>
                        <span wire:loading wire:target="guardarCampo">
                            <span class="spinner-border spinner-border-sm me-1"></span>Guardando...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ── MODAL ESTADO ──────────────────────────────────────────────────────── --}}
    @if($modalEstado)
    @php
        $coloresPreset = ['#6B7280','#EF4444','#F59E0B','#10B981','#3B82F6','#6366F1','#8B5CF6','#EC4899','#14B8A6','#F97316'];
    @endphp
    <div class="modal fade show d-block custom-modal-backdrop" tabindex="-1"
         wire:click.self="$set('modalEstado', false)"
         wire:keydown.escape.window="$set('modalEstado', false)">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header" style="background:{{ $estadoColor }};color:#fff;border-bottom:0">
                    <h5 class="modal-title text-white">
                        <i class="ri ri-flow-chart me-2"></i>
                        {{ $estadoEditIndex !== null ? 'Editar estado' : 'Nuevo estado' }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="$set('modalEstado', false)"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Nombre del estado <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" wire:model="estadoNombre"
                                   placeholder="Ej: En Triaje, En Laboratorio" autofocus>
                            @error('estadoNombre') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label">Color <span class="text-danger">*</span></label>
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <input type="color" class="form-control form-control-color"
                                       wire:model.live="estadoColor" style="width:50px;height:38px">
                                <input type="text" class="form-control" wire:model.live="estadoColor"
                                       placeholder="#6B7280" maxlength="7">
                            </div>
                            <div class="color-swatches">
                                @foreach($coloresPreset as $c)
                                    <span class="swatch {{ strtoupper($estadoColor) === strtoupper($c) ? 'selected' : '' }}"
                                          style="background:{{ $c }}"
                                          wire:click="$set('estadoColor', '{{ $c }}')"
                                          title="{{ $c }}"></span>
                                @endforeach
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox"
                                       wire:model="estadoActivo" id="chkEstadoActivo">
                                <label class="form-check-label" for="chkEstadoActivo">Estado activo</label>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="text-center p-3 bg-light rounded">
                                <small class="text-muted d-block mb-2">Vista previa</small>
                                <span class="badge" style="background-color:{{ $estadoColor }};font-size:1rem;padding:.6rem 1rem">
                                    {{ $estadoNombre ?: 'Nombre del estado' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-outline-secondary" wire:click="$set('modalEstado', false)">Cancelar</button>
                    <button class="btn btn-primary" wire:click="guardarEstado"
                            wire:loading.attr="disabled" wire:target="guardarEstado">
                        <span wire:loading.remove wire:target="guardarEstado">
                            <i class="ri ri-save-line me-1"></i>Guardar
                        </span>
                        <span wire:loading wire:target="guardarEstado">
                            <span class="spinner-border spinner-border-sm me-1"></span>Guardando...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ── MODAL PASO ──────────────────────────────────────────────────────── --}}
    @if($modalPaso)
    <div class="modal fade show d-block custom-modal-backdrop" tabindex="-1"
         wire:click.self="$set('modalPaso', false)"
         wire:keydown.escape.window="$set('modalPaso', false)">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="ri ri-list-check me-2 text-primary"></i>
                        {{ $pasoEditIndex !== null ? 'Editar paso' : 'Nuevo paso' }}
                    </h5>
                    <button type="button" class="btn-close" wire:click="$set('modalPaso', false)"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Nombre del paso <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" wire:model="pasoNombre"
                                   placeholder="Ej: Agudeza Visual, Tonometría" autofocus>
                            @error('pasoNombre') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Tipo <span class="text-danger">*</span></label>
                            <select class="form-select" wire:model="pasoTipo" @if($pasoEditIndex !== null) disabled @endif>
                                <option value="predefinido">Predefinido (lógica fija)</option>
                                <option value="formulario">Formulario (campos dinámicos)</option>
                            </select>
                            <small class="text-muted">Los pasos predefinidos tienen lógica especial (signos vitales, tratamientos, etc.)</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Ícono <span class="text-danger">*</span></label>
                            <select class="form-select" wire:model="pasoIcono">
                                @foreach($iconosPasos as $icono)
                                    <option value="{{ $icono }}">
                                        {{ str_replace(['ri-', '-line', '-'], ['', '', ' '], $icono) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox"
                                       wire:model="pasoActivo" id="chkPasoActivo">
                                <label class="form-check-label" for="chkPasoActivo">Paso activo</label>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="text-center p-3 bg-light rounded">
                                <small class="text-muted d-block mb-2">Vista previa</small>
                                <div class="d-inline-flex align-items-center gap-2 px-3 py-2 bg-white rounded border">
                                    <i class="{{ $pasoIcono }} text-primary" style="font-size:1.2rem"></i>
                                    <strong>{{ $pasoNombre ?: 'Nombre del paso' }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-outline-secondary" wire:click="$set('modalPaso', false)">Cancelar</button>
                    <button class="btn btn-primary" wire:click="guardarPaso"
                            wire:loading.attr="disabled" wire:target="guardarPaso">
                        <span wire:loading.remove wire:target="guardarPaso">
                            <i class="ri ri-save-line me-1"></i>Guardar
                        </span>
                        <span wire:loading wire:target="guardarPaso">
                            <span class="spinner-border spinner-border-sm me-1"></span>Guardando...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    @push('scripts')
    <script>
        (function () {
            const STORAGE_KEY = 'plantillaConsulta.activeTab';

            function activateTab(targetSel) {
                if (!targetSel) return;
                const btn = document.querySelector('.plantilla-tabs [data-tab-target="' + targetSel + '"]');
                const pane = document.querySelector(targetSel);
                if (!btn || !pane || btn.disabled) return;
                document.querySelectorAll('.plantilla-tabs .nav-link').forEach(b => b.classList.remove('active'));
                document.querySelectorAll('.tab-content > .tab-pane').forEach(p => p.classList.remove('show', 'active'));
                btn.classList.add('active');
                pane.classList.add('show', 'active');
            }

            function restoreActiveTab() {
                let target = sessionStorage.getItem(STORAGE_KEY);
                if (!target || !document.querySelector(target)) {
                    const current = document.querySelector('.plantilla-tabs .nav-link.active');
                    target = current ? current.getAttribute('data-tab-target') : '#tab-config';
                }
                activateTab(target);
            }

            document.addEventListener('click', (e) => {
                const btn = e.target.closest('.plantilla-tabs [data-tab-target]');
                if (!btn || btn.disabled) return;
                e.preventDefault();
                const target = btn.getAttribute('data-tab-target');
                sessionStorage.setItem(STORAGE_KEY, target);
                activateTab(target);
            });

            document.addEventListener('livewire:initialized', () => {
                restoreActiveTab();
                Livewire.hook('morph.updated', () => restoreActiveTab());

                Livewire.on('notify', (e) => {
                    const d = Array.isArray(e) && e.length ? e[0] : e;
                    if (!d || !d.message) return;
                    const type = d.type === 'success' ? 'success'
                               : d.type === 'error'   ? 'error'
                               : d.type === 'warning' ? 'warning'
                               : 'info';
                    if (typeof window.showToast === 'function') {
                        window.showToast(type, d.message, 3500);
                    }
                });
            });

            document.addEventListener('livewire:navigated', restoreActiveTab);
        })();
    </script>
    @endpush
</div>
