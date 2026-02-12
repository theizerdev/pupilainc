<div>
    @push('styles')
    <style>
        .doctor-dashboard-card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 0.125rem 0.25rem rgba(165, 163, 174, 0.3);
            transition: all 0.3s ease;
            height: 100%;
            background: var(--bs-white);
        }

        .doctor-dashboard-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.25rem 0.75rem rgba(165, 163, 174, 0.4);
        }

        .doctor-stats-card {
            background: linear-gradient(135deg, #4CAF50, #45a049);
            border: none;
            border-radius: 1rem;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .doctor-stats-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 50%;
            height: 100%;
            background: linear-gradient(135deg, transparent, rgba(255,255,255,0.1));
            border-radius: 0 1rem 1rem 0;
        }

        .doctor-stats-card .card-body {
            position: relative;
            z-index: 1;
        }

        .stats-icon-doctor {
            width: 3.5rem;
            height: 3.5rem;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            backdrop-filter: blur(10px);
        }

        .cita-card {
            border-left: 4px solid #4CAF50;
            background: linear-gradient(135deg, #f8fff8, #e8f5e8);
            border-radius: 0.75rem;
            padding: 1rem;
            margin-bottom: 0.75rem;
            transition: all 0.3s ease;
            position: relative;
        }

        .cita-card:hover {
            transform: translateX(4px);
            box-shadow: 0 0.25rem 0.5rem rgba(76, 175, 80, 0.15);
        }

        .cita-card.pendiente {
            border-left-color: #ff9800;
            background: linear-gradient(135deg, #fff8e1, #ffecb3);
        }

        .cita-card.completada {
            border-left-color: #2196F3;
            background: linear-gradient(135deg, #e3f2fd, #bbdefb);
        }

        .cita-card.cancelada {
            border-left-color: #f44336;
            background: linear-gradient(135deg, #ffebee, #ffcdd2);
        }

        .cita-hora {
            background: rgba(76, 175, 80, 0.1);
            color: #2e7d32;
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-weight: 600;
            font-size: 0.875rem;
        }

        .patient-avatar {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 1rem;
        }

        .action-btn-doctor {
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .btn-complete {
            background: #2196F3;
            color: white;
        }

        .btn-complete:hover {
            background: #1976D2;
            transform: translateY(-1px);
        }

        .btn-cancel {
            background: #f44336;
            color: white;
        }

        .btn-cancel:hover {
            background: #d32f2f;
            transform: translateY(-1px);
        }

        .btn-start {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }

        .btn-start:hover {
            background: linear-gradient(135deg, #5a6fd1, #6b439b);
            transform: translateY(-1px);
            color: white;
        }

        .btn-view {
            background: #757575;
            color: white;
        }

        .btn-view:hover {
            background: #616161;
            transform: translateY(-1px);
        }

        .chart-container-doctor {
            position: relative;
            height: 250px;
            padding: 1rem;
        }

        .welcome-banner {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            position: relative;
            overflow: hidden;
        }

        .welcome-banner::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 50%;
            height: 100%;
            background: linear-gradient(135deg, transparent, rgba(255,255,255,0.1));
        }

        .quick-actions-doctor {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .quick-action-btn {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            text-decoration: none;
            color: var(--bs-dark);
            background: var(--bs-light);
            transition: all 0.3s ease;
            border: 1px solid var(--bs-border-color);
            font-size: 0.875rem;
        }

        .quick-action-btn:hover {
            background: var(--bs-primary);
            color: white;
            border-color: var(--bs-primary);
            transform: translateY(-2px);
        }

        .status-badge {
            position: absolute;
            top: 0.5rem;
            right: 0.5rem;
            padding: 0.25rem 0.5rem;
            border-radius: 1rem;
            font-size: 0.75rem;
            font-weight: 600;
        }

        @media (max-width: 768px) {
            .stats-icon-doctor {
                width: 2.5rem;
                height: 2.5rem;
                font-size: 1.25rem;
            }

            .chart-container-doctor {
                height: 200px;
            }

            .quick-actions-doctor {
                flex-direction: column;
            }

            .cita-card {
                padding: 0.75rem;
            }
        }
    </style>
    @endpush

    <!-- Banner de Bienvenida -->
    <div class="welcome-banner">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h3 class="mb-1">
                    <i class="fas fa-user-md me-2"></i>
                    Bienvenido, Dr./Dra. {{ $medico->nombre ?? $medico->user->name }}
                </h3>
                <p class="mb-0 opacity-75">Aquí puedes gestionar tus citas y ver tu rendimiento</p>
            </div>
            <div class="col-md-4 text-end">
                <div class="text-end">
                    <div class="small opacity-75">Fecha actual</div>
                    <div class="fw-bold">{{ now()->format('d/m/Y') }}</div>
                    <div class="small">{{ now()->format('H:i') }} Hrs</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas del Médico -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card doctor-stats-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h5 class="card-title mb-1">Citas Hoy</h5>
                            <h2 class="mb-0">{{ $stats['citas_hoy'] }}</h2>
                            <small class="opacity-75">{{ $stats['citas_hoy'] > 0 ? 'Agendadas' : 'Sin citas' }}</small>
                        </div>
                        <div class="stats-icon-doctor">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card doctor-stats-card" style="background: linear-gradient(135deg, #2196F3, #1976D2);">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h5 class="card-title mb-1">Esta Semana</h5>
                            <h2 class="mb-0">{{ $stats['citas_semana'] }}</h2>
                            <small class="opacity-75">Citas programadas</small>
                        </div>
                        <div class="stats-icon-doctor">
                            <i class="fas fa-calendar-week"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card doctor-stats-card" style="background: linear-gradient(135deg, #FF9800, #F57C00);">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h5 class="card-title mb-1">Completadas</h5>
                            <h2 class="mb-0">{{ $stats['citas_completadas_mes'] }}</h2>
                            <small class="opacity-75">Este mes</small>
                        </div>
                        <div class="stats-icon-doctor">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card doctor-stats-card" style="background: linear-gradient(135deg, #9C27B0, #7B1FA2);">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h5 class="card-title mb-1">Pacientes</h5>
                            <h2 class="mb-0">{{ $stats['pacientes_unicos'] }}</h2>
                            <small class="opacity-75">Atendidos este mes</small>
                        </div>
                        <div class="stats-icon-doctor">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Acciones Rápidas -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card doctor-dashboard-card">
                <div class="card-body">
                    <h5 class="mb-3">
                        <i class="fas fa-bolt me-2 text-warning"></i>
                        Acciones Rápidas
                    </h5>
                    <div class="quick-actions-doctor">
                        <a href="{{ route('admin.citas.index') }}" class="quick-action-btn">
                            <i class="fas fa-plus-circle me-2 text-success"></i>
                            Nueva Cita
                        </a>
                        <a href="{{ route('admin.pacientes.create') }}" class="quick-action-btn">
                            <i class="fas fa-user-plus me-2 text-primary"></i>
                            Nuevo Paciente
                        </a>
                        <a href="{{ route('admin.citas.index') }}?medico={{ $medico->id }}" class="quick-action-btn">
                            <i class="fas fa-calendar-alt me-2 text-info"></i>
                            Mis Citas
                        </a>
                        <a href="{{ route('admin.pagos.index') }}" class="quick-action-btn">
                            <i class="fas fa-money-bill-wave me-2 text-warning"></i>
                            Mis Ingresos
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card doctor-dashboard-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-user-md me-2 text-info"></i>
                        Consultas en consultorio
                    </h5>
                    <span class="badge bg-info">{{ $consultasConsultorio->count() }} consultas</span>
                </div>
                <div class="card-body">
                    @if($consultasConsultorio->count() > 0)
                        @foreach($consultasConsultorio as $consulta)
                        <div class="cita-card en_consultorio">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <div class="patient-avatar">
                                        {{ substr($consulta->paciente->nombres ?? 'P', 0, 1) }}
                                    </div>
                                </div>
                                <div class="col">
                                    <h6 class="mb-1">{{ $consulta->paciente->nombres }} {{ $consulta->paciente->apellidos }}</h6>
                                    <small class="text-muted">
                                        <i class="fas fa-clock me-1"></i>
                                        {{ optional($consulta->fecha_consulta)->format('H:i') }}
                                    </small>
                                </div>
                                <div class="col-auto">
                                    <a href="{{ $consulta->cita_id ? route('doctor.consulta.proceso', $consulta->cita_id) : '#' }}" class="action-btn-doctor btn-start" title="Continuar consulta">
                                        <i class="fas fa-user-md"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-user-md text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-3 mb-0">No hay consultas en consultorio</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Citas de Hoy y Próximas -->
    <div class="row mb-4">
        <!-- Citas de Hoy -->
        <div class="col-lg-6 mb-4">
            <div class="card doctor-dashboard-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-day me-2 text-success"></i>
                        Mis Citas de Hoy
                    </h5>
                    <span class="badge bg-success">{{ $todayCitas->count() }} citas</span>
                </div>
                <div class="card-body">
                    @if($todayCitas->count() > 0)
                        @foreach($todayCitas as $cita)
                        <div class="cita-card {{ $cita->estado }}">
                            <div class="status-badge
                                @if($cita->estado == 'pendiente') bg-warning text-dark
                                @elseif($cita->estado == 'completada') bg-primary
                                @elseif($cita->estado == 'cancelada') bg-danger
                                @else bg-secondary
                                @endif">
                                {{ ucfirst($cita->estado) }}
                            </div>

                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <div class="patient-avatar">
                                        {{ substr($cita->paciente->nombre ?? 'P', 0, 1) }}
                                    </div>
                                </div>
                                <div class="col">
                                    <h6 class="mb-1">{{ $cita->paciente->nombre ?? 'Paciente' }}</h6>
                                    <small class="text-muted">
                                        <i class="fas fa-clock me-1"></i>
                                        {{ \Carbon\Carbon::parse($cita->hora)->format('H:i') }}
                                    </small>
                                    @if($cita->tipoConsulta)
                                    <br><small class="text-muted">
                                        <i class="fas fa-stethoscope me-1"></i>
                                        {{ $cita->tipoConsulta->nombre }}
                                    </small>
                                    @endif
                                </div>
                                <div class="col-auto">
                                    <div class="d-flex gap-1">
                                        @if($cita->estado == 'pendiente')
                                        <a href="{{ route('doctor.consulta.proceso', $cita->id) }}"
                                           class="action-btn-doctor btn-start"
                                           title="Iniciar Consulta">
                                            <i class="fas fa-user-md"></i>
                                        </a>
                                        <button class="action-btn-doctor btn-complete"
                                                wire:click="completarCita({{ $cita->id }})"
                                                title="Completar cita">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button class="action-btn-doctor btn-cancel"
                                                wire:click="cancelarCita({{ $cita->id }})"
                                                title="Cancelar cita">
                                            <i class="fas fa-times"></i>
                                        </button>
                                        @endif
                                        <a href="{{ route('admin.citas.index', $cita->id) }}"
                                           class="action-btn-doctor btn-view"
                                           title="Ver detalles">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-calendar-times text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-3 mb-0">No tienes citas programadas para hoy</p>
                            <a href="{{ route('admin.citas.index') }}" class="btn btn-success mt-2">
                                <i class="fas fa-plus me-1"></i>
                                Agendar Nueva Cita
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Próximas Citas de la Semana -->
        <div class="col-lg-6 mb-4">
            <div class="card doctor-dashboard-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-week me-2 text-primary"></i>
                        Próximas Citas
                    </h5>
                    <span class="badge bg-primary">Semana actual</span>
                </div>
                <div class="card-body">
                    @if($weekCitas->count() > 0)
                        @foreach($weekCitas as $cita)
                        <div class="cita-card {{ $cita->estado }}">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <div class="patient-avatar">
                                        {{ substr($cita->paciente->nombre ?? 'P', 0, 1) }}
                                    </div>
                                </div>
                                <div class="col">
                                    <h6 class="mb-1">{{ $cita->paciente->nombre ?? 'Paciente' }}</h6>
                                    <small class="text-muted">
                                        <i class="fas fa-calendar me-1"></i>
                                        {{ \Carbon\Carbon::parse($cita->fecha)->format('d/m') }}
                                        <i class="fas fa-clock ms-2 me-1"></i>
                                        {{ \Carbon\Carbon::parse($cita->hora)->format('H:i') }}
                                    </small>
                                    @if($cita->tipoConsulta)
                                    <br><small class="text-muted">
                                        <i class="fas fa-stethoscope me-1"></i>
                                        {{ $cita->tipoConsulta->nombre }}
                                    </small>
                                    @endif
                                </div>
                                <div class="col-auto">
                                    <span class="cita-hora">
                                        {{ \Carbon\Carbon::parse($cita->fecha)->format('D') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-calendar-check text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-3 mb-0">No tienes más citas programadas esta semana</p>
                        </div>
                    @endif
                </div>
                <div class="card-footer text-center">
                    <a href="{{ route('admin.citas.index') }}?medico={{ $medico->id }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-calendar me-1"></i>
                        Ver Todas Mis Citas
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos de Rendimiento -->
    <div class="row mb-4">
        <!-- Gráfico de Citas por Día -->
        <div class="col-lg-6 mb-4">
            <div class="card doctor-dashboard-card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-line me-2 text-success"></i>
                        Mis Citas - Últimos 7 Días
                    </h5>
                </div>
                <div class="card-body">
                    <div class="chart-container-doctor">
                        <canvas id="doctorCitasChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráfico de Ingresos -->
        <div class="col-lg-6 mb-4">
            <div class="card doctor-dashboard-card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-dollar-sign me-2 text-warning"></i>
                        Mis Ingresos - Últimos 6 Meses
                    </h5>
                </div>
                <div class="card-body">
                    <div class="chart-container-doctor">
                        <canvas id="doctorIngresosChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Resumen Mensual -->
    <div class="row">
        <div class="col-12">
            <div class="card doctor-dashboard-card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-bar me-2 text-info"></i>
                        Resumen del Mes
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3 mb-3">
                            <div class="border-end border-md-end-0">
                                <h3 class="text-success mb-1">{{ $stats['citas_mes'] }}</h3>
                                <small class="text-muted">Total Citas</small>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="border-end border-md-end-0">
                                <h3 class="text-primary mb-1">{{ $stats['citas_completadas_mes'] }}</h3>
                                <small class="text-muted">Completadas</small>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="border-end border-md-end-0">
                                <h3 class="text-warning mb-1">{{ $stats['pacientes_unicos'] }}</h3>
                                <small class="text-muted">Pacientes Únicos</small>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <h3 class="text-info mb-1">${{ number_format($stats['ingresos_mes'], 0) }}</h3>
                            <small class="text-muted">Ingresos Totales</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Gráfico de Citas del Doctor
        const doctorCitasCtx = document.getElementById('doctorCitasChart').getContext('2d');
        new Chart(doctorCitasCtx, {
            type: 'line',
            data: {
                labels: @json($citasChartData['labels']),
                datasets: [{
                    label: 'Citas',
                    data: @json($citasChartData['data']),
                    borderColor: '#4CAF50',
                    backgroundColor: 'rgba(76, 175, 80, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#4CAF50',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0,0,0,0.1)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // Gráfico de Ingresos del Doctor
        const doctorIngresosCtx = document.getElementById('doctorIngresosChart').getContext('2d');
        new Chart(doctorIngresosCtx, {
            type: 'bar',
            data: {
                labels: @json($ingresosChartData['labels']),
                datasets: [{
                    label: 'Ingresos',
                    data: @json($ingresosChartData['data']),
                    backgroundColor: 'rgba(255, 193, 7, 0.8)',
                    borderColor: '#FFC107',
                    borderWidth: 2,
                    borderRadius: 8,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0,0,0,0.1)'
                        },
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // Auto-refresh cada 3 minutos para el doctor
        setInterval(() => {
            @this.loadDashboardData();
        }, 180000);

        // Notificaciones de éxito/error
        window.addEventListener('notify', event => {
            if (event.detail.type === 'success') {
                toastr.success(event.detail.message);
            } else {
                toastr.error(event.detail.message);
            }
        });
    </script>
    @endpush
</div>
