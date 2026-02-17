<div>
@php
use Illuminate\Support\Facades\Storage;
@endphp

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

    <div class="">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0 text-gray-800">Pacientes</h1>
                <p class="text-muted">Gestión de pacientes</p>
            </div>
            <div>
                @can('create pacientes')
                    <a href="{{ route('admin.pacientes.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nuevo Paciente
                    </a>
                @endcan
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Total Pacientes
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total'] }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-users fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Pacientes Activos
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['activos'] }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    Pacientes Inactivos
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['inactivos'] }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-pause-circle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Menores de Edad
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['menores'] }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-child fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card shadow mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="search">Búsqueda:</label>
                            <input type="text" class="form-control" id="search" wire:model.live.debounce.300ms="search" placeholder="Buscar paciente...">
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="empresa_id">Empresa:</label>
                            <select class="form-control" id="empresa_id" wire:model.live="empresa_id">
                                <option value="">Todas las empresas</option>
                                @foreach($empresas as $empresa)
                                    <option value="{{ $empresa->id }}">{{ $empresa->razon_social }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="sucursal_id">Sucursal:</label>
                            <select class="form-control" id="sucursal_id" wire:model.live="sucursal_id">
                                <option value="">Todas las sucursales</option>
                                @foreach($sucursales as $sucursal)
                                    <option value="{{ $sucursal->id }}">{{ $sucursal->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="status">Estado:</label>
                            <select class="form-control" id="status" wire:model.live="status">
                                <option value="">Todos</option>
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" id="soloMenores" wire:model.live="soloMenores">
                                <label class="form-check-label" for="soloMenores">Solo Menores</label>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-1">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="button" class="btn btn-secondary btn-block" wire:click="resetFilters">
                                <i class="fas fa-refresh"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Listado de Pacientes</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>
                                    <a wire:click.prevent="sortBy('nombres')" href="#" class="text-decoration-none">
                                        Nombre
                                        @if($sortField === 'nombres')
                                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                        @else
                                            <i class="fas fa-sort"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>
                                    <a wire:click.prevent="sortBy('documento_identidad')" href="#" class="text-decoration-none">
                                        Documento
                                        @if($sortField === 'documento_identidad')
                                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                        @else
                                            <i class="fas fa-sort"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>Teléfono / Email</th>
                                <th>Fecha Nac. / Edad</th>
                                <th>Tutor</th>
                                <th>Estado</th>
                                <th>
                                    <a wire:click.prevent="sortBy('created_at')" href="#" class="text-decoration-none">
                                        Creado
                                        @if($sortField === 'created_at')
                                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                        @else
                                            <i class="fas fa-sort"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pacientes as $paciente)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm me-2">
                                                @if($paciente->foto)
                                                    <img src="{{ filter_var($paciente->foto, FILTER_VALIDATE_URL) ? $paciente->foto : \Illuminate\Support\Facades\Storage::url($paciente->foto) }}"
                                                         alt="{{ $paciente->nombres }}"
                                                         class="avatar-img rounded-circle"
                                                         style="width: 40px; height: 40px; object-fit: cover;">
                                                @else
                                                    <span class="avatar-initial rounded-circle bg-primary">
                                                        {{ substr($paciente->nombres, 0, 1) }}{{ substr($paciente->apellidos, 0, 1) }}
                                                    </span>
                                                @endif
                                            </div>
                                            <div>
                                                <div class="fw-semibold">{{ $paciente->nombres }} {{ $paciente->apellidos }}</div>
                                                @if($paciente->nickname)
                                                    <small class="text-muted">{{ $paciente->nickname }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $paciente->documento_identidad }}</td>
                                    <td>
                                        <div>{{ $paciente->telefono ?? '-' }}</div>
                                        <small class="text-muted">{{ $paciente->email ?? '-' }}</small>
                                    </td>
                                    <td>
                                        @if($paciente->fecha_nacimiento)
                                            <div>{{ $paciente->fecha_nacimiento->format('d/m/Y') }}</div>
                                            <small class="text-muted">
                                                {{ $paciente->edad_formateada ?? 'N/A' }}
                                                @if($paciente->es_menor)
                                                    <span class="badge bg-warning ms-1">Menor</span>
                                                @endif
                                            </small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($paciente->tutor)
                                            <div>{{ $paciente->tutor->nombres }} {{ $paciente->tutor->apellidos }}</div>
                                            @if($paciente->tutor->parentesco ?? null)
                                                <span class="badge bg-secondary">{{ $paciente->tutor->parentesco }}</span>
                                            @endif
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="form-check form-switch form-switch-lg">
                                            <input class="form-check-input" type="checkbox"
                                                   id="statusSwitch{{ $paciente->id }}"
                                                   wire:click="toggleStatus({{ $paciente->id }})"
                                                   wire:confirm="¿Estás seguro de {{ $paciente->status ? 'desactivar' : 'activar' }} este paciente?"
                                                   {{ $paciente->status ? 'checked' : '' }}
                                                   style="cursor: pointer;"
                                                   onchange="showSwitchLoading({{ $paciente->id }})">

                                        </div>
                                    </td>
                                    <td>{{ $paciente->created_at->format('d/m/Y') }}</td>
                                    <td>
                                        <div class="dropdown">
                                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                                <i class="ri ri-more-2-line"></i>
                                            </button>
                                            <div class="dropdown-menu">

                                                @can('edit pacientes')
                                                <a class="dropdown-item" href="{{ route('admin.pacientes.edit', $paciente->id) }}">
                                                    <i class="ri ri-pencil-line me-1"></i> Editar
                                                </a>
                                                @endcan

                                                @if($paciente->es_menor && auth()->user()->hasRole('Super Administrador'))
                                                    <a class="dropdown-item" href="{{ route('admin.pacientes.carnet-menor', $paciente->id) }}" target="_blank" rel="noopener">
                                                        <i class="ri ri-printer-line me-1"></i> Imprimir Carnet (Menor)
                                                    </a>
                                                    <a class="dropdown-item" href="{{ route('admin.pacientes.carnet-menor.png', $paciente->id) }}" target="_blank" rel="noopener">
                                                        <i class="ri ri-image-line me-1"></i> Ver Imagen del Carnet
                                                    </a>
                                                    <a class="dropdown-item" href="{{ route('admin.pacientes.carnet-menor.pdf', $paciente->id) }}" target="_blank" rel="noopener">
                                                        <i class="ri ri-file-pdf-2-line me-1"></i> Ver PDF del Carnet
                                                    </a>
                                                    <form action="{{ route('admin.pacientes.carnet-menor.whatsapp', $paciente->id) }}" method="POST"
                                                          onsubmit="return confirm('¿Enviar el carnet por WhatsApp al tutor?')">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item">
                                                            <i class="ri ri-whatsapp-line me-1"></i> Enviar por WhatsApp (Tutor)
                                                        </button>
                                                    </form>
                                                @endif

                                                @can('delete pacientes')
                                                <button type="button" class="dropdown-item text-danger"
                                                        wire:click="delete({{ $paciente->id }})"
                                                        wire:confirm="¿Estás seguro de eliminar este paciente?">
                                                    <i class="ri ri-delete-bin-line me-1"></i> Eliminar
                                                </button>
                                                @endcan
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">No se encontraron pacientes</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                      {{ $pacientes->links('livewire.pagination') }}
                </div>
            </div>
        </div>
    </div>
</div>
