<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-calendar-check me-2"></i>
                        Estadísticas de Confirmación de Citas
                    </h4>
                </div>
                <div class="card-body">
                    <!-- Filtros -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <label class="form-label">Estado</label>
                            <select wire:model.live="filtroEstado" class="form-select">
                                <option value="">Todos</option>
                                <option value="pendiente">Pendiente</option>
                                <option value="confirmado">Confirmado</option>
                                <option value="rechazado">Rechazado</option>
                                <option value="sin_respuesta">Sin Respuesta</option>
                                <option value="expirado">Expirado</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Método</label>
                            <select wire:model.live="filtroMetodo" class="form-select">
                                <option value="">Todos</option>
                                <option value="whatsapp">WhatsApp</option>
                                <option value="email">Email</option>
                                <option value="sms">SMS</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Fecha Inicio</label>
                            <input type="date" wire:model.live="filtroFechaInicio" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Fecha Fin</label>
                            <input type="date" wire:model.live="filtroFechaFin" class="form-control">
                        </div>
                    </div>

                    <!-- Estadísticas Principales -->
                    <div class="row mb-4">
                        <div class="col-md-2">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h3 class="text-primary">{{ $stats['total_confirmaciones'] ?? 0 }}</h3>
                                    <small class="text-muted">Total</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h3>{{ $stats['confirmadas'] ?? 0 }}</h3>
                                    <small>Confirmadas</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-danger text-white">
                                <div class="card-body text-center">
                                    <h3>{{ $stats['rechazadas'] ?? 0 }}</h3>
                                    <small>Rechazadas</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center">
                                    <h3>{{ $stats['pendientes'] ?? 0 }}</h3>
                                    <small>Pendientes</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-secondary text-white">
                                <div class="card-body text-center">
                                    <h3>{{ $stats['sin_respuesta'] ?? 0 }}</h3>
                                    <small>Sin Respuesta</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-dark text-white">
                                <div class="card-body text-center">
                                    <h3>{{ $stats['expiradas'] ?? 0 }}</h3>
                                    <small>Expiradas</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tasas -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Tasa de Confirmación</h5>
                                    <div class="progress mb-2" style="height: 25px;">
                                        <div class="progress-bar bg-success" role="progressbar" 
                                             style="width: {{ $stats['tasa_confirmacion'] ?? 0 }}%">
                                            {{ $stats['tasa_confirmacion'] ?? 0 }}%
                                        </div>
                                    </div>
                                    <small class="text-muted">
                                        Porcentaje de citas confirmadas sobre el total enviado
                                    </small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Tasa de Respuesta</h5>
                                    <div class="progress mb-2" style="height: 25px;">
                                        <div class="progress-bar bg-info" role="progressbar" 
                                             style="width: {{ $stats['tasa_respuesta'] ?? 0 }}%">
                                            {{ $stats['tasa_respuesta'] ?? 0 }}%
                                        </div>
                                    </div>
                                    <small class="text-muted">
                                        Porcentaje de respuestas recibidas (confirmadas + rechazadas)
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Acciones -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <button wire:click="reintentarPendientes" class="btn btn-warning" 
                                    onclick="return confirm('¿Reintentar todas las confirmaciones pendientes?')">
                                <i class="fas fa-redo me-2"></i>
                                Reintentar Pendientes
                            </button>
                            <button wire:click="cargarEstadisticas" class="btn btn-primary ms-2">
                                <i class="fas fa-sync me-2"></i>
                                Actualizar
                            </button>
                        </div>
                    </div>

                    <!-- Tabla de Confirmaciones Recientes -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Confirmaciones Recientes</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Paciente</th>
                                            <th>Médico</th>
                                            <th>Fecha Cita</th>
                                            <th>Método</th>
                                            <th>Estado</th>
                                            <th>Intentos</th>
                                            <th>Enviado</th>
                                            <th>Respuesta</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($confirmacionesRecientes as $confirmacion)
                                            <tr>
                                                <td>
                                                    <strong>{{ $confirmacion->cita->paciente->nombre_completo ?? 'N/A' }}</strong>
                                                    <br>
                                                    <small class="text-muted">{{ $confirmacion->destinatario }}</small>
                                                </td>
                                                <td>{{ $confirmacion->cita->medico->nombre_completo ?? 'N/A' }}</td>
                                                <td>
                                                    @if($confirmacion->cita)
                                                        {{ $confirmacion->cita->fecha_inicio->format('d/m/Y H:i') }}
                                                    @else
                                                        N/A
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge bg-info">
                                                        {{ strtoupper($confirmacion->metodo) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @php
                                                        $badgeClass = match($confirmacion->estado) {
                                                            'confirmado' => 'bg-success',
                                                            'rechazado' => 'bg-danger',
                                                            'pendiente' => 'bg-warning',
                                                            'sin_respuesta' => 'bg-secondary',
                                                            'expirado' => 'bg-dark',
                                                            default => 'bg-light'
                                                        };
                                                    @endphp
                                                    <span class="badge {{ $badgeClass }}">
                                                        {{ ucfirst($confirmacion->estado) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge {{ $confirmacion->intentos >= 3 ? 'bg-danger' : 'bg-info' }}">
                                                        {{ $confirmacion->intentos }}/{{ $confirmacion->max_intentos }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @if($confirmacion->fecha_envio)
                                                        {{ $confirmacion->fecha_envio->format('d/m/Y H:i') }}
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($confirmacion->fecha_respuesta)
                                                        <span class="text-success">
                                                            <i class="fas fa-check me-1"></i>
                                                            {{ $confirmacion->fecha_respuesta->format('d/m/Y H:i') }}
                                                        </span>
                                                        <br>
                                                        <small class="text-muted">
                                                            {{ $confirmacion->tiempo_respuesta }} min
                                                        </small>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($confirmacion->puedeReintentar())
                                                        <button wire:click="reintentarConfirmacion({{ $confirmacion->id }})" 
                                                                class="btn btn-sm btn-outline-warning"
                                                                title="Reintentar">
                                                            <i class="fas fa-redo"></i>
                                                        </button>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="9" class="text-center text-muted py-4">
                                                    <i class="fas fa-inbox fa-2x mb-2"></i>
                                                    <br>
                                                    No hay confirmaciones recientes
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
        </div>
    </div>
</div>
