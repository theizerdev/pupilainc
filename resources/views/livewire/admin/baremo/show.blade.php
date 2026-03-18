<div>
<div class="py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-2">
                        <li class="breadcrumb-item"><a href="{{ route('admin.baremos.index') }}">Servicios</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $baremo->nombre_servicio }}</li>
                    </ol>
                </nav>
                <h1 class="h3 mb-0 text-gray-800">
                    <span class="badge bg-primary me-2">
                        <i class="fas fa-concierge-bell"></i>
                    </span>
                    {{ $baremo->nombre_servicio }}
                </h1>
                <p class="text-muted">Detalles del servicio</p>
            </div>
            <div>
                <a href="{{ route('admin.baremos.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Volver al Listado
                </a>
                @can('edit baremos')
                <a href="{{ route('admin.baremos.edit', $baremo) }}" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Editar
                </a>
                @endcan
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Código
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $baremo->codigo }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-barcode fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Costo (USD)
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ format_money($baremo->costo_usd, 2) }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Duración
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $baremo->duracion_minutos ?? 30 }} min</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-clock fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    Estado
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <span class="badge badge-{{ $baremo->activo ? 'success' : 'secondary' }}">
                                        {{ $baremo->activo ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Details -->
        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Información General</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="fw-bold text-muted"><i class="fas fa-tag me-1"></i>Categoría:</label>
                            <p class="mb-0">
                                @if($baremo->categoria)
                                    <span class="badge" style="background-color: {{ $baremo->categoria->color }}; color: white;">
                                        {{ $baremo->categoria->nombre }}
                                    </span>
                                @else
                                    <span class="text-muted">Sin categoría</span>
                                @endif
                            </p>
                        </div>
                        
                        <hr>
                        
                        <div class="mb-3">
                            <label class="fw-bold text-muted"><i class="fas fa-user-md me-1"></i>Especialidad:</label>
                            <p class="mb-0">{{ $baremo->especialidad->nombre ?? 'N/A' }}</p>
                        </div>
                        
                        <hr>
                        
                        <div class="mb-3">
                            <label class="fw-bold text-muted"><i class="fas fa-barcode me-1"></i>Código:</label>
                            <p class="mb-0"><strong>{{ $baremo->codigo }}</strong></p>
                        </div>
                        
                        <hr>
                        
                        <div class="mb-3">
                            <label class="fw-bold text-muted"><i class="fas fa-font me-1"></i>Nombre del Servicio:</label>
                            <p class="mb-0">{{ $baremo->nombre_servicio }}</p>
                        </div>
                        
                        <hr>
                        
                        <div class="mb-3">
                            <label class="fw-bold text-muted"><i class="fas fa-align-left me-1"></i>Descripción:</label>
                            <p class="mb-0">{{ $baremo->descripcion ?? 'Sin descripción' }}</p>
                        </div>
                    </div>
                </div>

                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Costos e Impuestos</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="fw-bold text-muted"><i class="fas fa-dollar-sign me-1"></i>Costo en USD:</label>
                                <p class="mb-0 fs-5 fw-bold text-success">{{ format_money($baremo->costo_usd, 2) }}</p>
                            </div>
                            
                            @if(auth()->user()->empresa->pais->nombre == 'Venezuela')
                            <div class="col-md-6 mb-3">
                                <label class="fw-bold text-muted"><i class="fas fa-exchange-alt me-1"></i>Costo en Bs:</label>
                                <p class="mb-0 fs-5 fw-bold text-primary">Bs. {{ number_format($baremo->costo_bs, 2) }}</p>
                            </div>
                            @endif
                        </div>
                        
                        <hr>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="fw-bold text-muted"><i class="fas fa-percent me-1"></i>Aplica IVA:</label>
                                <p class="mb-0">
                                    <span class="badge badge-{{ $baremo->aplica_iva ? 'success' : 'secondary' }}">
                                        {{ $baremo->aplica_iva ? 'Sí' : 'No' }}
                                    </span>
                                </p>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="fw-bold text-muted"><i class="fas fa-receipt me-1"></i>Exento de IVA:</label>
                                <p class="mb-0">
                                    <span class="badge badge-{{ $baremo->exento_iva ? 'warning' : 'secondary' }}">
                                        {{ $baremo->exento_iva ? 'Sí' : 'No' }}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Información del Sistema</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="fw-bold text-muted"><i class="fas fa-building me-1"></i>Empresa:</label>
                                <p class="mb-0">{{ $baremo->empresa->razon_social ?? 'N/A' }}</p>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="fw-bold text-muted"><i class="fas fa-map-marker-alt me-1"></i>Sucursal:</label>
                                <p class="mb-0">{{ $baremo->sucursal->nombre ?? 'N/A' }}</p>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="fw-bold text-muted"><i class="fas fa-clock me-1"></i>Creado:</label>
                                <p class="mb-0">{{ $baremo->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="fw-bold text-muted"><i class="fas fa-history me-1"></i>Actualizado:</label>
                                <p class="mb-0">{{ $baremo->updated_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Preview Card -->
                <div class="card shadow-sm border-0 sticky-top" style="top: 20px; z-index: 100; border-radius: 15px; overflow: hidden;">
                    <div class="card-header text-white py-3 bg-primary">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="m-0">
                                <i class="fas fa-eye me-2"></i>Vista Previa
                            </h6>
                            <span class="badge bg-white text-primary" style="font-size: 0.75rem;">
                                <i class="fas fa-info-circle me-1"></i>Detalle
                            </span>
                        </div>
                    </div>
                    
                    <div class="card-body p-0">
                        <div class="text-center p-4 bg-light">
                            <div class="mb-3">
                                <div class="badge d-inline-block px-4 py-3 shadow-lg bg-primary" 
                                     style="min-width: 220px; border-radius: 12px;">
                                    <i class="fas fa-concierge-bell fa-3x d-block mb-2"></i>
                                    <span class="d-block fw-bold" style="letter-spacing: 1px; font-size: 0.9em;">{{ $baremo->codigo }}</span>
                                </div>
                            </div>
                            
                            <h5 class="fw-bold mb-2">{{ $baremo->nombre_servicio }}</h5>
                            
                            <div class="mb-3">
                                <span class="badge bg-success" style="font-size: 1.2em;">
                                    <i class="fas fa-dollar-sign me-1"></i>{{ format_money($baremo->costo_usd, 2) }}
                                </span>
                                @if($baremo->aplica_iva && !$baremo->exento_iva)
                                    <br><small class="text-muted">+ IVA</small>
                                @endif
                            </div>
                            
                            @if($baremo->categoria)
                            <div class="mb-3">
                                <span class="badge" style="background-color: {{ $baremo->categoria->color }}; color: white;">
                                    <i class="fas fa-tag me-1"></i>{{ $baremo->categoria->nombre }}
                                </span>
                            </div>
                            @endif
                            
                            @if($baremo->descripcion)
                            <div class="mx-3 mt-3 p-3 rounded bg-white">
                                <p class="text-muted small mb-0 fst-italic">
                                    <i class="fas fa-quote-left me-2 opacity-50"></i>{{ Str::limit($baremo->descripcion, 100) }}
                                </p>
                            </div>
                            @endif
                        </div>
                        
                        <div class="card-footer bg-light py-3">
                            <div class="row text-center">
                                <div class="col-6">
                                    <small class="text-muted d-block mb-1">Estado:</small>
                                    <span class="badge badge-{{ $baremo->activo ? 'success' : 'secondary' }}">
                                        {{ $baremo->activo ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block mb-1">Duración:</small>
                                    <strong>{{ $baremo->duracion_minutos ?? 30 }} min</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
