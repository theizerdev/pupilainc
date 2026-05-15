<div class="w-100">
    @section('title', 'Órdenes de Compra')

    @push('styles')
    <style>
        /* Hero Section */
        .ordenes-hero {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            color: #fff;
            border-radius: 0.75rem;
            padding: 1.4rem 1.6rem;
        }
        .ordenes-hero h2 {
            color: #fff;
            margin: 0;
        }
        .ordenes-hero p {
            opacity: 0.9;
            margin: 0;
        }

        /* Stat Cards - Compact Style */
        .stat-card {
            border: 1px solid rgba(0,0,0,.06);
            border-radius: .65rem;
            padding: .9rem 1rem;
            transition: all .2s;
            display: flex;
            align-items: center;
            gap: .85rem;
            height: 100%;
            background: #fff;
        }
        .stat-card:hover {
            box-shadow: 0 6px 18px rgba(0,0,0,.07);
            transform: translateY(-1px);
        }
        .stat-card .stat-icon {
            width: 44px;
            height: 44px;
            border-radius: 11px;
            flex: 0 0 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
        }
        .stat-card .stat-value {
            font-size: 1.35rem;
            font-weight: 600;
            line-height: 1;
        }
        .stat-card .stat-label {
            font-size: .72rem;
            color: var(--bs-secondary-color);
            text-transform: uppercase;
            letter-spacing: .4px;
            font-weight: 600;
        }

        /* Table Styles */
        .table-ordenes thead th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #e4e6f9;
            font-weight: 600;
            font-size: 0.8125rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #697a8d;
            padding: 0.875rem 1rem;
        }
        .table-ordenes tbody td {
            padding: 0.875rem 1rem;
            vertical-align: middle;
        }
        .orden-row { transition: background 0.15s; }
        .orden-row:hover { background-color: #f8f9fa; }
    </style>
    @endpush

    {{-- Hero Section --}}
    <div class="ordenes-hero mb-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <h2 class="fw-semibold"><i class="ri ri-file-list-3-line me-2"></i>Órdenes de Compra</h2>
            <p class="mt-1">Gestión de órdenes de compra a proveedores</p>
        </div>
        @can('create ordenes-compra')
            <a href="{{ route('admin.inventario.ordenes-compra.create') }}" class="btn btn-light btn-sm">
                <i class="ri ri-add-line me-1"></i>Nueva Orden
            </a>
        @endcan
    </div>

    {{-- Stat Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-4">
            <div class="stat-card">
                <div class="stat-icon" style="background:#dbeafe;color:#2563eb;"><i class="ri ri-database-2-line"></i></div>
                <div>
                    <div class="stat-label">Total órdenes</div>
                    <div class="stat-value">{{ $stats['total'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-4">
            <div class="stat-card">
                <div class="stat-icon" style="background:#fef3c7;color:#d97706;"><i class="ri ri-time-line"></i></div>
                <div>
                    <div class="stat-label">Activas</div>
                    <div class="stat-value text-warning">{{ $stats['activas'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-4">
            <div class="stat-card">
                <div class="stat-icon" style="background:#f3f4f6;color:#6b7280;"><i class="ri ri-draft-line"></i></div>
                <div>
                    <div class="stat-label">Borradores</div>
                    <div class="stat-value text-secondary">{{ $stats['borradores'] }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Card with Filters and Table --}}
    <div class="card">
        <div class="card-header border-bottom py-3">
            <h5 class="card-title mb-3">Filtros</h5>
            <div class="row g-3">
                <div class="col-md-4">
                    <input type="text" class="form-control" wire:model.live.debounce.300ms="search" placeholder="Buscar por número o proveedor...">
                </div>
                <div class="col-md-3">
                    <select class="form-select" wire:model.change="estado">
                        <option value="">Todos los estados</option>
                        @foreach($estados as $key => $info)
                            <option value="{{ $key }}">{{ $info['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" wire:model.change="proveedor_id">
                        <option value="">Todos los proveedores</option>
                        @foreach($proveedores as $prov)
                            <option value="{{ $prov->id }}">{{ $prov->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-outline-secondary w-100" wire:click="resetFilters" title="Limpiar filtros">
                        <i class="ri ri-close-line"></i>
                    </button>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="card-datatable table-responsive">
            <table class="datatables-products table table-ordenes">
                <thead>
                    <tr>
                        <th>Número</th>
                        <th>Proveedor</th>
                        <th>Almacén</th>
                        <th>Estado</th>
                        <th>Fecha Emisión</th>
                        <th>Fecha Esperada</th>
                        <th>Total</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ordenes as $orden)
                        @php $estadoInfo = $orden->estado_info; @endphp
                        <tr class="orden-row">
                            <td><span class="fw-bold">{{ $orden->numero }}</span></td>
                            <td>{{ $orden->proveedor->nombre }}</td>
                            <td>{{ $orden->almacen->nombre }}</td>
                            <td>
                                <span class="badge rounded-pill bg-label-{{ $estadoInfo['color'] }}">{{ $estadoInfo['label'] }}</span>
                                @if($orden->generada_automaticamente)
                                    <span class="badge rounded-pill bg-label-info ms-1" title="Generada automáticamente">Auto</span>
                                @endif
                            </td>
                            <td>{{ $orden->fecha_emision->format('d/m/Y') }}</td>
                            <td>{{ $orden->fecha_esperada?->format('d/m/Y') ?? '-' }}</td>
                            <td class="fw-bold">{{ money($orden->total, 2) }}</td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-icon btn-text-secondary rounded-pill dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                        <i class="ri ri-more-2-line"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        @can('edit ordenes-compra')
                                            @if(!in_array($orden->estado, ['recibida', 'cancelada']))
                                                <li>
                                                    <a href="{{ route('admin.inventario.ordenes-compra.edit', $orden) }}" class="dropdown-item">
                                                        <i class="ri ri-edit-line me-2"></i>Editar
                                                    </a>
                                                </li>
                                                <li>
                                                    <button class="dropdown-item text-success"
                                                            wire:click="recibirCompleta({{ $orden->id }})"
                                                            wire:confirm="¿Marcar como recibida completa? Esto actualizará el stock.">
                                                        <i class="ri ri-checkbox-circle-line me-2"></i>Recibir completa
                                                    </button>
                                                </li>
                                                <li>
                                                    <button class="dropdown-item text-danger"
                                                            wire:click="cancelar({{ $orden->id }})"
                                                            wire:confirm="¿Cancelar esta orden?">
                                                        <i class="ri ri-close-circle-line me-2"></i>Cancelar
                                                    </button>
                                                </li>
                                            @else
                                                <li>
                                                    <button class="dropdown-item"
                                                            wire:click="$dispatch('abrirModal', { ordenId: {{ $orden->id }} })">
                                                        <i class="ri ri-eye-line me-2"></i>Ver detalle
                                                    </button>
                                                </li>
                                            @endif
                                        @endcan
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                <i class="ri ri-inbox-line ri-3x d-block mb-3"></i>
                                <p class="mb-0">No se encontraron órdenes</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($ordenes->hasPages())
            <div class="card-footer bg-white border-0 pt-3 pb-3">
                {{ $ordenes->links('livewire.pagination') }}
            </div>
        @endif
    </div>

    {{-- Modal para ver detalle --}}
    @livewire('admin.inventario.ordenes-compra.ver-detalle')
</div>
