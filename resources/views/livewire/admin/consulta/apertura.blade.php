<div>
    <div class="container-fluid py-4">
        {{-- Encabezado --}}
        <div class="row mb-4">
            <div class="col-12">
                <div class="card bg-gradient-primary shadow-primary">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h3 class="text-white mb-0">📋 Apertura de Consultas</h3>
                                <p class="text-white text-sm mb-0">Gestión de pacientes y pre-consultas</p>
                            </div>
                            <div class="col-md-4 text-end">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-white border-0">
                                        <i class="fas fa-search text-primary"></i>
                                    </span>
                                    <input type="text" 
                                           class="form-control form-control-sm border-0" 
                                           placeholder="Buscar paciente..."
                                           wire:model.debounce.300ms="busqueda">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Alertas --}}
        @if (session()->has('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>✅ Éxito!</strong> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>❌ Error!</strong> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Cuestionario Status --}}
        @if($cuestionarioActivo)
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <strong>ℹ️ Cuestionario activo:</strong> {{ $cuestionarioActivo->titulo }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @else
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <strong>⚠️ Sin cuestionario:</strong> No hay cuestionario de pre-consulta activo.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Filtros --}}
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body py-2">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <small class="text-muted">Filtrar por estado:</small>
                                <div class="btn-group btn-group-sm" role="group">
                                    <button type="button" 
                                            class="btn btn-outline-primary {{ $filtroEstado === 'todos' ? 'active' : '' }}"
                                            wire:click="$set('filtroEstado', 'todos')">
                                        Todos
                                    </button>
                                    <button type="button" 
                                            class="btn btn-outline-primary {{ $filtroEstado === 'pendiente' ? 'active' : '' }}"
                                            wire:click="$set('filtroEstado', 'pendiente')">
                                        Pendientes
                                    </button>
                                    <button type="button" 
                                            class="btn btn-outline-primary {{ $filtroEstado === 'en_curso' ? 'active' : '' }}"
                                            wire:click="$set('filtroEstado', 'en_curso')">
                                        En Curso
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6 text-end">
                                <small class="text-muted">
                                    Citas hoy: <span class="badge bg-primary">{{ $citasHoy->count() }}</span>
                                    Sin cita: <span class="badge bg-secondary">{{ $pacientesSinCita->count() }}</span>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Citas de Hoy --}}
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">📅 Citas de Hoy</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped mb-0">
                                <thead class="table-dark">
                                    <tr>
                                        <th width="50">#</th>
                                        <th>Paciente</th>
                                        <th>Documento</th>
                                        <th>Hora</th>
                                        <th>Doctor</th>
                                        <th>Sucursal</th>
                                        <th>Estado</th>
                                        <th>Pre-consulta</th>
                                        <th width="200">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($citasHoy as $index => $cita)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar avatar-sm me-2">
                                                        @if($cita->paciente->foto)
                                                            <img src="{{ Storage::url($cita->paciente->foto) }}" 
                                                                 alt="{{ $cita->paciente->nombres }}" 
                                                                 class="avatar-img rounded-circle">
                                                        @else
                                                            <div class="avatar-img rounded-circle bg-primary text-white d-flex align-items-center justify-content-center">
                                                                {{ substr($cita->paciente->nombres, 0, 1) }}{{ substr($cita->paciente->apellidos, 0, 1) }}
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold">{{ $cita->paciente->nombres }} {{ $cita->paciente->apellidos }}</div>
                                                        <small class="text-muted">{{ $cita->paciente->telefono }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">{{ $cita->paciente->documento_identidad ?? 'N/A' }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">{{ \Carbon\Carbon::parse($cita->fecha_hora)->format('H:i') }}</span>
                                            </td>
                                            <td>{{ $cita->doctor->nombre ?? 'N/A' }}</td>
                                            <td>{{ $cita->sucursal->nombre ?? 'N/A' }}</td>
                                            <td>
                                                @if($cita->estado === 'pendiente')
                                                    <span class="badge bg-warning">⏳ Pendiente</span>
                                                @elseif($cita->estado === 'en_curso')
                                                    <span class="badge bg-success">🔄 En Curso</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ ucfirst($cita->estado) }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($cita->estado_preconsulta === 'pendiente')
                                                    <span class="badge bg-light text-dark">📋 Pendiente</span>
                                                @elseif($cita->estado_preconsulta === 'enviado')
                                                    <span class="badge bg-info">📤 Enviado</span>
                                                @elseif($cita->estado_preconsulta === 'completado')
                                                    <span class="badge bg-success">✅ Completado</span>
                                                @else
                                                    <span class="badge bg-light text-dark">❌ Sin cuestionario</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    @if($cita->estado === 'pendiente')
                                                        <button wire:click="abrirModalIniciar({{ $cita->id }})" 
                                                                class="btn btn-success btn-sm"
                                                                title="Iniciar Consulta">
                                                            <i class="fas fa-play"></i> Iniciar
                                                        </button>
                                                    @endif
                                                    
                                                    @if($cita->token_preconsulta && $cita->estado_preconsulta !== 'completado')
                                                        <button wire:click="enviarCuestionarioWhatsApp({{ $cita->id }})" 
                                                                class="btn btn-primary btn-sm"
                                                                title="Enviar Cuestionario por WhatsApp">
                                                            <i class="fab fa-whatsapp"></i> Enviar
                                                        </button>
                                                    @endif

                                                    <button class="btn btn-outline-secondary btn-sm" 
                                                            title="Ver Pre-consulta"
                                                            @if($cita->estado_preconsulta === 'completado')
                                                                onclick="alert('Ver respuestas de pre-consulta')"
                                                            @else
                                                                disabled
                                                            @endif>
                                                        <i class="fas fa-eye"></i> Ver
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center py-4">
                                                <div class="text-muted">
                                                    <i class="fas fa-calendar-times fa-2x mb-2"></i>
                                                    <p>No hay citas para hoy</p>
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
        </div>

        {{-- Pacientes sin Cita --}}
        @if($pacientesSinCita->count() > 0)
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-secondary text-white">
                            <h5 class="mb-0">👥 Pacientes sin Cita</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped mb-0">
                                    <thead class="table-dark">
                                        <tr>
                                            <th width="50">#</th>
                                            <th>Paciente</th>
                                            <th>Documento</th>
                                            <th>Teléfono</th>
                                            <th>Última Visita</th>
                                            <th width="150">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($pacientesSinCita as $index => $paciente)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar avatar-sm me-2">
                                                            @if($paciente->foto)
                                                                <img src="{{ Storage::url($paciente->foto) }}" 
                                                                     alt="{{ $paciente->nombres }}" 
                                                                     class="avatar-img rounded-circle">
                                                            @else
                                                                <div class="avatar-img rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center">
                                                                    {{ substr($paciente->nombres, 0, 1) }}{{ substr($paciente->apellidos, 0, 1) }}
                                                                </div>
                                                            @endif
                                                        </div>
                                                        <div>
                                                            <div class="fw-bold">{{ $paciente->nombres }} {{ $paciente->apellidos }}</div>
                                                            <small class="text-muted">{{ $paciente->email }}</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-secondary">{{ $paciente->documento_identidad ?? 'N/A' }}</span>
                                                </td>
                                                <td>{{ $paciente->telefono ?? 'N/A' }}</td>
                                                <td>
                                                    @if($paciente->ultima_cita)
                                                        <span class="text-muted">{{ $paciente->ultima_cita->format('d/m/Y') }}</span>
                                                    @else
                                                        <span class="text-muted">Primera vez</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <button class="btn btn-primary btn-sm" title="Crear Cita">
                                                        <i class="fas fa-plus"></i> Crear Cita
                                                    </button>
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
        @endif

        {{-- Modal para Iniciar Consulta --}}
        @if($mostrarModalIniciar)
            <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title">Iniciar Consulta</h5>
                            <button type="button" class="btn-close btn-close-white" wire:click="cerrarModalIniciar"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="avatar avatar-md me-3">
                                            @if($citaSeleccionada->paciente->foto)
                                                <img src="{{ Storage::url($citaSeleccionada->paciente->foto) }}" 
                                                     alt="{{ $citaSeleccionada->paciente->nombres }}" 
                                                     class="avatar-img rounded-circle">
                                            @else
                                                <div class="avatar-img rounded-circle bg-primary text-white d-flex align-items-center justify-content-center">
                                                    {{ substr($citaSeleccionada->paciente->nombres, 0, 1) }}{{ substr($citaSeleccionada->paciente->apellidos, 0, 1) }}
                                                </div>
                                            @endif
                                        </div>
                                        <div>
                                            <h6 class="mb-0">{{ $citaSeleccionada->paciente->nombres }} {{ $citaSeleccionada->paciente->apellidos }}</h6>
                                            <small class="text-muted">{{ $citaSeleccionada->paciente->documento_identidad }}</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label for="motivoConsulta" class="form-label">Motivo de la Consulta</label>
                                        <textarea class="form-control @error('motivoConsulta') is-invalid @enderror" 
                                                  id="motivoConsulta" 
                                                  rows="3" 
                                                  placeholder="Describa el motivo de la consulta..."
                                                  wire:model="motivoConsulta"></textarea>
                                        @error('motivoConsulta')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            @if($cuestionarioActivo)
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="alert alert-info">
                                            <strong>ℹ️ Cuestionario disponible:</strong> Se enviará el cuestionario "{{ $cuestionarioActivo->titulo }}" al paciente.
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="cerrarModalIniciar">Cancelar</button>
                            <button type="button" class="btn btn-primary" wire:click="iniciarConsulta" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="iniciarConsulta">
                                    <i class="fas fa-play"></i> Iniciar Consulta
                                </span>
                                <span wire:loading wire:target="iniciarConsulta">
                                    <i class="fas fa-spinner fa-spin"></i> Procesando...
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>