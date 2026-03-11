<div id="calendario-general-component">
    @push('scripts')
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('paciente-creado', (event) => {
                const data = event[0];
                
                // Solo cerrar el modal si fue exitoso y no hay errores de validación
                if (data.success && !data.errors) {
                    $('#modalPacienteRapido').modal('hide');
                }
                
                // Usar el sistema de toasts de la plantilla
                const toastContainer = document.querySelector('.toast-container');
                if (toastContainer) {
                    const toastHtml = `
                        <div class="bs-toast toast ${data.success ? 'bg-success' : 'bg-danger'}" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="5000">
                            <div class="toast-header">
                                <i class="ri ri-check-line me-2"></i>
                                <div class="me-auto fw-medium">${data.success ? 'Éxito' : 'Error'}</div>
                                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                            </div>
                            <div class="toast-body">
                                ${data.message}
                            </div>
                        </div>
                    `;
                    toastContainer.insertAdjacentHTML('beforeend', toastHtml);
                    const newToast = toastContainer.lastElementChild;
                    new bootstrap.Toast(newToast).show();
                }

                if (data.success && data.paciente) {
                    // Si se creó correctamente, añadirlo al select y seleccionarlo
                    const select = document.getElementById('eventPaciente');
                    const nombreMostrar = data.paciente.nickname ? `${data.paciente.nombre} (${data.paciente.nickname}) - ${data.paciente.documento_identidad || ''}` : `${data.paciente.nombre} - ${data.paciente.documento_identidad || ''}`;
                    const option = new Option(nombreMostrar, data.paciente.id, true, true);
                    select.appendChild(option);
                    $(select).trigger('change');
                }
            });
        });
    </script>
    @endpush
    @push('styles')
    <link rel="stylesheet" href="/materialize/assets/vendor/libs/select2/select2.css" />
    <link rel="stylesheet" href="/materialize/assets/vendor/libs/@form-validation/form-validation.css" />
    <link rel="stylesheet" href="/materialize/assets/vendor/libs/fullcalendar/fullcalendar.css" />
    <link rel="stylesheet" href="/materialize/assets/vendor/libs/flatpickr/flatpickr.css" />
    <link rel="stylesheet" href="/materialize/assets/vendor/css/pages/app-calendar.css" />
    <link rel="stylesheet" href="/materialize/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
    <style>
        .slot-btn { cursor: pointer; transition: all .2s; border: 1px solid #e0e0e0; }
        .slot-btn:hover { transform: scale(1.05); }
        .slot-btn.selected { border-color: var(--bs-primary); box-shadow: 0 0 0 2px var(--bs-primary); }
        .slot-btn.ocupado { opacity: .45; cursor: not-allowed; text-decoration: line-through; }
        .slots-container { max-height: 200px; overflow-y: auto; }
        .cascade-arrow { text-align: center; color: #aaa; font-size: .75rem; margin: 2px 0; }

        .fc .fc-event {
            border-radius: 4px !important;
            border-left: 4px solid transparent;
            transition: box-shadow 0.15s ease;
            overflow: visible;
        }
        .fc .fc-event:hover {
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
            z-index: 10;
        }
        .fc .fc-daygrid-event {
            padding: 1px 4px !important;
            margin-bottom: 1px !important;
        }
        .fc .fc-daygrid-event .fc-event-time {
            font-size: 0.68rem;
            font-weight: 600;
        }
        .fc .fc-daygrid-event .fc-event-title {
            font-size: 0.70rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .fc .fc-timegrid-event .fc-event-main {
            padding: 2px 5px !important;
        }
        .fc .fc-timegrid-event .fc-event-time {
            font-size: 0.70rem;
            font-weight: 600;
        }
        .fc .fc-timegrid-event .fc-event-title {
            font-size: 0.72rem;
        }
        .fc .fc-timegrid-event .fc-event-subtitle {
            font-size: 0.65rem;
            opacity: 0.75;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .fc .fc-list-event-title {
            font-size: 0.85rem;
        }
        .calendar-legend { font-size: 0.8rem; }
        .legend-dot { width: 10px; height: 10px; border-radius: 50%; display: inline-block; }
        .legend-bar { width: 4px; height: 16px; border-radius: 2px; display: inline-block; }

        .medico-item {
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 0.8rem;
            cursor: pointer;
            transition: background 0.15s;
        }
        .medico-item:hover {
            background: rgba(var(--bs-primary-rgb), 0.08);
        }
        .medico-item.active {
            background: rgba(var(--bs-primary-rgb), 0.12);
            font-weight: 600;
        }

        .fc .fc-daygrid-day-frame.fc-scrollgrid-sync-inner {
            min-height: 60px !important;
            padding: 2px 4px !important;
        }
        .fc .fc-daygrid-body-balanced .fc-daygrid-day-events {
            margin-top: 1px !important;
        }
        .fc .fc-daygrid-day-top {
            padding: 2px 4px !important;
        }
        .fc td.fc-daygrid-day {
            min-height: 60px !important;
        }
        .fc .fc-scrollgrid-sync-table {
            width: 100% !important;
        }
        .fc .fc-col-header-cell {
            padding: 4px 0 !important;
        }
        .fc .fc-daygrid-body {
            width: 100% !important;
        }
        .fc .fc-daygrid-body table {
            width: 100% !important;
        }
        .fc-event-title-container {
            min-width: 0;
            flex: 1;
        }

        /* Animación punto-agua (tipo consulta ripple) */
        .punto-agua {
            position: relative;
            width: 10px;
            height: 10px;
            flex-shrink: 0;
            margin-left: 5px;
        }
        .punto-agua::before {
            content: '';
            position: absolute;
            top: 0; left: 0;
            width: 100%;
            height: 100%;
            background-color: var(--color-punto);
            border-radius: 50%;
            z-index: 2;
        }
        .punto-agua::after {
            content: '';
            position: absolute;
            top: 0; left: 0;
            width: 100%;
            height: 100%;
            background-color: var(--color-punto);
            border-radius: 50%;
            z-index: 1;
            animation: ripple 2s infinite cubic-bezier(0, 0.2, 0.8, 1);
        }
        @keyframes ripple {
            0% { transform: scale(1); opacity: 1; }
            100% { transform: scale(2.5); opacity: 0; }
        }

        /* Event type indicators */
        .fc .fc-event .fc-event-type-badge {
            font-size: 0.55rem;
            padding: 0 4px;
            border-radius: 3px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .perfect-scrollbar-container {
            position: relative;
        }
        .perfect-scrollbar-container .ps__rail-y {
            width: 6px;
            background-color: transparent;
        }
        .perfect-scrollbar-container .ps__thumb-y {
            width: 6px;
            background-color: rgba(var(--bs-primary-rgb), 0.3);
            border-radius: 3px;
        }
        .perfect-scrollbar-container .ps__thumb-y:hover {
            background-color: rgba(var(--bs-primary-rgb), 0.5);
        }

        /* Toggle switches */
        .toggle-section {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 8px 12px;
            border-radius: 8px;
            margin-bottom: 8px;
            transition: background 0.15s;
        }
        .toggle-section:hover {
            background: rgba(var(--bs-primary-rgb), 0.04);
        }
        .toggle-section .form-check-input {
            width: 2.5em;
            height: 1.25em;
        }

        /* Tooltip custom */
        .cal-tooltip {
            position: absolute;
            z-index: 9999;
            background: var(--bs-body-bg, #fff);
            border: 1px solid var(--bs-border-color, #dee2e6);
            border-radius: 8px;
            padding: 10px 14px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.12);
            pointer-events: none;
            font-size: 0.8rem;
            max-width: 280px;
            line-height: 1.5;
        }
        .cal-tooltip .tooltip-header {
            font-weight: 600;
            margin-bottom: 4px;
            padding-bottom: 4px;
            border-bottom: 1px solid var(--bs-border-color, #dee2e6);
        }
        .cal-tooltip .tooltip-badge {
            display: inline-block;
            padding: 1px 8px;
            border-radius: 4px;
            font-size: 0.7rem;
            font-weight: 600;
            color: #fff;
        }

        /* Cita event indicator */
        .fc .fc-event.event-cita {
            border-left-color: #0d6efd !important;
        }
        .fc .fc-event.event-consulta {
            border-left-color: #6f42c1 !important;
        }

        /* Floating toggles */
        #floatingToggles {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: var(--bs-body-bg, #fff);
            border: 1px solid var(--bs-border-color, #dee2e6);
            border-radius: 12px;
            padding: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            z-index: 1000;
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.3s, transform 0.3s;
            pointer-events: none;
            min-width: 200px;
        }
        #floatingToggles.show {
            opacity: 1;
            transform: translateY(0);
            pointer-events: all;
        }
        #floatingToggles .toggle-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 8px 0;
        }
        #floatingToggles .toggle-item:not(:last-child) {
            border-bottom: 1px solid var(--bs-border-color, #dee2e6);
            margin-bottom: 8px;
            padding-bottom: 12px;
        }

        /* Sidebar animation */
        .app-calendar-sidebar {
            transition: all 0.3s ease-in-out;
            overflow: hidden;
        }
        .app-calendar-sidebar.sidebar-hidden {
            max-width: 0 !important;
            min-width: 0 !important;
            padding: 0 !important;
            border: none !important;
            opacity: 0;
        }

        /* Estilos para validación de pacientes */
        .form-control.is-invalid {
            border-color: #dc3545;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right calc(0.375em + 0.1875rem) center;
            background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
        }

        .invalid-feedback {
            display: none;
            width: 100%;
            margin-top: 0.25rem;
            font-size: 0.875em;
            color: #dc3545;
        }

        .invalid-feedback.d-block {
            display: block;
        }

        .form-floating > .form-control.is-invalid ~ label {
            color: #dc3545;
        }

        .form-floating > .form-control.is-invalid ~ label::after {
            background-color: transparent !important;
        }

        .form-floating > .form-control:focus.is-invalid {
            border-color: #dc3545;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
        }

        .form-floating > .form-control:not(:placeholder-shown).is-invalid ~ label {
            color: #dc3545;
        }

        /* Estilos específicos para mensaje de error de fecha de nacimiento */
        #mpFechaNacimiento_error {
            color: #dc3545 !important;
            font-size: 14px !important;
            margin-top: 0.25rem !important;
            margin-bottom: 0 !important;
            font-weight: 400 !important;
            line-height: 1.5 !important;
        }

        /* Estilo para botón deshabilitado */
        #modalPacienteCreateBtn:disabled {
            opacity: 0.65;
            cursor: not-allowed;
        }

        /* Toggle button in calendar header */
        #toggleSidebarBtn {
            transition: all 0.3s ease;
            opacity: 0;
            transform: scale(0.8);
            pointer-events: none;
        }
        #toggleSidebarBtn.show {
            opacity: 1;
            transform: scale(1);
            pointer-events: all;
        }

        /* Loading overlay */
        .calendar-loading {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255,255,255,0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 999;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.2s;
        }
        .calendar-loading.show {
            opacity: 1;
            pointer-events: all;
        }
    </style>
    @endpush

    <div class="card app-calendar-wrapper" wire:ignore>
        <div class="row g-0">
            <!-- Calendar Sidebar -->
            <div class="col app-calendar-sidebar border-end" id="app-calendar-sidebar">
                <div class="px-4">
                    <!-- Toggle Filtros Button -->
                    <div class="d-flex justify-content-between align-items-center mb-3 mt-3">
                        <h6 class="mb-0">Filtros</h6>
                        <button type="button" class="btn btn-sm btn-icon btn-outline-secondary" id="toggleFiltros">
                            <i class="ri ri-filter-3-line"></i>
                        </button>
                    </div>

                    <!-- Búsqueda Rápida -->
                    <div class="mb-3">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="ri ri-search-line"></i></span>
                            <input type="text" class="form-control" id="searchPaciente" placeholder="Buscar paciente...">
                        </div>
                    </div>

                    <!-- Stats -->
                    <div class="border-bottom my-sm-0 mb-4 p-3">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <small class="text-muted fw-medium">HOY</small>
                                <small class="text-muted">{{ now()->format('d/m/Y') }}</small>
                            </div>
                            <div class="row g-2">
                                <div class="col-6">
                                    <div class="text-center p-2 rounded" style="background: rgba(13, 110, 253, 0.1);">
                                        <h5 class="mb-0 text-primary">{{ $stats['citas_hoy'] }}</h5>
                                        <small class="text-muted">Citas</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="text-center p-2 rounded" style="background: rgba(111, 66, 193, 0.1);">
                                        <h5 class="mb-0" style="color: #6f42c1;">{{ $stats['consultas_hoy'] }}</h5>
                                        <small class="text-muted">Consultas</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <small class="text-muted fw-medium">SEMANA</small>
                                <small class="text-muted">{{ now()->startOfWeek()->format('d/m') }} - {{ now()->endOfWeek()->format('d/m') }}</small>
                            </div>
                            <div class="row g-2">
                                <div class="col-6">
                                    <div class="text-center p-2 rounded" style="background: rgba(13, 110, 253, 0.1);">
                                        <h5 class="mb-0 text-primary" id="citasSemana">0</h5>
                                        <small class="text-muted">Citas</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="text-center p-2 rounded" style="background: rgba(111, 66, 193, 0.1);">
                                        <h5 class="mb-0" style="color: #6f42c1;" id="consultasSemana">0</h5>
                                        <small class="text-muted">Consultas</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div>
                            <small class="text-muted fw-medium d-block mb-2">ESTADO</small>
                            <div class="row g-2">
                                <div class="col-6">
                                    <div class="d-flex align-items-center">
                                        <i class="ri-time-line text-warning me-1"></i>
                                        <small class="text-muted">Pendientes:</small>
                                        <strong class="ms-auto">{{ $stats['citas_pendientes'] }}</strong>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="d-flex align-items-center">
                                        <i class="ri-user-line text-info me-1"></i>
                                        <small class="text-muted">En espera:</small>
                                        <strong class="ms-auto">{{ $stats['consultas_en_espera'] }}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Inline Mini Calendar 
                    <div class="inline-calendar"></div>

                    <hr class="mb-4 mx-n4 mt-3" />-->

                    <!-- Filtros Container -->
                    <div id="filtrosContainer" class="perfect-scrollbar-container" style="max-height: 500px; position: relative;">
                    
                    <!-- Filtro por Especialidad -->
                    <div class="mb-3">
                        <label class="form-label fw-medium mb-2">Especialidad</label>
                        <select class="form-select form-select-sm" id="filterEspecialidad">
                            <option value="">Todas las especialidades</option>
                            @foreach($medicos->pluck('especialidades')->flatten()->unique('id') as $esp)
                                <option value="{{ $esp->id }}">{{ $esp->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <hr class="mb-4 mx-n4" />

                    <!-- Toggle Citas -->
                    <div class="toggle-section">
                        <div class="d-flex align-items-center">
                            <span class="legend-bar me-2" style="background-color: #0d6efd;"></span>
                            <h6 class="mb-0">Citas</h6>
                        </div>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input toggle-citas" type="checkbox" id="toggleCitas" checked />
                        </div>
                    </div>

                    <!-- Filtros de estados de Citas -->
                    <div class="app-calendar-events-filter ps-3 mb-3" id="filtros-citas">
                        @foreach($citaEstadoLabels as $value => $label)
                            <div class="form-check mb-2 ms-2">
                                <input class="form-check-input input-filter-cita" type="checkbox"
                                       id="select-cita-{{ $value }}"
                                       data-value="{{ $value }}"
                                       checked />
                                <label class="form-check-label d-flex align-items-center" for="select-cita-{{ $value }}">
                                    <span class="legend-dot me-2" style="background-color: {{ ['pendiente'=>'#ffc107','confirmada'=>'#0d6efd','completada'=>'#28a745','cancelada'=>'#dc3545','no_asistio'=>'#6c757d'][$value] ?? '#78909C' }};"></span>
                                    {{ $label }}
                                </label>
                            </div>
                        @endforeach
                    </div>

                    <hr class="mb-4 mx-n4" />

                    <!-- Toggle Consultas -->
                    <div class="toggle-section">
                        <div class="d-flex align-items-center">
                            <span class="legend-bar me-2" style="background-color: #6f42c1;"></span>
                            <h6 class="mb-0">Consultas</h6>
                        </div>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input toggle-consultas" type="checkbox" id="toggleConsultas" />
                        </div>
                    </div>

                    <!-- Filtros de estados de Consultas -->
                    <div class="app-calendar-events-filter ps-3 mb-3" id="filtros-consultas">
                        @foreach($consultaEstadoLabels as $value => $label)
                            <div class="form-check mb-2 ms-2">
                                <input class="form-check-input input-filter-consulta" type="checkbox"
                                       id="select-consulta-{{ $value }}"
                                       data-value="{{ $value }}"
                                       checked />
                                <label class="form-check-label d-flex align-items-center" for="select-consulta-{{ $value }}">
                                    <span class="legend-dot me-2" style="background-color: {{ $consultaEstadoColores[$value] ?? '#78909C' }};"></span>
                                    {{ $label }}
                                </label>
                            </div>
                        @endforeach
                    </div>

                    <!-- Médicos -->
                    <hr class="mb-4 mx-n4" />
                    <div class="mb-3 ms-1 d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Médicos</h6>
                        <small class="text-muted" id="medicosCount">{{ $medicos->count() }}</small>
                    </div>
                    <div id="medicos-calendario" class="ms-1" style="max-height: 200px; overflow-y: auto;">
                        @foreach($medicos as $medico)
                            <div class="medico-item d-flex justify-content-between align-items-center mb-1"
                                 data-medico-id="{{ $medico->id }}">
                                <span>{{ $medico->nombre_completo }}</span>
                                <div class="d-flex align-items-center gap-1">
                                    <div class="progress" style="width: 40px; height: 4px;">
                                        <div class="progress-bar bg-primary medico-ocupacion" data-medico-id="{{ $medico->id }}" style="width: 0%"></div>
                                    </div>
                                    <small class="text-muted medico-citas-count" data-medico-id="{{ $medico->id }}">0</small>
                                </div>
                            </div>
                        @endforeach
                        @if($medicos->isEmpty())
                            <small class="text-muted">Sin médicos activos</small>
                        @endif
                    </div>
                    
                    <!-- Alertas de Conflictos -->
                    <div id="conflictosAlert" class="mt-3 d-none">
                        <div class="alert alert-warning py-2 px-3 mb-0">
                            <div class="d-flex align-items-center">
                                <i class="ri-alert-line me-2"></i>
                                <div class="flex-grow-1">
                                    <small class="fw-medium d-block">Conflictos detectados</small>
                                    <small class="text-muted" id="conflictosCount">0 citas con conflicto de horario</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    </div>
                </div>
            </div>

            <!-- Calendar Content -->
            <div class="col app-calendar-content" style="position: relative;">
                <!-- Floating Toggles -->
                <div id="floatingToggles">
                    <div class="toggle-item">
                        <div class="d-flex align-items-center">
                            <span class="legend-bar me-2" style="background-color: #0d6efd; width: 4px; height: 16px; border-radius: 2px; display: inline-block;"></span>
                            <span class="fw-medium">Citas</span>
                        </div>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" id="floatingToggleCitas" checked />
                        </div>
                    </div>
                    <div class="toggle-item">
                        <div class="d-flex align-items-center">
                            <span class="legend-bar me-2" style="background-color: #6f42c1; width: 4px; height: 16px; border-radius: 2px; display: inline-block;"></span>
                            <span class="fw-medium">Consultas</span>
                        </div>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" id="floatingToggleConsultas" />
                        </div>
                    </div>
                </div>

                <div class="card shadow-none border-0">
                    <div class="card-body pb-0" style="position: relative;">
                        <!-- Loading Overlay -->
                        <div class="calendar-loading" id="calendarLoading">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                        </div>
                        <div id="calendar"></div>
                    </div>
                </div>
                <div class="app-overlay"></div>

                <!-- Offcanvas Detalle -->
                <div class="offcanvas offcanvas-end event-sidebar" tabindex="-1" id="calendarioDetailSidebar">
                    <div class="offcanvas-header border-bottom">
                        <h5 class="offcanvas-title" id="calendarioDetailTitle">Detalle</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
                    </div>
                    <div class="offcanvas-body" id="calendarioDetailBody">
                        <p class="text-muted">Seleccione un evento para ver los detalles</p>
                    </div>
                </div>

                <!-- Offcanvas: Crear/Editar Cita -->
                <div class="offcanvas offcanvas-end event-sidebar" tabindex="-1" id="addEventSidebar" aria-labelledby="addEventSidebarLabel">
                    <div class="offcanvas-header border-bottom">
                        <h5 class="offcanvas-title" id="addEventSidebarLabel">Nueva Cita</h5>
                        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                    </div>
                    <div class="offcanvas-body">
                        <form class="event-form pt-0" id="eventForm" onsubmit="return false">
                            <!-- 1. Paciente -->
                            <div class="mb-3">
                                <button type="button" class="btn btn-outline-primary btn-sm w-100" id="btnModalPacienteRapido" data-bs-toggle="modal" data-bs-target="#modalPacienteRapido">
                                    <i class="ri ri-user-add-line me-1"></i> Nuevo Paciente
                                </button>
                            </div>
                            <div class="form-floating form-floating-outline mb-5 form-control-validation">
                                <select class="select2 form-select" id="eventPaciente" name="eventPaciente">
                                    <option value="">Seleccionar paciente</option>
                                    @foreach($pacientes as $paciente)
                                        <option value="{{ $paciente->id }}">
                                            {{ $paciente->nombre_completo }}{{ $paciente->nickname ? " ($paciente->nickname)" : "" }} - {{ $paciente->documento_identidad }}
                                        </option>
                                    @endforeach
                                </select>
                                <label for="eventPaciente">Paciente</label>
                            </div>
                            
                            

                            <div class="cascade-arrow"><i class="ri ri-arrow-down-s-line"></i></div>

                            <!-- 2. Especialidad -->
                            <div class="form-floating form-floating-outline mb-5 form-control-validation">
                                <select class="select2 form-select" id="eventEspecialidad" name="eventEspecialidad" disabled>
                                    <option value="">Primero seleccione un paciente</option>
                                </select>
                                <label for="eventEspecialidad">Especialidad</label>
                            </div>

                            <div class="cascade-arrow"><i class="ri ri-arrow-down-s-line"></i></div>

                            <!-- 3. Subespecialidad (opcional) -->
                            <div class="form-floating form-floating-outline mb-5" id="subespecialidadContainer" style="display:none;">
                                <select class="select2 form-select" id="eventSubespecialidad" name="eventSubespecialidad">
                                    <option value="">Opcional - Seleccionar subespecialidad</option>
                                </select>
                                <label for="eventSubespecialidad">Subespecialidad (opcional)</label>
                            </div>

                            <!-- 4. Médico -->
                            <div class="form-floating form-floating-outline mb-5 form-control-validation">
                                <select class="select2 form-select" id="eventMedico" name="eventMedico" disabled>
                                    <option value="">Primero seleccione una especialidad</option>
                                </select>
                                <label for="eventMedico">Médico</label>
                            </div>

                            <div class="cascade-arrow"><i class="ri ri-arrow-down-s-line"></i></div>

                            <!-- 5. Fecha -->
                            <div class="form-floating form-floating-outline mb-5 form-control-validation">
                                <input type="text" class="form-control" id="eventFecha" name="eventFecha" placeholder="Seleccionar fecha" />
                                <label for="eventFecha">Fecha de la Cita</label>
                            </div>

                            <!-- 6. Horarios Disponibles -->
                            <div id="slotsContainer" class="mb-5" style="display:none;">
                                <label class="form-label fw-medium mb-2">Horarios Disponibles</label>
                                <div id="slotsMessage" class="alert alert-warning d-none py-2 px-3 mb-2" role="alert"></div>
                                <div id="slotsList" class="slots-container" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(110px, 1fr)); gap: 0.5rem;"></div>
                            </div>

                            <!-- Hidden: fecha_inicio / fecha_fin -->
                            <div class="form-control-validation d-none">
                                <input type="hidden" id="eventStartDate" name="eventStartDate" />
                                <input type="hidden" id="eventEndDate" name="eventEndDate" />
                            </div>

                            <!-- Tipo de Cita -->
                            <div class="form-floating form-floating-outline mb-5">
                                <select class="select2 form-select" id="eventTipoConsulta" name="eventTipoConsulta">
                                    <option value="">Sin tipo de cita</option>
                                    @foreach($tiposConsulta as $tipo)
                                        <option value="{{ $tipo->id }}" data-color="{{ $tipo->color }}">
                                            {{ $tipo->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                <label for="eventTipoConsulta">Tipo de Cita</label>
                            </div>

                            <!-- Motivo -->
                            <div class="form-floating form-floating-outline mb-5 form-control-validation">
                                <input type="text" class="form-control" id="eventMotivo" name="eventMotivo" placeholder="Motivo de la cita" />
                                <label for="eventMotivo">Motivo</label>
                            </div>

                            <!-- Estado -->
                            <div class="form-floating form-floating-outline mb-5">
                                <select class="select2 form-select" id="eventEstado" name="eventEstado">
                                    @foreach($estadoLabels as $value => $label)
                                        <option value="{{ $value }}" data-color="{{ $estadoColores[$value] }}" {{ $value === 'pendiente' ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                <label for="eventEstado">Estado</label>
                            </div>

                            <!-- Notas -->
                            <div class="form-floating form-floating-outline mb-5">
                                <textarea class="form-control" name="eventNotas" id="eventNotas" rows="3" placeholder="Notas adicionales"></textarea>
                                <label for="eventNotas">Notas</label>
                            </div>

                            <!-- Buttons -->
                            <div class="mb-5 d-flex justify-content-sm-between justify-content-start my-6 gap-2">
                                <div class="d-flex">
                                    <button type="submit" id="addEventBtn" class="btn btn-primary btn-add-event me-4">
                                        <i class="ri ri-add-line me-1"></i> Agregar
                                    </button>
                                    <button type="reset" class="btn btn-outline-secondary btn-cancel me-sm-0 me-1" data-bs-dismiss="offcanvas">Cancelar</button>
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-outline-success btn-send-reminder d-none" title="Enviar recordatorio por WhatsApp">
                                        <i class="ri ri-whatsapp-line"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger btn-delete-event d-none">
                                        <i class="ri ri-delete-bin-line"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Modal: Nuevo Paciente Rápido -->
                <div class="modal fade" id="modalPacienteRapido" tabindex="-1" aria-hidden="true" wire:ignore.self>
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Nuevo Paciente</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <div class="form-floating form-floating-outline">
                                            <input type="text" class="form-control" id="mpNombres" placeholder="Nombres" required>
                                            <label for="mpNombres">Nombres <span class="text-danger">*</span></label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-floating form-floating-outline">
                                            <input type="text" class="form-control" id="mpApellidos" placeholder="Apellidos" required>
                                            <label for="mpApellidos">Apellidos <span class="text-danger">*</span></label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating form-floating-outline">
                                            <input type="text" class="form-control" id="mpDocumento" placeholder="Documento de identidad" required>
                                            <label for="mpDocumento">Documento <span class="text-danger">*</span></label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating form-floating-outline">
                                            <input type="text" class="form-control" id="mpTelefono" placeholder="Teléfono" required>
                                            <label for="mpTelefono">Teléfono <span class="text-danger">*</span></label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-floating form-floating-outline">
                                            <input type="text" class="form-control" id="mpNickname" placeholder="Ej: Juanito, Beba, etc.">
                                            <label for="mpNickname">¿Cómo le gusta que le digan?</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating form-floating-outline">
                                            <input type="date" class="form-control" id="mpFechaNacimiento" placeholder="Fecha de nacimiento" required>
                                            <label for="mpFechaNacimiento">Fecha de Nacimiento <span class="text-danger">*</span></label>
                                        </div>
                                    </div>
                                    <div class="col-md-6 d-flex align-items-center">
                                        <div class="form-check mt-2">
                                            <input class="form-check-input" type="checkbox" id="mpEsMenor">
                                            <label class="form-check-label" for="mpEsMenor">¿Requiere tutor?</label>
                                        </div>
                                    </div>
                                </div>
                                <div id="mpTutorFields" class="mt-3" style="display:none;">
                                    <div class="alert alert-info py-2 px-3 mb-3">
                                        <i class="ri ri-information-line me-1"></i> Datos del tutor (opcional si es menor)
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="form-floating form-floating-outline">
                                                <input type="text" class="form-control" id="mpTutorNombres" placeholder="Nombres del tutor">
                                                <label for="mpTutorNombres">Nombres del tutor</label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-floating form-floating-outline">
                                                <input type="text" class="form-control" id="mpTutorApellidos" placeholder="Apellidos del tutor">
                                                <label for="mpTutorApellidos">Apellidos del tutor</label>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-floating form-floating-outline">
                                                <input type="text" class="form-control" id="mpTutorTelefono" placeholder="Teléfono del tutor">
                                                <label for="mpTutorTelefono">Teléfono del tutor</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                                <button type="button" class="btn btn-primary" id="modalPacienteCreateBtn">
                                    <i class="ri ri-user-add-line me-1"></i> Crear y seleccionar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="/materialize/assets/vendor/libs/fullcalendar/fullcalendar.js"></script>
    <script src="/materialize/assets/vendor/libs/moment/moment.js"></script>
    <script src="/materialize/assets/vendor/libs/flatpickr/flatpickr.js"></script>
    <script src="/materialize/assets/vendor/libs/@form-validation/popular.js"></script>
    <script src="/materialize/assets/vendor/libs/@form-validation/bootstrap5.js"></script>
    <script src="/materialize/assets/vendor/libs/@form-validation/auto-focus.js"></script>
    <script src="/materialize/assets/vendor/libs/select2/select2.js"></script>
    <script src="/materialize/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="{{ asset('js/app-calendario-general.js') }}"></script>
    @endpush

    @push('scripts')
    <script>
        (function waitFC() {
            if (typeof Calendar !== 'undefined') {
                initCalendarioGeneral(
                    @json($eventos),
                    @json($citaEstadoColores),
                    @json($consultaEstadoColores),
                    @json($citaEstadoLabels),
                    @json($consultaEstadoLabels)
                );
            } else {
                setTimeout(waitFC, 50);
            }
        })();
    </script>
    @endpush
</div>
