<div>
    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Formulario -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ $editingId ? 'Editar' : 'Nueva' }} Regla de Morosidad</h5>
                </div>
                <div class="card-body">
                    <form wire:submit="save">
                        <div class="mb-3">
                            <label class="form-label">Nombre</label>
                            <input type="text" class="form-control" wire:model="nombre" placeholder="Ej: Recargo por mora">
                            @error('nombre') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tipo de Recargo</label>
                            <select class="form-select" wire:model="tipo">
                                <option value="porcentaje">Porcentaje (%)</option>
                                <option value="monto_fijo">Monto Fijo ($)</option>
                            </select>
                            @error('tipo') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                Valor {{ $tipo === 'porcentaje' ? '(%)' : '($)' }}
                            </label>
                            <input type="number" step="0.01" class="form-control" wire:model="valor" 
                                   placeholder="{{ $tipo === 'porcentaje' ? 'Ej: 5' : 'Ej: 10.00' }}">
                            @error('valor') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Días de Gracia</label>
                            <input type="number" class="form-control" wire:model="dias_gracia" placeholder="0">
                            <small class="text-muted">Días después del vencimiento antes de aplicar recargo</small>
                            @error('dias_gracia') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                {{ $editingId ? 'Actualizar' : 'Guardar' }}
                            </button>
                            @if($editingId)
                            <button type="button" class="btn btn-secondary" wire:click="$reset">
                                Cancelar
                            </button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Lista de Reglas -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Reglas Configuradas</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Tipo</th>
                                    <th>Valor</th>
                                    <th>Días Gracia</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rules as $rule)
                                <tr>
                                    <td>{{ $rule->nombre }}</td>
                                    <td>
                                        <span class="badge bg-label-{{ $rule->tipo === 'porcentaje' ? 'info' : 'warning' }}">
                                            {{ $rule->tipo === 'porcentaje' ? 'Porcentaje' : 'Monto Fijo' }}
                                        </span>
                                    </td>
                                    <td class="fw-medium">
                                        {{ $rule->tipo === 'porcentaje' ? $rule->valor . '%' : '$' . format_money($rule->valor, 2) }}
                                    </td>
                                    <td>{{ $rule->dias_gracia }} días</td>
                                    <td>
                                        <span class="badge bg-label-{{ $rule->activo ? 'success' : 'secondary' }}">
                                            {{ $rule->activo ? 'Activa' : 'Inactiva' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                                <i class="ri ri-more-2-line"></i>
                                            </button>
                                            <div class="dropdown-menu">
                                                <button class="dropdown-item" wire:click="edit({{ $rule->id }})">
                                                    <i class="ri ri-pencil-line me-1"></i> Editar
                                                </button>
                                                <button class="dropdown-item text-danger" 
                                                        wire:click="delete({{ $rule->id }})"
                                                        wire:confirm="¿Eliminar esta regla?">
                                                    <i class="ri ri-delete-bin-line me-1"></i> Eliminar
                                                </button>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        No hay reglas configuradas
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>