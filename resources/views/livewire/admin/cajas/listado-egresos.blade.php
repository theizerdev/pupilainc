<div class="py-4">
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-money-bill-wave me-2 text-danger"></i>
                    Registro de Egresos
                </h5>
                <button
                    wire:click="limpiarFiltros"
                    class="btn btn-sm btn-outline-secondary"
                >
                    <i class="fas fa-filter-circle-xmark me-1"></i>Limpiar Filtros
                </button>
            </div>
        </div>

        <div class="card-body">
            {{-- Resumen --}}
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="bg-light p-3 rounded">
                        <small class="text-muted">Total Egresos</small>
                        <h4 class="mb-0 text-danger">{{ money($resumen['total_monto'], 2) }}</h4>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="bg-light p-3 rounded">
                        <small class="text-muted">Cantidad</small>
                        <h4 class="mb-0">{{ $resumen['cantidad'] }}</h4>
                    </div>
                </div>
                @if($resumen['total_monto_bs'] > 0)
                <div class="col-md-3">
                    <div class="bg-light p-3 rounded">
                        <small class="text-muted">Total en Bs</small>
                        <h4 class="mb-0">Bs {{ number_format($resumen['total_monto_bs'], 2) }}</h4>
                    </div>
                </div>
                @endif
            </div>

            {{-- Filtros --}}
            <div class="row mb-3 g-3">
                <div class="col-md-4">
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        class="form-control"
                        placeholder="Buscar por concepto, referencia..."
                    >
                </div>
                <div class="col-md-2">
                    <select wire:model.live="filtro_categoria" class="form-select">
                        <option value="">Todas las categorías</option>
                        <option value="combustible">Combustible</option>
                        <option value="materiales">Materiales</option>
                        <option value="servicios">Servicios</option>
                        <option value="mantenimiento">Mantenimiento</option>
                        <option value="otros">Otros</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select wire:model.live="filtro_metodo_pago" class="form-select">
                        <option value="">Todos los métodos</option>
                        <option value="efectivo">Efectivo</option>
                        <option value="transferencia">Transferencia</option>
                        <option value="tarjeta">Tarjeta</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input
                        type="date"
                        wire:model.live="filtro_fecha_inicio"
                        class="form-control"
                        title="Fecha inicio"
                    >
                </div>
                <div class="col-md-2">
                    <input
                        type="date"
                        wire:model.live="filtro_fecha_fin"
                        class="form-control"
                        title="Fecha fin"
                    >
                </div>
            </div>

            {{-- Tabla de egresos --}}
            @if($egresos->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Fecha</th>
                            <th>Concepto</th>
                            <th>Categoría</th>
                            <th>Método</th>
                            <th>Monto USD</th>
                            <th>Monto Bs</th>
                            <th>Usuario</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($egresos as $egreso)
                        <tr>
                            <td>{{ $egreso->fecha_gasto->format('d/m/Y H:i') }}</td>
                            <td>
                                <strong>{{ $egreso->concepto }}</strong>
                                @if($egreso->observaciones)
                                    <br><small class="text-muted">{{ Str::limit($egreso->observaciones, 50) }}</small>
                                @endif
                            </td>
                            <td>
                                @if($egreso->categoria)
                                    <span class="badge bg-secondary">{{ ucfirst($egreso->categoria) }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-info">{{ ucfirst($egreso->metodo_pago) }}</span>
                                @if($egreso->numero_referencia)
                                    <br><small class="text-muted">{{ $egreso->numero_referencia }}</small>
                                @endif
                            </td>
                            <td class="text-danger fw-bold">-{{ money($egreso->monto, 2) }}</td>
                            <td>
                                @if($egreso->monto_bs > 0)
                                    Bs {{ number_format($egreso->monto_bs, 2) }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if($egreso->usuario)
                                    <small>{{ $egreso->usuario->name }}</small>
                                @endif
                            </td>
                            <td>
                                <button
                                    wire:click="eliminarEgreso({{ $egreso->id }})"
                                    wire:confirm="¿Está seguro de eliminar este egreso?"
                                    class="btn btn-sm btn-outline-danger"
                                    title="Eliminar"
                                >
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Paginación --}}
            <div class="mt-3">
                {{ $egresos->links('livewire::simple-bootstrap') }}
            </div>
            @else
            <div class="text-center py-5">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <p class="text-muted">No se encontraron egresos registrados</p>
            </div>
            @endif
        </div>
    </div>
</div>
