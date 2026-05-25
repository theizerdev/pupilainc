<div>
 @section('title', 'Editar Médico')

    @push('styles')
    <style>
        /* Hero Section */
        .medico-edit-hero {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: #fff;
            border-radius: 0.75rem;
            padding: 1.4rem 1.6rem;
        }
        .medico-edit-hero h2 {
            color: #fff;
            margin: 0;
        }
        .medico-edit-hero p {
            opacity: 0.9;
            margin: 0;
        }

        /* Form Cards - Clean Style */
        .form-card {
            border: 1px solid rgba(0,0,0,.08);
            border-radius: 0.75rem;
            box-shadow: 0 2px 8px rgba(0,0,0,.04);
            transition: all 0.2s;
            background: #fff;
        }
        .form-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,.08);
        }
        .form-card .card-header {
            background: #f8f9fa;
            border-bottom: 1px solid rgba(0,0,0,.06);
            padding: 1rem 1.25rem;
        }
        .form-card .card-header h5 {
            font-size: 1rem;
            font-weight: 600;
            margin: 0;
            color: #2d3748;
        }
        .form-card .card-body {
            padding: 1.25rem;
        }

        /* Form Inputs */
        .form-control, .form-select {
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            padding: 0.625rem 0.875rem;
            font-size: 0.95rem;
            transition: all 0.2s;
        }
        .form-control:focus, .form-select:focus {
            border-color: #f59e0b;
            box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1);
        }
        label {
            font-weight: 500;
            color: #4a5568;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }

        /* Checkbox Styling */
        .subespecialidad-item {
            padding: 0.75rem;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            margin-bottom: 0.5rem;
            transition: all 0.2s;
        }
        .subespecialidad-item:hover {
            background: #f7fafc;
            border-color: #cbd5e0;
        }
        .subespecialidad-data {
            background: #f8f9fa;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-top: 0.75rem;
        }

        /* Schedule Table */
        .schedule-table th {
            background: #f8f9fa;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            color: #718096;
        }
        .schedule-table td {
            vertical-align: middle;
        }

        /* Action Buttons */
        .btn-action {
            border-radius: 0.5rem;
            padding: 0.625rem 1.25rem;
            font-weight: 500;
            transition: all 0.2s;
        }
        .btn-action:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0,0,0,.15);
        }

        /* Image Preview */
        .image-preview {
            border: 2px dashed #e2e8f0;
            border-radius: 0.5rem;
            padding: 1rem;
            text-align: center;
            background: #f8f9fa;
        }
        .image-preview img {
            max-height: 80px;
            max-width: 100%;
        }
    </style>
    @endpush

    <!-- Hero Section -->
    <div class="medico-edit-hero mb-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <h2 class="fw-semibold"><i class="ri ri-user-settings-line me-2"></i>Editar Médico</h2>
            <p class="mt-1">Modifique la información del médico seleccionado</p>
        </div>
        <a href="{{ route('admin.medicos.index') }}" class="btn btn-light btn-sm">
            <i class="ri ri-arrow-left-line me-1"></i>Volver al Listado
        </a>
    </div>
<form wire:submit.prevent="save">
        <div class="row g-4">
                <!-- Información Personal -->
                <div class="form-card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="ri ri-user-line me-2"></i>Información Personal</h5>
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
                                    <label for="telefono" class="fw-bold">
                                        <i class="ri ri-phone-line me-1"></i>Teléfono
                                    </label>
                                    <input type="tel"
                                        class="form-control @error('telefono') is-invalid @enderror"
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
                                        <i class="ri ri-information-line me-1"></i>Solo se permiten números y caracteres de teléfono
                                    </small>
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
                <div class="form-card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="ri ri-key-2-line me-2"></i>Credenciales de Acceso</h5>
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
                                        Para cambiar la contraseña, use la opción "Restablecer Contraseña" en el listado.
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
                <div class="form-card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="ri ri-stethoscope-line me-2"></i>Información Profesional</h5>
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
                <div class="form-card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="ri ri-medical-kit-line me-2"></i>Especialidad y Subespecialidades</h5>
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
                                        <label class="fw-bold mb-0">Subespecialidades (Opcional)</label>
                                        <button type="button"
                                                class="btn btn-outline-primary btn-sm"
                                                style="border-radius: 0.5rem;"
                                                wire:click="openSubespecialidadModal">
                                            <i class="ri ri-add-line me-1"></i>Nueva Subespecialidad
                                        </button>
                                    </div>
                                        <div class="border rounded p-3" style="max-height: 300px; overflow-y: auto;">
                                            @forelse($subespecialidades as $subespecialidad)
                                                <div class="subespecialidad-item">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox"
                                                               id="subespecialidad_{{ $subespecialidad->id }}"
                                                               value="{{ $subespecialidad->id }}"
                                                               wire:model.live="subespecialidades_seleccionadas">
                                                        <label class="form-check-label fw-medium" for="subespecialidad_{{ $subespecialidad->id }}">
                                                            {{ $subespecialidad->nombre }}
                                                        </label>
                                                    </div>

                                                    @if(in_array($subespecialidad->id, $subespecialidades_seleccionadas))
                                                        <div class="subespecialidad-data">
                                                            <div class="row g-3">
                                                                <div class="col-md-4">
                                                                    <label class="small fw-bold">Experiencia (años)</label>
                                                                    <input type="number" class="form-control form-control-sm"
                                                                           wire:model="subespecialidades_data.{{ $subespecialidad->id }}.experiencia_anios"
                                                                           min="0" max="50">
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <label class="small fw-bold">Nivel</label>
                                                                    <select class="form-select form-select-sm"
                                                                            wire:model="subespecialidades_data.{{ $subespecialidad->id }}.nivel_experiencia">
                                                                        <option value="Básico">Básico</option>
                                                                        <option value="Intermedio">Intermedio</option>
                                                                        <option value="Avanzado">Avanzado</option>
                                                                    </select>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <label class="small fw-bold">Tarifa (opcional)</label>
                                                                    <input type="number" class="form-control form-control-sm"
                                                                           wire:model="subespecialidades_data.{{ $subespecialidad->id }}.tarifa_consulta"
                                                                           step="0.01" min="0">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                            @empty
                                                <p class="text-muted text-center py-3 mb-0">No hay subespecialidades disponibles para esta especialidad.</p>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                <!-- Horarios de Atención -->
                <div class="form-card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="ri ri-time-line me-2"></i>Horario de Atención</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm schedule-table mb-0">
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
            <!-- Panel Derecho - Firma, Sello y Acciones -->
            <div class="col-md-12">
                <!-- Firma y Sello Digital -->
                <div class="form-card mb-4" style="border-left: 3px solid #f59e0b;">
                    <div class="card-header" style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);">
                        <h5 class="mb-0"><i class="ri ri-quill-pen-line me-2" style="color: #f59e0b;"></i>Firma y Sello Digital</h5>
                    </div>
                    <div class="card-body">

                        {{-- FIRMA --}}
                        <div class="mb-3">
                            <label class="fw-bold"><i class="ri ri-quill-pen-line me-1"></i>Firma Digital</label>
                            @if($firma_digital_actual)
                                <div class="image-preview mb-2">
                                    <img src="{{ Storage::url($firma_digital_actual) }}" alt="Firma">
                                </div>
                                <button type="button" class="btn btn-outline-danger btn-sm w-100 mb-2"
                                        wire:click="eliminarFirma"
                                        wire:confirm="¿Eliminar la firma digital?"
                                        style="border-radius: 0.5rem;">
                                    <i class="ri ri-delete-bin-line me-1"></i>Eliminar firma
                                </button>
                            @endif
                            <input type="file" class="form-control form-control-sm @error('nueva_firma') is-invalid @enderror"
                                   wire:model="nueva_firma" accept="image/*">
                            <small class="text-muted">PNG con fondo transparente recomendado. Máx 2MB.</small>
                            @error('nueva_firma')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>

                        {{-- SELLO --}}
                        <div class="mb-3">
                            <label class="fw-bold"><i class="ri ri-stamp-line me-1"></i>Sello Digital</label>
                            @if($sello_digital_actual)
                                <div class="image-preview mb-2">
                                    <img src="{{ Storage::url($sello_digital_actual) }}" alt="Sello">
                                </div>
                                <button type="button" class="btn btn-outline-danger btn-sm w-100 mb-2"
                                        wire:click="eliminarSello"
                                        wire:confirm="¿Eliminar el sello digital?"
                                        style="border-radius: 0.5rem;">
                                    <i class="ri ri-delete-bin-line me-1"></i>Eliminar sello
                                </button>
                            @endif
                            <input type="file" class="form-control form-control-sm @error('nuevo_sello') is-invalid @enderror"
                                   wire:model="nuevo_sello" accept="image/*">
                            <small class="text-muted">PNG con fondo transparente recomendado. Máx 2MB.</small>
                            @error('nuevo_sello')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>

                        <hr>

                        {{-- CONFIGURACIÓN --}}
                        <label class="fw-bold"><i class="ri ri-settings-3-line me-1"></i>Configuración en Informes</label>

                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" id="mostrar_firma" wire:model="config_firma.mostrar_firma">
                            <label class="form-check-label" for="mostrar_firma">Mostrar firma</label>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="mostrar_sello" wire:model="config_firma.mostrar_sello">
                            <label class="form-check-label" for="mostrar_sello">Mostrar sello</label>
                        </div>

                        <div class="mt-3">
                            <label class="small fw-bold">Posición</label>
                            <select class="form-select form-select-sm" wire:model="config_firma.posicion">
                                <option value="izquierda">Izquierda</option>
                                <option value="centro">Centro</option>
                                <option value="derecha">Derecha</option>
                            </select>
                        </div>

                        <div class="row mt-2">
                            <div class="col-6">
                                <label class="small fw-bold">Ancho firma (mm)</label>
                                <input type="number" class="form-control form-control-sm" wire:model="config_firma.ancho_firma" min="20" max="100">
                            </div>
                            <div class="col-6">
                                <label class="small fw-bold">Ancho sello (mm)</label>
                                <input type="number" class="form-control form-control-sm" wire:model="config_firma.ancho_sello" min="15" max="80">
                            </div>
                        </div>

                        <div class="mt-3">
                            <label class="small fw-bold">Mostrar en:</label>
                            @foreach(['informe' => 'Informe Médico', 'recipe' => 'Recipe Médico', 'orden_estudios' => 'Orden de Estudios'] as $key => $label)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox"
                                           id="mostrar_en_{{ $key }}"
                                           value="{{ $key }}"
                                           wire:model="config_firma.mostrar_en">
                                    <label class="form-check-label small" for="mostrar_en_{{ $key }}">{{ $label }}</label>
                                </div>
                            @endforeach
                        </div>

                    </div>
                </div>
                <!-- Empresa y Sucursal -->
                @if(auth()->user()->hasRole('Super Administrador'))
                    <div class="form-card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="ri ri-building-line me-2"></i>Empresa y Sucursal</h5>
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
                    <div class="form-card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="ri ri-building-line me-2"></i>Información de la Empresa</h5>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info" style="border-radius: 0.5rem;">
                                <i class="ri ri-information-line me-2"></i>
                                El médico está registrado en:
                                <hr class="my-2">
                                <strong>Empresa:</strong> {{ auth()->user()->empresa->razon_social }}<br>
                                <strong>Sucursal:</strong> {{ auth()->user()->sucursal->nombre }}
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Botones de Acción -->
                <div class="form-card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="ri ri-save-line me-2"></i>Acciones</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary" >
                                <i class="ri ri-check-line me-2"></i>Guardar Cambios
                            </button>
                            <a href="{{ route('admin.medicos.index') }}" class="btn btn-outline-secondary btn-action">
                                <i class="ri ri-close-line me-2"></i>Cancelar
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Nota Importante -->
                <div class="form-card" style="border-left: 3px solid #f59e0b;">
                    <div class="card-header" style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);">
                        <h5 class="mb-0"><i class="ri ri-alert-line me-2" style="color: #f59e0b;"></i>Importante</h5>
                    </div>
                    <div class="card-body">
                        <ul class="small mb-0" style="padding-left: 1.25rem;">
                            <li class="mb-2">Los cambios se guardarán inmediatamente.</li>
                            <li class="mb-2">Para cambiar la contraseña, use la opción "Restablecer Contraseña" en el listado.</li>
                            <li class="mb-2">El rol de Médico se mantendrá sin cambios.</li>
                            <li>Los horarios actualizados afectarán las citas futuras.</li>
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
