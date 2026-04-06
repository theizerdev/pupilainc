<div>
    @push('styles')
    <style>
        .analytics-card {
            border: none;
            border-radius: 0.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(165, 163, 174, 0.3);
        }
        .analytics-card .card-header {
            background-color: transparent;
            border-bottom: 1px solid #d9dee3;
            padding: 1.5rem;
        }
        .stats-card {
            background: linear-gradient(135deg, var(--bs-primary), var(--bs-primary-dark));
            border: none;
            border-radius: 0.75rem;
            color: white;
            position: relative;
            overflow: hidden;
        }
        .stats-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 50%;
            height: 100%;
            background: linear-gradient(135deg, transparent, rgba(255,255,255,0.1));
            border-radius: 0 0.75rem 0.75rem 0;
        }
        .stats-card .card-body {
            position: relative;
            z-index: 1;
        }
        .stats-icon {
            width: 3rem;
            height: 3rem;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        .chart-container {
            position: relative;
            height: 350px;
            padding: 1rem;
        }
        .table-hover tbody tr:hover {
            background-color: rgba(var(--bs-primary-rgb), 0.05);
        }
        .badge-analytics {
            font-size: 0.75rem;
            padding: 0.375rem 0.75rem;
            border-radius: 0.375rem;
        }
        .filter-section {
            background: #f8f9fa;
            border-radius: 0.5rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
    </style>
    @endpush

    <div>
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card analytics-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">
                            <i class="fas fa-chart-line text-primary me-2"></i>
                            Analytics de Citas Médicas
                        </h4>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="filter-section">
                    <div class="row align-items-end">
                        <div class="col-md-2">
                            <label class="form-label text-muted fw-semibold">Fecha Inicio</label>
                            <input type="date" class="form-control form-control-sm" wire:model="fechaInicio">
                            @error('fechaInicio') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-2">
                            <label class="form-label text-muted fw-semibold">Fecha Fin</label>
                            <input type="date" class="form-control form-control-sm" wire:model="fechaFin">
                            @error('fechaFin') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-2">
                            <label class="form-label text-muted fw-semibold">Sucursal</label>
                            <select class="form-select form-select-sm" wire:model="sucursalId">
                                <option value="">Todas las sucursales</option>
                                @foreach($sucursales as $sucursal)
                                    <option value="{{ $sucursal['id'] }}">{{ $sucursal['nombre'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label text-muted fw-semibold">Médico</label>
                            <select class="form-select form-select-sm" wire:model="medicoId">
                                <option value="">Todos los médicos</option>
                                @foreach($medicos as $medico)
                                    <option value="{{ $medico['id'] }}">{{ $medico['nombre'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label text-muted fw-semibold">Rápido</label>
                            <div class="btn-group btn-group-sm w-100" role="group">
                                <button type="button" class="btn btn-outline-primary" wire:click="actualizarFechas('hoy')">Hoy</button>
                                <button type="button" class="btn btn-outline-primary" wire:click="actualizarFechas('semana')">Sem</button>
                                <button type="button" class="btn btn-outline-primary" wire:click="actualizarFechas('mes')">Mes</button>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label text-muted fw-semibold">&nbsp;</label>
                            <button class="btn btn-primary btn-sm w-100" wire:click="cargarDatos">
                                <i class="fas fa-sync-alt me-1"></i>Actualizar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tarjetas de Estadísticas -->
        @if(!empty($analyticsData))
            <div class="row mb-4">
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card stats-card bg-primary">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="card-title mb-1">Total Citas</h6>
                                    <h3 class="mb-0">{{ format_money($analyticsData['resumen']['total'] ?? 0) }}</h3>
                                    <small class="opacity-75">
                                        <i class="fas fa-calendar-check me-1"></i>
                                        {{ $analyticsData['resumen']['porcentaje_crecimiento'] ?? 0 }}% vs período anterior
                                    </small>
                                </div>
                                <div class="stats-icon">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card stats-card bg-success">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="card-title mb-1">Finalizadas</h6>
                                    <h3 class="mb-0">{{ format_money($analyticsData['resumen']['finalizadas'] ?? 0) }}</h3>
                                    <small class="opacity-75">
                                        <i class="fas fa-check-circle me-1"></i>
                                        {{ format_money($analyticsData['asistencia']['tasa_asistencia'] ?? 0, 1) }}% de asistencia
                                    </small>
                                </div>
                                <div class="stats-icon">
                                    <i class="fas fa-check-double"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card stats-card bg-warning">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="card-title mb-1">Canceladas</h6>
                                    <h3 class="mb-0">{{ format_money($analyticsData['resumen']['canceladas'] ?? 0) }}</h3>
                                    <small class="opacity-75">
                                        <i class="fas fa-times-circle me-1"></i>
                                        {{ format_money($analyticsData['cancelaciones']['tasa_cancelacion'] ?? 0, 1) }}% tasa de cancelación
                                    </small>
                                </div>
                                <div class="stats-icon">
                                    <i class="fas fa-calendar-times"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card stats-card bg-info">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="card-title mb-1">Tiempo Promedio</h6>
                                    <h3 class="mb-0">{{ $analyticsData['tiempo_promedio']['promedio_minutos'] ?? 0 }} min</h3>
                                    <small class="opacity-75">
                                        <i class="fas fa-clock me-1"></i>
                                        Espera en consulta
                                    </small>
                                </div>
                                <div class="stats-icon">
                                    <i class="fas fa-hourglass-half"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gráficos -->
            <div class="row">
                <!-- Distribución por Estados -->
                <div class="col-lg-4 mb-4">
                    <div class="card analytics-card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-chart-pie text-primary me-2"></i>
                                Distribución por Estados
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <div id="estadosChart" wire:ignore></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tendencias Semanales -->
                <div class="col-lg-4 mb-4">
                    <div class="card analytics-card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-chart-line text-success me-2"></i>
                                Tendencias Semanales
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <div id="tendenciasChart" wire:ignore></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Especialidades Más Solicitadas -->
                <div class="col-lg-4 mb-4">
                    <div class="card analytics-card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-stethoscope text-info me-2"></i>
                                Especialidades Más Solicitadas
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <div id="especialidadesChart" wire:ignore></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tablas de Análisis -->
            <div class="row">
                <!-- Top Médicos -->
                <div class="col-lg-6 mb-4">
                    <div class="card analytics-card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-user-md text-info me-2"></i>
                                Médicos Más Solicitados
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-sm mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Médico</th>
                                            <th class="text-center">Citas</th>
                                            <th class="text-center">Completitud</th>
                                            <th class="text-center">Asistencia</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach(($analyticsData['medicos'] ?? []) as $index => $medico)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar avatar-sm me-2">
                                                            <span class="avatar-initial rounded-circle bg-primary">
                                                                {{ substr($medico['nombre'], 0, 1) }}
                                                            </span>
                                                        </div>
                                                        <div>
                                                            <div class="fw-semibold">{{ $medico['nombre'] }}</div>
                                                            <small class="text-muted">{{ $medico['especialidad'] ?? 'Sin especialidad' }}</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-primary badge-analytics">{{ $medico['total_citas'] }}</span>
                                                </td>
                                                <td class="text-center">
                                                    <div class="progress progress-sm" style="width: 60px;">
                                                        <div class="progress-bar bg-success" style="width: {{ $medico['tasa_completitud'] }}%"></div>
                                                    </div>
                                                    <small class="text-muted">{{ $medico['tasa_completitud'] }}%</small>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-success badge-analytics">{{ $medico['tasa_asistencia'] ?? 0 }}%</span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top Especialidades -->
                <div class="col-lg-6 mb-4">
                    <div class="card analytics-card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-stethoscope text-warning me-2"></i>
                                Especialidades Más Solicitadas
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-sm mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Especialidad</th>
                                            <th class="text-center">Citas</th>
                                            <th class="text-center">% del Total</th>
                                            <th class="text-center">Trend</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach(($analyticsData['especialidades'] ?? []) as $index => $especialidad)
                                            @php
                                                $especialidadData = collect($especialidades)->firstWhere('id', $especialidad['id']);
                                                $color = $especialidadData['color'] ?? '#6c757d';
                                                $icono = $especialidadData['icono'] ?? 'fa-stethoscope';
                                            @endphp
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar avatar-sm me-2" style="background-color: {{ $color }}20;">
                                                            <i class="fas {{ $icono }}" style="color: {{ $color }};"></i>
                                                        </div>
                                                        <div>
                                                            <div class="fw-semibold">{{ $especialidad['nombre'] }}</div>
                                                            <small class="text-muted">{{ $especialidad['descripcion'] ?? 'Sin descripción' }}</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-primary badge-analytics">{{ $especialidad['total_citas'] }}</span>
                                                </td>
                                                <td class="text-center">
                                                    <div class="progress progress-sm" style="width: 60px;">
                                                        <div class="progress-bar" style="width: {{ $especialidad['porcentaje'] }}%; background-color: {{ $color }};"></div>
                                                    </div>
                                                    <small class="text-muted">{{ format_money($especialidad['porcentaje'], 1) }}%</small>
                                                </td>
                                                <td class="text-center">
                                                    @if(($especialidad['tendencia'] ?? 0) > 0)
                                                        <i class="fas fa-arrow-up text-success"></i>
                                                    @elseif(($especialidad['tendencia'] ?? 0) < 0)
                                                        <i class="fas fa-arrow-down text-danger"></i>
                                                    @else
                                                        <i class="fas fa-minus text-warning"></i>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Re-agendamientos Pendientes -->
            @if(!empty($reagendamientosPendientes))
                <div class="row">
                    <div class="col-12">
                        <div class="card analytics-card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-exchange-alt text-danger me-2"></i>
                                    Re-agendamientos Pendientes
                                </h5>
                                <button wire:click="procesarReagendamientoAutomatico" class="btn btn-primary btn-sm">
                                    <i class="fas fa-magic me-1"></i>
                                    Procesar Automáticamente
                                </button>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover table-sm mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Paciente</th>
                                                <th>Médico</th>
                                                <th>Fecha Cancelada</th>
                                                <th>Motivo</th>
                                                <th class="text-center">Horarios Disponibles</th>
                                                <th class="text-center">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($reagendamientosPendientes as $reagendamiento)
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="avatar avatar-sm me-2">
                                                                <span class="avatar-initial rounded-circle bg-secondary">
                                                                    {{ substr($reagendamiento['paciente'], 0, 1) }}
                                                                </span>
                                                            </div>
                                                            <div>
                                                                <div class="fw-semibold">{{ $reagendamiento['paciente'] }}</div>
                                                                <small class="text-muted">Paciente</small>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>{{ $reagendamiento['medico'] }}</td>
                                                    <td>
                                                        <span class="text-muted">
                                                            {{ \Carbon\Carbon::parse($reagendamiento['fecha_cancelada'])->format('d/m/Y H:i') }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-warning badge-analytics">{{ $reagendamiento['motivo'] }}</span>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge bg-info badge-analytics">{{ $reagendamiento['horarios_disponibles'] }} disponibles</span>
                                                    </td>
                                                    <td class="text-center">
                                                        <button class="btn btn-outline-primary btn-sm">
                                                            <i class="fas fa-calendar-plus me-1"></i>
                                                            Ver Opciones
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

        @else
            <div class="row">
                <div class="col-12">
                    <div class="card analytics-card">
                        <div class="card-body text-center py-5">
                            <div class="mb-3">
                                <i class="fas fa-chart-bar fa-3x text-muted"></i>
                            </div>
                            <h5 class="text-muted">No hay datos disponibles</h5>
                            <p class="text-muted">Selecciona un rango de fechas y haz clic en "Actualizar" para cargar los datos.</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    @push('scripts')
    <script>
        let estadosChart = null;
        let tendenciasChart = null;
        let especialidadesChart = null;

        function destroyCharts() {
            if (estadosChart) { estadosChart.destroy(); estadosChart = null; }
            if (tendenciasChart) { tendenciasChart.destroy(); tendenciasChart = null; }
            if (especialidadesChart) { especialidadesChart.destroy(); especialidadesChart = null; }
        }

        function initCharts(analyticsData) {
            destroyCharts();

            if (!analyticsData) return;

            // Donut - Distribución por Estados
            if (analyticsData.estados && analyticsData.estados.length > 0) {
                const colorMap = {
                    'success': '#28a745', 'primary': '#007bff', 'warning': '#ffc107',
                    'danger': '#dc3545', 'info': '#17a2b8', 'secondary': '#6c757d'
                };

                const el = document.querySelector("#estadosChart");
                if (el) {
                    estadosChart = new ApexCharts(el, {
                        series: analyticsData.estados.map(e => e.total),
                        labels: analyticsData.estados.map(e => e.label),
                        chart: {
                            type: 'donut',
                            height: 350,
                            fontFamily: 'inherit',
                            toolbar: { show: true, tools: { download: true } },
                            animations: { enabled: true, speed: 800, animateGradually: { enabled: true, delay: 150 } }
                        },
                        colors: analyticsData.estados.map(e => colorMap[e.color] || '#6c757d'),
                        plotOptions: {
                            pie: {
                                donut: {
                                    size: '65%',
                                    labels: {
                                        show: true,
                                        name: { show: true, fontSize: '14px', color: '#6c757d' },
                                        value: {
                                            show: true, fontSize: '16px', color: '#343a40',
                                            formatter: function(val) { return val + ' citas'; }
                                        },
                                        total: {
                                            show: true, label: 'Total', fontSize: '14px', color: '#6c757d',
                                            formatter: function(w) {
                                                return w.globals.seriesTotals.reduce((a, b) => a + b, 0) + ' citas';
                                            }
                                        }
                                    }
                                }
                            }
                        },
                        dataLabels: {
                            enabled: true,
                            formatter: function(val) { return Math.round(val) + '%'; },
                            style: { fontSize: '12px', colors: ['#fff'] }
                        },
                        legend: {
                            show: true, position: 'bottom', fontSize: '12px',
                            labels: { colors: '#6c757d' },
                            markers: { width: 12, height: 12, radius: 50 },
                            itemMargin: { horizontal: 8, vertical: 4 }
                        },
                        tooltip: {
                            theme: 'dark',
                            y: { formatter: function(val) { return val + ' citas'; } }
                        },
                        responsive: [{ breakpoint: 480, options: { chart: { height: 300 }, legend: { position: 'bottom' } } }]
                    });
                    estadosChart.render();
                }
            }

            // Bar - Tendencias Semanales
            if (analyticsData.tendencias && analyticsData.tendencias.length > 0) {
                const el = document.querySelector("#tendenciasChart");
                if (el) {
                    tendenciasChart = new ApexCharts(el, {
                        series: [{
                            name: 'Citas por Día',
                            data: analyticsData.tendencias.map(t => t.total_citas)
                        }],
                        chart: {
                            type: 'bar',
                            height: 350,
                            fontFamily: 'inherit',
                            toolbar: { show: true, tools: { download: true, zoom: true, zoomin: true, zoomout: true, pan: true, reset: true } },
                            animations: { enabled: true, speed: 800, animateGradually: { enabled: true, delay: 150 } }
                        },
                        plotOptions: {
                            bar: { borderRadius: 4, horizontal: false, columnWidth: '60%', endingShape: 'rounded' }
                        },
                        dataLabels: { enabled: false },
                        stroke: { show: true, width: 2, colors: ['transparent'] },
                        colors: ['#36b9cc'],
                        xaxis: {
                            categories: analyticsData.tendencias.map(t => t.dia),
                            labels: { style: { fontSize: '12px', colors: '#6c757d' } },
                            axisBorder: { show: false },
                            axisTicks: { show: false }
                        },
                        yaxis: {
                            title: { text: 'Número de Citas', style: { fontSize: '12px', color: '#6c757d' } },
                            labels: { style: { fontSize: '12px', colors: '#6c757d' } }
                        },
                        fill: { opacity: 1, type: 'solid' },
                        tooltip: {
                            theme: 'dark',
                            y: { formatter: function(val) { return val + ' citas'; } }
                        },
                        grid: { borderColor: '#e0e6ed', strokeDashArray: 4 },
                        responsive: [{ breakpoint: 480, options: { chart: { height: 300 } } }]
                    });
                    tendenciasChart.render();
                }
            }

            // Horizontal Bar - Especialidades
            if (analyticsData.especialidades && analyticsData.especialidades.length > 0) {
                const el = document.querySelector("#especialidadesChart");
                if (el) {
                    const espColors = ['#696cff', '#71dd37', '#ffab00', '#03c3ec', '#ff3e1d', '#8592a3', '#e7515a', '#2196f3', '#e2a03f', '#805dca'];

                    especialidadesChart = new ApexCharts(el, {
                        series: [{
                            name: 'Citas',
                            data: analyticsData.especialidades.map(e => e.total_citas)
                        }],
                        chart: {
                            type: 'bar',
                            height: 350,
                            fontFamily: 'inherit',
                            toolbar: { show: true, tools: { download: true } },
                            animations: { enabled: true, speed: 800, animateGradually: { enabled: true, delay: 150 } }
                        },
                        plotOptions: {
                            bar: {
                                borderRadius: 4,
                                horizontal: true,
                                barHeight: '60%',
                                distributed: true,
                                dataLabels: { position: 'bottom' }
                            }
                        },
                        colors: espColors.slice(0, analyticsData.especialidades.length),
                        dataLabels: {
                            enabled: true,
                            textAnchor: 'start',
                            style: { fontSize: '12px', colors: ['#fff'] },
                            formatter: function(val, opt) {
                                return analyticsData.especialidades[opt.dataPointIndex].nombre + ' - ' + val + ' citas';
                            },
                            offsetX: 0
                        },
                        xaxis: {
                            categories: analyticsData.especialidades.map(e => e.nombre),
                            labels: { style: { fontSize: '12px', colors: '#6c757d' } },
                            axisBorder: { show: false },
                            axisTicks: { show: false }
                        },
                        yaxis: {
                            labels: { show: false }
                        },
                        legend: { show: false },
                        tooltip: {
                            theme: 'dark',
                            y: { formatter: function(val) { return val + ' citas'; } },
                            x: { show: true }
                        },
                        grid: { borderColor: '#e0e6ed', strokeDashArray: 4, xaxis: { lines: { show: true } }, yaxis: { lines: { show: false } } },
                        responsive: [{ breakpoint: 480, options: { chart: { height: 300 } } }]
                    });
                    especialidadesChart.render();
                }
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            const chartData = @json($this->getChartData());
            if (chartData && (chartData.estados?.length || chartData.tendencias?.length || chartData.especialidades?.length)) {
                initCharts(chartData);
            }
        });

        document.addEventListener('livewire:init', function () {
            Livewire.on('charts-updated', (data) => {
                const payload = Array.isArray(data) ? data[0] : data;
                if (payload && payload.chartData) {
                    setTimeout(() => initCharts(payload.chartData), 100);
                }
            });
        });
    </script>
    @endpush
</div>
