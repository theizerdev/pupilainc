<div>
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">📅 Recordatorios de Citas</h3>
                    <div class="card-tools">
                        <button wire:click="procesarPendientes" class="btn btn-sm btn-success">
                            <i class="fas fa-play"></i> Procesar Pendientes
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filtros -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <input type="text" class="form-control" placeholder="Buscar paciente o médico..." wire:model="search">
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
                            <select class="form-control" wire:model="tipo">
                                <option value="">Todos los tipos</option>
                                @foreach($tipos as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-control" wire:model="canal">
                                <option value="">Todos los canales</option>
                                @foreach($canales as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="date" class="form-control" wire:model="fechaDesde" placeholder="Desde">
                        </div>
                        <div class="col-md-2">
                            <input type="date" class="form-control" wire:model="fechaHasta" placeholder="Hasta">
                        </div>
                    </div>

                    <!-- Tabla de Recordatorios -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Cita</th>
                                    <th>Paciente</th>
                                    <th>Médico</th>
                                    <th>Especialidad</th>
                                    <th>Tipo</th>
                                    <th>Canal</th>
                                    <th>Fecha Programada</th>
                                    <th>Estado</th>
                                    <th>Intentos</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recordatorios as $recordatorio)
                                    <tr>
                                        <td>
                                            <small class="text-muted">#{{ $recordatorio->cita->id }}</small><br>
                                            {{ \Carbon\Carbon::parse($recordatorio->cita->fecha_inicio)->format('d/m/Y H:i') }}
                                        </td>
                                        <td>
                                            <strong>{{ $recordatorio->cita->paciente->nombre_completo }}</strong><br>
                                            <small class="text-muted">{{ $recordatorio->cita->paciente->telefono }}</small>
                                        </td>
                                        <td>
                                            {{ $recordatorio->cita->medico->nombre_completo }}
                                        </td>
                                        <td>
                                            {{ $recordatorio->cita->especialidad->nombre ?? 'Sin especialidad' }}
                                        </td>
                                        <td>
                                            <span class="badge badge-info">
                                                {{ $tipos[$recordatorio->tipo] ?? $recordatorio->tipo }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-secondary">
                                                {{ $canales[$recordatorio->canal] ?? $recordatorio->canal }}
                                            </span>
                                        </td>
                                        <td>
                                            {{ \Carbon\Carbon::parse($recordatorio->fecha_envio_programado)->format('d/m/Y H:i') }}
                                        </td>
                                        <td>
                                            @switch($recordatorio->estado)
                                                @case('pendiente')
                                                    <span class="badge badge-warning">
                                                        <i class="fas fa-clock"></i> Pendiente
                                                    </span>
                                                    @break
                                                @case('enviado')
                                                    <span class="badge badge-success">
                                                        <i class="fas fa-check"></i> Enviado
                                                    </span>
                                                    @break
                                                @case('fallido')
                                                    <span class="badge badge-danger">
                                                        <i class="fas fa-times"></i> Fallido
                                                    </span>
                                                    @break
                                                @default
                                                    <span class="badge badge-secondary">
                                                        {{ $recordatorio->estado }}
                                                    </span>
                                            @endswitch
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-info">{{ $recordatorio->intentos }}</span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                @if($recordatorio->estado === 'fallido')
                                                    <button wire:click="reenviarRecordatorio({{ $recordatorio->id }})" 
                                                            class="btn btn-outline-warning" 
                                                            title="Reenviar">
                                                        <i class="fas fa-redo"></i>
                                                    </button>
                                                @endif
                                                @if($recordatorio->estado === 'pendiente')
                                                    <button wire:click="cancelarRecordatorio({{ $recordatorio->id }})" 
                                                            class="btn btn-outline-danger" 
                                                            title="Cancelar">
                                                        <i class="fas fa-ban"></i>
                                                    </button>
                                                @endif
                                            </div>
                                            @if($recordatorio->error_mensaje)
                                                <br>
                                                <small class="text-danger" title="{{ $recordatorio->error_mensaje }}">
                                                    <i class="fas fa-exclamation-triangle"></i> {{ Str::limit($recordatorio->error_mensaje, 20) }}
                                                </small>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center text-muted">
                                            <i class="fas fa-calendar-times fa-2x mb-2"></i><br>
                                            No se encontraron recordatorios
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    <div class="d-flex justify-content-center">
                        {{ $recordatorios->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>