<div class="pos-app"
    x-data="{ now: new Date().toLocaleTimeString('es-VE',{hour:'2-digit',minute:'2-digit'}) }"
    x-init="setInterval(() => now = new Date().toLocaleTimeString('es-VE',{hour:'2-digit',minute:'2-digit'}), 30000)">

    {{-- ═══════════════════════ TOP BAR ═══════════════════════ --}}
    <header class="pos-topbar">
        <div class="topbar-left">
            <div class="pos-brand">
                <div class="brand-icon"><i class="ri ri-store-2-line"></i></div>
                <div class="brand-text">
                    <div class="brand-title">Punto de Venta</div>
                    <div class="brand-sub">{{ auth()->user()->sucursal?->nombre ?? 'Sucursal principal' }}</div>
                </div>
            </div>
        </div>

        <div class="topbar-right">
            @if($caja)
                <div class="status-pill ok">
                    <span class="status-dot"></span>
                    <i class="ri ri-archive-line"></i>
                    <div class="pill-text">
                        <span class="pill-label">Caja abierta</span>
                        <span class="pill-value">#{{ $caja->numero_corte }} · ${{ number_format($caja->monto_inicial, 2) }}</span>
                    </div>
                </div>
            @else
                <a href="{{ route('admin.cajas.index') }}" class="status-pill error">
                    <i class="ri ri-error-warning-line"></i>
                    <div class="pill-text">
                        <span class="pill-label">Sin caja</span>
                        <span class="pill-value">Click para abrir</span>
                    </div>
                </a>
            @endif

            <div class="status-pill muted">
                <i class="ri ri-exchange-dollar-line"></i>
                <div class="pill-text">
                    <span class="pill-label">Tasa USD</span>
                    <span class="pill-value">Bs {{ number_format($tasa_usd, 2) }}</span>
                </div>
            </div>

            <div class="status-pill muted hide-md">
                <i class="ri ri-time-line"></i>
                <div class="pill-text">
                    <span class="pill-label">Hora</span>
                    <span class="pill-value" x-text="now">--:--</span>
                </div>
            </div>

            <div class="status-pill light hide-sm">
                <i class="ri ri-user-line"></i>
                <div class="pill-text">
                    <span class="pill-label">Cajero</span>
                    <span class="pill-value">{{ auth()->user()->name }}</span>
                </div>
            </div>

            <a href="{{ url('/admin/dashboard') }}" class="topbar-exit" title="Salir del POS">
                <i class="ri ri-logout-box-r-line"></i>
            </a>
        </div>
    </header>

    {{-- ═══════════════════════ MAIN GRID ═══════════════════════ --}}
    <div class="pos-main">

        {{-- ─────────── CATÁLOGO ─────────── --}}
        <section class="pos-catalog">

            {{-- Cliente --}}
            <div class="pos-client-bar">
                <div class="client-icon"><i class="ri ri-user-3-line"></i></div>
                <div class="client-content">
                    @if($cliente_id && !$busqueda_cliente)
                        <div class="client-label">Cliente actual</div>
                        <div class="client-name">{{ $cliente_display }}</div>
                    @else
                        <div class="client-label">Buscar cliente</div>
                        <input type="text" wire:model.live.debounce.300ms="busqueda_cliente"
                            class="client-input"
                            placeholder="Nombre, documento o teléfono...">
                        @if(count($clientes_encontrados))
                            <div class="pos-dropdown">
                                @foreach($clientes_encontrados as $c)
                                    <button type="button" wire:click="seleccionarCliente({{ $c['id'] }})" class="dropdown-item">
                                        <div class="dropdown-content">
                                            <span class="fw-semibold">{{ $c['nombre'] ?? $c['razon_social'] }}</span>
                                            @if($c['telefono'] && $c['telefono']!=='00000000')
                                                <small><i class="ri ri-phone-line me-1"></i>{{ $c['telefono'] }}</small>
                                            @endif
                                        </div>
                                        @if($c['es_cliente_rapido'])
                                            <span class="dropdown-badge gray">Rápido</span>
                                        @else
                                            <span class="dropdown-badge info">{{ $c['tipo_documento'] }}-{{ $c['numero_documento'] }}</span>
                                        @endif
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    @endif
                </div>
                <div class="client-actions">
                    @if($cliente_id && !$busqueda_cliente)
                        <button wire:click="$set('cliente_id', null)" class="btn-icon-light" title="Cambiar cliente">
                            <i class="ri ri-exchange-line"></i>
                        </button>
                    @endif
                    <button wire:click="$set('mostrar_modal_cliente', true)" class="btn-add">
                        <i class="ri ri-user-add-line"></i><span>Nuevo</span>
                    </button>
                </div>
            </div>

            {{-- Tabs + buscador --}}
            <div class="pos-tabs-bar">
                <div class="pos-tabs">
                    <button wire:click="$set('tab_busqueda','todos')"
                        class="tab @if($tab_busqueda==='todos') active @endif">
                        <i class="ri ri-apps-2-line"></i><span>Todos</span>
                    </button>
                    <button wire:click="$set('tab_busqueda','productos')"
                        class="tab @if($tab_busqueda==='productos') active @endif">
                        <i class="ri ri-box-3-line"></i><span>Productos</span>
                    </button>
                    <button wire:click="$set('tab_busqueda','servicios')"
                        class="tab @if($tab_busqueda==='servicios') active @endif">
                        <i class="ri ri-stethoscope-line"></i><span>Servicios</span>
                    </button>
                </div>
                <div class="pos-search">
                    <i class="ri ri-search-line search-icon"></i>
                    <input type="text" wire:model.live.debounce.300ms="busqueda_item"
                        placeholder="Buscar por nombre, código o código de barras..."
                        autofocus>
                    @if($busqueda_item)
                        <button wire:click="$set('busqueda_item','')" class="search-clear">
                            <i class="ri ri-close-circle-fill"></i>
                        </button>
                    @endif
                </div>
            </div>
