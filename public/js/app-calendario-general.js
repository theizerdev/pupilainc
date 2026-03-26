/**
 * Calendario General - Citas + Consultas unificado
 * Integración con FullCalendar y Livewire
 */

'use strict';

function initCalendarioGeneral(events, citaColores, consultaColores, citaLabels, consultaLabels) {
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
    const toggleConsultas = document.getElementById('toggleConsultas');
    const filtrosCitas = document.getElementById('filtros-citas');
    const filtrosConsultas = document.getElementById('filtros-consultas');
    const filterInputsCita = Array.from(document.querySelectorAll('.input-filter-cita'));
    const filterInputsConsulta = Array.from(document.querySelectorAll('.input-filter-consulta'));
    const medicosContainer = document.getElementById('medicos-calendario');
    const detailSidebar = document.getElementById('calendarioDetailSidebar');
    const filtrosContainer = document.getElementById('filtrosContainer');
    const toggleFiltrosBtn = document.getElementById('toggleFiltros');
    const floatingToggles = document.getElementById('floatingToggles');
    const floatingToggleCitas = document.getElementById('floatingToggleCitas');
    const floatingToggleConsultas = document.getElementById('floatingToggleConsultas');
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
    const eventPaciente = $('#eventPaciente');
    const eventEspecialidad = $('#eventEspecialidad');
    const eventSubespecialidad = $('#eventSubespecialidad');
    const eventMedico = $('#eventMedico');
    const eventEstado = $('#eventEstado');
    const eventTipoConsulta = $('#eventTipoConsulta');
    const eventFecha = document.getElementById('eventFecha');
    const subespecialidadContainer = document.getElementById('subespecialidadContainer');
    const slotsContainer = document.getElementById('slotsContainer');
    const slotsMessage = document.getElementById('slotsMessage');
    const slotsList = document.getElementById('slotsList');

    const citaCalendarColors = {
        pendiente: '#ffc107',
        confirmada: '#0d6efd',
        completada: '#28a745',
        cancelada: '#dc3545',
        no_asistio: '#6c757d'
    };

    let currentEvents = events || [];
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

        let citasSemana = 0, consultasSemana = 0;
        const medicoStats = {};
        const conflictos = [];

        currentEvents.forEach(function(ev) {
            const ep = ev.extendedProps || {};
            const tipoEvento = ep.tipo_evento || 'cita';
            const evStart = new Date(ev.start);

            if (evStart >= startOfWeek && evStart <= endOfWeek) {
                if (tipoEvento === 'cita') citasSemana++;
                else consultasSemana++;
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
        const consultasSemanaEl = document.getElementById('consultasSemana');
        if (citasSemanaEl) citasSemanaEl.textContent = citasSemana;
        if (consultasSemanaEl) consultasSemanaEl.textContent = consultasSemana;

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
            toggleSidebarBtn.className = 'btn btn-sm btn-icon btn-outline-secondary me-2';
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
            if (floatingToggleCitas.checked && floatingToggleConsultas) {
                floatingToggleConsultas.checked = false;
                toggleConsultas.checked = false;
                toggleConsultas.dispatchEvent(new Event('change'));
            }
            toggleCitas.checked = floatingToggleCitas.checked;
            toggleCitas.dispatchEvent(new Event('change'));
        });
        toggleCitas.addEventListener('change', function() {
            if (toggleCitas.checked && toggleConsultas) {
                toggleConsultas.checked = false;
                if (floatingToggleConsultas) floatingToggleConsultas.checked = false;
            }
            floatingToggleCitas.checked = toggleCitas.checked;
        });
    }
    if (floatingToggleConsultas && toggleConsultas) {
        floatingToggleConsultas.addEventListener('change', function() {
            if (floatingToggleConsultas.checked && floatingToggleCitas) {
                floatingToggleCitas.checked = false;
                toggleCitas.checked = false;
                toggleCitas.dispatchEvent(new Event('change'));
            }
            toggleConsultas.checked = floatingToggleConsultas.checked;
            toggleConsultas.dispatchEvent(new Event('change'));
        });
        toggleConsultas.addEventListener('change', function() {
            if (toggleConsultas.checked && toggleCitas) {
                toggleCitas.checked = false;
                if (floatingToggleCitas) floatingToggleCitas.checked = false;
            }
            floatingToggleConsultas.checked = toggleConsultas.checked;
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

        // Documento
        var documentoInput = document.getElementById('mpDocumento');
        if (documentoInput) {
            documentoInput.addEventListener('blur', function() {
                var value = this.value.trim();
                clearFieldError('mpDocumento');
                if (!value) {
                    showFieldError('documento_identidad', 'Este campo es requerido');
                } else {
                    var documentoLimpio = value.replace(/[^0-9]/g, '');
                    if (documentoLimpio.length < 6 || documentoLimpio.length > 12) {
                        showFieldError('documento_identidad', 'El documento debe tener entre 6 y 12 dígitos');
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
                if (!value) {
                    showFieldError('telefono', 'Este campo es requerido');
                } else {
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

        // Validar documento de identidad
        var documentoInput = document.getElementById('mpDocumento');
        var documentoValue = documentoInput.value.trim();
        clearFieldError('mpDocumento');

        if (!documentoValue) {
            showFieldError('documento_identidad', 'Este campo es requerido');
            errores.push('documento_identidad');
        } else {
            var documentoLimpio = documentoValue.replace(/[^0-9]/g, '');
            if (documentoLimpio.length < 6 || documentoLimpio.length > 12) {
                showFieldError('documento_identidad', 'El documento debe tener entre 6 y 12 dígitos');
                errores.push('documento_identidad');
            }
        }

        // Validar teléfono
        var telefonoInput = document.getElementById('mpTelefono');
        var telefonoValue = telefonoInput.value.trim();
        clearFieldError('mpTelefono');

        if (!telefonoValue) {
            showFieldError('telefono', 'Este campo es requerido');
            errores.push('telefono');
        } else {
            var telefonoLimpio = telefonoValue.replace(/[^0-9]/g, '');
            if (telefonoLimpio.length < 7 || telefonoLimpio.length > 15) {
                showFieldError('telefono', 'El teléfono debe tener entre 7 y 15 dígitos');
                errores.push('telefono');
            }
        }

        // Validar fecha de nacimiento
        var fechaInput = document.getElementById('mpFechaNacimiento');
        var fechaValue = fechaInput.value;
        clearFieldError('mpFechaNacimiento');

        if (!fechaValue || fechaValue.trim() === '') {
            showFieldError('fecha_nacimiento', 'Este campo es requerido');
            errores.push('fecha_nacimiento');
        } else {
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
    function resetCascadeFrom(level) {
        if (level <= 2) { eventEspecialidad.val('').trigger('change.select2'); eventEspecialidad.prop('disabled',true); eventEspecialidad.empty().append('<option value="">Primero seleccione un paciente</option>'); selectedEspecialidadId = null; }
        if (level <= 3) { eventSubespecialidad.val('').trigger('change.select2'); if(subespecialidadContainer) subespecialidadContainer.style.display='none'; selectedSubespecialidadId = null; }
        if (level <= 4) { eventMedico.val('').trigger('change.select2'); eventMedico.prop('disabled',true); eventMedico.empty().append('<option value="">Primero seleccione una especialidad</option>'); }
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
           result.slots.forEach(function(slot) {
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
                    if (selectStartFull && slot.inicio_full === selectStartFull) { btn.classList.add('selected','btn-primary'); btn.classList.remove('btn-outline-primary'); if(eventStartDate) eventStartDate.value=slot.inicio_full; if(eventEndDate) eventEndDate.value=slot.fin_full; matched=true; }
                    btn.addEventListener('click', function() {
                        slotsList.querySelectorAll('.slot-btn').forEach(function(b){b.classList.remove('selected','btn-primary');b.classList.add('btn-outline-primary');});
                        btn.classList.add('selected','btn-primary'); btn.classList.remove('btn-outline-primary');
                        if(eventStartDate) eventStartDate.value=slot.inicio_full; if(eventEndDate) eventEndDate.value=slot.fin_full;
                    });
                }
                slotsList.appendChild(btn);
            });
            if (!matched && !selectStartFull && result.primer_disponible && !esAltaEmergencia) { var firstAvailable = slotsList.querySelector('.slot-btn:not(.ocupado)'); if(firstAvailable) firstAvailable.click(); }

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
            comp.call('fetchEspecialidades').then(function(especialidades) {
                eventEspecialidad.empty().append('<option value="">Seleccionar especialidad</option>');
                especialidades.forEach(function(e){eventEspecialidad.append('<option value="'+e.id+'">'+e.nombre+'</option>');});
                eventEspecialidad.prop('disabled',false); eventEspecialidad.trigger('change.select2');
            });
        });
    }
    // Especialidad -> Subespecialidades + Medicos
    if (eventEspecialidad.length) {
        eventEspecialidad.on('change', function() {
            var espId = eventEspecialidad.val(); selectedEspecialidadId = espId||null; if(!espId){resetCascadeFrom(3);return;}
            var comp = getLivewireComponent(); if(!comp) return; resetCascadeFrom(4);
            comp.call('fetchSubespecialidades', parseInt(espId)).then(function(subs) {
                if(subs&&subs.length>0){eventSubespecialidad.empty().append('<option value="">Opcional - Seleccionar subespecialidad</option>');subs.forEach(function(s){eventSubespecialidad.append('<option value="'+s.id+'">'+s.nombre+'</option>');});if(subespecialidadContainer) subespecialidadContainer.style.display='';eventSubespecialidad.trigger('change.select2');}else{if(subespecialidadContainer) subespecialidadContainer.style.display='none';}
            });
            comp.call('fetchMedicos', parseInt(espId), null).then(function(medicos) {
                eventMedico.empty().append('<option value="">Seleccionar médico</option>');
                medicos.forEach(function(m){eventMedico.append('<option value="'+m.id+'">'+m.nombre+'</option>');});
                eventMedico.prop('disabled',false); eventMedico.trigger('change.select2');
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
            onChange: function(selectedDates, dateStr) { if(!dateStr){resetCascadeFrom(6);return;} var medicoId=eventMedico.val(); if(!medicoId) return; loadSlots(medicoId, dateStr); }
        });
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

    // Hora inicio/fin change for prioridad
    var eventHoraInicio = document.getElementById('eventHoraInicioPrioridad');
    var eventHoraFin = document.getElementById('eventHoraFinPrioridad');
    if (eventHoraInicio) {
        eventHoraInicio.addEventListener('change', validatePrioridadTime);
    }
    if (eventHoraFin) {
        eventHoraFin.addEventListener('change', validatePrioridadTime);
    }

    // ===================== PRIORITY MODE =====================
    function updatePriorityMode() {
        var prioridad = eventPrioridad ? eventPrioridad.value : 'normal';
        var section = document.getElementById('prioridadAltaEmergenciaSection');
        var isAltaEmergencia = prioridad === 'alta' || prioridad === 'emergencia';

        if (section) {
            section.style.display = isAltaEmergencia ? '' : 'none';
        }

        validatePrioridadTime();
    }

    function validatePrioridadTime() {
        var prioridad = eventPrioridad ? eventPrioridad.value : 'normal';
        var isAltaEmergencia = prioridad === 'alta' || prioridad === 'emergencia';
        var errorEl = document.getElementById('horarioPrioridadError');
        var btnSubmit = document.getElementById('addEventBtn');
        var horaInicio = document.getElementById('eventHoraInicioPrioridad');
        var horaFin = document.getElementById('eventHoraFinPrioridad');

        if (!isAltaEmergencia) {
            if (errorEl) { errorEl.style.display = 'none'; errorEl.textContent = ''; }
            if (btnSubmit) { btnSubmit.disabled = false; }
            return true;
        }

        if (!horaInicio || !horaFin || !horaInicio.value || !horaFin.value) {
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
    function isPastDateTime(date) {
        if (!date) return false;
        var now = new Date();
        return date.getTime() < now.getTime();
    }
    function showPastAlert() {
        if (window.Swal) {
            Swal.fire({ icon: 'warning', title: 'Fecha no permitida', text: 'No se pueden crear ni mover citas a fechas u horas pasadas.', confirmButtonText: 'Entendido' });
        } else {
            alert('No se pueden crear ni mover citas a fechas u horas pasadas.');
        }
    }

    // ===================== FORM HELPERS =====================
    function formatDateForLivewire(date) {
        if (!date) return '';
        return date.getFullYear() + '-' + String(date.getMonth()+1).padStart(2,'0') + '-' + String(date.getDate()).padStart(2,'0') + ' ' + String(date.getHours()).padStart(2,'0') + ':' + String(date.getMinutes()).padStart(2,'0') + ':' + String(date.getSeconds()).padStart(2,'0');
    }

    function resetValues() {
        if(eventStartDate) eventStartDate.value=''; if(eventEndDate) eventEndDate.value='';
        if(eventMotivo) eventMotivo.value=''; if(eventNotas) eventNotas.value='';
        if(eventFecha) eventFecha.value=''; if(fechaFlatpickr) fechaFlatpickr.clear();
        if(eventPaciente.length) eventPaciente.val('').trigger('change.select2');
        if(eventEstado.length) eventEstado.val('pendiente').trigger('change');
        if(eventPrioridad) eventPrioridad.value = 'normal';
        updatePriorityMode();
        horarioLaboralMedico = null;
        if(typeof eventManualTime !== 'undefined' && eventManualTime) eventManualTime.value='';
        if(eventTipoConsulta.length) eventTipoConsulta.val('').trigger('change.select2');
        resetCascadeFrom(2);
        // No limpiar eventToUpdate aquí - se necesita para edición
    }

    function closeAndResetForm() {
        resetValues();
        eventToUpdate = null;
        currentCitaId = null;
        // Restaurar botón a modo "Agregar"
        if (btnSubmit) {
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

        var ep = eventToUpdate.extendedProps;
        if(eventMotivo) eventMotivo.value = ep.motivo || ep.descripcion || '';
        if(eventNotas) eventNotas.value = ep.notas || '';
        if(eventEstado.length) eventEstado.val(ep.estado || 'pendiente').trigger('change');
        if(eventTipoConsulta.length) eventTipoConsulta.val(ep.tipo_consulta_id || '').trigger('change.select2');
        if(eventStartDate) eventStartDate.value = formatDateForLivewire(eventToUpdate.start);
        if(eventEndDate) eventEndDate.value = eventToUpdate.end ? formatDateForLivewire(eventToUpdate.end) : formatDateForLivewire(eventToUpdate.start);

        var eventDate = eventToUpdate.start;
        var dateOnly = eventDate.getFullYear()+'-'+String(eventDate.getMonth()+1).padStart(2,'0')+'-'+String(eventDate.getDate()).padStart(2,'0');
        if(fechaFlatpickr) fechaFlatpickr.setDate(dateOnly, false);

        var comp = getLivewireComponent();
        if (comp && eventPaciente.length) {
            eventPaciente.val(ep.paciente_id).trigger('change.select2');
            comp.call('fetchEspecialidades').then(function(especialidades) {
                eventEspecialidad.empty().append('<option value="">Seleccionar especialidad</option>');
                especialidades.forEach(function(e){eventEspecialidad.append('<option value="'+e.id+'">'+e.nombre+'</option>');});
                eventEspecialidad.prop('disabled',false);
                if (ep.especialidad_id) {
                    selectedEspecialidadId = String(ep.especialidad_id);
                    eventEspecialidad.val(ep.especialidad_id).trigger('change.select2');
                    comp.call('fetchSubespecialidades', parseInt(ep.especialidad_id)).then(function(subs) {
                        if(subs&&subs.length>0){eventSubespecialidad.empty().append('<option value="">Opcional</option>');subs.forEach(function(s){eventSubespecialidad.append('<option value="'+s.id+'">'+s.nombre+'</option>');});if(subespecialidadContainer) subespecialidadContainer.style.display='';if(ep.subespecialidad_id){selectedSubespecialidadId=String(ep.subespecialidad_id);eventSubespecialidad.val(ep.subespecialidad_id).trigger('change.select2');}}
                    });
                    var subIdForMedicos = ep.subespecialidad_id ? parseInt(ep.subespecialidad_id) : null;
                    comp.call('fetchMedicos', parseInt(ep.especialidad_id), subIdForMedicos).then(function(medicos) {
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
        var badgeColor = isCita ? (citaCalendarColors[ep.estado]||'#78909C') : (consultaColores[ep.estado]||'#78909C');
        var tipoLabel = isCita ? 'CITA' : 'CONSULTA';
        var estadoLabel = ep.estado_label || ep.estadoLabel || ep.estado || '';
        var html = '<div class="tooltip-header"><span class="tooltip-badge me-1" style="background:'+(isCita?'#0d6efd':'#6f42c1')+'">'+tipoLabel+'</span><span class="tooltip-badge" style="background:'+badgeColor+'">'+estadoLabel+'</span></div>';
        html += '<div><strong>Paciente:</strong> '+(ep.paciente||info.event.title)+'</div>';
        if(!isCita && ep.nickname) html+='<div><strong>Le gusta que le digan:</strong> '+ep.nickname+'</div>';
        if(ep.edad) html+='<div><strong>Edad:</strong> '+ep.edad+'</div>';
        html += '<div><strong>Médico:</strong> '+(ep.medico||ep.medico_full||'')+'</div>';
        if(ep.especialidad) html+='<div><strong>Especialidad:</strong> '+ep.especialidad+'</div>';
        if(ep.motivo||ep.descripcion) html+='<div><strong>Motivo:</strong> '+(ep.motivo||ep.descripcion)+'</div>';
        if(ep.notas) html+='<div><strong>Notas:</strong> '+ep.notas+'</div>';
        if(ep.tiempo_espera_formateado&&ep.tiempo_espera_formateado!=='No en sala de espera') html+='<div><strong>Tiempo espera:</strong> '+ep.tiempo_espera_formateado+'</div>';
        if(ep.tipo_consulta_nombre) html+='<div><strong>Tipo:</strong> '+ep.tipo_consulta_nombre+'</div>';
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
        var citasEnabled = toggleCitas ? toggleCitas.checked : true;
        var consultasEnabled = toggleConsultas ? toggleConsultas.checked : true;
        var citaStates = citasEnabled ? filterInputsCita.filter(function(c){return c.checked;}).map(function(c){return c.dataset.value;}) : [];
        var consultaStates = consultasEnabled ? filterInputsConsulta.filter(function(c){return c.checked;}).map(function(c){return c.dataset.value;}) : [];
        return { citaStates: citaStates, consultaStates: consultaStates, citasEnabled: citasEnabled, consultasEnabled: consultasEnabled };
    }

    // ===================== SEARCH & FILTER =====================

    // ===================== FILTROS RÁPIDOS DE FECHA =====================
    var btnGoToday = document.getElementById('btnGoToday');
    var btnGoWeek = document.getElementById('btnGoWeek');
    var btnGoMonth = document.getElementById('btnGoMonth');
    var btnLimpiarFiltros = document.getElementById('btnLimpiarFiltros');
    var filterMedico = document.getElementById('filterMedico');

    if (btnGoToday) {
        btnGoToday.addEventListener('click', function() {
            calendar.gotoDate(new Date());
            calendar.changeView('timeGridDay');
        });
    }

    if (btnGoWeek) {
        btnGoWeek.addEventListener('click', function() {
            calendar.gotoDate(new Date());
            calendar.changeView('timeGridWeek');
        });
    }

    if (btnGoMonth) {
        btnGoMonth.addEventListener('click', function() {
            calendar.gotoDate(new Date());
            calendar.changeView('dayGridMonth');
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
            if (filterInputsCita) {
                filterInputsCita.forEach(function(cb) { cb.checked = true; });
            }
            if (filterInputsConsulta) {
                filterInputsConsulta.forEach(function(cb) { cb.checked = true; });
            }

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
        if (citasCount && filterInputsCita) {
            var checkedCitas = filterInputsCita.filter(function(cb) { return cb.checked; }).length;
            var totalCitas = filterInputsCita.length;
            citasCount.textContent = checkedCitas + '/' + totalCitas;
        }

        // Contador de consultas activas
        var consultasCount = document.getElementById('consultasActivasCount');
        if (consultasCount && filterInputsConsulta) {
            var checkedConsultas = filterInputsConsulta.filter(function(cb) { return cb.checked; }).length;
            var totalConsultas = filterInputsConsulta.length;
            consultasCount.textContent = checkedConsultas + '/' + totalConsultas;
        }
    }

    // Actualizar contadores cuando cambien los filtros
    if (filterInputsCita) {
        filterInputsCita.forEach(function(cb) {
            cb.addEventListener('change', updateAccordionCounts);
        });
    }
    if (filterInputsConsulta) {
        filterInputsConsulta.forEach(function(cb) {
            cb.addEventListener('change', updateAccordionCounts);
        });
    }
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

    // ===================== EVENT RENDERING =====================
    function getEventColor(event) {
        var ep = event.extendedProps||{}, tipoEvento = ep.tipo_evento||'cita', estado = ep.calendar||ep.estado||'';
        // Colores sólidos con buen contraste WCAG 2.1 (contraste mínimo 4.5:1 con blanco)
        var estadoColores = {
            pendiente: '#E65100',    // Naranja oscuro
            confirmada: '#0D47A1',   // Azul oscuro
            completada: '#1B5E20',  // Verde oscuro
            cancelada: '#B71C1C',   // Rojo oscuro
            no_asistio: '#37474F'    // Gris azulado oscuro
        };
        if(tipoEvento==='consulta') {
            var consultaColoresMapeo = {
                'en_sala': '#1B5E20',
                'en_atencion': '#0D47A1',
                'atendido': '#004D40',
                'cancelada': '#B71C1C',
                'no_asistio': '#37474F'
            };
            return consultaColoresMapeo[estado] || '#455A64';
        }
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
            else if(tipoEvento==='consulta'&&filters.consultasEnabled) visible=filters.consultaStates.indexOf(estado)!==-1;
            if(visible&&selectedMedicoFilter){var medicoId=ep.medico_id;if(medicoId&&String(medicoId)!==String(selectedMedicoFilter)) visible=false;}
            if(visible&&searchTerm){var paciente=(ep.paciente||'').toLowerCase();var nickname=(ep.nickname||'').toLowerCase();if(paciente.indexOf(searchTerm)===-1&&nickname.indexOf(searchTerm)===-1) visible=false;}
            if(visible&&selectedEspecialidadFilter){var especialidadId=ep.especialidad_id;if(especialidadId&&String(especialidadId)!==String(selectedEspecialidadFilter)) visible=false;}
            return visible;
        });
    }

    // ===================== SCROLL CONTAINER (declared early for viewDidMount access) =====================
    var calendarScrollContainer = document.getElementById('calendarScrollContainer');
    var calendarPs = null;

    // ===================== FULLCALENDAR =====================
    var calendar = new Calendar(calendarEl, {
        initialView: 'timeGridDay',
        plugins: [dayGridPlugin, interactionPlugin, listPlugin, timegridPlugin],
        events: fetchEvents,
        locale: 'es',
        firstDay: 1,
        nowIndicator: true,
        slotEventOverlap: false,
        slotMinTime: '06:00:00',
        slotMaxTime: '22:00:00',
        scrollTime: '07:00:00',

        contentHeight: 'auto',
        expandRows: true,
        slotDuration: '00:30:00',
        eventMinHeight: 25,
        eventShortHeight: 25,
        editable: true,
        eventResizableFromStart: true,
        customButtons: { sidebarToggle: { text: 'Menú' } },
        headerToolbar: { start: 'sidebarToggle, prev,next, title', end: 'timeGridDay,timeGridWeek,dayGridMonth,listMonth' },
        buttonText: { today: 'Hoy', month: 'Mes', week: 'Semana', day: 'Día', list: 'Lista' },
        direction: document.documentElement.getAttribute('dir')==='rtl'?'rtl':'ltr',
        initialDate: new Date(),
        navLinks: true,
        datesSet: function () {
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
                    wrapper.style.marginBottom = '1rem';
                    scrollContainer.parentNode.insertBefore(wrapper, scrollContainer);
                }
                wrapper.appendChild(header);
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
            var estadoLabel = ep.estado_label || ep.estadoLabel || ep.estado || '';
            var estadoColor = getEventColor(arg.event);
            var estadoBadge = '<span class="badge rounded-pill" style="background:' + estadoColor + ';color:#fff;font-size:0.6rem;padding:1px 6px;white-space:nowrap;">' + estadoLabel + '</span>';

            // Type badge (CITA vs CONSULTA)
            var typeBadgeColor = isCita ? '#0d6efd' : '#6f42c1';
            var typeLabel = isCita ? 'CITA' : 'CONSULTA';
            var typeBadge = '<span class="fc-event-type-badge" style="background:' + typeBadgeColor + ';color:#fff;">' + typeLabel + '</span>';

            // Indicador de consulta vinculada para citas
            var consultaBadge = '';
            if (isCita && ep.tiene_consulta) {
                consultaBadge = '<span hidden class="badge" style="background:#00897B;color:#fff;font-size:0.6rem;padding:1px 5px;" title="Tiene consulta vinculada"><i class="ri ri-links-line"></i></span>';
            }

            // Badge de prioridad (solo para citas, sin color)
            var prioridadBadge = '';
            if (isCita && ep.prioridad && ep.prioridad !== 'normal') {
                var prioridadLabel = ep.prioridad_label || ep.prioridad.charAt(0).toUpperCase() + ep.prioridad.slice(1);
                prioridadBadge = '<span class="badge" style="background:#6c757d;color:#fff;font-size:0.6rem;padding:1px 6px;font-weight:600;" title="Prioridad ' + prioridadLabel + '">' + prioridadLabel + '</span>';
            }

            // Edad
            var edadHtml = ep.edad ? '<span style="font-size:0.7rem;opacity:0.9;"> · ' + ep.edad + '</span>' : '';

            // Tiempo en estado (para consultas activas desde sala_espera en adelante)
            var tiempoBadge = '';
            if (!isCita) {
                var estadosActivos = ['sala_espera', 'en_enfermeria', 'en_consultorio', 'en_consultorio_optometrista', 'en_gotas', 'en_optica', 'en_estudio'];
                var esEstadoActivo = estadosActivos.indexOf(ep.estado) !== -1;

                if (esEstadoActivo && ep.estado_changed_at) {
                    var te = formatTiempoEstado(ep.estado_changed_at);
                    if (te) {
                        tiempoBadge = '<span class="fc-event-tiempo-badge" style="background:#E64A19;color:#fff;" title="Tiempo en ' + (ep.estado_label || ep.estado) + '"><i class="ri ri-time-line me-1"></i>' + te + '</span>';
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
            if (view.type === 'timeGridWeek' || view.type === 'timeGridDay') {
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

            if (view.type === 'dayGridMonth') {
                html = '<div class="fc-event-main-frame fc-month-event">' +
                    '<div style="display:flex;align-items:center;gap:4px;overflow:hidden;">' +
                        (timeText ? '<span class="fc-event-time">' + timeText + '</span>' : '') +
                        '<span class="fc-event-title" style="flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">' + pacienteNombre + '</span>' +
                    '</div>' +
                    '<div class="fc-event-subtitle" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">Dr(a). ' + medicoNombre + edadHtml + '</div>' +
                    '<div style="display:flex;align-items:center;gap:3px;flex-wrap:wrap;margin-top:2px;">' + typeBadge + prioridadBadge + consultaBadge + estadoBadge + tiempoBadge + '</div>' +
                '</div>';
            } else if (view.type === 'timeGridDay') {
                var dayTimeStart = arg.event.start ? moment(arg.event.start).format('hh:mm A') : '';
                var dayTimeEnd = arg.event.end ? moment(arg.event.end).format('hh:mm A') : '';
                var dayTimeRange = dayTimeStart + (dayTimeEnd ? ' - ' + dayTimeEnd : '');
                html = '<div class="fc-event-main-frame" style="width:100%;height:100%;display:flex;flex-direction:column;padding:2px 4px;box-sizing:border-box;overflow:hidden;">' +
                    '<div style="font-weight:600;font-size:0.75rem;line-height:1.2;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">' + nombreCompleto + '</div>' +
                    '<div style="font-size:0.65rem;opacity:0.85;line-height:1.2;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">Dr(a). ' + medicoNombre + '</div>' +
                    (ep.edad ? '<div style="font-size:0.6rem;opacity:0.8;line-height:1.2;">' + ep.edad + '</div>' : '') +
                    '<div style="font-size:0.6rem;opacity:0.8;line-height:1.2;">' + dayTimeRange + '</div>' +
                '</div>';
            } else if (view.type === 'timeGridWeek') {
                html = '<div class="fc-event-main-frame" style="width:100%;height:100%;display:flex;flex-direction:column;padding:2px 4px;box-sizing:border-box;overflow:hidden;">' +
                    '<div style="font-weight:600;font-size:0.75rem;line-height:1.2;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">' + nombreCompleto + '</div>' +
                    '<div style="font-size:0.65rem;opacity:0.85;line-height:1.2;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">Dr(a). ' + medicoNombre + '</div>' +
                '</div>';
            } else if (view.type === 'listMonth' || view.type === 'listWeek') {
                // Horario desde - hasta
                var listTimeStart = arg.event.start ? moment(arg.event.start).format('hh:mm A') : '';
                var listTimeEnd = arg.event.end ? moment(arg.event.end).format('hh:mm A') : '';
                var listTimeRange = listTimeStart + (listTimeEnd ? ' - ' + listTimeEnd : '');

                html = '<div style="display:flex;align-items:flex-start;gap:12px;width:100%;">' +
                    '<div style="flex:1;min-width:0;">' +
                        '<div style="font-weight:600;font-size:0.9rem;margin-bottom:2px;">' + nombreCompleto + '</div>' +
                        '<div style="font-size:0.8rem;color:#6c757d;">Dr(a). ' + medicoNombre + '</div>' +
                        '<div style="font-size:0.75rem;color:#888;margin-top:2px;"><i class="ri-time-line" style="font-size:0.7rem;"></i> ' + listTimeRange + '</div>' +
                    '</div>' +
                    '<div style="display:flex;align-items:center;gap:4px;flex-shrink:0;flex-wrap:wrap;justify-content:flex-end;">' + typeBadge + prioridadBadge + consultaBadge + estadoBadge + tiempoBadge + '</div>' +
                '</div>';
            } else {
                return { html: '<div>' + arg.event.title + '</div>' };
            }

            return { html: html };
        },
        eventDidMount: function(info) {
            var ep = info.event.extendedProps||{}, tipoEvento = ep.tipo_evento||'cita', isCita = tipoEvento==='cita';
            if(isCita) info.el.classList.add('event-cita'); else info.el.classList.add('event-consulta');

            var tipoColor = (isCita && ep.tipo_consulta_color) ? ep.tipo_consulta_color : (isCita ? '#1565C0' : '#7B1FA2');
            var estadoColor = getEventColor(info.event);

            // Estilo Google Calendar: fondo sólido, borde izquierdo de estado
            info.el.style.backgroundColor = tipoColor;
            info.el.style.borderColor = tipoColor;
            info.el.style.borderLeftColor = estadoColor || tipoColor;
            info.el.style.borderLeftWidth = '4px';
            info.el.style.color = '#fff';

            info.el.addEventListener('mouseenter',function(){showTooltip(info);});
            info.el.addEventListener('mouseleave',removeTooltip);
        },
        dateClick: function(info) {
            var clickedDate = info.date || new Date(info.dateStr);
            if(isPastDateTime(clickedDate)){showPastAlert();return;}
            var dateOnly = info.dateStr.substring(0,10);

            // Limpiar estado de edición anterior
            currentCitaId = null;
            eventToUpdate = null;
            resetValues();

            if(bsAddEventSidebar) bsAddEventSidebar.show();
            if(offcanvasTitle) offcanvasTitle.innerHTML='Nueva Cita';
            if(btnSubmit){btnSubmit.innerHTML='<i class="ri ri-add-line me-1"></i> Agregar';btnSubmit.classList.remove('btn-update-event');btnSubmit.classList.add('btn-add-event');}


            if(btnConfirmEvent) btnConfirmEvent.classList.add('d-none');
            if(btnCancelCita) btnCancelCita.classList.add('d-none');
            if(btnReagendar) btnReagendar.classList.add('d-none');
            if(btnReagendarAuto) btnReagendarAuto.classList.add('d-none');
            if(fechaFlatpickr) fechaFlatpickr.setDate(dateOnly, false);
        },
        eventClick: function(info) {
            removeTooltip();
            var ep = info.event.extendedProps||{};
            if(ep.tipo_evento==='cita') { citaEventClick(info); }
            else { showEventDetail(info.event); }
        },
        eventDrop: function(info) {
            var ep = info.event.extendedProps||{};
            if(ep.tipo_evento!=='cita'){info.revert();return;}
            if(isPastDateTime(info.event.start)){info.revert();showPastAlert();return;}
            var comp = getLivewireComponent(); if(!comp){info.revert();return;}
            var newStart = info.event.start;
            var newEnd = info.event.end;
            if(!newEnd && info.oldEvent.end && info.oldEvent.start){
                var durMs = info.oldEvent.end.getTime() - info.oldEvent.start.getTime();
                newEnd = new Date(newStart.getTime() + durMs);
            }
            if(!newEnd) newEnd = newStart;
            comp.call('updateCitaFechas', getCitaId(info.event.id), formatDateForLivewire(newStart), formatDateForLivewire(newEnd));
        },
        eventResize: function(info) {
            var ep = info.event.extendedProps||{};
            if(ep.tipo_evento!=='cita'){info.revert();return;}
            if(isPastDateTime(info.event.start)){info.revert();showPastAlert();return;}
            var comp = getLivewireComponent(); if(!comp){info.revert();return;}
            comp.call('updateCitaFechas', getCitaId(info.event.id), formatDateForLivewire(info.event.start), info.event.end?formatDateForLivewire(info.event.end):formatDateForLivewire(info.event.start));
        },
        viewDidMount: function() { modifyToggler(); }
    });

    calendar.render();
    modifyToggler();
    createToggleButton();

    function resizeCalendarScroll() {
        if (!calendarScrollContainer) return;
        var rect = calendarScrollContainer.getBoundingClientRect();
        var avail = window.innerHeight - rect.top - 16;
        if (avail > 300) calendarScrollContainer.style.height = avail + 'px';
        if (calendarPs && typeof calendarPs.update === 'function') calendarPs.update();
    }
    function initCalendarPs() {
        if (!calendarScrollContainer) return;
        if (typeof PerfectScrollbar !== 'undefined') {
            if (!calendarPs) calendarPs = new PerfectScrollbar(calendarScrollContainer, { wheelSpeed: 1, wheelPropagation: false, suppressScrollX: true, swipeEasing: true, minScrollbarLength: 40 });
        } else {
            calendarScrollContainer.style.overflowY = 'auto';
        }
        resizeCalendarScroll();
    }
    window.addEventListener('resize', resizeCalendarScroll);
    initCalendarPs();

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
        var consultasCount = 0;

        eventosOrdenados.forEach(function(seg) {
            var event = seg.event;
            var ep = event.extendedProps || {};
            var tipoEvento = ep.tipo_evento || 'cita';
            var isCita = tipoEvento === 'cita';

            if (isCita) citasCount++;
            else consultasCount++;

            var estadoColor = isCita ? (citaCalendarColors[ep.estado] || '#78909C') : (consultaColores[ep.estado] || '#78909C');
            var estadoLabel = ep.estado_label || ep.estadoLabel || ep.estado || '';
            var typeBadgeColor = isCita ? '#0d6efd' : '#6f42c1';
            var typeLabel = isCita ? 'CITA' : 'CONSULTA';

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
                bgStyle = 'background: linear-gradient(135deg, ' + hexToRgba(typeBadgeColor, 0.15) + ' 0%, ' + hexToRgba(estadoColor, 0.1) + ' 100%);';
            }

            html += '<div class="day-event-item ' + (isCita ? 'event-cita' : 'event-consulta') + '" data-event-id="' + event.id + '" style="' + bgStyle + 'border-left: 4px solid ' + estadoColor + ';">';
            html += '<div class="d-flex justify-content-between align-items-start mb-2">';
            html += '<div class="d-flex align-items-center gap-2">';
            html += '<span class="badge" style="background:' + typeBadgeColor + ';color:#fff;font-size:0.65rem;">' + typeLabel + '</span>';
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
        statsHtml += '<div class="col-6"><div class="fw-medium">' + consultasCount + '</div><div class="small text-muted">Consultas</div></div>';
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
    var consultaEventIdActual = null;

    function showEventDetail(event) {
        var ep=event.extendedProps||{},tipoEvento=ep.tipo_evento||'cita',isCita=tipoEvento==='cita';
        var title=document.getElementById('calendarioDetailTitle'),body=document.getElementById('calendarioDetailBody');
        title.textContent=isCita?'Detalle de Cita':'Detalle de Consulta';
        var badgeColor=isCita?(citaCalendarColors[ep.estado]||'#78909C'):(consultaColores[ep.estado]||'#78909C');
        var estadoLabel=ep.estado_label||ep.estadoLabel||ep.estado||'',tipoLabel=isCita?'CITA':'CONSULTA';

        consultaEventIdActual = isCita ? null : parseInt(event.id.replace('consulta_', ''), 10);

        var html='<div class="mb-3"><span class="badge me-1" style="background:'+(isCita?'#0d6efd':'#6f42c1')+'">'+tipoLabel+'</span><span class="badge" style="background:'+badgeColor+'">'+estadoLabel+'</span></div>';
        html+='<div class="mb-3"><h6 class="mb-1">Paciente</h6><p class="mb-0">'+(ep.paciente||event.title)+'</p></div>';
        if(ep.edad) html+='<div class="mb-3"><h6 class="mb-1">Edad</h6><p class="mb-0">'+ep.edad+'</p></div>';
        html+='<div class="mb-3"><h6 class="mb-1">Médico</h6><p class="mb-0">'+(ep.medico||ep.medico_full||'Sin médico')+'</p></div>';
        if(ep.especialidad) html+='<div class="mb-3"><h6 class="mb-1">Especialidad</h6><p class="mb-0">'+ep.especialidad+'</p></div>';
        if(ep.motivo||ep.descripcion) html+='<div class="mb-3"><h6 class="mb-1">Motivo</h6><p class="mb-0">'+(ep.motivo||ep.descripcion)+'</p></div>';
        if(ep.codigo) html+='<div class="mb-3"><h6 class="mb-1">Código</h6><p class="mb-0">'+ep.codigo+'</p></div>';
        if(ep.tiempo_espera_formateado&&ep.tiempo_espera_formateado!=='No en sala de espera') html+='<div class="mb-3"><h6 class="mb-1">Tiempo en Espera</h6><p class="mb-0">'+ep.tiempo_espera_formateado+'</p></div>';
        var start=event.start,end=event.end;
        if(start){var timeStr=moment(start).format('DD/MM/YYYY HH:mm');if(end) timeStr+=' - '+moment(end).format('HH:mm');html+='<div class="mb-3"><h6 class="mb-1">Horario</h6><p class="mb-0">'+timeStr+'</p></div>';}

        if (!isCita) {
            var estadosDisponibles = [
                { value: 'por_llegar', label: 'Por llegar' },
                { value: 'sala_espera', label: 'Sala de Espera' },
                { value: 'en_enfermeria', label: 'En Enfermería' },
                { value: 'en_consultorio', label: 'En Consultorio' },
                { value: 'en_consultorio_optometrista', label: 'En Consultorio Optometrista' },
                { value: 'en_gotas', label: 'En Gotas' },
                { value: 'en_optica', label: 'En Óptica' },
                { value: 'en_estudio', label: 'En Estudio' },
                { value: 'finalizada', label: 'Finalizada' }
            ];
            var estadosSelect = '';
            estadosDisponibles.forEach(function(est) {
                estadosSelect += '<option value="' + est.value + '"' + (ep.estado === est.value ? ' selected' : '') + '>' + est.label + '</option>';
            });
            html += '<hr/><div class="mb-3"><h6 class="mb-2">Cambiar Estado</h6><select class="form-select mb-2" id="consultaNuevoEstado">' + estadosSelect + '</select>';
            html += '<button class="btn btn-primary w-100" id="btnActualizarEstadoConsulta"><i class="ri-check-line me-1"></i>Actualizar Estado</button></div>';
        }

        html+='<div class="mt-3"><a href="/admin/gestion/consultas" class="btn btn-sm btn-outline-primary w-100"><i class="ri-stethoscope-line me-1"></i> Ir a Consultas</a></div>';
        body.innerHTML=html;

        if (!isCita) {
            var btnActualizar = document.getElementById('btnActualizarEstadoConsulta');
            if (btnActualizar) {
                btnActualizar.addEventListener('click', function() {
                    var nuevoEstado = document.getElementById('consultaNuevoEstado').value;
                    if (!nuevoEstado) {
                        showToast('Seleccione un estado', 'warning');
                        return;
                    }
                    var comp = getLivewireComponent();
                    console.log('Componente Livewire:', comp);
                    console.log('Consulta ID:', consultaEventIdActual, 'Tipo:', typeof consultaEventIdActual);
                    console.log('Nuevo Estado:', nuevoEstado);
                    if (!comp) {
                        showToast('Error: No se encontró el componente Livewire', 'error');
                        return;
                    }
                    if (!consultaEventIdActual) {
                        showToast('Error: ID de consulta no válido', 'error');
                        return;
                    }
                    btnActualizar.disabled = true;
                    btnActualizar.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Guardando...';
                    comp.call('cambiarEstadoConsulta', consultaEventIdActual, nuevoEstado).then(function() {
                        showToast('Estado actualizado', 'success');
                        prefetchCache={}; calendar.refetchEvents();
                        bsDetailSidebar.hide();
                    }).catch(function(err) {
                        console.error('Error al actualizar:', err);
                        btnActualizar.disabled = false;
                        btnActualizar.innerHTML = '<i class="ri-check-line me-1"></i>Actualizar Estado';
                        showToast('Error al actualizar', 'error');
                    });
                });
            }
        }

        bsDetailSidebar.show();
    }

    // ===================== INLINE MINI CALENDAR =====================
    var inlineFechaFiltroActivo = document.getElementById('fechaFiltroActivo');
    var inlineFechaFiltroTexto = document.getElementById('fechaFiltroTexto');

    if (inlineCalendar) {
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
    if(toggleConsultas){toggleConsultas.addEventListener('change',function(){filtrosConsultas.style.display=this.checked?'':'none';prefetchCache={};calendar.refetchEvents();});}
    filterInputsCita.forEach(function(input){input.addEventListener('change',function(){prefetchCache={};calendar.refetchEvents();});});
    filterInputsConsulta.forEach(function(input){input.addEventListener('change',function(){prefetchCache={};calendar.refetchEvents();});});

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
                eventPaciente: { validators: { notEmpty: { message: 'Seleccione un paciente' } } },
                eventEspecialidad: { validators: { notEmpty: { message: 'Seleccione una especialidad' } } },
                eventMedico: { validators: { notEmpty: { message: 'Seleccione un médico' } } },
                eventStartDate: { validators: { notEmpty: { message: 'Seleccione un horario' } } },
                eventMotivo: { validators: { notEmpty: { message: 'Ingrese el motivo de la cita' } } }
            },
            plugins: {
                trigger: new FormValidation.plugins.Trigger(),
                bootstrap5: new FormValidation.plugins.Bootstrap5({ eleValidClass:'', rowSelector:function(field,ele){return '.form-control-validation';} }),
                submitButton: new FormValidation.plugins.SubmitButton(),
                autoFocus: new FormValidation.plugins.AutoFocus()
            }
        }).on('core.form.valid',function(){isFormValid=true;}).on('core.form.invalid',function(){isFormValid=false;});
        if(eventPaciente.length) eventPaciente.on('change',function(){fv.revalidateField('eventPaciente');});
        if(eventEspecialidad.length) eventEspecialidad.on('change',function(){fv.revalidateField('eventEspecialidad');});
        if(eventMedico.length) eventMedico.on('change',function(){fv.revalidateField('eventMedico');});
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

            var startDate = eventStartDate ? eventStartDate.value : '';
            var endDate = eventEndDate ? eventEndDate.value : '';

            var esAltaEmergencia = prioridad === 'alta' || prioridad === 'emergencia';
            if (esAltaEmergencia) {
                var fecha = eventFecha ? eventFecha.value : '';
                var horaInicio = document.getElementById('eventHoraInicioPrioridad');
                var horaFin = document.getElementById('eventHoraFinPrioridad');
                if (fecha && horaInicio && horaInicio.value && horaFin && horaFin.value) {
                    startDate = fecha + ' ' + horaInicio.value + ':00';
                    endDate = fecha + ' ' + horaFin.value + ':00';
                }
            }

            var eventData = {
                paciente_id: eventPaciente.val(),
                especialidad_id: selectedEspecialidadId,
                subespecialidad_id: selectedSubespecialidadId,
                medico_id: eventMedico.val(),
                start: startDate,
                end: endDate,
                motivo: eventMotivo ? eventMotivo.value : '',
                notas: eventNotas ? eventNotas.value : '',
                estado: isNewCita ? 'pendiente' : (eventEstado.val() || 'pendiente'),
                tipo_consulta_id: eventTipoConsulta.val() || '',
                prioridad: prioridad
            };
            var comp = getLivewireComponent(); if(!comp) return;
            if (btnSubmit.classList.contains('btn-update-event') && currentCitaId) {
                comp.set('citaId', currentCitaId);
            }
            comp.call('saveCita', eventData);
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
    if(typeof Livewire!=='undefined'){Livewire.on('cita-saved',function(){if(btnSubmit){btnSubmit.disabled=false;var isUpdate=btnSubmit.classList.contains('btn-update-event');btnSubmit.innerHTML=isUpdate?'<i class="ri ri-save-line me-1"></i> Actualizar':'<i class="ri ri-add-line me-1"></i> Agregar';}prefetchCache={};calendar.refetchEvents();if(bsAddEventSidebar){try{bsAddEventSidebar.hide();}catch(e){}}});}
    if(typeof Livewire!=='undefined'){Livewire.on('show-toast',function(){window.setTimeout(function(){if(btnSubmit){btnSubmit.disabled=false;var isUpdate=btnSubmit.classList.contains('btn-update-event');btnSubmit.innerHTML=isUpdate?'<i class="ri ri-save-line me-1"></i> Actualizar':'<i class="ri ri-add-line me-1"></i> Agregar';}},500);});}
    if(typeof Livewire!=='undefined'){Livewire.on('show-alert',function(){window.setTimeout(function(){if(btnSubmit){btnSubmit.disabled=false;var isUpdate=btnSubmit.classList.contains('btn-update-event');btnSubmit.innerHTML=isUpdate?'<i class="ri ri-save-line me-1"></i> Actualizar':'<i class="ri ri-add-line me-1"></i> Agregar';}},500);});}

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
