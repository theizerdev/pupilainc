<div>
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-edit me-2"></i>Editar Subespecialidad Médica</h1>
                <p class="text-muted">Modifique la información de la subespecialidad #{{ $subespecialidad->id }}</p>
            </div>
            <div>
                <a href="{{ route('admin.subespecialidades.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Volver al Listado
                </a>
            </div>
        </div>

        <!-- Form -->
        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-edit me-2"></i>Información Básica</h6>
                    </div>
                    <div class="card-body">
                        <form wire:submit.prevent="update">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="nombre" class="fw-bold">
                                            <i class="fas fa-signature me-1"></i>Nombre *
                                        </label>
                                        <input type="text" 
                                               class="form-control form-control-lg @error('nombre') is-invalid @enderror" 
                                               id="nombre" 
                                               wire:model.live="nombre" 
                                               placeholder="Ej: Cirugía Refractiva, Retina..."
                                               autocomplete="off">
                                        @error('nombre')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                        <small class="form-text text-muted">Nombre descriptivo de la subespecialidad</small>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="codigo" class="fw-bold">
                                            <i class="fas fa-barcode me-1"></i>Código *
                                        </label>
                                        <input type="text" 
                                               class="form-control form-control-lg @error('codigo') is-invalid @enderror" 
                                               id="codigo" 
                                               wire:model.live="codigo" 
                                               autocomplete="off">
                                        @error('codigo')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                        <small class="form-text text-muted">Código único de identificación</small>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">
                            <h6 class="mb-3"><i class="fas fa-sitemap me-2"></i>Especialidad Relacionada</h6>
                            <div class="form-group mb-3">
                                <label for="especialidad_id" class="fw-bold">Especialidad Principal *</label>
                                <select class="form-control form-control-lg @error('especialidad_id') is-invalid @enderror" 
                                        id="especialidad_id" 
                                        wire:model.live="especialidad_id">
                                    <option value="">Seleccione una especialidad</option>
                                    @foreach($especialidades as $especialidad)
                                        <option value="{{ $especialidad->id }}">
                                            <i class="fas {{ $especialidad->icono }}"></i> {{ $especialidad->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('especialidad_id')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                                <small class="form-text text-muted">Especialidad a la que pertenece esta subespecialidad</small>
                            </div>

                            @if(auth()->user()->hasRole('Super Administrador'))
                            <hr class="my-4">
                            <h6 class="mb-3"><i class="fas fa-building me-2"></i>Asignación (Solo Super Admin)</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="empresa_id" class="fw-bold">Empresa *</label>
                                        <select class="form-control @error('empresa_id') is-invalid @enderror" 
                                                id="empresa_id" 
                                                wire:model.live="empresa_id">
                                            <option value="">Seleccione una empresa</option>
                                            @foreach($empresas as $empresa)
                                                <option value="{{ $empresa->id }}">{{ $empresa->razon_social }}</option>
                                            @endforeach
                                        </select>
                                        @error('empresa_id')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="sucursal_id" class="fw-bold">Sucursal *</label>
                                        <select class="form-control @error('sucursal_id') is-invalid @enderror" 
                                                id="sucursal_id" 
                                                wire:model.live="sucursal_id" 
                                                @if(!$empresa_id) disabled @endif>
                                            <option value="">Seleccione una sucursal</option>
                                            @foreach($sucursales as $sucursal)
                                                <option value="{{ $sucursal->id }}">{{ $sucursal->nombre }}</option>
                                            @endforeach
                                        </select>
                                        @error('sucursal_id')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            @else
                            <div class="alert alert-info d-flex align-items-start" role="alert">
                                <i class="fas fa-info-circle fa-2x me-3 mt-1"></i>
                                <div>
                                    <strong>Información de asignación:</strong><br>
                                    Esta subespecialidad pertenece a:
                                    <ul class="mb-0 mt-2">
                                        <li><strong>Empresa:</strong> {{ $subespecialidad->empresa->razon_social ?? 'No definida' }}</li>
                                        <li><strong>Sucursal:</strong> {{ $subespecialidad->sucursal->nombre ?? 'No definida' }}</li>
                                    </ul>
                                </div>
                            </div>
                            @endif

                            <hr class="my-4">
                            <h6 class="mb-3"><i class="fas fa-palette me-2"></i>Apariencia</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="color" class="fw-bold">Color de Identificación *</label>
                                        <div class="input-group">
                                            <input type="color" 
                                                   class="form-control form-control-color" 
                                                   id="color" 
                                                   wire:model.live="color"
                                                   style="width: 60px; height: 46px;">
                                            <input type="text" 
                                                   class="form-control form-control-lg" 
                                                   wire:model.live="color" 
                                                   readonly>
                                        </div>
                                        @error('color')
                                            <span class="invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
                                        <small class="form-text text-muted">Color distintivo para esta subespecialidad</small>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="icono" class="fw-bold">Icono Representativo *</label>
                                        <select class="form-control @error('icono') is-invalid @enderror" 
                                                id="icono" 
                                                wire:model.live="icono">
                                            <optgroup label="🩺 Especialidades Médicas">
                                                <option value="fa-stethoscope">Estetoscopio - Medicina General</option>
                                                <option value="fa-user-doctor">Médico Especialista</option>
                                                <option value="fa-heartbeat">Cardiología</option>
                                                <option value="fa-lungs">Neumología</option>
                                                <option value="fa-brain">Neurología</option>
                                                <option value="fa-eye">Oftalmología</option>
                                                <option value="fa-tooth">Odontología</option>
                                                <option value="fa-bone">Ortopedia</option>
                                                <option value="fa-baby">Pediatría</option>
                                            </optgroup>
                                            <optgroup label="🔬 Diagnóstico y Tratamiento">
                                                <option value="fa-flask">Laboratorio Clínico</option>
                                                <option value="fa-microscope">Patología</option>
                                                <option value="fa-x-ray">Radiología e Imagen</option>
                                                <option value="fa-pills">Farmacología</option>
                                                <option value="fa-syringe">Vacunación</option>
                                                <option value="fa-hand-holding-medical">Terapia</option>
                                                <option value="fa-crutch">Rehabilitación Física</option>
                                            </optgroup>
                                            <optgroup label="🏥 Instalaciones">
                                                <option value="fa-hospital">Hospital General</option>
                                                <option value="fa-hospital-user">Consulta Externa</option>
                                                <option value="fa-hospital-symbol">Emergencias</option>
                                                <option value="fa-ambulance">Servicio de Urgencias</option>
                                                <option value="fa-procedures">Hospitalización</option>
                                                <option value="fa-clinic-medical">Clínica Especializada</option>
                                            </optgroup>
                                            <optgroup label="📋 Gestión Médica">
                                                <option value="fa-file-medical">Historia Clínica</option>
                                                <option value="fa-notes-medical">Recetas Médicas</option>
                                                <option value="fa-user-injured">Traumatología</option>
                                                <option value="fa-first-aid">Primeros Auxilios</option>
                                                <option value="fa-medkit">Botiquín de Emergencia</option>
                                                <option value="fa-heart">Cardiología</option>
                                            </optgroup>
                                        </select>
                                        @error('icono')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">
                            <h6 class="mb-3"><i class="fas fa-align-left me-2"></i>Detalles Adicionales</h6>
                            <div class="form-group">
                                <label for="descripcion" class="fw-bold">Descripción</label>
                                <textarea class="form-control @error('descripcion') is-invalid @enderror" 
                                          id="descripcion" 
                                          wire:model.live="descripcion" 
                                          rows="4" 
                                          placeholder="Describa los servicios y características de esta subespecialidad..."></textarea>
                                @error('descripcion')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                                <small class="form-text text-muted">Descripción opcional pero recomendada</small>
                            </div>

                            <hr class="my-4">
                            <h6 class="mb-3"><i class="fas fa-cog me-2"></i>Configuración</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group p-3 border rounded">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" 
                                                   class="custom-control-input" 
                                                   id="requiere_cita_previa" 
                                                   wire:model.live="requiere_cita_previa">
                                            <label class="custom-control-label fw-bold" for="requiere_cita_previa">
                                                <i class="fas fa-calendar-alt me-1"></i>Requiere cita previa
                                            </label>
                                        </div>
                                        <small class="text-muted d-block mt-2">
                                            <i class="fas fa-info-circle me-1"></i>
                                            {{ $requiere_cita_previa ? 'Los pacientes necesitarán agendar cita previamente' : 'Se puede atender sin cita previa' }}
                                        </small>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group p-3 border rounded">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" 
                                                   class="custom-control-input" 
                                                   id="status" 
                                                   wire:model.live="status">
                                            <label class="custom-control-label fw-bold" for="status">
                                                <i class="fas fa-power-off me-1"></i>Activo
                                            </label>
                                        </div>
                                        <small class="text-muted d-block mt-2">
                                            <i class="fas fa-info-circle me-1"></i>
                                            {{ $status ? 'Visible y disponible para uso' : 'Oculto y no disponible' }}
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <a href="{{ route('admin.subespecialidades.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times"></i> Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save"></i> Actualizar Subespecialidad
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Preview Panel -->
            <div class="col-lg-4">
                <!-- Live Preview Card - Enhanced -->
                <div class="card shadow-sm border-0 sticky-top" style="top: 20px; z-index: 100; border-radius: 15px; overflow: hidden;">
                    <!-- Animated Header -->
                    <div class="card-header text-white py-3" 
                         style="background: linear-gradient(135deg, {{ $color ?: '#3B82F6' }} 0%, #1E40AF 100%); transition: all 0.3s ease;">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="m-0">
                                <i class="fas fa-eye me-2"></i>Vista Previa
                            </h6>
                            <span class="badge bg-white text-primary" style="font-size: 0.75rem;">
                                <i class="fas fa-bolt me-1"></i>En Vivo
                            </span>
                        </div>
                    </div>
                    
                    <div class="card-body p-0">
                        <!-- Hero Preview Section -->
                        <div class="text-center p-4" 
                             style="background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(59, 130, 246, 0.05) 100%);">
                            
                            <!-- Animated Badge Preview -->
                            <div class="mb-3" style="transition: transform 0.3s ease;" 
                                 onmouseover="this.style.transform='scale(1.1)'" 
                                 onmouseout="this.style.transform='scale(1)'">
                                <div class="badge d-inline-block px-4 py-3 shadow-lg" 
                                     style="background-color: {{ $color ?: '#3B82F6' }}; color: white; font-size: 1.2em; min-width: 220px; border-radius: 12px; transition: all 0.3s ease;">
                                    <i class="fas {{ $icono ?: 'fa-stethoscope' }} fa-3x d-block mb-2" 
                                       style="transition: transform 0.3s ease;"></i>
                                    <span class="d-block fw-bold" style="letter-spacing: 1px;">{{ $codigo }}</span>
                                </div>
                            </div>
                            
                            <!-- Name Display -->
                            <h4 class="mb-2 fw-bold" style="color: {{ $color ?: '#3B82F6' }}; min-height: 36px; transition: color 0.3s ease;">
                                {{ $nombre ?: 'Nombre de la Subespecialidad' }}
                            </h4>
                            
                            <!-- Parent Specialty Info -->
                            @if($especialidad_id)
                                @foreach($especialidades as $esp)
                                    @if($esp->id == $especialidad_id)
                                        <p class="text-muted small mb-2">
                                            <i class="fas {{ $esp->icono }} me-1"></i> {{ $esp->nombre }}
                                        </p>
                                        @break
                                    @endif
                                @endforeach
                            @endif
                            
                            <!-- Description Preview -->
                            @if(strlen($descripcion ?: '') > 0)
                            <div class="mx-3 mt-3 p-3 rounded" 
                                 style="background-color: rgba(0,0,0,0.03); min-height: 80px;">
                                <p class="text-muted small mb-0 fst-italic">
                                    <i class="fas fa-quote-left me-2 opacity-50"></i>
                                    {{ Str::limit($descripcion, 120) }}
                                </p>
                            </div>
                            @else
                            <p class="text-muted small mb-3 opacity-50">
                                <i class="fas fa-align-left me-1"></i>Sin descripción
                            </p>
                            @endif
                        </div>
                        
                        <!-- Status Cards Grid -->
                        <div class="row g-0 border-top border-bottom">
                            <div class="col-6 border-end">
                                <div class="p-3 text-center" style="transition: background-color 0.2s ease;"
                                     onmouseover="this.style.backgroundColor='rgba(0,0,0,0.02)'" 
                                     onmouseout="this.style.backgroundColor='transparent'">
                                    <small class="text-muted d-block mb-2">
                                        <i class="fas fa-toggle-on me-1"></i>Estado
                                    </small>
                                    <span class="badge {{ $status ? 'bg-success' : 'bg-secondary' }} px-3 py-2" 
                                          style="transition: all 0.3s ease;">
                                        <i class="fas {{ $status ? 'fa-check-circle' : 'fa-times-circle' }} me-1"></i>
                                        {{ $status ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-3 text-center" style="transition: background-color 0.2s ease;"
                                     onmouseover="this.style.backgroundColor='rgba(0,0,0,0.02)'" 
                                     onmouseout="this.style.backgroundColor='transparent'">
                                    <small class="text-muted d-block mb-2">
                                        <i class="fas fa-calendar-check me-1"></i>Cita Previa
                                    </small>
                                    <span class="badge {{ $requiere_cita_previa ? 'bg-info' : 'bg-secondary' }} px-3 py-2" 
                                          style="transition: all 0.3s ease;">
                                        <i class="fas {{ $requiere_cita_previa ? 'fa-check' : 'fa-times' }} me-1"></i>
                                        {{ $requiere_cita_previa ? 'Requerida' : 'No requerida' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Super Admin Info -->
                        @if(auth()->user()->hasRole('Super Administrador'))
                        <div class="p-3 bg-light">
                            <small class="fw-bold text-muted d-block mb-2">
                                <i class="fas fa-building me-1"></i>Asignación
                            </small>
                            <div class="small">
                                <div class="mb-2">
                                    <i class="fas fa-building text-primary me-2"></i>
                                    <span class="text-dark">{{ $empresa_id ? ($empresas->find($empresa_id)->razon_social ?? 'N/A') : 'No asignada' }}</span>
                                </div>
                                <div>
                                    <i class="fas fa-map-marker-alt text-danger me-2"></i>
                                    <span class="text-dark">{{ $sucursal_id ? ($sucursales->find($sucursal_id)->nombre ?? 'N/A') : 'No asignada' }}</span>
                                </div>
                            </div>
                        </div>
                        @endif
                        
                        <!-- Visual Impact Preview -->
                        <div class="p-3">
                            <small class="fw-bold text-muted d-block mb-2">
                                <i class="fas fa-image me-1"></i>Impacto Visual
                            </small>
                            <div class="rounded p-2" style="background-color: #f8f9fa;">
                                <!-- Calendar View Simulation -->
                                <div class="border rounded p-2 mb-2" style="background: white;">
                                    <div class="d-flex align-items-center" 
                                         style="background-color: {{ $color ?: '#3B82F6' }}; color: white; padding: 8px; border-radius: 6px;">
                                        <i class="fas {{ $icono ?: 'fa-stethoscope' }} me-2"></i>
                                        <small class="fw-bold">{{ $nombre ?: 'Subespecialidad' }}</small>
                                    </div>
                                </div>
                                <!-- List View Simulation -->
                                <div class="border rounded p-2" style="background: white;">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle me-2 d-flex align-items-center justify-content-center" 
                                                 style="width: 32px; height: 32px; background-color: {{ $color ?: '#3B82F6' }}; color: white;">
                                                <i class="fas {{ $icono ?: 'fa-stethoscope' }}" style="font-size: 14px;"></i>
                                            </div>
                                            <small>{{ $nombre ?: 'Subespecialidad' }}</small>
                                        </div>
                                        <span class="badge {{ $status ? 'bg-success' : 'bg-secondary' }}" style="font-size: 0.7rem;">
                                            {{ $status ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Stats Footer -->
                        <div class="p-3 bg-light border-top">
                            <div class="row text-center">
                                <div class="col-6 border-end">
                                    <small class="text-muted d-block">ID</small>
                                    <strong class="h5 mb-0">#{{ $subespecialidad->id }}</strong>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">Creada</small>
                                    <strong class="h5 mb-0">{{ $subespecialidad->created_at->format('d/m/Y') }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>