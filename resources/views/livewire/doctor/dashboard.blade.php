<div>
    @push('styles')
    <style>
        /* Hero Section */
        .doctor-hero {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #fff;
            border-radius: 0.75rem;
            padding: 1.4rem 1.6rem;
        }
        .doctor-hero h2 {
            color: #fff;
            margin: 0;
        }
        .doctor-hero p {
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

        /* Cita Item */
        .cita-item {
            padding: .85rem;
            border-left: 3px solid #10b981;
            border-radius: .5rem;
            margin-bottom: .75rem;
            background: #fff;
            transition: all .2s;
        }
        .cita-item:hover {
            box-shadow: 0 2px 8px rgba(0,0,0,.06);
            transform: translateX(2px);
        }
        .cita-item:last-child {
            margin-bottom: 0;
        }
        .cita-item.programada {
            border-left-color: #f59e0b;
            background: #fffbeb;
        }
        .cita-item.finalizada {
            border-left-color: #3b82f6;
            background: #eff6ff;
        }
        .cita-item.cancelada {
            border-left-color: #ef4444;
            background: #fef2f2;
        }

        /* Patient Avatar */
        .patient-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: .9rem;
            flex-shrink: 0;
        }

        /* Action Buttons */
        .btn-action-doctor {
            border-radius: .5rem;
            padding: .4rem .75rem;
            font-size: .8rem;
            font-weight: 600;
            transition: all .2s;
            border: none;
        }
        .btn-complete {
            background: #3b82f6;
            color: white;
        }
        .btn-complete:hover {
            background: #2563eb;
            transform: translateY(-1px);
        }
        .btn-cancel {
            background: #ef4444;
            color: white;
        }
        .btn-cancel:hover {
            background: #dc2626;
            transform: translateY(-1px);
        }
        .btn-start {
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
            color: white;
        }
        .btn-start:hover {
            transform: translateY(-1px);
            color: white;
        }
    </style>
    @endpush

    <!-- Hero Section -->
    <div class="doctor-hero mb-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <h2 class="fw-semibold"><i class="ri ri-stethoscope-line me-2"></i>Bienvenido, Dr. {{ $medico->nombres }}</h2>
            <p class="mt-1">{{ $medico->especialidad?->nombre ?? 'Médico' }} - Panel de control</p>
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
                    <div class="stat-label">Citas Hoy</div>
                    <div class="stat-value">{{ $stats['citas_hoy'] }}</div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#dcfce7;color:#16a34a;"><i class="ri ri-calendar-week-line"></i></div>
                <div>
                    <div class="stat-label">Esta Semana</div>
                    <div class="stat-value text-success">{{ $stats['citas_semana'] }}</div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#e0e7ff;color:#4f46e5;"><i class="ri ri-calendar-month-line"></i></div>
                <div>
                    <div class="stat-label">Este Mes</div>
                    <div class="stat-value text-primary">{{ $stats['citas_mes'] }}</div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#fef3c7;color:#d97706;"><i class="ri ri-user-heart-line"></i></div>
                <div>
                    <div class="stat-label">Pacientes Únicos</div>
                    <div class="stat-value text-warning">{{ $stats['pacientes_unicos'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Consultas en Consultorio y Citas de Hoy -->
    <div class="row g-4 mb-4">
        <!-- Consultas en Consultorio -->
        <div class="col-lg-4">
            <div class="dashboard-card h-100">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="ri ri-door-open-line me-2" style="color: #8b5cf6;"></i>
                        En Consultorio
                    </h5>
                </div>
                <div class="card-body">
                    @if($consultasConsultorio->count() > 0)
                        @foreach($consultasConsultorio as $consulta)
                        <div class="cita-item" style="border-left-color: #8b5cf6;">
                            <div class="d-flex align-items-center gap-3">
                                <div class="patient-avatar">
                                    {{ substr($consulta->paciente->nombres ?? 'P', 0, 1) }}{{ substr($consulta->paciente->apellidos ?? '', 0, 1) }}
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 fw-semibold" style="font-size: .9rem;">{{ $consulta->paciente->nombre_completo }}</h6>
                                    <small class="text-muted">
                                        <i class="ri ri-time-line me-1"></i>{{ \Carbon\Carbon::parse($consulta->fecha_consulta)->format('H:i') }}
                                    </small>
                                </div>
                            </div>
                            <div class="mt-2">
                                <a href="{{ route('admin.consulta.proceso', $consulta->id) }}" class="btn btn-start btn-action-doctor w-100">
                                    <i class="ri ri-play-line me-1"></i>Iniciar Consulta
                                </a>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <div class="text-center py-4">
                            <i class="ri ri-door-lock-line" style="font-size: 3rem; opacity: 0.3;"></i>
                            <p class="text-muted mt-2 mb-0">No hay pacientes en consultorio</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Citas de Hoy -->
        <div class="col-lg-8">
            <div class="dashboard-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="ri ri-calendar-event-line me-2 text-success"></i>
                        Citas de Hoy
                    </h5>
                    <span class="badge bg-success-subtle text-success" style="border-radius: .375rem;">{{ $todayCitas->count() }} citas</span>
                </div>
                <div class="card-body">
                    @if($todayCitas->count() > 0)
                        @foreach($todayCitas as $cita)
                        <div class="cita-item {{ $cita->estado }}">
                            <div class="d-flex align-items-start gap-3">
                                <div class="patient-avatar">
                                    {{ substr($cita->paciente->nombres ?? 'P', 0, 1) }}{{ substr($cita->paciente->apellidos ?? '', 0, 1) }}
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1 fw-semibold">{{ $cita->paciente->nombre_completo }}</h6>
                                            <p class="text-muted mb-1 small">
                                                <i class="ri ri-file-text-line me-1"></i>{{ $cita->tipoConsulta?->nombre ?? 'Consulta General' }}
                                            </p>
                                            <p class="text-muted mb-0 small">
                                                <i class="ri ri-time-line me-1"></i>{{ \Carbon\Carbon::parse($cita->fecha_inicio)->format('H:i') }}
                                            </p>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge mb-2" style="background: {{ $cita->estado === 'programada' ? '#f59e0b' : ($cita->estado === 'finalizada' ? '#3b82f6' : ($cita->estado === 'cancelada' ? '#ef4444' : '#10b981')) }}; color: white; border-radius: .375rem;">
                                                {{ ucfirst($cita->estado) }}
                                            </span>

                                            @if($cita->estado === 'programada')
                                                <div class="d-flex gap-2">
                                                    <button class="btn btn-complete btn-action-doctor"
                                                            wire:click="completarCita({{ $cita->id }})"
                                                            wire:confirm="¿Marcar esta cita como finalizada?">
                                                        <i class="ri ri-check-line me-1"></i>Completar
                                                    </button>
                                                    <button class="btn btn-cancel btn-action-doctor"
                                                            wire:click="cancelarCita({{ $cita->id }})"
                                                            wire:confirm="¿Cancelar esta cita?">
                                                        <i class="ri ri-close-line me-1"></i>Cancelar
                                                    </button>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <div class="text-center py-5">
                            <i class="ri ri-calendar-line" style="font-size: 3rem; opacity: 0.3;"></i>
                            <p class="text-muted mt-2 mb-0">No hay citas programadas para hoy</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Gráfico de Citas -->
    <div class="row g-4 mb-4">
        <div class="col-lg-12">
            <div class="dashboard-card">
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
                colors: ['#10b981'],
                series: [{ name: 'Citas', data: arr }],
                xaxis: { categories: labels },
                yaxis: { min: 0 }
            };

            if (window.doctorCitasChart && typeof window.doctorCitasChart.updateOptions === 'function') {
                window.doctorCitasChart.updateOptions({ xaxis: { categories: labels } });
                window.doctorCitasChart.updateSeries([{ name: 'Citas', data: arr }]);
            } else {
                window.doctorCitasChart = new ApexCharts(el, config);
                window.doctorCitasChart.render();
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
    </script>
    @endpush
</div>
