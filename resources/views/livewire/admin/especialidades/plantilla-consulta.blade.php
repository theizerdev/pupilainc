<div>
    @section('title', 'Plantilla de Consulta')

    @push('styles')
    <style>
        .seccion-card { border-left: 4px solid var(--seccion-color, #3B82F6); transition: all .2s; }
        .seccion-card:hover { box-shadow: 0 4px 15px rgba(0,0,0,.08); }
        .campo-row { border-left: 3px solid #e9ecef; padding-left: 10px; transition: all .2s; }
        .campo-row:hover { border-left-color: #3B82F6; background: #f8f9ff; }
        .campo-row.inactivo { opacity: .5; }
        .paso-badge { cursor: pointer; transition: all .15s; user-select: none; }
        .paso-badge.activo { opacity: 1; }
        .paso-badge.inactivo { opacity: .4; }
        .estado-badge { cursor: pointer; transition: all .15s; user-select: none; }
        .estado-badge.activo { opacity: 1; }
        .estado-badge.inactivo { opacity: .35; }
        .tipo-badge { font-size: .7rem; }
    </style>
    @endpush

    <div class="container-xxl flex-grow-1 container-p-y">

        {{-- ── HEADER ──────────────────────────────────────────────────────── --}}
        <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
            <div>
                <h4 class="mb-1">
                    <span class="badge me-2" style="background-color: {{ $especialidad->color }}">
                        <i class="fas {{ $especialidad->icono }}"></i>
                    </span>
                    Plantilla de Consulta — {{ $especialidad->nombre }}
                </h4>
                <p class="text-muted mb-0">
                    Configura los pasos, estados del flujo y secciones clínicas para esta especialidad.
                </p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.especialidades.show', $especialidad) }}" class="btn btn-outline-secondary">
                    <i class="ri ri-arrow-left-line me-1"></i>Volver
                </a>
                @if($plantilla)
                    <span class="badge bg-label-success align-self-center">
                        <i class="ri ri-check-line me-1"></i>Plantilla activa
                    </span>
                @else
                    <span class="badge bg-label-warning align-self-center">
                        <i class="ri ri-alert-line me-1"></i>Sin plantilla
                    </span>
                @endif
            </div>
        </div>

        {{-- ── PASOS Y ESTADOS ─────────────────────────────────────────────── --}}
        <div class="row g-4 mb-4">

            {{-- Pasos habilitados --}}
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent border-0 pb-0">
                        <h6 class="mb-0"><i class="ri ri-list-check me-2 text-primary"></i>Pasos del Proceso</h6>
                        <small class="text-muted">Haz clic para activar/desactivar cada paso</small>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($pasosDisponibles as $key => $label)
                            <span wire:click="togglePaso('{{ $key }}')"
                                  class="badge rounded-pill paso-badge {{ in_array($key, $pasosHabilitados) ? 'bg-primary activo' : 'bg-secondary inactivo' }}"
                                  style="font-size:.85rem; padding:.5rem .9rem; cursor:pointer;">
                                <i class="ri ri-{{ in_array($key, $pasosHabilitados) ? 'check' : 'close' }}-line me-1"></i>
                                {{ $label }}
                            </span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- Estados del flujo --}}
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent border-0 pb-0">
                        <h6 class="mb-0"><i class="ri ri-flow-chart me-2 text-primary"></i>Estados del Flujo (Kanban)</h6>
                        <small class="text-muted">Haz clic para incluir/excluir del flujo de esta especialidad</small>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($estadosDisponibles as $key => $label)
                            @php $color = \App\Models\Consulta::ESTADO_COLORES[$key] ?? '#78909C'; @endphp
                            <span wire:click="toggleEstado('{{ $key }}')"
                                  class="badge estado-badge {{ in_array($key, $estadosFlujo) ? 'activo' : 'inactivo' }}"
                                  style="background-color:{{ $color }};font-size:.8rem;padding:.45rem .8rem;cursor:pointer;">
                                {{ $label }}
                            </span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Botón guardar configuración --}}
        <div class="d-flex justify-content-end mb-4">
            <button class="btn btn-primary" wire:click="guardarConfiguracion">
                <i class="ri ri-save-line me-1"></i>Guardar Configuración de Pasos y Estados
            </button>
        </div>

        {{-- ── SECCIONES CLÍNICAS ───────────────────────────────────────────── --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent border-0 d-flex align-items-center justify-content-between">
                <h6 class="mb-0"><i class="ri ri-layout-line me-2 text-primary"></i>Secciones Clínicas</h6>
                @if($plantilla)
                <button class="btn btn-sm btn-primary" wire:click="abrirModalSeccion()">
                    <i class="ri ri-add-line me-1"></i>Nueva Sección
                </button>
                @endif
            </div>
            <div class="card-body">

                @if(!$plantilla)
                <div class="text-center py-4">
                    <i class="ri ri-file-add-line ri-3x text-muted mb-3 d-block"></i>
                    <p class="text-muted">Esta especialidad no tiene plantilla aún.</p>
                    <button class="btn btn-primary" wire:click="crearPlantilla">
                        <i class="ri ri-add-line me-1"></i>Crear Plantilla
                    </button>
                </div>

                @elseif(count($secciones) === 0)
                <div class="text-center py-4">
                    <i class="ri ri-layout-line ri-3x text-muted mb-3 d-block"></i>
                    <p class="text-muted">No hay secciones. Agrega la primera sección clínica.</p>
                </div>

                @else
                <div class="row g-3">
                    @foreach($secciones as $si => $seccion)
                    <div class="col-12">
                        <div class="card seccion-card {{ !$seccion['activo'] ? 'opacity-50' : '' }}"
                             style="--seccion-color: {{ $seccion['color'] ?? '#3B82F6' }}">
                            <div class="card-header bg-transparent d-flex align-items-center justify-content-between py-2">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fas {{ $seccion['icono'] ?? 'fa-stethoscope' }}"
                                       style="color: {{ $seccion['color'] ?? '#3B82F6' }}"></i>
                                    <strong>{{ $seccion['nombre'] }}</strong>
                                    <span class="badge bg-label-secondary">{{ count($seccion['campos']) }} campos</span>
                                    @if(!$seccion['activo'])
                                        <span class="badge bg-label-warning">Inactiva</span>
                                    @endif
                                </div>
                                <div class="d-flex gap-1">
                                    <button class="btn btn-xs btn-icon btn-outline-secondary"
                                            wire:click="moverSeccion({{ $seccion['id'] }}, 'up')"
                                            @if($si === 0) disabled @endif title="Subir">
                                        <i class="ri ri-arrow-up-s-line"></i>
                                    </button>
                                    <button class="btn btn-xs btn-icon btn-outline-secondary"
                                            wire:click="moverSeccion({{ $seccion['id'] }}, 'down')"
                                            @if($si === count($secciones) - 1) disabled @endif title="Bajar">
                                        <i class="ri ri-arrow-down-s-line"></i>
                                    </button>
                                    <button class="btn btn-xs btn-icon btn-outline-primary"
                                            wire:click="abrirModalSeccion({{ $seccion['id'] }})" title="Editar">
                                        <i class="ri ri-edit-line"></i>
                                    </button>
                                    <button class="btn btn-xs btn-icon {{ $seccion['activo'] ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                            wire:click="toggleSeccion({{ $seccion['id'] }})"
                                            title="{{ $seccion['activo'] ? 'Desactivar' : 'Activar' }}">
                                        <i class="ri ri-{{ $seccion['activo'] ? 'eye-off' : 'eye' }}-line"></i>
                                    </button>
                                    <button class="btn btn-xs btn-icon btn-outline-danger"
                                            wire:click="eliminarSeccion({{ $seccion['id'] }})"
                                            wire:confirm="¿Eliminar esta sección y todos sus campos?" title="Eliminar">
                                        <i class="ri ri-delete-bin-line"></i>
                                    </button>
                                    <button class="btn btn-xs btn-sm btn-primary"
                                            wire:click="abrirModalCampo({{ $seccion['id'] }})">
                                        <i class="ri ri-add-line me-1"></i>Campo
                                    </button>
                                </div>
                            </div>

                            {{-- Campos de la sección --}}
                            @if(count($seccion['campos']) > 0)
                            <div class="card-body pt-2 pb-3">
                                <div class="row g-2">
                                    @foreach($seccion['campos'] as $ci => $campo)
                                    <div class="col-12">
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
                                                    <span class="badge bg-label-secondary" style="font-size:.65rem">{{ $campo['ancho_columnas'] }}/12</span>
                                                </div>
                                                <div class="d-flex gap-1">
                                                    <button class="btn btn-xs btn-icon btn-outline-secondary"
                                                            wire:click="moverCampo({{ $campo['id'] }}, 'up')"
                                                            @if($ci === 0) disabled @endif>
                                                        <i class="ri ri-arrow-up-s-line"></i>
                                                    </button>
                                                    <button class="btn btn-xs btn-icon btn-outline-secondary"
                                                            wire:click="moverCampo({{ $campo['id'] }}, 'down')"
                                                            @if($ci === count($seccion['campos']) - 1) disabled @endif>
                                                        <i class="ri ri-arrow-down-s-line"></i>
                                                    </button>
                                                    <button class="btn btn-xs btn-icon btn-outline-primary"
                                                            wire:click="abrirModalCampo({{ $seccion['id'] }}, {{ $campo['id'] }})">
                                                        <i class="ri ri-edit-line"></i>
                                                    </button>
                                                    <button class="btn btn-xs btn-icon {{ $campo['activo'] ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                                            wire:click="toggleCampo({{ $campo['id'] }})">
                                                        <i class="ri ri-{{ $campo['activo'] ? 'eye-off' : 'eye' }}-line"></i>
                                                    </button>
                                                    <button class="btn btn-xs btn-icon btn-outline-danger"
                                                            wire:click="eliminarCampo({{ $campo['id'] }})"
                                                            wire:confirm="¿Eliminar este campo?">
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
                                <small class="text-muted fst-italic">Sin campos. Haz clic en "+ Campo" para agregar.</small>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif

            </div>
        </div>

    </div>{{-- /container --}}

    {{-- ── MODAL SECCIÓN ───────────────────────────────────────────────────── --}}
    @if($modalSeccion)
    <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5)">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="ri ri-layout-line me-2"></i>
                        {{ $seccionEditId ? 'Editar Sección' : 'Nueva Sección' }}
                    </h5>
                    <button type="button" class="btn-close" wire:click="$set('modalSeccion', false)"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nombre de la Sección <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" wire:model="seccionNombre"
                               placeholder="Ej: Agudeza Visual, Examen Cardiovascular">
                        @error('seccionNombre') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Color</label>
                            <div class="d-flex align-items-center gap-2">
                                <input type="color" class="form-control form-control-color"
                                       wire:model="seccionColor" style="width:50px;height:38px">
                                <input type="text" class="form-control" wire:model="seccionColor"
                                       placeholder="#3B82F6" maxlength="7">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ícono</label>
                            <select class="form-select" wire:model="seccionIcono">
                                @foreach($iconosDisponibles as $icono)
                                <option value="{{ $icono }}">{{ $icono }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    @if($seccionIcono)
                    <div class="mt-3 text-center">
                        <span class="badge" style="background-color:{{ $seccionColor }};font-size:1rem;padding:.6rem 1rem">
                            <i class="fas {{ $seccionIcono }} me-2"></i>{{ $seccionNombre ?: 'Vista previa' }}
                        </span>
                    </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button class="btn btn-outline-secondary" wire:click="$set('modalSeccion', false)">Cancelar</button>
                    <button class="btn btn-primary" wire:click="guardarSeccion">
                        <i class="ri ri-save-line me-1"></i>Guardar
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ── MODAL CAMPO ─────────────────────────────────────────────────────── --}}
    @if($modalCampo)
    <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5)">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="ri ri-input-field me-2"></i>
                        {{ $campoEditId ? 'Editar Campo' : 'Nuevo Campo' }}
                    </h5>
                    <button type="button" class="btn-close" wire:click="$set('modalCampo', false)"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">

                        {{-- Etiqueta --}}
                        <div class="col-md-8">
                            <label class="form-label">Etiqueta (visible) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" wire:model="campoEtiqueta"
                                   placeholder="Ej: Ojo Derecho (AV), Presión Sistólica">
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
                        <div class="col-md-3">
                            <label class="form-label">Unidad</label>
                            <input type="text" class="form-control" wire:model="campoUnidad"
                                   placeholder="Ej: mmHg, kg, °C">
                        </div>

                        {{-- Ancho --}}
                        <div class="col-md-3">
                            <label class="form-label">Ancho (columnas) <span class="text-danger">*</span></label>
                            <select class="form-select" wire:model="campoAncho">
                                <option value="2">2/12 — Muy estrecho</option>
                                <option value="3">3/12 — Estrecho</option>
                                <option value="4">4/12 — Tercio</option>
                                <option value="6">6/12 — Mitad</option>
                                <option value="8">8/12 — Amplio</option>
                                <option value="12">12/12 — Completo</option>
                            </select>
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
                    <button class="btn btn-primary" wire:click="guardarCampo">
                        <i class="ri ri-save-line me-1"></i>Guardar Campo
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    @push('scripts')
    <script>
        document.addEventListener('livewire:initialized', () => {
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
    </script>
    @endpush
</div>
