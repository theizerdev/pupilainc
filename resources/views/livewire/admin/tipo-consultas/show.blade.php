<div>
<div class="py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0 text-gray-800">Tipo de Atención: {{ $tipoConsulta->nombre }}</h1>
                <p class="text-muted">Detalle completo del tipo de atención</p>
            </div>
            <div>
                <a href="{{ route('admin.tipo-consultas.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
        </div>

        <!-- Info Cards -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card shadow border-left-primary">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Total Citas
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_citas'] }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-calendar-check fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow border-left-success">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Citas Activas
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['citas_activas'] }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow border-left-info">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Citas Finalizadas
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['citas_finalizadas'] }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-tasks fa-2x text-gray-300"></i>
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
                        <h6 class="m-0 font-weight-bold text-primary">Información del Tipo de Atención</h6>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="text-muted small">Nombre</label>
                                <h5>{{ $tipoConsulta->nombre }}</h5>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small">Código</label>
                                <div>
                                    <span class="badge" style="background-color: {{ $tipoConsulta->color }}; color: white;">
                                        <i class="fas {{ $tipoConsulta->icono }}"></i> {{ $tipoConsulta->codigo }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="text-muted small">Descripción</label>
                            <p>{{ $tipoConsulta->descripcion ?: 'Sin descripción' }}</p>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="text-muted small">Icono</label>
                                <div class="text-center p-3 border rounded">
                                    <i class="fas {{ $tipoConsulta->icono }} fa-3x" style="color: {{ $tipoConsulta->color }};"></i>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small">Color</label>
                                <div class="d-flex align-items-center">
                                    <div class="rounded mr-2" style="width: 40px; height: 40px; background-color: {{ $tipoConsulta->color }};"></div>
                                    <span class="font-weight-bold">{{ $tipoConsulta->color }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="text-muted small">Estado</label>
                                <div>
                                    <span class="badge badge-{{ $tipoConsulta->status ? 'success' : 'secondary' }}">
                                        {{ $tipoConsulta->status ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small">Requiere Cita Previa</label>
                                <div>
                                    <i class="fas {{ $tipoConsulta->requiere_cita_previa ? 'fa-check text-success' : 'fa-times text-danger' }}"></i>
                                    {{ $tipoConsulta->requiere_cita_previa ? 'Sí' : 'No' }}
                                </div>
                            </div>
                        </div>

                        @if(auth()->user()->hasRole('Super Administrador'))
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="text-muted small">Empresa</label>
                                <p class="mb-0">{{ $tipoConsulta->empresa->razon_social ?? '-' }}</p>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small">Sucursal</label>
                                <p class="mb-0">{{ $tipoConsulta->sucursal->nombre ?? '-' }}</p>
                            </div>
                        </div>
                        @endif

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="text-muted small">Fecha de Creación</label>
                                <p class="mb-0">{{ $tipoConsulta->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small">Última Actualización</label>
                                <p class="mb-0">{{ $tipoConsulta->updated_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>

                        <div class="mt-4">
                            @can('edit tipo-consultas')
                            <a href="{{ route('admin.tipo-consultas.edit', $tipoConsulta) }}" class="btn btn-primary">
                                <i class="fas fa-edit"></i> Editar
                            </a>
                            @endcan
                            <a href="{{ route('admin.tipo-consultas.index') }}" class="btn btn-secondary">
                                <i class="fas fa-list"></i> Volver al Listado
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Preview Card -->
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Vista Previa</h6>
                    </div>
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <span class="badge" style="background-color: {{ $tipoConsulta->color }}; color: white; font-size: 1.5em; padding: 0.7em 1.2em;">
                                <i class="fas {{ $tipoConsulta->icono }}"></i> {{ $tipoConsulta->codigo }}
                            </span>
                        </div>
                        <h5 class="mb-2">{{ $tipoConsulta->nombre }}</h5>
                        <p class="text-muted small">{{ Str::limit($tipoConsulta->descripcion, 80) ?: 'Sin descripción' }}</p>
                        
                        <hr>
                        
                        <div class="row text-left mb-2">
                            <div class="col-6">
                                <strong>Estado:</strong>
                            </div>
                            <div class="col-6 text-right">
                                <span class="badge badge-{{ $tipoConsulta->status ? 'success' : 'secondary' }}">
                                    {{ $tipoConsulta->status ? 'Activo' : 'Inactivo' }}
                                </span>
                            </div>
                        </div>
                        
                        <div class="row text-left">
                            <div class="col-6">
                                <strong>Cita Previa:</strong>
                            </div>
                            <div class="col-6 text-right">
                                <span class="badge badge-{{ $tipoConsulta->requiere_cita_previa ? 'info' : 'secondary' }}">
                                    {{ $tipoConsulta->requiere_cita_previa ? 'Requerida' : 'No requerida' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Citas Recientes -->
                @if($tipoConsulta->citas->count() > 0)
                <div class="card shadow mt-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Citas Recientes</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            @foreach($tipoConsulta->citas->take(5) as $cita)
                            <div class="list-group-item px-3 py-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <small class="font-weight-bold">{{ $cita->paciente->nombre ?? 'Paciente' }}</small>
                                        <br>
                                        <small class="text-muted">{{ $cita->fecha_inicio->format('d/m/Y H:i') }}</small>
                                    </div>
                                    <span class="badge badge-{{ $cita->estado === 'finalizada' ? 'success' : ($cita->estado === 'cancelada' ? 'danger' : 'info') }}">
                                        {{ $cita->estado }}
                                    </span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @if($tipoConsulta->citas->count() > 5)
                    <div class="card-footer text-center">
                        <small class="text-muted">+ {{ $tipoConsulta->citas->count() - 5 }} citas más</small>
                    </div>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>
</div>