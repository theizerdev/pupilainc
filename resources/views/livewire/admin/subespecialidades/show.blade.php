<div>
    {{-- Header Visual con Color e Ícono --}}
    <div class="card shadow-lg border-0 mb-4" 
         style="background: linear-gradient(135deg, {{ $subespecialidad->color }} 0%, rgba(0,0,0,0.2) 100%); border-radius: 16px;">
        <div class="card-body text-white p-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-white bg-opacity-25 rounded-circle p-3" 
                             style="width: 70px; height: 70px; display: flex; align-items: center; justify-content: center; backdrop-filter: blur(10px);">
                            <i class="fas {{ $subespecialidad->icono ?? 'fa-stethoscope' }}" 
                               style="font-size: 2.5rem;"></i>
                        </div>
                        <div>
                            <h2 class="mb-1 fw-bold">{{ $subespecialidad->nombre }}</h2>
                            <p class="mb-0 opacity-75">
                                <i class="fas fa-code me-1"></i>{{ $subespecialidad->codigo }}
                                <span class="mx-2">•</span>
                                <i class="fas fa-sitemap me-1"></i>{{ $subespecialidad->especialidad->nombre ?? 'N/A' }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <div class="btn-group" role="group">
                        @can('edit subespecialidades')
                            <a href="{{ route('admin.subespecialidades.edit', $subespecialidad->id) }}" 
                               class="btn btn-light btn-lg shadow-sm">
                                <i class="fas fa-edit me-2"></i>Editar
                            </a>
                        @endcan
                        <a href="{{ route('admin.subespecialidades.index') }}" 
                           class="btn btn-outline-light btn-lg">
                            <i class="fas fa-arrow-left me-2"></i>Volver
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Stats Cards con Información Principal --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-0 h-100" style="border-radius: 12px; transition: transform 0.2s;"
                 onmouseover="this.style.transform='translateY(-5px)'" 
                 onmouseout="this.style.transform='translateY(0)'">
                <div class="card-body text-center p-3">
                    <div class="mb-2">
                        <i class="fas fa-hand-holding-usd fa-2x" style="color: #10b981;"></i>
                    </div>
                    <small class="text-muted d-block mb-1">Costo Consulta</small>
                    <h4 class="mb-0 fw-bold" style="color: #10b981;">
                        {{ format_money($subespecialidad->costo_consulta) }}
                    </h4>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0 h-100" style="border-radius: 12px; transition: transform 0.2s;"
                 onmouseover="this.style.transform='translateY(-5px)'" 
                 onmouseout="this.style.transform='translateY(0)'">
                <div class="card-body text-center p-3">
                    <div class="mb-2">
                        <i class="fas fa-clock fa-2x" style="color: #3b82f6;"></i>
                    </div>
                    <small class="text-muted d-block mb-1">Duración</small>
                    <h4 class="mb-0 fw-bold" style="color: #3b82f6;">
                        {{ $subespecialidad->duracion_consulta }} min
                    </h4>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0 h-100" style="border-radius: 12px; transition: transform 0.2s;"
                 onmouseover="this.style.transform='translateY(-5px)'" 
                 onmouseout="this.style.transform='translateY(0)'">
                <div class="card-body text-center p-3">
                    <div class="mb-2">
                        <i class="fas fa-calendar-check fa-2x" style="color: #f59e0b;"></i>
                    </div>
                    <small class="text-muted d-block mb-1">Cita Previa</small>
                    <h4 class="mb-0 fw-bold" style="color: #f59e0b;">
                        {{ $subespecialidad->requiere_cita_previa ? 'Sí' : 'No' }}
                    </h4>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0 h-100" style="border-radius: 12px; transition: transform 0.2s;"
                 onmouseover="this.style.transform='translateY(-5px)'" 
                 onmouseout="this.style.transform='translateY(0)'">
                <div class="card-body text-center p-3">
                    <div class="mb-2">
                        <i class="fas fa-toggle-on fa-2x" style="color: {{ $subespecialidad->status ? '#10b981' : '#6b7280' }};"></i>
                    </div>
                    <small class="text-muted d-block mb-1">Estado</small>
                    <h4 class="mb-0 fw-bold" style="color: {{ $subespecialidad->status ? '#10b981' : '#6b7280' }};">
                        {{ $subespecialidad->status ? 'Activo' : 'Inactivo' }}
                    </h4>
                </div>
            </div>
        </div>
    </div>

    {{-- Información Detallada --}}
    <div class="row g-3 mb-4">
        {{-- Columna Izquierda - Información Organizacional --}}
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100" style="border-radius: 12px;">
                <div class="card-header bg-white py-3" style="border-bottom: 2px solid #e5e7eb; border-radius: 12px 12px 0 0 !important;">
                    <h6 class="mb-0 fw-bold text-primary">
                        <i class="fas fa-building me-2"></i>Información Organizacional
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item px-0 py-3">
                            <div class="d-flex align-items-start gap-3">
                                <div class="bg-primary bg-opacity-10 rounded-circle p-2" style="min-width: 40px;">
                                    <i class="fas fa-sitemap text-primary"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <small class="text-muted d-block mb-1">Especialidad Principal</small>
                                    <strong class="fs-6">{{ $subespecialidad->especialidad->nombre ?? 'N/A' }}</strong>
                                </div>
                            </div>
                        </div>

                        <div class="list-group-item px-0 py-3">
                            <div class="d-flex align-items-start gap-3">
                                <div class="bg-success bg-opacity-10 rounded-circle p-2" style="min-width: 40px;">
                                    <i class="fas fa-building text-success"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <small class="text-muted d-block mb-1">Empresa</small>
                                    <strong class="fs-6">{{ $subespecialidad->empresa->razon_social ?? 'N/A' }}</strong>
                                </div>
                            </div>
                        </div>

                        <div class="list-group-item px-0 py-3">
                            <div class="d-flex align-items-start gap-3">
                                <div class="bg-info bg-opacity-10 rounded-circle p-2" style="min-width: 40px;">
                                    <i class="fas fa-map-marker-alt text-info"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <small class="text-muted d-block mb-1">Sucursal</small>
                                    <strong class="fs-6">{{ $subespecialidad->sucursal->nombre ?? 'N/A' }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Columna Derecha - Descripción --}}
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100" style="border-radius: 12px;">
                <div class="card-header bg-white py-3" style="border-bottom: 2px solid #e5e7eb; border-radius: 12px 12px 0 0 !important;">
                    <h6 class="mb-0 fw-bold text-primary">
                        <i class="fas fa-align-left me-2"></i>Descripción
                    </h6>
                </div>
                <div class="card-body p-4">
                    @if($subespecialidad->descripcion)
                        <p class="mb-0 text-secondary" style="line-height: 1.8;">
                            {{ $subespecialidad->descripcion }}
                        </p>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-file-alt fa-3x text-muted mb-3 opacity-25"></i>
                            <p class="text-muted mb-0">Sin descripción registrada</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Timeline de Auditoría --}}
    <div class="card shadow-sm border-0" style="border-radius: 12px;">
        <div class="card-header bg-white py-3" style="border-bottom: 2px solid #e5e7eb; border-radius: 12px 12px 0 0 !important;">
            <h6 class="mb-0 fw-bold text-primary">
                <i class="fas fa-history me-2"></i>Línea de Tiempo
            </h6>
        </div>
        <div class="card-body p-4">
            <div class="timeline">
                {{-- Creación --}}
                <div class="position-relative pb-4">
                    <div class="d-flex gap-3">
                        <div class="bg-success rounded-circle d-flex align-items-center justify-content-center" 
                             style="width: 40px; height: 40px; min-width: 40px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                            <i class="fas fa-plus text-white"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="card border-0 shadow-sm" style="border-radius: 12px; background: #f9fafb;">
                                <div class="card-body p-3">
                                    <h6 class="fw-bold mb-1 text-success">
                                        <i class="fas fa-flag me-1"></i>Creada
                                    </h6>
                                    <p class="mb-1 text-dark">
                                        {{ $subespecialidad->created_at->format('d/m/Y H:i') }}
                                    </p>
                                    <small class="text-muted">
                                        <i class="fas fa-user me-1"></i>{{ $subespecialidad->user->name ?? 'Sistema' }}
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- Línea conectora --}}
                    <div class="position-absolute" 
                         style="left: 19px; top: 40px; bottom: 0; width: 2px; background: linear-gradient(to bottom, #10b981, #e5e7eb);"></div>
                </div>

                {{-- Última Actualización --}}
                <div class="position-relative pt-2">
                    <div class="d-flex gap-3">
                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" 
                             style="width: 40px; height: 40px; min-width: 40px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); z-index: 1;">
                            <i class="fas fa-sync-alt text-white"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="card border-0 shadow-sm" style="border-radius: 12px; background: #f9fafb;">
                                <div class="card-body p-3">
                                    <h6 class="fw-bold mb-1 text-primary">
                                        <i class="fas fa-edit me-1"></i>Última Actualización
                                    </h6>
                                    <p class="mb-0 text-dark">
                                        {{ $subespecialidad->updated_at->format('d/m/Y H:i') }}
                                    </p>
                                    <small class="text-muted">
                                        <i class="fas fa-clock me-1"></i>Hace {{ $subespecialidad->updated_at->diffForHumans() }}
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Acciones Rápidas --}}
    <div class="mt-4 text-center">
        <div class="btn-group" role="group">
            @can('edit subespecialidades')
                <a href="{{ route('admin.subespecialidades.edit', $subespecialidad->id) }}" 
                   class="btn btn-primary btn-lg px-4 shadow-sm">
                    <i class="fas fa-edit me-2"></i>Editar Subespecialidad
                </a>
            @endcan
            
            @can('delete subespecialidades')
                <button type="button" 
                        class="btn btn-outline-danger btn-lg px-4"
                        onclick="confirmDelete('{{ $subespecialidad->id }}')">
                    <i class="fas fa-trash-alt me-2"></i>Eliminar
                </button>
            @endcan
        </div>
    </div>

   
</div>

@push('scripts')
<script>
function confirmDelete(id) {
    if (confirm('¿Está seguro de que desea eliminar esta subespecialidad?\n\nEsta acción no se puede deshacer.')) {
        document.getElementById('delete-form').submit();
    }
}
</script>
@endpush