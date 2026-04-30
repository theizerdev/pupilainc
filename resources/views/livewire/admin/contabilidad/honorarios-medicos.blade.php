<div>
    <div class="py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0 text-gray-800">
                    <i class="fas fa-handshake me-2"></i>Honorarios Médicos
                </h1>
                <p class="text-muted">Gestión y reportes de honorarios por consultas médicas</p>
            </div>
        </div>

        <!-- Filtros -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-filter me-2"></i>Filtros de Búsqueda
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="fecha_desde" class="fw-bold">Fecha Desde</label>
                            <input type="date" 
                                   class="form-control" 
                                   id="fecha_desde" 
                                   wire:model.live="fecha_desde">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="fecha_hasta" class="fw-bold">Fecha Hasta</label>
                            <input type="date" 
                                   class="form-control" 
                                   id="fecha_hasta" 
                                   wire:model.live="fecha_hasta">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="medico_id" class="fw-bold">Médico</label>
                            <select class="form-control" id="medico_id" wire:model.live="medico_id">
                                <option value="">Todos los médicos</option>
                                @foreach($medicos as $medico)
                                    <option value="{{ $medico->id }}">{{ $medico->nombre_completo }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="especialidad_id" class="fw-bold">Especialidad</label>
                            <select class="form-control" id="especialidad_id" wire:model.live="especialidad_id">
                                <option value="">Todas las especialidades</option>
                                @foreach($especialidades as $especialidad)
                                    <option value="{{ $especialidad->id }}">{{ $especialidad->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="estado" class="fw-bold">Estado</label>
                            <select class="form-control" id="estado" wire:model.live="estado">
                                <option value="">Todos los estados</option>
                                <option value="calculado">Calculado</option>
                                <option value="incluido_nomina">Incluido en Nómina</option>
                                <option value="pagado">Pagado</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="button" class="btn btn-outline-secondary" wire:click="resetFilters">
                            <i class="fas fa-undo me-1"></i>Limpiar Filtros
                        </button>
                    </div>
                    <div class="col-md-6 d-flex align-items-end justify-content-end">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="mostrar_resumen" wire:model.live="mostrar_resumen">
                            <label class="form-check-label fw-bold" for="mostrar_resumen">
                                <i class="fas fa-chart-pie me-1"></i>Mostrar Resumen
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if($mostrar_resumen)
        <!-- Resumen General -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Total Consultas
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $resumen['total_consultas'] }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-stethoscope fa-2x text-gray-300"></i>
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
                                    Total Facturado
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    ${{ number_format($resumen['total_facturado_usd'], 2) }}
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
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Honorarios Médicos
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    ${{ number_format($resumen['total_honorarios_medico_usd'], 2) }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-user-md fa-2x text-gray-300"></i>
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
                                    Ingresos Clínica
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    ${{ number_format($resumen['total_ingresos_clinica_usd'], 2) }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-hospital fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Resumen por Médico -->
        @if($resumenPorMedico->count() > 0)
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-chart-bar me-2"></i>Resumen por Médico
                </h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Médico</th>
                                <th class="text-center">Consultas</th>
                                <th class="text-end">Total Facturado</th>
                                <th class="text-end">Honorarios</th>
                                <th class="text-end">Ingresos Clínica</th>
                                <th class="text-center">% Honorarios</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($resumenPorMedico as $resumenMedico)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm me-3">
                                            <div class="avatar-initial bg-primary rounded-circle">
                                                {{ substr($resumenMedico->consulta->medico->nombres, 0, 1) }}{{ substr($resumenMedico->consulta->medico->apellidos, 0, 1) }}
                                            </div>
                                        </div>
                                        <div>
                                            <strong>{{ $resumenMedico->consulta->medico->nombre_completo }}</strong>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-info">{{ $resumenMedico->total_consultas }}</span>
                                </td>
                                <td class="text-end">
                                    <strong>${{ number_format($resumenMedico->total_facturado, 2) }}</strong>
                                </td>
                                <td class="text-end">
                                    <span class="text-success fw-bold">
                                        ${{ number_format($resumenMedico->total_honorarios, 2) }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <span class="text-warning fw-bold">
                                        ${{ number_format($resumenMedico->total_ingresos_clinica, 2) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @php
                                        $porcentaje = $resumenMedico->total_facturado > 0 
                                            ? ($resumenMedico->total_honorarios / $resumenMedico->total_facturado) * 100 
                                            : 0;
                                    @endphp
                                    <span class="badge bg-{{ $porcentaje >= 50 ? 'success' : 'warning' }}">
                                        {{ number_format($porcentaje, 1) }}%
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
        @endif

        <!-- Detalle de Honorarios -->
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-list me-2"></i>Detalle de Honorarios
                </h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Médico</th>
                                <th>Paciente</th>
                                <th class="text-end">Facturado</th>
                                <th class="text-end">Honorario Médico</th>
                                <th class="text-end">Ingreso Clínica</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center">Servicios</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($honorarios as $honorario)
                            <tr>
                                <td>
                                    <small class="text-muted">
                                        {{ $honorario->consulta->fecha_consulta->format('d/m/Y') }}
                                    </small>
                                </td>
                                <td>
                                    <div>
                                        <strong>{{ $honorario->consulta->medico->nombre_completo }}</strong>
                                        @if($honorario->consulta->especialidad)
                                            <br><small class="text-muted">{{ $honorario->consulta->especialidad->nombre }}</small>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <strong>{{ $honorario->consulta->paciente->nombre_completo }}</strong>
                                </td>
                                <td class="text-end">
                                    <strong>${{ number_format($honorario->total_facturado_usd, 2) }}</strong>
                                </td>
                                <td class="text-end">
                                    <span class="text-success fw-bold">
                                        ${{ number_format($honorario->total_honorarios_medico_usd, 2) }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <span class="text-info fw-bold">
                                        ${{ number_format($honorario->total_ingresos_clinica_usd, 2) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @php
                                        $badgeClass = match($honorario->estado) {
                                            'calculado' => 'bg-warning',
                                            'incluido_nomina' => 'bg-info',
                                            'pagado' => 'bg-success',
                                            default => 'bg-secondary'
                                        };
                                        $estadoLabel = match($honorario->estado) {
                                            'calculado' => 'Calculado',
                                            'incluido_nomina' => 'En Nómina',
                                            'pagado' => 'Pagado',
                                            default => 'Sin Estado'
                                        };
                                    @endphp
                                    <span class="badge {{ $badgeClass }}">{{ $estadoLabel }}</span>
                                </td>
                                <td class="text-center">
                                    @if($honorario->servicios_detalle)
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-info" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#serviciosModal{{ $honorario->id }}">
                                            <i class="fas fa-eye me-1"></i>Ver ({{ count($honorario->servicios_detalle) }})
                                        </button>

                                        <!-- Modal de Servicios -->
                                        <div class="modal fade" id="serviciosModal{{ $honorario->id }}" tabindex="-1">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">
                                                            <i class="fas fa-list me-2"></i>Servicios - {{ $honorario->consulta->paciente->nombre_completo }}
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="table-responsive">
                                                            <table class="table table-sm">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Servicio</th>
                                                                        <th class="text-center">Cant.</th>
                                                                        <th class="text-end">Subtotal</th>
                                                                        <th class="text-center">% Médico</th>
                                                                        <th class="text-end">Honorario</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @foreach($honorario->servicios_detalle as $servicio)
                                                                    <tr>
                                                                        <td>{{ $servicio['nombre_servicio'] ?? 'Servicio' }}</td>
                                                                        <td class="text-center">{{ $servicio['cantidad'] ?? 1 }}</td>
                                                                        <td class="text-end">${{ number_format($servicio['subtotal_usd'] ?? 0, 2) }}</td>
                                                                        <td class="text-center">
                                                                            <span class="badge bg-success">{{ $servicio['porcentaje_medico'] ?? 0 }}%</span>
                                                                        </td>
                                                                        <td class="text-end">
                                                                            <strong>${{ number_format($servicio['honorario_medico_usd'] ?? 0, 2) }}</strong>
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
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-search fa-2x mb-3"></i>
                                        <p>No se encontraron honorarios en el período seleccionado</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $honorarios->links('livewire.pagination') }}
                </div>
            </div>
        </div>
    </div>
</div>