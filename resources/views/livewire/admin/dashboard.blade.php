<div>
    @push('styles')
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

        .chart-container {
            position: relative;
            height: 300px;
            padding: 1rem;
        }

        .module-card {
            background: var(--bs-light);
            border: 1px solid var(--bs-border-color);
            border-radius: 0.5rem;
            padding: 1.5rem;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            height: 100%;
        }

        .module-card:hover {
            background: var(--bs-white);
            border-color: var(--bs-primary);
            transform: translateY(-2px);
            box-shadow: 0 0.25rem 0.75rem rgba(var(--bs-primary-rgb), 0.15);
        }

        .module-icon {
            width: 4rem;
            height: 4rem;
            margin: 0 auto 1rem;
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            color: white;
        }

        .cita-item {
            padding: 0.75rem;
            border-left: 3px solid var(--bs-primary);
            background: var(--bs-light);
            border-radius: 0 0.5rem 0.5rem 0;
            margin-bottom: 0.5rem;
            transition: all 0.3s ease;
        }

        .cita-item:hover {
            background: var(--bs-white);
            box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.1);
        }

        .badge-status {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            border-radius: 0.375rem;
            font-weight: 500;
        }

        .quick-actions {
            background: var(--bs-white);
            border-radius: 0.75rem;
            padding: 1.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(165, 163, 174, 0.3);
        }

        .action-btn {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            text-decoration: none;
            color: var(--bs-dark);
            transition: all 0.3s ease;
            border: 1px solid var(--bs-border-color);
        }

        .action-btn:hover {
            background: var(--bs-primary);
            color: white;
            border-color: var(--bs-primary);
            transform: translateX(4px);
        }

        @media (max-width: 768px) {
            .stats-icon {
                width: 2.5rem;
                height: 2.5rem;
                font-size: 1.25rem;
            }
            
            .module-icon {
                width: 3rem;
                height: 3rem;
                font-size: 1.25rem;
            }
            
            .chart-container {
                height: 250px;
            }
        }
    </style>
    @endpush

    <!-- Encabezado del Dashboard -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">Dashboard Principal</h2>
                    <p class="text-muted mb-0">Resumen general del sistema médico</p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-primary" wire:click="loadDashboardData">
                        <i class="fas fa-sync-alt me-1"></i>Actualizar
                    </button>
                    <div class="text-muted small">
                        <i class="fas fa-clock me-1"></i>
                        {{ now()->format('d/m/Y H:i') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tarjetas de Estadísticas -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stats-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h5 class="card-title mb-1">Citas Hoy</h5>
                            <h2 class="mb-0">{{ $stats['citas_hoy'] }}</h2>
                            <small class="opacity-75">Total: {{ $stats['total_citas'] }}</small>
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
                            <h2 class="mb-0">{{ $stats['total_pacientes'] }}</h2>
                            <small class="opacity-75">Registrados</small>
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
                            <h2 class="mb-0">{{ $stats['total_medicos'] }}</h2>
                            <small class="opacity-75">Activos</small>
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
                            <h2 class="mb-0">${{ number_format($stats['ingresos_mes'], 0) }}</h2>
                            <small class="opacity-75">{{ now()->format('F') }}</small>
                        </div>
                        <div class="stats-icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos y Módulos -->
    <div class="row mb-4">
        <!-- Gráfico de Citas -->
        <div class="col-lg-8 mb-4">
            <div class="card dashboard-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-line me-2 text-primary"></i>
                        Citas por Día - Últimos 7 días
                    </h5>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#"><i class="fas fa-download me-2"></i>Exportar</a></li>
                            <li><a class="dropdown-item" href="#"><i class="fas fa-expand me-2"></i>Ampliar</a></li>
                        </ul>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="citasChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Próximas Citas -->
        <div class="col-lg-4 mb-4">
            <div class="card dashboard-card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-clock me-2 text-primary"></i>
                        Próximas Citas
                    </h5>
                </div>
                <div class="card-body">
                    @if($recentCitas->count() > 0)
                        @foreach($recentCitas as $cita)
                        <div class="cita-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1">{{ $cita->paciente->nombre ?? 'Paciente' }}</h6>
                                    <small class="text-muted">
                                        <i class="fas fa-user-md me-1"></i>
                                        {{ $cita->medico->nombre ?? 'Médico' }}
                                    </small>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold">{{ \Carbon\Carbon::parse($cita->hora)->format('H:i') }}</div>
                                    <small class="text-muted">{{ \Carbon\Carbon::parse($cita->fecha)->format('d/m') }}</small>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-calendar-times text-muted" style="font-size: 2rem;"></i>
                            <p class="text-muted mt-2">No hay citas programadas</p>
                        </div>
                    @endif
                </div>
                <div class="card-footer text-center">
                    <a href="{{ route('admin.citas.index') }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-calendar me-1"></i>Ver Todas
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Módulos del Sistema -->
    <div class="row mb-4">
        <div class="col-12">
            <h4 class="mb-3">
                <i class="fas fa-th-large me-2 text-primary"></i>
                Módulos del Sistema
            </h4>
        </div>

        <!-- Citas -->
        <div class="col-lg-3 col-md-6 mb-4">
            <a href="{{ route('admin.citas.index') }}" class="text-decoration-none">
                <div class="module-card">
                    <div class="module-icon" style="background: linear-gradient(135deg, #667eea, #764ba2);">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <h5 class="mb-2">Citas</h5>
                    <p class="text-muted mb-2 small">Gestión de citas médicas y horarios</p>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="badge bg-primary">{{ $stats['total_citas'] }} total</span>
                        <i class="fas fa-arrow-right text-primary"></i>
                    </div>
                </div>
            </a>
        </div>

        <!-- Médicos -->
        <div class="col-lg-3 col-md-6 mb-4">
            <a href="{{ route('admin.medicos.index') }}" class="text-decoration-none">
                <div class="module-card">
                    <div class="module-icon" style="background: linear-gradient(135deg, #17a2b8, #138496);">
                        <i class="fas fa-user-md"></i>
                    </div>
                    <h5 class="mb-2">Médicos</h5>
                    <p class="text-muted mb-2 small">Perfiles y horarios de médicos</p>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="badge bg-info">{{ $stats['total_medicos'] }} activos</span>
                        <i class="fas fa-arrow-right text-info"></i>
                    </div>
                </div>
            </a>
        </div>

        <!-- Pacientes -->
        <div class="col-lg-3 col-md-6 mb-4">
            <a href="{{ route('admin.pacientes.index') }}" class="text-decoration-none">
                <div class="module-card">
                    <div class="module-icon" style="background: linear-gradient(135deg, #28a745, #20c997);">
                        <i class="fas fa-users"></i>
                    </div>
                    <h5 class="mb-2">Pacientes</h5>
                    <p class="text-muted mb-2 small">Registro y historial de pacientes</p>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="badge bg-success">{{ $stats['total_pacientes'] }} registrados</span>
                        <i class="fas fa-arrow-right text-success"></i>
                    </div>
                </div>
            </a>
        </div>

        <!-- Cajas -->
        <div class="col-lg-3 col-md-6 mb-4">
            <a href="{{ route('admin.cajas.index') }}" class="text-decoration-none">
                <div class="module-card">
                    <div class="module-icon" style="background: linear-gradient(135deg, #ffc107, #fd7e14);">
                        <i class="fas fa-cash-register"></i>
                    </div>
                    <h5 class="mb-2">Cajas</h5>
                    <p class="text-muted mb-2 small">Control de cajas y arqueos</p>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="badge bg-warning">{{ $stats['cajas_activas'] }} abiertas</span>
                        <i class="fas fa-arrow-right text-warning"></i>
                    </div>
                </div>
            </a>
        </div>

        <!-- Pagos -->
        <div class="col-lg-3 col-md-6 mb-4">
            <a href="{{ route('admin.pagos.index') }}" class="text-decoration-none">
                <div class="module-card">
                    <div class="module-icon" style="background: linear-gradient(135deg, #dc3545, #e83e8c);">
                        <i class="fas fa-credit-card"></i>
                    </div>
                    <h5 class="mb-2">Pagos</h5>
                    <p class="text-muted mb-2 small">Gestión de pagos y comprobantes</p>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="badge bg-danger">${{ number_format($stats['ingresos_mes'], 0) }} mes</span>
                        <i class="fas fa-arrow-right text-danger"></i>
                    </div>
                </div>
            </a>
        </div>

        <!-- Especialidades -->
        <div class="col-lg-3 col-md-6 mb-4">
            <a href="{{ route('admin.especialidades.index') }}" class="text-decoration-none">
                <div class="module-card">
                    <div class="module-icon" style="background: linear-gradient(135deg, #6f42c1, #8e44ad);">
                        <i class="fas fa-stethoscope"></i>
                    </div>
                    <h5 class="mb-2">Especialidades</h5>
                    <p class="text-muted mb-2 small">Catálogo de especialidades médicas</p>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="badge bg-purple">Médicas</span>
                        <i class="fas fa-arrow-right text-purple"></i>
                    </div>
                </div>
            </a>
        </div>

        <!-- Empresas -->
        <div class="col-lg-3 col-md-6 mb-4">
            <a href="{{ route('admin.empresas.index') }}" class="text-decoration-none">
                <div class="module-card">
                    <div class="module-icon" style="background: linear-gradient(135deg, #20c997, #17a2b8);">
                        <i class="fas fa-building"></i>
                    </div>
                    <h5 class="mb-2">Empresas</h5>
                    <p class="text-muted mb-2 small">Gestión de empresas y sucursales</p>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="badge bg-teal">{{ $stats['total_empresas'] }} registradas</span>
                        <i class="fas fa-arrow-right text-teal"></i>
                    </div>
                </div>
            </a>
        </div>

        <!-- WhatsApp -->
        <div class="col-lg-3 col-md-6 mb-4">
            <a href="{{ route('admin.whatsapp.dashboard') }}" class="text-decoration-none">
                <div class="module-card">
                    <div class="module-icon" style="background: linear-gradient(135deg, #25d366, #128c7e);">
                        <i class="fab fa-whatsapp"></i>
                    </div>
                    <h5 class="mb-2">WhatsApp</h5>
                    <p class="text-muted mb-2 small">Mensajes y notificaciones</p>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="badge bg-success">{{ $stats['mensajes_whatsapp'] }} hoy</span>
                        <i class="fas fa-arrow-right text-success"></i>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Acciones Rápidas -->
    <div class="row">
        <div class="col-12">
            <div class="quick-actions">
                <h5 class="mb-3">
                    <i class="fas fa-bolt me-2 text-warning"></i>
                    Acciones Rápidas
                </h5>
                <div class="row">
                    <div class="col-md-3 mb-2">
                        <a href="{{ route('admin.citas.index') }}" class="action-btn">
                            <i class="fas fa-plus-circle me-2 text-primary"></i>
                            Nueva Cita
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="{{ route('admin.pacientes.create') }}" class="action-btn">
                            <i class="fas fa-user-plus me-2 text-success"></i>
                            Nuevo Paciente
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="{{ route('admin.pagos.create') }}" class="action-btn">
                            <i class="fas fa-money-bill-wave me-2 text-warning"></i>
                            Registrar Pago
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="{{ route('admin.cajas.create') }}" class="action-btn">
                            <i class="fas fa-cash-register me-2 text-info"></i>
                            Abrir Caja
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Gráfico de Citas
        const citasCtx = document.getElementById('citasChart').getContext('2d');
        new Chart(citasCtx, {
            type: 'line',
            data: {
                labels: @json($citasChartData['labels']),
                datasets: [{
                    label: 'Citas',
                    data: @json($citasChartData['data']),
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#667eea',
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
                },
                elements: {
                    point: {
                        hoverRadius: 8
                    }
                }
            }
        });

        // Auto-refresh cada 5 minutos
        setInterval(() => {
            @this.loadDashboardData();
        }, 300000);
    </script>
    @endpush
</div>