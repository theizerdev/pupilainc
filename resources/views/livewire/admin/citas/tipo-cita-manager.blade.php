<div>
    <div class="row">
        <div class="col-lg-12">
            <div class="card" id="orderList">
                <div class="card-header border-0">
                    <div class="row align-items-center gy-3">
                        <div class="col-sm">
                            <h5 class="card-title mb-0">Administrador de Tipos de Cita</h5>
                        </div>
                        <div class="col-sm-auto">
                            <div class="d-flex gap-1 flex-wrap">
                                <button type="button" class="btn btn-success add-btn" data-bs-toggle="modal" id="create-btn" data-bs-target="#showModal" wire:click="openModal">
                                    <i class="ri-add-line align-bottom me-1"></i> Nuevo Tipo de Cita
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body border border-dashed border-end-0 border-start-0">
                    <form wire:submit.prevent="applyFilters">
                        <div class="row g-3">
                            <div class="col-xxl-5 col-sm-6">
                                <div class="search-box">
                                    <input type="text" class="form-control search" placeholder="Buscar por nombre o descripción..." wire:model.lazy="search">
                                    <i class="ri-search-line search-icon"></i>
                                </div>
                            </div>
                            <div class="col-xxl-2 col-sm-4">
                                <div>
                                    <select class="form-select" wire:model.lazy="perPage">
                                        <option value="10">10</option>
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-xxl-1 col-sm-4">
                                <div>
                                    <button type="button" class="btn btn-primary w-100" wire:click="applyFilters">
                                        <i class="ri-equalizer-fill me-1 align-bottom"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="card-body pt-0">
                    <div>
                        <div class="table-responsive table-card mb-1">
                            <table class="table table-nowrap align-middle" id="orderTable">
                                <thead class="text-muted table-light">
                                <tr class="text-uppercase">
                                    <th>Nombre</th>
                                    <th>Descripción</th>
                                    <th>Duración</th>
                                    <th>Precio</th>
                                    <th>Acciones</th>
                                </tr>
                                </thead>
                                <tbody class="list form-check-all">
                                @forelse($tiposCita as $tipo)
                                    <tr>
                                        <td>{{ $tipo->nombre }}</td>
                                        <td>{{ Str::limit($tipo->descripcion, 50) }}</td>
                                        <td>{{ $tipo->duracion }} min</td>
                                        <td>{{-- format_currency($tipo->precio) --}}</td>
                                        <td>
                                            <ul class="list-inline hstack gap-2 mb-0">
                                                <li class="list-inline-item" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="Editar">
                                                    <a href="#" class="text-primary d-inline-block edit-item-btn" data-bs-toggle="modal" data-bs-target="#showModal" wire:click="edit({{ $tipo->id }})">
                                                        <i class="ri-pencil-fill fs-16"></i>
                                                    </a>
                                                </li>
                                                <li class="list-inline-item" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="Eliminar">
                                                    <a class="text-danger d-inline-block remove-item-btn" href="#" wire:click.prevent="$dispatch('delete', { id: {{ $tipo->id }}, eventName: 'deleteTipoCita' })">
                                                        <i class="ri-delete-bin-5-fill fs-16"></i>
                                                    </a>
                                                </li>
                                            </ul>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">No se encontraron registros.</td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-end">
                            {{ $tiposCita->links() }}
                        </div>
                    </div>

                    <!-- Modal -->
                    <div wire:ignore.self class="modal fade" id="showModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header bg-light p-3">
                                    <h5 class="modal-title" id="exampleModalLabel">{{ $modalTitle }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="close-modal"></button>
                                </div>
                                <form wire:submit.prevent="save">
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label for="nombre" class="form-label">Nombre</label>
                                            <input type="text" wire:model="nombre" class="form-control" id="nombre" placeholder="Ingrese el nombre">
                                            @error('nombre') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                        <div class="mb-3">
                                            <label for="descripcion" class="form-label">Descripción</label>
                                            <textarea wire:model="descripcion" class="form-control" id="descripcion" rows="3" placeholder="Ingrese una descripción"></textarea>
                                            @error('descripcion') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="duracion" class="form-label">Duración (minutos)</label>
                                                <input type="number" wire:model="duracion" class="form-control" id="duracion" placeholder="Ej: 30">
                                                @error('duracion') <span class="text-danger">{{ $message }}</span> @enderror
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="precio" class="form-label">Precio</label>
                                                <input type="text" wire:model="precio" class="form-control" id="precio" placeholder="Ej: 50.00">
                                                @error('precio') <span class="text-danger">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <div class="hstack gap-2 justify-content-end">
                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
                                            <button type="submit" class="btn btn-success" id="add-btn">Guardar</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>