<div>
    @section('title', 'Horarios - ' . $enfermero->nombres . ' ' . $enfermero->apellidos)
    
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0">
                <i class="fas fa-clock me-2"></i>Horarios de Atención
            </h2>
            <p class="text-muted mb-0">{{ $enfermero->nombres }} {{ $enfermero->apellidos }} - {{ $enfermero->tipo_enfermero }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.enfermeros.edit', $enfermero->id) }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Volver al Enfermero/a
            </a>
        </div>
    </div>

    <!-- Mensajes -->
    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Información del enfermero -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-2 text-center">
                   <div class="avatar avatar-xl @if($enfermero->status) avatar-online @else avatar-offline @endif">
                    @if($enfermero->iniciales)
                    <span class="avatar-initials bg-primary text-white fa-20px">{{ $enfermero->iniciales }}</span>
                    @else
                    <img src="{{ asset('materialize/assets/img/avatars/1.png') }}" alt="avatar" class="w-px-40 h-auto rounded-circle" />
                    @endif
                  </div> 
                </div>
                <div class="col-md-6">
                    <h5 class="mb-1">{{ $enfermero->nombres }} {{ $enfermero->apellidos }}</h5>
                    <p class="text-muted mb-1">
                        <i class="fas fa-id-card me-2"></i>{{ $enfermero->documento_identidad }}
                        <span class="mx-2">|</span>
                        <i class="fas fa-certificate me-2"></i>{{ $enfermero->licencia_enfermeria }}
                    </p>
                    <p class="mb-0">
                        <span class="badge bg-info me-2">{{ $enfermero->tipo_enfermero }}</span>
                        <span class="badge bg-secondary">{{ $enfermero->nivel_experiencia }}</span>
                    </p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="mb-2">
                        <span class="text-muted">Estado:</span>
                        <span class="badge bg-{{ $enfermero->status ? 'success' : 'danger' }}">
                            {{ $enfermero->status ? 'Activo' : 'Inactivo' }}
                        </span>
                    </div>
                    <div>
                        <span class="text-muted">Experiencia:</span>
                        <strong>{{ $enfermero->anios_experiencia }} años</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulario de horarios -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Configuración de Horarios</h5>
        </div>
        <div class="card-body">
            <form wire:submit.prevent="guardarHorarios">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 15%">Día</th>
                                <th style="width: 15%" class="text-center">Activo</th>
                                <th style="width: 20%">Hora Inicio</th>
                                <th style="width: 20%">Hora Fin</th>
                                <th style="width: 20%">Duración Cita</th>
                                <th style="width: 10%" class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach([1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo'] as $dia => $nombre)
                                <tr class="{{ isset($horarios[$dia]['activo']) && $horarios[$dia]['activo'] ? '' : 'table-secondary' }}">
                                    <td>
                                        <strong>{{ $nombre }}</strong>
                                        <br><small class="text-muted">Día {{ $dia }}</small>
                                    </td>
                                    <td class="text-center">
                                        <div class="form-check form-switch d-flex justify-content-center">
                                            <input class="form-check-input" type="checkbox" 
                                                   id="dia_{{ $dia }}" 
                                                   wire:model="horarios.{{ $dia }}.activo"
                                                   wire:change="actualizarEstadoDia({{ $dia }})">
                                        </div>
                                    </td>
                                    <td>
                                        <input type="time" class="form-control form-control-sm" 
                                               wire:model="horarios.{{ $dia }}.hora_inicio"
                                               @disabled(!isset($horarios[$dia]['activo']) || !$horarios[$dia]['activo'])
                                               min="06:00" max="22:00">
                                        @error("horarios.{$dia}.hora_inicio")
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </td>
                                    <td>
                                        <input type="time" class="form-control form-control-sm" 
                                               wire:model="horarios.{{ $dia }}.hora_fin"
                                               @disabled(!isset($horarios[$dia]['activo']) || !$horarios[$dia]['activo'])
                                               min="06:00" max="22:00">
                                        @error("horarios.{$dia}.hora_fin")
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </td>
                                    <td>
                                        <select class="form-control form-control-sm" 
                                                wire:model="horarios.{{ $dia }}.duracion_cita"
                                                @disabled(!isset($horarios[$dia]['activo']) || !$horarios[$dia]['activo'])>
                                            <option value="15">15 min</option>
                                            <option value="30">30 min</option>
                                            <option value="45">45 min</option>
                                            <option value="60">1 hora</option>
                                            <option value="90">1.5 horas</option>
                                            <option value="120">2 horas</option>
                                        </select>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                                wire:click="copiarHorario({{ $dia }})"
                                                title="Copiar este horario">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="row mt-3">
                    <div class="col-md-12">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <button type="button" class="btn btn-outline-secondary btn-sm" wire:click="activarDiasHabiles">
                                    <i class="fas fa-calendar-check me-2"></i>Activar días hábiles
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" wire:click="desactivarTodos">
                                    <i class="fas fa-calendar-times me-2"></i>Desactivar todos
                                </button>
                            </div>
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Guardar Horarios
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Resumen de horarios -->
    <div class="row">
        <div class="col-md-4">
            <div class="card bg-light">
                <div class="card-body text-center">
                    <h5 class="text-primary">{{ $resumenHorarios['dias_activos'] }}</h5>
                    <p class="mb-0 text-muted">Días activos</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-light">
                <div class="card-body text-center">
                    <h5 class="text-success">{{ $resumenHorarios['horas_semanales'] }}</h5>
                    <p class="mb-0 text-muted">Horas semanales</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-light">
                <div class="card-body text-center">
                    <h5 class="text-info">{{ $resumenHorarios['promedio_duracion'] }} min</h5>
                    <p class="mb-0 text-muted">Duración promedio</p>
                </div>
            </div>
        </div>
    </div>
</div>