<div class="card">
    <div class="card-header border-bottom">
        <h5 class="mb-0">Editar Usuario</h5>
    </div>
    <div class="card-body">
        @if (session()->has('message'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('message') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form wire:submit.prevent="update">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nombre <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                           wire:model.live="name" placeholder="Ingrese el nombre completo">
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Usuario (Username) <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('username') is-invalid @enderror"
                           wire:model="username" placeholder="Se genera automáticamente desde el nombre">
                    <small class="text-muted">El username se genera automáticamente al escribir el nombre, pero puedes editarlo manualmente</small>
                    @error('username')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                           wire:model="email" placeholder="Ingrese el correo electrónico">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Teléfono</label>
                    <input type="text" class="form-control @error('phone') is-invalid @enderror"
                           wire:model="phone" placeholder="Ingrese el número de teléfono">
                    @error('phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Contraseña</label>
                    <input type="password" class="form-control @error('password') is-invalid @enderror"
                           wire:model="password" placeholder="Ingrese la nueva contraseña (dejar en blanco para no cambiar)">
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Confirmar Contraseña</label>
                    <input type="password" class="form-control @error('password_confirmation') is-invalid @enderror"
                           wire:model="password_confirmation" placeholder="Confirme la nueva contraseña">
                    @error('password_confirmation')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Empresa</label>
                    <select class="form-select @error('empresa_id') is-invalid @enderror" wire:model="empresa_id" wire:change="loadSucursales">
                        <option value="">Seleccione una empresa</option>
                        @foreach($empresas as $empresa)
                            <option value="{{ $empresa->id }}">{{ $empresa->razon_social }}</option>
                        @endforeach
                    </select>
                    @error('empresa_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Sucursal</label>
                    <select class="form-select @error('sucursal_id') is-invalid @enderror" wire:model="sucursal_id">
                        <option value="">Seleccione una sucursal</option>
                        @foreach($sucursales as $sucursal)
                            <option value="{{ $sucursal->id }}">{{ $sucursal->nombre }}</option>
                        @endforeach
                    </select>
                    @error('sucursal_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Rol <span class="text-danger">*</span></label>
                    <select class="form-select @error('role') is-invalid @enderror" wire:model="role">
                        <option value="">Seleccione un rol</option>
                        @foreach($roles as $r)
                            <option value="{{ $r->name }}">{{ $r->name }}</option>
                        @endforeach
                    </select>
                    @error('role')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Estado</label>
                    <select class="form-select @error('status') is-invalid @enderror" wire:model="status">
                        <option value="active">Activo</option>
                        <option value="pending">Pendiente</option>
                        <option value="inactive">Inactivo</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="d-flex justify-content-between mt-4">
                <a href="{{ route('admin.users.index') }}" class="btn btn-label-secondary">
                    <i class="ri ri-arrow-left-line"></i> Volver
                </a>
                @can('edit users')
                <button type="submit" class="btn btn-primary">
                    <i class="ri ri-save-line"></i> Actualizar Usuario
                </button>
                @endcan
            </div>
        </form>
    </div>
</div>