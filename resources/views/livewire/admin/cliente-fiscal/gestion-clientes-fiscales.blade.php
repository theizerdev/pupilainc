<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6>Clientes Fiscales</h6>
                        <button wire:click="crear" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Nuevo Cliente
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <input wire:model.live="search" type="text" class="form-control form-control-sm" placeholder="Buscar por razón social o documento...">
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th>RIF/CI</th>
                                    <th>Razón Social</th>
                                    <th>Dirección</th>
                                    <th>Teléfono</th>
                                    <th>Email</th>
                                    <th>Paciente</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($clientes as $cliente)
                                <tr>
                                    <td>{{ $cliente->documento_completo }}</td>
                                    <td>
                                        <div>{{ $cliente->razon_social }}</div>
                                        @if($cliente->nombre_comercial)
                                        <small class="text-muted">{{ $cliente->nombre_comercial }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <small>{{ $cliente->direccion_fiscal }}</small>
                                        @if($cliente->ciudad)
                                        <br><small class="text-muted">{{ $cliente->ciudad }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $cliente->telefono }}</td>
                                    <td>{{ $cliente->email }}</td>
                                    <td>
                                        @if($cliente->paciente)
                                        <span class="badge badge-sm bg-info">{{ $cliente->paciente->nombre_completo }}</span>
                                        @else
                                        -
                                        @endif
                                    </td>
                                    <td>
                                        <button wire:click="editar({{ $cliente->id }})" class="btn btn-sm btn-info">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button wire:click="eliminar({{ $cliente->id }})" onclick="confirm('¿Eliminar?') || event.stopImmediatePropagation()" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center">No hay clientes fiscales registrados</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $clientes->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    @if($modal)
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $cliente_id ? 'Editar' : 'Nuevo' }} Cliente Fiscal</h5>
                    <button type="button" wire:click="$set('modal', false)" class="btn-close"></button>
                </div>
                <form wire:submit.prevent="guardar">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Tipo Doc. *</label>
                                <select wire:model="tipo_documento" class="form-select">
                                    <option value="V">V - Venezolano</option>
                                    <option value="E">E - Extranjero</option>
                                    <option value="J">J - Jurídico</option>
                                    <option value="G">G - Gubernamental</option>
                                    <option value="P">P - Pasaporte</option>
                                </select>
                                @error('tipo_documento') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-9 mb-3">
                                <label class="form-label">Número de Documento *</label>
                                <input wire:model="numero_documento" type="text" class="form-control">
                                @error('numero_documento') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Razón Social *</label>
                                <input wire:model="razon_social" type="text" class="form-control">
                                @error('razon_social') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nombre Comercial</label>
                                <input wire:model="nombre_comercial" type="text" class="form-control">
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Dirección Fiscal *</label>
                                <textarea wire:model="direccion_fiscal" class="form-control" rows="2"></textarea>
                                @error('direccion_fiscal') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Ciudad</label>
                                <input wire:model="ciudad" type="text" class="form-control">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Estado</label>
                                <input wire:model="estado" type="text" class="form-control">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Código Postal</label>
                                <input wire:model="codigo_postal" type="text" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Teléfono</label>
                                <input wire:model="telefono" type="text" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input wire:model="email" type="email" class="form-control">
                                @error('email') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Asociar a Paciente (opcional)</label>
                                <select wire:model="paciente_id" class="form-select">
                                    <option value="">Ninguno</option>
                                    @foreach($pacientes as $paciente)
                                    <option value="{{ $paciente->id }}">{{ $paciente->nombre_completo }} - {{ $paciente->numero_documento }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" wire:click="$set('modal', false)" class="btn btn-secondary">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
