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

                // Solo cerrar el modal si fue exitoso y no hay errores
                if (data.success && !data.errors) {
                    $('#modalPacienteRapido').modal('hide');
                }

                // Mostrar errores de campo si los hay
                if (!data.success && data.errors) {
                    if (typeof showPacienteValidationErrors === 'function') {
                        showPacienteValidationErrors(data.errors);
                    }
                }

                // Toast de resultado
                const toastContainer = document.querySelector('.toast-container');
                if (toastContainer) {
                    const toastHtml = `
                        <div class="bs-toast toast ${data.success ? 'bg-success' : 'bg-danger'}" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="6000">
                            <div class="toast-header">
                                <i class="ri ri-${data.success ? 'check' : 'error-warning'}-line me-2"></i>
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
                    const select = document.getElementById('eventPaciente');
                    const nombreMostrar = data.paciente.nickname
                        ? `${data.paciente.nombre} (${data.paciente.nickname}) - ${data.paciente.documento_identidad || ''}`
                        : `${data.paciente.nombre} - ${data.paciente.documento_identidad || ''}`;
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
                         <!-- Botón limpiar filtros -->
                        <button type="button" class="btn btn-sm btn-outline-secondary w-100 mt-2 mb-3" id="btnLimpiarFiltros">
                            <i class="ri ri-close-line me-1"></i>Limpiar Filtros
                        </button>

                        <!-- Filtro por Médico -->
                        <div class="mb-3">
                            <small class="text-muted fw-medium d-block mb-2">MÉDICO</small>
                            <select class="form-select form-select-sm" id="filterMedico"
                                    wire:model.live="filtroMedico">
                                <option value="">Todos los médicos</option>
                                @foreach($medicos as $medico)
                                    <option value="{{ $medico->id }}">{{ $medico->nombres.' '.$medico->apellidos }}</option>
                                @endforeach
                            </select>
                            @if($filtroMedico)
                                <button type="button" class="btn btn-xs btn-link text-danger p-0 mt-1"
                                        wire:click="$set('filtroMedico', '')">
                                    <i class="ri ri-close-line me-1"></i>Limpiar filtro médico
                                </button>
                            @endif


                        <!-- Búsqueda por paciente -->
                        <div class="input-group input-group-sm mt-2 mb-4">
                            <span class="input-group-text"><i class="ri ri-search-line"></i></span>
                            <input type="text" class="form-control" id="searchPaciente" placeholder="Buscar paciente...">
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



                    </div>





                    <hr class="mb-4 mx-n4 mt-3" />

                    <!-- Filtros Container -->
                    <div id="filtrosContainer">

                    <!-- Filtro por Especialidad (Colapsable) -->
                    <div class="accordion" id="filtrosAccordion">




                        </div>





                    </div>





                    </div>
                </div>
            </div>

            <!-- Calendar Content -->
            <div class="col app-calendar-content" style="position: relative;">
                <div class="card shadow-none border-0">
                    <div class="card-body pb-0 pt-0 px-2" style="position: relative;">

                        <!-- Barra de filtros rápidos ENCIMA del calendario (Issue 6) -->


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
                            <!-- 1. Paciente/Mascota -->
                            <div class="mb-3" id="nuevoPacienteContainer">
                                <button type="button" class="btn btn-outline-primary btn-sm w-100" id="btnModalNuevoRegistro" data-bs-toggle="modal" data-bs-target="#modalNuevoRegistro">
                                    <i class="ri ri-add-line me-1"></i> <span id="btnNuevoRegistroText">Nuevo Paciente</span>
                                </button>
                            </div>
                            <!-- Info banner visible solo en edición -->
                            <div id="editInfoBanner" class="alert alert-light-primary alert-dismissible mb-3 py-2 px-3" style="display:none;" role="alert">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="ri ri-information-line"></i>
                                    <small>Para cambiar el <strong>estado</strong>, haga clic en el badge de estado en el calendario.</small>
                                </div>
                            </div>
                            <div class="form-floating form-floating-outline mb-1 form-control-validation">
                                <select class="select2 form-select" id="eventEspecie" name="eventEspecie">
                                    <option value="">Seleccionar especie</option>
                                    @foreach($especies as $especie)
                                        <option value="{{ $especie->id }}">
                                            {{ $especie->icono }} {{ $especie->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                <label for="eventEspecie"><i class="mdi mdi-paw me-1"></i>Especie</label>
                            </div>

                           <div class="cascade-arrow" id="razaArrow" style="display:none;"><i class="ri ri-arrow-down-s-line"></i></div>

                            <!-- 2. Raza -->
                            <div class="form-floating form-floating-outline mb-1 form-control-validation" id="razaContainer" style="display:none;">
                                <select class="select2 form-select" id="eventRaza" name="eventRaza">
                                    <option value="">Primero seleccione una especie</option>
                                </select>
                                <label for="eventRaza">Raza</label>
                            </div>

                            <div class="cascade-arrow" id="mascotaArrow" style="display:none;"><i class="mdi mdi-arrow-down"></i></div>

                            <!-- 3. Mascota -->
                            <div class="form-floating form-floating-outline mb-3 form-control-validation" id="mascotaContainer" style="display:none;">
                                <select class="select2 form-select" id="eventMascota" name="eventMascota">
                                    <option value="">Primero seleccione especie/raza</option>
                                </select>
                                <label for="eventMascota"><i class="ri ri-paw me-1"></i>Mascota</label>
                            </div>

                           <div class="cascade-arrow" id="especialidadArrowTop" style="display:none;"><i class="ri ri-arrow-down-s-line"></i></div>

                            <!-- 2. Especialidad -->
                            <div class="form-floating form-floating-outline mb-5 form-control-validation" id="especialidadContainer" style="display:none;">
                                <select class="select2 form-select" id="eventEspecialidad" name="eventEspecialidad" disabled>
                                    <option value="">Primero seleccione un paciente</option>
                                </select>
                                <label for="eventEspecialidad">Especialidad</label>
                            </div>

                            <div class="cascade-arrow" id="especialidadArrowBottom" style="display:none;"><i class="ri ri-arrow-down-s-line"></i></div>

                            <!-- 3. Subespecialidad (opcional) -->
                            <div class="form-floating form-floating-outline mb-5" id="subespecialidadContainer" style="display:none;">
                                <select class="select2 form-select" id="eventSubespecialidad" name="eventSubespecialidad">
                                    <option value="">Opcional - Seleccionar subespecialidad</option>
                                </select>
                            </div>
                            <!-- 4. Médico -->
                            <div class="form-floating form-floating-outline mb-5 mt-4 form-control-validation">
                                <select class="select2 form-select" id="eventMedico" name="eventMedico" disabled>
                                    <option value="">Primero seleccione un paciente</option>
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

                            <!-- Tipo de Atención -->
                            <div class="form-floating form-floating-outline mb-5">
                                <select class="select2 form-select" id="eventTipoConsulta" name="eventTipoConsulta">
                                    <option value="">Sin tipo de atención</option>
                                    @foreach($tiposAtencion as $tipo)
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
                                    <option value="programada" data-color="#ffc107" selected>Programada</option>
                                    <option value="confirmada" data-color="#0d6efd">Confirmada</option>
                                    <option value="cancelada" data-color="#dc3545">Cancelada</option>
                                    <option value="no_asistio" data-color="#6c757d">No Asistió</option>
                                    <!-- Estados de consulta -->


                                    <!-- Estados veterinarios -->
                                    <option value="en_triage" data-color="#FF5722">En Triaje/Urgencias</option>
                                    <option value="en_tratamiento" data-color="#2196F3">En Tratamiento</option>
                                    <option value="en_procedimiento" data-color="#00BCD4">En Procedimiento/Curas</option>
                                    <option value="pre_quirurgico" data-color="#FFC107">Pre-Quirúrgico</option>
                                    <option value="en_cirugia" data-color="#F44336">En Cirugía</option>
                                    <option value="recuperacion" data-color="#4CAF50">En Recuperación</option>
                                    <option value="educacion_propietario" data-color="#9C27B0">Educación/Alta</option>
                                    <option value="finalizada" data-color="#66BB6A">Finalizada</option>
                                    <option value="pagada" data-color="#4CAF50">Pagada</option>
                                </select>
                                <label for="eventEstado">Estado</label>
                            </div>



                            <!-- Notas -->
                            <div class="form-floating form-floating-outline mb-5">
                                <textarea class="form-control" name="eventNotas" id="eventNotas" rows="3" placeholder="Notas adicionales"></textarea>
                                <label for="eventNotas">Notas</label>
                            </div>

                            <!-- Payment Button Container (hidden by default) -->
                            <div id="paymentButtonContainer" class="mb-4" style="display: none;">
                                <a id="btnRegistrarPago" href="#" class="btn btn-success w-100" style="display: none;">
                                    <i class="ri ri-money-dollar-circle-line me-2"></i> 💳 Registrar Pago
                                </a>
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

                <!-- Modal: Nuevo Registro (Paciente o Mascota) -->
                <div class="modal fade" id="modalNuevoRegistro" tabindex="-1" aria-hidden="true" wire:ignore.self>
                    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="modalNuevoRegistroTitle">Nuevo Paciente</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <!-- Formulario para Mascota -->
                                <div id="formMascota" style="display:none;">
                                    <div class="alert alert-warning py-2 px-3 mb-3">
                                        <i class="ri ri-paw-line me-1"></i> Complete los datos de la mascota y su propietario
                                    </div>

                                    <h6 class="fw-semibold mb-3"><i class="ri ri-paw-line me-2 text-warning"></i>Datos de la Mascota</h6>
                                    <div class="row g-3 mb-4">
                                        <div class="col-md-8">
                                            <div class="form-floating form-floating-outline">
                                                <input type="text" class="form-control" id="mascotaNombre" placeholder="Nombre de la mascota" required>
                                                <label for="mascotaNombre">Nombre <span class="text-danger">*</span></label>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-floating form-floating-outline">
                                                <select class="form-select" id="mascotaSexo">
                                                    <option value="macho">Macho</option>
                                                    <option value="hembra">Hembra</option>
                                                </select>
                                                <label for="mascotaSexo">Sexo</label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-floating form-floating-outline">
                                                <select class="form-select" id="mascotaEspecie" required>
                                                    <option value="">Seleccione especie...</option>
                                                </select>
                                                <label for="mascotaEspecie">Especie <span class="text-danger">*</span></label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-floating form-floating-outline">
                                                <select class="form-select" id="mascotaRaza">
                                                    <option value="">Primero seleccione especie</option>
                                                </select>
                                                <label for="mascotaRaza">Raza</label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-floating form-floating-outline">
                                                <input type="date" class="form-control" id="mascotaFechaNacimiento">
                                                <label for="mascotaFechaNacimiento">Fecha de nacimiento</label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-floating form-floating-outline">
                                                <input type="number" step="0.01" class="form-control" id="mascotaPeso" placeholder="0.00">
                                                <label for="mascotaPeso">Peso (kg)</label>
                                            </div>
                                        </div>
                                    </div>

                                    <h6 class="fw-semibold mb-3"><i class="ri ri-user-line me-2 text-primary"></i>Datos del Propietario (Dueño)</h6>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="form-floating form-floating-outline">
                                                <input type="text" class="form-control" id="propietarioNombres" placeholder="Nombres" required>
                                                <label for="propietarioNombres">Nombres <span class="text-danger">*</span></label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-floating form-floating-outline">
                                                <input type="text" class="form-control" id="propietarioApellidos" placeholder="Apellidos" required>
                                                <label for="propietarioApellidos">Apellidos <span class="text-danger">*</span></label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-floating form-floating-outline">
                                                <input type="tel"
                                                       class="form-control"
                                                       id="propietarioTelefono"
                                                       placeholder="+58 412 1234567"
                                                       required
                                                       pattern="[\d\s\-\+\(\)]+"
                                                       title="Solo números y caracteres válidos (+, -, espacios, paréntesis)">
                                                <label for="propietarioTelefono">Teléfono <span class="text-danger">*</span></label>
                                                <small class="text-muted mt-1 d-block">
                                                    <i class="ri ri-information-line me-1"></i>Ej: +58 412 1234567
                                                </small>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-floating form-floating-outline">
                                                <input type="tel"
                                                       class="form-control"
                                                       id="propietarioTelefonoAlt"
                                                       placeholder="+58 412 1234567"
                                                       pattern="[\d\s\-\+\(\)]+">
                                                <label for="propietarioTelefonoAlt">Teléfono alternativo</label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-floating form-floating-outline">
                                                <input type="email" class="form-control" id="propietarioEmail" placeholder="correo@ejemplo.com">
                                                <label for="propietarioEmail">Email</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Formulario para Paciente Humano -->
                                <div id="formPaciente">
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
                                                <input type="text" class="form-control" id="mpDocumento" placeholder="Documento de identidad">
                                                <label for="mpDocumento">Documento</label>
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
                                                       title="Solo números y caracteres válidos (+, -, espacios, paréntesis)">
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
                                                           pattern="[\d\s\-\+\(\)]+">
                                                    <label for="mpTutorTelefono">Teléfono del tutor</label>
                                                    <small class="text-muted mt-1 d-block">
                                                        <i class="ri ri-information-line me-1"></i>Solo números. Ej: +58 412 1234567
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                                <button type="button" class="btn btn-primary" id="modalCrearRegistroBtn">
                                    <i class="ri ri-add-line me-1"></i> <span id="modalCrearRegistroText">Crear y seleccionar</span>
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
    <script>
        // Manejo de selects dependientes para mascotas (Especie → Raza → Mascota)
        document.addEventListener('livewire:init', function() {
            const eventEspecie = document.getElementById('eventEspecie');
            const eventRaza = document.getElementById('eventRaza');
            const eventMascota = document.getElementById('eventMascota');
            const razaContainer = document.getElementById('razaContainer');
            const mascotaContainer = document.getElementById('mascotaContainer');
            const razaArrow = document.getElementById('razaArrow');
            const mascotaArrow = document.getElementById('mascotaArrow');

            if (eventEspecie) {
                $(eventEspecie).on('change', function() {
                    const especieId = $(this).val();

                    // Resetear selects dependientes
                    $(eventRaza).html('<option value="">Cargando razas...</option>');
                    $(eventMascota).html('<option value="">Primero seleccione raza</option>');
                    $(razaContainer).hide();
                    $(mascotaContainer).hide();
                    $(razaArrow).hide();
                    $(mascotaArrow).hide();

                    // Deshabilitar select de médico
                    $('#eventMedico').prop('disabled', true).html('<option value="">Primero seleccione una mascota</option>');

                    if (!especieId) return;

                    // Hacer llamada AJAX directa al backend
                    fetch(`/admin/api/calendario/razas/${especieId}`)
                        .then(response => response.json())
                        .then(razas => {
                            $(eventRaza).html('<option value="">Seleccionar raza</option>');
                            razas.forEach(raza => {
                                $(eventRaza).append(`<option value="${raza.id}">${raza.nombre}</option>`);
                            });
                            $(razaContainer).slideDown();
                            $(razaArrow).show();
                            $(eventRaza).trigger('change.select2');
                        })
                        .catch(err => {
                            console.error('Error cargando datos:', err);
                        });
                });
            }

            if (eventRaza) {
                $(eventRaza).on('change', function() {
                    const razaId = $(this).val();
                    const especieId = $(eventEspecie).val();

                    $(eventMascota).html('<option value="">Cargando mascotas...</option>');
                    $(mascotaContainer).hide();
                    $(mascotaArrow).hide();

                    // Deshabilitar select de médico
                    $('#eventMedico').prop('disabled', true).html('<option value="">Primero seleccione una mascota</option>');

                    if (!razaId) return;

                    // Cargar mascotas filtradas por raza
                    fetch(`/admin/api/calendario/mascotas?especie_id=${especieId}&raza_id=${razaId}`)
                        .then(response => response.json())
                        .then(mascotas => {
                            $(eventMascota).html('<option value="">Seleccionar mascota</option>');
                            mascotas.forEach(mascota => {
                                const texto = `${mascota.nombre} - ${mascota.especie?.nombre || ''} ${mascota.raza ? '(' + mascota.raza.nombre + ')' : ''}`;
                                $(eventMascota).append(`<option value="${mascota.id}">${texto}</option>`);
                            });
                            $(mascotaContainer).slideDown();
                            $(mascotaArrow).show();
                            $(eventMascota).trigger('change.select2');
                        })
                        .catch(err => {
                            console.error('Error cargando mascotas:', err);
                        });
                });
            }

            // Habilitar select de médico cuando se selecciona una mascota
            if (eventMascota) {
                $(eventMascota).on('change', function() {
                    const mascotaId = $(this).val();
                    const medicoSelect = $('#eventMedico');

                    // ACTUALIZAR LIVWIRE: Enviar valor al componente correcto usando evento
                    if (mascotaId) {
                        // Dispatch event to Livewire component
                        Livewire.dispatch('mascotaSeleccionada', { mascotaId: mascotaId });
                        console.log('✅ Mascota ID enviada via evento:', mascotaId);
                    } else {
                        // Limpiar propiedad en Livewire
                        Livewire.dispatch('mascotaSeleccionada', { mascotaId: null });
                        console.log('✅ Mascota ID limpiada');
                    }

                    if (mascotaId) {
                        // Habilitar el select de médico
                        medicoSelect.prop('disabled', false);
                        medicoSelect.html('<option value="">Cargando médicos...</option>');

                        // Fetch doctors via API route (igual que razas y mascotas)
                        fetch('/admin/api/calendario/medicos')
                            .then(response => response.json())
                            .then(medicos => {
                                medicoSelect.html('<option value="">Seleccionar médico</option>');
                                if (medicos && medicos.length > 0) {
                                    medicos.forEach(medico => {
                                        medicoSelect.append(`<option value="${medico.id}">${medico.nombre}</option>`);
                                    });
                                    medicoSelect.trigger('change.select2');
                                } else {
                                    medicoSelect.html('<option value="">No hay médicos disponibles</option>');
                                }
                            })
                            .catch(err => {
                                console.error('Error cargando médicos:', err);
                                medicoSelect.html('<option value="">Error al cargar médicos</option>');
                            });
                    } else {
                        // Deshabilitar si no hay mascota seleccionada
                        medicoSelect.prop('disabled', true).html('<option value="">Primero seleccione una mascota</option>');
                    }
                });
            }
        });
    </script>
    @endpush

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
    <script src="{{ asset('js/app-calendario-general.js') }}?v={{ time() }}"></script>
    @endpush

    @push('scripts')
    <script>
        (function waitFC() {
            if (typeof Calendar !== 'undefined') {
                initCalendarioGeneral(
                    @json($eventos),
                    @json($citaEstadoColores),
                    @json($citaEstadoLabels),
                    @json($timezone)
                );
            } else {
                setTimeout(waitFC, 50);
            }
        })();
    </script>
    @endpush

    @push('scripts')
    <script>
        // ===== MANEJO DEL MODAL DINÁMICO (PACIENTE/MASCOTA) =====
        document.addEventListener('DOMContentLoaded', function() {
            const btnModalNuevoRegistro = document.getElementById('btnModalNuevoRegistro');
            const modalNuevoRegistro = document.getElementById('modalNuevoRegistro');

            if (btnModalNuevoRegistro && modalNuevoRegistro) {
                const eventForm = document.getElementById('eventForm');

                // Función para actualizar el modo del modal
                function actualizarModoModal() {
                    const mascotaSelect = document.getElementById('eventMascota');
                    const especieSelect = document.getElementById('eventEspecie');
                    const formMascota = document.getElementById('formMascota');
                    const formPaciente = document.getElementById('formPaciente');
                    const btnText = document.getElementById('btnNuevoRegistroText');
                    const modalTitle = document.getElementById('modalNuevoRegistroTitle');
                    const modalBtnText = document.getElementById('modalCrearRegistroText');

                    // Si hay selects de mascota visibles, mostrar formulario de mascota
                    const isVetMode = (mascotaSelect && !mascotaSelect.closest('[style*="display: none"]')) ||
                                     (especieSelect && !especieSelect.closest('[style*="display: none"]'));

                    if (isVetMode) {
                        // Modo Veterinario
                        if (formMascota) formMascota.style.display = 'block';
                        if (formPaciente) formPaciente.style.display = 'none';
                        if (btnText) btnText.textContent = 'Nueva Mascota';
                        if (modalTitle) modalTitle.textContent = 'Nueva Mascota y Propietario';
                        if (modalBtnText) modalBtnText.textContent = 'Crear Mascota y seleccionar';
                    } else {
                        // Modo Humano
                        if (formMascota) formMascota.style.display = 'none';
                        if (formPaciente) formPaciente.style.display = 'block';
                        if (btnText) btnText.textContent = 'Nuevo Paciente';
                        if (modalTitle) modalTitle.textContent = 'Nuevo Paciente';
                        if (modalBtnText) modalBtnText.textContent = 'Crear y seleccionar';
                    }
                }

                // Ejecutar al abrir el modal
                modalNuevoRegistro.addEventListener('show.bs.modal', function() {
                    actualizarModoModal();

                    // Cargar especies si estamos en modo veterinario
                    const mascotaEspecieSelect = document.getElementById('mascotaEspecie');
                    if (mascotaEspecieSelect && mascotaEspecieSelect.options.length <= 1) {
                        fetch('/admin/api/calendario/especies')
                            .then(response => response.json())
                            .then(especies => {
                                mascotaEspecieSelect.innerHTML = '<option value="">Seleccione especie...</option>';
                                especies.forEach(especie => {
                                    mascotaEspecieSelect.innerHTML += `<option value="${especie.id}">${especie.nombre}</option>`;
                                });
                            })
                            .catch(err => console.error('Error cargando especies:', err));
                    }
                });

                // Cargar razas cuando cambia la especie
                const mascotaEspecieSelect = document.getElementById('mascotaEspecie');
                if (mascotaEspecieSelect) {
                    mascotaEspecieSelect.addEventListener('change', function() {
                        const especieId = this.value;
                        const razaSelect = document.getElementById('mascotaRaza');

                        if (!razaSelect) return;

                        razaSelect.innerHTML = '<option value="">Cargando razas...</option>';

                        if (!especieId) {
                            razaSelect.innerHTML = '<option value="">Primero seleccione especie</option>';
                            return;
                        }

                        fetch(`/admin/api/calendario/razas/${especieId}`)
                            .then(response => response.json())
                            .then(razas => {
                                razaSelect.innerHTML = '<option value="">Seleccionar raza</option>';
                                razas.forEach(raza => {
                                    razaSelect.innerHTML += `<option value="${raza.id}">${raza.nombre}</option>`;
                                });
                            })
                            .catch(err => console.error('Error cargando razas:', err));
                    });
                }

                // Manejar creación de mascota + propietario
                const modalCrearBtn = document.getElementById('modalCrearRegistroBtn');
                if (modalCrearBtn) {
                    modalCrearBtn.addEventListener('click', function() {
                        const formMascota = document.getElementById('formMascota');
                        const formMascotaVisible = formMascota && formMascota.style.display !== 'none';

                        if (formMascotaVisible) {
                            crearMascotaYPropietario();
                        } else {
                            // Para paciente humano, usar la lógica existente
                            $('#modalNuevoRegistro').modal('hide');
                        }
                    });
                }

                async function crearMascotaYPropietario() {
                    const data = {
                        nombre: document.getElementById('mascotaNombre')?.value,
                        sexo: document.getElementById('mascotaSexo')?.value,
                        especie_id: document.getElementById('mascotaEspecie')?.value,
                        raza_id: document.getElementById('mascotaRaza')?.value || null,
                        fecha_nacimiento: document.getElementById('mascotaFechaNacimiento')?.value || null,
                        peso_actual_kg: document.getElementById('mascotaPeso')?.value || null,
                        propietario: {
                            nombres: document.getElementById('propietarioNombres')?.value,
                            apellidos: document.getElementById('propietarioApellidos')?.value,
                            telefono: document.getElementById('propietarioTelefono')?.value,
                            telefono_alternativo: document.getElementById('propietarioTelefonoAlt')?.value || null,
                            email: document.getElementById('propietarioEmail')?.value || null,
                        }
                    };

                    // Validar campos requeridos
                    if (!data.nombre || !data.especie_id || !data.propietario.nombres || !data.propietario.apellidos || !data.propietario.telefono) {
                        alert('Por favor complete todos los campos obligatorios (*)');
                        return;
                    }

                    try {
                        const response = await fetch('/api/mascotas', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                            },
                            body: JSON.stringify(data)
                        });

                        const result = await response.json();

                        if (result.success) {
                            // Actualizar select de mascotas
                            const eventMascota = document.getElementById('eventMascota');
                            if (eventMascota) {
                                eventMascota.innerHTML += `<option value="${result.mascota_id}" selected>${data.nombre}</option>`;
                                $(eventMascota).trigger('change.select2');

                                // Disparar evento para cargar médicos
                                $(eventMascota).trigger('change');
                            }

                            // Cerrar modal
                            $('#modalNuevoRegistro').modal('hide');

                            // Limpiar formulario
                            limpiarFormularioMascota();

                            // Mostrar mensaje de éxito
                            alert('✅ Mascota creada exitosamente');
                        } else {
                            alert('❌ Error: ' + (result.message || 'No se pudo crear la mascota'));
                        }
                    } catch (error) {
                        console.error('Error creando mascota:', error);
                        alert('❌ Error de conexión. Intente nuevamente.');
                    }
                }

                function limpiarFormularioMascota() {
                    const fields = [
                        'mascotaNombre', 'mascotaEspecie', 'mascotaRaza', 'mascotaFechaNacimiento',
                        'mascotaPeso', 'propietarioNombres', 'propietarioApellidos',
                        'propietarioTelefono', 'propietarioTelefonoAlt', 'propietarioEmail'
                    ];

                    fields.forEach(id => {
                        const el = document.getElementById(id);
                        if (el) el.value = '';
                    });

                    const razaSelect = document.getElementById('mascotaRaza');
                    if (razaSelect) {
                        razaSelect.innerHTML = '<option value="">Primero seleccione especie</option>';
                    }
                }

                // ===== LISTENER PARA TIPO DE ATENCIÓN =====
                const tipoAtencionSelect = document.getElementById('eventTipoConsulta');
                if (tipoAtencionSelect) {
                    $(tipoAtencionSelect).on('change', function() {
                        const tipoId = $(this).val();

                        // Enviar a Livewire usando dispatch
                        if (typeof Livewire !== 'undefined') {
                            Livewire.dispatch('tipoAtencionSeleccionado', { tipoId: tipoId ? parseInt(tipoId) : null });
                            console.log('✅ Tipo de atención enviado:', tipoId);
                        }
                    });
                }
            }
        });
    </script>
    @endpush
</div>
