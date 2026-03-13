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
            var matched = false;
           result.slots.forEach(function(slot) {
                var btn = document.createElement('button'); btn.type='button';
                // Formato compacto: solo hora de inicio
                var timeOnly = slot.label.split(' - ')[0];
                btn.className = 'btn btn-sm rounded-pill slot-btn ' + (slot.disponible ? 'btn-outline-primary' : 'btn-outline-secondary ocupado');
                btn.textContent = timeOnly; btn.disabled = !slot.disponible;
                if (slot.disponible) {
                    if (selectStartFull && slot.inicio_full === selectStartFull) { btn.classList.add('selected','btn-primary'); btn.classList.remove('btn-outline-primary'); if(eventStartDate) eventStartDate.value=slot.inicio_full; if(eventEndDate) eventEndDate.value=slot.fin_full; matched=true; }
                    btn.addEventListener('click', function() {
                        slotsList.querySelectorAll('.slot-btn').forEach(function(b){b.classList.remove('selected','btn-primary');b.classList.add('btn-outline-primary');});
                        btn.classList.add('selected','btn-primary'); btn.classList.remove('btn-outline-primary');
                        if(eventStartDate) eventStartDate.value=slot.inicio_full; if(eventEndDate) eventEndDate.value=slot.fin_full;
                    });
                }
                slotsList.appendChild(btn);
            });
            if (!matched && !selectStartFull && result.primer_disponible) { var firstAvailable = slotsList.querySelector('.slot-btn:not(.ocupado)'); if(firstAvailable) firstAvailable.click(); }
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
            dateFormat: 'Y-m-d', minDate: 'today',
            locale: typeof flatpickr !== 'undefined' && flatpickr.l10ns && flatpickr.l10ns.es ? 'es' : 'default',
            onChange: function(selectedDates, dateStr) { if(!dateStr){resetCascadeFrom(6);return;} var medicoId=eventMedico.val(); if(!medicoId) return; loadSlots(medicoId, dateStr); }
        });
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
        if(eventTipoConsulta.length) eventTipoConsulta.val('').trigger('change.select2');
        resetCascadeFrom(2); eventToUpdate = null;
    }

    function getCitaId(eventId) {
        if (!eventId) return null;
        return parseInt(String(eventId).replace('cita_', ''));
    }

    // ===================== CITA EVENT CLICK =====================
    function citaEventClick(info) {
        eventToUpdate = info.event;
        info.jsEvent.preventDefault();
        if (bsAddEventSidebar) bsAddEventSidebar.show();
        if (offcanvasTitle) offcanvasTitle.innerHTML = 'Editar Cita';
        if (btnSubmit) { btnSubmit.innerHTML = '<i class="ri ri-save-line me-1"></i> Actualizar'; btnSubmit.classList.add('btn-update-event'); btnSubmit.classList.remove('btn-add-event'); }
        
        if (btnSendReminder) btnSendReminder.classList.remove('d-none');

        // Create extra buttons if needed
       

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
        if(tipoEvento==='consulta') return consultaColores[estado]||'#78909C';
        return citaCalendarColors[estado]||'#ffc107';
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

    // ===================== FULLCALENDAR =====================
    var calendar = new Calendar(calendarEl, {
        initialView: 'timeGridDay',
        plugins: [dayGridPlugin, interactionPlugin, listPlugin, timegridPlugin],
        events: fetchEvents,
        locale: 'es',
        firstDay: 1,
        nowIndicator: true,
        dayMaxEvents: 4,
        slotMinTime: '06:00:00',
        slotMaxTime: '22:00:00',
        scrollTime: '07:00:00',
        contentHeight: 'auto',
        expandRows: false,
        editable: true,
        eventResizableFromStart: true,
        customButtons: { sidebarToggle: { text: 'Menú' } },
        headerToolbar: { start: 'sidebarToggle, prev,next, title', end: 'timeGridDay,timeGridWeek,dayGridMonth,listMonth' },
        buttonText: { today: 'Hoy', month: 'Mes', week: 'Semana', day: 'Día', list: 'Lista' },
        direction: document.documentElement.getAttribute('dir')==='rtl'?'rtl':'ltr',
        initialDate: new Date(),
        navLinks: true,
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

            // Edad
            var edadHtml = ep.edad ? '<span style="font-size:0.6rem;opacity:0.7;"> · ' + ep.edad + '</span>' : '';

            // Tiempo en estado (solo consultas)
            var tiempoBadge = '';
            if (!isCita && ep.estado_changed_at) {
                var tiempoEstado = formatTiempoEstado(ep.estado_changed_at);
                if (tiempoEstado) {
                    tiempoBadge = '<span style="background:rgba(0,0,0,0.08);color:#555;border-radius:4px;padding:1px 5px;font-size:65%;margin-left:4px;" title="Tiempo en estado actual"><i class="ri ri-time-line" style="font-size:0.6rem;vertical-align:middle;margin-right:2px;"></i>' + tiempoEstado + '</span>';
                }
            }

            if (view.type === 'dayGridMonth') {
                html = '<div class="fc-event-main-frame fc-month-event">' +
                    '<div style="display:flex;align-items:center;gap:4px;overflow:hidden;">' +
                        (timeText ? '<span class="fc-event-time">' + timeText + '</span>' : '') +
                        '<span class="fc-event-title" style="flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">' + pacienteNombre + '</span>' +
                    '</div>' +
                    '<div class="fc-event-subtitle" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">Dr(a). ' + medicoNombre + edadHtml + '</div>' +
                    '<div style="display:flex;align-items:center;gap:3px;flex-wrap:wrap;margin-top:2px;">' + typeBadge + estadoBadge + tiempoBadge + '</div>' +
                '</div>';
            } else if (view.type === 'timeGridWeek' || view.type === 'timeGridDay') {
                html = '<div class="fc-event-main-frame" style="width:100%;">' +
                    '<div class="fc-event-title-container">' +
                        '<div class="fc-event-title">' + nombreCompleto + '</div>' +
                        '<div class="fc-event-subtitle">Dr(a). ' + medicoNombre + edadHtml + '</div>' +
                        '<div style="display:flex;align-items:center;gap:4px;flex-wrap:wrap;margin-top:2px;">' + typeBadge + estadoBadge + tiempoBadge + '</div>' +
                    '</div>' +
                '</div>';
            } else if (view.type === 'listMonth' || view.type === 'listWeek') {
                html = '<div class="fc-list-event-main-frame" style="display:flex;align-items:center;gap:8px;width:100%;">' +
                    '<div class="fc-event-title-container" style="flex:1;min-width:0;">' +
                        '<div class="fc-event-title">' + nombreCompleto + '</div>' +
                        '<div class="fc-event-subtitle">Dr(a). ' + medicoNombre + '</div>' +
                    '</div>' +
                    '<div style="display:flex;align-items:center;gap:6px;flex-shrink:0;">' + typeBadge + estadoBadge + tiempoBadge + '</div>' +
                '</div>';
            } else {
                return { html: '<div>' + arg.event.title + '</div>' };
            }

            return { html: html };
        },
        eventDidMount: function(info) {
            var ep = info.event.extendedProps||{}, tipoEvento = ep.tipo_evento||'cita', isCita = tipoEvento==='cita';
            if(isCita) info.el.classList.add('event-cita'); else info.el.classList.add('event-consulta');
            var bgHex = (isCita && ep.tipo_consulta_color) ? ep.tipo_consulta_color : (isCita ? '#0d6efd' : '#6f42c1');
            info.el.style.backgroundColor = hexToRgba(bgHex, 0.12);
            info.el.style.borderTopColor = hexToRgba(bgHex, 0.25);
            info.el.style.borderRightColor = hexToRgba(bgHex, 0.25);
            info.el.style.borderBottomColor = hexToRgba(bgHex, 0.25);
            var stateHex = getEventColor(info.event);
            info.el.style.borderLeftColor = stateHex || bgHex;
            info.el.style.borderLeftWidth = '4px';
            info.el.addEventListener('mouseenter',function(){showTooltip(info);});
            info.el.addEventListener('mouseleave',removeTooltip);
        },
        dateClick: function(info) {
            var clickedDate = info.date || new Date(info.dateStr);
            if(isPastDateTime(clickedDate)){showPastAlert();return;}
            var dateOnly = info.dateStr.substring(0,10);
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

    // ===================== EVENT DETAIL OFFCANVAS (consultas) =====================
    function showEventDetail(event) {
        var ep=event.extendedProps||{},tipoEvento=ep.tipo_evento||'cita',isCita=tipoEvento==='cita';
        var title=document.getElementById('calendarioDetailTitle'),body=document.getElementById('calendarioDetailBody');
        title.textContent=isCita?'Detalle de Cita':'Detalle de Consulta';
        var badgeColor=isCita?(citaCalendarColors[ep.estado]||'#78909C'):(consultaColores[ep.estado]||'#78909C');
        var estadoLabel=ep.estado_label||ep.estadoLabel||ep.estado||'',tipoLabel=isCita?'CITA':'CONSULTA';
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
        html+='<div class="mt-4"><a href="/admin/gestion/consultas" class="btn btn-sm btn-outline-primary w-100"><i class="ri-stethoscope-line me-1"></i> Ir a Consultas</a></div>';
        body.innerHTML=html; bsDetailSidebar.show();
    }

    // ===================== INLINE MINI CALENDAR =====================
    if (inlineCalendar) {
        inlineCalInstance = flatpickr(inlineCalendar, { inline:true, locale:'es', onChange:function(selectedDates){if(selectedDates.length) calendar.gotoDate(selectedDates[0]);} });
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
            var isNewCita = btnSubmit.classList.contains('btn-add-event');
            var eventData = {
                paciente_id: eventPaciente.val(),
                especialidad_id: selectedEspecialidadId,
                subespecialidad_id: selectedSubespecialidadId,
                medico_id: eventMedico.val(),
                start: eventStartDate ? eventStartDate.value : '',
                end: eventEndDate ? eventEndDate.value : '',
                motivo: eventMotivo ? eventMotivo.value : '',
                notas: eventNotas ? eventNotas.value : '',
                estado: isNewCita ? 'pendiente' : (eventEstado.val() || 'pendiente'),
                tipo_consulta_id: eventTipoConsulta.val() || ''
            };
            var comp = getLivewireComponent(); if(!comp) return;
            if (btnSubmit.classList.contains('btn-update-event') && eventToUpdate) {
                comp.set('citaId', getCitaId(eventToUpdate.id));
            }
            comp.call('saveCita', eventData);
            if(bsAddEventSidebar) bsAddEventSidebar.hide();
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
                horarios.forEach(function(h){var b=document.createElement('button');b.type='button';b.className='btn btn-sm btn-outline-primary rounded-pill mb-1';b.textContent='Re-agendar '+h.fecha_formateada;b.addEventListener('click',function(){comp.call('reagendarManualmenteDesdeCalendario',getCitaId(eventToUpdate.id),h.fecha_hora);if(bsAddEventSidebar) bsAddEventSidebar.hide();});slotsList.appendChild(b);});
                if(horarios.length===0){var p=document.createElement('div');p.className='text-muted small';p.textContent='Sin horarios disponibles';slotsList.appendChild(p);}
            });
        };}
        if(btnReagendarAuto){btnReagendarAuto.onclick=function(){if(!eventToUpdate) return;var comp=getLivewireComponent();if(comp){comp.call('reagendarAutomaticamenteDesdeCalendario',getCitaId(eventToUpdate.id));if(bsAddEventSidebar) bsAddEventSidebar.hide();}};}
    }

    // ===================== OFFCANVAS HIDDEN =====================
    if(addEventSidebar){addEventSidebar.addEventListener('hidden.bs.offcanvas',function(){resetValues();});}

    // ===================== LIVEWIRE LISTENERS =====================
    if(typeof Livewire!=='undefined'){Livewire.on('calendario-updated',function(){prefetchCache={};calendar.refetchEvents();});}

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