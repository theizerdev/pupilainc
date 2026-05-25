<div class="w-100">
    @section('title', 'Médicos')

    @push('styles')
    <style>
        /* Hero Section */
        .medicos-hero {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: #fff;
            border-radius: 0.75rem;
            padding: 1.4rem 1.6rem;
        }
        .medicos-hero h2 {
            color: #fff;
            margin: 0;
        }
        .medicos-hero p {
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
        .stat-card .stat-icon {
            width: 44px;
            height: 44px;
            border-radius: 11px;
            flex: 0 0 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
        }
        .stat-card .stat-value {
            font-size: 1.35rem;
            font-weight: 600;
            line-height: 1;
        }
        .stat-card .stat-label {
            font-size: .72rem;
            color: var(--bs-secondary-color);
            text-transform: uppercase;
            letter-spacing: .4px;
            font-weight: 600;
        }

        /* Table Styles */
        .table-medicos thead th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #e4e6f9;
            font-weight: 600;
            font-size: 0.8125rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #697a8d;
            padding: 0.875rem 1rem;
        }
        .table-medicos tbody td {
            padding: 0.875rem 1rem;
            vertical-align: middle;
        }
        .medico-row { transition: background 0.15s; }
        .medico-row:hover { background-color: #f8f9fa; }
    </style>
    @endpush
    {{-- Hero Section --}}
    <div class="medicos-hero mb-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <h2 class="fw-semibold"><i class="ri ri-user-star-line me-2"></i>Médicos</h2>
            <p class="mt-1">Gestión de médicos y profesionales de la salud</p>
        </div>
        @can('create medicos')
            <a href="{{ route('admin.medicos.create') }}" class="btn btn-light btn-sm">
                <i class="ri ri-add-line me-1"></i>Nuevo Médico
            </a>
        @endcan
    </div>

    {{-- Stat Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#dbeafe;color:#2563eb;"><i class="ri ri-database-2-line"></i></div>
                <div>
                    <div class="stat-label">Total médicos</div>
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
                <div class="stat-icon" style="background:#e0e7ff;color:#4f46e5;"><i class="ri ri-calendar-line"></i></div>
                <div>
                    <div class="stat-label">Exp. promedio</div>
                    <div class="stat-value text-primary">{{ $stats['promedio_experiencia'] }} años</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Card with Filters and Table --}}
    <div class="card">
        <div class="card-header border-bottom py-3">
            <h5 class="card-title mb-3">Filtros</h5>
            <div class="row g-3">
                <div class="col-md-2">
                    <input type="text" class="form-control" wire:model.live.debounce.300ms="search" placeholder="Buscar médico...">
                </div>
                <div class="col-md-2">
                    <select class="form-select" wire:model.change="empresa_id">
                        <option value="">Todas las empresas</option>
                        @foreach($empresas as $empresa)
                            <option value="{{ $empresa->id }}">{{ $empresa->razon_social }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" wire:model.change="sucursal_id">
                        <option value="">Todas las sucursales</option>
                        @foreach($sucursales as $sucursal)
                            <option value="{{ $sucursal->id }}">{{ $sucursal->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" wire:model.change="especialidad_id">
                        <option value="">Todas las especialidades</option>
                        @foreach($especialidades as $especialidad)
                            <option value="{{ $especialidad->id }}">{{ $especialidad->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" wire:model.change="status">
                        <option value="">Todos los estados</option>
                        <option value="1">Activo</option>
                        <option value="0">Inactivo</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-outline-secondary w-100" wire:click="resetFilters" title="Limpiar filtros">
                        <i class="ri ri-close-line"></i>
                    </button>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="card-datatable table-responsive">
            <table class="datatables-products table table-medicos">
                <thead>
                    <tr>
                        <th wire:click="sortBy('nombres')" style="cursor: pointer;">
                            Nombre
                            @if($sortField === 'nombres')
                                <i class="ri ri-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}-line ms-1"></i>
                            @endif
                        </th>
                        <th wire:click="sortBy('documento_identidad')" style="cursor: pointer;">
                            Documento
                            @if($sortField === 'documento_identidad')
                                <i class="ri ri-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}-line ms-1"></i>
                            @endif
                        </th>
                        <th>Especialidad</th>
                        <th>Empresa</th>
                        <th>Sucursal</th>
                        <th wire:click="sortBy('anios_experiencia')" style="cursor: pointer;">
                            Experiencia
                            @if($sortField === 'anios_experiencia')
                                <i class="ri ri-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}-line ms-1"></i>
                            @endif
                        </th>
                        <th>Nivel</th>
                        <th>Estado</th>
                        <th wire:click="sortBy('created_at')" style="cursor: pointer;">
                            Creado
                            @if($sortField === 'created_at')
                                <i class="ri ri-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}-line ms-1"></i>
                            @endif
                        </th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($medicos as $medico)
                        <tr class="medico-row">
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="avatar-initial rounded-circle bg-primary" style="width:32px;height:32px;display:flex;align-items:center;justify-content:center;font-size:.75rem;">
                                        {{ substr($medico->nombres, 0, 1) }}{{ substr($medico->apellidos, 0, 1) }}
                                    </span>
                                    <div>
                                        <div class="fw-semibold">{{ $medico->nombres }} {{ $medico->apellidos }}</div>
                                        <small class="text-muted">{{ $medico->user->email }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $medico->documento_identidad }}</td>
                            <td>
                                @if($medico->especialidades->count() > 0)
                                    @foreach($medico->especialidades->take(2) as $especialidad)
                                        <span class="badge rounded-pill bg-info-label me-1">{{ $especialidad->nombre }}</span>
                                    @endforeach
                                    @if($medico->especialidades->count() > 2)
                                        <span class="badge rounded-pill bg-secondary-label">+{{ $medico->especialidades->count() - 2 }}</span>
                                    @endif
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>{{ $medico->empresa->razon_social ?? '-' }}</td>
                            <td>{{ $medico->sucursal->nombre ?? '-' }}</td>
                            <td>{{ $medico->anios_experiencia }} años</td>
                            <td>
                                <span class="badge rounded-pill bg-{{ $medico->nivel_experiencia == 'experto' ? 'danger' : ($medico->nivel_experiencia == 'intermedio' ? 'warning' : 'success') }}-label">
                                    {{ ucfirst($medico->nivel_experiencia) }}
                                </span>
                            </td>
                            <td>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox"
                                           wire:click="toggleStatus({{ $medico->id }})"
                                           {{ $medico->status ? 'checked' : '' }}
                                           style="cursor: pointer;">
                                </div>
                            </td>
                            <td>{{ $medico->created_at->format('d/m/Y') }}</td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-icon btn-text-secondary rounded-pill dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                        <i class="ri ri-more-2-line"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        @can('admin.medicos.show')
                                            <li>
                                                <a class="dropdown-item" href="{{ route('admin.medicos.show', $medico) }}">
                                                    <i class="ri ri-eye-line me-2"></i>Ver
                                                </a>
                                            </li>
                                        @endcan
                                        @can('edit medicos')
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <a class="dropdown-item" href="{{ route('admin.medicos.edit', $medico) }}">
                                                    <i class="ri ri-pencil-line me-2"></i>Editar
                                                </a>
                                            </li>
                                        @endcan
                                        @if($medico->status)
                                            @can('edit medicos')
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <button type="button" class="dropdown-item text-warning"
                                                            wire:click="toggleStatus({{ $medico->id }})"
                                                            wire:confirm="¿Estás seguro de desactivar este médico?">
                                                        <i class="ri ri-pause-circle-line me-2"></i>Desactivar
                                                    </button>
                                                </li>
                                            @endcan
                                        @else
                                            @can('edit medicos')
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <button type="button" class="dropdown-item text-success"
                                                            wire:click="toggleStatus({{ $medico->id }})"
                                                            wire:confirm="¿Estás seguro de activar este médico?">
                                                        <i class="ri ri-play-circle-line me-2"></i>Activar
                                                    </button>
                                                </li>
                                            @endcan
                                        @endif
                                        @can('admin.medicos.destroy')
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <button type="button" class="dropdown-item text-danger"
                                                        wire:click="delete({{ $medico->id }})"
                                                        wire:confirm="¿Estás seguro de eliminar este médico?">
                                                    <i class="ri ri-delete-bin-line me-2"></i>Eliminar
                                                </button>
                                            </li>
                                        @endcan
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted py-5">
                                <i class="ri ri-inbox-line ri-3x d-block mb-3"></i>
                                <p class="mb-0">No se encontraron médicos</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($medicos->hasPages())
            <div class="card-footer bg-white border-0 pt-3 pb-3">
                {{ $medicos->links('livewire.pagination') }}
            </div>
        @endif
    </div>
</div>
