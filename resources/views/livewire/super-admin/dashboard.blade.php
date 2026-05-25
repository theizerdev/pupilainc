<div>
    @push('styles')
    <style>
        /* Hero Section */
        .superadmin-hero {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            color: #fff;
            border-radius: 0.75rem;
            padding: 1.4rem 1.6rem;
        }
        .superadmin-hero h2 {
            color: #fff;
            margin: 0;
        }
        .superadmin-hero p {
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

        /* Table Styles */
        .table-modern thead th {
            background: #f8f9fa;
            border-bottom: 2px solid #e2e8f0;
            font-weight: 600;
            font-size: .85rem;
            text-transform: uppercase;
            letter-spacing: .5px;
            color: #64748b;
            padding: 1rem;
        }
        .table-modern tbody td {
            padding: 1rem;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
        }
        .table-modern tbody tr:hover {
            background-color: #f8fafc;
        }
        .table-modern tbody tr:last-child td {
            border-bottom: none;
        }

        /* Server Info */
        .server-info-item {
            padding: .75rem 0;
            border-bottom: 1px solid #f1f5f9;
        }
        .server-info-item:last-child {
            border-bottom: none;
        }
        .server-info-item strong {
            color: #64748b;
            font-size: .85rem;
            display: block;
            margin-bottom: .25rem;
        }
        .server-info-item span {
            font-size: .95rem;
            font-weight: 600;
            color: #2d3748;
        }
    </style>
    @endpush

    <!-- Hero Section -->
    <div class="superadmin-hero mb-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <h2 class="fw-semibold"><i class="ri ri-shield-user-line me-2"></i>Super Administrador</h2>
            <p class="mt-1">Panel de control del sistema - Gestión global</p>
        </div>
        <div class="d-flex gap-2">
            <select class="form-select form-select-sm" wire:model.live="dateRange" style="width: auto;">
                <option value="week">Última semana</option>
                <option value="month">Último mes</option>
                <option value="quarter">Último trimestre</option>
                <option value="year">Último año</option>
            </select>
            <button class="btn btn-light btn-sm">
                <i class="ri ri-refresh-line me-1"></i>Actualizar
            </button>
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-2">
            <div class="stat-card">
                <div class="stat-icon" style="background:#dbeafe;color:#2563eb;"><i class="ri ri-user-line"></i></div>
                <div>
                    <div class="stat-label">Usuarios</div>
                    <div class="stat-value">{{ $totalUsers }}</div>
                    @if(isset($comparisonData['users']))
                        <small class="{{ $comparisonData['users']['change'] >= 0 ? 'text-success' : 'text-danger' }}" style="font-size: .7rem;">
                            {{ $comparisonData['users']['change'] >= 0 ? '+' : '' }}{{ $comparisonData['users']['change'] }}%
                        </small>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-2">
            <div class="stat-card">
                <div class="stat-icon" style="background:#dcfce7;color:#16a34a;"><i class="ri ri-building-line"></i></div>
                <div>
                    <div class="stat-label">Empresas</div>
                    <div class="stat-value text-success">{{ $totalEmpresas }}</div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-2">
            <div class="stat-card">
                <div class="stat-icon" style="background:#fef3c7;color:#d97706;"><i class="ri ri-map-pin-line"></i></div>
                <div>
                    <div class="stat-label">Sucursales</div>
                    <div class="stat-value text-warning">{{ $totalSucursales }}</div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-2">
            <div class="stat-card">
                <div class="stat-icon" style="background:#e0e7ff;color:#4f46e5;"><i class="ri ri-shield-line"></i></div>
                <div>
                    <div class="stat-label">Roles</div>
                    <div class="stat-value text-primary">{{ $totalRoles }}</div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-2">
            <div class="stat-card">
                <div class="stat-icon" style="background:#fce7f3;color:#db2777;"><i class="ri ri-key-line"></i></div>
                <div>
                    <div class="stat-label">Permisos</div>
                    <div class="stat-value" style="color: #db2777;">{{ $totalPermissions }}</div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-2">
            <div class="stat-card">
                <div class="stat-icon" style="background:#cffafe;color:#0891b2;"><i class="ri ri-wifi-line"></i></div>
                <div>
                    <div class="stat-label">Sesiones Activas</div>
                    <div class="stat-value" style="color: #0891b2;">{{ $totalActiveSessions }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos Principales -->
    <div class="row g-4 mb-4">
        <!-- Usuarios por Período -->
        <div class="col-lg-8">
            <div class="dashboard-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="ri ri-user-add-line me-2 text-primary"></i>
                        Usuarios Registrados
                    </h5>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-primary btn-sm" style="border-radius: .5rem;">
                        Ver todos
                    </a>
                </div>
                <div class="card-body" wire:ignore>
                    <div id="usersChart"></div>
                    <script class="users-data" type="application/json">
                        @json($usersByPeriod)
                    </script>
                </div>
            </div>
        </div>

        <!-- Estado de Sesiones -->
        <div class="col-lg-4">
            <div class="dashboard-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="ri ri-wifi-line me-2" style="color: #0891b2;"></i>
                        Estado de Sesiones
                    </h5>
                    <a href="{{ route('admin.active-sessions.index') }}" class="btn btn-outline-info btn-sm" style="border-radius: .5rem;">
                        Ver todas
                    </a>
                </div>
                <div class="card-body" wire:ignore>
                    <div id="sessionsChart"></div>
                    <script class="sessions-data" type="application/json">
                        @json($sessionsByStatus)
                    </script>
                </div>
            </div>
        </div>
    </div>

    <!-- Historial de Sesiones y Usuarios por Empresa -->
    <div class="row g-4 mb-4">
        <!-- Historial de Sesiones -->
        <div class="col-lg-4">
            <div class="dashboard-card h-100">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="ri ri-history-line me-2" style="color: #8b5cf6;"></i>
                        Historial de Sesiones
                    </h5>
                </div>
                <div class="card-body" wire:ignore>
                    <div id="sessionsHistoryChart"></div>
                    <div class="mt-3">
                        <p class="text-muted small mb-0">
                            En el último período has tenido <strong>{{ $totalActiveSessions }}</strong> sesiones activas,
                            <strong>{{ $sessionsByStatus['inactive'] }}</strong> inactivas y
                            <strong>{{ $totalUsers }}</strong> usuarios registrados.
                        </p>
                    </div>
                    <script class="sessions-history-data" type="application/json">
                        @json($sessionsByStatus)
                    </script>
                </div>
                <script class="total-users-data" type="application/json">
                    @json($totalUsers)
                </script>
            </div>
        </div>

        <!-- Usuarios por Empresa -->
        <div class="col-lg-8">
            <div class="dashboard-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="ri ri-building-4-line me-2" style="color: #16a34a;"></i>
                        Usuarios por Empresa
                    </h5>
                    <a href="{{ route('admin.empresas.index') }}" class="btn btn-outline-success btn-sm" style="border-radius: .5rem;">
                        Ver empresas
                    </a>
                </div>
                <div class="card-body" wire:ignore>
                    <div id="usersByEmpresaChart"></div>
                    <script class="users-by-empresa-data" type="application/json">
                        @json(['labels' => array_keys($usersByEmpresa), 'values' => array_values($usersByEmpresa)])
                    </script>
                </div>
            </div>
        </div>
    </div>

    <!-- Accesos al Sistema -->
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="dashboard-card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="ri ri-login-box-line me-2" style="color: #f59e0b;"></i>
                        Accesos al Sistema
                    </h5>
                </div>
                <div class="card-body" wire:ignore>
                    <div id="loginsChart"></div>
                    <script class="logins-data" type="application/json">
                        @json($loginsByPeriod)
                    </script>
                </div>
            </div>
        </div>
    </div>

    <!-- Distribución de Permisos -->
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="dashboard-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="ri ri-shield-check-line me-2" style="color: #db2777;"></i>
                        Distribución de Permisos por Rol
                    </h5>
                    <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-pink btn-sm" style="border-radius: .5rem; border-color: #db2777; color: #db2777;">
                        Ver roles
                    </a>
                </div>
                <div class="card-body" wire:ignore>
                    <div id="permissionsChart"></div>
                    <script class="permissions-data" type="application/json">
                        @json(['labels' => array_keys($permissionsStats), 'values' => array_values($permissionsStats)])
                    </script>
                </div>
            </div>
        </div>
    </div>

    <!-- Información del Servidor -->
    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="dashboard-card h-100">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="ri ri-server-line me-2 text-primary"></i>
                        Información del Servidor
                    </h5>
                </div>
                <div class="card-body">
                    <div class="server-info-item">
                        <strong>Versión de PHP</strong>
                        <span><i class="ri ri-code-s-slash-line me-1"></i>{{ $serverInfo['php_version'] }}</span>
                    </div>
                    <div class="server-info-item">
                        <strong>Versión de Laravel</strong>
                        <span><i class="ri ri-braces-line me-1"></i>{{ $serverInfo['laravel_version'] }}</span>
                    </div>
                    <div class="server-info-item">
                        <strong>Base de Datos</strong>
                        <span><i class="ri ri-database-2-line me-1"></i>{{ ucfirst($serverInfo['database']) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="dashboard-card h-100">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="ri ri-settings-3-line me-2" style="color: #64748b;"></i>
                        Configuración del Sistema
                    </h5>
                </div>
                <div class="card-body">
                    <div class="server-info-item">
                        <strong>Servidor</strong>
                        <span>{{ $serverInfo['server_software'] ?? 'N/A' }}</span>
                    </div>
                    <div class="server-info-item">
                        <strong>Sistema Operativo</strong>
                        <span>{{ $serverInfo['server_os'] }}</span>
                    </div>
                    <div class="server-info-item">
                        <strong>Uso de Memoria</strong>
                        <span><i class="ri ri-cpu-line me-1"></i>{{ $serverInfo['memory_usage'] }}</span>
                    </div>
                    <div class="server-info-item">
                        <strong>Tiempo Máximo de Ejecución</strong>
                        <span><i class="ri ri-timer-line me-1"></i>{{ $serverInfo['max_execution_time'] }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Últimas Sesiones -->
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="dashboard-card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="ri ri-time-line me-2" style="color: #0891b2;"></i>
                        Últimas Sesiones
                    </h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-modern mb-0">
                        <thead>
                            <tr>
                                <th>Usuario</th>
                                <th>IP</th>
                                <th>Última Actividad</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentSessions as $session)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div style="width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(135deg, #6366f1, #4f46e5); color: white; display: flex; align-items: center; justify-content: center; font-size: .8rem; font-weight: 600;">
                                            {{ substr($session->user->name ?? 'U', 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ $session->user->name ?? 'Desconocido' }}</div>
                                            <small class="text-muted">{{ $session->user->email ?? '' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <code style="background: #f1f5f9; padding: .25rem .5rem; border-radius: .25rem; font-size: .8rem;">{{ $session->ip_address }}</code>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <i class="ri ri-time-line me-1"></i>{{ \Carbon\Carbon::parse($session->last_activity)->diffForHumans() }}
                                    </small>
                                </td>
                                <td>
                                    @if($session->is_active)
                                        <span class="badge bg-success-subtle text-success" style="border-radius: .375rem;">
                                            <i class="ri ri-checkbox-circle-line me-1"></i>Activa
                                        </span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary" style="border-radius: .375rem;">
                                            <i class="ri ri-close-circle-line me-1"></i>Inactiva
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="ri ri-inbox-line" style="font-size: 3rem; opacity: 0.3;"></i>
                                        <h5 class="mt-3">No hay sesiones registradas</h5>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Inicializar gráficos aquí (similar al admin dashboard)
        // Los scripts específicos de ApexCharts se mantienen igual
    </script>
    @endpush
</div>
