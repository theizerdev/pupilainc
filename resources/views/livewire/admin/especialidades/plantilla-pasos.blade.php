<div>
    @section('title', 'Pasos de Consulta')

    @push('styles')
    <style>
        .plantilla-sidebar {
            background: #fff;
            border-radius: 0.75rem;
            border: 1px solid rgba(0,0,0,.06);
            padding: 1.25rem;
            position: sticky;
            top: 1rem;
        }
        .plantilla-sidebar .sidebar-title {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--bs-secondary-color);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 1rem;
        }
        .menu-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem;
            border-radius: 0.5rem;
            color: var(--bs-body-color);
            text-decoration: none;
            transition: all 0.2s;
            margin-bottom: 0.5rem;
        }
        .menu-item:hover {
            background: var(--bs-primary-bg-subtle);
            color: var(--bs-primary);
        }
        .menu-item.active {
            background: var(--bs-primary);
            color: #fff;
        }
        .menu-item .icon {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
        }
        .menu-item .label {
            flex: 1;
            font-weight: 500;
            font-size: 0.9rem;
        }
        .menu-item .badge-status {
            font-size: 0.65rem;
            padding: 0.25rem 0.5rem;
        }
        .paso-card {
            border-left: 4px solid var(--bs-primary);
            background: #fff;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 0.75rem;
            transition: all 0.2s;
        }
        .paso-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,.08);
        }
        .paso-card.inactivo {
            opacity: 0.5;
            border-left-color: var(--bs-secondary-color);
        }
        .hero-header {
            background: linear-gradient(135deg, {{ $especialidad->color ?: '#3B82F6' }} 0%, {{ $especialidad->color ?: '#6366F1' }} 100%);
            color: #fff;
            border-radius: 0.75rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .hero-header h4, .hero-header p {
            color: #fff;
            margin: 0;
        }
    </style>
    @endpush

    <div class="container-xxl flex-grow-1 container-p-y">
        {{-- Header Hero --}}
        <div class="hero-header d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-3">
                <div style="width: 56px; height: 56px; border-radius: 14px; background: rgba(255,255,255,.18); display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                    <i class="fas {{ $especialidad->icono }}"></i>
                </div>
                <div>
                    <h4 class="mb-1">Pasos de la Consulta</h4>
                    <p class="mb-0">{{ $especialidad->nombre }}</p>
                </div>
            </div>
            <a href="{{ route('admin.especialidades.show', $especialidad) }}" class="btn btn-light">
                <i class="ri ri-arrow-left-line me-1"></i>Volver
            </a>
        </div>

        <div class="row g-4">
            {{-- Sidebar Menu --}}
            <div class="col-md-3 col-lg-2">
                <div class="plantilla-sidebar">
                    <div class="sidebar-title">Configuración</div>

                    @foreach($this->getMenuLateralItems() as $item)
                        <a href="{{ route($item['route'], $especialidad) }}"
                           class="menu-item {{ $item['activo'] ? 'active' : '' }}">
                            <div class="icon">
                                <i class="{{ $item['icon'] }}"></i>
                            </div>
                            <div class="label">{{ $item['label'] }}</div>
                            @if(!$item['completado'])
                                <span class="badge bg-warning text-dark badge-status">Pendiente</span>
                            @elseif($item['activo'])
                                <span class="badge bg-white text-primary badge-status">Actual</span>
                            @else
                                <span class="badge bg-success badge-status"><i class="ri ri-check-line"></i></span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- Main Content --}}
            <div class="col-md-9 col-lg-10">
                {{-- Info Box --}}
                <div class="alert alert-info d-flex align-items-start gap-3 mb-4">
                    <i class="ri ri-information-line fs-4"></i>
                    <div>
                        <strong>¿Qué son los pasos?</strong>
                        <p class="mb-0 mt-1" style="font-size: 0.9rem;">
                            Los pasos definen el orden en que el médico realiza la consulta.
                            Puedes activar/desactivar pasos, cambiar sus nombres e iconos,
                            y reordenarlos según tu flujo de trabajo.
                        </p>
                    </div>
                </div>

                {{-- Lista de Pasos --}}
                @if(count($pasosHabilitados) === 0)
                    <div class="text-center py-5" style="border: 2px dashed rgba(0,0,0,.1); border-radius: .75rem; background: rgba(0,0,0,.02);">
                        <div style="width: 84px; height: 84px; border-radius: 50%; background: var(--bs-primary-bg-subtle); color: var(--bs-primary); display: inline-flex; align-items: center; justify-content: center; font-size: 2rem; margin-bottom: 1rem;">
                            <i class="ri ri-footprints-line"></i>
                        </div>
                        <h5 class="mb-2">No hay pasos configurados</h5>
                        <p class="text-muted mb-4">Agrega el primer paso para comenzar.</p>
                        <button class="btn btn-primary" wire:click="abrirModalPaso()">
                            <i class="ri ri-add-line me-1"></i>Agregar Paso
                        </button>
                    </div>
                @else
                    @foreach($pasosHabilitados as $index => $paso)
                        <div class="paso-card {{ !$paso['activo'] ? 'inactivo' : '' }}">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-3">
                                    <div style="width: 48px; height: 48px; border-radius: 12px; background: {{ $paso['activo'] ? 'var(--bs-primary-bg-subtle)' : 'var(--bs-gray-200)' }}; color: {{ $paso['activo'] ? 'var(--bs-primary)' : 'var(--bs-secondary-color)' }}; display: flex; align-items: center; justify-content: center; font-size: 1.3rem;">
                                        <i class="{{ $paso['icono'] ?? 'ri-stethoscope-line' }}"></i>
                                    </div>
                                    <div>
                                        <div class="fw-semibold">{{ $paso['nombre'] }}</div>
                                        <small class="text-muted">
                                            {{ $paso['tipo'] === 'predefinido' ? 'Paso predefinido' : 'Formulario personalizado' }}
                                            · Orden: {{ $paso['orden'] }}
                                        </small>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <button class="btn btn-sm btn-icon"
                                            wire:click="moverPaso({{ $index }}, 'up')"
                                            @if($index === 0) disabled @endif
                                            title="Mover arriba">
                                        <i class="ri ri-arrow-up-s-line"></i>
                                    </button>
                                    <button class="btn btn-sm btn-icon"
                                            wire:click="moverPaso({{ $index }}, 'down')"
                                            @if($index === count($pasosHabilitados) - 1) disabled @endif
                                            title="Mover abajo">
                                        <i class="ri ri-arrow-down-s-line"></i>
                                    </button>
                                    <div class="vr mx-2"></div>
                                    <button class="btn btn-sm btn-outline-primary"
                                            wire:click="abrirModalPaso({{ $index }})"
                                            title="Editar">
                                        <i class="ri ri-edit-line"></i>
                                    </button>
                                    <button class="btn btn-sm {{ $paso['activo'] ? 'btn-outline-success' : 'btn-outline-secondary' }}"
                                            wire:click="togglePaso({{ $index }})"
                                            title="{{ $paso['activo'] ? 'Desactivar' : 'Activar' }}">
                                        <i class="ri ri-{{ $paso['activo'] ? 'check-line' : 'close-line' }}"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger"
                                            wire:click="eliminarPaso({{ $index }})"
                                            wire:confirm="¿Eliminar este paso?"
                                            title="Eliminar">
                                        <i class="ri ri-delete-bin-line"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <div class="mt-3">
                        <button class="btn btn-primary" wire:click="abrirModalPaso()">
                            <i class="ri ri-add-line me-1"></i>Agregar Nuevo Paso
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Modal Paso --}}
    @if($modalPaso)
        <div class="custom-modal-backdrop" style="display: block;">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            {{ $pasoEditIndex !== null ? 'Editar Paso' : 'Nuevo Paso' }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="resetModalPaso"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nombre del Paso *</label>
                            <input type="text" class="form-control" wire:model="pasoNombre" placeholder="Ej: Evaluación Visual">
                            @error('pasoNombre') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tipo</label>
                            <select class="form-select" wire:model="pasoTipo">
                                <option value="predefinido">Paso Predefinido</option>
                                <option value="formulario">Formulario Personalizado</option>
                            </select>
                            @error('pasoTipo') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Icono</label>
                            <div class="icon-picker">
                                @foreach($iconosDisponibles as $icono)
                                    <button type="button"
                                            class="{{ $pasoIcono === $icono ? 'selected' : '' }}"
                                            wire:click="$set('pasoIcono', '{{ $icono }}')">
                                        <i class="{{ $icono }}"></i>
                                    </button>
                                @endforeach
                            </div>
                            @error('pasoIcono') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="resetModalPaso">Cancelar</button>
                        <button type="button" class="btn btn-primary" wire:click="guardarPaso">
                            {{ $pasoEditIndex !== null ? 'Actualizar' : 'Crear' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
