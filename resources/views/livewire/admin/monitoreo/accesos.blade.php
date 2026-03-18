<div wire:poll.10s="$dispatch('refresh-accesos')">
    <!-- Header profesional -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-gradient-warning text-white border-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm me-3">
                                <div class="avatar-initial bg-white bg-opacity-20 rounded">
                                    <i class="ri ri-login-box-line ri-20px text-white"></i>
                                </div>
                            </div>
                            <div>
                                <h4 class="mb-0 text-white">Monitoreo de Accesos</h4>
                                <small class="text-white-50">Control de entradas y salidas de estudiantes</small>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <div class="text-end">
                                <div class="d-flex align-items-center">
                                    <div class="spinner-border spinner-border-sm text-white me-2" role="status" style="width: 12px; height: 12px;">
                                        <span class="visually-hidden">Actualizando...</span>
                                    </div>
                                    <small class="text-white-75">{{ $lastUpdate }}</small>
                                </div>
                                <div class="d-flex align-items-center mt-1">
                                    <span class="badge bg-white bg-opacity-20 text-white border border-white border-opacity-20">
                                        <i class="ri ri-door-line me-1"></i>{{ format_money($stats['total'] ?? 0) }} Accesos
                                    </span>
                                </div>
                            </div>
                            <button wire:click="exportExcel" class="btn btn-light btn-sm">
                                <i class="ri ri-file-excel-line me-1"></i>Exportar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros mejorados -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light border-0">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-sm me-3">
                            <div class="avatar-initial bg-primary bg-opacity-10 text-primary rounded">
                                <i class="ri ri-filter-line ri-18px"></i>
                            </div>
                        </div>
                        <h5 class="mb-0">Filtros de Búsqueda</h5>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-4">
                            <label class="form-label fw-medium">
                                <i class="ri ri-calendar-line me-1"></i>Fecha Inicio
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="ri ri-calendar-2-line text-muted"></i>
                                </span>
                                <input type="date" wire:model.live="startDate" class="form-control border-start-0">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-medium">
                                <i class="ri ri-calendar-line me-1"></i>Fecha Fin
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="ri ri-calendar-2-line text-muted"></i>
                                </span>
                                <input type="date" wire:model.live="endDate" class="form-control border-start-0">
                            </div>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <div class="d-flex gap-2 w-100">
                                <button class="btn btn-primary flex-grow-1">
                                    <i class="ri ri-search-line me-1"></i>Buscar
                                </button>
                                <button class="btn btn-outline-secondary">
                                    <i class="ri ri-refresh-line"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Métricas principales mejoradas -->
    <div class="row g-4 mb-4">
        <div class="col-xl-4 col-sm-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="avatar">
                            <div class="avatar-initial bg-primary bg-opacity-10 text-primary rounded">
                                <i class="ri ri-door-line ri-24px"></i>
                            </div>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="ri ri-more-2-line"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#"><i class="ri ri-eye-line me-2"></i>Ver detalles</a></li>
                                <li><a class="dropdown-item" href="#"><i class="ri ri-download-line me-2"></i>Exportar</a></li>
                            </ul>
                        </div>
                    </div>
                    <div>
                        <h3 class="mb-1 text-primary">{{ format_money($stats['total'] ?? 0) }}</h3>
                        <p class="text-muted mb-3">Total de Accesos</p>
                        <div class="d-flex align-items-center">
                            <div class="progress flex-grow-1 me-2" style="height: 6px;">
                                <div class="progress-bar bg-primary" style="width: 100%"></div>
                            </div>
                            <small class="text-primary fw-medium">100%</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-sm-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="avatar">
                            <div class="avatar-initial bg-success bg-opacity-10 text-success rounded">
                                <i class="ri ri-login-box-line ri-24px"></i>
                            </div>
                        </div>
                        <span class="badge bg-success bg-opacity-10 text-success">
                            {{ ($stats['total'] ?? 0) > 0 ? round((($stats['entradas'] ?? 0) / $stats['total']) * 100, 1) : 0 }}%
                        </span>
                    </div>
                    <div>
                        <h3 class="mb-1 text-success">{{ format_money($stats['entradas'] ?? 0) }}</h3>
                        <p class="text-muted mb-3">Entradas Registradas</p>
                        <div class="d-flex align-items-center">
                            <div class="progress flex-grow-1 me-2" style="height: 6px;">
                                <div class="progress-bar bg-success" style="width: {{ ($stats['total'] ?? 0) > 0 ? (($stats['entradas'] ?? 0) / $stats['total']) * 100 : 0 }}%"></div>
                            </div>
                            <small class="text-success fw-medium">Entrada</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-sm-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="avatar">
                            <div class="avatar-initial bg-danger bg-opacity-10 text-danger rounded">
                                <i class="ri ri-logout-box-line ri-24px"></i>
                            </div>
                        </div>
                        <span class="badge bg-danger bg-opacity-10 text-danger">
                            {{ ($stats['total'] ?? 0) > 0 ? round((($stats['salidas'] ?? 0) / $stats['total']) * 100, 1) : 0 }}%
                        </span>
                    </div>
                    <div>
                        <h3 class="mb-1 text-danger">{{ format_money($stats['salidas'] ?? 0) }}</h3>
                        <p class="text-muted mb-3">Salidas Registradas</p>
                        <div class="d-flex align-items-center">
                            <div class="progress flex-grow-1 me-2" style="height: 6px;">
                                <div class="progress-bar bg-danger" style="width: {{ ($stats['total'] ?? 0) > 0 ? (($stats['salidas'] ?? 0) / $stats['total']) * 100 : 0 }}%"></div>
                            </div>
                            <small class="text-danger fw-medium">Salida</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Accesos por Día</h5>
                </div>
                <div class="card-body">
                    @if($byDay->count() > 0)
                        <div id="accessByDayChart"></div>
                    @else
                        <div class="text-center py-5">
                            <i class="ri ri-file-chart-line ri-3x text-muted mb-3"></i>
                            <h5 class="mb-2">No hay datos disponibles</h5>
                            <p class="text-muted mb-0">No se encontraron registros de acceso para el período seleccionado</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Horarios Pico</h5>
                </div>
                <div class="card-body">
                    @if($byHour->count() > 0)
                        @foreach($byHour as $hour)
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-sm me-2">
                                    <span class="avatar-initial rounded bg-label-primary">
                                        <i class="ri ri-time-line"></i>
                                    </span>
                                </div>
                                <span>{{ str_pad($hour->hour, 2, '0', STR_PAD_LEFT) }}:00</span>
                            </div>
                            <strong>{{ $hour->count }}</strong>
                        </div>
                        @endforeach
                    @else
                        <div class="text-center py-3">
                            <i class="ri ri-time-line ri-2x text-muted mb-2"></i>
                            <p class="text-muted mb-0">No hay datos de horarios pico</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de registros recientes mejorada -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light border-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm me-3">
                                <div class="avatar-initial bg-warning bg-opacity-10 text-warning rounded">
                                    <i class="ri ri-history-line ri-18px"></i>
                                </div>
                            </div>
                            <div>
                                <h5 class="mb-0">Registros Recientes</h5>
                                <small class="text-muted">Últimos 20 accesos registrados</small>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-outline-primary">
                                <i class="ri ri-refresh-line me-1"></i>Actualizar
                            </button>
                            <button class="btn btn-sm btn-outline-success">
                                <i class="ri ri-download-line me-1"></i>Exportar
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($recent->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="border-0 ps-4">
                                            <div class="d-flex align-items-center">
                                                <i class="ri ri-time-line text-muted me-2"></i>
                                                Fecha/Hora
                                            </div>
                                        </th>
                                        <th class="border-0">
                                            <div class="d-flex align-items-center">
                                                <i class="ri ri-hashtag text-muted me-2"></i>
                                                Código
                                            </div>
                                        </th>
                                        <th class="border-0">
                                            <div class="d-flex align-items-center">
                                                <i class="ri ri-user-line text-muted me-2"></i>
                                                Estudiante
                                            </div>
                                        </th>
                                        <th class="border-0">
                                            <div class="d-flex align-items-center">
                                                <i class="ri ri-graduation-cap-line text-muted me-2"></i>
                                                Grado
                                            </div>
                                        </th>
                                        <th class="border-0">
                                            <div class="d-flex align-items-center">
                                                <i class="ri ri-door-line text-muted me-2"></i>
                                                Tipo
                                            </div>
                                        </th>
                                        <th class="border-0 pe-4">
                                            <div class="d-flex align-items-center">
                                                <i class="ri ri-user-settings-line text-muted me-2"></i>
                                                Registrado Por
                                            </div>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recent as $index => $access)
                                    <tr class="{{ $index % 2 == 0 ? 'bg-light bg-opacity-50' : '' }}">
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-xs me-2">
                                                    <div class="avatar-initial bg-info bg-opacity-10 text-info rounded">
                                                        <i class="ri ri-time-line ri-12px"></i>
                                                    </div>
                                                </div>
                                                <div>
                                                    <div class="fw-medium">{{ $access->access_time->format('d/m/Y') }}</div>
                                                    <small class="text-muted">{{ $access->access_time->format('H:i:s') }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <code class="text-primary fw-medium">{{ $access->student->codigo }}</code>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm me-2">
                                                    <div class="avatar-initial bg-primary bg-opacity-10 text-primary rounded">
                                                        {{ substr($access->student->nombres, 0, 1) }}{{ substr($access->student->apellidos, 0, 1) }}
                                                    </div>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0">{{ $access->student->nombres }} {{ $access->student->apellidos }}</h6>
                                                    <small class="text-muted">Estudiante</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-info bg-opacity-10 text-info">
                                                <i class="ri ri-graduation-cap-line me-1"></i>{{ $access->student->grado }} - {{ $access->student->seccion }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($access->type === 'entrada')
                                            <span class="badge bg-success bg-opacity-10 text-success">
                                                <i class="ri ri-login-box-line me-1"></i>Entrada
                                            </span>
                                            @else
                                            <span class="badge bg-danger bg-opacity-10 text-danger">
                                                <i class="ri ri-logout-box-line me-1"></i>Salida
                                            </span>
                                            @endif
                                        </td>
                                        <td class="pe-4">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-xs me-2">
                                                    <div class="avatar-initial bg-secondary bg-opacity-10 text-secondary rounded">
                                                        <i class="ri ri-user-line ri-12px"></i>
                                                    </div>
                                                </div>
                                                <span class="fw-medium">{{ $access->registeredBy->name ?? 'Sistema' }}</span>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="ri ri-file-list-line ri-3x text-muted mb-3"></i>
                            <h5 class="mb-2">No hay registros recientes</h5>
                            <p class="text-muted mb-0">No se encontraron registros de acceso para el período seleccionado</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        let accessByDayChart;

        function renderAccessChart() {
            if (accessByDayChart) accessByDayChart.destroy();

            const byDayData = @json($byDay);
            
            if (byDayData.length === 0) {
                return;
            }
            
            const options = {
                series: [{
                    name: 'Accesos',
                    data: byDayData.map(d => d.count)
                }],
                chart: {
                    height: 300,
                    type: 'area',
                    toolbar: { show: false }
                },
                colors: ['#667eea'],
                dataLabels: { enabled: false },
                stroke: { curve: 'smooth', width: 2 },
                xaxis: {
                    categories: byDayData.map(d => d.date)
                },
                yaxis: { min: 0 }
            };

            accessByDayChart = new ApexCharts(document.querySelector('#accessByDayChart'), options);
            accessByDayChart.render();
        }

        document.addEventListener('DOMContentLoaded', renderAccessChart);
        document.addEventListener('livewire:update', () => setTimeout(renderAccessChart, 100));
    </script>
    @endpush
</div>