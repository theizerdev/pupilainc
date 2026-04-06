<div>
    {{-- Header con gradiente Materialize --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-gradient-primary shadow-primary">
                <div class="card-body py-3">
                    <div class="row align-items-center">
                        <div class="col-md-7">
                            <h4 class="text-white mb-1 fw-bold">
                                <i class="ri ri-customer-service-2-line me-2"></i>Panel de Recepción
                            </h4>
                            <p class="text-white-50 mb-0">Gestión diaria de pacientes, citas y consultorios</p>
                        </div>
                        <div class="col-md-5 text-md-end mt-3 mt-md-0">
                            <a href="{{ route('admin.citas.index') }}" class="btn btn-light btn-sm me-2">
                                <i class="ri ri-calendar-add-line me-1"></i> Agendar Cita
                            </a>
                            <a href="{{ route('admin.recepcion.control-consultorios') }}" class="btn btn-outline-light btn-sm">
                                <i class="ri ri-hospital-line me-1"></i> Consultorios
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Stats Cards estilo Materialize --}}
    <div class="row g-4 mb-4">
        <div class="col-sm-6 col-lg-2">
            <div class="card card-border-shadow-warning h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="avatar me-2">
                            <span class="avatar-initial rounded bg-label-warning">
                                <i class="ri ri-time-line ri-20px"></i>
                            </span>
                        </div>
                        <div>
                            <h4 class="mb-0">{{ $stats['programadas'] }}</h4>
                            <small class="text-muted">Programadas</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-2">
            <div class="card card-border-shadow-primary h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="avatar me-2">
                            <span class="avatar-initial rounded bg-label-primary">
                                <i class="ri ri-checkbox-circle-line ri-20px"></i>
                            </span>
                        </div>
                        <div>
                            <h4 class="mb-0">{{ $stats['confirmadas'] }}</h4>
                            <small class="text-muted">Confirmadas</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-2">
            <div class="card card-border-shadow-success h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="avatar me-2">
                            <span class="avatar-initial rounded bg-label-success">
                                <i class="ri ri-check-double-line ri-20px"></i>
                            </span>
                        </div>
                        <div>
                            <h4 class="mb-0">{{ $stats['finalizadas'] }}</h4>
                            <small class="text-muted">Finalizadas</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-2">
            <div class="card card-border-shadow-danger h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="avatar me-2">
                            <span class="avatar-initial rounded bg-label-danger">
                                <i class="ri ri-close-circle-line ri-20px"></i>
                            </span>
                        </div>
                        <div>
                            <h4 class="mb-0">{{ $stats['canceladas'] }}</h4>
                            <small class="text-muted">Canceladas</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-2">
            <div class="card card-border-shadow-secondary h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="avatar me-2">
                            <span class="avatar-initial rounded bg-label-secondary">
                                <i class="ri ri-user-unfollow-line ri-20px"></i>
                            </span>
                        </div>
                        <div>
                            <h4 class="mb-0">{{ $stats['no_asistio'] }}</h4>
                            <small class="text-muted">No Asistió</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Búsqueda de Pacientes --}}
    <div class="row g-4">
        <div class="col-12">
            <div class="card card-border-shadow-primary">
                <div class="card-header border-bottom d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <div class="avatar me-3">
                            <span class="avatar-initial rounded bg-label-primary">
                                <i class="ri ri-search-line ri-20px"></i>
                            </span>
                        </div>
                        <h5 class="card-title mb-0">Buscar Pacientes</h5>
                    </div>
                    @if($selectedPaciente)
                        <button wire:click="clearSelection" class="btn btn-label-secondary btn-sm">
                            <i class="ri ri-arrow-left-line me-1"></i> Volver a resultados
                        </button>
                    @endif
                </div>
                <div class="card-body pb-2">
                    <div class="input-group input-group-lg input-group-merge">
                        <span class="input-group-text"><i class="ri ri-search-line"></i></span>
                        <input type="text"
                               wire:model.live.debounce.300ms="search"
                               class="form-control"
                               placeholder="Buscar por nombre, apellido, documento, teléfono o email...">
                        @if(strlen($search) > 0)
                            <span class="input-group-text" style="cursor: pointer;" wire:click="$set('search', '')">
                                <i class="ri ri-close-line"></i>
                            </span>
                        @endif
                    </div>
                    <div class="form-text mt-2">
                        <i class="ri ri-information-line me-1"></i>Ingrese al menos 3 caracteres para buscar.
                    </div>
                </div>

                @if($selectedPaciente)
                    {{-- Panel Detallado del Paciente --}}
                    <div class="card-body border-top">
                        <div class="row g-4">
                            {{-- Info del Paciente --}}
                            <div class="col-md-4">
                                <div class="text-center mb-4">
                                    <div class="mx-auto mb-3" style="width: 80px; height: 80px;">
                                        @if($selectedPaciente->foto)
                                            <img src="{{ asset('storage/' . $selectedPaciente->foto) }}"
                                                 alt="{{ $selectedPaciente->nombre_completo }}"
                                                 class="w-100 h-100 rounded-circle object-fit-cover border border-3 border-primary">
                                        @else
                                            <span class="avatar-initial rounded-circle bg-label-primary d-flex align-items-center justify-content-center w-100 h-100" style="font-size: 1.5rem;">
                                                {{ substr($selectedPaciente->nombres, 0, 1) }}{{ substr($selectedPaciente->apellidos, 0, 1) }}
                                            </span>
                                        @endif
                                    </div>
                                    <h5 class="fw-bold mb-1">{{ $selectedPaciente->nombre_completo }}</h5>
                                    <span class="text-muted d-block mb-3">
                                        <i class="ri ri-id-card-line me-1"></i>{{ $selectedPaciente->documento_identidad ?? 'Sin documento' }}
                                    </span>
                                    <div class="d-flex justify-content-center gap-2 flex-wrap">
                                        @if($selectedPaciente->edad)
                                            <span class="badge bg-label-primary rounded-pill">
                                                <i class="ri ri-user-heart-line me-1"></i>{{ $selectedPaciente->edad }} años
                                            </span>
                                        @endif
                                        @if($selectedPaciente->genero)
                                            <span class="badge bg-label-info rounded-pill">
                                                {{ $selectedPaciente->genero === 'M' ? '♂ Masculino' : '♀ Femenino' }}
                                            </span>
                                        @endif
                                        @if($this->pacienteTieneDatosCompletos($selectedPaciente))
                                            <span class="badge bg-label-success rounded-pill">
                                                <i class="ri ri-check-line me-1"></i>Completo
                                            </span>
                                        @else
                                            <span class="badge bg-label-danger rounded-pill">
                                                <i class="ri ri-error-warning-line me-1"></i>Incompleto
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                {{-- Contacto --}}
                                <div class="card bg-lighter border shadow-none mb-3">
                                    <div class="card-header py-2">
                                        <h6 class="card-title mb-0 small fw-bold">
                                            <i class="ri ri-contacts-book-line me-1 text-primary"></i>Contacto
                                        </h6>
                                    </div>
                                    <div class="card-body py-2">
                                        <ul class="list-unstyled mb-0">
                                            <li class="d-flex align-items-center py-2 border-bottom">
                                                <div class="avatar avatar-xs me-2 flex-shrink-0">
                                                    <span class="avatar-initial rounded bg-label-success">
                                                        <i class="ri ri-phone-line ri-14px"></i>
                                                    </span>
                                                </div>
                                                <div>
                                                    <small class="text-muted d-block" style="font-size: 0.7rem;">Teléfono</small>
                                                    <span class="fw-medium small">{{ $selectedPaciente->telefono ?? 'No registrado' }}</span>
                                                </div>
                                            </li>
                                            <li class="d-flex align-items-center py-2 border-bottom">
                                                <div class="avatar avatar-xs me-2 flex-shrink-0">
                                                    <span class="avatar-initial rounded bg-label-primary">
                                                        <i class="ri ri-mail-line ri-14px"></i>
                                                    </span>
                                                </div>
                                                <div class="overflow-hidden">
                                                    <small class="text-muted d-block" style="font-size: 0.7rem;">Email</small>
                                                    <span class="fw-medium small text-truncate d-block">{{ $selectedPaciente->email ?? 'No registrado' }}</span>
                                                </div>
                                            </li>
                                            <li class="d-flex align-items-center py-2 {{ $selectedPaciente->tutor ? 'border-bottom' : '' }}">
                                                <div class="avatar avatar-xs me-2 flex-shrink-0">
                                                    <span class="avatar-initial rounded bg-label-warning">
                                                        <i class="ri ri-map-pin-line ri-14px"></i>
                                                    </span>
                                                </div>
                                                <div>
                                                    <small class="text-muted d-block" style="font-size: 0.7rem;">Dirección</small>
                                                    <span class="fw-medium small">{{ Str::limit($selectedPaciente->direccion, 35) ?? 'No registrada' }}</span>
                                                </div>
                                            </li>
                                            @if($selectedPaciente->tutor)
                                                <li class="d-flex align-items-center py-2">
                                                    <div class="avatar avatar-xs me-2 flex-shrink-0">
                                                        <span class="avatar-initial rounded bg-label-danger">
                                                            <i class="ri ri-parent-line ri-14px"></i>
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <small class="text-muted d-block" style="font-size: 0.7rem;">Tutor</small>
                                                        <span class="fw-medium small">{{ $selectedPaciente->tutor->nombres }} {{ $selectedPaciente->tutor->apellidos }}</span>
                                                        <div class="small text-muted">
                                                            <i class="ri ri-phone-line me-1"></i>{{ $selectedPaciente->tutor->telefono ?? 'Sin tel.' }}
                                                        </div>
                                                    </div>
                                                </li>
                                            @endif
                                        </ul>
                                    </div>
                                </div>

                                {{-- Acciones --}}
                                <div class="d-grid gap-2">
                                    <a href="{{ route('admin.pacientes.preconsulta', $selectedPaciente->id) }}" class="btn btn-primary">
                                        <i class="ri ri-file-list-3-line me-1"></i> Iniciar Pre-consulta
                                    </a>
                                    <a href="{{ route('admin.citas.index') }}" class="btn btn-label-success">
                                        <i class="ri ri-calendar-add-line me-1"></i> Agendar Cita
                                    </a>
                                    <a href="{{ route('admin.pacientes.edit', $selectedPaciente->id) }}" target="_blank" class="btn btn-label-primary">
                                        <i class="ri ri-edit-line me-1"></i> Editar Paciente
                                    </a>
                                </div>
                            </div>

                            {{-- Historial de Citas --}}
                            <div class="col-md-8">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="avatar avatar-sm me-2">
                                        <span class="avatar-initial rounded bg-label-primary">
                                            <i class="ri ri-calendar-line ri-16px"></i>
                                        </span>
                                    </div>
                                    <h6 class="fw-bold mb-0">Historial de Citas Recientes</h6>
                                </div>
                                @if(count($selectedPacienteCitas) > 0)
                                    <div class="table-responsive">
                                        <table class="table table-hover table-sm mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Fecha</th>
                                                    <th>Especialidad</th>
                                                    <th>Médico</th>
                                                    <th>Motivo</th>
                                                    <th>Estado</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($selectedPaciente->citas as $citaHistorial)
                                                    @php
                                                        $badgeColors = [
                                                            'programada' => 'warning',
                                                            'confirmada' => 'primary',
                                                            'en_curso' => 'info',
                                                            'finalizada' => 'success',
                                                            'cancelada' => 'danger',
                                                            'no_asistio' => 'secondary',
                                                        ];
                                                        $labels = \App\Models\Cita::ESTADO_LABELS;
                                                        $bColor = $badgeColors[$citaHistorial->estado] ?? 'secondary';
                                                        $bLabel = $labels[$citaHistorial->estado] ?? ucfirst($citaHistorial->estado);
                                                    @endphp
                                                    <tr>
                                                        <td>
                                                            <div class="fw-medium">{{ $citaHistorial->fecha_inicio->format('d/m/Y') }}</div>
                                                            <small class="text-muted">{{ $citaHistorial->fecha_inicio->format('h:i A') }}</small>
                                                        </td>
                                                        <td>{{ $citaHistorial->especialidad->nombre ?? '-' }}</td>
                                                        <td>{{ $citaHistorial->medico->nombre_completo ?? '-' }}</td>
                                                        <td><small>{{ Str::limit($citaHistorial->motivo, 25) ?? '-' }}</small></td>
                                                        <td><span class="badge bg-label-{{ $bColor }}">{{ $bLabel }}</span></td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="text-center text-muted py-5">
                                        <div class="avatar avatar-lg mx-auto mb-3">
                                            <span class="avatar-initial rounded bg-label-secondary">
                                                <i class="ri ri-calendar-close-line ri-24px"></i>
                                            </span>
                                        </div>
                                        <p class="mb-1">Este paciente no tiene citas registradas.</p>
                                        <a href="{{ route('admin.citas.index') }}" class="btn btn-sm btn-label-primary mt-2">
                                            <i class="ri ri-calendar-add-line me-1"></i> Agendar primera cita
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @elseif(strlen($search) > 2)
                    {{-- Resultados de Búsqueda --}}
                    <div class="card-body border-top pt-3">
                        @if(count($pacientes) > 0)
                            <div class="row g-3">
                                @foreach($pacientes as $paciente)
                                    @php $proximaCita = $paciente->citas->first(); @endphp
                                    <div class="col-md-6 col-xl-4">
                                        <div class="card card-border-shadow-primary h-100"
                                             wire:click="selectPaciente({{ $paciente->id }})"
                                             style="cursor: pointer; transition: all 0.15s ease-in-out;"
                                             onmouseover="this.style.transform='translateY(-2px)'"
                                             onmouseout="this.style.transform='translateY(0)'">
                                            <div class="card-body pb-2">
                                                <div class="d-flex align-items-start mb-3">
                                                    <div class="avatar me-3 flex-shrink-0">
                                                        <span class="avatar-initial rounded-circle bg-label-primary">
                                                            {{ substr($paciente->nombres, 0, 1) }}{{ substr($paciente->apellidos, 0, 1) }}
                                                        </span>
                                                    </div>
                                                    <div class="flex-grow-1 overflow-hidden">
                                                        <h6 class="mb-0 fw-bold text-truncate">{{ $paciente->nombre_completo }}</h6>
                                                        <small class="text-muted">
                                                            <i class="ri ri-id-card-line me-1"></i>{{ $paciente->documento_identidad ?? 'Sin documento' }}
                                                        </small>
                                                        <div class="mt-1">
                                                            @if($this->pacienteTieneDatosCompletos($paciente))
                                                                <span class="badge bg-label-success rounded-pill" style="font-size: 0.65rem;">
                                                                    <i class="ri ri-check-line"></i> Completo
                                                                </span>
                                                            @else
                                                                <span class="badge bg-label-danger rounded-pill" style="font-size: 0.65rem;">
                                                                    <i class="ri ri-error-warning-line"></i> Incompleto
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>

                                                <ul class="list-unstyled mb-2 small">
                                                    @if($paciente->telefono)
                                                        <li class="text-muted mb-1">
                                                            <i class="ri ri-phone-line me-1 text-success"></i>{{ $paciente->telefono }}
                                                        </li>
                                                    @endif
                                                    @if($paciente->email)
                                                        <li class="text-muted text-truncate">
                                                            <i class="ri ri-mail-line me-1 text-info"></i>{{ $paciente->email }}
                                                        </li>
                                                    @endif
                                                </ul>

                                                @if($proximaCita)
                                                    <div class="card bg-lighter border-0 shadow-none mb-0">
                                                        <div class="card-body py-2 px-3">
                                                            <div class="d-flex align-items-center small">
                                                                <i class="ri ri-calendar-check-line me-2 text-primary"></i>
                                                                <div>
                                                                    <strong>{{ $proximaCita->fecha_inicio->format('d/m/Y h:i A') }}</strong>
                                                                    <div class="text-muted">{{ $proximaCita->medico->nombre_completo ?? 'Sin médico' }}</div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @else
                                                    <div class="small text-muted fst-italic">
                                                        <i class="ri ri-calendar-close-line me-1"></i>Sin citas próximas
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="card-footer bg-transparent pt-0">
                                                <div class="d-flex gap-2">
                                                    <a href="{{ route('admin.pacientes.preconsulta', $paciente->id) }}"
                                                       class="btn btn-sm btn-primary flex-fill" onclick="event.stopPropagation();">
                                                        <i class="ri ri-file-list-3-line me-1"></i> Pre-consulta
                                                    </a>
                                                @if($this->pacienteTieneDatosCompletos($selectedPaciente))
                                                    <a href="{{ route('admin.pacientes.edit', $paciente->id) }}" target="_blank"
                                                       class="btn btn-sm btn-label-primary" onclick="event.stopPropagation();" title="Editar">
                                                        <i class="ri ri-edit-line"></i>
                                                    </a>
                                                </div>
                                               @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center text-muted py-5">
                                <div class="avatar avatar-lg mx-auto mb-3">
                                    <span class="avatar-initial rounded bg-label-secondary">
                                        <i class="ri ri-user-search-line ri-24px"></i>
                                    </span>
                                </div>
                                <p class="mb-1">No se encontraron pacientes para "<strong>{{ $search }}</strong>"</p>
                                <small>Intente con otro nombre, documento, teléfono o email.</small>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        {{-- Citas de Hoy --}}
        <div class="col-lg-8">
            <div class="card card-border-shadow-info">
                <div class="card-header border-bottom d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <div class="avatar me-3">
                            <span class="avatar-initial rounded bg-label-info">
                                <i class="ri ri-calendar-todo-line ri-20px"></i>
                            </span>
                        </div>
                        <h5 class="card-title mb-0">Citas de Hoy</h5>
                    </div>
                    <span class="badge bg-label-primary rounded-pill px-3">{{ $stats['total'] }} citas</span>
                </div>
                <div class="card-body">
                    <div id="citas-states-bars" style="min-height: 240px;"></div>
                </div>

                <div class="divider">
                    <div class="divider-text">
                        <i class="ri ri-list-check me-1"></i> Listado
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Hora</th>
                                <th>Paciente</th>
                                <th>Médico</th>
                                <th>Estado</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($citasHoy as $cita)
                                <tr>
                                    <td>
                                        <span class="fw-medium">{{ $cita->fecha_inicio->format('h:i A') }}</span>
                                        <div class="small text-muted">{{ $cita->especialidad->nombre ?? '' }}</div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm me-2 flex-shrink-0">
                                                <span class="avatar-initial rounded-circle bg-label-primary">
                                                    {{ substr($cita->paciente->nombres, 0, 1) }}{{ substr($cita->paciente->apellidos, 0, 1) }}
                                                </span>
                                            </div>
                                            <div>
                                                <span class="fw-medium">{{ $cita->paciente->nombre_completo }}</span>
                                                <div class="small text-muted">{{ $cita->paciente->documento_identidad ?? 'N/A' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="fw-medium">{{ $cita->medico->nombre_completo ?? '' }}</span>
                                        <div class="small text-muted">{{ $cita->especialidad->nombre ?? '' }}</div>
                                    </td>
                                    <td>
                                        @php
                                            $badgeColors = [
                                                'programada' => 'warning',
                                                'confirmada' => 'primary',
                                                'en_curso' => 'info',
                                                'finalizada' => 'success',
                                                'cancelada' => 'danger',
                                                'no_asistio' => 'secondary',
                                            ];
                                            $labels = \App\Models\Cita::ESTADO_LABELS;
                                            $color = $badgeColors[$cita->estado] ?? 'secondary';
                                            $label = $labels[$cita->estado] ?? ucfirst($cita->estado);
                                        @endphp
                                        <span class="badge bg-label-{{ $color }}">{{ $label }}</span>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-inline-flex gap-1">
                                            @if($cita->estado === 'programada' || $cita->estado === 'confirmada')
                                                <button class="btn btn-icon btn-sm btn-label-info"
                                                        wire:click="marcarLlegada({{ $cita->id }})" title="Marcar llegada"
                                                        wire:confirm="¿Marcar llegada del paciente?">
                                                    <i class="ri ri-user-location-line ri-16px"></i>
                                                </button>
                                            @endif
                                            @if($cita->estado === 'en_curso')
                                                <button class="btn btn-icon btn-sm btn-label-success"
                                                        wire:click="completarAtencion({{ $cita->id }})" title="Completar"
                                                        wire:confirm="¿Marcar como atención finalizada?">
                                                    <i class="ri ri-check-line ri-16px"></i>
                                                </button>
                                            @endif
                                            @if(!in_array($cita->estado, ['finalizada', 'cancelada', 'no_asistio']))
                                                <button class="btn btn-icon btn-sm btn-label-danger"
                                                        wire:click="cancelarCita({{ $cita->id }})" title="Cancelar"
                                                        wire:confirm="¿Está seguro de cancelar esta cita?">
                                                    <i class="ri ri-close-circle-line ri-16px"></i>
                                                </button>
                                            @endif
                                            @if(!in_array($cita->estado, ['finalizada', 'cancelada', 'no_asistio']))
                                                <button class="btn btn-icon btn-sm btn-label-primary"
                                                        wire:click="enviarRecordatorio({{ $cita->id }})" title="Recordatorio WhatsApp">
                                                    <i class="ri ri-whatsapp-line ri-16px"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <div class="avatar avatar-lg mx-auto mb-3">
                                            <span class="avatar-initial rounded bg-label-secondary">
                                                <i class="ri ri-calendar-close-line ri-24px"></i>
                                            </span>
                                        </div>
                                        <p class="text-muted mb-0">No hay citas registradas para hoy.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Consultorios --}}
        <div class="col-lg-4">
            <div class="card card-border-shadow-success">
                <div class="card-header border-bottom d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-sm me-2">
                            <span class="avatar-initial rounded bg-label-success">
                                <i class="ri ri-hospital-line ri-16px"></i>
                            </span>
                        </div>
                        <h5 class="card-title mb-0">Consultorios</h5>
                    </div>
                    <a href="{{ route('admin.recepcion.control-consultorios') }}" class="btn btn-sm btn-label-primary">
                        <i class="ri ri-settings-3-line me-1"></i> Gestionar
                    </a>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div id="consultorios-ocupacion-donut" style="min-height: 200px;"></div>
                    </div>

                    <div class="divider">
                        <div class="divider-text">
                            <i class="ri ri-door-open-line me-1"></i> Detalle
                        </div>
                    </div>

                    @php
                        $ocupadosIds = $ocupacionConsultorios->keys()->all();
                    @endphp
                    @forelse($consultorios as $c)
                        @php
                            $ocupado = in_array($c->id, $ocupadosIds);
                            $badgeType = $ocupado ? 'danger' : 'success';
                            $estado = $ocupado ? 'Ocupado' : 'Disponible';
                            $medicoNombre = $ocupado ? ($ocupacionConsultorios[$c->id]->medico->nombre_completo ?? '') : '';
                        @endphp
                        <div class="d-flex align-items-center justify-content-between py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-sm me-2">
                                    <span class="avatar-initial rounded bg-label-{{ $badgeType }}">
                                        <i class="ri ri-door-line ri-16px"></i>
                                    </span>
                                </div>
                                <div>
                                    <span class="fw-medium">{{ $c->nombre }}</span>
                                    <div class="small text-muted">
                                        {{ $c->ubicacion ?? 'Sin ubicación' }}
                                        @if($medicoNombre)
                                            <span class="d-block text-primary">
                                                <i class="ri ri-stethoscope-line me-1"></i>{{ $medicoNombre }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <span class="badge bg-label-{{ $badgeType }} rounded-pill">{{ $estado }}</span>
                        </div>
                    @empty
                        <div class="text-center text-muted py-3">
                            <i class="ri ri-hospital-line ri-2x mb-2 d-block opacity-25"></i>
                            <small>No hay consultorios configurados.</small>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

@push('scripts')
<script>
    document.addEventListener('livewire:init', function () {
        var citasBarsOptions = {
            chart: { type: 'bar', height: 240, toolbar: { show: false } },
            series: [{ name: 'Citas', data: [{{ $stats['programadas'] }}, {{ $stats['confirmadas'] }}, {{ $stats['finalizadas'] }}, {{ $stats['canceladas'] }}, {{ $stats['no_asistio'] }}] }],
            colors: ['#ffc107', '#7367f0', '#28c76f', '#ff4c51', '#a8aaae'],
            xaxis: { categories: ['Programadas', 'Confirmadas', 'Finalizadas', 'Canceladas', 'No asistió'] },
            plotOptions: { bar: { columnWidth: '45%', borderRadius: 6, distributed: true } },
            dataLabels: { enabled: true },
            legend: { show: false },
            grid: { borderColor: '#f1f1f1', padding: { top: -10, bottom: -10 } }
        };
        var citasBarsChart = new ApexCharts(document.querySelector('#citas-states-bars'), citasBarsOptions);
        citasBarsChart.render();

        var ocupacionDonutOptions = {
            chart: { type: 'donut', height: 200 },
            labels: ['Ocupados', 'Disponibles'],
            series: [{{ $stats['ocupados'] ?? 0 }}, {{ $stats['disponibles'] ?? 0 }}],
            colors: ['#ff4c51', '#28c76f'],
            legend: { position: 'bottom', fontSize: '13px' },
            dataLabels: { enabled: true },
            stroke: { width: 0 },
            plotOptions: { pie: { donut: { size: '70%', labels: { show: true, total: { show: true, label: 'Total', fontSize: '14px' } } } } }
        };
        var consultoriosDonut = new ApexCharts(document.querySelector('#consultorios-ocupacion-donut'), ocupacionDonutOptions);
        consultoriosDonut.render();
    });
</script>
@endpush
</div>
