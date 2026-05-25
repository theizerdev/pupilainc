<div>
    @push('styles')
    <style>
        /* Hero Section */
        .dashboard-hero {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            color: #fff;
            border-radius: 0.75rem;
            padding: 1.4rem 1.6rem;
        }
        .dashboard-hero h2 {
            color: #fff;
            margin: 0;
        }
        .dashboard-hero p {
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

        /* Dashboard Cards */
        .dashboard-card {
            border: 1px solid rgba(0,0,0,.06);
            border-radius: .75rem;
            box-shadow: 0 2px 8px rgba(0,0,0,.04);
            transition: all 0.2s;
            background: #fff;
        }
        .dashboard-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,.08);
        }
        .dashboard-card .card-header {
            background: #f8f9fa;
            border-bottom: 1px solid rgba(0,0,0,.06);
            padding: 1rem 1.25rem;
        }
        .dashboard-card .card-header h5 {
            font-size: 1rem;
            font-weight: 600;
            margin: 0;
            color: #2d3748;
        }
        .dashboard-card .card-body {
            padding: 1.25rem;
        }

        /* Alert Items */
        .alert-item {
            padding: .85rem;
            border-left: 3px solid;
            border-radius: .5rem;
            margin-bottom: .75rem;
            background: #fff;
            transition: all .2s;
        }
        .alert-item:hover {
            box-shadow: 0 2px 8px rgba(0,0,0,.06);
            transform: translateX(2px);
        }
        .alert-item:last-child {
            margin-bottom: 0;
        }

        /* Payment Item */
        .payment-item {
            padding: .85rem 0;
            border-bottom: 1px solid #f1f5f9;
            transition: background-color .15s;
        }
        .payment-item:last-child {
            border-bottom: none;
        }
        .payment-item:hover {
            background-color: #f8fafc;
            margin: 0 -1.25rem;
            padding-left: 1.25rem;
            padding-right: 1.25rem;
        }

        /* Cita List Item */
        .cita-item {
            padding: .85rem 0;
            border-bottom: 1px solid #f1f5f9;
            transition: background-color .15s;
        }
        .cita-item:last-child {
            border-bottom: none;
        }
        .cita-item:hover {
            background-color: #f8fafc;
            margin: 0 -1.25rem;
            padding-left: 1.25rem;
            padding-right: 1.25rem;
        }

        /* Quick Actions */
        .quick-action-btn {
            border: 2px dashed rgba(0,0,0,.1);
            border-radius: .75rem;
            padding: 1.25rem;
            text-align: center;
            transition: all .2s;
            background: #fff;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
            display: block;
        }
        .quick-action-btn:hover {
            border-color: #6366f1;
            background: #f8f9ff;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(99,102,241,.15);
            color: inherit;
        }
        .quick-action-btn i {
            font-size: 1.75rem;
            margin-bottom: .5rem;
            display: block;
        }
        .quick-action-btn span {
            font-size: .85rem;
            font-weight: 600;
        }

        /* Medico Row */
        .medico-row {
            padding: .75rem 0;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .medico-row:last-child {
            border-bottom: none;
        }
        .medico-row:hover {
            background-color: #f8fafc;
            margin: 0 -1.25rem;
            padding-left: 1.25rem;
            padding-right: 1.25rem;
        }
    </style>
    @endpush

    <!-- Hero Section -->
    <div class="dashboard-hero mb-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <h2 class="fw-semibold"><i class="ri ri-dashboard-line me-2"></i>Dashboard</h2>
            <p class="mt-1">Resumen general del sistema médico</p>
        </div>
        <button class="btn btn-light btn-sm" wire:click="loadDashboardData">
            <i class="ri ri-refresh-line me-1"></i>Actualizar
        </button>
    </div>

    <!-- Stat Cards -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#dbeafe;color:#2563eb;"><i class="ri ri-calendar-check-line"></i></div>
                <div>
                    <div class="stat-label">Citas hoy</div>
                    <div class="stat-value">{{ $stats['citas_hoy'] }}</div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#dcfce7;color:#16a34a;"><i class="ri ri-user-heart-line"></i></div>
                <div>
                    <div class="stat-label">Total pacientes</div>
                    <div class="stat-value text-success">{{ $stats['pacientes_total'] }}</div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#e0e7ff;color:#4f46e5;"><i class="ri ri-user-star-line"></i></div>
                <div>
                    <div class="stat-label">Total médicos</div>
                    <div class="stat-value text-primary">{{ $stats['medicos_total'] }}</div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#fef3c7;color:#d97706;"><i class="ri ri-money-dollar-circle-line"></i></div>
                <div>
                    <div class="stat-label">Ingresos mes</div>
                    <div class="stat-value text-warning">{{ format_money($stats['ingresos_mes'], 0) }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alerts Panel and Today's Income -->
    <div class="row g-4 mb-4">
        <!-- Alertas -->
        <div class="col-lg-8">
            <div class="dashboard-card h-100">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="ri ri-notification-badge-line me-2" style="color: #ef4444;"></i>
                        Panel de Alertas
                    </h5>
                </div>
                <div class="card-body">
                    @if(count($alerts) > 0)
                        @foreach($alerts as $alert)
                        <div class="alert-item" style="border-left-color: {{ $alert['color'] }};">
                            <div class="d-flex align-items-start gap-3">
                                <div style="width: 40px; height: 40px; border-radius: .5rem; background: {{ $alert['color'] }}15; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                    <i class="{{ $alert['icon'] }}" style="font-size: 1.25rem; color: {{ $alert['color'] }};"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 fw-semibold" style="font-size: .9rem;">{{ $alert['title'] }}</h6>
                                    <p class="mb-0 text-muted" style="font-size: .85rem;">{{ $alert['message'] }}</p>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <div class="text-center py-4">
                            <i class="ri ri-checkbox-circle-line" style="font-size: 3rem; color: #10b981; opacity: 0.5;"></i>
                            <p class="text-muted mt-2 mb-0">No hay alertas pendientes</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Ingresos del Día y Tasa de Asistencia -->
        <div class="col-lg-4">
            <div class="row g-3">
                <!-- Ingresos Hoy -->
                <div class="col-12">
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="ri ri-wallet-line me-2" style="color: #10b981;"></i>
                                Ingresos Hoy
                            </h5>
                        </div>
                        <div class="card-body text-center py-4">
                            <div class="stat-value text-success mb-2" style="font-size: 2rem;">{{ format_money($stats['ingresos_hoy'], 0) }}</div>
                            <small class="text-muted">Pagos aprobados hoy</small>
                        </div>
                    </div>
                </div>

                <!-- Tasa de Asistencia -->
                <div class="col-12">
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="ri ri-percent-line me-2" style="color: #8b5cf6;"></i>
                                Tasa de Asistencia
                            </h5>
                        </div>
                        <div class="card-body text-center py-4">
                            <div class="stat-value mb-2" style="font-size: 2rem; color: {{ $stats['tasa_asistencia'] >= 80 ? '#10b981' : ($stats['tasa_asistencia'] >= 60 ? '#f59e0b' : '#ef4444') }};">
                                {{ $stats['tasa_asistencia'] }}%
                            </div>
                            <small class="text-muted">Citas completadas vs total</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart and Recent Appointments -->
    <div class="row g-4 mb-4">
        <!-- Chart -->
        <div class="col-lg-8">
            <div class="dashboard-card h-100">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="ri ri-bar-chart-box-line me-2 text-primary"></i>
                        Citas de los Últimos 7 Días
                    </h5>
                </div>
                <div class="card-body" wire:ignore>
                    <div id="citasChart"></div>
                </div>
            </div>
        </div>

        <!-- Upcoming Appointments -->
        <div class="col-lg-4">
            <div class="dashboard-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="ri ri-calendar-event-line me-2 text-primary"></i>
                        Próximas Citas
                    </h5>
                    <a href="{{ url('admin/calendario') }}" class="btn btn-outline-primary btn-sm" style="border-radius: .5rem;">
                        Ver todas
                    </a>
                </div>
                <div class="card-body p-0">
                    @if($recentCitas->count() > 0)
                        <div class="px-3 py-2">
                            @foreach($recentCitas as $cita)
                            <div class="cita-item">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 fw-semibold">{{ $cita->paciente->nombre_completo ?? 'Sin nombre' }}</h6>
                                        <p class="text-muted mb-1 small">
                                            <i class="ri ri-user-star-line me-1"></i>{{ $cita->medico->nombre_completo ?? 'Sin médico' }}
                                        </p>
                                        <p class="text-muted mb-0 small">
                                            <i class="ri ri-time-line me-1"></i>{{ \Carbon\Carbon::parse($cita->fecha_inicio)->format('d/m H:i') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="ri ri-calendar-line" style="font-size: 3rem; opacity: 0.3;"></i>
                            <p class="text-muted mt-2 mb-0">No hay citas programadas</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Payments and Top Doctors -->
    <div class="row g-4 mb-4">
        <!-- Últimos Pagos -->
        <div class="col-lg-6">
            <div class="dashboard-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="ri ri-bank-card-line me-2" style="color: #10b981;"></i>
                        Últimos Pagos
                    </h5>

                </div>
                <div class="card-body p-0">
                    @if(count($recentPayments) > 0)
                        <div class="px-3 py-2">
                            @foreach($recentPayments as $pago)
                            <div class="payment-item">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 fw-semibold">{{ $pago->cita->paciente->nombre_completo ?? 'Sin paciente' }}</h6>
                                        <p class="text-muted mb-1 small">
                                            <i class="ri ri-bank-card-line me-1"></i>{{ $pago->metodoPago->nombre ?? 'N/A' }}
                                        </p>
                                        <p class="text-muted mb-0 small">
                                            <i class="ri ri-time-line me-1"></i>{{ $pago->created_at->format('d/m H:i') }}
                                        </p>
                                    </div>
                                    <div class="text-end">
                                        <div class="fw-bold text-success" style="font-size: 1.1rem;">{{ format_money($pago->total_usd, 0) }}</div>
                                        <span class="badge bg-success-subtle text-success" style="border-radius: .375rem; font-size: .7rem;">Aprobado</span>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="ri ri-bank-card-line" style="font-size: 3rem; opacity: 0.3;"></i>
                            <p class="text-muted mt-2 mb-0">No hay pagos registrados</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Top Médicos -->
        <div class="col-lg-6">
            <div class="dashboard-card h-100">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="ri ri-user-star-line me-2" style="color: #8b5cf6;"></i>
                        Top Médicos Hoy
                    </h5>
                </div>
                <div class="card-body p-0">
                    @if(count($topMedicos) > 0)
                        <div class="px-3 py-2">
                            @foreach($topMedicos as $index => $medico)
                            <div class="medico-row">
                                <div class="d-flex align-items-center gap-3">
                                    <div style="width: 36px; height: 36px; border-radius: 50%; background: {{ $index === 0 ? '#fef3c7' : ($index === 1 ? '#e5e7eb' : '#fed7aa') }}; display: flex; align-items: center; justify-content: center; font-weight: 700; color: {{ $index === 0 ? '#d97706' : ($index === 1 ? '#6b7280' : '#ea580c') }};">
                                        {{ $index + 1 }}
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-semibold" style="font-size: .9rem;">{{ $medico->nombre_completo }}</h6>
                                        <small class="text-muted">{{ $medico->especialidad?->nombre ?? 'General' }}</small>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <span class="badge" style="background: #e0e7ff; color: #4f46e5; border-radius: .375rem; font-size: .8rem; font-weight: 600;">
                                        {{ $medico->citas_hoy_count }} cita{{ $medico->citas_hoy_count != 1 ? 's' : '' }}
                                    </span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="ri ri-user-star-line" style="font-size: 3rem; opacity: 0.3;"></i>
                            <p class="text-muted mt-2 mb-0">No hay datos disponibles</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>



    @push('scripts')
    <script>
        function initCitasChart(labels, data) {
            labels = Array.isArray(labels) ? labels : [];
            var arr = Array.isArray(data) ? data.map(function(v){ return Number(v) || 0; }) : [];
            var el = document.querySelector('#citasChart');
            if (!el) return;

            var config = {
                chart: { height: 300, type: 'bar', toolbar: { show: false } },
                plotOptions: { bar: { borderRadius: 6, columnWidth: '40%' } },
                dataLabels: { enabled: false },
                colors: ['#6366f1'],
                series: [{ name: 'Citas', data: arr }],
                xaxis: { categories: labels },
                yaxis: { min: 0 }
            };

            if (window.citasChart && typeof window.citasChart.updateOptions === 'function') {
                window.citasChart.updateOptions({ xaxis: { categories: labels } });
                window.citasChart.updateSeries([{ name: 'Citas', data: arr }]);
            } else {
                window.citasChart = new ApexCharts(el, config);
                window.citasChart.render();
            }
        }

        initCitasChart(@json($citasChartData['labels']), @json($citasChartData['data']));

        document.addEventListener('livewire:init', function () {
            Livewire.on('chartDataUpdated', function () {
                var args = Array.prototype.slice.call(arguments);
                var payload = args[0] || {};
                var chartData = payload.citasChartData || (Array.isArray(payload) ? payload[0]?.citasChartData : null);
                if (chartData && chartData.labels) {
                    initCitasChart(chartData.labels, chartData.data || []);
                }
            });
        });

        setInterval(function () {
            @this.loadDashboardData();
        }, 300000);
    </script>
    @endpush
</div>
