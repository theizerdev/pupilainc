<div id="citas-calendar-component">
    @push('scripts')
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('paciente-creado', (event) => {
                const data = event[0];
                $('#modalPacienteRapido').modal('hide');
                
                // Usar el sistema de toasts de la plantilla
                const toastContainer = document.querySelector('.toast-container');
                if (toastContainer) {
                    const toastHtml = `
                        <div class="bs-toast toast ${data.success ? 'bg-success' : 'bg-danger'}" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="5000">
                            <div class="toast-header">
                                <i class="ri-check-line me-2"></i>
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
                    const option = new Option(`${data.paciente.nombre} - ${data.paciente.documento_identidad || ''}`, data.paciente.id, true, true);
                    select.appendChild(option);
                    $(select).trigger('change');
                }
            });
        });
    </script>
    @endpush
    @push('styles')
    <link rel="stylesheet" href="/materialize/assets/vendor/libs/fullcalendar/fullcalendar.css" />
    <link rel="stylesheet" href="/materialize/assets/vendor/libs/flatpickr/flatpickr.css" />
    <link rel="stylesheet" href="/materialize/assets/vendor/libs/select2/select2.css" />
    <link rel="stylesheet" href="/materialize/assets/vendor/libs/@form-validation/form-validation.css" />
    <link rel="stylesheet" href="/materialize/assets/vendor/css/pages/app-calendar.css" />
    <style>
        .slot-btn { cursor: pointer; transition: all .2s; border: 1px solid #e0e0e0; }
        .slot-btn:hover { transform: scale(1.05); }
        .slot-btn.selected { border-color: var(--bs-primary); box-shadow: 0 0 0 2px var(--bs-primary); }
        .slot-btn.ocupado { opacity: .45; cursor: not-allowed; text-decoration: line-through; }
        .slots-container { max-height: 200px; overflow-y: auto; }
        .cascade-arrow { text-align: center; color: #aaa; font-size: .75rem; margin: 2px 0; }

        /* ========== Compact calendar events ========== */
        .fc .fc-event {
            border-radius: 4px !important;
            border-left: 4px solid transparent !important;
            transition: box-shadow 0.15s ease;
            overflow: hidden;
        }
        .fc .fc-event:hover {
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
            z-index: 10;
        }

        /* Month view: ultra compact */
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

        /* Week/Day view: compact */
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

        /* List view */
        .fc .fc-list-event-title {
            font-size: 0.85rem;
        }

        /* Leyendas y sidebar */
        .calendar-legend { font-size: 0.8rem; }
        .legend-dot { width: 10px; height: 10px; border-radius: 50%; display: inline-block; }
        .legend-bar { width: 4px; height: 16px; border-radius: 2px; display: inline-block; }

        /* Médicos con citas sidebar */
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

        /* Tipos de consulta sidebar */
        .tipo-consulta-item-sidebar {
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 0.8rem;
            cursor: pointer;
            transition: background 0.15s;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .tipo-consulta-item-sidebar:hover {
            background: rgba(var(--bs-primary-rgb), 0.08);
        }
        .tipo-consulta-item-sidebar.active {
            background: rgba(var(--bs-primary-rgb), 0.12);
            font-weight: 600;
        }
        .tipo-consulta-color-bar {
            width: 4px;
            height: 16px;
            border-radius: 2px;
            margin-right: 8px;
        }

        /* Contenedores PerfectScrollbar */
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
        
        /* Tipo consulta legend */
        .tipo-consulta-item {
            display: flex;
            align-items: center;
            padding: 3px 0;
            font-size: 0.8rem;
        }
       /* Animación de titilado constante */
        /* Contenedor del punto */
        .punto-agua {
            position: relative;
            width: 10px;
            height: 10px;
            flex-shrink: 0; /* Evita que el flexbox lo aplaste */
            margin-left: 5px; /* Espacio para que la onda no se corte a la izquierda */
        }

        /* El punto sólido central */
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

        /* La onda expansiva (efecto agua) */
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

/* Celdas del calendario más compactas y anchas */
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

/* Ajuste para el texto responsivo */
.fc-event-title-container {
    min-width: 0;
    flex: 1;
}





    </style>
    @endpush

    <div class="card app-calendar-wrapper" wire:ignore>
        <div class="row g-0">
            <!-- Calendar Sidebar -->
            <div class="col app-calendar-sidebar border-end" id="app-calendar-sidebar">
               
                <div class="px-4">
                    <div class="inline-calendar"></div>
                    
                    <hr class="mb-5 mx-n4 mt-3" />
                    <div class="d-flex justify-content-between align-items-center mb-4 ms-1">
                    <h5 class="mb-0">Filtrar por Estado</h5>
                        
                    </div>
                    
                    <div class="form-check form-check-secondary mb-5 ms-3">
                        <input class="form-check-input select-all" type="checkbox" id="selectAll" data-value="all" checked />
                        <label class="form-check-label" for="selectAll">Ver Todos</label>
                    </div>
                    <div class="app-calendar-events-filter text-heading">
                        <div class="form-check form-check-warning mb-5 ms-3">
                            <input class="form-check-input input-filter" type="checkbox"  id="select-pendiente" data-value="pendiente" checked />
                            <label class="form-check-label" for="select-pendiente">Pendiente</label>
                        </div>
                        <div class="form-check mb-5 ms-3">
                            <input class="form-check-input input-filter" type="checkbox" id="select-confirmada" data-value="confirmada" checked />
                            <label class="form-check-label" for="select-confirmada">Confirmada</label>
                        </div>
                        <div class="form-check form-check-info mb-5 ms-3">
                            <input class="form-check-input input-filter" type="checkbox" id="select-en_curso" data-value="en_curso" checked />
                            <label class="form-check-label" for="select-en_curso">En Curso</label>
                        </div>
                        <div class="form-check form-check-warning mb-5 ms-3">
                            <input class="form-check-input input-filter" type="checkbox" id="select-sala_espera" data-value="sala_espera" checked />
                            <label class="form-check-label" for="select-sala_espera">En Sala de Espera</label>
                        </div>
                        <div class="form-check form-check-success mb-5 ms-3">
                            <input class="form-check-input input-filter" type="checkbox" id="select-completada" data-value="completada" checked />
                            <label class="form-check-label" for="select-completada">Completada</label>
                        </div>
                        <div class="form-check form-check-danger mb-5 ms-3">
                            <input class="form-check-input input-filter" type="checkbox" id="select-cancelada" data-value="cancelada" checked />
                            <label class="form-check-label" for="select-cancelada">Cancelada</label>
                        </div>
                        <div class="form-check form-check-secondary ms-3">
                            <input class="form-check-input input-filter" type="checkbox" id="select-no_asistio" data-value="no_asistio" checked />
                            <label class="form-check-label" for="select-no_asistio">No Asistió</label>
                        </div>
                    </div>

                    <!-- Tipo de Cita con filtro -->
                    @if($this->tiposConsulta->isNotEmpty())
                        <hr class="mb-5 mx-n4 mt-3" />
                        <div class="mb-3 ms-1 d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Tipos de Cita</h5>
                        </div>
                        <div id="tipos-consulta-con-citas" class="ms-1 perfect-scrollbar-container" style="max-height: 200px; position: relative;">
                            <small class="text-muted">Cargando...</small>
                        </div>
                    @endif

                    <!-- Médicos con citas -->
                    <hr class="mb-5 mx-n4 mt-3" />
                    <div class="mb-3 ms-1 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Médicos con Citas</h5>
                    </div>
                    <div id="medicos-con-citas" class="ms-1 perfect-scrollbar-container" style="max-height: 200px; position: relative;">
                        <small class="text-muted">Cargando...</small>
                    </div>

                   
                </div>
            </div>

            <!-- Calendar & Modal -->
            <div class="col app-calendar-content">
                <div class="card shadow-none border-0">
                    <div class="card-body pb-0">
                        <div id="calendar"></div>
                    </div>
                </div>
                <div class="app-overlay"></div>

                <!-- Offcanvas -->
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
                                            {{ $paciente->nombre_completo }} - {{ $paciente->documento_identidad }}
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
                                <div id="slotsList" class="slots-container d-flex flex-wrap gap-2"></div>
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
                                            <input type="text" class="form-control" id="mpNombres" placeholder="Nombres">
                                            <label for="mpNombres">Nombres</label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-floating form-floating-outline">
                                            <input type="text" class="form-control" id="mpApellidos" placeholder="Apellidos">
                                            <label for="mpApellidos">Apellidos</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating form-floating-outline">
                                            <input type="text" class="form-control" id="mpDocumento" placeholder="Documento de identidad">
                                            <label for="mpDocumento">Documento</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating form-floating-outline">
                                            <input type="text" class="form-control" id="mpTelefono" placeholder="Teléfono">
                                            <label for="mpTelefono">Teléfono</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating form-floating-outline">
                                            <input type="date" class="form-control" id="mpFechaNacimiento" placeholder="Fecha de nacimiento">
                                            <label for="mpFechaNacimiento">Fecha de Nacimiento</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6 d-flex align-items-center">
                                        <div class="form-check mt-2">
                                            <input class="form-check-input" type="checkbox" id="mpEsMenor">
                                            <label class="form-check-label" for="mpEsMenor">Es menor</label>
                                        </div>
                                    </div>
                                </div>
                                <div id="mpTutorFields" class="mt-3" style="display:none;">
                                    <div class="alert alert-info py-2 px-3 mb-3">
                                        Datos del tutor (si es menor)
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
                                        <div class="col-md-6">
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
    <script src="/materialize/assets/vendor/libs/@form-validation/popular.js"></script>
    <script src="/materialize/assets/vendor/libs/@form-validation/bootstrap5.js"></script>
    <script src="/materialize/assets/vendor/libs/@form-validation/auto-focus.js"></script>
    <script src="/materialize/assets/vendor/libs/select2/select2.js"></script>
    <script src="/materialize/assets/vendor/libs/moment/moment.js"></script>
    <script src="/materialize/assets/vendor/libs/flatpickr/flatpickr.js"></script>
    <script src="{{ asset('js/app-calendar-citas.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            initCitasCalendar(@json($eventos));
        });
    </script>
    @endpush
</div>