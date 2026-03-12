<div>
    @section('title', 'Nuevo Paciente')

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0">
                <i class="fas fa-user-plus me-2"></i>Nuevo Paciente
            </h2>
            <p class="text-muted mb-0">Registro de nuevos pacientes en el sistema</p>
        </div>
        <a href="{{ route('admin.pacientes.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Volver al Listado
        </a>
    </div>

    <!-- Progress Steps -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row text-center">
                <div class="col-md-6">
                    <div class="step-item active">
                        <div class="step-icon bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width: 50px; height: 50px;">
                            <i class="fas fa-user fa-lg"></i>
                        </div>
                        <h6 class="mb-0">Datos Personales</h6>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="step-item {{ $showTutorSection ? 'active' : '' }}">
                        <div class="step-icon {{ $showTutorSection ? 'bg-success' : 'bg-secondary' }} text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width: 50px; height: 50px;">
                            <i class="fas fa-user-shield fa-lg"></i>
                        </div>
                        <h6 class="mb-0">Datos del Tutor</h6>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulario Principal -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-user me-2"></i>Datos Personales</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group mb-3">
                        <label for="nombres" class="fw-bold">
                            <i class="fas fa-signature me-1"></i>Nombres *
                        </label>
                        <input type="text" 
                               class="form-control form-control-lg @error('nombres') is-invalid @enderror"
                               id="nombres" 
                               wire:model.blur="nombres" 
                               placeholder="Ej: Juan Carlos"
                               autocomplete="given-name">
                        @error('nombres')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group mb-3">
                        <label for="apellidos" class="fw-bold">
                            <i class="fas fa-user-tag me-1"></i>Apellidos *
                        </label>
                        <input type="text" 
                               class="form-control form-control-lg @error('apellidos') is-invalid @enderror"
                               id="apellidos" 
                               wire:model.blur="apellidos" 
                               placeholder="Ej: Pérez González"
                               autocomplete="family-name">
                        @error('apellidos')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group mb-3">
                        <label for="documento_identidad" class="fw-bold">
                            <i class="fas fa-id-card me-1"></i>Documento de Identidad *
                        </label>
                        <input type="text" 
                               class="form-control form-control-lg @error('documento_identidad') is-invalid @enderror"
                               id="documento_identidad" 
                               wire:model.blur="documento_identidad" 
                               wire:change="formatDocumento"
                               placeholder="Ej: V-12345678"
                               autocomplete="off">
                        @error('documento_identidad')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group mb-3">
                        <label for="fecha_nacimiento" class="fw-bold">
                            <i class="fas fa-calendar-alt me-1"></i>Fecha de Nacimiento *
                        </label>
                        <input type="date" 
                               class="form-control form-control-lg @error('fecha_nacimiento') is-invalid @enderror"
                               id="fecha_nacimiento" 
                               wire:model.live="fecha_nacimiento"
                               max="{{ date('Y-m-d') }}">
                        @error('fecha_nacimiento')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                        @if($edad)
                            <small class="text-success mt-1 d-block">
                                <i class="fas fa-birthday-cake me-1"></i>Edad calculada: <strong>{{ $edad }}</strong>
                            </small>
                        @endif
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group mb-3">
                        <label for="telefono" class="fw-bold">
                            <i class="fas fa-phone me-1"></i>Teléfono
                        </label>
                        <input type="tel" 
                               class="form-control form-control-lg @error('telefono') is-invalid @enderror"
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
                <div class="col-md-4">
                    <div class="form-group mb-3">
                        <label for="email" class="fw-bold">
                            <i class="fas fa-envelope me-1"></i>Email
                        </label>
                        <input type="email" 
                               class="form-control form-control-lg @error('email') is-invalid @enderror"
                               id="email" 
                               wire:model.blur="email" 
                               placeholder="ejemplo@correo.com"
                               autocomplete="email">
                        @error('email')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group mb-3">
                        <label for="nickname" class="fw-bold">
                            <i class="fas fa-comment-dots me-1"></i>¿Cómo le gusta que le digan?
                        </label>
                        <input type="text" 
                               class="form-control form-control-lg @error('nickname') is-invalid @enderror"
                               id="nickname" 
                               wire:model.blur="nickname" 
                               placeholder="Ej: Juanito, Beba, Mi amor..."
                               maxlength="100">
                        @error('nickname')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                        <small class="text-muted">Nombre cariñoso o preferido</small>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="form-group mb-3">
                        <label for="direccion" class="fw-bold">
                            <i class="fas fa-map-marker-alt me-1"></i>Dirección
                        </label>
                        <textarea class="form-control form-control-lg @error('direccion') is-invalid @enderror"
                                  id="direccion" 
                                  wire:model.blur="direccion" 
                                  rows="2" 
                                  placeholder="Dirección completa de habitación"
                                  autocomplete="street-address"></textarea>
                        @error('direccion')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            @if(auth()->user()->hasRole('Super Administrador'))
                <hr class="my-4">
                <h6 class="fw-bold mb-3">
                    <i class="fas fa-building me-2"></i>Asignación Organizacional
                </h6>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="empresa_id" class="fw-bold">Empresa *</label>
                            <select class="form-select form-select-lg @error('empresa_id') is-invalid @enderror"
                                    id="empresa_id" 
                                    wire:model.live="empresa_id">
                                <option value="">Seleccione una empresa</option>
                                @foreach($empresas as $empresa)
                                    <option value="{{ $empresa->id }}">{{ $empresa->razon_social }}</option>
                                @endforeach
                            </select>
                            @error('empresa_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="sucursal_id" class="fw-bold">Sucursal *</label>
                            <select class="form-select form-select-lg @error('sucursal_id') is-invalid @enderror"
                                    id="sucursal_id" 
                                    wire:model="sucursal_id" 
                                    @if(!$empresa_id) disabled @endif>
                                <option value="">
                                    @if($empresa_id)
                                        Seleccione una sucursal
                                    @else
                                        Primero seleccione empresa
                                    @endif
                                </option>
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
        </div>
    </div>

    <!-- Datos del Tutor (Sección Condicional) -->
    @if($showTutorSection)
        <div class="card mb-4 shadow-sm border-success">
            <div class="card-header bg-success text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-user-shield me-2"></i>Datos del Tutor/Representante
                    </h5>
                    <span class="badge bg-warning">
                        <i class="fas fa-exclamation-triangle me-1"></i>Obligatorio para menores
                    </span>
                </div>
            </div>
            <div class="card-body">
                <div class="alert alert-info d-flex align-items-center mb-4">
                    <i class="fas fa-info-circle fa-2x me-3"></i>
                    <div>
                        El paciente es menor de edad (<strong>{{ $edad }}</strong>). 
                        Es obligatorio registrar los datos de su representante legal.
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label for="tutor_nombres" class="fw-bold">
                                <i class="fas fa-signature me-1"></i>Nombres del Tutor *
                            </label>
                            <input type="text" 
                                   class="form-control form-control-lg @error('tutor.nombres') is-invalid @enderror"
                                   id="tutor_nombres" 
                                   wire:model.blur="tutor.nombres" 
                                   placeholder="Nombres completos del tutor">
                            @error('tutor.nombres')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label for="tutor_apellidos" class="fw-bold">
                                <i class="fas fa-user-tag me-1"></i>Apellidos del Tutor *
                            </label>
                            <input type="text" 
                                   class="form-control form-control-lg @error('tutor.apellidos') is-invalid @enderror"
                                   id="tutor_apellidos" 
                                   wire:model.blur="tutor.apellidos" 
                                   placeholder="Apellidos completos del tutor">
                            @error('tutor.apellidos')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label for="tutor_documento_identidad" class="fw-bold">
                                <i class="fas fa-id-card me-1"></i>Documento del Tutor
                            </label>
                            <input type="text" 
                                   class="form-control form-control-lg @error('tutor.documento_identidad') is-invalid @enderror"
                                   id="tutor_documento_identidad" 
                                   wire:model.blur="tutor.documento_identidad" 
                                   placeholder="Documento de identidad">
                            @error('tutor.documento_identidad')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label for="tutor_parentesco" class="fw-bold">
                                <i class="fas fa-users me-1"></i>Parentesco *
                            </label>
                            <select class="form-select form-select-lg @error('tutor.parentesco') is-invalid @enderror"
                                    id="tutor_parentesco" 
                                    wire:model.blur="tutor.parentesco">
                                <option value="">Seleccione el parentesco</option>
                                <option value="Padre">Padre</option>
                                <option value="Madre">Madre</option>
                                <option value="Abuelo/a">Abuelo/a</option>
                                <option value="Tío/a">Tío/a</option>
                                <option value="Hermano/a">Hermano/a</option>
                                <option value="Tutor Legal">Tutor Legal</option>
                                <option value="Representante">Representante</option>
                                <option value="Otro">Otro</option>
                            </select>
                            @error('tutor.parentesco')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label for="tutor_edad" class="fw-bold">
                                <i class="fas fa-birthday-cake me-1"></i>Edad del Tutor
                            </label>
                            <input type="number" 
                                   class="form-control form-control-lg @error('tutor.edad') is-invalid @enderror"
                                   id="tutor_edad" 
                                   wire:model.blur="tutor.edad" 
                                   placeholder="Edad en años" 
                                   min="1" 
                                   max="120">
                            @error('tutor.edad')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label for="tutor_telefono" class="fw-bold">
                                <i class="fas fa-phone me-1"></i>Teléfono del Tutor *
                            </label>
                            <input type="tel" 
                                   class="form-control form-control-lg @error('tutor.telefono') is-invalid @enderror"
                                   id="tutor_telefono" 
                                   wire:model.blur="tutor.telefono" 
                                   placeholder="Teléfono de contacto"
                                   autocomplete="tel"
                                   pattern="[\d\s\-\+\(\)]+"
                                   title="Solo números y caracteres válidos (+, -, espacios, paréntesis)"
                                   onkeypress="return /[0-9+\-()\s]/.test(String.fromCharCode(event.keyCode))">
                            @error('tutor.telefono')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            <small class="text-muted mt-1 d-block">
                                <i class="fas fa-info-circle me-1"></i>Solo se permiten números y caracteres de teléfono
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Footer con Botones -->
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        Los campos marcados con <span class="text-danger">*</span> son obligatorios
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.pacientes.index') }}" 
                           class="btn btn-outline-secondary btn-lg">
                            <i class="fas fa-times me-2"></i>Cancelar
                        </a>
                        <button type="button" 
                                class="btn btn-primary btn-lg" 
                                wire:click="store" 
                                wire:loading.attr="disabled"
                                wire:target="store">
                            <span wire:loading.remove wire:target="store">
                                <i class="fas fa-save me-2"></i>Guardar Paciente
                            </span>
                            <span wire:loading wire:target="store">
                                <i class="fas fa-spinner fa-spin me-2"></i>Procesando...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .step-item {
        position: relative;
    }
    .step-item:not(:last-child)::after {
        content: '';
        position: absolute;
        top: 25px;
        left: 50%;
        width: calc(100% - 50px);
        height: 3px;
        background-color: #e9ecef;
        z-index: -1;
    }
    .step-item.active:not(:last-child)::after {
        background-color: #0d6efd;
    }
    .form-control:focus, .form-select:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }
    .form-control-lg {
        font-size: 1rem;
    }
    label {
        font-size: 0.9rem;
        color: #495057;
    }
    small.text-muted {
        font-size: 0.8rem;
    }
</style>
@endpush