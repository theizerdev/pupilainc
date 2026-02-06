<div>
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">🔄 Re-agendamiento de Citas</h3>
                    <div class="card-tools">
                        <button wire:click="procesarReagendamientosMasivos" class="btn btn-sm btn-primary">
                            <i class="fas fa-magic"></i> Procesar Masivamente
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filtros -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <input type="text" class="form-control" placeholder="Buscar paciente..." wire:model="search">
                        </div>
                        <div class="col-md-2">
                            <select class="form-control" wire:model="estado">
                                <option value="">Todos los estados</option>
                                @foreach($estados as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="date" class="form-control" wire:model="fechaDesde">
                        </div>
                        <div class="col-md-2">
                            <input type="date" class="form-control" wire:model="fechaHasta">
                        </div>
                        <div class="col-md-3">
                            <button wire:click="$refresh" class="btn btn-outline-primary">
                                <i class="fas fa-search"></i> Filtrar
                            </button>
                            <button wire:click="$set('search', '')" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i> Limpiar
                            </button>
                        </div>
                    </div>

                    <!-- Tabla de Citas -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Cita</th>
                                    <th>Paciente</th>
                                    <th>Médico</th>
                                    <th>Fecha/Hora</th>
                                    <th>Estado</th>
                                    <th>Motivo</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($citas as $cita)
                                    <tr>
                                        <td>
                                            <small class="text-muted">#{{ $cita->id }}</small>
                                        </td>
                                        <td>
                                            <strong>{{ $cita->paciente->nombre_completo }}</strong><br>
                                            <small class="text-muted">{{ $cita->paciente->telefono }}</small>
                                        </td>
                                        <td>
                                            {{ $cita->medico->nombre_completo }}<br>
                                            <small class="text-muted">{{ $cita->especialidad->nombre ?? 'Sin especialidad' }}</small>
                                        </td>
                                        <td>
                                            {{ \Carbon\Carbon::parse($cita->fecha_inicio)->format('d/m/Y') }}<br>
                                            <small class="text-muted">{{ \Carbon\Carbon::parse($cita->fecha_inicio)->format('H:i') }}</small>
                                        </td>
                                        <td>
                                            @switch($cita->estado)
                                                @case('confirmada')
                                                    <span class="badge badge-success">
                                                        <i class="fas fa-check"></i> Confirmada
                                                    </span>
                                                    @break
                                                @case('pendiente')
                                                    <span class="badge badge-warning">
                                                        <i class="fas fa-clock"></i> Pendiente
                                                    </span>
                                                    @break
                                                @case('cancelada')
                                                    <span class="badge badge-danger">
                                                        <i class="fas fa-times"></i> Cancelada
                                                    </span>
                                                    @break
                                                @case('no_asistio')
                                                    <span class="badge badge-secondary">
                                                        <i class="fas fa-user-times"></i> No Asistió
                                                    </span>
                                                    @break
                                                @default
                                                    <span class="badge badge-light">
                                                        {{ ucfirst($cita->estado) }}
                                                    </span>
                                            @endswitch
                                        </td>
                                        <td>
                                            {{ Str::limit($cita->motivo, 50) }}
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                @if(in_array($cita->estado, ['cancelada', 'no_asistio']))
                                                    <button wire:click="buscarHorariosDisponibles({{ $cita->id }})" 
                                                            class="btn btn-outline-primary" 
                                                            title="Buscar horarios disponibles">
                                                        <i class="fas fa-search"></i>
                                                    </button>
                                                    <button wire:click="reagendarAutomaticamente({{ $cita->id }})" 
                                                            class="btn btn-outline-success" 
                                                            title="Re-agendar automáticamente">
                                                        <i class="fas fa-magic"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">
                                            <i class="fas fa-calendar-times fa-2x mb-2"></i><br>
                                            No se encontraron citas para re-agendar
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    <div class="d-flex justify-content-center">
                        {{ $citas->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Re-agendamiento -->
    @if($mostrarModalReagendamiento)
        <div class="modal show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">🔄 Re-agendar Cita</h5>
                        <button type="button" class="close" wire:click="cerrarModal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        @if($citaSeleccionada)
                            <div class="mb-3">
                                <h6>Información de la cita original:</h6>
                                <p>
                                    <strong>Paciente:</strong> {{ $citaSeleccionada->paciente->nombre_completo }}<br>
                                    <strong>Médico:</strong> {{ $citaSeleccionada->medico->nombre_completo }}<br>
                                    <strong>Fecha original:</strong> {{ \Carbon\Carbon::parse($citaSeleccionada->fecha_inicio)->format('d/m/Y H:i') }}
                                </p>
                            </div>

                            @if(!empty($horariosDisponibles))
                                <h6>Horarios disponibles:</h6>
                                <div class="row">
                                    @foreach($horariosDisponibles as $horario)
                                        <div class="col-md-4 mb-2">
                                            <button wire:click="reagendarManualmente('{{ $horario['fecha_hora'] }}')" 
                                                    class="btn btn-outline-primary btn-block">
                                                <i class="fas fa-clock"></i> {{ $horario['fecha_formateada'] }}
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle"></i> No se encontraron horarios disponibles.
                                </div>
                            @endif
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="cerrarModal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>