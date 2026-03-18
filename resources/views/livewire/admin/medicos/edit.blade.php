<div>
    @section('title', 'Editar Médico')
    
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-user-md me-2"></i>Editar Médico
        </h2>
        <a href="{{ route('admin.medicos.index') }}" class="btn btn-secondary">
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
    <form wire:submit.prevent="save">
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
                                           id="documento_identidad" wire:model="documento_identidad" placeholder="DNI/Pasaporte">
                                    @error('documento_identidad')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                             <div class="col-md-6">
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
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="licencia_medica">Cédula Médica *</label>
                                    <input type="text" class="form-control @error('licencia_medica') is-invalid @enderror" 
                                           id="licencia_medica" wire:model="licencia_medica" placeholder="Número de licencia">
                                    @error('licencia_medica')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group mb-3">
                                    <label for="email">Email *</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                           id="email" wire:model="email" placeholder="correo@ejemplo.com">
                                    @error('email')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group mb-3">
                                    <label for="direccion">Dirección</label>
                                    <input type="text" class="form-control @error('direccion') is-invalid @enderror" 
                                           id="direccion" wire:model="direccion" placeholder="Dirección completa">
                                    @error('direccion')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label for="anios_experiencia">Años de Experiencia *</label>
                                    <input type="number" class="form-control @error('anios_experiencia') is-invalid @enderror" 
                                           id="anios_experiencia" wire:model="anios_experiencia" min="0" max="50">
                                    @error('anios_experiencia')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
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
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label for="status">Estado</label>
                                    <select class="form-control @error('status') is-invalid @enderror" 
                                            id="status" wire:model="status">
                                        <option value="1">Activo</option>
                                        <option value="0">Inactivo</option>
                                    </select>
                                    @error('status')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Especialidad Principal -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-stethoscope me-2"></i>Especialidad Principal</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="especialidad_id">Especialidad *</label>
                                    <select class="form-control @error('especialidad_id') is-invalid @enderror" 
                                            id="especialidad_id" wire:model="especialidad_id">
                                        <option value="">Seleccione una especialidad</option>
                                        @foreach($especialidades as $especialidad)
                                            <option value="{{ $especialidad->id }}">{{ $especialidad->nombre }}</option>
                                        @endforeach
                                    </select>
                                    @error('especialidad_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="tarifa_consulta">Tarifa de Consulta</label>
                                    <input type="number" class="form-control @error('tarifa_consulta') is-invalid @enderror" 
                                           id="tarifa_consulta" wire:model="tarifa_consulta" step="0.01" min="0">
                                    @error('tarifa_consulta')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Subespecialidades con Experiencia y Nivel -->
                @if($especialidad_id)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-user-md me-2"></i>Subespecialidades</h5>
                        <small class="text-muted">Seleccione las subespecialidades y configure experiencia/nivel</small>
                    </div>
                    <div class="card-body">
                        @if($subespecialidades->count() > 0)
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label>Subespecialidades Disponibles:</label>
                                    <div class="row">
                                        @foreach($subespecialidades as $subespecialidad)
                                            <div class="col-md-4 mb-2">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" 
                                                           id="subesp_{{ $subespecialidad->id }}" 
                                                           value="{{ $subespecialidad->id }}"
                                                           wire:model="subespecialidades_seleccionadas">
                                                    <label class="form-check-label" for="subesp_{{ $subespecialidad->id }}">
                                                        {{ $subespecialidad->nombre }}
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            @if(count($subespecialidades_seleccionadas) > 0)
                                <div class="row">
                                    <div class="col-md-12">
                                        <h6 class="mb-3">Configuración de Subespecialidades Seleccionadas:</h6>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Subespecialidad</th>
                                                        <th>Años Experiencia</th>
                                                        <th>Nivel</th>
                                                        <th>Tarifa Consulta</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($subespecialidades_seleccionadas as $subespecialidadId)
                                                        @php
                                                            $subespecialidad = $subespecialidades->find($subespecialidadId);
                                                            $data = $subespecialidades_data[$subespecialidadId] ?? ['experiencia_anios' => 0, 'nivel_experiencia' => 'Básico', 'tarifa_consulta' => null];
                                                        @endphp
                                                        <tr>
                                                            <td>{{ $subespecialidad->nombre }}</td>
                                                            <td>
                                                                <input type="number" class="form-control form-control-sm" 
                                                                       wire:model="subespecialidades_data.{{ $subespecialidadId }}.experiencia_anios"
                                                                       min="0" max="50" placeholder="Años">
                                                            </td>
                                                            <td>
                                                                <select class="form-select form-select-sm" 
                                                                        wire:model="subespecialidades_data.{{ $subespecialidadId }}.nivel_experiencia">
                                                                    <option value="Básico">Básico</option>
                                                                    <option value="Intermedio">Intermedio</option>
                                                                    <option value="Avanzado">Avanzado</option>
                                                                </select>
                                                            </td>
                                                            <td>
                                                                <input type="number" class="form-control form-control-sm" 
                                                                       wire:model="subespecialidades_data.{{ $subespecialidadId }}.tarifa_consulta"
                                                                       step="0.01" min="0" placeholder="Tarifa">
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @else
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>No hay subespecialidades disponibles para esta especialidad.
                            </div>
                        @endif
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
                                                           wire:model="horarios.{{ $dia }}.activo" 
                                                           id="dia_{{ $dia }}">
                                                </div>
                                            </td>
                                            <td>
                                                @if($horarios[$dia]['activo'])
                                                    <input type="time" class="form-control form-control-sm" 
                                                           wire:model="horarios.{{ $dia }}.hora_inicio">
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($horarios[$dia]['activo'])
                                                    <input type="time" class="form-control form-control-sm" 
                                                           wire:model="horarios.{{ $dia }}.hora_fin">
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($horarios[$dia]['activo'])
                                                    <select class="form-select form-select-sm" 
                                                            wire:model="horarios.{{ $dia }}.duracion_cita">
                                                        <option value="15">15 min</option>
                                                        <option value="20">20 min</option>
                                                        <option value="30">30 min</option>
                                                        <option value="45">45 min</option>
                                                        <option value="60">1 hora</option>
                                                        <option value="90">1.5 horas</option>
                                                        <option value="120">2 horas</option>
                                                    </select>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Información Adicional -->
            <div class="col-md-4">
                <!-- Empresa y Sucursal -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-building me-2"></i>Ubicación</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group mb-3">
                            <label for="empresa_id">Empresa *</label>
                            <select class="form-control @error('empresa_id') is-invalid @enderror" 
                                    id="empresa_id" wire:model="empresa_id">
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
                                @if($empresa_id)
                                    @foreach($sucursales as $sucursal)
                                        <option value="{{ $sucursal->id }}">{{ $sucursal->nombre }}</option>
                                    @endforeach
                                @endif
                            </select>
                            @error('sucursal_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Acciones -->
                <div class="card">
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary w-100 mb-2">
                            <i class="fas fa-save me-2"></i>Actualizar Médico
                        </button>
                        <a href="{{ route('admin.medicos.index') }}" class="btn btn-secondary w-100">
                            <i class="fas fa-times me-2"></i>Cancelar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
