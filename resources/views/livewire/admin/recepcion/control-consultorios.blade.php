<div>
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-0">Control de Consultorios</h4>
                    <p class="text-muted mb-0">Asignación de consultorios para el día: {{ \Carbon\Carbon::parse($fecha)->translatedFormat('l d \d\e F, Y') }}</p>
                </div>
                <div class="d-flex align-items-center">
                    <span class="badge bg-info me-2">
                        <i class="ri-calendar-line me-1"></i> Hoy
                    </span>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-sm-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Médicos con citas</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['medicosConCitas'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-md fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-sm-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Sin consultorio</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['medicosSinAsignacion'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-sm-4">
            <div class="card border-left-secondary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">Total Consultorios</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['totalConsultorios'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clinic-medical fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Ocupados (Hoy)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['ocupados'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-procedures fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Disponibles (Hoy)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['disponibles'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-door-open fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Panel Izquierdo: Lista de Médicos con Citas -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="card-title mb-0">Médicos con Citas Hoy</h5>
                        </div>
                        <div class="col-auto">
                            <input type="text" wire:model.live.debounce.300ms="search" class="form-control form-control-sm" placeholder="Buscar médico...">
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Médico</th>
                                    <th>Especialidad</th>
                                    <th>Citas Hoy</th>
                                    <th>Consultorio Asignado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($medicos as $medico)
                                    @php
                                        // Buscar asignación actual
                                        $asignacion = $medico->asignacionesConsultorios->first();
                                        $consultorioId = $asignacion ? $asignacion->consultorio_id : '';
                                        
                                        // Contar citas (opcional, pero útil)
                                        $citasCount = $medico->citas()
                                            ->whereDate('fecha_inicio', $fecha)
                                            ->whereNotIn('estado', ['cancelada', 'no_asistio'])
                                            ->count();
                                    @endphp
                                    <tr wire:key="medico-{{ $medico->id }}">
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm me-2">
                                                    <span class="avatar-title rounded-circle bg-primary-subtle text-primary">
                                                        {{ substr($medico->nombres, 0, 1) }}{{ substr($medico->apellidos, 0, 1) }}
                                                    </span>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0">{{ $medico->nombres }} {{ $medico->apellidos }}</h6>
                                                    <small class="text-muted">{{ $medico->telefono ?? 'Sin teléfono' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @foreach($medico->especialidades as $especialidad)
                                                <span class="badge bg-light text-dark border">{{ $especialidad->nombre }}</span>
                                            @endforeach
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary rounded-pill">{{ $citasCount }} citas</span>
                                        </td>
                                        <td>
                                            <select class="form-select form-select-sm" 
                                                    wire:change="asignarConsultorio({{ $medico->id }}, $event.target.value)">
                                                <option value="">-- Sin Asignar --</option>
                                                @foreach($consultorios as $consultorio)
                                                    @php
                                                        // Verificar si está ocupado por otro médico
                                                        $ocupadoPorOtro = isset($ocupacion[$consultorio->id]) && $ocupacion[$consultorio->id]->medico_id != $medico->id;
                                                    @endphp
                                                    <option value="{{ $consultorio->id }}" 
                                                            {{ $consultorioId == $consultorio->id ? 'selected' : '' }}
                                                            {{ $ocupadoPorOtro ? 'disabled' : '' }}>
                                                        {{ $consultorio->nombre }} {{ $ocupadoPorOtro ? '(Ocupado: ' . $ocupacion[$consultorio->id]->medico->nombres . ')' : '' }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="ri-calendar-event-line fs-1 d-block mb-2"></i>
                                                No hay médicos con citas programadas para hoy.
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

        <!-- Panel Derecho: Estado de Consultorios -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Estado de Consultorios</h5>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        @foreach($consultorios as $consultorio)
                            @php
                                $ocupado = isset($ocupacion[$consultorio->id]);
                                $asignacion = $ocupado ? $ocupacion[$consultorio->id] : null;
                            @endphp
                            <div class="col-12">
                                <div class="p-3 border rounded {{ $ocupado ? 'bg-success-subtle border-success' : 'bg-light' }}">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="mb-0 fw-bold {{ $ocupado ? 'text-success' : '' }}">
                                            <i class="ri-hospital-line me-1"></i> {{ $consultorio->nombre }}
                                        </h6>
                                        <span class="badge {{ $ocupado ? 'bg-success' : 'bg-secondary' }}">
                                            {{ $ocupado ? 'Ocupado' : 'Disponible' }}
                                        </span>
                                    </div>
                                    @if($ocupado)
                                        <div class="small text-muted">Asignado a:</div>
                                        <div class="fw-bold">{{ $asignacion->medico->nombres }} {{ $asignacion->medico->apellidos }}</div>
                                    @else
                                        <div class="small text-muted">Ubicación: {{ $consultorio->ubicacion ?? 'N/A' }}</div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                        
                        @if($consultorios->isEmpty())
                            <div class="col-12 text-center text-muted">
                                No hay consultorios registrados.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
