<div>
    @section('title', 'Crear Enfermero/a')
    
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-user-nurse me-2"></i>Crear Enfermero/a
        </h2>
        <a href="{{ route('admin.enfermeros.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Volver
        </a>
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

    <!-- Formulario -->
    <form wire:submit.prevent="store">
        <div class="row">
            <!-- Información Personal -->
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-user me-2"></i>Información Personal</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="nombres">Nombres *</label>
                                    <input type="text" class="form-control @error('nombres') is-invalid @enderror" 
                                           id="nombres" wire:model="nombres" placeholder="Ingrese los nombres">
                                    @error('nombres')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="apellidos">Apellidos *</label>
                                    <input type="text" class="form-control @error('apellidos') is-invalid @enderror" 
                                           id="apellidos" wire:model="apellidos" placeholder="Ingrese los apellidos">
                                    @error('apellidos')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="documento_identidad">Documento de Identidad *</label>
                                    <input type="text" class="form-control @error('documento_identidad') is-invalid @enderror" 
                                           id="documento_identidad" wire:model="documento_identidad" placeholder="Ingrese el documento">
                                    @error('documento_identidad')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="genero">Género</label>
                                    <select id="genero" class="form-control @error('genero') is-invalid @enderror" wire:model="genero">
                                        <option value="">No especifica</option>
                                        <option value="Masculino">Masculino</option>
                                        <option value="Femenino">Femenino</option>
                                        <option value="Otro">Otro</option>
                                    </select>
                                    @error('genero')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-12">
                              <div class="form-group mb-3">
                                <label for="telefono" class="fw-bold">
                                    <i class="fas fa-phone me-1"></i>Teléfono
                                </label>
                                <input type="tel" 
                                    class="form-control form-control @error('telefono') is-invalid @enderror"
                                    id="telefono" 
                                    wire:model.blur="telefono"
                                    wire:change="formatPhone"
                                    placeholder="Ej: +58 412 1234567"
                                    autocomplete="tel"
                                    pattern="[\d\s\-\+\(\)]+"
                                    title="Solo números y caracteres válidos (+, -, espacios, paréntesis)"
                                    onkeypress="return /[0-9+\-()\s]/.test(String.fromCharCode(event.keyCode))">
                                @error('telefono')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                                <small class="text-muted mt-1 d-block">
                                    <i class="fas fa-info-circle me-1"></i>Solo se permiten números y caracteres de teléfono
                                </small>
                            </div>
                           </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group mb-3">
                                    <label for="direccion">Dirección</label>
                                    <textarea class="form-control @error('direccion') is-invalid @enderror" 
                                              id="direccion" wire:model="direccion" rows="2" placeholder="Ingrese la dirección"></textarea>
                                    @error('direccion')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Credenciales de Acceso -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-key me-2"></i>Credenciales de Acceso</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="email">Correo Electrónico *</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                           id="email" wire:model="email" placeholder="Ingrese el correo electrónico">
                                    @error('email')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="password">Contraseña Temporal *</label>
                                    <input type="text" class="form-control @error('password') is-invalid @enderror" 
                                           id="password" wire:model="password" placeholder="Contraseña temporal">
                                    <small class="form-text text-muted">
                                        La contraseña por defecto es el documento de identidad. El enfermero/a deberá cambiarla al iniciar sesión.
                                    </small>
                                    @error('password')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Información Profesional -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-stethoscope me-2"></i>Información Profesional</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="licencia_enfermeria">Cédula de Enfermería *</label>
                                    <input type="text" class="form-control @error('licencia_enfermeria') is-invalid @enderror" 
                                           id="licencia_enfermeria" wire:model="licencia_enfermeria" placeholder="Ingrese la cédula de enfermería">
                                    @error('licencia_enfermeria')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="anios_experiencia">Años de Experiencia *</label>
                                    <input type="number" class="form-control @error('anios_experiencia') is-invalid @enderror" 
                                           id="anios_experiencia" wire:model="anios_experiencia" min="0" max="50">
                                    @error('anios_experiencia')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="nivel_experiencia">Nivel de Experiencia *</label>
                                    <select class="form-control @error('nivel_experiencia') is-invalid @enderror" 
                                            id="nivel_experiencia" wire:model="nivel_experiencia">
                                        <option value="Básico">Básico</option>
                                        <option value="Intermedio">Intermedio</option>
                                        <option value="Avanzado">Avanzado</option>
                                    </select>
                                    @error('nivel_experiencia')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="tipo_enfermero">Tipo de Enfermero *</label>
                                    <select class="form-control @error('tipo_enfermero') is-invalid @enderror" 
                                            id="tipo_enfermero" wire:model="tipo_enfermero">
                                        <option value="General">General</option>
                                        <option value="Especialista">Especialista</option>
                                        <option value="Supervisor">Supervisor</option>
                                        <option value="Jefe de Servicio">Jefe de Servicio</option>
                                    </select>
                                    @error('tipo_enfermero')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group mb-3">
                                    <label>Especialidades en Enfermería</label>
                                    <div class="input-group mb-2">
                                        <input type="text" class="form-control @error('nueva_especialidad') is-invalid @enderror" 
                                               wire:model="nueva_especialidad" placeholder="Ej: Enfermería Quirúrgica, Pediatría, etc."
                                               wire:keydown.enter="agregarEspecialidad">
                                        <button type="button" class="btn btn-primary" wire:click="agregarEspecialidad">
                                            <i class="fas fa-plus"></i> Agregar
                                        </button>
                                    </div>
                                    @error('nueva_especialidad')
                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                    @enderror
                                    @error('especialidades.*')
                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                    @enderror
                                    
                                    @if(count($especialidades) > 0)
                                        <div class="mt-2">
                                            <small class="text-muted">Especialidades agregadas:</small>
                                            <div class="d-flex flex-wrap gap-2 mt-1">
                                                @foreach($especialidades as $index => $especialidad)
                                                    <span class="badge bg-primary">
                                                        {{ $especialidad }}
                                                        <button type="button" class="btn-close btn-close-white ms-2" 
                                                                wire:click="eliminarEspecialidad({{ $index }})"
                                                                style="font-size: 0.6rem;" 
                                                                aria-label="Eliminar"></button>
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Horarios de Atención -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Horario de Atención</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Día</th>
                                        <th>Activo</th>
                                        <th>Hora Inicio</th>
                                        <th>Hora Fin</th>
                                        <th>Duración Cita</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach([1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo'] as $dia => $nombre)
                                        <tr>
                                            <td><strong>{{ $nombre }}</strong></td>
                                            <td>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" 
                                                           id="dia_{{ $dia }}" 
                                                           wire:model="horarios.{{ $dia }}.activo">
                                                </div>
                                            </td>
                                            <td>
                                                <input type="time" class="form-control form-control-sm" 
                                                       wire:model="horarios.{{ $dia }}.hora_inicio"
                                                       @if(!isset($horarios[$dia]['activo']) || !$horarios[$dia]['activo']) readonly @endif>
                                            </td>
                                            <td>
                                                <input type="time" class="form-control form-control-sm" 
                                                       wire:model="horarios.{{ $dia }}.hora_fin"
                                                       @if(!isset($horarios[$dia]['activo']) || !$horarios[$dia]['activo']) readonly @endif>
                                            </td>
                                            <td>
                                                <select class="form-control form-control-sm" 
                                                        wire:model="horarios.{{ $dia }}.duracion_cita"
                                                        @if(!isset($horarios[$dia]['activo']) || !$horarios[$dia]['activo']) disabled @endif>
                                                    <option value="15">15 min</option>
                                                    <option value="30">30 min</option>
                                                    <option value="45">45 min</option>
                                                    <option value="60">1 hora</option>
                                                    <option value="90">1.5 horas</option>
                                                    <option value="120">2 horas</option>
                                                </select>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Panel lateral -->
            <div class="col-md-4">
                <!-- Empresa y Sucursal (solo para Super Admin) -->
                @if(auth()->user()->hasRole('Super Administrador'))
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-building me-2"></i>Ubicación</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group mb-3">
                                <label for="empresa_id">Empresa *</label>
                                <select class="form-control @error('empresa_id') is-invalid @enderror" 
                                        id="empresa_id" wire:model.change="empresa_id">
                                    <option value="">Seleccione una empresa</option>
                                    @foreach($empresas as $empresa)
                                        <option value="{{ $empresa->id }}">{{ $empresa->razon_social }}</option>
                                    @endforeach
                                </select>
                                @error('empresa_id')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group mb-3">
                                <label for="sucursal_id">Sucursal *</label>
                                <select class="form-control @error('sucursal_id') is-invalid @enderror" 
                                        id="sucursal_id" wire:model="sucursal_id">
                                    <option value="">Seleccione una sucursal</option>
                                    @foreach($sucursales as $sucursal)
                                        <option value="{{ $sucursal->id }}">{{ $sucursal->nombre }}</option>
                                    @endforeach
                                </select>
                                @error('sucursal_id')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Resumen -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Resumen</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">
                            Al crear este enfermero/a:
                        </p>
                        <ul class="text-muted small">
                            <li>Se creará un usuario con rol "Enfermería"</li>
                            <li>Se enviarán las credenciales por WhatsApp</li>
                            <li>El enfermero/a podrá acceder al sistema</li>
                            <li>Se asignarán los horarios de atención</li>
                        </ul>
                    </div>
                </div>

                <!-- Botones de acción -->
                <div class="card">
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary w-100 mb-2">
                            <i class="fas fa-save me-2"></i>Crear Enfermero/a
                        </button>
                        <a href="{{ route('admin.enfermeros.index') }}" class="btn btn-secondary w-100">
                            <i class="fas fa-times me-2"></i>Cancelar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>