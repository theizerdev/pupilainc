<div>
    <div class="py-4">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-2">
                        <li class="breadcrumb-item"><a href="{{ route('admin.categorias.index') }}">Categorías</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $categoria->nombre }}</li>
                    </ol>
                </nav>
                <h1 class="h3 mb-0 text-gray-800">
                    <span class="badge me-2" style="background-color: {{ $categoria->color }}; color: white;">
                        <i class="ri {{ $categoria->icono }}"></i>
                    </span>
                    {{ $categoria->nombre }}
                </h1>
                <p class="text-muted">Detalles de la categoría</p>
            </div>
            <div>
                <a href="{{ route('admin.categorias.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Volver al Listado
                </a>
                @can('edit categorias')
                <a href="{{ route('admin.categorias.edit', $categoria) }}" class="btn btn-primary">
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
                                    ID
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">#{{ $categoria->id }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-hashtag fa-2x text-gray-300"></i>
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
                                    Estado
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <span class="badge badge-{{ $categoria->activo ? 'success' : 'secondary' }}">
                                        {{ $categoria->activo ? 'Activo' : 'Inactivo' }}
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

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Orden
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $categoria->orden ?? 0 }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-sort fa-2x text-gray-300"></i>
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
                                    Color
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <span class="badge" style="background-color: {{ $categoria->color }}; color: white;">
                                        {{ $categoria->color }}
                                    </span>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-palette fa-2x text-gray-300"></i>
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
                            <label class="fw-bold text-muted"><i class="fas fa-font me-1"></i>Nombre:</label>
                            <p class="mb-0">{{ $categoria->nombre }}</p>
                        </div>
                        
                        <hr>
                        
                        <div class="mb-3">
                            <label class="fw-bold text-muted"><i class="fas fa-align-left me-1"></i>Descripción:</label>
                            <p class="mb-0">{{ $categoria->descripcion ?? 'Sin descripción' }}</p>
                        </div>
                        
                        <hr>
                        
                        <div class="mb-3">
                            <label class="fw-bold text-muted"><i class="fas fa-palette me-1"></i>Color:</label>
                            <div class="d-flex align-items-center mt-2">
                                <div class="me-2" style="width: 40px; height: 40px; background-color: {{ $categoria->color }}; border-radius: 8px;"></div>
                                <span class="fw-bold">{{ $categoria->color }}</span>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="mb-3">
                            <label class="fw-bold text-muted"><i class="ri ri-shape-line me-1"></i>Icono:</label>
                            <div class="d-flex align-items-center mt-2">
                                <i class="ri {{ $categoria->icono }} fa-2x me-2" style="color: {{ $categoria->color }};"></i>
                                <span class="fw-bold">{{ $categoria->icono ?? 'Sin icono' }}</span>
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
                                <p class="mb-0">{{ $categoria->empresa->razon_social ?? 'N/A' }}</p>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="fw-bold text-muted"><i class="fas fa-map-marker-alt me-1"></i>Sucursal:</label>
                                <p class="mb-0">{{ $categoria->sucursal->nombre ?? 'N/A' }}</p>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="fw-bold text-muted"><i class="fas fa-clock me-1"></i>Creado:</label>
                                <p class="mb-0">{{ $categoria->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="fw-bold text-muted"><i class="fas fa-history me-1"></i>Actualizado:</label>
                                <p class="mb-0">{{ $categoria->updated_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Preview Card -->
                <div class="card shadow-sm border-0 sticky-top" style="top: 20px; z-index: 100; border-radius: 15px; overflow: hidden;">
                    <div class="card-header text-white py-3" 
                         style="background: linear-gradient(135deg, {{ $categoria->color }} 0%, #1E40AF 100%);">
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
                        <div class="text-center p-4" 
                             style="background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(59, 130, 246, 0.05) 100%);">
                            
                            <div class="mb-3">
                                <div class="badge d-inline-block px-4 py-3 shadow-lg" 
                                     style="background-color: {{ $categoria->color }}; color: white; font-size: 1.2em; min-width: 220px; border-radius: 12px;">
                                    <i class="ri {{ $categoria->icono }} fa-3x d-block mb-2"></i>
                                    <span class="d-block fw-bold" style="letter-spacing: 1px;">{{ $categoria->nombre }}</span>
                                </div>
                            </div>
                            
                            @if($categoria->descripcion)
                            <div class="mx-3 mt-3 p-3 rounded" 
                                 style="background-color: rgba(0,0,0,0.03);">
                                <p class="text-muted small mb-0 fst-italic">
                                    <i class="fas fa-quote-left me-2 opacity-50"></i>{{ Str::limit($categoria->descripcion, 100) }}
                                </p>
                            </div>
                            @endif
                        </div>
                        
                        <div class="card-footer bg-light py-3">
                            <div class="row text-center">
                                <div class="col-6">
                                    <small class="text-muted d-block mb-1">Estado:</small>
                                    <span class="badge badge-{{ $categoria->activo ? 'success' : 'secondary' }}">
                                        {{ $categoria->activo ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block mb-1">Orden:</small>
                                    <strong>{{ $categoria->orden ?? 0 }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
