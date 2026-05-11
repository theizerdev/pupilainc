<div>
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h3 class="mb-1">Configuración de Notificaciones</h3>
            <p class="text-muted mb-0">
                Controla qué notificaciones se envían a pacientes y doctores cuando cambian los estados de las citas.
            </p>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="mdi mdi-check-circle me-2"></i>
            {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Pacientes -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <i class="mdi mdi-account-multiple me-2 fs-4"></i>
                <h5 class="card-title mb-0 text-white">Notificaciones a Pacientes</h5>
            </div>
            <div>
                <button wire:click="aplicarRecomendacion('paciente')"
                        class="btn btn-sm btn-light me-2">
                    <i class="mdi mdi-star me-1"></i>Recomendado
                </button>
                <button wire:click="activarTodas('paciente')"
                        class="btn btn-sm btn-success me-2">
                    <i class="mdi mdi-check-all me-1"></i>Activar Todas
                </button>
                <button wire:click="desactivarTodas('paciente')"
                        class="btn btn-sm btn-danger">
                    <i class="mdi mdi-close-circle me-1"></i>Desactivar Todas
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="row g-3">
                @foreach($estadosDisponibles as $estado)
                    <div class="col-md-6 col-lg-4">
                        <div class="form-check form-switch p-3 border rounded hover-shadow">
                            <input class="form-check-input"
                                   type="checkbox"
                                   id="paciente_{{ $estado }}"
                                   wire:model="configPaciente.{{ $estado }}">
                            <label class="form-check-label w-100" for="paciente_{{ $estado }}">
                                <strong>{{ $estadoLabels[$estado] ?? ucfirst($estado) }}</strong>
                                <br>
                                <small class="text-muted">
                                    Estado: <code>{{ $estado }}</code>
                                </small>
                            </label>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Doctores -->
    <div class="card mb-4">
        <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <i class="mdi mdi-doctor me-2 fs-4"></i>
                <h5 class="card-title mb-0 text-white">Notificaciones a Doctores</h5>
            </div>
            <div>
                <button wire:click="aplicarRecomendacion('doctor')"
                        class="btn btn-sm btn-light me-2">
                    <i class="mdi mdi-star me-1"></i>Recomendado
                </button>
                <button wire:click="activarTodas('doctor')"
                        class="btn btn-sm btn-success me-2">
                    <i class="mdi mdi-check-all me-1"></i>Activar Todas
                </button>
                <button wire:click="desactivarTodas('doctor')"
                        class="btn btn-sm btn-danger">
                    <i class="mdi mdi-close-circle me-1"></i>Desactivar Todas
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="row g-3">
                @foreach($estadosDisponibles as $estado)
                    <div class="col-md-6 col-lg-4">
                        <div class="form-check form-switch p-3 border rounded hover-shadow">
                            <input class="form-check-input"
                                   type="checkbox"
                                   id="doctor_{{ $estado }}"
                                   wire:model="configDoctor.{{ $estado }}">
                            <label class="form-check-label w-100" for="doctor_{{ $estado }}">
                                <strong>{{ $estadoLabels[$estado] ?? ucfirst($estado) }}</strong>
                                <br>
                                <small class="text-muted">
                                    Estado: <code>{{ $estado }}</code>
                                </small>
                            </label>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Info Box -->
    <div class="alert alert-info" role="alert">
        <div class="d-flex">
            <div class="flex-shrink-0">
                <i class="mdi mdi-information-outline fs-4"></i>
            </div>
            <div class="flex-grow-1 ms-3">
                <h6 class="alert-heading">Información Importante</h6>
                <ul class="mb-0">
                    <li>La configuración recomendada está optimizada para evitar saturación de notificaciones.</li>
                    <li>Los estados internos del flujo clínico (enfermería, consultorio, etc.) no generan notificaciones por defecto.</li>
                    <li>Solo se notifican eventos importantes: confirmaciones, cancelaciones y pagos.</li>
                    <li>Puedes personalizar la configuración según las necesidades de tu clínica.</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Save Button -->
    <div class="d-flex justify-content-end">
        <button wire:click="guardarConfiguracion"
                wire:loading.attr="disabled"
                class="btn btn-primary btn-lg">
            <span wire:loading.remove>
                <i class="mdi mdi-content-save me-2"></i>Guardar Configuración
            </span>
            <span wire:loading>
                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                Guardando...
            </span>
        </button>
    </div>

    <style>
        .hover-shadow:hover {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            transition: box-shadow 0.15s ease-in-out;
        }
        .form-check-input:checked {
            background-color: var(--bs-primary);
            border-color: var(--bs-primary);
        }
        </style>

</div>
