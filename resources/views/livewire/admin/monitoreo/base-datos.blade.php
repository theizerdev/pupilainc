<div wire:poll.10s="$dispatch('refresh-base-datos')">
    <!-- Header profesional -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-gradient-info text-white border-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm me-3">
                                <div class="avatar-initial bg-white bg-opacity-20 rounded">
                                    <i class="ri ri-database-2-line ri-20px text-white"></i>
                                </div>
                            </div>
                            <div>
                                <h4 class="mb-0 text-white">Monitoreo de Base de Datos</h4>
                                <small class="text-white-50">Estado y rendimiento de la base de datos</small>
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="d-flex align-items-center">
                                <div class="spinner-border spinner-border-sm text-white me-2" role="status" style="width: 12px; height: 12px;">
                                    <span class="visually-hidden">Actualizando...</span>
                                </div>
                                <small class="text-white-75">{{ $lastUpdate }}</small>
                            </div>
                            <div class="d-flex align-items-center mt-1">
                                <span class="badge bg-success bg-opacity-20 text-success border border-success border-opacity-20">
                                    <i class="ri ri-database-2-line me-1"></i>{{ $dbInfo['connection'] }} {{ $dbInfo['version'] }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Métricas principales mejoradas -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="avatar">
                            <div class="avatar-initial bg-primary bg-opacity-10 text-primary rounded">
                                <i class="ri ri-database-line ri-24px"></i>
                            </div>
                        </div>
                        <span class="badge bg-primary bg-opacity-10 text-primary">Activa</span>
                    </div>
                    <div>
                        <h4 class="mb-1 text-primary">{{ $dbInfo['database'] }}</h4>
                        <p class="text-muted mb-2">Base de Datos Principal</p>
                        <div class="d-flex align-items-center">
                            <i class="ri ri-shield-check-line text-success me-1"></i>
                            <small class="text-success">Conexión estable</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="avatar">
                            <div class="avatar-initial bg-success bg-opacity-10 text-success rounded">
                                <i class="ri ri-table-line ri-24px"></i>
                            </div>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="ri ri-more-2-line"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#"><i class="ri ri-refresh-line me-2"></i>Actualizar</a></li>
                                <li><a class="dropdown-item" href="#"><i class="ri ri-file-list-line me-2"></i>Ver todas</a></li>
                            </ul>
                        </div>
                    </div>
                    <div>
                        <h4 class="mb-1 text-success">{{ format_money($dbInfo['total_tables']) }}</h4>
                        <p class="text-muted mb-2">Total de Tablas</p>
                        <div class="progress" style="height: 4px;">
                            <div class="progress-bar bg-success" style="width: 100%"></div>
                        </div>
                        <small class="text-muted">Todas operativas</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="avatar">
                            <div class="avatar-initial {{ $dbInfo['total_size'] > 1000 ? 'bg-warning bg-opacity-10 text-warning' : 'bg-info bg-opacity-10 text-info' }} rounded">
                                <i class="ri ri-hard-drive-2-line ri-24px"></i>
                            </div>
                        </div>
                        <span class="badge {{ $dbInfo['total_size'] > 1000 ? 'bg-warning bg-opacity-10 text-warning' : 'bg-info bg-opacity-10 text-info' }}">
                            {{ $dbInfo['total_size'] > 1000 ? 'Grande' : 'Normal' }}
                        </span>
                    </div>
                    <div>
                        <h4 class="mb-1 {{ $dbInfo['total_size'] > 1000 ? 'text-warning' : 'text-info' }}">{{ format_money($dbInfo['total_size'], 1) }} MB</h4>
                        <p class="text-muted mb-2">Tamaño Total</p>
                        <div class="progress" style="height: 4px;">
                            <div class="progress-bar {{ $dbInfo['total_size'] > 1000 ? 'bg-warning' : 'bg-info' }}" style="width: {{ min(($dbInfo['total_size'] / 2000) * 100, 100) }}%"></div>
                        </div>
                        <small class="text-muted">Crecimiento normal</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="avatar">
                            <div class="avatar-initial bg-secondary bg-opacity-10 text-secondary rounded">
                                <i class="ri ri-server-line ri-24px"></i>
                            </div>
                        </div>
                        <span class="badge bg-secondary bg-opacity-10 text-secondary">{{ strtoupper($dbInfo['connection']) }}</span>
                    </div>
                    <div>
                        <h4 class="mb-1 text-secondary">{{ ucfirst($dbInfo['connection']) }}</h4>
                        <p class="text-muted mb-2">Motor de Base de Datos</p>
                        <div class="progress" style="height: 4px;">
                            <div class="progress-bar bg-secondary" style="width: 100%"></div>
                        </div>
                        <small class="text-muted">Versión: {{ explode('-', $dbInfo['version'])[0] ?? 'N/A' }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de estadísticas mejorada -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light border-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm me-3">
                                <div class="avatar-initial bg-primary bg-opacity-10 text-primary rounded">
                                    <i class="ri ri-table-line ri-18px"></i>
                                </div>
                            </div>
                            <div>
                                <h5 class="mb-0">Estadísticas de Tablas</h5>
                                <small class="text-muted">Rendimiento y uso de almacenamiento</small>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-outline-primary">
                                <i class="ri ri-refresh-line me-1"></i>Actualizar
                            </button>
                            <button class="btn btn-sm btn-outline-success">
                                <i class="ri ri-download-line me-1"></i>Exportar
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="border-0 ps-4">
                                        <div class="d-flex align-items-center">
                                            <i class="ri ri-table-line text-muted me-2"></i>
                                            Tabla
                                        </div>
                                    </th>
                                    <th class="border-0">
                                        <div class="d-flex align-items-center">
                                            <i class="ri ri-file-list-line text-muted me-2"></i>
                                            Registros
                                        </div>
                                    </th>
                                    <th class="border-0">
                                        <div class="d-flex align-items-center">
                                            <i class="ri ri-hard-drive-line text-muted me-2"></i>
                                            Tamaño (MB)
                                        </div>
                                    </th>
                                    <th class="border-0">
                                        <div class="d-flex align-items-center">
                                            <i class="ri ri-settings-line text-muted me-2"></i>
                                            Motor
                                        </div>
                                    </th>
                                    <th class="border-0 pe-4">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($tableStats->take(15) as $index => $table)
                                <tr class="{{ $index % 2 == 0 ? 'bg-light bg-opacity-50' : '' }}">
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-xs me-2">
                                                <div class="avatar-initial bg-primary bg-opacity-10 text-primary rounded">
                                                    <i class="ri ri-table-line ri-14px"></i>
                                                </div>
                                            </div>
                                            <code class="text-primary">{{ $table['name'] }}</code>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="fw-medium">{{ format_money($table['rows']) }}</span>
                                            @if($table['rows'] > 10000)
                                                <span class="badge bg-warning bg-opacity-10 text-warning ms-2">Alto</span>
                                            @elseif($table['rows'] > 1000)
                                                <span class="badge bg-info bg-opacity-10 text-info ms-2">Medio</span>
                                            @else
                                                <span class="badge bg-success bg-opacity-10 text-success ms-2">Bajo</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="fw-medium">{{ format_money($table['size'], 2) }}</span>
                                            <div class="progress ms-2" style="width: 60px; height: 4px;">
                                                <div class="progress-bar {{ $table['size'] > 50 ? 'bg-danger' : ($table['size'] > 10 ? 'bg-warning' : 'bg-success') }}" 
                                                     style="width: {{ min(($table['size'] / 100) * 100, 100) }}%"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary">
                                            <i class="ri ri-database-line me-1"></i>{{ $table['engine'] }}
                                        </span>
                                    </td>
                                    <td class="pe-4">
                                        <span class="badge bg-success bg-opacity-10 text-success">
                                            <i class="ri ri-checkbox-circle-line me-1"></i>Activa
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($tableStats->count() > 15)
                    <div class="card-footer bg-light border-0 text-center">
                        <small class="text-muted">
                            Mostrando 15 de {{ $tableStats->count() }} tablas. 
                            <a href="#" class="text-primary">Ver todas las tablas</a>
                        </small>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
