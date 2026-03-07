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
    let filtrosScrollbar = null;
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

    // ===================== PERFECT SCROLLBAR INIT =====================
    if (filtrosContainer && typeof PerfectScrollbar !== 'undefined') {
        filtrosScrollbar = new PerfectScrollbar(filtrosContainer, {
            wheelPropagation: false,
            suppressScrollX: true
        });
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
            if (filtrosScrollbar) setTimeout(function() { filtrosScrollbar.update(); }, 300);
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
    if (modalPacienteRapidoEl) {
        modalPacienteRapidoEl.addEventListener('hidden.bs.modal', function() {
            ['mpNombres','mpApellidos','mpDocumento','mpTelefono','mpNickname','mpFechaNacimiento','mpTutorNombres','mpTutorApellidos','mpTutorTelefono'].forEach(function(id){ var el = document.getElementById(id); if(el) el.value=''; });
            if(mpEsMenor) mpEsMenor.checked=false; if(mpTutorFields) mpTutorFields.style.display='none';
        });
    }
    if (modalPacienteCreateBtn) {
        modalPacienteCreateBtn.addEventListener('click', function() {
            var data = { nombres: (document.getElementById('mpNombres')||{}).value||'', apellidos: (document.getElementById('mpApellidos')||{}).value||'', documento_identidad: (document.getElementById('mpDocumento')||{}).value||'', telefono: (document.getElementById('mpTelefono')||{}).value||'', nickname: (document.getElementById('mpNickname')||{}).value||'', fecha_nacimiento: (document.getElementById('mpFechaNacimiento')||{}).value||'', es_menor: mpEsMenor ? mpEsMenor.checked : false, tutor: { nombres: (document.getElementById('mpTutorNombres')||{}).value||'', apellidos: (document.getElementById('mpTutorApellidos')||{}).value||'', telefono: (document.getElementById('mpTutorTelefono')||{}).value||'' } };
            var comp = getLivewireComponent(); if(comp) comp.call('crearPacienteRapido', data);
        });
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
                btn.className = 'btn btn-sm rounded-pill slot-btn ' + (slot.disponible ? 'btn-outline-primary' : 'btn-outline-secondary ocupado');
                btn.textContent = slot.label; btn.disabled = !slot.disponible;
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

    // ===================== FORM HELPERS =====================
    function formatDateForLivewire(date) {
        if (!date) return '';
        return date.getFullYear() + '-' + String(date.getMonth()+1).padStart(2,'0') + '-' + String(date.getDate()).padStart(2,'0') + ' ' + String(date.getHours()).padStart(2,'0') + ':' + String(date.getMinutes()).padStart(2,'0');
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
        if (btnDeleteEvent) btnDeleteEvent.classList.remove('d-none');
        if (btnSendReminder) btnSendReminder.classList.remove('d-none');

        // Create extra buttons if needed
        if (!btnConfirmEvent && btnSendReminder && btnSendReminder.parentNode) { btnConfirmEvent = document.createElement('button'); btnConfirmEvent.type='button'; btnConfirmEvent.className='btn btn-sm btn-success ms-2 btn-confirm-event'; btnConfirmEvent.textContent='Confirmar'; btnSendReminder.parentNode.insertBefore(btnConfirmEvent,btnSendReminder.nextSibling); bindExtraButtons(); }
        if (!btnCancelCita && btnSendReminder && btnSendReminder.parentNode) { btnCancelCita = document.createElement('button'); btnCancelCita.type='button'; btnCancelCita.className='btn btn-sm btn-outline-danger ms-2 btn-cancel-cita'; btnCancelCita.textContent='Cancelar Cita'; btnSendReminder.parentNode.insertBefore(btnCancelCita,btnSendReminder.nextSibling); bindExtraButtons(); }
        if (!btnReagendar && btnSendReminder && btnSendReminder.parentNode) { btnReagendar = document.createElement('button'); btnReagendar.type='button'; btnReagendar.className='btn btn-sm btn-outline-primary ms-2 btn-reagendar'; btnReagendar.textContent='Re-agendar'; btnSendReminder.parentNode.insertBefore(btnReagendar,btnSendReminder.nextSibling); bindExtraButtons(); }
        if (!btnReagendarAuto && btnSendReminder && btnSendReminder.parentNode) { btnReagendarAuto = document.createElement('button'); btnReagendarAuto.type='button'; btnReagendarAuto.className='btn btn-sm btn-outline-secondary ms-2 btn-reagendar-auto'; btnReagendarAuto.textContent='Auto'; btnSendReminder.parentNode.insertBefore(btnReagendarAuto,btnSendReminder.nextSibling); bindExtraButtons(); }

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
        var citasEnabled = toggleCitas.checked, consultasEnabled = toggleConsultas.checked;
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
        initialView: 'dayGridMonth',
        plugins: [dayGridPlugin, interactionPlugin, listPlugin, timegridPlugin],
        events: fetchEvents,
        locale: 'es',
        firstDay: 1,
        nowIndicator: true,
        dayMaxEvents: 3,
        contentHeight: 'auto',
        expandRows: false,
        editable: true,
        eventResizableFromStart: true,
        customButtons: { sidebarToggle: { text: 'Menú' } },
        headerToolbar: { start: 'sidebarToggle, prev,next, title', end: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth' },
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

            // Tipo consulta with punto-agua ripple (only for citas with tipo_consulta)
            var tipoConsultaHtml = '';
            if (ep.tipo_consulta_nombre) {
                var tipoColor = ep.tipo_consulta_color || '#6c757d';
                tipoConsultaHtml = '<div class="d-flex align-items-center gap-1" style="margin-top:1px;">' +
                    '<div class="punto-agua" style="--color-punto: ' + tipoColor + ';"></div>' +
                    '<span style="font-size:0.6rem;color:' + tipoColor + ';font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">' + ep.tipo_consulta_nombre + '</span>' +
                    '</div>';
            }

            // Edad
            var edadHtml = ep.edad ? '<span style="font-size:0.6rem;opacity:0.7;"> · ' + ep.edad + '</span>' : '';

            if (view.type === 'dayGridMonth') {
                html = '<div class="fc-event-main-frame">' +
                    '<div class="fc-event-time">' + timeText + '</div>' +
                    '<div class="fc-event-title-container">' +
                        '<div class="fc-event-title">' + nombreCompleto + '</div>' +
                        '<div class="fc-event-subtitle">Dr(a). ' + medicoNombre + edadHtml + '</div>' +
                        '<div style="display:flex;align-items:center;gap:4px;flex-wrap:wrap;margin-top:1px;">' + typeBadge + estadoBadge + tipoConsultaHtml + '</div>' +
                    '</div>' +
                '</div>';
            } else if (view.type === 'timeGridWeek' || view.type === 'timeGridDay') {
                html = '<div class="fc-event-main-frame" style="width:100%;">' +
                    '<div class="fc-event-title-container">' +
                        '<div class="fc-event-title">' + nombreCompleto + '</div>' +
                        '<div class="fc-event-subtitle">Dr(a). ' + medicoNombre + edadHtml + '</div>' +
                        '<div style="display:flex;align-items:center;gap:4px;flex-wrap:wrap;margin-top:2px;">' + typeBadge + estadoBadge + tipoConsultaHtml + '</div>' +
                    '</div>' +
                '</div>';
            } else if (view.type === 'listMonth' || view.type === 'listWeek') {
                html = '<div class="fc-list-event-main-frame" style="display:flex;align-items:center;gap:8px;width:100%;">' +
                    '<div class="fc-event-title-container" style="flex:1;min-width:0;">' +
                        '<div class="fc-event-title">' + nombreCompleto + '</div>' +
                        '<div class="fc-event-subtitle">Dr(a). ' + medicoNombre + '</div>' +
                    '</div>' +
                    '<div style="display:flex;align-items:center;gap:6px;flex-shrink:0;">' + typeBadge + estadoBadge + tipoConsultaHtml + '</div>' +
                '</div>';
            } else {
                return { html: '<div>' + arg.event.title + '</div>' };
            }

            return { html: html };
        },
        eventDidMount: function(info) {
            var ep = info.event.extendedProps||{}, tipoEvento = ep.tipo_evento||'cita', isCita = tipoEvento==='cita';
            if(isCita) info.el.classList.add('event-cita'); else info.el.classList.add('event-consulta');
            var stateHex = getEventColor(info.event);
            if (stateHex) {
                info.el.style.backgroundColor = hexToRgba(stateHex, 0.12);
                info.el.style.borderTopColor = hexToRgba(stateHex, 0.4);
                info.el.style.borderRightColor = hexToRgba(stateHex, 0.4);
                info.el.style.borderBottomColor = hexToRgba(stateHex, 0.4);
            }
            if (isCita && ep.tipo_consulta_color) {
                info.el.style.borderLeftColor = ep.tipo_consulta_color;
                info.el.style.borderLeftWidth = '4px';
            } else {
                info.el.style.borderLeftColor = isCita ? '#0d6efd' : '#6f42c1';
                info.el.style.borderLeftWidth = '4px';
            }
            info.el.addEventListener('mouseenter',function(){showTooltip(info);});
            info.el.addEventListener('mouseleave',removeTooltip);
        },
        dateClick: function(info) {
            var dateOnly = info.dateStr.substring(0,10);
            resetValues();
            if(bsAddEventSidebar) bsAddEventSidebar.show();
            if(offcanvasTitle) offcanvasTitle.innerHTML='Nueva Cita';
            if(btnSubmit){btnSubmit.innerHTML='<i class="ri ri-add-line me-1"></i> Agregar';btnSubmit.classList.remove('btn-update-event');btnSubmit.classList.add('btn-add-event');}
            if(btnDeleteEvent) btnDeleteEvent.classList.add('d-none');
            if(btnSendReminder) btnSendReminder.classList.add('d-none');
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
            var comp = getLivewireComponent(); if(!comp){info.revert();return;}
            comp.call('updateCitaFechas', getCitaId(info.event.id), formatDateForLivewire(info.event.start), info.event.end?formatDateForLivewire(info.event.end):formatDateForLivewire(info.event.start));
        },
        eventResize: function(info) {
            var ep = info.event.extendedProps||{};
            if(ep.tipo_evento!=='cita'){info.revert();return;}
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
    if(toggleCitas){toggleCitas.addEventListener('change',function(){filtrosCitas.style.display=this.checked?'':'none';if(filtrosScrollbar) filtrosScrollbar.update();prefetchCache={};calendar.refetchEvents();});}
    if(toggleConsultas){toggleConsultas.addEventListener('change',function(){filtrosConsultas.style.display=this.checked?'':'none';if(filtrosScrollbar) filtrosScrollbar.update();prefetchCache={};calendar.refetchEvents();});}
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
            var eventData = {
                paciente_id: eventPaciente.val(),
                especialidad_id: selectedEspecialidadId,
                subespecialidad_id: selectedSubespecialidadId,
                medico_id: eventMedico.val(),
                start: eventStartDate ? eventStartDate.value : '',
                end: eventEndDate ? eventEndDate.value : '',
                motivo: eventMotivo ? eventMotivo.value : '',
                notas: eventNotas ? eventNotas.value : '',
                estado: eventEstado.val() || 'pendiente',
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
    if (btnDeleteEvent) {
        btnDeleteEvent.addEventListener('click', function() {
            if (!eventToUpdate) return;
            var ep=eventToUpdate.extendedProps||{};
            var msg='¿Está seguro de eliminar esta cita?\n\nPaciente: '+(ep.paciente||eventToUpdate.title)+'\nMédico: Dr(a). '+(ep.medico_full||ep.medico||'')+'\n\nEsta acción no se puede deshacer.';
            if(!confirm(msg)) return;
            var comp=getLivewireComponent();if(comp) comp.call('deleteCita', getCitaId(eventToUpdate.id));
            if(bsAddEventSidebar) bsAddEventSidebar.hide();
        });
    }

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
