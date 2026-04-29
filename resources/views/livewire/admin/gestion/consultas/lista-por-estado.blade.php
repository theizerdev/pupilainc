<div>
    <div>
    <div class="">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0 text-gray-800">{{ $titulo }}</h1>
                <p class="text-muted">Gestión de consultas por estado</p>
            </div>
            <a href="{{ route('admin.gestion.consultas.index') }}" class="btn btn-outline-primary">
                <i class="ri ri-calendar-line me-1"></i> Ver Calendario
            </a>
        </div>

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-xl-4 col-md-6 mb-3">
                <div class="card shadow-sm h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col me-2">
                                <div class="text-xs fw-bold text-primary text-uppercase mb-1">Total</div>
                                <div class="h5 mb-0 fw-bold">{{ $stats['total'] }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="ri ri-file-list-3-line ri-2x text-muted"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-6 mb-3">
                <div class="card shadow-sm h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col me-2">
                                <div class="text-xs fw-bold text-success text-uppercase mb-1">Hoy</div>
                                <div class="h5 mb-0 fw-bold">{{ $stats['total_hoy'] }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="ri ri-calendar-check-line ri-2x text-muted"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-12 mb-3">
                <div class="card shadow-sm h-100 py-2">
                    <div class="card-body">
                        <div class="text-xs fw-bold text-info text-uppercase mb-2">Por Médico</div>
                        @forelse($stats['por_medico'] as $medico => $count)
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <small>{{ $medico }}</small>
                                <span class="badge bg-label-primary rounded-pill">{{ $count }}</span>
                            </div>
                        @empty
                            <small class="text-muted">Sin consultas</small>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="form-floating form-floating-outline">
                            <input type="text" class="form-control" id="search"
                                   wire:model.live.debounce.300ms="search"
                                   placeholder="Buscar...">
                            <label for="search">Buscar paciente, médico, código...</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-floating form-floating-outline">
                            <select class="form-select" wire:model.live="filtroMedico" id="filtroMedico">
                                <option value="">Todos los médicos</option>
                                @foreach($medicos as $medico)
                                    <option value="{{ $medico->id }}">{{ $medico->nombre_completo }}</option>
                                @endforeach
                            </select>
                            <label for="filtroMedico">Médico</label>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-floating form-floating-outline">
                            <input type="date" class="form-control" id="filtroFecha"
                                   wire:model.live="filtroFecha">
                            <label for="filtroFecha">Fecha</label>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-floating form-floating-outline">
                            <select class="form-select" wire:model.live="perPage" id="perPage">
                                <option value="10">10</option>
                                <option value="15">15</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                            </select>
                            <label for="perPage">Por página</label>
                        </div>
                    </div>
                    <div class="col-md-2 d-flex align-items-center">
                        <button class="btn btn-outline-secondary w-100" wire:click="resetFilters">
                            <i class="ri ri-filter-off-line me-1"></i> Limpiar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="card shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="cursor:pointer;" wire:click="sortBy('codigo')">
                                Código
                                @if($sortField === 'codigo')
                                    <i class="ri ri-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}-s-line"></i>
                                @endif
                            </th>
                            <th>Paciente</th>
                            <th>Médico</th>
                            <th>Especialidad</th>
                            <th style="cursor:pointer;" wire:click="sortBy('fecha_consulta')">
                                Fecha
                                @if($sortField === 'fecha_consulta')
                                    <i class="ri ri-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}-s-line"></i>
                                @endif
                            </th>
                            <th>Estado</th>
                            <th>Tiempo</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($consultas as $consulta)
                            <tr>
                                <td>
                                    <span class="fw-medium">#{{ $consulta->codigo }}</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div>
                                            <span class="fw-medium">{{ $consulta->paciente->nombre_completo }}</span>
                                            <br>
                                            <small class="text-muted">{{ $consulta->paciente->documento_identidad ?? '' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $consulta->medico->nombre_completo ?? 'Sin médico' }}</td>
                                <td>{{ $consulta->especialidad->nombre ?? 'Sin especialidad' }}</td>
                                <td>
                                    <span>{{ $consulta->fecha_consulta->format('d/m/Y') }}</span>
                                    <br>
                                    <small class="text-muted">{{ $consulta->fecha_consulta->format('h:i A') }}</small>
                                </td>
                                <td>
                                    <span class="badge" style="background-color: {{ $estadoColores[$consulta->estado] ?? '#78909C' }}">
                                        {{ $estadoLabels[$consulta->estado] ?? ucfirst($consulta->estado) }}
                                    </span>
                                    @if($consulta->estado === \App\Models\Consulta::ESTADO_PAGADA)
                                        <i class="ri ri-check-double-line text-success ms-1" title="Pagada"></i>
                                    @endif
                                    @if($consulta->estado === \App\Models\Consulta::ESTADO_EN_GOTAS && $consulta->gotasAplicadas->count() > 0)
                                        <div class="mt-1 d-flex flex-column gap-1">
                                            <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25" style="font-size: 0.7rem;">
                                                <i class="ri ri-drop-fill me-1"></i>{{ $consulta->gotasAplicadas->count() }} aplicación(es)
                                            </span>
                                            <span class="badge bg-light text-secondary border" style="font-size: 0.65rem;">
                                                OD: {{ $consulta->gotasAplicadas->sum('gotas_od') }} | OI: {{ $consulta->gotasAplicadas->sum('gotas_oi') }}
                                            </span>
                                        </div>
                                    @endif
                                    @php $datosEstado = $consulta->getDatosEstado($consulta->estado); @endphp
                                    @if(!empty($datosEstado))
                                        <div class="mt-1">
                                            <a href="{{ route('admin.consulta.proceso', $consulta->id) }}" 
                                               class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 text-decoration-none" 
                                               style="font-size:0.65rem;">
                                                <i class="ri ri-checkbox-circle-line me-1"></i>Ver formulario completado
                                            </a>
                                        </div>
                                    @elseif($this->tieneFormularioEstado($consulta))
                                        <div class="mt-1">
                                            <a href="{{ route('admin.consulta.proceso', $consulta->id) }}" 
                                               class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 text-decoration-none" 
                                               style="font-size:0.65rem;">
                                                <i class="ri ri-edit-line me-1"></i>{{ $this->getTituloFormularioEstado($consulta) ?? 'Completar formulario' }}
                                            </a>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    @if($consulta->estado_changed_at)
                                        <small class="text-muted d-block" title="{{ $consulta->estado_changed_at->format('d/m/Y H:i') }}">
                                            <i class="ri ri-time-line"></i>
                                            {{ $consulta->estado_changed_at->diffForHumans(null, true, true) }}
                                        </small>
                                    @else
                                        <small class="text-muted d-block">-</small>
                                    @endif

                                    @if($consulta->estado === \App\Models\Consulta::ESTADO_EN_GOTAS && $consulta->gotasAplicadas->count() > 0)
                                        <small class="text-info d-block mt-1" title="Última aplicación de gotas: {{ $consulta->gotasAplicadas->last()->created_at->format('d/m/Y H:i') }}">
                                            <i class="ri ri-timer-line"></i>
                                            {{ $consulta->gotasAplicadas->last()->created_at->diffForHumans(null, true, true) }}
                                        </small>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-icon btn-outline-secondary dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                            <i class="ri ri-more-2-fill"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">

                                            {{-- Acción dinámica: formulario configurado para el estado actual --}}
                                            @php
                                                $tieneForm   = $this->tieneFormularioEstado($consulta);
                                                $tituloForm  = $tieneForm ? $this->getTituloFormularioEstado($consulta) : null;
                                                $estadoColor = \App\Models\Consulta::ESTADO_COLORES[$consulta->estado] ?? '#78909C';
                                            @endphp

                                            @if($tieneForm)
                                            <li>
                                                <a class="dropdown-item d-flex align-items-center"
                                                   href="{{ route('admin.consulta.proceso', $consulta->id) }}">
                                                    <span class="badge me-2" style="background-color:{{ $estadoColor }};width:10px;height:10px;padding:0;border-radius:50%;display:inline-block;"></span>
                                                    {{ $tituloForm ?? 'Formulario del Estado' }}
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            @endif

                                            {{-- Signos vitales (enfermería) --}}
                                            @if($consulta->estado === \App\Models\Consulta::ESTADO_EN_ENFERMERIA && auth()->user()->can('registrar signos vitales'))
                                            <li>
                                                <a class="dropdown-item d-flex align-items-center" href="#"
                                                   data-bs-toggle="modal" data-bs-target="#registrarSignosVitalesModal{{ $consulta->id }}">
                                                    <i class="ri ri-heart-pulse-line me-2 text-danger"></i>
                                                    Signos Vitales
                                                </a>
                                            </li>
                                            @endif

                                            {{-- Proceso de consulta (consultorio) --}}
                                            @if(in_array($consulta->estado, [\App\Models\Consulta::ESTADO_EN_CONSULTORIO, \App\Models\Consulta::ESTADO_EN_CONSULTORIO_OPTOMETRISTA]))
                                            <li>
                                                <a class="dropdown-item d-flex align-items-center"
                                                   href="{{ route('admin.consulta.proceso', $consulta->id) }}">
                                                    <i class="ri ri-stethoscope-line me-2 text-primary"></i>
                                                    Procesar Consulta
                                                </a>
                                            </li>
                                            @endif

                                            {{-- Gotas (oftalmología) --}}
                                            @if($consulta->estado === \App\Models\Consulta::ESTADO_EN_GOTAS)
                                            <li>
                                                <a class="dropdown-item d-flex align-items-center" href="#"
                                                   data-bs-toggle="modal" data-bs-target="#registrarGotasModal{{ $consulta->id }}">
                                                    <i class="ri ri-drop-line me-2 text-info"></i>
                                                    Registrar Gotas
                                                </a>
                                            </li>
                                            @endif

                                        
                                           

                                            {{-- Documentos (finalizada) --}}
                                            @if($consulta->estado === \App\Models\Consulta::ESTADO_FINALIZADA)
                                            <li><hr class="dropdown-divider"></li>
                                            <li><h6 class="dropdown-header">Documentos</h6></li>
                                            <li>
                                                <a class="dropdown-item d-flex align-items-center"
                                                   href="{{ route('admin.consulta.informe', $consulta->id) }}" target="_blank">
                                                    <i class="ri ri-file-text-line me-2 text-primary"></i>
                                                    Informe Médico
                                                </a>
                                            </li>
                                            @if($consulta->reposo)
                                            <li>
                                                <a class="dropdown-item d-flex align-items-center"
                                                   href="{{ route('admin.consulta.reposo', $consulta->id) }}" target="_blank">
                                                    <i class="ri ri-hotel-bed-line me-2 text-success"></i>
                                                    Reposo Médico
                                                </a>
                                            </li>
                                            @endif
                                            <li>
                                                <a class="dropdown-item d-flex align-items-center" href="#"
                                                   data-bs-toggle="modal" data-bs-target="#modalConstancia{{ $consulta->id }}">
                                                    <i class="ri ri-award-line me-2 text-warning"></i>
                                                    Constancia de Asistencia
                                                </a>
                                            </li>
                                            @endif

                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="ri ri-file-search-line ri-3x mb-3 d-block"></i>
                                        <h5>No se encontraron consultas</h5>
                                        <p>Intente ajustar los filtros de búsqueda</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($consultas->hasPages())
                <div class="card-footer d-flex justify-content-between align-items-center">
                    <small class="text-muted">
                        Mostrando {{ $consultas->firstItem() }} a {{ $consultas->lastItem() }} de {{ $consultas->total() }}
                    </small>
                    {{ $consultas->links('livewire.pagination') }}
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Modales de Signos Vitales --}}
@foreach($consultas as $consulta)
    @if($consulta->estado === \App\Models\Consulta::ESTADO_EN_ENFERMERIA && auth()->user()->can('registrar signos vitales'))
        @livewire('admin.gestion.consultas.registrar-signos-vitales', ['consultaId' => $consulta->id], key('signos-vitales-' . $consulta->id))
    @endif
    @if($consulta->estado === \App\Models\Consulta::ESTADO_EN_GOTAS)
        @livewire('admin.gestion.consultas.registrar-gotas', ['consultaId' => $consulta->id], key('gotas-' . $consulta->id))
    @endif
@endforeach

{{-- Modales de Constancia de Asistencia --}}
@foreach($consultas as $consulta)
    @if($consulta->estado === \App\Models\Consulta::ESTADO_FINALIZADA)
    <div class="modal fade" id="modalConstancia{{ $consulta->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="GET" action="{{ route('admin.consulta.constancia', $consulta->id) }}" target="_blank">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="ri ri-award-line me-2 text-warning"></i>Constancia de Asistencia
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted small mb-3">
                            Paciente: <strong>{{ $consulta->paciente->nombre_completo }}</strong>
                        </p>

                        <div class="mb-3">
                            <label class="form-label">Motivo de la consulta (para la constancia)</label>
                            <input type="text" class="form-control" name="motivo"
                                   value="Consulta médica" placeholder="Ej: Consulta médica, control, etc.">
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="chkAcomp{{ $consulta->id }}"
                                   name="acompanante" value="1"
                                   onchange="toggleAcompanante({{ $consulta->id }}, this.checked)">
                            <label class="form-check-label" for="chkAcomp{{ $consulta->id }}">
                                Incluir acompañante
                            </label>
                        </div>

                        <div id="seccionAcomp{{ $consulta->id }}" style="display:none">
                            <div class="card border bg-light p-3">
                                <div class="mb-2">
                                    <label class="form-label small">Nombre completo del acompañante</label>
                                    <input type="text" class="form-control form-control-sm"
                                           name="nombre_acompanante" placeholder="Nombre y apellido">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small">Cédula / Documento</label>
                                    <input type="text" class="form-control form-control-sm"
                                           name="documento_acompanante" placeholder="Ej: 12345678">
                                </div>
                                <div class="mb-0">
                                    <label class="form-label small">Relación con el paciente</label>
                                    <select class="form-select form-select-sm" name="relacion_acompanante">
                                        <option value="">-- Seleccionar --</option>
                                        <option value="familiar">Familiar</option>
                                        <option value="padre/madre">Padre / Madre</option>
                                        <option value="cónyuge">Cónyuge</option>
                                        <option value="hijo(a)">Hijo(a)</option>
                                        <option value="hermano(a)">Hermano(a)</option>
                                        <option value="tutor legal">Tutor legal</option>
                                        <option value="amigo(a)">Amigo(a)</option>
                                        <option value="otro">Otro</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        @if($consulta->paciente->es_menor && $consulta->paciente->tutor)
                        <div class="alert alert-info small mt-3 mb-0">
                            <i class="ri ri-information-line me-1"></i>
                            Paciente menor de edad. Si no agrega acompañante manual, se incluirá automáticamente
                            al tutor: <strong>{{ $consulta->paciente->tutor->nombre_completo }}</strong>
                            ({{ $consulta->paciente->tutor->parentesco }}).
                        </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-warning">
                            <i class="ri ri-printer-line me-1"></i>Generar Constancia
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
@endforeach

<script>
    window.addEventListener('alert', event => {
        if (event.detail.type === 'success') {
            alert(event.detail.message);
        } else if (event.detail.type === 'error') {
            alert(event.detail.message);
        }
    });

    window.addEventListener('cerrar-modal-signos-vitales', event => {
        // Cerrar todos los modales de signos vitales
        document.querySelectorAll('[id^="registrarSignosVitalesModal"]').forEach(modal => {
            const bootstrapModal = bootstrap.Modal.getInstance(modal);
            if (bootstrapModal) {
                bootstrapModal.hide();
            }
        });
    });

    window.addEventListener('cerrar-modal-gotas', event => {
        // Cerrar todos los modales de gotas
        document.querySelectorAll('[id^="registrarGotasModal"]').forEach(modal => {
            const bootstrapModal = bootstrap.Modal.getInstance(modal);
            if (bootstrapModal) {
                bootstrapModal.hide();
            }
        });
    });

    function toggleAcompanante(consultaId, show) {
        const seccion = document.getElementById('seccionAcomp' + consultaId);
        if (seccion) seccion.style.display = show ? 'block' : 'none';
    }
</script>
</div>
