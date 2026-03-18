<div>
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Gestión de Consultorios</h4>
                <a href="{{ route('admin.consultorios.create') }}" class="btn btn-primary">
                    <i class="ri-add-line me-1"></i> Nuevo Consultorio
                </a>
            </div>
        </div>
        <div class="col-xl-2 col-sm-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Consultorios</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clinic-medical fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-sm-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Activos</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['activos'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-sm-4">
            <div class="card border-left-secondary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">Inactivos</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['inactivos'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-minus-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Ocupados (Hoy)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['ocupadosHoy'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-md fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Disponibles (Hoy)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['disponiblesHoy'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-door-open fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row g-4 mb-4">
                <div class="col-lg-6">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-header">
                            <h6 class="mb-0">Ocupación de Consultorios (Hoy)</h6>
                        </div>
                        <div class="card-body">
                            <div id="consultorios-donut" style="min-height: 240px;"></div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-header">
                            <h6 class="mb-0">Estado de Consultorios</h6>
                        </div>
                        <div class="card-body">
                            <div id="consultorios-bars" style="min-height: 240px;"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4">
                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Buscar consultorio...">
                </div>
                <div class="col-md-3">
                    <select wire:model.live="status" class="form-select">
                        <option value="">Todos los estados</option>
                        <option value="1">Activos</option>
                        <option value="0">Inactivos</option>
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover table-striped">
                    <thead>
                        <tr>
                            <th wire:click="sortBy('nombre')" style="cursor: pointer;">
                                Nombre 
                                @if($sortField === 'nombre')
                                    <i class="ri-sort-{{ $sortDirection === 'asc' ? 'asc' : 'desc' }}"></i>
                                @endif
                            </th>
                            <th wire:click="sortBy('ubicacion')" style="cursor: pointer;">
                                Ubicación
                                @if($sortField === 'ubicacion')
                                    <i class="ri-sort-{{ $sortDirection === 'asc' ? 'asc' : 'desc' }}"></i>
                                @endif
                            </th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($consultorios as $consultorio)
                            <tr>
                                <td>
                                    <div class="fw-bold">{{ $consultorio->nombre }}</div>
                                    <small class="text-muted">{{ Str::limit($consultorio->descripcion, 50) }}</small>
                                </td>
                                <td>{{ $consultorio->ubicacion ?? 'N/A' }}</td>
                                <td>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" 
                                               id="status-{{ $consultorio->id }}"
                                               wire:click="toggleStatus({{ $consultorio->id }})"
                                               {{ $consultorio->status ? 'checked' : '' }}>
                                        <label class="form-check-label" for="status-{{ $consultorio->id }}">
                                            {{ $consultorio->status ? 'Activo' : 'Inactivo' }}
                                        </label>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('admin.consultorios.edit', $consultorio->id) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="ri-pencil-line"></i>
                                        </a>
                                        <button wire:click="delete({{ $consultorio->id }})" 
                                                wire:confirm="¿Está seguro de eliminar este consultorio?"
                                                class="btn btn-sm btn-outline-danger">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4">
                                    <div class="text-muted">No se encontraron consultorios.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $consultorios->links('livewire.pagination') }}
            </div>
        </div>
    </div>
@push('scripts')
<script>
    document.addEventListener('livewire:init', function () {
        var donutOptions = {
            chart: { type: 'donut', height: 240 },
            labels: ['Ocupados', 'Disponibles'],
            series: [{{ $stats['ocupadosHoy'] }}, {{ $stats['disponiblesHoy'] }}],
            colors: ['#dc3545', '#28a745'],
            legend: { position: 'bottom' },
            dataLabels: { enabled: true }
        };
        var donutChart = new ApexCharts(document.querySelector('#consultorios-donut'), donutOptions);
        donutChart.render();

        var barsOptions = {
            chart: { type: 'bar', height: 240 },
            series: [{ name: 'Consultorios', data: [{{ $stats['total'] }}, {{ $stats['activos'] }}, {{ $stats['inactivos'] }}] }],
            colors: ['#0d6efd', '#28a745', '#6c757d'],
            xaxis: { categories: ['Total', 'Activos', 'Inactivos'] },
            plotOptions: { bar: { columnWidth: '40%', borderRadius: 6 } },
            dataLabels: { enabled: true }
        };
        var barsChart = new ApexCharts(document.querySelector('#consultorios-bars'), barsOptions);
        barsChart.render();
    });
</script>
@endpush
</div>
