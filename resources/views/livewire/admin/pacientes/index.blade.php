<div>
@php
use Illuminate\Support\Facades\Storage;
@endphp

    @push('styles')
    <style>
        /* Hero Section */
        .pacientes-hero {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #fff;
            border-radius: 0.75rem;
            padding: 1.4rem 1.6rem;
        }
        .pacientes-hero h2 {
            color: #fff;
            margin: 0;
        }
        .pacientes-hero p {
            opacity: 0.9;
            margin: 0;
        }

        /* Stat Cards - Compact Style */
        .stat-card {
            border: 1px solid rgba(0,0,0,.06);
            border-radius: .65rem;
            padding: .9rem 1rem;
            transition: all .2s;
            display: flex;
            align-items: center;
            gap: .85rem;
            height: 100%;
            background: #fff;
        }
        .stat-card:hover {
            box-shadow: 0 6px 18px rgba(0,0,0,.07);
            transform: translateY(-1px);
        }
        .stat-icon {
            width: 44px;
            height: 44px;
            border-radius: .5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
        }
        .stat-label {
            font-size: .72rem;
            text-transform: uppercase;
            letter-spacing: .5px;
            color: #64748b;
            font-weight: 600;
            margin-bottom: .15rem;
        }
        .stat-value {
            font-size: 1.35rem;
            font-weight: 700;
            line-height: 1.2;
        }

        /* Table Styles */
        .paciente-table thead th {
            background: #f8f9fa;
            border-bottom: 2px solid #e9ecef;
            font-weight: 600;
            text-transform: uppercase;
            font-size: .75rem;
            letter-spacing: .5px;
            color: #64748b;
            padding: .75rem 1rem;
            white-space: nowrap;
        }
        .paciente-table tbody tr {
            transition: background-color .15s;
        }
        .paciente-table tbody tr:hover {
            background-color: #f8fafc;
        }
        .paciente-table td {
            vertical-align: middle;
            padding: .85rem 1rem;
        }
        .avatar-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: .9rem;
            flex-shrink: 0;
        }

        /* Filter Section */
        .filter-section {
            background: #fff;
            border: 1px solid rgba(0,0,0,.06);
            border-radius: .75rem;
            padding: 1.25rem;
            margin-bottom: 1.5rem;
        }

        /* Badge Styling */
        .badge-menor {
            background: #fef3c7;
            color: #92400e;
            padding: .25rem .6rem;
            border-radius: .375rem;
            font-size: .7rem;
            font-weight: 600;
        }

        /* Action Dropdown */
        .btn-action-dropdown {
            border-radius: .5rem;
            padding: .4rem .6rem;
            border: 1px solid #e2e8f0;
            background: #fff;
            transition: all .2s;
        }
        .btn-action-dropdown:hover {
            background: #f8fafc;
            border-color: #cbd5e0;
        }
    </style>
    @endpush

    <script>
        // Función para mostrar loading en el switch
        function showSwitchLoading(pacienteId) {
            const switchElement = document.getElementById(`statusSwitch${pacienteId}`);
            const label = switchElement.nextElementSibling;
            const originalContent = label.innerHTML;

            // Deshabilitar el switch temporalmente
            switchElement.disabled = true;

            // Mostrar loading
            label.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span>';

            // Restaurar después de 2 segundos (el tiempo que normalmente toma la actualización)
            setTimeout(() => {
                label.innerHTML = originalContent;
                switchElement.disabled = false;
            }, 2000);
        }
    </script>

    <!-- Hero Section -->
    <div class="pacientes-hero mb-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <h2 class="fw-semibold"><i class="ri ri-user-heart-line me-2"></i>Pacientes</h2>
            <p class="mt-1">Gestione los pacientes registrados en el sistema</p>
        </div>
        @can('create pacientes')
            <a href="{{ route('admin.pacientes.create') }}" class="btn btn-light btn-sm">
                <i class="ri ri-add-line me-1"></i>Nuevo Paciente
            </a>
        @endcan
    </div>

    <!-- Stat Cards -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#dbeafe;color:#2563eb;"><i class="ri ri-group-line"></i></div>
                <div>
                    <div class="stat-label">Total pacientes</div>
                    <div class="stat-value">{{ $stats['total'] }}</div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#dcfce7;color:#16a34a;"><i class="ri ri-checkbox-circle-line"></i></div>
                <div>
                    <div class="stat-label">Activos</div>
                    <div class="stat-value text-success">{{ $stats['activos'] }}</div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#fef3c7;color:#d97706;"><i class="ri ri-pause-circle-line"></i></div>
                <div>
                    <div class="stat-label">Inactivos</div>
                    <div class="stat-value text-warning">{{ $stats['inactivos'] }}</div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#e0e7ff;color:#4f46e5;"><i class="ri ri-baby-line"></i></div>
                <div>
                    <div class="stat-label">Menores</div>
                    <div class="stat-value text-primary">{{ $stats['menores'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="filter-section">
        <div class="row g-3">
            <div class="col-md-2">
                <label class="small fw-bold mb-1">Búsqueda</label>
                <input type="text" class="form-control form-control-sm" wire:model.live.debounce.300ms="search" placeholder="Buscar paciente...">
            </div>

            <div class="col-md-2">
                <label class="small fw-bold mb-1">Empresa</label>
                <select class="form-select form-select-sm" wire:model.live="empresa_id">
                    <option value="">Todas las empresas</option>
                    @foreach($empresas as $empresa)
                        <option value="{{ $empresa->id }}">{{ $empresa->razon_social }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label class="small fw-bold mb-1">Sucursal</label>
                <select class="form-select form-select-sm" wire:model.live="sucursal_id">
                    <option value="">Todas las sucursales</option>
                    @foreach($sucursales as $sucursal)
                        <option value="{{ $sucursal->id }}">{{ $sucursal->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label class="small fw-bold mb-1">Estado</label>
                <select class="form-select form-select-sm" wire:model.live="status">
                    <option value="">Todos</option>
                    <option value="1">Activo</option>
                    <option value="0">Inactivo</option>
                </select>
            </div>

            <div class="col-md-2">
                <label class="small fw-bold mb-1">&nbsp;</label>
                <div class="form-check mt-2">
                    <input class="form-check-input" type="checkbox" id="soloMenores" wire:model.live="soloMenores">
                    <label class="form-check-label small" for="soloMenores">Solo Menores</label>
                </div>
            </div>

            <div class="col-md-2">
                <label class="small fw-bold mb-1">&nbsp;</label>
                <button type="button" class="btn btn-outline-secondary btn-sm w-100" wire:click="resetFilters">
                    <i class="ri ri-close-line me-1"></i>Limpiar
                </button>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="card shadow-sm" style="border: 1px solid rgba(0,0,0,.06); border-radius: .75rem;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover paciente-table mb-0">
                    <thead>
                        <tr>
                            <th>Paciente</th>
                            <th>Documento</th>
                            <th>Contacto</th>
                            <th>Nacimiento / Edad</th>
                            <th>Tutor</th>
                            <th>Estado</th>
                            <th>Creado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pacientes as $paciente)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        @if($paciente->foto)
                                            <img src="{{ filter_var($paciente->foto, FILTER_VALIDATE_URL) ? $paciente->foto : \Illuminate\Support\Facades\Storage::url($paciente->foto) }}"
                                                 alt="{{ $paciente->nombres }}"
                                                 class="rounded-circle"
                                                 style="width: 40px; height: 40px; object-fit: cover;">
                                        @else
                                            <div class="avatar-circle" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white;">
                                                {{ substr($paciente->nombres, 0, 1) }}{{ substr($paciente->apellidos, 0, 1) }}
                                            </div>
                                        @endif
                                        <div>
                                            <div class="fw-semibold">{{ $paciente->nombres }} {{ $paciente->apellidos }}</div>
                                            @if($paciente->nickname)
                                                <small class="text-muted">{{ $paciente->nickname }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-medium">{{ $paciente->documento_identidad }}</span>
                                </td>
                                <td>
                                    <div>{{ $paciente->telefono ?? '-' }}</div>
                                    @if($paciente->email)
                                        <small class="text-muted">{{ Str::limit($paciente->email, 25) }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($paciente->fecha_nacimiento)
                                        <div>{{ $paciente->fecha_nacimiento->format('d/m/Y') }}</div>
                                        <small class="text-muted">
                                            {{ $paciente->edad_formateada ?? 'N/A' }}
                                            @if($paciente->es_menor)
                                                <span class="badge-menor ms-1">Menor</span>
                                            @endif
                                        </small>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($paciente->tutor)
                                        <div class="fw-medium">{{ $paciente->tutor->nombres }} {{ $paciente->tutor->apellidos }}</div>
                                        @if($paciente->tutor->parentesco ?? null)
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary">{{ $paciente->tutor->parentesco }}</span>
                                        @endif
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox"
                                               id="statusSwitch{{ $paciente->id }}"
                                               wire:click="toggleStatus({{ $paciente->id }})"
                                               wire:confirm="¿Estás seguro de {{ $paciente->status ? 'desactivar' : 'activar' }} este paciente?"
                                               {{ $paciente->status ? 'checked' : '' }}
                                               onchange="showSwitchLoading({{ $paciente->id }})">
                                    </div>
                                </td>
                                <td>
                                    <small class="text-muted">{{ $paciente->created_at->format('d/m/Y') }}</small>
                                </td>
                                <td class="text-end">
                                    <div class="dropdown">
                                        <button type="button" class="btn-action-dropdown dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                            <i class="ri ri-more-2-line"></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-end">
                                            @can('edit pacientes')
                                            <a class="dropdown-item" href="{{ route('admin.pacientes.edit', $paciente->id) }}">
                                                <i class="ri ri-pencil-line me-2"></i>Editar
                                            </a>
                                            @endcan

                                            @if($paciente->es_menor && auth()->user()->hasRole('Super Administrador'))
                                                <div class="dropdown-divider"></div>
                                                <a class="dropdown-item" href="{{ route('admin.pacientes.carnet-menor', $paciente->id) }}" target="_blank" rel="noopener">
                                                    <i class="ri ri-printer-line me-2"></i>Imprimir Carnet
                                                </a>
                                                <a class="dropdown-item" href="{{ route('admin.pacientes.carnet-menor.png', $paciente->id) }}" target="_blank" rel="noopener">
                                                    <i class="ri ri-image-line me-2"></i>Ver Imagen
                                                </a>
                                                <a class="dropdown-item" href="{{ route('admin.pacientes.carnet-menor.pdf', $paciente->id) }}" target="_blank" rel="noopener">
                                                    <i class="ri ri-file-pdf-2-line me-2"></i>Ver PDF
                                                </a>
                                            @endif

                                            @can('delete pacientes')
                                                <div class="dropdown-divider"></div>
                                                <button type="button" class="dropdown-item text-danger"
                                                        wire:click="delete({{ $paciente->id }})"
                                                        wire:confirm="¿Estás seguro de eliminar este paciente?">
                                                    <i class="ri ri-delete-bin-line me-2"></i>Eliminar
                                                </button>
                                            @endcan
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="ri ri-inbox-line" style="font-size: 3rem; opacity: 0.3;"></i>
                                        <p class="mt-2 mb-0">No se encontraron pacientes</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($pacientes->hasPages())
                <div class="p-3 border-top">
                    {{ $pacientes->links('livewire.pagination') }}
                </div>
            @endif
        </div>
    </div>
</div>
