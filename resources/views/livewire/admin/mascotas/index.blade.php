<div>
@php
use Illuminate\Support\Facades\Storage;
@endphp

    @push('styles')
    <style>
        /* Hero Section */
        .mascotas-hero {
            background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);
            color: #fff;
            border-radius: 0.75rem;
            padding: 1.4rem 1.6rem;
        }
        .mascotas-hero h2 {
            color: #fff;
            margin: 0;
        }
        .mascotas-hero p {
            opacity: 0.9;
            margin: 0;
        }

        /* Stat Cards */
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
        .mascota-table thead th {
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
        .mascota-table tbody tr {
            transition: background-color .15s;
        }
        .mascota-table tbody tr:hover {
            background-color: #f8fafc;
        }
        .mascota-table td {
            padding: .85rem 1rem;
            vertical-align: middle;
        }

        /* Badge Styles */
        .badge-especie {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.35rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        /* Avatar */
        .mascota-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #e5e7eb;
        }
    </style>
    @endpush

    <!-- Hero Section -->
    <div class="mascotas-hero mb-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <h2 class="fw-semibold"><i class="mdi mdi-paw me-2"></i>Gestión de Mascotas</h2>
            <p class="mt-1">Administra el registro de pacientes veterinarios</p>
        </div>
        <button class="btn btn-light btn-sm" wire:click="createMascota">
            <i class="ri ri-add-line me-1"></i>Nueva Mascota
        </button>
    </div>

    <!-- Stat Cards -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#dbeafe;color:#2563eb;">
                    <i class="mdi mdi-paw"></i>
                </div>
                <div>
                    <div class="stat-label">Total Mascotas</div>
                    <div class="stat-value">{{ $stats['total'] }}</div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#fef3c7;color:#d97706;">
                    <i class="mdi mdi-dog"></i>
                </div>
                <div>
                    <div class="stat-label">Perros</div>
                    <div class="stat-value text-warning">{{ $stats['perros'] }}</div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#fce7f3;color:#db2777;">
                    <i class="mdi mdi-cat"></i>
                </div>
                <div>
                    <div class="stat-label">Gatos</div>
                    <div class="stat-value" style="color: #db2777;">{{ $stats['gatos'] }}</div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#dcfce7;color:#16a34a;">
                    <i class="mdi mdi-bird"></i>
                </div>
                <div>
                    <div class="stat-label">Otros</div>
                    <div class="stat-value text-success">{{ $stats['otros'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small text-muted">Buscar</label>
                    <input type="text"
                           wire:model.live.debounce.300ms="search"
                           class="form-control"
                           placeholder="Nombre, propietario, raza...">
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted">Especie</label>
                    <select wire:model.live="especieFilter" class="form-select">
                        <option value="">Todas las especies</option>
                        @foreach($especies as $especie)
                            <option value="{{ $especie->id }}">{{ $especie->icono }} {{ $especie->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted">Sexo</label>
                    <select wire:model.live="sexoFilter" class="form-select">
                        <option value="">Todos</option>
                        <option value="macho">Macho</option>
                        <option value="hembra">Hembra</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button class="btn btn-outline-secondary w-100"
                            wire:click="$set('search', ''); $set('especieFilter', ''); $set('sexoFilter', '')">
                        <i class="ri ri-refresh-line me-1"></i>Limpiar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Mascotas Table -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mascota-table mb-0">
                    <thead>
                        <tr>
                            <th>Mascota</th>
                            <th>Especie / Raza</th>
                            <th>Sexo</th>
                            <th>Edad</th>
                            <th>Peso</th>
                            <th>Propietario</th>
                            <th>Contacto</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($mascotas as $mascota)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        @if($mascota->foto)
                                            <img src="{{ Storage::url($mascota->foto) }}"
                                                 alt="{{ $mascota->nombre }}"
                                                 class="mascota-avatar">
                                        @else
                                            <div class="mascota-avatar d-flex align-items-center justify-content-center bg-light">
                                                <i class="mdi mdi-paw fs-4 text-muted"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <div class="fw-semibold">{{ $mascota->nombre }}</div>

                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge-especie"
                                          style="background: {{ $mascota->especie->color ?? '#e5e7eb' }}20;
                                                 color: {{ $mascota->especie->color ?? '#6b7280' }};">
                                        {{ $mascota->especie->icono ?? '🐾' }} {{ $mascota->especie->nombre }}
                                    </span>
                                    @if($mascota->raza)
                                        <div class="small text-muted mt-1">{{ $mascota->raza->nombre }}</div>
                                    @endif
                                </td>
                                <td>
                                    @if($mascota->sexo === 'macho')
                                        <span class="badge bg-primary">
                                            <i class="fas fa-male me-1"></i>Macho
                                        </span>
                                    @else
                                        <span class="badge bg-pink" style="background-color: #ec4899;">
                                            <i class="fas fa-female me-1"></i>Hembra
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-medium">{{ $mascota->edad_formateada ?? 'N/A' }}</div>
                                    @if($mascota->fecha_nacimiento)
                                        <small class="text-muted">
                                            Nac. {{ $mascota->fecha_nacimiento->format('d/m/Y') }}
                                        </small>
                                    @endif
                                </td>
                                <td>
                                    @if($mascota->peso_actual_kg)
                                        <div class="fw-medium">{{ number_format($mascota->peso_actual_kg, 2) }} kg</div>
                                    @else
                                        <span class="text-muted">No registrado</span>
                                    @endif
                                </td>
                                <td>
                                    @if($mascota->propietario)
                                        <div class="fw-medium">{{ $mascota->propietario->nombre_completo }}</div>
                                        @if($mascota->propietario->documento_identidad)
                                            <small class="text-muted">{{ $mascota->propietario->documento_identidad }}</small>
                                        @endif
                                    @else
                                        <span class="text-muted">Sin propietario</span>
                                    @endif
                                </td>
                                <td>
                                    @if($mascota->propietario)
                                        @if($mascota->propietario->telefono)
                                            <div>
                                                <i class="ri ri-phone-line text-success me-1"></i>
                                                {{ $mascota->propietario->telefono }}
                                            </div>
                                        @endif
                                        @if($mascota->propietario->email)
                                            <div class="small text-muted">
                                                <i class="ri ri-mail-line me-1"></i>
                                                {{ Str::limit($mascota->propietario->email, 20) }}
                                            </div>
                                        @endif
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="btn-group">
                                        <a href="{{ route('admin.mascotas.edit', $mascota->id) }}"
                                           class="btn btn-sm btn-outline-primary"
                                           title="Editar">
                                            <i class="ri ri-edit-line"></i>
                                        </a>
                                        <a href="{{ route('admin.mascotas.show', $mascota->id) }}"
                                           class="btn btn-sm btn-outline-info"
                                           title="Ver detalles">
                                            <i class="ri ri-eye-line"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <i class="ri ri-inbox-line fs-1 text-muted"></i>
                                    <p class="text-muted mt-3">No se encontraron mascotas registradas</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($mascotas->hasPages())
            <div class="card-footer bg-white border-top-0">
                {{ $mascotas->links() }}
            </div>
        @endif
    </div>

    <!-- Create Modal Placeholder -->
    @if($showCreateModal)
        @livewire('admin.mascotas.create')
    @endif
</div>
