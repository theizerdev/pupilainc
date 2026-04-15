<div>
    <style>
        .dashboard-card {
            border: none;
            border-radius: 0.75rem;
            box-shadow: 0 0.125rem 0.25rem rgba(165, 163, 174, 0.3);
            transition: all 0.3s ease;
            height: 100%;
        }

        .dashboard-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.25rem 0.75rem rgba(165, 163, 174, 0.4);
        }

        .stats-card {
            background: linear-gradient(135deg, var(--bs-primary), var(--bs-primary-dark, #5a3cc7));
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
            width: 3.5rem;
            height: 3.5rem;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            backdrop-filter: blur(10px);
        }
    </style>

    <!-- Encabezado -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">Dashboard</h2>
                    <p class="text-muted mb-0">Sistema Médico</p>
                </div>
                <button class="btn btn-outline-primary" wire:click="loadDashboardData">
                    <i class="fas fa-sync-alt me-1"></i>Actualizar
                </button>
            </div>
        </div>
    </div>

    <!-- Estadísticas Principales -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stats-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h5 class="card-title mb-1">Citas Hoy</h5>
                            <h2 class="mb-0">{{ $stats['citas_hoy'] }}</h2>
                        </div>
                        <div class="stats-icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stats-card" style="background: linear-gradient(135deg, #28a745, #1e7e34);">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h5 class="card-title mb-1">Pacientes</h5>
                            <h2 class="mb-0">{{ $stats['pacientes_total'] }}</h2>
                        </div>
                        <div class="stats-icon">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stats-card" style="background: linear-gradient(135deg, #17a2b8, #117a8b);">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h5 class="card-title mb-1">Médicos</h5>
                            <h2 class="mb-0">{{ $stats['medicos_total'] }}</h2>
                        </div>
                        <div class="stats-icon">
                            <i class="fas fa-user-md"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stats-card" style="background: linear-gradient(135deg, #ffc107, #e0a800);">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h5 class="card-title mb-1">Ingresos Mes</h5>
                            <h2 class="mb-0">{{ format_money($stats['ingresos_mes'], 0) }}</h2>
                        </div>
                        <div class="stats-icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráfico y Citas Recientes -->
    <div class="row mb-4">
        <!-- Gráfico de Citas -->
        <div class="col-lg-8 mb-4">
            <div class="card dashboard-card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-line me-2 text-primary"></i>
                        Citas de los Últimos 7 Días
                    </h5>
                </div>
                <div class="card-body" wire:ignore>
                    <div id="citasChart"></div>
                </div>
            </div>
        </div>

        <!-- Próximas Citas -->
        <div class="col-lg-4 mb-4">
            <div class="card dashboard-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-alt me-2 text-primary"></i>
                        Próximas Citas
                    </h5>
                    <a href="{{ route('admin.citas.index') }}" class="btn btn-sm btn-outline-primary">Ver todas</a>
                </div>
                <div class="card-body">
                    @if($recentCitas->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($recentCitas as $cita)
                            <div class="list-group-item px-0">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">{{ $cita->paciente->nombre_completo ?? 'Sin nombre' }}</h6>
                                        <p class="text-muted mb-1 small">
                                            <i class="fas fa-user-md me-1"></i>{{ $cita->medico->nombre_completo ?? 'Sin médico' }}
                                        </p>
                                        <p class="text-muted mb-0 small">
                                            <i class="fas fa-clock me-1"></i>{{ \Carbon\Carbon::parse($cita->fecha_inicio)->format('d/m H:i') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No hay citas programadas</p>
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
                colors: ['#667eea'],
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
