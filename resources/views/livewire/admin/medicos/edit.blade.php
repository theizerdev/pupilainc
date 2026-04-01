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
                                        La contraseña por defecto es "password". El médico deberá cambiarla al iniciar sesión.
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
                                    <label for="licencia_medica">Cédula Médica *</label>
                                    <input type="text" class="form-control @error('licencia_medica') is-invalid @enderror"
                                           id="licencia_medica" wire:model="licencia_medica" placeholder="Ingrese la cédula médica">
                                    @error('licencia_medica')
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
                <!-- Especialidad y Subespecialidades -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-user-md me-2"></i>Especialidad y Subespecialidades</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group mb-3">
                                    <label for="especialidad_id">Especialidad Principal *</label>
                                    <select class="form-control @error('especialidad_id') is-invalid @enderror"
                                            id="especialidad_id" wire:model.change="especialidad_id">
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
                        </div>

                        @if($especialidad_id)
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <label class="fw-bold">Subespecialidades (Opcional)</label>
                                        <button type="button" class="btn btn-sm btn-outline-primary" wire:click="openSubespecialidadModal">
                                            <i class="fas fa-plus me-1"></i> Nueva Subespecialidad
                                        </button>
                                    </div>
                                        <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                                            @forelse($subespecialidades as $subespecialidad)
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox"
                                                           id="subespecialidad_{{ $subespecialidad->id }}"
                                                           value="{{ $subespecialidad->id }}"
                                                           wire:model.live="subespecialidades_seleccionadas">
                                                    <label class="form-check-label" for="subespecialidad_{{ $subespecialidad->id }}">
                                                        {{ $subespecialidad->nombre }}
                                                    </label>
                                                </div>

                                                @if(in_array($subespecialidad->id, $subespecialidades_seleccionadas))
                                                    <div class="ms-4 mb-3 p-2 bg-light rounded">
                                                        <div class="row">
                                                            <div class="col-md-4">
                                                                <div class="form-group mb-2">
                                                                    <label class="small">Experiencia (años)</label>
                                                                    <input type="number" class="form-control form-control-sm"
                                                                           wire:model="subespecialidades_data.{{ $subespecialidad->id }}.experiencia_anios"
                                                                           min="0" max="50">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="form-group mb-2">
                                                                    <label class="small">Nivel</label>
                                                                    <select class="form-control form-control-sm"
                                                                            wire:model="subespecialidades_data.{{ $subespecialidad->id }}.nivel_experiencia">
                                                                        <option value="Básico">Básico</option>
                                                                        <option value="Intermedio">Intermedio</option>
                                                                        <option value="Avanzado">Avanzado</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="form-group mb-2">
                                                                    <label class="small">Tarifa (opcional)</label>
                                                                    <input type="number" class="form-control form-control-sm"
                                                                           wire:model="subespecialidades_data.{{ $subespecialidad->id }}.tarifa_consulta"
                                                                           step="0.01" min="0">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            @empty
                                                <p class="text-muted">No hay subespecialidades disponibles para esta especialidad.</p>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
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
            </div>
            <!-- Panel Derecho - Resumen y Empresa/Sucursal -->
            <div class="col-md-4">
                <!-- Empresa y Sucursal -->
                @if(auth()->user()->hasRole('Super Administrador'))
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-building me-2"></i>Empresa y Sucursal</h5>
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
                                        id="sucursal_id" wire:model="sucursal_id" @if(!$empresa_id) disabled @endif>
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
                @else
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-building me-2"></i>Información de la Empresa</h5>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                El médico será registrado automáticamente en:
                                <hr>
                                <strong>Empresa:</strong> {{ auth()->user()->empresa->razon_social }}<br>
                                <strong>Sucursal:</strong> {{ auth()->user()->sucursal->nombre }}
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
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save me-2"></i>Editar Médico
                            </button>
                            <a href="{{ route('admin.medicos.index') }}" class="btn btn-secondary mt-2">
                                <i class="fas fa-times me-2"></i>Cancelar
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Nota Importante -->
                <div class="card">
                    <div class="card-header bg-warning">
                        <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Importante</h5>
                    </div>
                    <div class="card-body">
                        <ul class="small mb-0">
                            <li>El médico recibirá un correo de bienvenida con sus credenciales.</li>
                            <li>La contraseña temporal es "password".</li>
                            <li>Se Editará automáticamente un usuario con rol de Médico.</li>
                            <li>El médico deberá cambiar su contraseña al primer inicio de sesión.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </form>

    @if($showSubespecialidadModal)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5); z-index: 1050;">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-plus-circle me-2"></i>Crear Nueva Subespecialidad
                    </h5>
                    <button type="button" class="btn-close" wire:click="closeSubespecialidadModal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="subNombreEdit" class="fw-bold">Nombre *</label>
                                <input type="text" class="form-control @error('sub_nombre') is-invalid @enderror"
                                       id="subNombreEdit" wire:model="sub_nombre"
                                       placeholder="Ej: Cirugía Refractiva, Retina...">
                                @error('sub_nombre')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="subCodigoEdit" class="fw-bold">Código</label>
                                <input type="text" class="form-control @error('sub_codigo') is-invalid @enderror"
                                       id="subCodigoEdit" wire:model="sub_codigo"
                                       placeholder="Auto-generado">
                                @error('sub_codigo')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                                <small class="text-muted">Deje vacío para generar automáticamente</small>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="subCostoEdit" class="fw-bold">Costo de Consulta *</label>
                                <input type="number" class="form-control @error('sub_costo_consulta') is-invalid @enderror"
                                       id="subCostoEdit" wire:model="sub_costo_consulta"
                                       step="0.01" min="0">
                                @error('sub_costo_consulta')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="subDuracionEdit" class="fw-bold">Duración de Consulta (minutos) *</label>
                                <input type="number" class="form-control @error('sub_duracion_consulta') is-invalid @enderror"
                                       id="subDuracionEdit" wire:model="sub_duracion_consulta"
                                       min="15" max="240">
                                @error('sub_duracion_consulta')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="subColorEdit" class="fw-bold">Color de Identificación *</label>
                                <div class="input-group">
                                    <input type="color" class="form-control form-control-color"
                                           id="subColorEdit" wire:model="sub_color"
                                           style="width: 60px; height: 38px;">
                                    <input type="text" class="form-control" wire:model="sub_color" placeholder="#3B82F6" readonly>
                                </div>
                                @error('sub_color')
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="subIconoEdit" class="fw-bold">Icono Representativo *</label>
                                <input type="text" class="form-control @error('sub_icono') is-invalid @enderror"
                                       id="subIconoEdit" wire:model="sub_icono"
                                       placeholder="fa-stethoscope">
                                @error('sub_icono')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                                <small class="text-muted">Usa clases de Font Awesome</small>
                            </div>
                        </div>
                    </div>
                    <div class="form-group mb-3">
                        <label for="subDescripcionEdit" class="fw-bold">Descripción</label>
                        <textarea class="form-control @error('sub_descripcion') is-invalid @enderror"
                                  id="subDescripcionEdit" wire:model="sub_descripcion"
                                  rows="3" placeholder="Descripción de la subespecialidad"></textarea>
                        @error('sub_descripcion')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="subRequiereCitaEdit" wire:model="sub_requiere_cita_previa">
                        <label class="form-check-label" for="subRequiereCitaEdit">Requiere cita previa</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeSubespecialidadModal">
                        <i class="fas fa-times me-1"></i>Cancelar
                    </button>
                    <button type="button" class="btn btn-primary" wire:click="storeSubespecialidad">
                        <i class="fas fa-save me-1"></i>Crear Subespecialidad
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
