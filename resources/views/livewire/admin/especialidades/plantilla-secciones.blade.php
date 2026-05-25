<div>
    @section('title', 'Secciones y Campos')
    @push('styles')
    <style>
        .plantilla-sidebar { background: #fff; border-radius: 0.75rem; border: 1px solid rgba(0,0,0,.06); padding: 1.25rem; position: sticky; top: 1rem; }
        .plantilla-sidebar .sidebar-title { font-size: 0.85rem; font-weight: 600; color: var(--bs-secondary-color); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 1rem; }
        .menu-item { display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem; border-radius: 0.5rem; color: var(--bs-body-color); text-decoration: none; transition: all 0.2s; margin-bottom: 0.5rem; }
        .menu-item:hover { background: var(--bs-primary-bg-subtle); color: var(--bs-primary); }
        .menu-item.active { background: var(--bs-primary); color: #fff; }
        .menu-item .icon { width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; }
        .menu-item .label { flex: 1; font-weight: 500; font-size: 0.9rem; }
        .menu-item .badge-status { font-size: 0.65rem; padding: 0.25rem 0.5rem; }
        .seccion-card { border-left: 4px solid var(--seccion-color, #3B82F6); background: #fff; border-radius: 0.5rem; padding: 1rem; margin-bottom: 1rem; }
        .campo-row { border-left: 3px solid #e9ecef; padding: 0.75rem; margin: 0.5rem 0 0.5rem 1rem; background: #f8f9fa; border-radius: 0.25rem; }
        .hero-header { background: linear-gradient(135deg, {{ $especialidad->color ?: '#3B82F6' }} 0%, {{ $especialidad->color ?: '#6366F1' }} 100%); color: #fff; border-radius: 0.75rem; padding: 1.5rem; margin-bottom: 1.5rem; }
        .hero-header h4, .hero-header p { color: #fff; margin: 0; }
    </style>
    @endpush

    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="hero-header d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-3">
                <div style="width: 56px; height: 56px; border-radius: 14px; background: rgba(255,255,255,.18); display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                    <i class="fas {{ $especialidad->icono }}"></i>
                </div>
                <div><h4 class="mb-1">Secciones y Campos</h4><p class="mb-0">{{ $especialidad->nombre }}</p></div>
            </div>
            <a href="{{ route('admin.especialidades.show', $especialidad) }}" class="btn btn-light"><i class="ri ri-arrow-left-line me-1"></i>Volver</a>
        </div>

        <div class="row g-4">
            <div class="col-md-3 col-lg-2">
                <div class="plantilla-sidebar">
                    <div class="sidebar-title">Configuración</div>
                    @foreach($this->getMenuLateralItems() as $item)
                        <a href="{{ route($item['route'], $especialidad) }}" class="menu-item {{ $item['activo'] ? 'active' : '' }}">
                            <div class="icon"><i class="{{ $item['icon'] }}"></i></div>
                            <div class="label">{{ $item['label'] }}</div>
                            @if(!$item['completado'])<span class="badge bg-warning text-dark badge-status">Pendiente</span>
                            @elseif($item['activo'])<span class="badge bg-white text-primary badge-status">Actual</span>
                            @else<span class="badge bg-success badge-status"><i class="ri ri-check-line"></i></span>@endif
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="col-md-9 col-lg-10">
                <div class="alert alert-info d-flex align-items-start gap-3 mb-4">
                    <i class="ri ri-information-line fs-4"></i>
                    <div><strong>¿Qué son las secciones?</strong><p class="mb-0 mt-1" style="font-size: 0.9rem;">Las secciones organizan los campos de la evaluación clínica. Cada sección puede tener múltiples campos de diferentes tipos (texto, números, selecciones, etc.).</p></div>
                </div>

                @if(count($secciones) === 0)
                    <div class="text-center py-5" style="border: 2px dashed rgba(0,0,0,.1); border-radius: .75rem; background: rgba(0,0,0,.02);">
                        <div style="width: 84px; height: 84px; border-radius: 50%; background: var(--bs-primary-bg-subtle); color: var(--bs-primary); display: inline-flex; align-items: center; justify-content: center; font-size: 2rem; margin-bottom: 1rem;"><i class="ri ri-layout-grid-line"></i></div>
                        <h5 class="mb-2">No hay secciones configuradas</h5>
                        <p class="text-muted mb-4">Crea la primera sección para comenzar.</p>
                        <button class="btn btn-primary" wire:click="abrirModalSeccion()"><i class="ri ri-add-line me-1"></i>Agregar Sección</button>
                    </div>
                @else
                    @foreach($secciones as $seccion)
                        <div class="seccion-card" style="--seccion-color: {{ $seccion['color'] }}">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div style="width: 48px; height: 48px; border-radius: 12px; background: {{ $seccion['color'] }}20; color: {{ $seccion['color'] }}; display: flex; align-items: center; justify-content: center; font-size: 1.3rem;"><i class="fas {{ $seccion['icono'] }}"></i></div>
                                    <div><div class="fw-semibold">{{ $seccion['nombre'] }}</div><small class="text-muted">{{ count($seccion['campos']) }} campos</small></div>
                                </div>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-sm btn-outline-primary" wire:click="abrirModalSeccion({{ $seccion['id'] }})" title="Editar"><i class="ri ri-edit-line"></i></button>
                                    <button class="btn btn-sm {{ $seccion['activo'] ? 'btn-outline-success' : 'btn-outline-secondary' }}" wire:click="toggleSeccion({{ $seccion['id'] }})" title="{{ $seccion['activo'] ? 'Desactivar' : 'Activar' }}"><i class="ri ri-{{ $seccion['activo'] ? 'check-line' : 'close-line' }}"></i></button>
                                    <button class="btn btn-sm btn-outline-danger" wire:click="eliminarSeccion({{ $seccion['id'] }})" wire:confirm="¿Eliminar esta sección?" title="Eliminar"><i class="ri ri-delete-bin-line"></i></button>
                                </div>
                            </div>

                            @if(count($seccion['campos']) > 0)
                                <div class="mt-3">
                                    @foreach($seccion['campos'] as $campo)
                                        <div class="campo-row {{ !$campo['activo'] ? 'opacity-50' : '' }}">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div>
                                                    <div class="fw-semibold">{{ $campo['etiqueta'] }}</div>
                                                    <small class="text-muted">{{ $tiposCampo[$campo['tipo']] ?? $campo['tipo'] }}{{ $campo['unidad'] ? ' (' . $campo['unidad'] . ')' : '' }}{{ $campo['obligatorio'] ? ' *' : '' }}</small>
                                                </div>
                                                <div class="d-flex gap-2">
                                                    <button class="btn btn-sm btn-outline-primary" wire:click="abrirModalCampo({{ $seccion['id'] }}, {{ $campo['id'] }})" title="Editar"><i class="ri ri-edit-line"></i></button>
                                                    <button class="btn btn-sm {{ $campo['activo'] ? 'btn-outline-success' : 'btn-outline-secondary' }}" wire:click="toggleCampo({{ $campo['id'] }})" title="Activar/Desactivar"><i class="ri ri-{{ $campo['activo'] ? 'check-line' : 'close-line' }}"></i></button>
                                                    <button class="btn btn-sm btn-outline-danger" wire:click="eliminarCampo({{ $campo['id'] }})" wire:confirm="¿Eliminar este campo?" title="Eliminar"><i class="ri ri-delete-bin-line"></i></button>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <div class="mt-3">
                                <button class="btn btn-sm btn-outline-primary" wire:click="abrirModalCampo({{ $seccion['id'] }})"><i class="ri ri-add-line me-1"></i>Agregar Campo</button>
                            </div>
                        </div>
                    @endforeach

                    <div class="mt-3">
                        <button class="btn btn-primary" wire:click="abrirModalSeccion()"><i class="ri ri-add-line me-1"></i>Agregar Nueva Sección</button>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Modal Sección --}}
    @if($modalSeccion)
        <div class="custom-modal-backdrop" style="display: block;">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header"><h5 class="modal-title">{{ $seccionEditId ? 'Editar Sección' : 'Nueva Sección' }}</h5><button type="button" class="btn-close" wire:click="resetModalSeccion"></button></div>
                    <div class="modal-body">
                        <div class="mb-3"><label class="form-label">Nombre *</label><input type="text" class="form-control" wire:model="seccionNombre">@error('seccionNombre') <span class="text-danger small">{{ $message }}</span> @enderror</div>
                        <div class="mb-3"><label class="form-label">Icono</label><select class="form-select" wire:model="seccionIcono"><option value="fa-stethoscope">Stethoscope</option><option value="fa-heartbeat">Heartbeat</option><option value="fa-brain">Brain</option><option value="fa-eye">Eye</option><option value="fa-lungs">Lungs</option></select></div>
                        <div class="mb-3"><label class="form-label">Color</label><input type="color" class="form-control form-control-color" wire:model="seccionColor" style="width: 100%; height: 50px;"></div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary" wire:click="resetModalSeccion">Cancelar</button><button type="button" class="btn btn-primary" wire:click="guardarSeccion">{{ $seccionEditId ? 'Actualizar' : 'Crear' }}</button></div>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Campo --}}
    @if($modalCampo)
        <div class="custom-modal-backdrop" style="display: block;">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header"><h5 class="modal-title">{{ $campoEditId ? 'Editar Campo' : 'Nuevo Campo' }}</h5><button type="button" class="btn-close" wire:click="resetModalCampo"></button></div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6"><label class="form-label">Etiqueta *</label><input type="text" class="form-control" wire:model="campoEtiqueta">@error('campoEtiqueta') <span class="text-danger small">{{ $message }}</span> @enderror</div>
                            <div class="col-md-6"><label class="form-label">Tipo *</label><select class="form-select" wire:model="campoTipo">@foreach($tiposCampo as $key => $label)<option value="{{ $key }}">{{ $label }}</option>@endforeach</select>@error('campoTipo') <span class="text-danger small">{{ $message }}</span> @enderror</div>
                            <div class="col-md-6"><label class="form-label">Unidad</label><input type="text" class="form-control" wire:model="campoUnidad" placeholder="Ej: mmHg, °C"></div>
                            <div class="col-md-6"><label class="form-label">Ancho</label><select class="form-select" wire:model="campoAncho"><option value="12">Completo (12)</option><option value="6">Mitad (6)</option><option value="4">Tercio (4)</option><option value="3">Cuarto (3)</option></select></div>
                            @if(in_array($campoTipo, ['select', 'radio', 'checkbox']))
                                <div class="col-12"><label class="form-label">Opciones (una por línea)</label><textarea class="form-control" wire:model="campoOpciones" rows="3"></textarea></div>
                            @endif
                            <div class="col-md-6"><label class="form-label">Placeholder</label><input type="text" class="form-control" wire:model="campoPlaceholder"></div>
                            <div class="col-md-6"><label class="form-label">Valor por Defecto</label><input type="text" class="form-control" wire:model="campoDefecto"></div>
                            <div class="col-md-6"><label class="form-label">Mínimo</label><input type="number" step="any" class="form-control" wire:model="campoMin"></div>
                            <div class="col-md-6"><label class="form-label">Máximo</label><input type="number" step="any" class="form-control" wire:model="campoMax"></div>
                            <div class="col-12"><div class="form-check"><input class="form-check-input" type="checkbox" wire:model="campoObligatorio" id="campoObligatorio"><label class="form-check-label" for="campoObligatorio">Campo Obligatorio</label></div></div>
                        </div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary" wire:click="resetModalCampo">Cancelar</button><button type="button" class="btn btn-primary" wire:click="guardarCampo">{{ $campoEditId ? 'Actualizar' : 'Crear' }}</button></div>
                </div>
            </div>
        </div>
    @endif
</div>
