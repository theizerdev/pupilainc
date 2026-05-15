<div class="w-100">
    @section('title', 'Subespecialidades Médicas')

    @push('styles')
    <style>
        /* Hero Section */
        .subespecialidades-hero {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            color: #fff;
            border-radius: 0.75rem;
            padding: 1.4rem 1.6rem;
        }
        .subespecialidades-hero h2 {
            color: #fff;
            margin: 0;
        }
        .subespecialidades-hero p {
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
        .table-subespecialidades thead th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #e4e6f9;
            font-weight: 600;
            font-size: 0.8125rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #697a8d;
            padding: 0.875rem 1rem;
        }
        .table-subespecialidades tbody td {
            padding: 0.875rem 1rem;
            vertical-align: middle;
        }
        .subespecialidad-row { transition: background 0.15s; }
        .subespecialidad-row:hover { background-color: #f8f9fa; }
    </style>
    @endpush
    {{-- Hero Section --}}
    <div class="subespecialidades-hero mb-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <h2 class="fw-semibold"><i class="ri ri-stethoscope-line me-2"></i>Subespecialidades Médicas</h2>
            <p class="mt-1">Gestione las subespecialidades médicas de su clínica</p>
        </div>
        @can('create subespecialidades')
            <a href="{{ route('admin.subespecialidades.create') }}" class="btn btn-light btn-sm">
                <i class="ri ri-add-line me-1"></i>Nueva Subespecialidad
            </a>
        @endcan
    </div>

    {{-- Stat Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#dbeafe;color:#2563eb;"><i class="ri ri-database-2-line"></i></div>
                <div>
                    <div class="stat-label">Total subespecialidades</div>
                    <div class="stat-value">{{ $stats['total'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#dcfce7;color:#16a34a;"><i class="ri ri-checkbox-circle-line"></i></div>
                <div>
                    <div class="stat-label">Activas</div>
                    <div class="stat-value text-success">{{ $stats['activas'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#fef3c7;color:#d97706;"><i class="ri ri-pause-circle-line"></i></div>
                <div>
                    <div class="stat-label">Inactivas</div>
                    <div class="stat-value text-warning">{{ $stats['inactivas'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#e0e7ff;color:#4f46e5;"><i class="ri ri-money-dollar-circle-line"></i></div>
                <div>
                    <div class="stat-label">Costo promedio</div>
                    <div class="stat-value text-primary">
                        @if($stats['promedio_costo'] > 0)
                            {{ format_money($stats['promedio_costo'], 2) }}
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Card with Filters and Table --}}
    <div class="card">
        <div class="card-header border-bottom py-3">
            <h5 class="card-title mb-3">Filtros</h5>
            <div class="row g-3">
                <div class="col-md-3">
                    <input type="text" class="form-control" wire:model.live.debounce.300ms="search" placeholder="Buscar subespecialidad...">
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
                    <select class="form-select" wire:model.change="empresa_id">
                        <option value="">Todas las empresas</option>
                        @foreach($empresas as $empresa)
                            <option value="{{ $empresa->id }}">{{ $empresa->razon_social }}</option>
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
                    <button class="btn btn-outline-secondary w-100" wire:click="clearFilters" title="Limpiar filtros">
                        <i class="ri ri-close-line"></i>
                    </button>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="card-datatable table-responsive">
            <table class="datatables-products table table-subespecialidades">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th wire:click="sortBy('nombre')" style="cursor: pointer;">
                            Nombre
                            @if($sortField === 'nombre')
                                <i class="ri ri-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}-line ms-1"></i>
                            @endif
                        </th>
                        <th>Especialidad</th>
                        <th>Empresa</th>
                        <th>Sucursal</th>
                        <th>Costo</th>
                        <th>Duración</th>
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
                    @forelse($subespecialidades as $subespecialidad)
                        <tr class="subespecialidad-row">
                            <td>
                                <span class="badge rounded-pill" style="background-color: {{ $subespecialidad->color }}; color: white;">
                                    <i class="ri ri-stethoscope-line me-1"></i>{{ $subespecialidad->codigo }}
                                </span>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $subespecialidad->nombre }}</div>
                                @if($subespecialidad->descripcion)
                                    <small class="text-muted">{{ Str::limit($subespecialidad->descripcion, 50) }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge rounded-pill bg-info-label">
                                    {{ $subespecialidad->especialidad->nombre ?? '-' }}
                                </span>
                            </td>
                            <td>{{ $subespecialidad->empresa->razon_social ?? '-' }}</td>
                            <td>{{ $subespecialidad->sucursal->nombre ?? '-' }}</td>
                            <td>
                                @if($subespecialidad->costo_consulta > 0)
                                    <strong class="text-success">{{ format_money($subespecialidad->costo_consulta, 2) }}</strong>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>{{ $subespecialidad->duracion_consulta }} min</td>
                            <td>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox"
                                           wire:click="toggleStatus({{ $subespecialidad->id }})"
                                           {{ $subespecialidad->status ? 'checked' : '' }}
                                           style="cursor: pointer;">
                                </div>
                            </td>
                            <td>{{ $subespecialidad->created_at->format('d/m/Y') }}</td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-icon btn-text-secondary rounded-pill dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                        <i class="ri ri-more-2-line"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        @can('access subespecialidades')
                                            <li>
                                                <a class="dropdown-item" href="{{ route('admin.subespecialidades.show', $subespecialidad) }}">
                                                    <i class="ri ri-eye-line me-2"></i>Ver
                                                </a>
                                            </li>
                                        @endcan
                                        @can('edit subespecialidades')
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <a class="dropdown-item" href="{{ route('admin.subespecialidades.edit', $subespecialidad) }}">
                                                    <i class="ri ri-pencil-line me-2"></i>Editar
                                                </a>
                                            </li>
                                        @endcan
                                        @can('delete subespecialidades')
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <button type="button" class="dropdown-item text-danger"
                                                        wire:click="deleteSubespecialidad({{ $subespecialidad->id }})"
                                                        wire:confirm="¿Estás seguro de eliminar esta subespecialidad?">
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
                                <p class="mb-0">No se encontraron subespecialidades</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($subespecialidades->hasPages())
            <div class="card-footer bg-white border-0 pt-3 pb-3">
                {{ $subespecialidades->links('livewire.pagination') }}
            </div>
        @endif
    </div>
</div>
