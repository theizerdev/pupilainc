<div>
    <div>
    <div class="">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0 text-gray-800">{{ $titulo }}</h1>
                <p class="text-muted">Gestión de consultas por estado</p>
            </div>
            <a href="{{ route('admin.gestion.consultas.index') }}" class="btn btn-outline-primary">
                <i class="ri ri-calendar-line me-1"></i> Ver Calendario
            </a>
        </div>

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-xl-4 col-md-6 mb-3">
                <div class="card shadow-sm h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col me-2">
                                <div class="text-xs fw-bold text-primary text-uppercase mb-1">Total</div>
                                <div class="h5 mb-0 fw-bold">{{ $stats['total'] }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="ri ri-file-list-3-line ri-2x text-muted"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-6 mb-3">
                <div class="card shadow-sm h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col me-2">
                                <div class="text-xs fw-bold text-success text-uppercase mb-1">Hoy</div>
                                <div class="h5 mb-0 fw-bold">{{ $stats['total_hoy'] }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="ri ri-calendar-check-line ri-2x text-muted"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-12 mb-3">
                <div class="card shadow-sm h-100 py-2">
                    <div class="card-body">
                        <div class="text-xs fw-bold text-info text-uppercase mb-2">Por Médico</div>
                        @forelse($stats['por_medico'] as $medico => $count)
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <small>{{ $medico }}</small>
                                <span class="badge bg-label-primary rounded-pill">{{ $count }}</span>
                            </div>
                        @empty
                            <small class="text-muted">Sin consultas</small>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="form-floating form-floating-outline">
                            <input type="text" class="form-control" id="search"
                                   wire:model.live.debounce.300ms="search"
                                   placeholder="Buscar...">
                            <label for="search">Buscar paciente, médico, código...</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-floating form-floating-outline">
                            <select class="form-select" wire:model.live="filtroMedico" id="filtroMedico">
                                <option value="">Todos los médicos</option>
                                @foreach($medicos as $medico)
                                    <option value="{{ $medico->id }}">{{ $medico->nombre_completo }}</option>
                                @endforeach
                            </select>
                            <label for="filtroMedico">Médico</label>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-floating form-floating-outline">
                            <input type="date" class="form-control" id="filtroFecha"
                                   wire:model.live="filtroFecha">
                            <label for="filtroFecha">Fecha</label>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-floating form-floating-outline">
                            <select class="form-select" wire:model.live="perPage" id="perPage">
                                <option value="10">10</option>
                                <option value="15">15</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                            </select>
                            <label for="perPage">Por página</label>
                        </div>
                    </div>
                    <div class="col-md-2 d-flex align-items-center">
                        <button class="btn btn-outline-secondary w-100" wire:click="resetFilters">
                            <i class="ri ri-filter-off-line me-1"></i> Limpiar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="card shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="cursor:pointer;" wire:click="sortBy('codigo')">
                                Código
                                @if($sortField === 'codigo')
                                    <i class="ri ri-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}-s-line"></i>
                                @endif
                            </th>
                            <th>Paciente</th>
                            <th>Médico</th>
                            <th>Especialidad</th>
                            <th style="cursor:pointer;" wire:click="sortBy('fecha_consulta')">
                                Fecha
                                @if($sortField === 'fecha_consulta')
                                    <i class="ri ri-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}-s-line"></i>
                                @endif
                            </th>
                            <th>Estado</th>
                            <th>Tiempo</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($consultas as $consulta)
                            <tr>
                                <td>
                                    <span class="fw-medium">#{{ $consulta->codigo }}</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div>
                                            <span class="fw-medium">{{ $consulta->paciente->nombre_completo }}</span>
                                            <br>
                                            <small class="text-muted">{{ $consulta->paciente->documento_identidad ?? '' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $consulta->medico->nombre_completo ?? 'Sin médico' }}</td>
                                <td>{{ $consulta->especialidad->nombre ?? 'Sin especialidad' }}</td>
                                <td>
                                    <span>{{ $consulta->fecha_consulta->format('d/m/Y') }}</span>
                                    <br>
                                    <small class="text-muted">{{ $consulta->fecha_consulta->format('h:i A') }}</small>
                                </td>
                                <td>
                                    <span class="badge" style="background-color: {{ $estadoColores[$consulta->estado] ?? '#78909C' }}">
                                        {{ $estadoLabels[$consulta->estado] ?? ucfirst($consulta->estado) }}
                                    </span>
                                    @if($consulta->estado === \App\Models\Consulta::ESTADO_PAGADA)
                                        <i class="ri ri-check-double-line text-success ms-1" title="Pagada"></i>
                                    @endif
                                </td>
                                <td>
                                    @if($consulta->estado_changed_at)
                                        <small class="text-muted" title="{{ $consulta->estado_changed_at->format('d/m/Y H:i') }}">
                                            <i class="ri ri-time-line"></i>
                                            {{ $consulta->estado_changed_at->diffForHumans(null, true, true) }}
                                        </small>
                                    @else
                                        <small class="text-muted">-</small>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-icon btn-outline-secondary dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                            <i class="ri ri-more-2-fill"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            @if($consulta->estado === \App\Models\Consulta::ESTADO_FINALIZADA)
                                                <li>
                                                    <a class="dropdown-item d-flex align-items-center"
                                                       href="{{ route('admin.consulta.informe', $consulta->id) }}"
                                                       target="_blank">
                                                        <i class="ri ri-file-text-line me-2"></i>
                                                        Informe Médico
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item d-flex align-items-center"
                                                       href="{{ route('admin.consulta.justificativo', $consulta->id) }}"
                                                       target="_blank">
                                                        <i class="ri ri-file-list-line me-2"></i>
                                                        Justificativo
                                                    </a>
                                                </li>
                                                @if($consulta->reposo)
                                                    <li>
                                                        <a class="dropdown-item d-flex align-items-center"
                                                           href="{{ route('admin.consulta.reposo', $consulta->id) }}"
                                                           target="_blank">
                                                            <i class="ri ri-file-shield-line me-2"></i>
                                                            Reposo Médico
                                                        </a>
                                                    </li>
                                                @endif
                                                <li><hr class="dropdown-divider"></li>
                                                @can('create pagos')
                                                <li>
                                                    <a class="dropdown-item d-flex align-items-center"
                                                       href="{{ route('admin.pagos.create') }}">
                                                        <i class="ri ri-money-dollar-circle-line me-2"></i>
                                                        Registrar Pago
                                                    </a>
                                                </li>
                                                @endcan
                                                <li><hr class="dropdown-divider"></li>
                                            @endif

                                            @if($consulta->estado === \App\Models\Consulta::ESTADO_EN_ENFERMERIA && auth()->user()->can('registrar signos vitales'))
                                                <li>
                                                    <a class="dropdown-item d-flex align-items-center" href="#"
                                                       data-bs-toggle="modal" data-bs-target="#registrarSignosVitalesModal{{ $consulta->id }}">
                                                        <i class="ri ri-heart-pulse-line me-2"></i>
                                                        Registrar Signos Vitales
                                                    </a>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                            @endif

                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="ri ri-file-search-line ri-3x mb-3 d-block"></i>
                                        <h5>No se encontraron consultas</h5>
                                        <p>Intente ajustar los filtros de búsqueda</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($consultas->hasPages())
                <div class="card-footer d-flex justify-content-between align-items-center">
                    <small class="text-muted">
                        Mostrando {{ $consultas->firstItem() }} a {{ $consultas->lastItem() }} de {{ $consultas->total() }}
                    </small>
                    {{ $consultas->links('livewire.pagination') }}
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Modales de Signos Vitales --}}
@foreach($consultas as $consulta)
    @if($consulta->estado === \App\Models\Consulta::ESTADO_EN_ENFERMERIA && auth()->user()->can('registrar signos vitales'))
        @livewire('admin.gestion.consultas.registrar-signos-vitales', ['consultaId' => $consulta->id], key('signos-vitales-' . $consulta->id))
    @endif
@endforeach

<script>
    window.addEventListener('alert', event => {
        if (event.detail.type === 'success') {
            alert(event.detail.message);
        } else if (event.detail.type === 'error') {
            alert(event.detail.message);
        }
    });

    window.addEventListener('cerrar-modal-signos-vitales', event => {
        // Cerrar todos los modales de signos vitales
        document.querySelectorAll('[id^="registrarSignosVitalesModal"]').forEach(modal => {
            const bootstrapModal = bootstrap.Modal.getInstance(modal);
            if (bootstrapModal) {
                bootstrapModal.hide();
            }
        });
    });
</script>
</div>
