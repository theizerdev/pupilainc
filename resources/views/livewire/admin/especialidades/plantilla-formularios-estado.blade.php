<div>
    @section('title', 'Formularios por Estado')

    @push('styles')
    <style>
        .plantilla-sidebar { background: #fff; border-radius: 0.75rem; border: 1px solid rgba(0,0,0,.06); padding: 1.25rem; position: sticky; top: 1rem; }
        .plantilla-sidebar .sidebar-title { font-size: 0.85rem; font-weight: 600; color: var(--bs-secondary-color); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 1rem; }
        .menu-item { display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem; border-radius: 0.5rem; color: var(--bs-body-color); text-decoration: none; transition: all 0.2s; margin-bottom: 0.5rem; }
        .menu-item:hover { background: var(--bs-primary-bg-subtle); color: var(--bs-primary); }
        .menu-item.active { background: var(--bs-primary); color: #fff; }
        .menu-item .icon { width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; }
        .menu-item .label { font-weight: 500; font-size: 0.95rem; }
        .estado-card { background: #fff; border: 1px solid rgba(0,0,0,.08); border-radius: 0.75rem; padding: 1.25rem; transition: all 0.2s; cursor: pointer; }
        .estado-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,.1); transform: translateY(-2px); }
        .estado-badge { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; border-radius: 2rem; font-weight: 600; font-size: 0.85rem; }
    </style>
    @endpush

    <div class="d-flex gap-3">
        {{-- Sidebar --}}
        <div class="plantilla-sidebar" style="width: 260px; flex-shrink: 0;">
            <div class="sidebar-title">Configuración de Plantilla</div>

            <a href="{{ route('admin.especialidades.plantilla.index', $especialidad) }}" class="menu-item">
                <span class="icon"><i class="ri-dashboard-line"></i></span>
                <span class="label">Dashboard</span>
            </a>

            <a href="{{ route('admin.especialidades.plantilla.pasos', $especialidad) }}" class="menu-item">
                <span class="icon"><i class="ri-footprints-line"></i></span>
                <span class="label">Pasos de Consulta</span>
            </a>

            <a href="{{ route('admin.especialidades.plantilla.estados', $especialidad) }}" class="menu-item">
                <span class="icon"><i class="ri-flow-chart"></i></span>
                <span class="label">Estados del Flujo</span>
            </a>

            <a href="{{ route('admin.especialidades.plantilla.secciones', $especialidad) }}" class="menu-item">
                <span class="icon"><i class="ri-layout-column-line"></i></span>
                <span class="label">Secciones y Campos</span>
            </a>

            <a href="{{ route('admin.especialidades.plantilla.formularios-estado', $especialidad) }}" class="menu-item active">
                <span class="icon"><i class="ri-file-list-3-line"></i></span>
                <span class="label">Formularios por Estado</span>
            </a>
        </div>

        {{-- Contenido Principal --}}
        <div class="flex-grow-1">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-1">Formularios por Estado</h2>
                    <p class="text-muted mb-0">Configura campos específicos que aparecen según el estado de la consulta</p>
                </div>
            </div>

            @if($tieneEstados)
                <div class="row g-3">
                    @foreach($estadosFlujo as $index => $estado)
                        @php
                            $estadoKey = is_array($estado) ? ($estado['key'] ?? '') : $estado;
                            $estadoNombre = is_array($estado) ? ($estado['nombre'] ?? ucfirst($estadoKey)) : ucfirst($estadoKey);
                            $estadoColor = is_array($estado) ? ($estado['color'] ?? '#6B7280') : '#6B7280';

                            // Buscar formulario para este estado
                            $formulario = collect($estadoFormularios)->firstWhere('estado', $estadoKey);
                            $tieneCampos = $formulario && isset($formulario['secciones']) && count($formulario['secciones']) > 0;
                        @endphp

                        <div class="col-md-6 col-lg-4">
                            <div class="estado-card" wire:click="crearOAbrirFormularioEstado('{{ $estadoKey }}')">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div class="estado-badge" style="background: {{ $estadoColor }}20; color: {{ $estadoColor }};">
                                        <i class="ri-circle-fill" style="font-size: 0.6rem;"></i>
                                        {{ $estadoNombre }}
                                    </div>
                                    @if($tieneCampos)
                                        <span class="badge bg-success">{{ count($formulario['secciones']) }} secciones</span>
                                    @endif
                                </div>

                                <div class="text-muted small">
                                    @if($tieneCampos)
                                        <div class="mb-2">
                                            <strong>Última actualización:</strong><br>
                                            {{ $formulario['updated_at'] ?? 'N/A' }}
                                        </div>
                                        <div>
                                            <strong>Total campos:</strong>
                                            {{ array_sum(array_map(fn($s) => count($s['campos'] ?? []), $formulario['secciones'])) }}
                                        </div>
                                    @else
                                        <p class="mb-0">Sin configurar - Click para agregar campos</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="alert alert-warning">
                    <i class="ri-alert-line me-2"></i>
                    Primero debes configurar los estados del flujo en el módulo
                    <a href="{{ route('admin.especialidades.plantilla.estados', $especialidad) }}" class="alert-link">Estados del Flujo</a>.
                </div>
            @endif
        </div>
    </div>

    {{-- Modal Sección --}}
    @if($modalSeccion)
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $seccionEditId ? 'Editar Sección' : 'Nueva Sección' }}</h5>
                    <button type="button" class="btn-close" wire:click="$set('modalSeccion', false)"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nombre de la sección</label>
                        <input type="text" class="form-control" wire:model.live="seccionNombre" placeholder="Ej: Signos Vitales">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Icono</label>
                        <select class="form-select" wire:model.live="seccionIcono">
                            <option value="fa-stethoscope">🩺 Stethoscope</option>
                            <option value="fa-heartbeat">❤️ Heartbeat</option>
                            <option value="fa-notes-medical">📋 Notes Medical</option>
                            <option value="fa-prescription-bottle">💊 Prescription</option>
                            <option value="fa-user-md">👨‍⚕️ Doctor</option>
                            <option value="fa-hospital">🏥 Hospital</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Color</label>
                        <input type="color" class="form-control form-control-color" wire:model.live="seccionColor" value="#3B82F6">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="$set('modalSeccion', false)">Cancelar</button>
                    <button type="button" class="btn btn-primary" wire:click="guardarSeccion">Guardar</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Modal Campo --}}
    @if($modalCampo)
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $campoEditId ? 'Editar Campo' : 'Nuevo Campo' }}</h5>
                    <button type="button" class="btn-close" wire:click="$set('modalCampo', false)"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nombre del campo</label>
                            <input type="text" class="form-control" wire:model.live="campoNombre" placeholder="Ej: presion_arterial">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Etiqueta visible</label>
                            <input type="text" class="form-control" wire:model.live="campoEtiqueta" placeholder="Ej: Presión Arterial">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tipo de campo</label>
                            <select class="form-select" wire:model.live="campoTipo">
                                <option value="text">Texto</option>
                                <option value="textarea">Área de texto</option>
                                <option value="number">Número entero</option>
                                <option value="decimal">Número decimal</option>
                                <option value="date">Fecha</option>
                                <option value="time">Hora</option>
                                <option value="select">Lista desplegable</option>
                                <option value="radio">Opción única</option>
                                <option value="checkbox">Casillas de verificación</option>
                                <option value="boolean">Sí/No</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Ancho (Bootstrap grid)</label>
                            <select class="form-select" wire:model.live="campoAncho">
                                <option value="12">Completo (12)</option>
                                <option value="6">Mitad (6)</option>
                                <option value="4">Tercio (4)</option>
                                <option value="3">Cuarto (3)</option>
                            </select>
                        </div>
                    </div>

                    @if(in_array($campoTipo, ['select', 'radio', 'checkbox']))
                    <div class="mb-3">
                        <label class="form-label">Opciones (una por línea)</label>
                        <textarea class="form-control" rows="4" wire:model.live="campoOpciones" placeholder="Opción 1&#10;Opción 2&#10;Opción 3"></textarea>
                    </div>
                    @endif

                    <div class="form-check mb-3">
                        <input type="checkbox" class="form-check-input" wire:model.live="campoObligatorio" id="campoObligatorio">
                        <label class="form-check-label" for="campoObligatorio">Campo obligatorio</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="$set('modalCampo', false)">Cancelar</button>
                    <button type="button" class="btn btn-primary" wire:click="guardarCampo">Guardar</button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
