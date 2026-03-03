<div>
    @section('title', 'Nuevo Paciente')

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-user-plus me-2"></i>Nuevo Paciente
        </h2>
        <a href="{{ route('admin.pacientes.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Volver
        </a>
    </div>

    <!-- Formulario -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-user me-2"></i>Datos Personales</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group mb-3">
                        <label for="nombres">Nombres *</label>
                        <input type="text" class="form-control @error('nombres') is-invalid @enderror"
                               id="nombres" wire:model="nombres" placeholder="Ingrese los nombres">
                        @error('nombres')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group mb-3">
                        <label for="apellidos">Apellidos *</label>
                        <input type="text" class="form-control @error('apellidos') is-invalid @enderror"
                               id="apellidos" wire:model="apellidos" placeholder="Ingrese los apellidos">
                        @error('apellidos')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group mb-3">
                        <label for="documento_identidad">Documento de Identidad *</label>
                        <input type="text" class="form-control @error('documento_identidad') is-invalid @enderror"
                               id="documento_identidad" wire:model="documento_identidad" placeholder="Ingrese el documento">
                        @error('documento_identidad')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group mb-3">
                        <label for="fecha_nacimiento">Fecha de Nacimiento *</label>
                        <input type="date" class="form-control @error('fecha_nacimiento') is-invalid @enderror"
                               id="fecha_nacimiento" wire:model.live="fecha_nacimiento">
                        @error('fecha_nacimiento')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group mb-3">
                        <label for="telefono">Teléfono</label>
                        <input type="text" class="form-control @error('telefono') is-invalid @enderror"
                               id="telefono" wire:model="telefono" placeholder="Ingrese el teléfono">
                        @error('telefono')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group mb-3">
                        <label for="email">Email</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                               id="email" wire:model="email" placeholder="Ingrese el correo electrónico">
                        @error('email')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group mb-3">
                        <label for="nickname">¿Cómo le gusta que le digan?</label>
                        <input type="text" class="form-control @error('nickname') is-invalid @enderror"
                               id="nickname" wire:model="nickname" placeholder="Ej: Juanito, Beba, etc.">
                        @error('nickname')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="col-md-8">
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

            @if(auth()->user()->hasRole('Super Administrador'))
                <div class="row">
                    <div class="col-md-6">
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
                    </div>
                    <div class="col-md-6">
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
            @endif
        </div>
    </div>

    <!-- Datos del Tutor -->
    @if($this->isMinor())
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            El paciente es menor de edad. Se requieren los datos del tutor/representante.
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-user-shield me-2"></i>Datos del Tutor</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label for="tutor_nombres">Nombres *</label>
                            <input type="text" class="form-control @error('tutor.nombres') is-invalid @enderror"
                                   id="tutor_nombres" wire:model="tutor.nombres" placeholder="Ingrese los nombres del tutor">
                            @error('tutor.nombres')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label for="tutor_apellidos">Apellidos *</label>
                            <input type="text" class="form-control @error('tutor.apellidos') is-invalid @enderror"
                                   id="tutor_apellidos" wire:model="tutor.apellidos" placeholder="Ingrese los apellidos del tutor">
                            @error('tutor.apellidos')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label for="tutor_documento_identidad">Documento de Identidad</label>
                            <input type="text" class="form-control @error('tutor.documento_identidad') is-invalid @enderror"
                                   id="tutor_documento_identidad" wire:model="tutor.documento_identidad" placeholder="Ingrese el documento del tutor">
                            @error('tutor.documento_identidad')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label for="tutor_parentesco">Parentesco *</label>
                            <select class="form-select @error('tutor.parentesco') is-invalid @enderror"
                                    id="tutor_parentesco" wire:model="tutor.parentesco">
                                <option value="">Seleccione el parentesco</option>
                                <option value="Padre">Padre</option>
                                <option value="Madre">Madre</option>
                                <option value="Abuelo/a">Abuelo/a</option>
                                <option value="Tío/a">Tío/a</option>
                                <option value="Hermano/a">Hermano/a</option>
                                <option value="Tutor Legal">Tutor Legal</option>
                                <option value="Otro">Otro</option>
                            </select>
                            @error('tutor.parentesco')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label for="tutor_edad">Edad</label>
                            <input type="number" class="form-control @error('tutor.edad') is-invalid @enderror"
                                   id="tutor_edad" wire:model="tutor.edad" placeholder="Ingrese la edad del tutor" min="0">
                            @error('tutor.edad')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label for="tutor_telefono">Teléfono</label>
                            <input type="text" class="form-control @error('tutor.telefono') is-invalid @enderror"
                                   id="tutor_telefono" wire:model="tutor.telefono" placeholder="Ingrese el teléfono del tutor">
                            @error('tutor.telefono')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Footer -->
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('admin.pacientes.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times me-2"></i>Cancelar
                </a>
                <button type="button" class="btn btn-primary" wire:click="store" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="store">
                        <i class="fas fa-save me-2"></i>Guardar Paciente
                    </span>
                    <span wire:loading wire:target="store">
                        <i class="fas fa-spinner fa-spin me-2"></i>Guardando...
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>
