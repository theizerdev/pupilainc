<div>
<div class="py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0 text-gray-800">Detalles de Especialidad</h1>
                <p class="text-muted">Información completa de la especialidad médica</p>
            </div>
            <div>
                <a href="{{ route('admin.especialidades.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
                @can('admin.especialidades.edit')
                    <a href="{{ route('admin.especialidades.edit', $especialidad) }}" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                @endcan
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Total Médicos
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $estadisticas['total_medicos'] }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-user-md fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Citas Este Mes
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $estadisticas['citas_mes'] }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-calendar-check fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Ingresos del Mes
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ format_money($estadisticas['ingresos_mes'], 2) }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    Citas Hoy
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $estadisticas['citas_hoy'] }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-calendar-day fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Info -->
        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Información General</h6>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Código:</strong>
                                <span class="badge" style="background-color: {{ $especialidad->color }}; color: white;">
                                    <i class="fas {{ $especialidad->icono }}"></i> {{ $especialidad->codigo }}
                                </span>
                            </div>
                            <div class="col-md-6">
                                <strong>Estado:</strong>
                                <span class="badge badge-{{ $especialidad->status ? 'success' : 'secondary' }}">
                                    {{ $especialidad->status ? 'Activo' : 'Inactivo' }}
                                </span>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <strong>Nombre:</strong>
                                <p class="text-muted">{{ $especialidad->nombre }}</p>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <strong>Descripción:</strong>
                                <p class="text-muted">{{ $especialidad->descripcion ?: 'Sin descripción' }}</p>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <strong>Costo de Consulta:</strong>
                                <p class="text-muted">{{ format_money($especialidad->costo_consulta) }}</p>
                            </div>
                            <div class="col-md-4">
                                <strong>Duración de Consulta:</strong>
                                <p class="text-muted">{{ $especialidad->duracion_consulta }} minutos</p>
                            </div>
                            <div class="col-md-4">
                                <strong>Requiere Cita Previa:</strong>
                                <p class="text-muted">{{ $especialidad->requiere_cita_previa ? 'Sí' : 'No' }}</p>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Empresa:</strong>
                                <p class="text-muted">{{ $especialidad->empresa->razon_social ?? '-' }}</p>
                            </div>
                            <div class="col-md-6">
                                <strong>Sucursal:</strong>
                                <p class="text-muted">{{ $especialidad->sucursal->nombre ?? '-' }}</p>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <strong>Fecha de Creación:</strong>
                                <p class="text-muted">{{ $especialidad->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                            <div class="col-md-6">
                                <strong>Última Actualización:</strong>
                                <p class="text-muted">{{ $especialidad->updated_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Medical Staff -->
                <div class="card shadow mt-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Médicos Asignados</h6>
                    </div>
                    <div class="card-body">
                        @if($especialidad->medicos->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Nombre</th>
                                            <th>Email</th>
                                            <th>Teléfono</th>
                                            <th>Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($especialidad->medicos as $medico)
                                            <tr>
                                                <td>{{ $medico->nombres.' '.$medico->apellidos }}</td>
                                                <td>{{ $medico->user->email }}</td>
                                                <td>{{ $medico->telefono ?? '-' }}</td>
                                                <td>
                                                    <span class="badge badge-{{ $medico->status ? 'success' : 'secondary' }}">
                                                        {{ $medico->status ? 'Activo' : 'Inactivo' }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center text-muted">
                                <i class="fas fa-user-md fa-3x mb-3"></i>
                                <p>No hay médicos asignados a esta especialidad</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Quick Actions -->
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Acciones Rápidas</h6>
                    </div>
                    <div class="card-body">
                        @can('admin.especialidades.edit')
                            <a href="{{ route('admin.especialidades.edit', $especialidad) }}" class="btn btn-warning btn-block">
                                <i class="fas fa-edit"></i> Editar Especialidad
                            </a>
                        @endcan

                        <button class="btn btn-info btn-block mt-2">
                            <i class="fas fa-calendar-plus"></i> Nueva Cita
                        </button>

                        <button class="btn btn-secondary btn-block mt-2">
                            <i class="fas fa-chart-bar"></i> Ver Reportes
                        </button>
                    </div>
                </div>

                <!-- Color Preview -->
                <div class="card shadow mt-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Identificación Visual</h6>
                    </div>
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <span class="badge" style="background-color: {{ $especialidad->color }}; color: white; font-size: 1.5em;">
                                <i class="fas {{ $especialidad->icono }}"></i> {{ $especialidad->codigo }}
                            </span>
                        </div>
                        <p class="text-muted">Este es el color e icono que se mostrará en calendarios y citas</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>