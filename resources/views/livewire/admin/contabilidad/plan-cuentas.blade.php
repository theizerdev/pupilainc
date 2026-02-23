<div>
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Plan de Cuentas Contables</h5>
                        <button wire:click="create" class="btn btn-primary">
                            <i class="ri-add-line me-1"></i> Nueva Cuenta
                        </button>
                    </div>

                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <input type="text" wire:model.live="search" class="form-control" placeholder="Buscar por código o nombre...">
                            </div>
                            <div class="col-md-3">
                                <select wire:model.live="tipo" class="form-select">
                                    <option value="">Todos los tipos</option>
                                    <option value="activo">Activo</option>
                                    <option value="pasivo">Pasivo</option>
                                    <option value="patrimonio">Patrimonio</option>
                                    <option value="ingreso">Ingreso</option>
                                    <option value="egreso">Egreso</option>
                                    <option value="costo">Costo</option>
                                </select>
                            </div>
                        </div>

                        @if(session()->has('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Código</th>
                                        <th>Nombre</th>
                                        <th>Tipo</th>
                                        <th>Naturaleza</th>
                                        <th>Nivel</th>
                                        <th>Movimientos</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($cuentas as $cuenta)
                                        <tr>
                                            <td><strong>{{ $cuenta->codigo }}</strong></td>
                                            <td>{{ $cuenta->nombre }}</td>
                                            <td>
                                                <span class="badge bg-label-{{ $cuenta->tipo === 'activo' ? 'success' : ($cuenta->tipo === 'pasivo' ? 'danger' : ($cuenta->tipo === 'patrimonio' ? 'info' : ($cuenta->tipo === 'ingreso' ? 'primary' : 'warning'))) }}">
                                                    {{ ucfirst($cuenta->tipo) }}
                                                </span>
                                            </td>
                                            <td>{{ ucfirst($cuenta->naturaleza) }}</td>
                                            <td><span class="badge bg-label-secondary">{{ $cuenta->nivel }}</span></td>
                                            <td>
                                                @if($cuenta->acepta_movimientos)
                                                    <i class="ri-check-line text-success"></i>
                                                @else
                                                    <i class="ri-close-line text-danger"></i>
                                                @endif
                                            </td>
                                            <td>
                                                @if($cuenta->activo)
                                                    <span class="badge bg-label-success">Activo</span>
                                                @else
                                                    <span class="badge bg-label-secondary">Inactivo</span>
                                                @endif
                                            </td>
                                            <td>
                                                <button wire:click="edit({{ $cuenta->id }})" class="btn btn-sm btn-icon btn-text-secondary rounded-pill">
                                                    <i class="ri-edit-box-line ri-20px"></i>
                                                </button>
                                                <button wire:click="delete({{ $cuenta->id }})" wire:confirm="¿Está seguro de eliminar esta cuenta?" class="btn btn-sm btn-icon btn-text-secondary rounded-pill">
                                                    <i class="ri-delete-bin-7-line ri-20px"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center">No hay cuentas registradas</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            {{ $cuentas->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    @if($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $editingId ? 'Editar' : 'Nueva' }} Cuenta Contable</h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Código *</label>
                                <input type="text" wire:model="codigo" class="form-control @error('codigo') is-invalid @enderror">
                                @error('codigo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nivel *</label>
                                <input type="number" wire:model="nivel" class="form-control @error('nivel') is-invalid @enderror" min="1" max="10">
                                @error('nivel') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label">Nombre *</label>
                                <input type="text" wire:model="nombre" class="form-control @error('nombre') is-invalid @enderror">
                                @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tipo *</label>
                                <select wire:model="tipo_cuenta" class="form-select @error('tipo_cuenta') is-invalid @enderror">
                                    <option value="">Seleccione...</option>
                                    <option value="activo">Activo</option>
                                    <option value="pasivo">Pasivo</option>
                                    <option value="patrimonio">Patrimonio</option>
                                    <option value="ingreso">Ingreso</option>
                                    <option value="egreso">Egreso</option>
                                    <option value="costo">Costo</option>
                                </select>
                                @error('tipo_cuenta') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Naturaleza *</label>
                                <select wire:model="naturaleza" class="form-select @error('naturaleza') is-invalid @enderror">
                                    <option value="">Seleccione...</option>
                                    <option value="deudora">Deudora</option>
                                    <option value="acreedora">Acreedora</option>
                                </select>
                                @error('naturaleza') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Cuenta Padre</label>
                                <select wire:model="cuenta_padre_id" class="form-select">
                                    <option value="">Sin cuenta padre</option>
                                    @foreach($cuentasPadre as $padre)
                                        <option value="{{ $padre->id }}">{{ $padre->codigo }} - {{ $padre->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Opciones</label>
                                <div class="form-check form-switch mt-2">
                                    <input type="checkbox" wire:model="acepta_movimientos" class="form-check-input" id="acepta_movimientos">
                                    <label class="form-check-label" for="acepta_movimientos">Acepta movimientos</label>
                                </div>
                                <div class="form-check form-switch">
                                    <input type="checkbox" wire:model="activo" class="form-check-input" id="activo">
                                    <label class="form-check-label" for="activo">Activo</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Descripción</label>
                                <textarea wire:model="descripcion" class="form-control" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-label-secondary" wire:click="closeModal">Cancelar</button>
                        <button type="button" class="btn btn-primary" wire:click="save">Guardar</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
