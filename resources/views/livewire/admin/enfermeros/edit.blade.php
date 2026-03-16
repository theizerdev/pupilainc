<div>
    @section('title', 'Editar Enfermero/a')
    
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-user-nurse me-2"></i>Editar Enfermero/a
        </h2>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.enfermeros.horarios', $enfermero->id) }}" class="btn btn-info">
                <i class="fas fa-clock me-2"></i>Horarios
            </a>
            <a href="{{ route('admin.enfermeros.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Volver
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

    <!-- Formulario -->
    <form wire:submit.prevent="update">
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
                        </div>
                        <div class="row">
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
                                    <label for="newPassword">Nueva Contraseña</label>
                                    <input type="text" class="form-control @error('newPassword') is-invalid @enderror" 
                                           id="newPassword" wire:model="newPassword" placeholder="Dejar vacío para mantener actual">
                                    <small class="form-text text-muted">
                                        Dejar vacío para mantener la contraseña actual.
                                    </small>
                                    @error('newPassword')
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
                                    <label for="especialidad_enfermeria">Especialidades en Enfermería</label>
                                    <div class="input-group mb-2">
                                        <input type="text" class="form-control @error('nueva_especialidad') is-invalid @enderror" 
                                               wire:model="nueva_especialidad" placeholder="Ej: Enfermería Quirúrgica, Pediatría, etc."
                                               wire:keydown.enter="agregarEspecialidad">
                                        <button type="button" class="btn btn-primary" wire:click="agregarEspecialidad">
                                            <i class="fas fa-plus"></i> Agregar
                                        </button>
                                        @error('nueva_especialidad')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="mb-2">
                                        @foreach($especialidades as $index => $especialidad)
                                            <span class="badge bg-primary me-1 mb-1">
                                                {{ $especialidad }}
                                                <button type="button" class="btn-close btn-close-white ms-2" 
                                                        wire:click="eliminarEspecialidad({{ $index }})"
                                                        style="font-size: 0.6rem;" 
                                                        aria-label="Eliminar"></button>
                                            </span>
                                        @endforeach
                                    </div>
                                    @if(count($especialidades) === 0)
                                        <small class="form-text text-muted">
                                            No hay especialidades agregadas. Use el campo de arriba para agregar especialidades.
                                        </small>
                                    @else
                                        <small class="form-text text-muted">
                                            Las especialidades se guardarán al actualizar el enfermero.
                                        </small>
                                    @endif
                                </div>
                            </div>
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

                <!-- Estado -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-toggle-on me-2"></i>Estado</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="status" wire:model="status" wire:change="actualizarEstado">
                                <label class="form-check-label" for="status">
                                    {{ $status ? 'Activo' : 'Inactivo' }}
                                </label>
                            </div>
                            <small class="form-text text-muted">
                                Los enfermeros/as inactivos no podrán acceder al sistema.
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Información del Enfermero -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Información del Enfermero/a</h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                           <div class="avatar @if($enfermero->status) avatar-online @else avatar-offline @endif">
                              @if($enfermero->iniciales)
                                <span class="avatar-initials bg-primary text-white">{{ $enfermero->iniciales }}</span>
                              @else
                                <img src="{{ asset('materialize/assets/img/avatars/1.png') }}" alt="avatar" class="w-px-40 h-auto rounded-circle" />
                              @endif
                            </div> 
                            <h6>{{ $enfermero->nombres }} {{ $enfermero->apellidos }}</h6>
                            <p class="text-muted small">{{ $enfermero->tipo_enfermero }}</p>
                        </div>
                        <hr>
                        <div class="row text-center">
                            <div class="col-6">
                                <h5 class="mb-0">{{ $enfermero->anios_experiencia }}</h5>
                                <small class="text-muted">Años de experiencia</small>
                            </div>
                            <div class="col-6">
                                <h5 class="mb-0">{{ $enfermero->nivel_experiencia }}</h5>
                                <small class="text-muted">Nivel</small>
                            </div>
                        </div>
                        <hr>
                        <div class="d-grid gap-2">
                            <a href="{{ route('admin.enfermeros.horarios', $enfermero->id) }}" class="btn btn-info">
                                <i class="fas fa-clock me-2"></i>Gestionar Horarios
                            </a>
                            <button type="button" class="btn btn-warning" wire:click="resetPassword"
                                    onclick="confirm('¿Está seguro de restablecer la contraseña?') || event.stopImmediatePropagation()">
                                <i class="fas fa-key me-2"></i>Restablecer Contraseña
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Mensaje de Bienvenida WhatsApp -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fab fa-whatsapp me-2 text-success"></i>Mensaje de Bienvenida</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-3">Envíe un mensaje de bienvenida por WhatsApp con las credenciales de acceso al sistema.</p>
                        
                        @if($enfermero->user && $enfermero->telefono)
                            <div class="d-grid gap-2">
                                <button type="button" class="btn btn-success" wire:click="enviarMensajeBienvenida"
                                        onclick="confirm('¿Está seguro de enviar el mensaje de bienvenida por WhatsApp?') || event.stopImmediatePropagation()">
                                    <i class="fab fa-whatsapp me-2"></i>Enviar Mensaje de Bienvenida
                                </button>
                            </div>
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Se enviará: usuario, contraseña temporal, empresa, sucursal y especialidades.
                            </small>
                        @else
                            <div class="alert alert-warning mb-0">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                @if(!$enfermero->user)
                                    El enfermero debe tener un usuario asociado.
                                @elseif(!$enfermero->telefono)
                                    El enfermero debe tener un teléfono registrado.
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Botones de acción -->
                <div class="card">
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary w-100 mb-2">
                            <i class="fas fa-save me-2"></i>Guardar Cambios
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