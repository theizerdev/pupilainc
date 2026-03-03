<div>
    @section('title', 'Editar Paciente')
    
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-user-injured me-2"></i>Editar Paciente
        </h2>
        <a href="{{ route('admin.pacientes.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Volver
        </a>
    </div>

    <!-- Formulario -->
    <form wire:submit.prevent="update">
        <div class="row">
            <!-- Datos Personales -->
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-user me-2"></i>Datos Personales</h5>
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
                                    <label for="telefono">Teléfono</label>
                                    <input type="text" class="form-control @error('telefono') is-invalid @enderror" 
                                           id="telefono" wire:model="telefono" placeholder="Ingrese el teléfono">
                                    @if($pais)
                                        <small class="form-text text-muted">
                                            Formato: {{ $pais->formato_telefono ?? 'Sin formato específico' }}
                                        </small>
                                    @endif
                                    @error('telefono')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="email">Correo Electrónico</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                           id="email" wire:model="email" placeholder="correo@ejemplo.com">
                                    @error('email')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="fecha_nacimiento">Fecha de Nacimiento *</label>
                                    <input type="date" class="form-control @error('fecha_nacimiento') is-invalid @enderror" 
                                           id="fecha_nacimiento" wire:model.live="fecha_nacimiento">
                                    @error('fecha_nacimiento')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="nickname">¿Cómo le gusta que le digan?</label>
                                    <input type="text" class="form-control @error('nickname') is-invalid @enderror" 
                                           id="nickname" wire:model="nickname" placeholder="Ej: Juanito, Beba, etc.">
                                    @error('nickname')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="status">Estado</label>
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" 
                                               id="status" wire:model="status" value="1">
                                        <label class="form-check-label" for="status">
                                            {{ $status ? 'Activo' : 'Inactivo' }}
                                        </label>
                                    </div>
                                    @error('status')
                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                    @enderror
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

                <!-- Datos del Tutor -->
                @if($this->isMinor())
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-user-shield me-2"></i>Datos del Tutor</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="tutor_nombres">Nombres del Tutor *</label>
                                    <input type="text" class="form-control @error('tutor.nombres') is-invalid @enderror" 
                                           id="tutor_nombres" wire:model="tutor.nombres" placeholder="Ingrese los nombres del tutor">
                                    @error('tutor.nombres')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="tutor_apellidos">Apellidos del Tutor *</label>
                                    <input type="text" class="form-control @error('tutor.apellidos') is-invalid @enderror" 
                                           id="tutor_apellidos" wire:model="tutor.apellidos" placeholder="Ingrese los apellidos del tutor">
                                    @error('tutor.apellidos')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="tutor_documento_identidad">Documento de Identidad</label>
                                    <input type="text" class="form-control @error('tutor.documento_identidad') is-invalid @enderror" 
                                           id="tutor_documento_identidad" wire:model="tutor.documento_identidad" placeholder="Documento del tutor">
                                    @error('tutor.documento_identidad')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="tutor_parentesco">Parentesco *</label>
                                    <input type="text" class="form-control @error('tutor.parentesco') is-invalid @enderror" 
                                           id="tutor_parentesco" wire:model="tutor.parentesco" placeholder="Ej: Padre, Madre, Abuelo">
                                    @error('tutor.parentesco')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="tutor_edad">Edad</label>
                                    <input type="number" class="form-control @error('tutor.edad') is-invalid @enderror" 
                                           id="tutor_edad" wire:model="tutor.edad" min="1" placeholder="Edad del tutor">
                                    @error('tutor.edad')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="tutor_telefono">Teléfono</label>
                                    <input type="text" class="form-control @error('tutor.telefono') is-invalid @enderror" 
                                           id="tutor_telefono" wire:model="tutor.telefono" placeholder="Teléfono del tutor">
                                    @error('tutor.telefono')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Panel Derecho -->
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
                @endif

                <!-- Acciones -->
                <div class="card">
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary w-100 mb-2" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="update">
                                <i class="fas fa-save me-2"></i>Actualizar
                            </span>
                            <span wire:loading wire:target="update">
                                <i class="fas fa-spinner fa-spin me-2"></i>Actualizando...
                            </span>
                        </button>
                        <a href="{{ route('admin.pacientes.index') }}" class="btn btn-secondary w-100">
                            <i class="fas fa-times me-2"></i>Cancelar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>