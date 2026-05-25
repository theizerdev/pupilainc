<div>
    @section('title', 'Plantilla de Consulta')

    @push('styles')
    <style>
        .dashboard-card {
            background: #fff;
            border-radius: 0.75rem;
            border: 1px solid rgba(0,0,0,.08);
            padding: 1.5rem;
            transition: all 0.2s;
            height: 100%;
        }
        .dashboard-card:hover {
            box-shadow: 0 4px 16px rgba(0,0,0,.1);
            transform: translateY(-2px);
        }
        .progress-indicator {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }
        .progress-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #e5e7eb;
            position: relative;
        }
        .progress-dot.completed {
            background: #10b981;
        }
        .progress-dot.active {
            background: #3b82f6;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        .module-icon {
            width: 64px;
            height: 64px;
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            margin-bottom: 1rem;
        }
        .badge-status {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            font-weight: 600;
            font-size: 0.85rem;
        }
    </style>
    @endpush

    <div class="mb-4">
        <h2 class="mb-2">Configuración de Plantilla - {{ $especialidad->nombre }}</h2>
        <p class="text-muted mb-0">Configura los pasos, estados y campos personalizados para las consultas de esta especialidad</p>
    </div>

    {{-- Barra de Progreso --}}
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Progreso de Configuración</h5>
                <span class="badge bg-primary">{{ $this->getProgresoPorcentaje() }}% completado</span>
            </div>

            <div class="progress" style="height: 8px;">
                <div class="progress-bar bg-success" role="progressbar"
                     style="width: {{ $this->getProgresoPorcentaje() }}%"
                     aria-valuenow="{{ $this->getProgresoPorcentaje() }}"
                     aria-valuemin="0"
                     aria-valuemax="100">
                </div>
            </div>

            <div class="progress-indicator mt-3">
                <div class="progress-dot {{ $tienePasos ? 'completed' : ($this->getProgresoActual() == 1 ? 'active' : '') }}">
                    @if($tienePasos)<i class="ri-check-line" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); color: white; font-size: 10px;"></i>@endif
                </div>
                <small class="text-muted">Pasos</small>

                <div style="flex: 1; height: 2px; background: #e5e7eb; margin: 0 0.5rem;"></div>

                <div class="progress-dot {{ $tieneEstados ? 'completed' : ($this->getProgresoActual() == 2 ? 'active' : '') }}">
                    @if($tieneEstados)<i class="ri-check-line" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); color: white; font-size: 10px;"></i>@endif
                </div>
                <small class="text-muted">Estados</small>

                <div style="flex: 1; height: 2px; background: #e5e7eb; margin: 0 0.5rem;"></div>

                <div class="progress-dot {{ $tieneSecciones ? 'completed' : ($this->getProgresoActual() == 3 ? 'active' : '') }}">
                    @if($tieneSecciones)<i class="ri-check-line" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); color: white; font-size: 10px;"></i>@endif
                </div>
                <small class="text-muted">Secciones</small>

                <div style="flex: 1; height: 2px; background: #e5e7eb; margin: 0 0.5rem;"></div>

                <div class="progress-dot {{ $tieneFormulariosEstado ? 'completed' : ($this->getProgresoActual() == 4 ? 'active' : '') }}">
                    @if($tieneFormulariosEstado)<i class="ri-check-line" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); color: white; font-size: 10px;"></i>@endif
                </div>
                <small class="text-muted">Formularios</small>
            </div>
        </div>
    </div>

    {{-- Módulos --}}
    <div class="row g-4">
        {{-- Módulo 1: Pasos --}}
        <div class="col-md-6 col-lg-3">
            <a href="{{ route('admin.especialidades.plantilla.pasos', $especialidad) }}" class="text-decoration-none">
                <div class="dashboard-card">
                    <div class="module-icon" style="background: #dbeafe; color: #3b82f6;">
                        <i class="ri-footprints-line"></i>
                    </div>

                    <h5 class="mb-2">Pasos de Consulta</h5>
                    <p class="text-muted small mb-3">Define el flujo de la consulta médica (signos vitales, evaluación, tratamiento, etc.)</p>

                    @if($tienePasos)
                        <div class="badge-status" style="background: #d1fae5; color: #065f46;">
                            <i class="ri-check-circle-fill"></i>
                            Configurado
                        </div>
                    @else
                        <div class="badge-status" style="background: #fef3c7; color: #92400e;">
                            <i class="ri-alert-line"></i>
                            Pendiente
                        </div>
                    @endif
                </div>
            </a>
        </div>

        {{-- Módulo 2: Estados --}}
        <div class="col-md-6 col-lg-3">
            <a href="{{ route('admin.especialidades.plantilla.estados', $especialidad) }}" class="text-decoration-none">
                <div class="dashboard-card">
                    <div class="module-icon" style="background: #fce7f3; color: #ec4899;">
                        <i class="ri-flow-chart"></i>
                    </div>

                    <h5 class="mb-2">Estados del Flujo</h5>
                    <p class="text-muted small mb-3">Configura los estados por los que pasa una cita (programada, en espera, finalizada, etc.)</p>

                    @if($tieneEstados)
                        <div class="badge-status" style="background: #d1fae5; color: #065f46;">
                            <i class="ri-check-circle-fill"></i>
                            Configurado
                        </div>
                    @else
                        <div class="badge-status" style="background: #fef3c7; color: #92400e;">
                            <i class="ri-alert-line"></i>
                            Pendiente
                        </div>
                    @endif
                </div>
            </a>
        </div>

        {{-- Módulo 3: Secciones --}}
        <div class="col-md-6 col-lg-3">
            <a href="{{ route('admin.especialidades.plantilla.secciones', $especialidad) }}" class="text-decoration-none">
                <div class="dashboard-card">
                    <div class="module-icon" style="background: #e0e7ff; color: #6366f1;">
                        <i class="ri-layout-column-line"></i>
                    </div>

                    <h5 class="mb-2">Secciones y Campos</h5>
                    <p class="text-muted small mb-3">Crea secciones personalizadas con campos dinámicos para la evaluación principal</p>

                    @if($tieneSecciones)
                        <div class="badge-status" style="background: #d1fae5; color: #065f46;">
                            <i class="ri-check-circle-fill"></i>
                            Configurado
                        </div>
                    @else
                        <div class="badge-status" style="background: #fef3c7; color: #92400e;">
                            <i class="ri-alert-line"></i>
                            Pendiente
                        </div>
                    @endif
                </div>
            </a>
        </div>

        {{-- Módulo 4: Formularios por Estado --}}
        <div class="col-md-6 col-lg-3">
            <a href="{{ route('admin.especialidades.plantilla.formularios-estado', $especialidad) }}" class="text-decoration-none">
                <div class="dashboard-card">
                    <div class="module-icon" style="background: #fef3c7; color: #f59e0b;">
                        <i class="ri-file-list-3-line"></i>
                    </div>

                    <h5 class="mb-2">Formularios por Estado</h5>
                    <p class="text-muted small mb-3">Agrega campos específicos que aparecen según el estado actual de la consulta</p>

                    @if($tieneFormulariosEstado)
                        <div class="badge-status" style="background: #d1fae5; color: #065f46;">
                            <i class="ri-check-circle-fill"></i>
                            Configurado
                        </div>
                    @else
                        <div class="badge-status" style="background: #fef3c7; color: #92400e;">
                            <i class="ri-alert-line"></i>
                            Pendiente
                        </div>
                    @endif
                </div>
            </a>
        </div>
    </div>

    {{-- Información Adicional --}}
    <div class="card mt-4">
        <div class="card-body">
            <h5 class="mb-3"><i class="ri-information-line me-2"></i>¿Cómo funciona?</h5>

            <div class="row">
                <div class="col-md-6">
                    <div class="d-flex mb-3">
                        <div class="me-3">
                            <span class="badge bg-primary rounded-circle p-2">1</span>
                        </div>
                        <div>
                            <strong>Pasos:</strong> Define qué etapas tendrá la consulta (ej: signos vitales → cuestionario → evaluación → tratamiento)
                        </div>
                    </div>

                    <div class="d-flex mb-3">
                        <div class="me-3">
                            <span class="badge bg-primary rounded-circle p-2">2</span>
                        </div>
                        <div>
                            <strong>Estados:</strong> Configura los estados de la cita (programada, en sala de espera, en consultorio, finalizada)
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="d-flex mb-3">
                        <div class="me-3">
                            <span class="badge bg-primary rounded-circle p-2">3</span>
                        </div>
                        <div>
                            <strong>Secciones:</strong> Crea grupos de campos personalizados para capturar información específica de la especialidad
                        </div>
                    </div>

                    <div class="d-flex mb-3">
                        <div class="me-3">
                            <span class="badge bg-primary rounded-circle p-2">4</span>
                        </div>
                        <div>
                            <strong>Formularios por Estado:</strong> Agrega campos que solo aparecen cuando la cita está en un estado específico
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
