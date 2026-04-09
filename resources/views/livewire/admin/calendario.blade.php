<div id="calendario-general-component">
    @push('scripts')
    <script>
        document.addEventListener('livewire:init', () => {
            // Listener para alertas con SweetAlert2
            Livewire.on('show-alert', (event) => {
                const data = event[0];

                if (window.Swal) {
                    Swal.fire({
                        icon: data.icon || 'info',

                        text: data.message || '',
                        confirmButtonText: 'Entendido',
                        confirmButtonColor: data.type === 'success' ? '#28a745' :
                                          data.type === 'error' ? '#dc3545' :
                                          data.type === 'warning' ? '#ffc107' : '#0d6efd',
                    });
                } else {
                    // Fallback a toast nativo si no hay SweetAlert
                    const toastContainer = document.querySelector('.toast-container');
                    if (toastContainer) {
                        const bgClass = data.type === 'success' ? 'bg-success' :
                                       data.type === 'error' ? 'bg-danger' :
                                       data.type === 'warning' ? 'bg-warning' : 'bg-info';
                        const toastHtml = `
                            <div class="bs-toast toast ${bgClass}" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="5000">
                                <div class="toast-header">
                                    <i class="ri ri-check-line me-2"></i>
                                    <div class="me-auto fw-medium">${data.title || 'Alerta'}</div>
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
                }
            });

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
    <link rel="stylesheet" href="/css/calendar-custom.css" />

    @endpush

    <div class="card app-calendar-wrapper" wire:ignore>
        <div class="row g-0" style="min-width: 0;">
            <!-- Calendar Sidebar -->
            <div class="col app-calendar-sidebar sidebar-hidden border-end" id="app-calendar-sidebar">
                <!-- Toggle Filtros Button (Fixed at top) -->
                <div class="px-4 py-3 border-bottom bg-white sticky-top" style="z-index: 10;">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class="ri ri-filter-3-line me-2"></i>Filtros</h6>
                        <button type="button" class="btn btn-sm btn-icon btn-outline-secondary" id="toggleFiltros">
                            <i class="ri ri-filter-off-line"></i>
                        </button>
                    </div>
                </div>
                <!-- Scrollable Content -->
                <div class="px-4 py-3 sidebar-scroll" id="filtrosScrollContainer">

                    <!-- Búsqueda Rápida -->
                    <div class="mb-3">
                        <!-- Botón Nueva Cita -->
                        <div class="mb-2">
                            <button type="button" class="btn btn-sm btn-primary w-100 mb-4" id="btnNuevaCita">
                                <i class="ri ri-add-line me-1"></i>Nueva Cita
                            </button>
                        </div>

                        <!-- Inline Mini Calendar - Filtro de Fecha Principal -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <small class="text-muted fw-medium">FECHA</small>
                            </div>
                            <div class="inline-calendar"></div>
                            <div id="fechaFiltroActivo" class="small mt-2" style="display: none;">
                                <span class="badge bg-primary w-100">
                                    <i class="ri ri-calendar-check-line me-1"></i>
                                    <span id="fechaFiltroTexto"></span>
                                </span>
                            </div>
                        </div>

                        <!-- Búsqueda por paciente -->
                        <div class="input-group input-group-sm mt-2">
                            <span class="input-group-text"><i class="ri ri-search-line"></i></span>
                            <input type="text" class="form-control" id="searchPaciente" placeholder="Buscar paciente...">
                        </div>

                        <!-- Botón limpiar filtros -->
                        <button type="button" class="btn btn-sm btn-outline-secondary w-100 mt-2" id="btnLimpiarFiltros">
                            <i class="ri ri-close-line me-1"></i>Limpiar Filtros
                        </button>
                    </div>





                    <hr class="mb-4 mx-n4 mt-3" />

                    <!-- Filtros Container -->
                    <div id="filtrosContainer">

                    <!-- Filtro por Especialidad (Colapsable) -->
                    <div class="accordion" id="filtrosAccordion">
                        <!-- Filtro por Especialidad -->
                        <div class="accordion-item border-0 bg-transparent">
                            <h2 class="accordion-header">
                                <button class="accordion-button p-1 py-2 bg-transparent collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#especialidadCollapse">
                                    <i class="ri ri-medicine-bottle-line me-2"></i>Especialidad
                                    <span class="badge bg-primary rounded-pill ms-2" id="especialidadCount">{{ \App\Models\Categoria::count() }}</span>

                                </button>
                            </h2>
                            <div id="especialidadCollapse" class="accordion-collapse collapse" data-bs-parent="#filtrosAccordion">
                                <div class="accordion-body p-0 pt-2">
                                    <select class="form-select form-select-sm" id="filterEspecialidad">
                                        <option value="">Todas las especialidades</option>
                                        @foreach($medicos->pluck('especialidades')->flatten()->unique('id') as $esp)
                                            <option value="{{ $esp->id }}">{{ $esp->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <hr class="mb-2 mx-n2" />

                        <!-- Filtro por Médico -->
                        <div class="accordion-item border-0 bg-transparent">
                            <h2 class="accordion-header">
                                <button class="accordion-button p-1 py-2 bg-transparent collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#medicoCollapse">
                                    <i class="ri ri-user-star-line me-2"></i>Médico
                                    <span class="badge bg-secondary rounded-pill ms-2" id="medicoCount">0</span>

                                </button>
                            </h2>
                            <div id="medicoCollapse" class="accordion-collapse collapse" data-bs-parent="#filtrosAccordion">
                                <div class="accordion-body p-0 pt-2">
                                    <select class="form-select form-select-sm" id="filterMedico">
                                        <option value="">Todos los médicos</option>
                                        @foreach($medicos as $medico)
                                            <option value="{{ $medico->id }}">{{ $medico->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <hr class="mb-2 mx-n2" />

                        <!-- Filtros de estados de Citas -->
                        <div class="accordion-item border-0 bg-transparent">
                            <h2 class="accordion-header">
                                <button class="accordion-button p-1 py-2 bg-transparent collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#citasCollapse">
                                    <i class="ri ri-calendar-check-line me-2"></i>Estados de Citas
                                    <span class="badge bg-primary rounded-pill ms-2" id="citasActivasCount">0</span>

                                </button>
                            </h2>
                            <div id="citasCollapse" class="accordion-collapse collapse" data-bs-parent="#filtrosAccordion">
                                <div class="accordion-body p-0 pt-2">
                                    <div class="app-calendar-events-filter ps-3" id="filtros-citas">
                                    @foreach($citaEstadoLabels as $value => $label)
                                        <div class="form-check mb-2 ms-2">
                                            <input class="form-check-input input-filter-cita" type="checkbox"
                                                   id="select-cita-{{ $value }}"
                                                   data-value="{{ $value }}"
                                                   checked />
                                            <label class="form-check-label d-flex align-items-center" for="select-cita-{{ $value }}">
                                                <span class="legend-dot me-2" style="background-color: {{ ['programada'=>'#ffc107','confirmada'=>'#0d6efd','cancelada'=>'#dc3545','no_asistio'=>'#6c757d'][$value] ?? '#78909C' }};"></span>
                                                {{ $label }}
                                            </label>
                                        </div>
                                    @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>


                    </div>

                    <!-- Médicos -->
                    <hr class="mb-4 mx-n4" />
                    <div class="mb-3 ms-1 d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Médicos</h6>
                        <small class="text-muted" id="medicosCount">{{ $medicos->count() }}</small>
                    </div>
                    <div id="medicos-calendario" class="ms-1" style="{{ $medicos->isEmpty() ? '' : 'max-height: 200px; overflow-y: auto;' }}">
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
                            <small class="text-muted d-block text-center mt-2 mb-3">Sin médicos activos</small>
                        @endif
                    </div>

                    </div>
                </div>
            </div>

            <!-- Calendar Content -->
            <div class="col app-calendar-content" style="position: relative;">
                <div class="card shadow-none border-0">
                    <div class="card-body pb-0 pt-0 px-2" style="position: relative;">
                        <!-- Loading Overlay -->
                        <div class="calendar-loading" id="calendarLoading">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                        </div>

                        <div id="calendarScrollContainer" class="calendar-scroll"><div id="calendar"></div></div>
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
                    <div class="offcanvas-header border-bottom ">
                        <h5 class="offcanvas-title" id="addEventSidebarLabel">Nueva Cita</h5>
                        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                    </div>
                    <div class="offcanvas-body">
                        <form class="event-form pt-0" id="eventForm" onsubmit="return false">
                            <!-- 1. Paciente -->
                            <div class="mb-3" id="nuevoPacienteContainer">
                                <button type="button" class="btn btn-outline-primary btn-sm w-100" id="btnModalPacienteRapido" data-bs-toggle="modal" data-bs-target="#modalPacienteRapido">
                                    <i class="ri ri-user-add-line me-1"></i> Nuevo Paciente
                                </button>
                            </div>
                            <!-- Info banner visible solo en edición -->
                            <div id="editInfoBanner" class="alert alert-light-primary alert-dismissible mb-3 py-2 px-3" style="display:none;" role="alert">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="ri ri-information-line"></i>
                                    <small>Para cambiar el <strong>estado</strong>, haga clic en el badge de estado en el calendario.</small>
                                </div>
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



                            <div class="cascade-arrow" id="especialidadArrowTop"><i class="ri ri-arrow-down-s-line"></i></div>

                            <!-- 2. Especialidad -->
                            <div class="form-floating form-floating-outline mb-5 form-control-validation" id="especialidadContainer">
                                <select class="select2 form-select" id="eventEspecialidad" name="eventEspecialidad" disabled>
                                    <option value="">Primero seleccione un paciente</option>
                                </select>
                                <label for="eventEspecialidad">Especialidad</label>
                            </div>

                            <div class="cascade-arrow" id="especialidadArrowBottom"><i class="ri ri-arrow-down-s-line"></i></div>

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

                            <!-- Prioridad -->
                            <div class="form-floating form-floating-outline mb-5">
                                <select class="select2 form-select" id="eventPrioridad" name="eventPrioridad">
                                    <option value="normal" selected>Normal</option>
                                    <option value="emergencia">Emergencia</option>
                                    <option value="alta">Otros</option>
                                </select>
                                <label for="eventPrioridad">Registro manual de cita</label>
                            </div>



                            <!-- 6. Horarios Disponibles (Solo Informativos - OCULTO) -->
                            {{-- <div id="slotsContainer" class="mb-5" style="display:none;">
                                <label class="form-label fw-medium mb-2">
                                    <i class="ri ri-information-line me-1"></i>Horarios del Médico
                                    <small class="text-muted fw-normal">(Solo referencia)</small>
                                </label>
                                <div id="slotsMessage" class="alert alert-info py-2 px-3 mb-2" role="alert">
                                    <i class="ri ri-information-line me-1"></i>
                                    Estos son los horarios disponibles del médico. Debe seleccionar las horas manualmente abajo.
                                </div>
                                <div id="slotsList" class="slots-container" style="pointer-events: none; opacity: 0.7;"></div>
                            </div> --}}

                            <!-- 7. Horarios Manuales -->
                            <div class="row g-2 mb-5">
                                <div class="col-6">
                                    <div class="form-floating form-control-validation">
                                        <input type="time" class="form-control" id="eventHoraInicio" name="eventHoraInicio" step="300" required>
                                        <label for="eventHoraInicio">Hora de Inicio</label>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-floating form-control-validation">
                                        <input type="time" class="form-control" id="eventHoraFin" name="eventHoraFin" step="300" required>
                                        <label for="eventHoraFin">Hora de Fin</label>
                                    </div>
                                </div>
                            </div>
                            <div id="horarioError" class="text-danger mb-3" style="display: none;"></div>

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
                                <label for="eventTipoConsulta">Tipo de Atención</label>
                            </div>

                            <!-- Motivo -->
                            <div class="form-floating form-floating-outline mb-5 form-control-validation">
                                <input type="text" class="form-control" id="eventMotivo" name="eventMotivo" placeholder="Motivo de la cita" />
                                <label for="eventMotivo">Motivo</label>
                            </div>

                            <!-- Estado -->
                            <div class="form-floating form-floating-outline mb-5" id="estadoContainer">
                                <select class="select2 form-select" id="eventEstado" name="eventEstado">
                                    @foreach($estadoLabels as $value => $label)
                                        <option value="{{ $value }}" data-color="{{ $estadoColores[$value] }}" {{ $value === 'programada' ? 'selected' : '' }}>
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
                                    <div class="col-md-12">
                                        <div class="form-floating form-floating-outline">
                                            <input type="text" class="form-control" id="mpDocumento" placeholder="Documento de identidad" required>
                                            <label for="mpDocumento">Documento <span class="text-danger">*</span></label>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-floating form-floating-outline">
                                            <input type="tel"
                                                   class="form-control"
                                                   id="mpTelefono"
                                                   placeholder="+58 412 1234567"
                                                   required
                                                   pattern="[\d\s\-\+\(\)]+"
                                                   title="Solo números y caracteres válidos (+, -, espacios, paréntesis)"
                                                   onkeypress="return /[0-9+\-()\s]/.test(String.fromCharCode(event.keyCode))">
                                            <label for="mpTelefono">Teléfono <span class="text-danger">*</span></label>
                                            <small class="text-muted mt-1 d-block">
                                                <i class="ri ri-information-line me-1"></i>Solo números. Ej: +58 412 1234567
                                            </small>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-floating form-floating-outline">
                                            <input type="text" class="form-control" id="mpNickname" placeholder="Ej: Juanito, Beba, etc.">
                                            <label for="mpNickname">¿Cómo le gusta que le digan?</label>
                                        </div>
                                    </div><br><br>
                                    <div class="col-md-12 mb-4">
                                        <div class="form-floating form-floating-outline">
                                            <input type="date" class="form-control" id="mpFechaNacimiento" placeholder="Fecha de nacimiento" required>
                                            <label for="mpFechaNacimiento">Fecha de Nacimiento <span class="text-danger">*</span></label>
                                        </div>
                                    </div>
                                    <div class="col-md-12 d-flex align-items-center">
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
                                        <div class="col-md-12">
                                            <div class="form-floating form-floating-outline">
                                                <input type="text" class="form-control" id="mpTutorNombres" placeholder="Nombres del tutor">
                                                <label for="mpTutorNombres">Nombres del tutor</label>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-floating form-floating-outline">
                                                <input type="text" class="form-control" id="mpTutorApellidos" placeholder="Apellidos del tutor">
                                                <label for="mpTutorApellidos">Apellidos del tutor</label>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-floating form-floating-outline">
                                                <input type="tel"
                                                       class="form-control"
                                                       id="mpTutorTelefono"
                                                       placeholder="Teléfono del tutor"
                                                       pattern="[\d\s\-\+\(\)]+"
                                                       title="Solo números y caracteres válidos (+, -, espacios, paréntesis)"
                                                       onkeypress="return /[0-9+\-()\s]/.test(String.fromCharCode(event.keyCode))">
                                                <label for="mpTutorTelefono">Teléfono del tutor</label>
                                                <small class="text-muted mt-1 d-block">
                                                    <i class="ri ri-information-line me-1"></i>Solo números. Ej: +58 412 1234567
                                                </small>
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

        <!-- Modal: Ver más eventos del día -->

        <!-- Modal: Cambiar Estado de Cita -->
        <div class="modal fade" id="modalCambiarEstado" tabindex="-1" aria-labelledby="modalCambiarEstadoLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalCambiarEstadoLabel">Cambiar Estado</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="estadoCitaId" />
                        <input type="hidden" id="estadoCitaActual" />
                        <div class="mb-3">
                            <label for="estadoSelect" class="form-label">Nuevo estado</label>
                            <select id="estadoSelect" class="form-select">
                                <option value="">Seleccione un estado...</option>
                            </select>
                        </div>
                        <div class="mb-0">
                            <label for="estadoNota" class="form-label">Nota (opcional)</label>
                            <textarea id="estadoNota" class="form-control" rows="2" placeholder="Agregar una nota al cambio de estado..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-primary" id="btnGuardarEstado">
                            <span class="indicator-label">Guardar</span>
                            <span class="indicator-progress d-none">
                                <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Guardando...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>

    @push('scripts')
    <script src="/materialize/assets/vendor/libs/fullcalendar/fullcalendar.js"></script>
    <script src="/materialize/assets/vendor/libs/moment/moment.js"></script>
    <script src="/materialize/assets/vendor/libs/flatpickr/flatpickr.js"></script>
    <script src="https://npmcdn.com/flatpickr/dist/l10n/es.js"></script>
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
                    @json($citaEstadoLabels)
                );
            } else {
                setTimeout(waitFC, 50);
            }
        })();
    </script>
    @endpush
</div>
