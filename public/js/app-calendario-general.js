/**
 * Calendario General - Citas + Consultas unificado
 * Integración con FullCalendar y Livewire
 */

'use strict';
var globalCompanyTimezone = 'local';

function initCalendarioGeneral(events, citaColores, citaLabels, companyTimezone) {
    globalCompanyTimezone = companyTimezone || 'local';
    (function ensureNowIndicatorStyle() {
        if (document.getElementById('fc-now-indicator-style')) return;
        var s = document.createElement('style');
        s.id = 'fc-now-indicator-style';
        s.textContent = '.fc .fc-timegrid-now-indicator-line{border-color:#d00;border-width:2px 0 0}.fc .fc-timegrid-now-indicator-arrow{border-top-color:#d00}';
        document.head.appendChild(s);
    })();

    const calendarEl = document.getElementById('calendar');
    if (!calendarEl) return;

    const appCalendarSidebar = document.querySelector('.app-calendar-sidebar');
    const appOverlay = document.querySelector('.app-overlay');
    const inlineCalendar = document.querySelector('.inline-calendar');
    const toggleCitas = document.getElementById('toggleCitas');
    const filtrosCitas = document.getElementById('filtros-citas');
    const filterInputsCita = Array.from(document.querySelectorAll('.input-filter-cita'));
    const medicosContainer = document.getElementById('medicos-calendario');
    const detailSidebar = document.getElementById('calendarioDetailSidebar');
    const filtrosContainer = document.getElementById('filtrosContainer');
    const toggleFiltrosBtn = document.getElementById('toggleFiltros');
    const floatingToggles = document.getElementById('floatingToggles');
    const floatingToggleCitas = document.getElementById('floatingToggleCitas');
    const calendarContent = document.querySelector('.app-calendar-content');
    const searchPaciente = document.getElementById('searchPaciente');
    const filterEspecialidad = document.getElementById('filterEspecialidad');
    const calendarLoading = document.getElementById('calendarLoading');

    // Cita form elements
    const addEventSidebar = document.getElementById('addEventSidebar');
    const offcanvasTitle = document.querySelector('#addEventSidebarLabel');
    const btnSubmit = document.getElementById('addEventBtn');
    const btnDeleteEvent = document.querySelector('.btn-delete-event');
    const btnCancel = document.querySelector('.btn-cancel');
    const btnSendReminder = document.querySelector('.btn-send-reminder');
    let btnConfirmEvent = null;
    let btnCancelCita = null;
    let btnReagendar = null;
    let btnReagendarAuto = null;
    const eventStartDate = document.getElementById('eventStartDate');
    const eventEndDate = document.getElementById('eventEndDate');
    const eventMotivo = document.getElementById('eventMotivo');
    const eventNotas = document.getElementById('eventNotas');
    const eventHoraInicio = document.getElementById('eventHoraInicio');
    const eventHoraFin = document.getElementById('eventHoraFin');
    const horarioError = document.getElementById('horarioError');
    const eventPaciente = $('#eventPaciente');
    const eventEspecialidad = $('#eventEspecialidad');
    const eventSubespecialidad = $('#eventSubespecialidad');
    const eventMedico = $('#eventMedico');
    const eventEstado = $('#eventEstado');
    const eventTipoConsulta = $('#eventTipoConsulta');
    const eventFecha = document.getElementById('eventFecha');
    const subespecialidadContainer = document.getElementById('subespecialidadContainer');
    const especialidadContainer = document.getElementById('especialidadContainer');
    const especialidadArrowTop = document.getElementById('especialidadArrowTop');
    const especialidadArrowBottom = document.getElementById('especialidadArrowBottom');
    const slotsContainer = document.getElementById('slotsContainer');
    const slotsMessage = document.getElementById('slotsMessage');
    const slotsList = document.getElementById('slotsList');

    const citaCalendarColors = {
        programada: '#ffc107',
        confirmada: '#0d6efd',
        cancelada: '#dc3545',
        no_asistio: '#6c757d',
        por_llegar: '#9E9E9E',
        sala_espera: '#FFA726',
        en_consultorio: '#42A5F5',
        en_consultorio_optometrista: '#7E57C2',
        en_gotas: '#26C6DA',
        dilatado: '#00BCD4',
        en_optica: '#AB47BC',
        en_estudio: '#EC407A',
        finalizada: '#66BB6A',
        pagada: '#4CAF50',
        borrador: '#BDBDBD'
    };

    const prioridadColors = {
        normal: '#4e73df',
        emergencia: '#e74a3b',
        alta: '#fd7e14'
    };

    let currentEvents = events || [];
    let calendar = null;
    let selectedMedicoFilter = null;
    let inlineCalInstance = null;
    let tooltipEl = null;
    let bsDetailSidebar = detailSidebar ? new bootstrap.Offcanvas(detailSidebar) : null;
    let bsAddEventSidebar = addEventSidebar ? new bootstrap.Offcanvas(addEventSidebar) : null;
    let isFormValid = false;
    let eventToUpdate = null;
    let selectedEspecialidadId = null;
    let selectedSubespecialidadId = null;
    let fechaFlatpickr = null;
    let currentSlotDuration = 30;
    var prefetchCache = {};
    var currentCitaId = null;

    let searchTerm = '';
    let selectedEspecialidadFilter = null;

    // ===================== DASHBOARD METRICS =====================
    function updateDashboardMetrics() {
        if (!currentEvents || currentEvents.length === 0) return;

        const now = new Date();
        const startOfWeek = new Date(now);
        startOfWeek.setDate(now.getDate() - now.getDay() + 1);
        const endOfWeek = new Date(startOfWeek);
        endOfWeek.setDate(startOfWeek.getDate() + 6);

        let citasSemana = 0;
        const medicoStats = {};
        const conflictos = [];

        currentEvents.forEach(function(ev) {
            const ep = ev.extendedProps || {};
            const tipoEvento = ep.tipo_evento || 'cita';
            const evStart = new Date(ev.start);

            if (evStart >= startOfWeek && evStart <= endOfWeek) {
                if (tipoEvento === 'cita') citasSemana++;
            }

            const medicoId = ep.medico_id;
            if (medicoId) {
                if (!medicoStats[medicoId]) medicoStats[medicoId] = 0;
                medicoStats[medicoId]++;
            }
        });

        // Detectar conflictos
        for (let i = 0; i < currentEvents.length; i++) {
            for (let j = i + 1; j < currentEvents.length; j++) {
                const ev1 = currentEvents[i], ev2 = currentEvents[j];
                const ep1 = ev1.extendedProps || {}, ep2 = ev2.extendedProps || {};
                if (ep1.medico_id === ep2.medico_id && ep1.tipo_evento === 'cita' && ep2.tipo_evento === 'cita') {
                    const start1 = new Date(ev1.start), end1 = new Date(ev1.end || ev1.start);
                    const start2 = new Date(ev2.start), end2 = new Date(ev2.end || ev2.start);
                    if (start1 < end2 && start2 < end1) {
                        conflictos.push({ ev1: ev1, ev2: ev2 });
                    }
                }
            }
        }

        const citasSemanaEl = document.getElementById('citasSemana');
        if (citasSemanaEl) citasSemanaEl.textContent = citasSemana;

        Object.keys(medicoStats).forEach(function(medicoId) {
            const count = medicoStats[medicoId];
            const countEl = document.querySelector('.medico-citas-count[data-medico-id="' + medicoId + '"]');
            const progressEl = document.querySelector('.medico-ocupacion[data-medico-id="' + medicoId + '"]');
            if (countEl) countEl.textContent = count;
            if (progressEl) {
                const percent = Math.min(100, (count / 10) * 100);
                progressEl.style.width = percent + '%';
                progressEl.classList.remove('bg-success', 'bg-warning', 'bg-danger');
                if (percent < 50) progressEl.classList.add('bg-success');
                else if (percent < 80) progressEl.classList.add('bg-warning');
                else progressEl.classList.add('bg-danger');
            }
        });

        const conflictosAlert = document.getElementById('conflictosAlert');
        const conflictosCount = document.getElementById('conflictosCount');
        if (conflictos.length > 0) {
            if (conflictosAlert) conflictosAlert.classList.remove('d-none');
            if (conflictosCount) conflictosCount.textContent = conflictos.length + ' cita' + (conflictos.length > 1 ? 's' : '') + ' con conflicto de horario';
        } else {
            if (conflictosAlert) conflictosAlert.classList.add('d-none');
        }
    }

    // ===================== LOADING INDICATOR =====================
    function showLoading() {
        if (calendarLoading) calendarLoading.classList.add('show');
    }
    function hideLoading() {
        if (calendarLoading) calendarLoading.classList.remove('show');
    }



    // ===================== TOGGLE FILTROS =====================
    const sidebar = document.getElementById('app-calendar-sidebar');
    let toggleSidebarBtn = null;

    function createToggleButton() {
        if (toggleSidebarBtn) return;
        const fcToolbar = document.querySelector('.fc-header-toolbar');
        const fcToolbarStart = fcToolbar ? fcToolbar.querySelector('.fc-toolbar-chunk:first-child') : null;
        if (fcToolbarStart) {
            toggleSidebarBtn = document.createElement('button');
            toggleSidebarBtn.type = 'button';
            toggleSidebarBtn.className = 'btn btn-sm btn-icon btn-outline-secondary me-2 show';
            toggleSidebarBtn.id = 'toggleSidebarBtn';
            toggleSidebarBtn.innerHTML = '<i class="ri ri-filter-3-line"></i>';
            toggleSidebarBtn.addEventListener('click', toggleSidebar);
            fcToolbarStart.insertBefore(toggleSidebarBtn, fcToolbarStart.firstChild);
        }
    }

    function toggleSidebar() {
        if (sidebar.classList.contains('sidebar-hidden')) {
            sidebar.classList.remove('sidebar-hidden');
            if (toggleSidebarBtn) {
                toggleSidebarBtn.classList.remove('show');
            }


        } else {
            sidebar.classList.add('sidebar-hidden');
            if (toggleSidebarBtn) {
                setTimeout(function() {
                    toggleSidebarBtn.classList.add('show');
                }, 300);
            }
        }
    }

    if (toggleFiltrosBtn) {
        toggleFiltrosBtn.addEventListener('click', toggleSidebar);
    }

    // ===================== FLOATING TOGGLES ON SCROLL =====================
    let scrollTimeout;
    function checkScroll() {
        const fcScroller = document.querySelector('.fc-scroller');
        const calBody = document.querySelector('.card-body');
        const scrollElement = fcScroller || calBody || calendarContent;

        if (!scrollElement || !floatingToggles) return;

        const scrollTop = scrollElement.scrollTop || window.pageYOffset || document.documentElement.scrollTop;

        if (scrollTop > 100) {
            floatingToggles.classList.add('show');
        } else {
            floatingToggles.classList.remove('show');
        }
    }

    // Listen to multiple scroll sources
    window.addEventListener('scroll', function() {
        clearTimeout(scrollTimeout);
        checkScroll();
        scrollTimeout = setTimeout(checkScroll, 100);
    });

    if (calendarContent) {
        calendarContent.addEventListener('scroll', function() {
            clearTimeout(scrollTimeout);
            checkScroll();
            scrollTimeout = setTimeout(checkScroll, 100);
        });
    }

    // Check on calendar render
    setTimeout(function() {
        const fcScrollers = document.querySelectorAll('.fc-scroller');
        fcScrollers.forEach(function(scroller) {
            scroller.addEventListener('scroll', function() {
                clearTimeout(scrollTimeout);
                checkScroll();
                scrollTimeout = setTimeout(checkScroll, 100);
            });
        });
    }, 1000);

    // Sync floating toggles with sidebar toggles (radio behavior)
    if (floatingToggleCitas && toggleCitas) {
        floatingToggleCitas.addEventListener('change', function() {
            toggleCitas.checked = floatingToggleCitas.checked;
            toggleCitas.dispatchEvent(new Event('change'));
        });
        toggleCitas.addEventListener('change', function() {
            floatingToggleCitas.checked = toggleCitas.checked;
        });
    }

    // ===================== SELECT2 INIT =====================
    if (eventPaciente.length) {
        eventPaciente.select2({ placeholder: 'Buscar paciente...', dropdownParent: eventPaciente.parent(), allowClear: true, language: { noResults: function() { return 'No se encontraron pacientes'; }, searching: function() { return 'Buscando...'; } } });
    }
    if (eventEspecialidad.length) {
        eventEspecialidad.select2({ placeholder: 'Seleccionar especialidad...', dropdownParent: eventEspecialidad.parent(), allowClear: true, language: { noResults: function() { return 'No se encontraron especialidades'; } } });
    }
    if (eventSubespecialidad.length) {
        eventSubespecialidad.select2({ placeholder: 'Seleccionar subespecialidad...', dropdownParent: eventSubespecialidad.parent(), allowClear: true, language: { noResults: function() { return 'No se encontraron subespecialidades'; } } });
    }
    if (eventMedico.length) {
        eventMedico.select2({ placeholder: 'Buscar médico...', dropdownParent: eventMedico.parent(), allowClear: true, language: { noResults: function() { return 'No se encontraron médicos'; }, searching: function() { return 'Buscando...'; } } });
    }
    if (eventEstado.length) {
        function renderEstadoBadge(option) {
            if (!option.id) return option.text;
            var color = $(option.element).data('color') || 'secondary';
            return "<span class='badge badge-dot bg-" + color + " me-2'></span>" + option.text;
        }
        eventEstado.select2({ placeholder: 'Seleccionar estado', dropdownParent: eventEstado.parent(), templateResult: renderEstadoBadge, templateSelection: renderEstadoBadge, minimumResultsForSearch: -1, escapeMarkup: function(es) { return es; } });
    }
    if (eventTipoConsulta.length) {
        function renderTipoConsultaOption(option) {
            if (!option.id) return option.text;
            var color = $(option.element).data('color') || '#6c757d';
            return "<span style='display:inline-block;width:12px;height:12px;border-radius:3px;background:" + color + ";margin-right:8px;vertical-align:middle;'></span>" + option.text;
        }
        eventTipoConsulta.select2({ placeholder: 'Sin tipo de consulta', dropdownParent: eventTipoConsulta.parent(), allowClear: true, templateResult: renderTipoConsultaOption, templateSelection: renderTipoConsultaOption, escapeMarkup: function(es) { return es; } });
    }

    // ===================== MODAL PACIENTE RAPIDO =====================
    var modalPacienteRapidoEl = document.getElementById('modalPacienteRapido');
    var modalPacienteRapido = modalPacienteRapidoEl ? new bootstrap.Modal(modalPacienteRapidoEl) : null;
    var mpEsMenor = document.getElementById('mpEsMenor');
    var mpTutorFields = document.getElementById('mpTutorFields');
    var modalPacienteCreateBtn = document.getElementById('modalPacienteCreateBtn');
    var btnModalPacienteRapido = document.getElementById('btnModalPacienteRapido');

    if (btnModalPacienteRapido) {
        btnModalPacienteRapido.addEventListener('click', function() {
            if (modalPacienteRapido) { try { modalPacienteRapido.show(); return; } catch(e){} }
            var el = document.getElementById('modalPacienteRapido');
            if (!el) return;
            var backdrop = document.createElement('div'); backdrop.className = 'modal-backdrop fade show'; document.body.appendChild(backdrop);
            el.classList.add('show'); el.style.display = 'block'; el.removeAttribute('aria-hidden'); el.setAttribute('aria-modal','true'); el.setAttribute('role','dialog');
            function closeFallback() { el.classList.remove('show'); el.style.display='none'; el.setAttribute('aria-hidden','true'); el.removeAttribute('aria-modal'); if(backdrop&&backdrop.parentNode) backdrop.parentNode.removeChild(backdrop); }
            var closeBtn = el.querySelector('.btn-close'); var cancelBtn = el.querySelector('.btn-outline-secondary');
            if(closeBtn) closeBtn.addEventListener('click',closeFallback,{once:true}); if(cancelBtn) cancelBtn.addEventListener('click',closeFallback,{once:true});
        });
    }
    if (mpEsMenor && mpTutorFields) { mpEsMenor.addEventListener('change', function() { mpTutorFields.style.display = mpEsMenor.checked ? '' : 'none'; }); }

    // Eventos para validación en tiempo real de todos los campos requeridos
    function agregarEventosValidacionRealTime() {
        // Nombres
        var nombresInput = document.getElementById('mpNombres');
        if (nombresInput) {
            nombresInput.addEventListener('blur', function() {
                var value = this.value.trim();
                clearFieldError('mpNombres');
                if (!value) {
                    showFieldError('nombres', 'Este campo es requerido');
                } else if (value.length < 2) {
                    showFieldError('nombres', 'Los nombres deben tener al menos 2 caracteres');
                }
                actualizarEstadoBotonSubmit();
            });

            nombresInput.addEventListener('input', function() {
                clearFieldError('mpNombres');
                actualizarEstadoBotonSubmit();
            });
        }

        // Apellidos
        var apellidosInput = document.getElementById('mpApellidos');
        if (apellidosInput) {
            apellidosInput.addEventListener('blur', function() {
                var value = this.value.trim();
                clearFieldError('mpApellidos');
                if (!value) {
                    showFieldError('apellidos', 'Este campo es requerido');
                } else if (value.length < 2) {
                    showFieldError('apellidos', 'Los apellidos deben tener al menos 2 caracteres');
                }
                actualizarEstadoBotonSubmit();
            });

            apellidosInput.addEventListener('input', function() {
                clearFieldError('mpApellidos');
                actualizarEstadoBotonSubmit();
            });
        }

        // Documento (opcional)
        var documentoInput = document.getElementById('mpDocumento');
        if (documentoInput) {
            documentoInput.addEventListener('blur', function() {
                var value = this.value.trim();
                clearFieldError('mpDocumento');
                if (value) {
                    var documentoLimpio = value.replace(/[^0-9A-Za-z]/g, '');
                    if (documentoLimpio.length < 4 || documentoLimpio.length > 20) {
                        showFieldError('documento_identidad', 'El documento debe tener entre 4 y 20 caracteres');
                    }
                }
                actualizarEstadoBotonSubmit();
            });

            documentoInput.addEventListener('input', function() {
                clearFieldError('mpDocumento');
                actualizarEstadoBotonSubmit();
            });
        }

        // Teléfono
        var telefonoInput = document.getElementById('mpTelefono');
        if (telefonoInput) {
            telefonoInput.addEventListener('blur', function() {
                var value = this.value.trim();
                clearFieldError('mpTelefono');
                if (value) {
                    var telefonoLimpio = value.replace(/[^0-9]/g, '');
                    if (telefonoLimpio.length < 7 || telefonoLimpio.length > 15) {
                        showFieldError('telefono', 'El teléfono debe tener entre 7 y 15 dígitos');
                    }
                }
                actualizarEstadoBotonSubmit();
            });

            telefonoInput.addEventListener('input', function() {
                clearFieldError('mpTelefono');
                actualizarEstadoBotonSubmit();
            });
        }

        // Fecha de nacimiento
        var fechaNacimientoInput = document.getElementById('mpFechaNacimiento');
        if (fechaNacimientoInput) {
            fechaNacimientoInput.addEventListener('blur', function() {
                var fechaValue = this.value;
                clearFieldError('mpFechaNacimiento');

                if (!fechaValue || fechaValue.trim() === '') {
                    showFieldError('fecha_nacimiento', 'Este campo es requerido');
                } else {
                    var fecha = new Date(fechaValue);
                    var hoy = new Date();
                    var fechaMinima = new Date();
                    fechaMinima.setFullYear(fechaMinima.getFullYear() - 120);

                    if (isNaN(fecha.getTime())) {
                        showFieldError('fecha_nacimiento', 'La fecha no es válida');
                    } else if (fecha > hoy) {
                        showFieldError('fecha_nacimiento', 'La fecha no puede ser futura');
                    } else if (fecha < fechaMinima) {
                        showFieldError('fecha_nacimiento', 'La fecha no puede ser mayor a 120 años');
                    }
                }
                actualizarEstadoBotonSubmit();
            });

            fechaNacimientoInput.addEventListener('input', function() {
                clearFieldError('mpFechaNacimiento');
                actualizarEstadoBotonSubmit();
            });
        }
    }

    // Llamar a la función cuando el modal se muestre
    if (modalPacienteRapidoEl) {
        modalPacienteRapidoEl.addEventListener('shown.bs.modal', function() {
            agregarEventosValidacionRealTime();
        });
    }

    if (modalPacienteRapidoEl) {
        modalPacienteRapidoEl.addEventListener('hidden.bs.modal', function() {
            // Limpiar campos
            ['mpNombres','mpApellidos','mpDocumento','mpTelefono','mpNickname','mpFechaNacimiento','mpTutorNombres','mpTutorApellidos','mpTutorTelefono'].forEach(function(id){ var el = document.getElementById(id); if(el) el.value=''; });
            if(mpEsMenor) mpEsMenor.checked=false; if(mpTutorFields) mpTutorFields.style.display='none';

            // Limpiar errores de validación
            clearPacienteValidationErrors();
        });
    }
    // Función para validar todos los campos requeridos del modal
    function validarCamposPacienteRequeridos() {
        var errores = [];

        // Validar nombres
        var nombresInput = document.getElementById('mpNombres');
        var nombresValue = nombresInput.value.trim();
        clearFieldError('mpNombres');

        if (!nombresValue) {
            showFieldError('nombres', 'Este campo es requerido');
            errores.push('nombres');
        } else if (nombresValue.length < 2) {
            showFieldError('nombres', 'Los nombres deben tener al menos 2 caracteres');
            errores.push('nombres');
        }

        // Validar apellidos
        var apellidosInput = document.getElementById('mpApellidos');
        var apellidosValue = apellidosInput.value.trim();
        clearFieldError('mpApellidos');

        if (!apellidosValue) {
            showFieldError('apellidos', 'Este campo es requerido');
            errores.push('apellidos');
        } else if (apellidosValue.length < 2) {
            showFieldError('apellidos', 'Los apellidos deben tener al menos 2 caracteres');
            errores.push('apellidos');
        }

        // Validar documento de identidad (opcional)
        var documentoInput = document.getElementById('mpDocumento');
        var documentoValue = documentoInput ? documentoInput.value.trim() : '';
        clearFieldError('mpDocumento');
        if (documentoValue) {
            var documentoLimpio = documentoValue.replace(/[^0-9A-Za-z]/g, '');
            if (documentoLimpio.length < 4 || documentoLimpio.length > 20) {
                showFieldError('documento_identidad', 'El documento debe tener entre 4 y 20 caracteres');
                errores.push('documento_identidad');
            }
        }

        // Validar teléfono (opcional)
        var telefonoInput = document.getElementById('mpTelefono');
        var telefonoValue = telefonoInput ? telefonoInput.value.trim() : '';
        clearFieldError('mpTelefono');
        if (telefonoValue) {
            var telefonoLimpio = telefonoValue.replace(/[^0-9]/g, '');
            if (telefonoLimpio.length < 7 || telefonoLimpio.length > 15) {
                showFieldError('telefono', 'El teléfono debe tener entre 7 y 15 dígitos');
                errores.push('telefono');
            }
        }

        // Validar fecha de nacimiento (opcional)
        var fechaInput = document.getElementById('mpFechaNacimiento');
        var fechaValue = fechaInput ? fechaInput.value : '';
        clearFieldError('mpFechaNacimiento');
        if (fechaValue && fechaValue.trim() !== '') {
            var fecha = new Date(fechaValue);
            var hoy = new Date();
            var fechaMinima = new Date();
            fechaMinima.setFullYear(fechaMinima.getFullYear() - 120);
            if (isNaN(fecha.getTime())) {
                showFieldError('fecha_nacimiento', 'La fecha no es válida');
                errores.push('fecha_nacimiento');
            } else if (fecha > hoy) {
                showFieldError('fecha_nacimiento', 'La fecha no puede ser futura');
                errores.push('fecha_nacimiento');
            } else if (fecha < fechaMinima) {
                showFieldError('fecha_nacimiento', 'La fecha no puede ser mayor a 120 años');
                errores.push('fecha_nacimiento');
            }
        }

        return errores.length === 0;
    }

    // Función para actualizar estado del botón submit
    function actualizarEstadoBotonSubmit() {
        var submitBtn = document.getElementById('modalPacienteCreateBtn');
        if (!submitBtn) return;

        // Verificar si hay errores de validación
        var tieneErrores = document.querySelectorAll('#modalPacienteRapido .is-invalid').length > 0;

        if (tieneErrores) {
            submitBtn.disabled = true;
            submitBtn.classList.add('disabled');
        } else {
            submitBtn.disabled = false;
            submitBtn.classList.remove('disabled');
        }
    }

    if (modalPacienteCreateBtn) {
        modalPacienteCreateBtn.addEventListener('click', function() {
            // Limpiar errores previos
            clearPacienteValidationErrors();

            // Validar todos los campos requeridos antes de enviar
            if (!validarCamposPacienteRequeridos()) {
                actualizarEstadoBotonSubmit();
                return; // Prevenir envío si hay error
            }

            var data = {
                nombres: (document.getElementById('mpNombres')||{}).value||'',
                apellidos: (document.getElementById('mpApellidos')||{}).value||'',
                documento_identidad: (document.getElementById('mpDocumento')||{}).value||'',
                telefono: (document.getElementById('mpTelefono')||{}).value||'',
                nickname: (document.getElementById('mpNickname')||{}).value||'',
                fecha_nacimiento: (document.getElementById('mpFechaNacimiento')||{}).value||'',
                es_menor: mpEsMenor ? mpEsMenor.checked : false,
                tutor: {
                    nombres: (document.getElementById('mpTutorNombres')||{}).value||'',
                    apellidos: (document.getElementById('mpTutorApellidos')||{}).value||'',
                    telefono: (document.getElementById('mpTutorTelefono')||{}).value||''
                }
            };

            var comp = getLivewireComponent();
            if(comp) {
                comp.call('crearPacienteRapido', data).then(function(response) {
                    if (response && response.errors) {
                        showPacienteValidationErrors(response.errors);
                        actualizarEstadoBotonSubmit();
                    }
                });
            }
        });
    }

    // Función para mostrar errores de validación en el modal
    function showPacienteValidationErrors(errors) {
        // Mostrar errores generales
        if (errors.general) {
            showErrorAlert(errors.general);
        }

        // Mostrar errores por campo
        for (var field in errors) {
            if (field !== 'general') {
                showFieldError(field, errors[field]);
            }
        }
    }

    // Función para mostrar error en un campo específico
    function showFieldError(fieldName, errorMessage) {
        var fieldId = getFieldIdByName(fieldName);
        var field = document.getElementById(fieldId);

        if (field) {
            // Agregar clase de error
            field.classList.add('is-invalid');

            // Crear o actualizar mensaje de error
            var errorElement = document.getElementById(fieldId + '_error');
            if (!errorElement) {
                errorElement = document.createElement('div');
                errorElement.id = fieldId + '_error';
                errorElement.className = 'invalid-feedback d-block';
                field.parentNode.appendChild(errorElement);
            }
            errorElement.textContent = errorMessage;

            // Agregar evento para limpiar error al escribir
            field.addEventListener('input', function() {
                clearFieldError(fieldId);
            }, { once: true });
        }
    }

    // Función para obtener el ID del campo por nombre
    function getFieldIdByName(fieldName) {
        var fieldMap = {
            'nombres': 'mpNombres',
            'apellidos': 'mpApellidos',
            'documento_identidad': 'mpDocumento',
            'telefono': 'mpTelefono',
            'nickname': 'mpNickname',
            'fecha_nacimiento': 'mpFechaNacimiento',
            'tutor_nombres': 'mpTutorNombres',
            'tutor_apellidos': 'mpTutorApellidos',
            'tutor_telefono': 'mpTutorTelefono'
        };
        return fieldMap[fieldName] || fieldName;
    }

    // Función para limpiar error de un campo
    function clearFieldError(fieldId) {
        var field = document.getElementById(fieldId);
        if (field) {
            field.classList.remove('is-invalid');
            var errorElement = document.getElementById(fieldId + '_error');
            if (errorElement) {
                errorElement.remove();
            }
        }
    }

    // Función para limpiar todos los errores de validación
    function clearPacienteValidationErrors() {
        // Limpiar clases de error
        var fields = ['mpNombres', 'mpApellidos', 'mpDocumento', 'mpTelefono', 'mpNickname', 'mpFechaNacimiento', 'mpTutorNombres', 'mpTutorApellidos', 'mpTutorTelefono'];
        fields.forEach(function(fieldId) {
            clearFieldError(fieldId);
        });

        // Limpiar alerta de error general
        clearErrorAlert();
    }

    // Función para mostrar alerta de error general
    function showErrorAlert(message) {
        var alertContainer = document.getElementById('pacienteValidationAlert');
        if (!alertContainer) {
            alertContainer = document.createElement('div');
            alertContainer.id = 'pacienteValidationAlert';
            alertContainer.className = 'alert alert-danger alert-dismissible fade show mb-3';
            alertContainer.innerHTML = '<button type="button" class="btn-close" data-bs-dismiss="alert"></button><span id="pacienteValidationAlertText"></span>';

            var modalBody = document.querySelector('#modalPacienteRapido .modal-body');
            if (modalBody) {
                modalBody.insertBefore(alertContainer, modalBody.firstChild);
            }
        }
        document.getElementById('pacienteValidationAlertText').textContent = message;
    }

    // Función para limpiar alerta de error general
    function clearErrorAlert() {
        var alertContainer = document.getElementById('pacienteValidationAlert');
        if (alertContainer) {
            alertContainer.remove();
        }
    }

    // ===================== CASCADE LOGIC =====================
    function showEspecialidadField(visible) {
        if (especialidadContainer) especialidadContainer.style.display = visible ? '' : 'none';
        if (especialidadArrowTop) especialidadArrowTop.style.display = visible ? '' : 'none';
        if (especialidadArrowBottom) especialidadArrowBottom.style.display = visible ? '' : 'none';
    }

    function resetCascadeFrom(level) {
        if (level <= 2) { showEspecialidadField(false); eventEspecialidad.val('').trigger('change.select2'); eventEspecialidad.prop('disabled',true); eventEspecialidad.empty().append('<option value="">Primero seleccione un paciente</option>'); selectedEspecialidadId = null; }
        if (level <= 3) { eventSubespecialidad.val('').trigger('change.select2'); if(subespecialidadContainer) subespecialidadContainer.style.display='none'; selectedSubespecialidadId = null; }
        if (level <= 4) { eventMedico.val('').trigger('change.select2'); eventMedico.prop('disabled',true); eventMedico.empty().append('<option value="">Primero seleccione un paciente</option>'); }
        if (level <= 5) { if(slotsContainer) slotsContainer.style.display='none'; if(slotsList) slotsList.innerHTML=''; if(slotsMessage){slotsMessage.classList.add('d-none');slotsMessage.textContent='';} if(eventStartDate) eventStartDate.value=''; if(eventEndDate) eventEndDate.value=''; }
    }

    var horarioLaboralMedico = null;

    function loadSlots(medicoId, dateStr, selectStartFull) {
        if (!medicoId || !dateStr) return;
        var comp = getLivewireComponent(); if(!comp) return;
        if(slotsList) slotsList.innerHTML = '<div class="text-center w-100 py-2"><div class="spinner-border spinner-border-sm" role="status"></div></div>';
        if(slotsContainer) slotsContainer.style.display=''; if(slotsMessage) slotsMessage.classList.add('d-none');
        comp.call('fetchHorariosDisponibles', parseInt(medicoId), dateStr).then(function(result) {
            if(slotsList) slotsList.innerHTML='';
            if (!result||!result.disponible) { if(slotsMessage){slotsMessage.textContent=result?result.mensaje:'No hay horarios disponibles.';slotsMessage.classList.remove('d-none');} return; }
            if(slotsMessage) slotsMessage.classList.add('d-none');
            currentSlotDuration = result.duracion_cita || 30;

            if (result.horario_laboral) {
                horarioLaboralMedico = result.horario_laboral;
            }

            var esAltaEmergencia = (eventPrioridad && (eventPrioridad.value === 'alta' || eventPrioridad.value === 'emergencia'));

            var matched = false;
           if (slotsList) result.slots.forEach(function(slot) {
                var btn = document.createElement('button'); btn.type='button';
                var timeOnly = slot.label.split(' - ')[0];

                if (esAltaEmergencia) {
                    btn.className = 'btn btn-sm rounded-pill slot-btn btn-primary';
                    btn.disabled = true;
                } else {
                    btn.className = 'btn btn-sm rounded-pill slot-btn ' + (slot.disponible ? 'btn-outline-primary' : 'btn-outline-secondary ocupado');
                    btn.disabled = !slot.disponible;
                }

                btn.textContent = timeOnly;

                if (!esAltaEmergencia && slot.disponible) {
                    if (selectStartFull && slot.inicio_full === selectStartFull) {
                        btn.classList.add('selected','btn-primary');
                        btn.classList.remove('btn-outline-primary');
                        if(eventStartDate) eventStartDate.value=slot.inicio_full;
                        if(eventEndDate) eventEndDate.value=slot.fin_full;

                        // Sincronizar los campos de hora visibles
                        if(eventHoraInicio && slot.inicio_full) {
                            var inicioParts = slot.inicio_full.split(' ');
                            if(inicioParts.length >= 2) {
                                eventHoraInicio.value = inicioParts[1].substring(0, 5);
                            }
                        }
                        if(eventHoraFin && slot.fin_full) {
                            var finParts = slot.fin_full.split(' ');
                            if(finParts.length >= 2) {
                                eventHoraFin.value = finParts[1].substring(0, 5);
                            }
                        }
                        matched=true;
                    }
                    btn.addEventListener('click', function() {
                        slotsList.querySelectorAll('.slot-btn').forEach(function(b){b.classList.remove('selected','btn-primary');b.classList.add('btn-outline-primary');});
                        btn.classList.add('selected','btn-primary'); btn.classList.remove('btn-outline-primary');
                        if(eventStartDate) eventStartDate.value=slot.inicio_full; if(eventEndDate) eventEndDate.value=slot.fin_full;

                        // Sincronizar los campos de hora visibles
                        if(eventHoraInicio && slot.inicio_full) {
                            var inicioParts = slot.inicio_full.split(' ');
                            if(inicioParts.length >= 2) {
                                eventHoraInicio.value = inicioParts[1].substring(0, 5);
                            }
                        }
                        if(eventHoraFin && slot.fin_full) {
                            var finParts = slot.fin_full.split(' ');
                            if(finParts.length >= 2) {
                                eventHoraFin.value = finParts[1].substring(0, 5);
                            }
                        }
                    });
                }
                slotsList.appendChild(btn);
            });
            if (!matched && !selectStartFull && result.primer_disponible && !esAltaEmergencia && slotsList) {
                var firstAvailable = slotsList.querySelector('.slot-btn:not(.ocupado)');
                if(firstAvailable) {
                    firstAvailable.click();
                    // Sincronizar los campos de hora visibles
                    var slot = result.slots.find(function(s) { return s.disponible; });
                    if(slot) {
                        if(eventHoraInicio && slot.inicio_full) {
                            var inicioParts = slot.inicio_full.split(' ');
                            if(inicioParts.length >= 2) {
                                eventHoraInicio.value = inicioParts[1].substring(0, 5);
                            }
                        }
                        if(eventHoraFin && slot.fin_full) {
                            var finParts = slot.fin_full.split(' ');
                            if(finParts.length >= 2) {
                                eventHoraFin.value = finParts[1].substring(0, 5);
                            }
                        }
                    }
                }
            }

            if (esAltaEmergencia && slotsMessage) {
                slotsMessage.textContent = 'Horário personalizado activo. Use los campos de hora de inicio y fin.';
                slotsMessage.classList.remove('d-none', 'alert-warning');
                slotsMessage.classList.add('alert-info');
            }

            validatePrioridadTime();
        });
    }

    // Paciente -> Especialidades
    if (eventPaciente.length) {
        eventPaciente.on('change', function() {
            var pacienteId = eventPaciente.val(); if(!pacienteId){resetCascadeFrom(2);return;}
            var comp = getLivewireComponent(); if(!comp) return; resetCascadeFrom(3);
            showEspecialidadField(false);
            comp.call('fetchMedicos', null, null).then(function(medicos) {
                eventMedico.empty().append('<option value="">Seleccionar médico</option>');
                medicos.forEach(function(m){eventMedico.append('<option value="'+m.id+'">'+m.nombre+'</option>');});
                eventMedico.prop('disabled', false);
                eventMedico.trigger('change.select2');
            });
        });
    }
    // Especialidad -> Subespecialidades + Medicos
    if (eventEspecialidad.length) {
        eventEspecialidad.on('change', function() {
            var espId = eventEspecialidad.val(); selectedEspecialidadId = espId||null; if(!espId){resetCascadeFrom(3);return;}
            var comp = getLivewireComponent(); if(!comp) return; resetCascadeFrom(4);
            comp.call('fetchSubespecialidades', parseInt(espId)).then(function(subs) {
                if(subs&&subs.length>0){
                    eventSubespecialidad.empty().append('<option value="">Opcional - Seleccionar subespecialidad</option>');
                    subs.forEach(function(s){eventSubespecialidad.append('<option value="'+s.id+'">'+s.nombre+'</option>');});
                    if(subespecialidadContainer) subespecialidadContainer.style.display='';
                    eventSubespecialidad.trigger('change.select2');
                } else {
                    if(subespecialidadContainer) subespecialidadContainer.style.display='none';
                }
            });
            comp.call('fetchMedicos', parseInt(espId), null).then(function(medicos) {
                eventMedico.empty().append('<option value="">Seleccionar médico</option>');
                medicos.forEach(function(m){eventMedico.append('<option value="'+m.id+'">'+m.nombre+'</option>');});
                eventMedico.prop('disabled',false);
                if (medicos.length === 1) {
                    // Auto-seleccionar si solo hay un médico
                    eventMedico.val(medicos[0].id).trigger('change');
                } else {
                    eventMedico.trigger('change.select2');
                }
            });
        });
    }
    // Subespecialidad -> reload Medicos
    if (eventSubespecialidad.length) {
        eventSubespecialidad.on('change', function() {
            var subId = eventSubespecialidad.val(); selectedSubespecialidadId = subId||null; if(!selectedEspecialidadId) return;
            var comp = getLivewireComponent(); if(!comp) return; resetCascadeFrom(5); eventMedico.prop('disabled',true);
            comp.call('fetchMedicos', parseInt(selectedEspecialidadId), subId?parseInt(subId):null).then(function(medicos) {
                eventMedico.empty().append('<option value="">Seleccionar médico</option>');
                medicos.forEach(function(m){eventMedico.append('<option value="'+m.id+'">'+m.nombre+'</option>');});
                eventMedico.prop('disabled',false); eventMedico.trigger('change.select2');
            });
        });
    }
    // Medico -> load slots if date set
    if (eventMedico.length) {
        eventMedico.on('change', function() { var medicoId=eventMedico.val(); resetCascadeFrom(5); var currentDate=eventFecha?eventFecha.value:null; if(currentDate&&medicoId) loadSlots(medicoId,currentDate); });
    }
    // Fecha flatpickr
    if (eventFecha) {
        fechaFlatpickr = flatpickr(eventFecha, {
            dateFormat: 'Y-m-d', minDate: 'today', static: true,
            locale: typeof flatpickr !== 'undefined' && flatpickr.l10ns && flatpickr.l10ns.es ? 'es' : 'default',
            onChange: function(selectedDates, dateStr) {
                if(!dateStr){resetCascadeFrom(6);return;}
                var medicoId=eventMedico.val();
                if(!medicoId) return;
                loadSlots(medicoId, dateStr);
                validateManualTime();
            }
        });
    }

    // Validación de horarios manuales
    if (eventHoraInicio) {
        eventHoraInicio.addEventListener('change', function() {
            validateManualTime();
        });
    }
    if (eventHoraFin) {
        eventHoraFin.addEventListener('change', function() {
            validateManualTime();
        });
    }

    function validateManualTime() {
        var fecha = eventFecha ? eventFecha.value : null;
        var horaInicio = eventHoraInicio ? eventHoraInicio.value : null;
        var horaFin = eventHoraFin ? eventHoraFin.value : null;
        var btnSubmit = document.getElementById('addEventBtn');

        if (!fecha || !horaInicio || !horaFin) {
            if (horarioError) {
                horarioError.style.display = 'none';
                horarioError.textContent = '';
            }
            if (btnSubmit) btnSubmit.disabled = false;
            return true;
        }

        var inicioMin = parseInt(horaInicio.replace(':', ''), 10);
        var finMin = parseInt(horaFin.replace(':', ''), 10);

        if (finMin <= inicioMin) {
            if (horarioError) {
                horarioError.textContent = 'La hora de fin debe ser posterior a la hora de inicio.';
                horarioError.style.display = '';
            }
            if (btnSubmit) btnSubmit.disabled = true;
            return false;
        }

        // Validar que esté dentro del horario laboral del médico
        if (horarioLaboralMedico) {
            var laborInicio = parseInt(horarioLaboralMedico.hora_inicio.replace(':', ''), 10);
            var laborFin = parseInt(horarioLaboralMedico.hora_fin.replace(':', ''), 10);

            if (inicioMin < laborInicio || finMin > laborFin) {
                if (horarioError) {
                    horarioError.textContent = 'El horario debe estar dentro de la jornada del médico (' + horarioLaboralMedico.hora_inicio + ' - ' + horarioLaboralMedico.hora_fin + ').';
                    horarioError.style.display = '';
                }
                if (btnSubmit) btnSubmit.disabled = true;
                return false;
            }
        }

        // Si la validación es exitosa, actualizar campos ocultos
        if (eventStartDate && eventEndDate && fecha && horaInicio && horaFin) {
            eventStartDate.value = fecha + ' ' + horaInicio + ':00';
            eventEndDate.value = fecha + ' ' + horaFin + ':00';
        }

        if (horarioError) {
            horarioError.style.display = 'none';
            horarioError.textContent = '';
        }
        if (btnSubmit) btnSubmit.disabled = false;
        return true;
    }

    // Prioridad change
    var eventPrioridad = document.getElementById('eventPrioridad');
    if (eventPrioridad) {
        eventPrioridad.addEventListener('change', function() {
            updatePriorityMode();
            var medicoId = eventMedico.val();
            var currentDate = eventFecha ? eventFecha.value : null;
            if (currentDate && medicoId) loadSlots(medicoId, currentDate);
        });
    }

    // ===================== PRIORITY MODE =====================
    function updatePriorityMode() {
        var prioridad = eventPrioridad ? eventPrioridad.value : 'normal';
        var section = document.getElementById('prioridadAltaEmergenciaSection');
        var isAltaEmergencia = prioridad === 'alta' || prioridad === 'emergencia';

        if (section) {
            section.style.display = isAltaEmergencia ? '' : 'none';
        }

        // Actualizar visualización de slots según modo
        updateSlotsDisplay(isAltaEmergencia);

        validatePrioridadTime();
    }

    function updateSlotsDisplay(isAltaEmergencia) {
        var slotsList = document.getElementById('slotsList');
        if (!slotsList) return;

        var slotButtons = slotsList.querySelectorAll('.slot-btn');
        if (!slotButtons || slotButtons.length === 0) return;

        slotButtons.forEach(function(btn) {
            if (!btn) return;

            if (isAltaEmergencia) {
                // En modo alta/emergencia, mostrar slots ocupados deshabilitados
                if (btn.classList && btn.classList.contains('ocupado')) {
                    btn.style.opacity = '0.4';
                    btn.style.pointerEvents = 'none';
                    btn.style.textDecoration = 'line-through';
                } else {
                    btn.style.opacity = '1';
                    btn.style.pointerEvents = 'none';
                    btn.style.textDecoration = 'none';
                }
            } else {
                // En modo normal, mantener slots no seleccionables
                btn.style.opacity = '0.7';
                btn.style.pointerEvents = 'none';
                btn.style.textDecoration = 'none';
            }
        });
    }

    function validatePrioridadTime() {
        var prioridad = eventPrioridad ? eventPrioridad.value : 'normal';
        var isAltaEmergencia = prioridad === 'alta' || prioridad === 'emergencia';
        var errorEl = document.getElementById('horarioPrioridadError');
        var btnSubmit = document.getElementById('addEventBtn');

        // Usar los mismos campos de hora para todos los modos
        var horaInicio = eventHoraInicio;
        var horaFin = eventHoraFin;

        if (!isAltaEmergencia) {
            if (errorEl) { errorEl.style.display = 'none'; errorEl.textContent = ''; }
            // La validación normal se hace en validateManualTime
            return true;
        }

        if (!horaInicio || !horaFin || !horaInicio.value || !horaFin.value) {
            if (errorEl) {
                errorEl.textContent = 'Debe seleccionar hora de inicio y fin.';
                errorEl.style.display = '';
            }
            if (btnSubmit) { btnSubmit.disabled = true; }
            return false;
        }

        var inicioMin = parseInt(horaInicio.value.replace(':', ''), 10);
        var finMin = parseInt(horaFin.value.replace(':', ''), 10);

        if (finMin <= inicioMin) {
            if (errorEl) {
                errorEl.textContent = 'La hora de fin debe ser posterior a la hora de inicio.';
                errorEl.style.display = '';
            }
            if (btnSubmit) { btnSubmit.disabled = true; }
            return false;
        }

        if (horarioLaboralMedico) {
            var laborInicio = parseInt(horarioLaboralMedico.hora_inicio.replace(':', ''), 10);
            var laborFin = parseInt(horarioLaboralMedico.hora_fin.replace(':', ''), 10);

            if (inicioMin < laborInicio || finMin > laborFin) {
                if (errorEl) {
                    errorEl.textContent = 'El horario debe estar dentro de la jornada del médico (' + horarioLaboralMedico.hora_inicio + ' - ' + horarioLaboralMedico.hora_fin + ').';
                    errorEl.style.display = '';
                }
                if (btnSubmit) { btnSubmit.disabled = true; }
                return false;
            }
        }

        if (errorEl) { errorEl.style.display = 'none'; errorEl.textContent = ''; }
        if (btnSubmit) { btnSubmit.disabled = false; }
        return true;
    }

    // ===================== DATE VALIDATION =====================
    function isPastDateTime(date, info) {
        if (!date || !info) return false;
        
        try {
            // FullCalendar usa 'dateStr' en clics y 'event.startStr' en arrastres
            var rawStr = info.dateStr || (info.event ? info.event.startStr : null);
            if (!rawStr) return false;

            // 1. Obtener la cadena de la fecha seleccionada (formato ISO: YYYY-MM-DDTHH:mm:ss)
            var selectedStr = rawStr.includes('T') ? rawStr : rawStr + 'T00:00:00';
            
            // 2. Obtener el "ahora" en la zona horaria LOCAL del navegador
            // para que coincida exactamente con lo que el usuario ve en la línea roja.
            var nowStr = new Date().toLocaleString('sv-SE').replace(' ', 'T');

            // 3. Comparar como cadenas (orden lexicográfico funciona para ISO)
            // Añadimos un pequeño margen: si son el mismo minuto, permitimos.
            // Para eso comparamos solo hasta los minutos.
            var selectedMin = selectedStr.substring(0, 16);
            var nowMin = nowStr.substring(0, 16);
            
            if (selectedMin < nowMin) {
                showPastAlert(selectedMin.replace('T', ' '), nowMin.replace('T', ' '));
                return true;
            }
        } catch (e) {
            console.error('Error in isPastDateTime:', e);
        }
        return false;
    }

    function showPastAlert(selected, now) {
        // Extraer solo la hora para el mensaje
        var selTime = selected.substring(11);
        var nowTime = now.substring(11);
        var msg = 'No se pueden crear citas en el pasado. (Seleccionado: ' + selTime + ', Ahora: ' + nowTime + ')';
        
        if (window.Swal) {
            Swal.fire({ 
                icon: 'warning', 
                title: 'Fecha no permitida', 
                text: msg, 
                confirmButtonText: 'Entendido',
                confirmButtonColor: '#ffc107'
            });
        } else {
            alert(msg);
        }
    }

    // ===================== FORM HELPERS =====================
    function formatDateForLivewire(date) {
        if (!date) return '';
        
        try {
            // Intentamos formatear usando la zona horaria global de la empresa
            // Esto asegura que si el usuario está en México y la clínica en Caracas,
            // se envíe la hora de Caracas al servidor.
            var options = {
                timeZone: globalCompanyTimezone !== 'local' ? globalCompanyTimezone : undefined,
                year: 'numeric', month: '2-digit', day: '2-digit',
                hour: '2-digit', minute: '2-digit', second: '2-digit',
                hour12: false
            };
            var formatter = new Intl.DateTimeFormat('en-US', options);
            var parts = formatter.formatToParts(date);
            
            var p = {};
            parts.forEach(function(part) { p[part.type] = part.value; });
            
            // Reconstruir formato Y-m-d H:i:s (Intl en-US da m/d/Y por defecto en format())
            return p.year + '-' + p.month + '-' + p.day + ' ' + p.hour + ':' + p.minute + ':' + p.second;
        } catch (e) {
            console.error('Error formatting date for timezone:', e);
            // Fallback al comportamiento anterior si falla Intl (aunque es moderno)
            return date.getFullYear() + '-' + String(date.getMonth()+1).padStart(2,'0') + '-' + String(date.getDate()).padStart(2,'0') + ' ' + String(date.getHours()).padStart(2,'0') + ':' + String(date.getMinutes()).padStart(2,'0') + ':' + String(date.getSeconds()).padStart(2,'0');
        }
    }

    function resetValues() {
        if(eventStartDate) eventStartDate.value=''; if(eventEndDate) eventEndDate.value='';
        if(eventMotivo) eventMotivo.value=''; if(eventNotas) eventNotas.value='';
        if(eventFecha) eventFecha.value=''; if(fechaFlatpickr) fechaFlatpickr.clear();
        if(eventPaciente.length) eventPaciente.val('').trigger('change.select2');
        if(eventEstado.length) eventEstado.val('programada').trigger('change');
        if(eventPrioridad) eventPrioridad.value = 'normal';
        updatePriorityMode();
        horarioLaboralMedico = null;
        if(typeof eventManualTime !== 'undefined' && eventManualTime) eventManualTime.value='';
        if(eventTipoConsulta.length) eventTipoConsulta.val('').trigger('change.select2');
        if(eventHoraInicio) eventHoraInicio.value = '';
        if(eventHoraFin) eventHoraFin.value = '';
        if(horarioError) { horarioError.style.display = 'none'; horarioError.textContent = ''; }
        resetCascadeFrom(2);
        // No limpiar eventToUpdate aquí - se necesita para edición
    }

    function closeAndResetForm() {
        resetValues();
        eventToUpdate = null;
        currentCitaId = null;
        // Restaurar botón a modo "Agregar"
        if (btnSubmit) {
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = '<i class="ri ri-add-line me-1"></i> Agregar';
            btnSubmit.classList.remove('btn-update-event');
            btnSubmit.classList.add('btn-add-event');
        }
        // Ocultar botones de acciones de cita
        if(btnSendReminder) btnSendReminder.classList.add('d-none');
        if(btnConfirmEvent) btnConfirmEvent.classList.add('d-none');
        if(btnCancelCita) btnCancelCita.classList.add('d-none');
        if(btnReagendar) btnReagendar.classList.add('d-none');
        if(btnReagendarAuto) btnReagendarAuto.classList.add('d-none');
        // Restaurar campos ocultos en modo edición
        var estadoContainer = document.getElementById('estadoContainer');
        if(estadoContainer) estadoContainer.style.display = '';
        var nuevoPacienteContainer = document.getElementById('nuevoPacienteContainer');
        if(nuevoPacienteContainer) nuevoPacienteContainer.style.display = '';
        var editInfoBanner = document.getElementById('editInfoBanner');
        if(editInfoBanner) editInfoBanner.style.display = 'none';
        // Re-habilitar paciente
        if(eventPaciente && eventPaciente.length) eventPaciente.prop('disabled', false);
        // Cambiar título a "Nueva Cita"
        if(offcanvasTitle) offcanvasTitle.innerHTML = 'Nueva Cita';
    }

    function getCitaId(eventId) {
        if (!eventId) return null;
        return parseInt(String(eventId).replace('cita_', ''));
    }

    // ===================== CITA EVENT CLICK =====================
    function citaEventClick(info) {
        info.jsEvent.preventDefault();

        // Guardar datos del evento ANTES de cualquier manipulación
        eventToUpdate = info.event;
        currentCitaId = getCitaId(info.event.id);

        if (bsAddEventSidebar) bsAddEventSidebar.show();
        if (offcanvasTitle) offcanvasTitle.innerHTML = 'Editar Cita';
        if (btnSubmit) { btnSubmit.innerHTML = '<i class="ri ri-save-line me-1"></i> Actualizar'; btnSubmit.classList.add('btn-update-event'); btnSubmit.classList.remove('btn-add-event'); }

        if (btnSendReminder) btnSendReminder.classList.remove('d-none');
        if(btnConfirmEvent) btnConfirmEvent.classList.remove('d-none');
        if(btnCancelCita) btnCancelCita.classList.remove('d-none');
        if(btnReagendar) btnReagendar.classList.remove('d-none');
        if(btnReagendarAuto) btnReagendarAuto.classList.remove('d-none');
        // Modo edición: ocultar estado (se cambia desde el modal), ocultar botón nuevo paciente, deshabilitar paciente
        var estadoContainer = document.getElementById('estadoContainer');
        if(estadoContainer) estadoContainer.style.display = 'none';
        var nuevoPacienteContainer = document.getElementById('nuevoPacienteContainer');
        if(nuevoPacienteContainer) nuevoPacienteContainer.style.display = 'none';
        var editInfoBanner = document.getElementById('editInfoBanner');
        if(editInfoBanner) editInfoBanner.style.display = '';
        // Deshabilitar cambio de paciente en edición
        if(eventPaciente.length) eventPaciente.prop('disabled', true);

        var ep = eventToUpdate.extendedProps;
        if(eventMotivo) eventMotivo.value = ep.motivo || ep.descripcion || '';
        if(eventNotas) eventNotas.value = ep.notas || '';
        if(eventEstado.length) eventEstado.val(ep.estado || 'programada').trigger('change');
        if(eventTipoConsulta.length) eventTipoConsulta.val(ep.tipo_consulta_id || '').trigger('change.select2');
        if(eventStartDate) eventStartDate.value = formatDateForLivewire(eventToUpdate.start);
        if(eventEndDate) eventEndDate.value = eventToUpdate.end ? formatDateForLivewire(eventToUpdate.end) : formatDateForLivewire(eventToUpdate.start);

        var eventDate = eventToUpdate.start;
        var dateOnly = eventDate.getFullYear()+'-'+String(eventDate.getMonth()+1).padStart(2,'0')+'-'+String(eventDate.getDate()).padStart(2,'0');
        if(fechaFlatpickr) fechaFlatpickr.setDate(dateOnly, false);

        // Cargar horas manuales al editar
        var eventTimeStart = eventToUpdate.start;
        var eventTimeEnd = eventToUpdate.end;
        if(eventTimeStart && eventHoraInicio) {
            var horaInicioStr = String(eventTimeStart.getHours()).padStart(2,'0')+':'+String(eventTimeStart.getMinutes()).padStart(2,'0');
            eventHoraInicio.value = horaInicioStr;
        }
        if(eventTimeEnd && eventHoraFin) {
            var horaFinStr = String(eventTimeEnd.getHours()).padStart(2,'0')+':'+String(eventTimeEnd.getMinutes()).padStart(2,'0');
            eventHoraFin.value = horaFinStr;
        }

        var comp = getLivewireComponent();
        if (comp && eventPaciente.length) {
            eventPaciente.val(ep.paciente_id).trigger('change.select2');
            // En edición: ocultar especialidad y subespecialidad siempre
            showEspecialidadField(false);
            if(subespecialidadContainer) subespecialidadContainer.style.display='none';

            comp.call('fetchEspecialidades').then(function(especialidades) {
                eventEspecialidad.empty().append('<option value="">Seleccionar especialidad</option>');
                especialidades.forEach(function(e){eventEspecialidad.append('<option value="'+e.id+'">'+e.nombre+'</option>');});
                eventEspecialidad.prop('disabled',false);
                var espIdToUse = ep.especialidad_id || (especialidades.length === 1 ? especialidades[0].id : null);
                if (espIdToUse) {
                    selectedEspecialidadId = String(espIdToUse);
                    eventEspecialidad.val(espIdToUse).trigger('change.select2');
                    var subIdForMedicos = ep.subespecialidad_id ? parseInt(ep.subespecialidad_id) : null;
                    if (ep.subespecialidad_id) {
                        selectedSubespecialidadId = String(ep.subespecialidad_id);
                    }
                    comp.call('fetchMedicos', parseInt(espIdToUse), subIdForMedicos).then(function(medicos) {
                        eventMedico.empty().append('<option value="">Seleccionar médico</option>');
                        medicos.forEach(function(m){eventMedico.append('<option value="'+m.id+'">'+m.nombre+'</option>');});
                        eventMedico.prop('disabled',false);
                        if(ep.medico_id){eventMedico.val(ep.medico_id).trigger('change.select2');if(dateOnly) loadSlots(parseInt(ep.medico_id),dateOnly,eventStartDate?eventStartDate.value:null);}
                    });
                }
            });
        }
    }

    // ===================== TOOLTIP =====================
    function showTooltip(info) {
        removeTooltip();
        var ep = info.event.extendedProps || {};
        var tipoEvento = ep.tipo_evento || 'cita';
        var isCita = tipoEvento === 'cita';
        var badgeColor = citaCalendarColors[ep.calendar] || '#78909C';
        var tipoLabel = isCita ? 'CITA' : 'CONSULTA';
        var estadoLabel = ep.estado_label || ep.estadoLabel || ep.calendar || ep.estado || '';
        var html = '<div class="tooltip-header"><span class="tooltip-badge me-1" style="background:'+badgeColor+'">'+estadoLabel+'</span><span class="tooltip-badge" style="background:'+(isCita?'#0d6efd':'#6f42c1')+'">'+tipoLabel+'</span></div>';
        html += '<div><strong>Paciente:</strong> '+(ep.paciente||info.event.title)+'</div>';
        if(!isCita && ep.nickname) html+='<div><strong>Le gusta que le digan:</strong> '+ep.nickname+'</div>';
        if(ep.edad) html+='<div><strong>Edad:</strong> '+ep.edad+'</div>';
        html += '<div><strong>Médico:</strong> '+(ep.medico||ep.medico_full||'')+'</div>';
        if(ep.especialidad) html+='<div><strong>Especialidad:</strong> '+ep.especialidad+'</div>';
        if(ep.motivo||ep.descripcion) html+='<div><strong>Motivo:</strong> '+(ep.motivo||ep.descripcion)+'</div>';
        if(ep.notas) html+='<div><strong>Notas:</strong> '+ep.notas+'</div>';
        if(ep.tiempo_espera_formateado&&ep.tiempo_espera_formateado!=='No en sala de espera') html+='<div><strong>Tiempo espera:</strong> '+ep.tiempo_espera_formateado+'</div>';
        if(ep.tiempo_gotas_formateado) html+='<div><strong>Tiempo en gotas:</strong> '+ep.tiempo_gotas_formateado+'</div>';
        if(ep.tipo_consulta_nombre) html+='<div><strong>Tipo:</strong> '+ep.tipo_consulta_nombre+'</div>';
        if(ep.prioridad && ep.prioridad !== 'normal') html+='<div><strong>Prioridad:</strong> '+(ep.prioridad_label||ep.prioridad)+'</div>';
        if(ep.codigo) html+='<div><strong>Código:</strong> '+ep.codigo+'</div>';
        var start=info.event.start,end=info.event.end;
        if(start){var timeStr=moment(start).format('DD/MM/YYYY HH:mm');if(end) timeStr+=' - '+moment(end).format('HH:mm');html+='<div><strong>Horario:</strong> '+timeStr+'</div>';}
        tooltipEl=document.createElement('div');tooltipEl.className='cal-tooltip';tooltipEl.innerHTML=html;document.body.appendChild(tooltipEl);
        var rect=info.el.getBoundingClientRect(),tt=tooltipEl.getBoundingClientRect();
        var top=rect.top-tt.height-8;if(top<4) top=rect.bottom+8;
        var left=rect.left+rect.width/2-tt.width/2;if(left<4) left=4;if(left+tt.width>window.innerWidth-4) left=window.innerWidth-tt.width-4;
        tooltipEl.style.top=top+window.scrollY+'px';tooltipEl.style.left=left+window.scrollX+'px';
    }
    function removeTooltip(){if(tooltipEl){tooltipEl.remove();tooltipEl=null;}}

    // ===================== FILTER LOGIC =====================
    function getActiveFilters() {
        var tc = document.getElementById('toggleCitas');
        var citasEnabled = tc ? tc.checked : true;
        var inputs = Array.from(document.querySelectorAll('.input-filter-cita'));
        var citaStates = [];
        if (citasEnabled) {
            if (inputs.length > 0) {
                citaStates = inputs.filter(function(c) {
                    return c.checked;
                }).map(function(c) {
                    return c.dataset.value;
                });
            } else {
                // Si no existen filtros de estado en la vista, mostrar todas las citas
                citaStates = Array.from(new Set(currentEvents.map(function(ev) {
                    var ep = ev.extendedProps || {};
                    return ep.calendar || ep.estado || null;
                }).filter(function(value) {
                    return value;
                })));
            }
        }
        return { citaStates: citaStates, citasEnabled: citasEnabled };
    }

    // ===================== SEARCH & FILTER =====================

    // ===================== BOTÓN NUEVA CITA =====================
    var btnNuevaCita = document.getElementById('btnNuevaCita');
    var btnLimpiarFiltros = document.getElementById('btnLimpiarFiltros');
    var filterMedico = document.getElementById('filterMedico');

    if (btnNuevaCita) {
        btnNuevaCita.addEventListener('click', function() {
            // Abrir offcanvas de nueva cita
            var offcanvasElement = document.getElementById('addEventSidebar');
            if (offcanvasElement) {
                var bsOffcanvas = new bootstrap.Offcanvas(offcanvasElement);
                bsOffcanvas.show();
            }
        });
    }

    // Botón limpiar filtros
    if (btnLimpiarFiltros) {
        btnLimpiarFiltros.addEventListener('click', function() {
            // Limpiar búsqueda de paciente
            if (searchPaciente) searchPaciente.value = '';
            searchTerm = '';

            // Limpiar filtros de especialidad
            if (filterEspecialidad) filterEspecialidad.value = '';
            selectedEspecialidadFilter = null;

            // Limpiar filtro de médico
            if (filterMedico) filterMedico.value = '';
            selectedMedicoFilter = null;

            // Deseleccionar médicos en la lista
            if (medicosContainer) {
                medicosContainer.querySelectorAll('.medico-item').forEach(function(el) {
                    el.classList.remove('active');
                });
            }

            // Restaurar todos los checkboxes de estados
            var inputs = Array.from(document.querySelectorAll('.input-filter-cita'));
            inputs.forEach(function(cb) { cb.checked = true; });

            // Limpiar prefetch cache y recargar
            prefetchCache = {};
            showLoading();
            calendar.refetchEvents();
        });
    }

    // ===================== SEARCH PACIENTE =====================
    if (searchPaciente) {
        let searchTimeout;
        searchPaciente.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                searchTerm = searchPaciente.value.toLowerCase().trim();
                prefetchCache = {};
                showLoading();
                calendar.refetchEvents();
            }, 300);
        });
    }

    if (filterEspecialidad) {
        filterEspecialidad.addEventListener('change', function() {
            selectedEspecialidadFilter = filterEspecialidad.value || null;
            prefetchCache = {};
            showLoading();
            calendar.refetchEvents();
        });
    }

    // Filtro por médico
    if (filterMedico) {
        filterMedico.addEventListener('change', function() {
            selectedMedicoFilter = filterMedico.value || null;
            // También sincronizar con la lista de médicos
            if (medicosContainer) {
                medicosContainer.querySelectorAll('.medico-item').forEach(function(el) {
                    if (selectedMedicoFilter && el.dataset.medicoId === selectedMedicoFilter) {
                        el.classList.add('active');
                    } else {
                        el.classList.remove('active');
                    }
                });
            }
            prefetchCache = {};
            showLoading();
            calendar.refetchEvents();
        });
    }

    // ===================== ACTUALIZAR CONTADORES DE ACORDEONES =====================
    function updateAccordionCounts() {
        // Contador de especialidad
        var espCount = document.getElementById('especialidadCount');
        if (espCount) {
            espCount.textContent = selectedEspecialidadFilter ? '1' : '0';
        }

        // Contador de médico
        var medCount = document.getElementById('medicoCount');
        if (medCount) {
            medCount.textContent = selectedMedicoFilter ? '1' : '0';
        }

        // Contador de citas activas
        var citasCount = document.getElementById('citasActivasCount');
        var inputs = Array.from(document.querySelectorAll('.input-filter-cita'));
        if (citasCount && inputs) {
            var checkedCitas = inputs.filter(function(cb) { return cb.checked; }).length;
            var totalCitas = inputs.length;
            citasCount.textContent = checkedCitas + '/' + totalCitas;
        }
    }

    // Actualizar contadores cuando cambien los filtros
    document.addEventListener('change', function(e) {
        if (e.target && e.target.classList.contains('input-filter-cita')) {
            updateAccordionCounts();
        }
    });
    if (filterEspecialidad) {
        filterEspecialidad.addEventListener('change', updateAccordionCounts);
    }
    if (filterMedico) {
        filterMedico.addEventListener('change', updateAccordionCounts);
    }

    // Inicializar contadores
    updateAccordionCounts();

    // ===================== TIEMPO EN ESTADO =====================
    function formatTiempoEstado(isoDate) {
        if (!isoDate) return '';
        var changed = new Date(isoDate);
        var now = new Date();
        var diffMs = now.getTime() - changed.getTime();
        if (diffMs < 0) return '';
        var totalMin = Math.floor(diffMs / 60000);
        if (totalMin < 1) return 'hace un momento';
        if (totalMin < 60) return totalMin + ' min';
        var hours = Math.floor(totalMin / 60);
        var mins = totalMin % 60;
        if (hours < 24) return hours + 'h ' + (mins > 0 ? mins + 'min' : '');
        var days = Math.floor(hours / 24);
        return days + 'd ' + (hours % 24) + 'h';
    }

    // ===================== ACTUALIZAR TIMERS EN TIEMPO REAL =====================
    function updateTimers() {
        // Refrescar eventos del calendario para obtener tiempos actualizados desde el backend
        if (calendar) {
            calendar.refetchEvents();
        }
    }

    // Actualizar timers cada 30 segundos
    setInterval(updateTimers, 30000);

    // ===================== EVENT RENDERING =====================
    function getEventColor(event) {
        var ep = event.extendedProps||{}, tipoEvento = ep.tipo_evento||'cita', estado = ep.calendar||ep.estado||'';
        // Colores sólidos con buen contraste WCAG 2.1 (contraste mínimo 4.5:1 con blanco)
        var estadoColores = {
            programada: '#E65100',    // Naranja oscuro
            confirmada: '#0D47A1',   // Azul oscuro
            cancelada: '#B71C1C',   // Rojo oscuro
            no_asistio: '#37474F'    // Gris azulado oscuro
        };

        return estadoColores[estado] || '#455A64';
    }

    // ===================== LIVEWIRE COMPONENT =====================
    function getLivewireComponent() {
        var wrapper = document.getElementById('calendario-general-component');
        if(wrapper&&window.Livewire){var wireId=wrapper.getAttribute('wire:id');if(wireId) return window.Livewire.find(wireId);}
        return null;
    }

    // ===================== FETCH EVENTS =====================
    function fetchEvents(info, successCallback) {
        showLoading();
        var comp = getLivewireComponent(), key = info.startStr+'|'+info.endStr;
        if(prefetchCache[key]){
            currentEvents=prefetchCache[key];
            successCallback(applyFilters(currentEvents));
            updateDashboardMetrics();
            hideLoading();
        }
        else if(comp){
            comp.call('fetchEventosRango',info.startStr,info.endStr).then(function(events){
                currentEvents=Array.isArray(events)?events:[];
                prefetchCache[key]=currentEvents;
                successCallback(applyFilters(currentEvents));
                updateDashboardMetrics();
                hideLoading();
            });
        }
        else{
            successCallback(applyFilters(currentEvents));
            updateDashboardMetrics();
            hideLoading();
        }
    }
    function applyFilters(events) {
        var filters = getActiveFilters();
        return events.filter(function(ev) {
            var ep=ev.extendedProps||{},tipoEvento=ep.tipo_evento||'cita',estado=ep.calendar||ep.estado||'',visible=false;
            if(tipoEvento==='cita'&&filters.citasEnabled) visible=filters.citaStates.indexOf(estado)!==-1;

            if(visible&&selectedMedicoFilter){var medicoId=ep.medico_id;if(medicoId&&String(medicoId)!==String(selectedMedicoFilter)) visible=false;}
            if(visible&&searchTerm){var paciente=(ep.paciente||'').toLowerCase();var nickname=(ep.nickname||'').toLowerCase();if(paciente.indexOf(searchTerm)===-1&&nickname.indexOf(searchTerm)===-1) visible=false;}
            if(visible&&selectedEspecialidadFilter){var especialidadId=ep.especialidad_id;if(especialidadId&&String(especialidadId)!==String(selectedEspecialidadFilter)) visible=false;}
            return visible;
        });
    }

    // ===================== SCROLL CONTAINER (declared early for viewDidMount access) =====================
    var calendarScrollContainer = document.getElementById('calendarScrollContainer');

    // ===================== DAYS SELECTOR STATE =====================
    var customDaysCount = parseInt(localStorage.getItem('cal_days_count') || '7', 10);
    if (customDaysCount < 2 || customDaysCount > 7) customDaysCount = 7;

    // ===================== FULLCALENDAR INIT FUNCTION =====================
    function initCalendar(startView = 'timeGridDay', startDate = new Date()) {
        if (calendar) {
            calendar.destroy();
            var oldWrapper = document.getElementById('external-fc-header');
            if (oldWrapper) oldWrapper.remove();
        }

        calendar = new Calendar(calendarEl, {
            initialView: startView,
            initialDate: startDate,
            timeZone: companyTimezone || 'local',
            height: 'auto', // Permite que el contenedor nativo maneje el scroll
            plugins: [dayGridPlugin, interactionPlugin, listPlugin, timegridPlugin],
        events: fetchEvents,
        locale: 'es',
        firstDay: 1,
        nowIndicator: true,
        slotEventOverlap: true,
        slotMinTime: '06:00:00',
        slotMaxTime: '24:00:00',
        scrollTime: (function() {
            var now = new Date();
            var h = Math.max(now.getHours() - 3, 6); // 3 horas antes de la actual, mínimo 06:00
            return (h < 10 ? '0' : '') + h + ':00:00';
        })(),

        stickyHeaderDates: true,
        expandRows: false, // Desactivar expandRows para que nuestras celdas compactas se respeten
        slotDuration: '00:10:00',
        slotLabelInterval: '01:00:00',
        snapDuration: '00:05:00',
        slotLabelFormat: {
            hour: 'numeric',
            minute: '2-digit',
            omitZeroMinute: true,
            meridiem: 'short'
        },
        eventMinHeight: 20,
        eventShortHeight: 20,
        slotEventOverlap: false,
        dayMaxEventRows: false, // Permitir que las filas crezcan infinitamente
        dayMaxEvents: false, // Quitar límite para que siempre se muestren todos
        editable: true,
        eventResizableFromStart: true,
        dragScroll: true, // Revertir a true para permitir ver la tarjeta al arrastrar
        views: {
            timeGridCustom: {
                type: 'timeGrid',
                duration: { days: customDaysCount },
                buttonText: 'Semana'
            }
        },
        customButtons: { sidebarToggle: { text: 'Menú' } },
        headerToolbar: { start: 'sidebarToggle, prev,next, title', end: 'timeGridDay,timeGridCustom,dayGridMonth,listMonth' },
        buttonText: { today: 'Hoy', month: 'Mes', day: 'Día', list: 'Lista' },
        direction: document.documentElement.getAttribute('dir')==='rtl'?'rtl':'ltr',

        navLinks: true,
        datesSet: function (info) {
            // Mover el header nativo fuera del "calendarScrollContainer"
            // De esa manera mantiene la 'lógica del proyecto' pero se escapa de PerfectScrollbar
            var header = document.querySelector('#calendar > .fc-header-toolbar');
            var scrollContainer = document.getElementById('calendarScrollContainer');
            if (header && scrollContainer) {
                var wrapper = document.getElementById('external-fc-header');
                if (!wrapper) {
                    wrapper = document.createElement('div');
                    wrapper.id = 'external-fc-header';
                    wrapper.className = 'fc fc-media-screen fc-theme-standard';
                    wrapper.style.marginBottom = '0.5rem';
                    wrapper.style.paddingTop = '0.5rem';
                    scrollContainer.parentNode.insertBefore(wrapper, scrollContainer);
                }
                wrapper.appendChild(header);
                resizeCalendarScroll();
            }

            // Inyectar selector de días junto al botón "Semana"
            var isCustomView = info.view.type === 'timeGridCustom';
            var existing = document.getElementById('daysSelectorWrapper');
            if (isCustomView) {
                if (!existing) {
                    var customBtn = document.querySelector('.fc-timeGridCustom-button');
                    if (customBtn) {
                        var wrap = document.createElement('span');
                        wrap.id = 'daysSelectorWrapper';
                        wrap.style.display = 'inline-flex';
                        wrap.style.alignItems = 'center';
                        wrap.style.marginLeft = '6px';
                        wrap.innerHTML =
                            '<select id="daysSelectorSelect" class="form-select form-select-sm" style="width:auto;min-width:90px;padding:4px 28px 4px 10px;font-size:0.85rem;height:32px;border-radius:6px;cursor:pointer;" title="Días a mostrar">' +
                                '<option value="2"' + (customDaysCount===2?' selected':'') + '>2 días</option>' +
                                '<option value="3"' + (customDaysCount===3?' selected':'') + '>3 días</option>' +
                                '<option value="4"' + (customDaysCount===4?' selected':'') + '>4 días</option>' +
                                '<option value="5"' + (customDaysCount===5?' selected':'') + '>5 días</option>' +
                                '<option value="6"' + (customDaysCount===6?' selected':'') + '>6 días</option>' +
                                '<option value="7"' + (customDaysCount===7?' selected':'') + '>7 días</option>' +
                            '</select>';
                        customBtn.parentNode.insertBefore(wrap, customBtn.nextSibling);

                        document.getElementById('daysSelectorSelect').addEventListener('change', function() {
                            var days = parseInt(this.value, 10);
                            customDaysCount = days;
                            localStorage.setItem('cal_days_count', days);

                            var currentDate = calendar.getDate();
                            var currentScroll = document.getElementById('calendarScrollContainer').scrollTop;

                            // Re-init calendar with new days
                            initCalendar('timeGridCustom', currentDate);

                            // Restore scroll
                            setTimeout(function() {
                                var scrollEl = document.getElementById('calendarScrollContainer');
                                if (scrollEl) scrollEl.scrollTop = currentScroll;
                            }, 100);
                        });
                    }
                }
                if (existing) existing.style.display = 'inline-flex';
            } else {
                if (existing) existing.style.display = 'none';
            }

            // Auto-scroll a la hora actual cuando se entra a una vista timeGrid
            if (info.view.type.indexOf('timeGrid') !== -1) {
                setTimeout(function() {
                    var sc = document.getElementById('calendarScrollContainer');
                    if (!sc) return;
                    var nowLine = sc.querySelector('.fc-timegrid-now-indicator-line');
                    if (nowLine) {
                        var cRect = sc.getBoundingClientRect();
                        var nRect = nowLine.getBoundingClientRect();
                        var off = nRect.top - cRect.top + sc.scrollTop;
                        sc.scrollTop = Math.max(0, off - cRect.height * 0.3);
                    }
                }, 350);
            }
        },
        eventContent: function(arg) {
            var view = arg.view, ep = arg.event.extendedProps||{}, timeText = arg.timeText||'';
            var tipoEvento = ep.tipo_evento||'cita', isCita = tipoEvento==='cita';
            var html = '';

            // Paciente name with nickname
            var pacienteNombre = ep.paciente || arg.event.title || '';
            var nickname = ep.nickname || '';
            var nombreCompleto = nickname ? '(' + nickname + ') ' + pacienteNombre : pacienteNombre;

            // Doctor
            var medicoNombre = ep.medico || ep.medico_full || '';

            // Estado badge
            var estadoLabel = ep.estado_label || ep.estadoLabel || ep.calendar || ep.estado || '';
            var estadoColor = citaCalendarColors[ep.estado] || '#78909C';
            var estadoBadge = '<span class=\"badge rounded-pill fc-event-state-badge\" data-event-id=\"' + arg.event.id + '\" style=\"background:' + estadoColor + ';color:#fff;font-size:0.6rem;padding:2px 8px;white-space:nowrap;cursor:pointer;display:inline-flex;align-items:center;gap:3px;box-shadow:0 2px 4px rgba(0,0,0,0.1);\" title=\"Clic para cambiar estado\">' + estadoLabel + ' <i class=\"ri-arrow-down-s-line\" style=\"font-size:0.7rem;\"></i></span>';

            // Type badge removed per design

            // Indicador de consulta vinculada para citas
            var consultaBadge = '';
            if (isCita && ep.tiene_consulta) {
                consultaBadge = '<span hidden class="badge" style="background:#00897B;color:#fff;font-size:0.6rem;padding:1px 5px;" title="Tiene consulta vinculada"><i class="ri ri-links-line"></i></span>';
            }

            // Badge de prioridad (solo para citas no-normales)
            var prioridadBadge = '';
            if (isCita && ep.prioridad && ep.prioridad !== 'normal') {
                var prioridadLabel = ep.prioridad_label || ep.prioridad.charAt(0).toUpperCase() + ep.prioridad.slice(1);
                var prioridadBadgeColor = prioridadColors[ep.prioridad] || '#6c757d';
                prioridadBadge = '<span class="badge" style="background:' + prioridadBadgeColor + ';color:#fff;font-size:0.6rem;padding:1px 6px;font-weight:600;" title="Prioridad ' + prioridadLabel + '">' + prioridadLabel + '</span>';
            }

            // Edad
            var edadHtml = ep.edad ? '<span style="font-size:0.7rem;opacity:0.9;"> · ' + ep.edad + '</span>' : '';

            // Tiempo en estado (para consultas activas desde sala_espera en adelante)
            var tiempoBadge = '';

            // Para gotas y dilatado, usar el tiempo calculado desde el backend
            if ((ep.estado === 'en_gotas' || ep.estado === 'dilatado') && ep.tiempo_gotas_formateado) {
                var iconoTiempo = 'ri-drop-line';
                var colorTiempo = '#00BCD4';
                tiempoBadge = '<span class="fc-event-tiempo-badge" data-event-id="' + arg.event.id + '" style="background:' + colorTiempo + ';color:#fff;font-size:0.6rem;padding:2px 6px;border-radius:4px;display:inline-flex;align-items:center;gap:2px;" title="Tiempo en ' + (ep.estado_label || ep.estado) + '"><i class="ri ' + iconoTiempo + '"></i>' + ep.tiempo_gotas_formateado + '</span>';
            } else {
                // Para otros estados activos, calcular el tiempo desde estado_changed_at
                var estadosActivos = ['sala_espera', 'en_consultorio', 'en_consultorio_optometrista', 'en_optica', 'en_estudio'];
                var esEstadoActivo = estadosActivos.indexOf(ep.estado) !== -1;

                if (esEstadoActivo && ep.estado_changed_at) {
                    var te = formatTiempoEstado(ep.estado_changed_at);
                    if (te) {
                        var iconoTiempo = 'ri-time-line';
                        var colorTiempo = '#E64A19';
                        tiempoBadge = '<span class="fc-event-tiempo-badge" data-event-id="' + arg.event.id + '" style="background:' + colorTiempo + ';color:#fff;font-size:0.6rem;padding:2px 6px;border-radius:4px;display:inline-flex;align-items:center;gap:2px;" title="Tiempo en ' + (ep.estado_label || ep.estado) + '"><i class="ri ' + iconoTiempo + '"></i>' + te + '</span>';
                    }
                }
            }

            // Calcular duracion
            var duracionMinutos = 0;
            if (arg.event.start && arg.event.end) {
                duracionMinutos = Math.round((arg.event.end - arg.event.start) / 60000);
            }
            var duracionHoras = Math.floor(duracionMinutos / 60);
            var duracionMins = duracionMinutos % 60;
            var duracionTexto = duracionHoras > 0 ? duracionHoras + 'h ' + (duracionMins > 0 ? duracionMins + 'min' : '') : duracionMins + 'min';

            // Controles de duracion para vistas de tiempo
            var durationControls = '';
            if (view.type === 'timeGridWeek' || view.type === 'timeGridCustom' || view.type === 'timeGridDay') {
                var eventId = arg.event.id || '';
                durationControls = '<div class="fc-event-duration-controls" data-event-id="' + eventId + '">' +
                    '<button hidden type="button" class="fc-event-duration-btn" data-minutes="-15" title="Disminuir 15 min">−</button>' +
                    '<span class="fc-event-duration-display">' + duracionTexto + '</span>' +
                    '<button hidden type="button" class="fc-event-duration-btn" data-minutes="15" title="Aumentar 15 min">+</button>' +
                    '</div>';
            }

            // Indicador de sincronizacion con consulta
            var syncIndicator = '';
            if (isCita && ep.tiene_consulta) {
                syncIndicator = '<span hidden class="fc-event-sync-indicator" title="Sincronizado con consulta #'+ep.consulta_id+'"><i class="ri ri-links-line"></i> Sincronizado</span>';
            }

            // Shared helpers for all grid views (day, week, month)
            var gridTimeStart = arg.event.start ? moment(arg.event.start).format('hh:mm A') : '';
            var gridTimeEnd = arg.event.end ? moment(arg.event.end).format('hh:mm A') : '';
            var gridTimeRange = gridTimeStart + (gridTimeEnd ? ' - ' + gridTimeEnd : '');

            var prioridadIcon = '';
            if (isCita && ep.prioridad && ep.prioridad === 'alta') {
                prioridadIcon = '<span style="display:inline-flex;align-items:center;justify-content:center;width:8px;height:8px;background:#dc3545;border-radius:50%;margin-right:4px;"></span>';
            }

            var consultaIcon = '';
            if (isCita && ep.tiene_consulta) {
                consultaIcon = '<i class="ri-links-line" style="font-size:0.65rem;opacity:0.7;margin-right:3px;"></i>';
            }

            if (view.type === 'dayGridMonth') {
                // Diseño súper minimalista para vista de Mes (solo una fila con flex row)
                html = '<div class="fc-event-main-frame fc-month-event" style="width:100%;display:flex;flex-direction:row;align-items:center;gap:3px;overflow:hidden;padding:1px 3px;">' +
                    estadoBadge +
                    prioridadIcon +
                    consultaIcon +
                    '<span class="fc-event-title" style="flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">' + nombreCompleto + '</span>' +
                    (tiempoBadge ? tiempoBadge : '') +
                '</div>';
            } else if (view.type === 'timeGridDay' || view.type === 'timeGridWeek' || view.type === 'timeGridCustom') {
                if (duracionMinutos <= 30) {
                    // Diseño ultracompacto para citas de 30 min o menos (1 sola línea)
                    html = '<div class="fc-event-main-frame" style="width:100%;height:100%;display:flex;flex-direction:row;align-items:center;box-sizing:border-box;overflow:hidden;gap:3px;">' +
                        estadoBadge +
                        prioridadIcon +
                        consultaIcon +
                        '<span class="fc-event-title" style="flex:1;">' + nombreCompleto + '</span>' +
                        (tiempoBadge ? tiempoBadge : '') +
                        '<span class="fc-event-subtitle" style="flex-shrink:0;">' + medicoNombre + '</span>' +
                    '</div>';
                } else {
                    // Diseño para citas largas (> 30 min) (2 líneas)
                    html = '<div class="fc-event-main-frame" style="width:100%;height:100%;display:flex;flex-direction:row;align-items:flex-start;box-sizing:border-box;overflow:hidden;gap:4px;">' +
                        '<div style="display:flex;flex-direction:column;flex:1;min-width:0;overflow:hidden;gap:1px;">' +
                            '<div style="display:flex;align-items:center;gap:3px;">' +
                                prioridadIcon +
                                consultaIcon +
                                '<span class="fc-event-title">' + nombreCompleto + '</span>' +
                                (tiempoBadge ? tiempoBadge : '') +
                            '</div>' +
                            '<span class="fc-event-subtitle">Dr(a). ' + medicoNombre + (ep.edad ? ' · ' + ep.edad : '') + '</span>' +
                        '</div>' +
                        '<div style="display:flex;flex-direction:column;align-items:flex-end;flex-shrink:0;gap:2px;">' +
                            estadoBadge +
                            '<span class="fc-event-subtitle" style="font-size:0.6rem;">' + gridTimeRange + '</span>' +
                        '</div>' +
                    '</div>';
                }
            } else if (view.type === 'listMonth' || view.type === 'listWeek') {
                // Horario desde - hasta
                var listTimeStart = arg.event.start ? moment(arg.event.start).format('HH:mm') : '';
                var listTimeEnd = arg.event.end ? moment(arg.event.end).format('HH:mm') : '';
                var listTimeRange = listTimeStart + (listTimeEnd ? ' - ' + listTimeEnd : '');

                html = '<div style="display:flex;align-items:center;gap:8px;width:100%;padding:4px 8px 4px 20px;box-sizing:border-box;">' +
                    '<div style="flex:1;min-width:0;overflow:hidden;">' +
                        '<div style="font-weight:600;font-size:0.85rem;margin-bottom:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">' + nombreCompleto + '</div>' +
                        '<div style="font-size:0.75rem;color:#6c757d;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">Dr(a). ' + medicoNombre + (ep.edad ? ' · ' + ep.edad : '') + '</div>' +
                        '<div style="font-size:0.7rem;color:#888;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><i class="ri-time-line" style="font-size:0.65rem;"></i> ' + listTimeRange + '</div>' +
                    '</div>' +
                    '<div style="display:flex;align-items:center;gap:3px;flex-shrink:0;flex-wrap:nowrap;justify-content:flex-end;">' + prioridadBadge + consultaBadge + estadoBadge + tiempoBadge + '</div>' +
                '</div>';
            } else {
                return { html: '<div>' + arg.event.title + '</div>' };
            }

            return { html: html };
        },
        eventDidMount: function(info) {
            var ep = info.event.extendedProps||{}, tipoEvento = ep.tipo_evento||'cita', isCita = tipoEvento==='cita';
            info.el.classList.add('event-cita');

            // Agregar data-event-id para poder actualizar timers
            info.el.setAttribute('data-event-id', info.event.id);

            var tipoColor = (isCita && ep.tipo_consulta_color) ? ep.tipo_consulta_color : (isCita ? '#1565C0' : '#7B1FA2');

            // Estilo Google Calendar: fondo sólido, borde izquierdo según prioridad
            info.el.style.backgroundColor = tipoColor;
            info.el.style.borderColor = tipoColor;
            if (isCita) {
                var prioridadHex = prioridadColors[ep.prioridad] || prioridadColors.normal;
                info.el.style.borderLeftColor = prioridadHex + ' !important';
                info.el.style.setProperty('border-left-color', prioridadHex, 'important');
            } else {
                info.el.style.borderLeftColor = tipoColor + ' !important';
                info.el.style.setProperty('border-left-color', tipoColor, 'important');
            }
            info.el.style.borderLeftWidth = '4px';
            info.el.style.color = '#fff';

            info.el.addEventListener('mouseenter',function(){showTooltip(info);});
            info.el.addEventListener('mouseleave',removeTooltip);
        },
        dateClick: function(info) {
            var clickedDate = info.date || new Date(info.dateStr);
            if(isPastDateTime(clickedDate, info)){return;}
            var dateOnly = info.dateStr.substring(0,10);

            // Limpiar estado de edición anterior
            currentCitaId = null;
            eventToUpdate = null;
            resetValues();

            // Si se hizo click en un slot de tiempo (timeGrid), auto-completar la hora
            if (info.view.type.includes('timeGrid')) {
                // Usamos la cadena de fecha de FullCalendar para obtener la hora exacta
                // en la zona horaria del calendario (evitando desfases locales)
                var startHour = info.dateStr.includes('T') ? info.dateStr.substring(11, 16) : '08:00';

                // Calcular hora de fin sumando la duración base (usando el objeto Date pero formateando con cuidado)
                var endDate = new Date(clickedDate.getTime() + (currentSlotDuration * 60000));
                var endHour = String(endDate.getHours()).padStart(2, '0') + ':' + String(endDate.getMinutes()).padStart(2, '0');
                
                // Si estamos en una vista con zona horaria, es mejor recalcular el fin basándose en el inicio de la cadena
                if (info.dateStr.includes('T')) {
                    var parts = startHour.split(':');
                    var mins = parseInt(parts[0]) * 60 + parseInt(parts[1]) + currentSlotDuration;
                    endHour = String(Math.floor(mins / 60)).padStart(2, '0') + ':' + String(mins % 60).padStart(2, '0');
                }

                if (eventHoraInicio) {
                    eventHoraInicio.value = startHour;
                    eventHoraInicio.dispatchEvent(new Event('change', { bubbles: true }));
                }
                if (eventHoraFin) {
                    eventHoraFin.value = endHour;
                    eventHoraFin.dispatchEvent(new Event('change', { bubbles: true }));
                }
            }

            if(bsAddEventSidebar) bsAddEventSidebar.show();
            if(offcanvasTitle) offcanvasTitle.innerHTML='Nueva Cita';
            if(btnSubmit){btnSubmit.innerHTML='<i class="ri ri-add-line me-1"></i> Agregar';btnSubmit.classList.remove('btn-update-event');btnSubmit.classList.add('btn-add-event');}

            if(btnConfirmEvent) btnConfirmEvent.classList.add('d-none');
            if(btnCancelCita) btnCancelCita.classList.add('d-none');
            if(btnReagendar) btnReagendar.classList.add('d-none');
            if(btnReagendarAuto) btnReagendarAuto.classList.add('d-none');
            // Modo creación: mostrar estado y botón nuevo paciente
            var estadoContainer = document.getElementById('estadoContainer');
            if(estadoContainer) estadoContainer.style.display = '';
            var nuevoPacienteContainer = document.getElementById('nuevoPacienteContainer');
            if(nuevoPacienteContainer) nuevoPacienteContainer.style.display = '';
            var editInfoBanner = document.getElementById('editInfoBanner');
            if(editInfoBanner) editInfoBanner.style.display = 'none';
            if(eventPaciente && eventPaciente.length) eventPaciente.prop('disabled', false);
            if(fechaFlatpickr) fechaFlatpickr.setDate(dateOnly, false);
        },
        eventClick: function(info) {
            var target = info.jsEvent.target;
            var stateBadge = target.closest('.fc-event-state-badge');

            if (stateBadge) {
                // Prevenir que se abra el offcanvas si el click fue en el badge de estado
                info.jsEvent.preventDefault();
                info.jsEvent.stopPropagation();

                // Llamar a la función del modal con el ID del evento
                var eventId = stateBadge.dataset.eventId || info.event.id;
                window.openEstadoModal(eventId);

                return false;
            }

            removeTooltip();
            var ep = info.event.extendedProps||{};
            if(ep.tipo_evento==='cita') { citaEventClick(info); }
            else { showEventDetail(info.event); }
        },
        eventDrop: function(info) {
            var ep = info.event.extendedProps||{};
            if(ep.tipo_evento!=='cita'){info.revert();return;}
            if(isPastDateTime(info.event.start, info)){info.revert();return;}
            var comp = getLivewireComponent(); if(!comp){info.revert();return;}
            var newStart = info.event.start;
            var newEnd = info.event.end;
            if(!newEnd && info.oldEvent.end && info.oldEvent.start){
                var durMs = info.oldEvent.end.getTime() - info.oldEvent.start.getTime();
                newEnd = new Date(newStart.getTime() + durMs);
            }
            if(!newEnd) newEnd = newStart;
            
            // Preferimos usar la cadena con zona horaria de FullCalendar si está disponible
            var startStr = info.event.startStr ? info.event.startStr.replace('T', ' ').substring(0, 19) : formatDateForLivewire(newStart);
            var endStr = info.event.endStr ? info.event.endStr.replace('T', ' ').substring(0, 19) : formatDateForLivewire(newEnd);
            
            comp.call('updateCitaFechas', getCitaId(info.event.id), startStr, endStr);
        },
        eventResize: function(info) {
            var ep = info.event.extendedProps||{};
            if(ep.tipo_evento!=='cita'){info.revert();return;}
            if(isPastDateTime(info.event.start, info)){info.revert();return;}
            var comp = getLivewireComponent(); if(!comp){info.revert();return;}
            comp.call('updateCitaFechas', getCitaId(info.event.id), formatDateForLivewire(info.event.start), info.event.end?formatDateForLivewire(info.event.end):formatDateForLivewire(info.event.start));
        },
        viewDidMount: function() { modifyToggler(); }
    });

    calendar.render();
    modifyToggler();
    createToggleButton();

    // Auto-scroll a la hora actual en vistas de timeGrid
    setTimeout(function() {
        var view = calendar.view;
        if (view && view.type.indexOf('timeGrid') !== -1 && calendarScrollContainer) {
            var nowIndicator = calendarScrollContainer.querySelector('.fc-timegrid-now-indicator-line');
            if (nowIndicator) {
                var containerRect = calendarScrollContainer.getBoundingClientRect();
                var indicatorRect = nowIndicator.getBoundingClientRect();
                var offset = indicatorRect.top - containerRect.top + calendarScrollContainer.scrollTop;
                // Posicionar el indicador ~30% desde el tope para mostrar contexto previo
                var targetScroll = Math.max(0, offset - containerRect.height * 0.3);
                calendarScrollContainer.scrollTop = targetScroll;
            }
        }
    }, 300);
    }

    // Initialize the calendar for the first time
    initCalendar();

    function resizeCalendarScroll() {
        if (!calendarScrollContainer) return;
        // Flexbox handles sizing; this is a fallback for edge cases
        var computedH = calendarScrollContainer.offsetHeight;
        if (computedH < 200) {
            var rect = calendarScrollContainer.getBoundingClientRect();
            var avail = window.innerHeight - rect.top - 8;
            if (avail > 300) calendarScrollContainer.style.height = avail + 'px';
        }
    }
    window.addEventListener('resize', resizeCalendarScroll);
    resizeCalendarScroll();


    // ===================== STATE CHANGE MODAL =====================
    var preConfirmStates = {
        'programada': 'Programada',
        'confirmada': 'Confirmada',
        'cancelada': 'Cancelada',
        'no_asistio': 'No Asistió'
    };
    var postConfirmStates = {
        'sala_espera': 'Sala de Espera',
        'en_consultorio': 'Consultorio',
        'en_consultorio_optometrista': 'Consultorio Optometrista',
        'en_gotas': 'Gotas',
        'dilatado': 'Dilatado',
        'en_optica': 'Óptica',
        'en_estudio': 'Estudio',
        'finalizada': 'Finalizada',
        '---': '──────────',
        'cancelada': 'Cancelada',
        'no_asistio': 'No Asistió'
    };
    var confirmedStates = ['confirmada','sala_espera','en_consultorio','en_consultorio_optometrista','en_gotas','dilatado','en_optica','en_estudio','finalizada','pagada'];

    // Inicializar el modal de Bootstrap
    var modalCambiarEstado = null;
    document.addEventListener('DOMContentLoaded', function() {
        var modalEl = document.getElementById('modalCambiarEstado');
        if (modalEl) {
            modalCambiarEstado = new bootstrap.Modal(modalEl);
        }
    });

    // Función para abrir el modal de estado
    window.openEstadoModal = function(eventId) {
        if (!eventId) return;

        var event = calendar.getEventById(eventId);
        if (!event) return;

        var ep = event.extendedProps || {};
        var currentState = ep.estado || 'programada';
        var isConfirmed = confirmedStates.indexOf(currentState) !== -1;
        var availableStates = isConfirmed ? postConfirmStates : preConfirmStates;

        // Configurar el modal
        var selectEl = document.getElementById('estadoSelect');
        var notaEl = document.getElementById('estadoNota');
        var idEl = document.getElementById('estadoCitaId');
        var actualEl = document.getElementById('estadoCitaActual');

        if (selectEl && idEl && actualEl) {
            // Limpiar select
            selectEl.innerHTML = '<option value="">Seleccione un estado...</option>';

            // Llenar opciones
            Object.keys(availableStates).forEach(function(key) {
                var option = document.createElement('option');
                option.value = key;
                option.textContent = availableStates[key];
                if (key === '---') option.disabled = true;
                if (key === currentState) option.selected = true;
                selectEl.appendChild(option);
            });

            // Reinicializar Select2 si está disponible
            if (window.jQuery && $(selectEl).hasClass('select2-hidden-accessible')) {
                $(selectEl).select2('destroy');
            }
            if (window.jQuery) {
                $(selectEl).select2({
                    dropdownParent: $('#modalCambiarEstado')
                });
            }

            // Setear datos ocultos
            idEl.value = eventId;
            actualEl.value = currentState;

            // Limpiar nota
            if (notaEl) notaEl.value = '';

            // Mostrar modal
            if (modalCambiarEstado) {
                modalCambiarEstado.show();
            } else {
                var m = new bootstrap.Modal(document.getElementById('modalCambiarEstado'));
                m.show();
            }
        }
    };

    // Lógica para guardar el estado desde el modal
    document.addEventListener('DOMContentLoaded', function() {
        var btnGuardarEstado = document.getElementById('btnGuardarEstado');
        if (btnGuardarEstado) {
            btnGuardarEstado.addEventListener('click', function() {
                var selectEl = document.getElementById('estadoSelect');
                var notaEl = document.getElementById('estadoNota');
                var idEl = document.getElementById('estadoCitaId');
                var actualEl = document.getElementById('estadoCitaActual');

                var newState = window.jQuery ? $(selectEl).val() : selectEl.value;
                var currentState = actualEl.value;
                var eventId = idEl.value;
                var nota = notaEl ? notaEl.value.trim() : '';

                if (!newState || newState === currentState || newState === '---') {
                    if (modalCambiarEstado) modalCambiarEstado.hide();
                    return;
                }

                var comp = getLivewireComponent();
                if (!comp) {
                    showToast('No se pudo conectar con el servidor', 'error');
                    return;
                }

                var numericId = eventId;
                if (eventId.toString().startsWith('cita_')) {
                    numericId = eventId.toString().replace('cita_', '');
                }

                // Mostrar loading state
                var indicatorLabel = btnGuardarEstado.querySelector('.indicator-label');
                var indicatorProgress = btnGuardarEstado.querySelector('.indicator-progress');

                btnGuardarEstado.disabled = true;
                if (indicatorLabel) indicatorLabel.classList.add('d-none');
                if (indicatorProgress) indicatorProgress.classList.remove('d-none');

                // Si hay nota, guardarla primero
                var notaPromise = Promise.resolve();
                if (nota) {
                    notaPromise = comp.call('guardarNotaCita', numericId, nota);
                }

                notaPromise.then(() => {
                    return comp.call('cambiarEstado', numericId, newState);
                }).then(() => {
                    if (modalCambiarEstado) modalCambiarEstado.hide();
                    if (calendar) calendar.refetchEvents();
                }).catch(error => {
                    showToast('Error al cambiar el estado', 'error');
                    console.error(error);
                }).finally(() => {
                    // Restaurar botón
                    btnGuardarEstado.disabled = false;
                    if (indicatorLabel) indicatorLabel.classList.remove('d-none');
                    if (indicatorProgress) indicatorProgress.classList.add('d-none');
                });
            });
        }
    });

    // ===================== ADJUST EVENT DURATION (Event Delegation) =====================
    document.addEventListener('click', function(e) {
        var btn = e.target.closest('.fc-event-duration-btn');
        if (!btn) return;

        e.stopPropagation();
        e.preventDefault();

        var controls = btn.closest('.fc-event-duration-controls');
        if (!controls) return;

        var eventId = controls.dataset.eventId;
        var minutes = parseInt(btn.dataset.minutes, 10);

        if (isNaN(minutes) || !eventId) return;

        adjustEventDurationById(eventId, minutes);
    });

    function adjustEventDurationById(eventId, minutes) {
        var comp = getLivewireComponent();
        if (!comp) {
            showToast('No se pudo conectar con el servidor', 'error');
            return;
        }

        var event = calendar.getEventById(eventId);
        if (!event) {
            event = calendar.getEventById(parseInt(eventId));
        }

        if (!event) {
            showToast('Evento no encontrado', 'error');
            return;
        }

        var currentStart = event.start;
        var currentEnd = event.end;

        if (!currentStart || !currentEnd) {
            showToast('No se pudo obtener la información del evento', 'error');
            return;
        }

        var currentDurationMs = currentEnd.getTime() - currentStart.getTime();
        var currentDuration = Math.round(currentDurationMs / 60000);
        var newDuration = currentDuration + minutes;

        if (newDuration < 15) {
            showToast('La duración mínima es 15 minutos', 'warning');
            return;
        }
        if (newDuration > 480) {
            showToast('La duración máxima es 8 horas', 'warning');
            return;
        }

        var newEnd = new Date(currentStart.getTime() + newDuration * 60000);
        var ep = event.extendedProps || {};
        var tipoEvento = ep.tipo_evento || 'cita';

        var startStr = formatDateForLivewire(currentStart);
        var endStr = formatDateForLivewire(newEnd);

        showLoading();

        comp.call('updateCitaFechas', parseInt(eventId), startStr, endStr);
    }

    function formatDuration(minutes) {
        var h = Math.floor(minutes / 60);
        var m = minutes % 60;
        if (h > 0 && m > 0) return h + 'h ' + m + 'min';
        if (h > 0) return h + 'h';
        return m + 'min';
    }

    // ===================== DAY EVENTS MODAL =====================
    function showDayEventsModal(date, allSegs) {
        var modalEl = document.getElementById('dayEventsModal');
        var modalTitle = document.getElementById('dayEventsModalTitle');
        var modalBody = document.getElementById('dayEventsModalBody');

        if (!modalEl || !modalTitle || !modalBody) {
            return;
        }

        // Si no hay allSegs, intentar obtener eventos del día desde calendar


        // Formatear fecha para el título
        var fechaFormateada = moment(date).format('dddd, D [de] MMMM [de] YYYY');
        fechaFormateada = fechaFormateada.charAt(0).toUpperCase() + fechaFormateada.slice(1);
        modalTitle.textContent = 'Eventos del ' + fechaFormateada;

        // Ordenar eventos por hora de inicio
        var eventosOrdenados = allSegs.sort(function(a, b) {
            var timeA = a.event && a.event.start ? a.event.start.getTime() : 0;
            var timeB = b.event && b.event.start ? b.event.start.getTime() : 0;
            return timeA - timeB;
        });

        // Generar HTML de eventos
        var html = '<div class="day-events-list">';
        var citasCount = 0;


        eventosOrdenados.forEach(function(seg) {
            var event = seg.event;
            var ep = event.extendedProps || {};
            var tipoEvento = ep.tipo_evento || 'cita';
            var isCita = tipoEvento === 'cita';

            if (isCita) citasCount++;


            var estadoColor = citaCalendarColors[ep.estado] || '#78909C';
            var estadoLabel = ep.estado_label || ep.estadoLabel || ep.estado || '';

            var pacienteNombre = ep.paciente || event.title || '';
            var nickname = ep.nickname || '';
            var nombreCompleto = nickname ? '(' + nickname + ') ' + pacienteNombre : pacienteNombre;

            var horaInicio = event.start ? moment(event.start).format('HH:mm') : '--:--';
            var horaFin = event.end ? moment(event.end).format('HH:mm') : '';
            var horarioStr = horaFin ? horaInicio + ' - ' + horaFin : 'Desde ' + horaInicio;

            var bgStyle = '';
            if (isCita && ep.tipo_consulta_color) {
                bgStyle = 'background: linear-gradient(135deg, ' + hexToRgba(ep.tipo_consulta_color, 0.15) + ' 0%, ' + hexToRgba(estadoColor, 0.1) + ' 100%);';
            } else {
                bgStyle = 'background: linear-gradient(135deg, ' + hexToRgba('#0d6efd', 0.15) + ' 0%, ' + hexToRgba(estadoColor, 0.1) + ' 100%);';
            }

            var borderLeftColor = isCita ? (prioridadColors[ep.prioridad] || prioridadColors.normal) : estadoColor;
            html += '<div class="day-event-item event-cita" data-event-id="' + event.id + '" style="' + bgStyle + 'border-left: 4px solid ' + borderLeftColor + ';">';
            html += '<div class="d-flex justify-content-between align-items-start mb-2">';
            html += '<div class="d-flex align-items-center gap-2">';
            html += '<span class="badge" style="background:' + estadoColor + ';color:#fff;font-size:0.65rem;">' + estadoLabel + '</span>';
            if (!isCita && ep.tiempo_espera_formateado && ep.tiempo_espera_formateado !== 'No en sala de espera') {
                html += '<span class="badge bg-secondary" style="font-size:0.6rem;"><i class="ri-time-line me-1"></i>' + ep.tiempo_espera_formateado + '</span>';
            }
            html += '</div>';
            html += '<span class="day-event-time">' + horarioStr + '</span>';
            html += '</div>';
            html += '<h6 class="day-event-patient mb-1">' + nombreCompleto + '</h6>';
            html += '<div class="day-event-meta text-muted small">';
            html += '<div><i class="ri-stethoscope-line me-1"></i>Dr(a). ' + (ep.medico || 'Sin médico asignado') + '</div>';
            if (ep.especialidad) html += '<div><i class="ri-medicine-bottle-line me-1"></i>' + ep.especialidad + '</div>';
            if (ep.motivo || ep.descripcion) html += '<div class="text-truncate"><i class="ri-file-text-line me-1"></i>' + (ep.motivo || ep.descripcion) + '</div>';
            html += '</div>';
            html += '<div class="day-event-actions mt-2 pt-2 border-top">';
            html += '<button type="button" class="btn btn-sm btn-primary ver-evento-btn" data-event-id="' + event.id + '"><i class="ri-eye-line me-1"></i>Ver Detalle</button>';
            if (isCita) {
                html += '<button type="button" class="btn btn-sm btn-outline-primary editar-evento-btn ms-1" data-event-id="' + event.id + '"><i class="ri-edit-line me-1"></i>Editar</button>';
            }
            html += '</div>';
            html += '</div>';
        });

        html += '</div>';

        // Agregar estadísticas
        var statsHtml = '<div class="day-events-stats mb-3 p-2 rounded bg-light">';
        statsHtml += '<div class="row text-center">';
        statsHtml += '<div class="col-6"><div class="fw-medium">' + citasCount + '</div><div class="small text-muted">Citas</div></div>';

        statsHtml += '</div></div>';

        modalBody.innerHTML = statsHtml + html;

        // Agregar eventos a los botones
        modalBody.querySelectorAll('.ver-evento-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var eventId = this.getAttribute('data-event-id');
                var event = findEventById(eventId, eventosOrdenados);
                if (event) {
                    var bsModal = bootstrap.Modal.getInstance(modalEl);
                    if (bsModal) bsModal.hide();
                    setTimeout(function() { showEventDetail(event); }, 300);
                }
            });
        });

        modalBody.querySelectorAll('.editar-evento-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var eventId = this.getAttribute('data-event-id');
                var event = findEventById(eventId, eventosOrdenados);
                if (event) {
                    var bsModal = bootstrap.Modal.getInstance(modalEl);
                    if (bsModal) bsModal.hide();
                    setTimeout(function() {
                        var info = { event: event, jsEvent: { preventDefault: function() {} } };
                        citaEventClick(info);
                    }, 300);
                }
            });
        });

        // Mostrar modal
        var bsModal = new bootstrap.Modal(modalEl);
        bsModal.show();
    }

    function findEventById(eventId, segs) {
        for (var i = 0; i < segs.length; i++) {
            if (segs[i].event && segs[i].event.id == eventId) {
                return segs[i].event;
            }
        }
        return null;
    }

    // ===================== EVENT DETAIL OFFCANVAS (consultas) =====================



    function showEventDetail(event) {
        var ep=event.extendedProps||{};
        var title=document.getElementById('calendarioDetailTitle'),body=document.getElementById('calendarioDetailBody');
        title.textContent='Detalle de Cita';
        var badgeColor = citaCalendarColors[ep.estado] || '#78909C';
        var estadoLabel=ep.estado_label||ep.estadoLabel||ep.estado||'';

        var prioridadDetailBadge = '';
        if (ep.prioridad && ep.prioridad !== 'normal') {
            var pColor = prioridadColors[ep.prioridad] || '#6c757d';
            var pLabel = ep.prioridad_label || ep.prioridad;
            prioridadDetailBadge = '<span class="badge ms-1" style="background:'+pColor+'">'+pLabel+'</span>';
        }
        var html='<div class="mb-3"><span class="badge me-1" style="background:#0d6efd">CITA</span><span class="badge" style="background:'+badgeColor+'">'+estadoLabel+'</span>'+prioridadDetailBadge+'</div>';
        html+='<div class="mb-3"><h6 class="mb-1">Paciente</h6><p class="mb-0">'+(ep.paciente||event.title)+'</p></div>';
        if(ep.edad) html+='<div class="mb-3"><h6 class="mb-1">Edad</h6><p class="mb-0">'+ep.edad+'</p></div>';
        html+='<div class="mb-3"><h6 class="mb-1">Médico</h6><p class="mb-0">'+(ep.medico||ep.medico_full||'Sin médico')+'</p></div>';
        if(ep.especialidad) html+='<div class="mb-3"><h6 class="mb-1">Especialidad</h6><p class="mb-0">'+ep.especialidad+'</p></div>';
        if(ep.motivo||ep.descripcion) html+='<div class="mb-3"><h6 class="mb-1">Motivo</h6><p class="mb-0">'+(ep.motivo||ep.descripcion)+'</p></div>';

        var start=event.start,end=event.end;
        if(start){var timeStr=moment(start).format('DD/MM/YYYY HH:mm');if(end) timeStr+=' - '+moment(end).format('HH:mm');html+='<div class="mb-3"><h6 class="mb-1">Horario</h6><p class="mb-0">'+timeStr+'</p></div>';}

        body.innerHTML=html;
        bsDetailSidebar.show();
    }


    // ===================== INLINE MINI CALENDAR =====================
    var inlineFechaFiltroActivo = document.getElementById('fechaFiltroActivo');
    var inlineFechaFiltroTexto = document.getElementById('fechaFiltroTexto');

    if (inlineCalendar) {
        // Asegurar que Flatpickr use español
        if (typeof flatpickr !== 'undefined' && flatpickr.l10ns && flatpickr.l10ns.es) {
            flatpickr.localize(flatpickr.l10ns.es);
        }

        inlineCalInstance = flatpickr(inlineCalendar, {
            inline: true,
            locale: 'es',
            defaultDate: 'today',
            onChange: function(selectedDates, dateStr) {
                if (selectedDates.length) {
                    calendar.gotoDate(selectedDates[0]);
                    // Mostrar badge de fecha seleccionada
                    if (inlineFechaFiltroActivo) {
                        inlineFechaFiltroActivo.style.display = 'block';
                        var fechaFormateada = moment(selectedDates[0]).format('dddd, D [de] MMMM YYYY');
                        inlineFechaFiltroTexto.textContent = fechaFormateada;
                    }
                }
            }
        });
    }

    // ===================== SIDEBAR TOGGLER =====================
    function modifyToggler() {
        var fcSidebarToggleButton=document.querySelector('.fc-sidebarToggle-button'),fcPrevButton=document.querySelector('.fc-prev-button'),fcNextButton=document.querySelector('.fc-next-button'),fcHeaderToolbar=document.querySelector('.fc-header-toolbar');
        if(fcPrevButton) fcPrevButton.classList.add('btn','btn-sm','btn-icon','btn-outline-secondary','me-2');
        if(fcNextButton) fcNextButton.classList.add('btn','btn-sm','btn-icon','btn-outline-secondary','me-4');
        if(fcHeaderToolbar) fcHeaderToolbar.classList.add('row-gap-4','gap-2');
        if(fcSidebarToggleButton){fcSidebarToggleButton.classList.remove('fc-button-primary');fcSidebarToggleButton.classList.add('d-lg-none','d-inline-block','ps-0');while(fcSidebarToggleButton.firstChild) fcSidebarToggleButton.firstChild.remove();fcSidebarToggleButton.setAttribute('data-bs-toggle','sidebar');fcSidebarToggleButton.setAttribute('data-overlay','');fcSidebarToggleButton.setAttribute('data-target','#app-calendar-sidebar');fcSidebarToggleButton.insertAdjacentHTML('beforeend','<i class="icon-base ri ri-menu-line icon-24px text-body"></i>');}
    }

    // ===================== TOGGLE HANDLERS =====================
    if(toggleCitas){toggleCitas.addEventListener('change',function(){filtrosCitas.style.display=this.checked?'':'none';prefetchCache={};calendar.refetchEvents();});}
    document.addEventListener('change', function(e) {
        if (e.target && e.target.classList.contains('input-filter-cita')) {
            prefetchCache = {};
            calendar.refetchEvents();
        }
    });

    // ===================== MEDICO FILTER =====================
    if(medicosContainer){
        medicosContainer.addEventListener('click',function(e){
            var item=e.target.closest('.medico-item');if(!item) return;var medicoId=item.dataset.medicoId;
            if(selectedMedicoFilter===medicoId){selectedMedicoFilter=null;item.classList.remove('active');}
            else{medicosContainer.querySelectorAll('.medico-item').forEach(function(el){el.classList.remove('active');});selectedMedicoFilter=medicoId;item.classList.add('active');}
            prefetchCache={};showLoading();calendar.refetchEvents();
        });
    }

    // ===================== FORM VALIDATION =====================
    var eventForm = document.getElementById('eventForm');
    if (eventForm && typeof FormValidation !== 'undefined') {
        var fv = FormValidation.formValidation(eventForm, {
            fields: {
                eventPaciente: {
                    validators: { notEmpty: { message: 'Seleccione un paciente' } }
                },
                eventMedico: {
                    validators: { notEmpty: { message: 'Seleccione un médico' } }
                },
                eventFecha: {
                    validators: { notEmpty: { message: 'Seleccione una fecha' } }
                },
                eventHoraInicio: {
                    validators: { notEmpty: { message: 'Seleccione la hora de inicio' } }
                },
                eventHoraFin: {
                    validators: { notEmpty: { message: 'Seleccione la hora de fin' } }
                },
                eventMotivo: {
                    validators: { notEmpty: { message: 'Ingrese el motivo de la cita' } }
                }
            },
            plugins: {
                trigger: new FormValidation.plugins.Trigger(),
                bootstrap5: new FormValidation.plugins.Bootstrap5({
                    eleValidClass:'',
                    rowSelector:'.form-control-validation'
                }),
                submitButton: new FormValidation.plugins.SubmitButton(),
                autoFocus: new FormValidation.plugins.AutoFocus()
            }
        }).on('core.form.valid',function(){isFormValid=true;}).on('core.form.invalid',function(){isFormValid=false;});

        // Solo agregar listeners si los elementos existen
        if(eventPaciente && eventPaciente.length) {
            eventPaciente.on('change',function(){fv.revalidateField('eventPaciente');});
        }
        if(eventMedico && eventMedico.length) {
            eventMedico.on('change',function(){fv.revalidateField('eventMedico');});
        }
        if(eventFecha) {
            eventFecha.addEventListener('change',function(){fv.revalidateField('eventFecha');});
        }
        if(eventHoraInicio) {
            eventHoraInicio.addEventListener('change',function(){fv.revalidateField('eventHoraInicio');});
        }
        if(eventHoraFin) {
            eventHoraFin.addEventListener('change',function(){fv.revalidateField('eventHoraFin');});
        }
    }

    // ===================== FORM SUBMIT =====================
    if (btnSubmit) {
        btnSubmit.addEventListener('click', function() {
            if (!isFormValid) return;
            if (btnSubmit.disabled) return;

            btnSubmit.disabled = true;
            btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Guardando...';

            var isNewCita = btnSubmit.classList.contains('btn-add-event');

            var prioridad = 'normal';
            var eventPrioridadEl = document.getElementById('eventPrioridad');
            if (eventPrioridadEl) prioridad = eventPrioridadEl.value;

            var fecha = eventFecha ? eventFecha.value : '';
            var horaInicio = eventHoraInicio ? eventHoraInicio.value : '';
            var horaFin = eventHoraFin ? eventHoraFin.value : '';

            var startDate = '';
            var endDate = '';

            // Usar siempre los mismos campos de hora
            if (fecha && horaInicio && horaFin) {
                startDate = fecha + ' ' + horaInicio + ':00';
                endDate = fecha + ' ' + horaFin + ':00';
            }

            var eventData = {
                paciente_id: eventPaciente.val(),
                especialidad_id: selectedEspecialidadId,
                subespecialidad_id: selectedSubespecialidadId,
                medico_id: eventMedico.val(),
                start: startDate,
                end: endDate,
                fecha_inicio: startDate,
                fecha_fin: endDate,
                motivo: eventMotivo ? eventMotivo.value : '',
                notas: eventNotas ? eventNotas.value : '',
                estado: isNewCita ? 'programada' : (eventEstado.val() || 'programada'),
                tipo_consulta_id: eventTipoConsulta.val() || '',
                prioridad: prioridad
            };
            var comp = getLivewireComponent();
            if(!comp) {
                // Restablecer el botón si no se encuentra el componente Livewire
                btnSubmit.disabled = false;
                var isUpdate = btnSubmit.classList.contains('btn-update-event');
                btnSubmit.innerHTML = isUpdate ? '<i class="ri ri-save-line me-1"></i> Actualizar' : '<i class="ri ri-add-line me-1"></i> Agregar';
                return;
            }
            if (btnSubmit.classList.contains('btn-update-event') && currentCitaId) {
                comp.set('citaId', currentCitaId);
            }
            comp.call('saveCita', eventData).catch(function(error) {
                // Manejar errores de validación de Livewire u otros errores
                btnSubmit.disabled = false;
                var isUpdate = btnSubmit.classList.contains('btn-update-event');
                btnSubmit.innerHTML = isUpdate ? '<i class="ri ri-save-line me-1"></i> Actualizar' : '<i class="ri ri-add-line me-1"></i> Agregar';

                if (error && error.body && error.body.message) {
                    // Error de validación de Livewire
                    if (window.Swal) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error de validación',
                            html: error.body.message,
                            confirmButtonText: 'Entendido',
                            confirmButtonColor: '#dc3545'
                        });
                    } else {
                        alert('Error de validación: ' + error.body.message);
                    }
                } else {
                    if (window.Swal) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Ocurrió un error al guardar la cita. Por favor, intente de nuevo.',
                            confirmButtonText: 'Entendido',
                            confirmButtonColor: '#dc3545'
                        });
                    } else {
                        alert('Ocurrió un error al guardar la cita.');
                    }
                }
            });
        });
    }

    // ===================== DELETE =====================


    // ===================== REMINDER =====================
    if (btnSendReminder) {
        btnSendReminder.addEventListener('click', function() {
            if(!eventToUpdate) return;var comp=getLivewireComponent();if(comp) comp.call('enviarRecordatorio', getCitaId(eventToUpdate.id));
        });
    }

    // ===================== EXTRA BUTTONS =====================
    function bindExtraButtons() {
        if(btnConfirmEvent){btnConfirmEvent.onclick=function(){if(!eventToUpdate) return;var comp=getLivewireComponent();if(comp) comp.call('cambiarEstado',getCitaId(eventToUpdate.id),'confirmada');};}
        if(btnCancelCita){btnCancelCita.onclick=function(){if(!eventToUpdate) return;var comp=getLivewireComponent();if(comp) comp.call('cambiarEstado',getCitaId(eventToUpdate.id),'cancelada');};}
        if(btnReagendar){btnReagendar.onclick=function(){if(!eventToUpdate) return;var comp=getLivewireComponent();if(!comp) return;
            comp.call('sugerirReagendamiento',getCitaId(eventToUpdate.id)).then(function(horarios){
                if(!Array.isArray(horarios)||!slotsList) return;slotsList.innerHTML='';
                horarios.forEach(function(h){var b=document.createElement('button');b.type='button';b.className='btn btn-sm btn-outline-primary rounded-pill mb-1';b.textContent='Re-agendar '+h.fecha_formateada;b.addEventListener('click',function(){comp.call('reagendarManualmenteDesdeCalendario',getCitaId(eventToUpdate.id),h.fecha_hora);});slotsList.appendChild(b);});
                if(horarios.length===0){var p=document.createElement('div');p.className='text-muted small';p.textContent='Sin horarios disponibles';slotsList.appendChild(p);}
            });
        };}
        if(btnReagendarAuto){btnReagendarAuto.onclick=function(){if(!eventToUpdate) return;var comp=getLivewireComponent();if(comp){comp.call('reagendarAutomaticamenteDesdeCalendario',getCitaId(eventToUpdate.id));}};}
    }

    // ===================== OFFCANVAS HIDDEN =====================
    if(addEventSidebar){addEventSidebar.addEventListener('hidden.bs.offcanvas',function(){closeAndResetForm();});}

    // ===================== LIVEWIRE LISTENERS =====================
    if(typeof Livewire!=='undefined'){Livewire.on('calendario-updated',function(){prefetchCache={};calendar.refetchEvents();});}
    if(typeof Livewire!=='undefined'){Livewire.on('cita-saved',function(){if(btnSubmit){btnSubmit.disabled=false;var isUpdate=btnSubmit.classList.contains('btn-update-event');btnSubmit.innerHTML=isUpdate?'<i class="ri ri-save-line me-1"></i> Actualizar':'<i class="ri ri-add-line me-1"></i> Agregar';}prefetchCache={};calendar.refetchEvents();setTimeout(function(){if(bsAddEventSidebar){try{bsAddEventSidebar.hide();}catch(e){}}},600);});}
    if(typeof Livewire!=='undefined'){Livewire.on('show-toast',function(){if(btnSubmit){btnSubmit.disabled=false;var isUpdate=btnSubmit.classList.contains('btn-update-event');btnSubmit.innerHTML=isUpdate?'<i class="ri ri-save-line me-1"></i> Actualizar':'<i class="ri ri-add-line me-1"></i> Agregar';}});}
    if(typeof Livewire!=='undefined'){Livewire.on('show-alert',function(){if(btnSubmit){btnSubmit.disabled=false;var isUpdate=btnSubmit.classList.contains('btn-update-event');btnSubmit.innerHTML=isUpdate?'<i class="ri ri-save-line me-1"></i> Actualizar':'<i class="ri ri-add-line me-1"></i> Agregar';}});}

    // ===================== CITA-SAVED LISTENER =====================
    window.addEventListener('cita-saved', function() { prefetchCache={}; calendar.refetchEvents(); });

    // ===================== SHOW-TOAST LISTENER =====================
    window.addEventListener('show-toast', function(event) {
        var data = event.detail; if(Array.isArray(data)&&data.length>0) data=data[0];
        if(data&&data.type&&data.message&&typeof window.showToast==='function') window.showToast(data.type, data.message, data.duration||5000);
    });

    // ===================== SIDEBAR TOGGLE (responsive) =====================
    if(appOverlay){appOverlay.addEventListener('click',function(){if(appCalendarSidebar) appCalendarSidebar.classList.remove('show');appOverlay.classList.remove('show');});}

    // ===================== UTILS =====================
    function hexToRgba(hex,alpha){try{hex=hex.replace('#','');var r=parseInt(hex.substring(0,2),16),g=parseInt(hex.substring(2,4),16),b=parseInt(hex.substring(4,6),16);return 'rgba('+r+','+g+','+b+','+(alpha!=null?alpha:1)+')';}catch(e){return hex;}}
}
