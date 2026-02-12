<div>
    |<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card card-primary card-outline">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-history text-primary mr-2"></i>
                        <h3 class="card-title mb-0">Registro de Actividades</h3>
                        <span class="badge badge-secondary ml-2">{{ number_format($activities->total()) }} actividades</span>
                    </div>
                    <div class="card-tools">
                        <div class="btn-group" role="group">
                            <button class="btn btn-success btn-sm dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-download"></i> Exportar
                            </button>
                            <div class="dropdown-menu dropdown-menu-right">
                                <button wire:click="export('csv')" class="dropdown-item">
                                    <i class="fas fa-file-csv text-success"></i> Exportar CSV
                                </button>
                                <button wire:click="export('json')" class="dropdown-item">
                                    <i class="fas fa-file-code text-warning"></i> Exportar JSON
                                </button>
                                <button wire:click="export('xml')" class="dropdown-item">
                                    <i class="fas fa-file-code text-info"></i> Exportar XML
                                </button>
                            </div>
                        </div>
                        
                        @if(count($selectedActivities) > 0)
                            <button wire:click="deleteSelected" class="btn btn-danger btn-sm" onclick="return confirm('¿Está seguro de eliminar {{ count($selectedActivities) }} actividades?')">
                                <i class="fas fa-trash"></i> Eliminar seleccionados ({{ count($selectedActivities) }})
                            </button>
                        @endif
                    </div>
                </div>

                <div class="card-body p-0">
                    <!-- Filtros superiores -->
                    <div class="p-3 border-bottom bg-light">
                        <div class="row">
                            <div class="col-lg-4 col-md-6 mb-2">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    </div>
                                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Buscar usuario, acción, modelo...">
                                    @if($search)
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-secondary" wire:click="$set('search', '')">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="col-lg-2 col-md-6 mb-2">
                                <select wire:model.live="userFilter" class="form-control select2">
                                    <option value="">Todos los usuarios</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="col-lg-2 col-md-6 mb-2">
                                <select wire:model.live="actionFilter" class="form-control">
                                    <option value="">Todas las acciones</option>
                                    @foreach($actions as $actionValue => $actionLabel)
                                        <option value="{{ $actionValue }}">{{ $actionLabel }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="col-lg-2 col-md-6 mb-2">
                                <select wire:model.live="dateRange" class="form-control">
                                    <option value="">Todo el tiempo</option>
                                    <option value="today">Hoy</option>
                                    <option value="yesterday">Ayer</option>
                                    <option value="last7days">Últimos 7 días</option>
                                    <option value="week">Esta semana</option>
                                    <option value="last30days">Últimos 30 días</option>
                                    <option value="month">Este mes</option>
                                </select>
                            </div>
                            
                            <div class="col-lg-2 col-md-6 mb-2">
                                <select wire:model.live="doctorFilter" class="form-control select2">
                                    <option value="">Todos los médicos</option>
                                    @foreach($medicos as $medico)
                                        <option value="{{ $medico->id }}">{{ $medico->nombres }} {{ $medico->apellidos }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="col-lg-2 col-md-6 mb-2">
                                <div class="form-check mt-1">
                                    <input class="form-check-input" type="checkbox" id="onlyCitaEstados" wire:model.live="onlyCitaEstados">
                                    <label class="form-check-label" for="onlyCitaEstados">
                                        Solo cambios de estado de citas
                                    </label>
                                </div>
                            </div>
                            
                            <div class="col-lg-2 col-md-6 mb-2">
                                <button wire:click="clearFilters" class="btn btn-outline-secondary btn-block">
                                    <i class="fas fa-broom"></i> Limpiar filtros
                                </button>
                            </div>
                        </div>
                        
                        @if($search || $userFilter || $actionFilter || $dateRange || $subjectTypeFilter || $doctorFilter || $onlyCitaEstados)
                            <div class="row mt-2">
                                <div class="col-12">
                                    <div class="d-flex flex-wrap gap-2">
                                        <span class="text-muted">Filtros activos:</span>
                                        @if($search)
                                            <span class="badge badge-primary">Búsqueda: "{{ $search }}"</span>
                                        @endif
                                        @if($userFilter)
                                            <span class="badge badge-info">Usuario: {{ $users->find($userFilter)->name ?? 'Desconocido' }}</span>
                                        @endif
                                        @if($actionFilter)
                                            <span class="badge badge-warning">Acción: {{ $actions[$actionFilter] ?? $actionFilter }}</span>
                                        @endif
                                        @if($dateRange)
                                            <span class="badge badge-success">Rango: {{ ucfirst($dateRange) }}</span>
                                        @endif
                                        @if($subjectTypeFilter)
                                            <span class="badge badge-secondary">Modelo: {{ class_basename($subjectTypeFilter) }}</span>
                                        @endif
                                        @if($doctorFilter)
                                            <span class="badge badge-info">Médico: {{ optional($medicos->firstWhere('id', (int) $doctorFilter))->nombres }} {{ optional($medicos->firstWhere('id', (int) $doctorFilter))->apellidos }}</span>
                                        @endif
                                        @if($onlyCitaEstados)
                                            <span class="badge badge-dark">Solo estados de citas</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                <!-- Tabla de actividades mejorada -->
               <div class="table-responsive">
                   <table class="table table-hover mb-0">
                       <thead class="thead-light">
                           <tr>
                               <th class="text-center" width="40">
                                   <input type="checkbox" wire:model.live="selectAll" class="form-check-input">
                               </th>
                               <th>
                                   <a href="#" wire:click.prevent="sort('created_at')" class="text-dark text-decoration-none">
                                       Fecha y Hora
                                       @if($sortBy === 'created_at')
                                           <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                       @else
                                           <i class="fas fa-sort text-muted"></i>
                                       @endif
                                   </a>
                               </th>
                               <th>
                                   <a href="#" wire:click.prevent="sort('causer_id')" class="text-dark text-decoration-none">
                                       Usuario
                                       @if($sortBy === 'causer_id')
                                           <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                       @else
                                           <i class="fas fa-sort text-muted"></i>
                                       @endif
                                   </a>
                               </th>
                               <th>
                                   <a href="#" wire:click.prevent="sort('description')" class="text-dark text-decoration-none">
                                       Acción
                                       @if($sortBy === 'description')
                                           <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                       @else
                                           <i class="fas fa-sort text-muted"></i>
                                       @endif
                                   </a>
                               </th>
                               <th>Elemento</th>
                               <th class="text-center">IP</th>
                               <th class="text-center">Detalles</th>
                           </tr>
                       </thead>
                       <tbody>
                           @forelse($activities as $activity)
                               <tr class="{{ in_array($activity->id, $selectedActivities) ? 'table-primary' : '' }}">
                                   <td class="text-center">
                                       <input type="checkbox" wire:model.live="selectedActivities" value="{{ $activity->id }}" class="form-check-input">
                                   </td>
                                   <td>
                                       <div class="d-flex flex-column">
                                           <span class="font-weight-medium">{{ $activity->created_at->format('d/m/Y') }}</span>
                                           <small class="text-muted">
                                               <i class="fas fa-clock mr-1"></i>{{ $activity->created_at->format('H:i:s') }}
                                           </small>
                                       </div>
                                   </td>
                                   <td>
                                       @if($activity->causer)
                                           <div class="d-flex align-items-center">
                                               <img src="{{ $activity->causer->profile_photo_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($activity->causer->name) . '&background=4f46e5&color=fff' }}" 
                                                    class="rounded-circle mr-2" width="32" height="32" alt="{{ $activity->causer->name }}">
                                               <div>
                                                   <div class="font-weight-medium text-truncate" style="max-width: 150px;">{{ $activity->causer->name }}</div>
                                                   <small class="text-muted text-truncate" style="max-width: 150px;">{{ $activity->causer->email }}</small>
                                               </div>
                                           </div>
                                       @else
                                           <span class="badge badge-secondary">
                                               <i class="fas fa-robot mr-1"></i>Sistema
                                           </span>
                                       @endif
                                   </td>
                                   <td>
                                       <span class="badge badge-{{ $this->getActionColor($activity->description) }} font-weight-normal">
                                           <i class="fas fa-{{ match($activity->description) {
                                               'created' => 'plus',
                                               'updated' => 'edit',
                                               'deleted' => 'trash',
                                               'restored' => 'undo',
                                               'force-deleted' => 'trash-alt',
                                               'login' => 'sign-in-alt',
                                               'logout' => 'sign-out-alt',
                                               'password-updated' => 'key',
                                               'profile-updated' => 'user-edit',
                                               default => 'circle'
                                           } }}"></i>
                                           {{ ucfirst($activity->description) }}
                                       </span>
                                   </td>
                                   <td>
                                       @if($activity->subject)
                                           <div class="d-flex align-items-center">
                                               <div class="avatar avatar-xs me-2">
                                                   <span class="avatar-initial rounded bg-label-secondary">
                                                       {{ substr($activity->subject_type, 0, 1) }}
                                                   </span>
                                               </div>
                                               <div>
                                                   <div class="fw-medium">{{ class_basename($activity->subject_type) }}</div>
                                                   <small class="text-muted">
                                                       @if(isset($activity->subject->name))
                                                           {{ $activity->subject->name }}
                                                       @elseif(isset($activity->subject->nombres))
                                                           {{ $activity->subject->nombres }}
                                                       @elseif(isset($activity->subject->razon_social))
                                                           {{ $activity->subject->razon_social }}
                                                       @else
                                                           ID: {{ $activity->subject_id }}
                                                       @endif
                                                   </small>
                                               </div>
                                           </div>
                                       @else
                                           <span class="text-muted">No especificado</span>
                                       @endif
                                   </td>
                                   <td class="text-center">
                                       <small class="text-muted" data-toggle="tooltip" title="Dirección IP">
                                           <i class="fas fa-map-marker-alt mr-1"></i>{{ $activity->properties->get('ip_address', 'N/A') }}
                                       </small>
                                   </td>
                                   <td class="text-center">
                                       @if($activity->properties->count() > 0)
                                           <button class="btn btn-sm btn-outline-primary" 
                                                   data-bs-toggle="modal"
                                                   data-bs-target="#activityDetailsModal{{ $activity->id }}"
                                                   title="Ver detalles">
                                               <i class="fas fa-eye"></i>
                                           </button>

                                           <!-- Modal de detalles mejorado -->
                                           <div class="modal fade" id="activityDetailsModal{{ $activity->id }}" tabindex="-1">
                                               <div class="modal-dialog modal-lg">
                                                   <div class="modal-content">
                                                       <div class="modal-header bg-primary text-white">
                                                           <h5 class="modal-title">
                                                               <i class="fas fa-info-circle mr-2"></i>Detalles de la Actividad
                                                           </h5>
                                                           <button type="button" class="close text-white" data-bs-dismiss="modal">
                                                               <span aria-hidden="true">&times;</span>
                                                           </button>
                                                       </div>
                                                       <div class="modal-body">
                                                           <div class="row mb-3">
                                                               <div class="col-md-6">
                                                                   <div class="card card-outline card-primary h-100">
                                                                       <div class="card-header">
                                                                           <strong><i class="fas fa-user mr-1"></i>Usuario</strong>
                                                                       </div>
                                                                       <div class="card-body py-2">
                                                                           <p class="mb-0">
                                                                               @if($activity->causer)
                                                                                   <strong>{{ $activity->causer->name }}</strong><br>
                                                                                   <small class="text-muted">{{ $activity->causer->email }}</small>
                                                                               @else
                                                                                   <span class="badge badge-secondary">Sistema</span>
                                                                               @endif
                                                                           </p>
                                                                       </div>
                                                                   </div>
                                                               </div>
                                                               <div class="col-md-6">
                                                                   <div class="card card-outline card-info h-100">
                                                                       <div class="card-header">
                                                                           <strong><i class="fas fa-tasks mr-1"></i>Acción</strong>
                                                                       </div>
                                                                       <div class="card-body py-2">
                                                                           <p class="mb-0">
                                                                               <span class="badge badge-{{ $this->getActionColor($activity->description) }}">
                                                                                   {{ ucfirst($activity->description) }}
                                                                               </span>
                                                                           </p>
                                                                       </div>
                                                                   </div>
                                                               </div>
                                                           </div>
                                                           
                                                           <div class="row mb-3">
                                                               <div class="col-md-6">
                                                                   <div class="card card-outline card-success h-100">
                                                                       <div class="card-header">
                                                                           <strong><i class="fas fa-calendar mr-1"></i>Fecha</strong>
                                                                       </div>
                                                                       <div class="card-body py-2">
                                                                           <p class="mb-0">
                                                                               <strong>{{ $activity->created_at->format('d/m/Y H:i:s') }}</strong><br>
                                                                               <small class="text-muted">{{ $activity->created_at->diffForHumans() }}</small>
                                                                           </p>
                                                                       </div>
                                                                   </div>
                                                               </div>
                                                               <div class="col-md-6">
                                                                   <div class="card card-outline card-warning h-100">
                                                                       <div class="card-header">
                                                                           <strong><i class="fas fa-map-marker-alt mr-1"></i>Dirección IP</strong>
                                                                       </div>
                                                                       <div class="card-body py-2">
                                                                           <p class="mb-0">
                                                                               <code>{{ $activity->properties->get('ip_address', 'N/A') }}</code>
                                                                           </p>
                                                                       </div>
                                                                   </div>
                                                               </div>
                                                           </div>
                                                           
                                                           <div class="row">
                                                               <div class="col-12">
                                                                   <div class="card card-outline card-secondary">
                                                                       <div class="card-header">
                                                                           <strong><i class="fas fa-cog mr-1"></i>Propiedades adicionales</strong>
                                                                       </div>
                                                                       <div class="card-body">
                                                                           @if($activity->properties->has('attributes'))
                                                                               <h6 class="text-primary">Cambios realizados:</h6>
                                                                               <div class="table-responsive">
                                                                                   <table class="table table-bordered table-sm">
                                                                                       <thead class="thead-light">
                                                                                           <tr>
                                                                                               <th>Campo</th>
                                                                                               <th>Valor</th>
                                                                                               @if($activity->properties->has('old'))
                                                                                                   <th>Valor anterior</th>
                                                                                               @endif
                                                                                           </tr>
                                                                                       </thead>
                                                                                       <tbody>
                                                                                           @foreach($activity->properties['attributes'] as $key => $value)
                                                                                               <tr>
                                                                                                   <td><strong>{{ $key }}</strong></td>
                                                                                                   <td>
                                                                                                       @if(is_array($value) || is_object($value))
                                                                                                           <pre class="mb-0 small">{{ json_encode($value, JSON_PRETTY_PRINT) }}</pre>
                                                                                                       @else
                                                                                                           {{ $value }}
                                                                                                       @endif
                                                                                                   </td>
                                                                                                   @if($activity->properties->has('old'))
                                                                                                       <td>
                                                                                                           @if(isset($activity->properties['old'][$key]))
                                                                                                               @if(is_array($activity->properties['old'][$key]) || is_object($activity->properties['old'][$key]))
                                                                                                                   <pre class="mb-0 small">{{ json_encode($activity->properties['old'][$key], JSON_PRETTY_PRINT) }}</pre>
                                                                                                               @else
                                                                                                                   {{ $activity->properties['old'][$key] }}
                                                                                                               @endif
                                                                                                           @else
                                                                                                               <span class="text-muted">No existía</span>
                                                                                                           @endif
                                                                                                       </td>
                                                                                                   @endif
                                                                                               </tr>
                                                                                           @endforeach
                                                                                       </tbody>
                                                                                   </table>
                                                                               </div>
                                                                           @else
                                                                               <p class="text-muted">No hay propiedades adicionales</p>
                                                                           @endif
                                                                       </div>
                                                                   </div>
                                                               </div>
                                                           </div>
                                                       </div>
                                                       <div class="modal-footer">
                                                           <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                               <i class="fas fa-times mr-1"></i>Cerrar
                                                           </button>
                                                       </div>
                                                   </div>
                                               </div>
                                           </div>
                                       @else
                                           <span class="text-muted">Sin detalles</span>
                                       @endif
                                   </td>
                               </tr>
                           @empty
                               <tr>
                                   <td colspan="8" class="text-center text-muted py-5">
                                       <div class="py-4">
                                           <i class="fas fa-history fa-3x mb-3 text-muted"></i>
                                           <h5 class="text-muted">No hay actividades registradas</h5>
                                           <p class="text-muted mb-0">No se encontraron actividades con los filtros actuales.</p>
                                           <button wire:click="clearFilters" class="btn btn-outline-primary btn-sm mt-3">
                                               <i class="fas fa-broom mr-1"></i>Limpiar filtros
                                           </button>
                                       </div>
                                   </td>
                               </tr>
                           @endforelse
                       </tbody>
                   </table>
               </div>

                <!-- Paginación y estadísticas mejoradas -->
                <div class="card-footer d-flex justify-content-between align-items-center bg-light">
                    <div class="d-flex align-items-center">
                        <span class="text-muted mr-3">
                            Mostrando {{ $activities->firstItem() ?? 0 }} a {{ $activities->lastItem() ?? 0 }} de {{ number_format($activities->total()) }} actividades
                        </span>
                        <select wire:model.live="perPage" class="form-control form-control-sm" style="width: auto;">
                            <option value="10">10 por página</option>
                            <option value="25">25 por página</option>
                            <option value="50">50 por página</option>
                            <option value="100">100 por página</option>
                        </select>
                    </div>
                    <div>
                        {{ $activities->links('livewire.pagination') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('livewire:init', function () {
        // Escuchar eventos de Livewire para mostrar notificaciones
        Livewire.on('activityDeleted', function () {
            toastr.success('Actividad eliminada correctamente');
        });
        
        Livewire.on('filtersCleared', function () {
            toastr.success('Filtros limpiados correctamente');
        });
        
        Livewire.on('activitiesExported', function (format) {
            toastr.success(`Actividades exportadas en formato ${format} correctamente`);
        });
        
        Livewire.on('activitiesDeleted', function (count) {
            toastr.success(`${count} actividades eliminadas correctamente`);
        });
        
        Livewire.on('noActivitiesToExport', function () {
            toastr.warning('No hay actividades para exportar con los filtros actuales');
        });
    });

    // Función para confirmar eliminación masiva
    function confirmBulkDelete() {
        if (confirm('¿Estás seguro de que deseas eliminar las actividades seleccionadas? Esta acción no se puede deshacer.')) {
            Livewire.dispatch('bulkDeleteActivities');
        }
    }
    
    // Inicializar tooltips
    $(document).ready(function() {
        $('[data-toggle="tooltip"]').tooltip();
    });
</script>
@endpush
</div>
