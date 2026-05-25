<div>
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h3 class="mb-1">Configuración de Wizard de Consultas</h3>
            <p class="text-muted mb-0">
                Controla si cada especialidad muestra el flujo de consulta por pasos (wizard) o formulario completo en el estado "En Consultorio".
            </p>
        </div>
    </div>

    <!-- Estadísticas -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-start border-primary border-3">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="mdi mdi-view-dashboard fs-1 text-primary"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-0">Total Especialidades</h6>
                            <h3 class="mb-0">{{ $totalEspecialidades }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-start border-success border-3">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="mdi mdi-steps fs-1 text-success"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-0">Con Wizard</h6>
                            <h3 class="mb-0 text-success">{{ $conWizard }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-start border-warning border-3">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="mdi mdi-form-select fs-1 text-warning"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-0">Con Formulario</h6>
                            <h3 class="mb-0 text-warning">{{ $conFormulario }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="mdi mdi-check-circle me-2"></i>
            {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Barra de herramientas -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-center">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="mdi mdi-magnify"></i>
                        </span>
                        <input type="text"
                               class="form-control"
                               placeholder="Buscar especialidad..."
                               wire:model.live="search">
                    </div>
                </div>
                <div class="col-md-6 text-end">
                    <button wire:click="aplicarRecomendacion('wizard')"
                            class="btn btn-success me-2">
                        <i class="mdi mdi-steps me-1"></i>Todas con Wizard
                    </button>
                    <button wire:click="aplicarRecomendacion('formulario')"
                            class="btn btn-warning me-2">
                        <i class="mdi mdi-form-select me-1"></i>Todas con Formulario
                    </button>
                    <button wire:click="guardarConfiguracion"
                            wire:loading.attr="disabled"
                            class="btn btn-primary">
                        <span wire:loading.remove>
                            <i class="mdi mdi-content-save me-2"></i>Guardar Todo
                        </span>
                        <span wire:loading>
                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                            Guardando...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de Especialidades -->
    <div class="card">
        <div class="card-header bg-light">
            <h5 class="card-title mb-0">
                <i class="mdi mdi-hospital-building me-2"></i>
                Especialidades Configuradas
            </h5>
        </div>
        <div class="card-body p-0">
            @if(count($especialidades) > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th style="width: 50px;"></th>
                                <th>Especialidad</th>
                                <th>Código</th>
                                <th style="width: 200px;">Modo de Visualización</th>
                                <th style="width: 150px;" class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($especialidades as $especialidad)
                                <tr>
                                    <td>
                                        <div class="rounded-circle d-flex align-items-center justify-content-center"
                                             style="width: 40px; height: 40px; background-color: {{ $especialidad['color'] }};">
                                            <i class="fas {{ $especialidad['icono'] }} text-white"></i>
                                        </div>
                                    </td>
                                    <td>
                                        <strong>{{ $especialidad['nombre'] }}</strong>
                                        @if(!$especialidad['tiene_plantilla'])
                                            <br>
                                            <small class="text-danger">
                                                <i class="mdi mdi-alert-circle me-1"></i>Sin plantilla configurada
                                            </small>
                                        @endif
                                    </td>
                                    <td>
                                        <code>{{ $especialidad['codigo'] }}</code>
                                    </td>
                                    <td>
                                        @if($especialidad['usar_wizard_en_consultorio'])
                                            <span class="badge bg-success">
                                                <i class="mdi mdi-steps me-1"></i>Wizard por Pasos
                                            </span>
                                            <br>
                                            <small class="text-muted">Signos → Cuestionario → Evaluación → Estudios → Tratamiento → Reposo</small>
                                        @else
                                            <span class="badge bg-warning text-dark">
                                                <i class="mdi mdi-form-select me-1"></i>Formulario Completo
                                            </span>
                                            <br>
                                            <small class="text-muted">Formulario único del estado "En Consultorio"</small>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($especialidad['tiene_plantilla'])
                                            <div class="form-check form-switch d-inline-block">
                                                <input class="form-check-input"
                                                       type="checkbox"
                                                       id="wizard_{{ $especialidad['id'] }}"
                                                       {{ $especialidad['usar_wizard_en_consultorio'] ? 'checked' : '' }}
                                                       wire:change="toggleWizard({{ $especialidad['id'] }})">
                                                <label class="form-check-label" for="wizard_{{ $especialidad['id'] }}">
                                                    {{ $especialidad['usar_wizard_en_consultorio'] ? 'Activado' : 'Desactivado' }}
                                                </label>
                                            </div>
                                        @else
                                            <button class="btn btn-sm btn-outline-secondary" disabled>
                                                <i class="mdi mdi-lock me-1"></i>No disponible
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="mdi mdi-hospital-building-off fs-1 text-muted"></i>
                    <h5 class="mt-3 text-muted">No se encontraron especialidades</h5>
                    <p class="text-muted">Intenta con otro término de búsqueda</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Información -->
    <div class="alert alert-info mt-4" role="alert">
        <div class="d-flex">
            <div class="flex-shrink-0">
                <i class="mdi mdi-information-outline fs-4"></i>
            </div>
            <div class="flex-grow-1 ms-3">
                <h6 class="alert-heading">¿Cómo funciona?</h6>
                <ul class="mb-0">
                    <li><strong>Wizard por Pasos:</strong> El médico completa la consulta paso a paso (Signos Vitales → Cuestionario → Evaluación → Estudios → Tratamientos → Reposo). Ideal para consultas estructuradas.</li>
                    <li><strong>Formulario Completo:</strong> Se muestra un único formulario con todas las secciones del estado "En Consultorio". Ideal para consultas rápidas o flexibles.</li>
                    <li>La configuración se aplica automáticamente cuando una consulta cambia al estado "En Consultorio".</li>
                    <li>Puedes cambiar la configuración en cualquier momento y se aplicará a las nuevas consultas.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<style>
    .form-check-input:checked {
        background-color: var(--bs-success);
        border-color: var(--bs-success);
    }
    .table tbody tr:hover {
        background-color: rgba(0, 0, 0, 0.02);
    }
</style>
