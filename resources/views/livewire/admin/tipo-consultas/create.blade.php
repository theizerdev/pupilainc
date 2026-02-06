<div>
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0 text-gray-800">Crear Tipo de Consulta Médica</h1>
                <p class="text-muted">Complete el formulario para registrar un nuevo tipo de consulta</p>
            </div>
            <div>
                <a href="{{ route('admin.tipo-consultas.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
        </div>

        <!-- Form -->
        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Información del Tipo de Consulta</h6>
                    </div>
                    <div class="card-body">
                        <form wire:submit.prevent="save">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="nombre">Nombre del Tipo de Consulta *</label>
                                        <input type="text" 
                                               class="form-control @error('nombre') is-invalid @enderror" 
                                               id="nombre" 
                                               wire:model="nombre" 
                                               placeholder="Ej: Consulta General, Revisión, Emergencia, etc.">
                                        @error('nombre')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="codigo">Código</label>
                                        <input type="text" 
                                               class="form-control @error('codigo') is-invalid @enderror" 
                                               id="codigo" 
                                               wire:model="codigo" 
                                               placeholder="Se generará automáticamente si se deja vacío">
                                        @error('codigo')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                        <small class="form-text text-muted">Si se deja vacío, se generará automáticamente</small>
                                    </div>
                                </div>
                            </div>

                            @if(auth()->user()->hasRole('Super Administrador'))
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="empresa_id">Empresa *</label>
                                        <select class="form-control @error('empresa_id') is-invalid @enderror" 
                                                id="empresa_id" 
                                                wire:model="empresa_id">
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
                                    <div class="form-group">
                                        <label for="sucursal_id">Sucursal *</label>
                                        <select class="form-control @error('sucursal_id') is-invalid @enderror" 
                                                id="sucursal_id" 
                                                wire:model="sucursal_id" 
                                                @if(!$empresa_id) disabled @endif>
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
                            <!-- Información de empresa y sucursal para usuarios normales -->
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> Este tipo de consulta se registrará automáticamente para:
                                <ul class="mb-0 mt-2">
                                    <li><strong>Empresa:</strong> {{ auth()->user()->empresa->razon_social ?? 'No definida' }}</li>
                                    <li><strong>Sucursal:</strong> {{ auth()->user()->sucursal->nombre ?? 'No definida' }}</li>
                                </ul>
                            </div>
                            @endif

                          
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="color">Color de Identificación *</label>
                                        <div class="input-group">
                                            <input type="color" 
                                                   class="form-control @error('color') is-invalid @enderror" 
                                                   id="color" 
                                                   wire:model="color">
                                            <input type="text" 
                                                   class="form-control" 
                                                   wire:model="color" 
                                                   placeholder="#3B82F6">
                                        </div>
                                        @error('color')
                                            <span class="invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="icono">Icono *</label>
                                        <select class="form-control @error('icono') is-invalid @enderror" 
                                                id="icono" 
                                                wire:model="icono">
                                            @foreach($iconos as $icon => $nombre)
                                                <option value="{{ $icon }}">
                                                    <i class="fas {{ $icon }}"></i> {{ $nombre }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('icono')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                        <small class="form-text text-muted">
                                            <i class="fas {{ $icono }}"></i> Vista previa del icono
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="descripcion">Descripción</label>
                                <textarea class="form-control @error('descripcion') is-invalid @enderror" 
                                          id="descripcion" 
                                          wire:model="descripcion" 
                                          rows="3" 
                                          placeholder="Descripción detallada del tipo de consulta y sus características..."></textarea>
                                @error('descripcion')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" 
                                           class="custom-control-input" 
                                           id="requiere_cita_previa" 
                                           wire:model="requiere_cita_previa">
                                    <label class="custom-control-label" for="requiere_cita_previa">
                                        Requiere cita previa
                                    </label>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" 
                                           class="custom-control-input" 
                                           id="status" 
                                           wire:model="status">
                                    <label class="custom-control-label" for="status">
                                        Activo
                                    </label>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="{{ route('admin.tipo-consultas.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Guardar Tipo de Consulta
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Preview Card -->
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Vista Previa</h6>
                    </div>
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <span class="badge" style="background-color: {{ $color }}; color: white; font-size: 1.2em;">
                                <i class="fas {{ $icono }}"></i> {{ $codigo ?: 'TDC001' }}
                            </span>
                        </div>
                        <h5>{{ $nombre ?: 'Nombre de Tipo de Consulta' }}</h5>
                        <p class="text-muted">{{ $descripcion ?: 'Descripción del tipo de consulta' }}</p>
                        
                        <hr>
                        
                        <div class="row text-left">
                            <div class="col-6">
                                <strong>Costo:</strong>
                            </div>
                            <div class="col-6 text-right">
                                ${{ number_format($costo_consulta, 2) }}
                            </div>
                        </div>
                        
                        <div class="row text-left">
                            <div class="col-6">
                                <strong>Duración:</strong>
                            </div>
                            <div class="col-6 text-right">
                                {{ $duracion_consulta }} min
                            </div>
                        </div>
                        
                        <div class="row text-left">
                            <div class="col-6">
                                <strong>Cita previa:</strong>
                            </div>
                            <div class="col-6 text-right">
                                {{ $requiere_cita_previa ? 'Sí' : 'No' }}
                            </div>
                        </div>
                        
                        <div class="row text-left">
                            <div class="col-6">
                                <strong>Estado:</strong>
                            </div>
                            <div class="col-6 text-right">
                                <span class="badge badge-{{ $status ? 'success' : 'secondary' }}">
                                    {{ $status ? 'Activo' : 'Inactivo' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats -->
                @if($empresa_id && $sucursal_id)
                <div class="card shadow mt-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Ubicación</h6>
                    </div>
                    <div class="card-body">
                        @foreach($empresas as $empresa)
                            @if($empresa->id == $empresa_id)
                                <p><strong>Empresa:</strong> {{ $empresa->razon_social }}</p>
                                @break
                            @endif
                        @endforeach
                        
                        @foreach($sucursales as $sucursal)
                            @if($sucursal->id == $sucursal_id)
                                <p><strong>Sucursal:</strong> {{ $sucursal->nombre }}</p>
                                @break
                            @endif
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>