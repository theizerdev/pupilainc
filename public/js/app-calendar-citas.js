/**
 * App Calendar - Citas Médicas
 * Integración con FullCalendar y Livewire para gestión de citas médicas
 */

'use strict';

function initCitasCalendar(events) {
    const direction = document.documentElement.getAttribute('dir') === 'rtl' ? 'rtl' : 'ltr';

    const calendarEl = document.getElementById('calendar');
    const appCalendarSidebar = document.querySelector('.app-calendar-sidebar');
    const addEventSidebar = document.getElementById('addEventSidebar');
    const appOverlay = document.querySelector('.app-overlay');
    const offcanvasTitle = document.querySelector('.offcanvas-title');
    const btnToggleSidebar = document.querySelector('.btn-toggle-sidebar');
    const btnSubmit = document.getElementById('addEventBtn');
    const btnDeleteEvent = document.querySelector('.btn-delete-event');
    const btnCancel = document.querySelector('.btn-cancel');
    const btnSendReminder = document.querySelector('.btn-send-reminder');
    let btnConfirmEvent = document.querySelector('.btn-confirm-event');
    let btnCancelCita = document.querySelector('.btn-cancel-cita');
    let btnReagendar = document.querySelector('.btn-reagendar');
    let btnReagendarAuto = document.querySelector('.btn-reagendar-auto');
    const eventStartDate = document.getElementById('eventStartDate');
    const eventEndDate = document.getElementById('eventEndDate');
    const eventMotivo = document.getElementById('eventMotivo');
    const eventNotas = document.getElementById('eventNotas');
    const selectAll = document.querySelector('.select-all');
    const filterInputs = Array.from(document.querySelectorAll('.input-filter'));
   const inlineCalendar = document.querySelector('.inline-calendar');
    const medicosConCitasContainer = document.getElementById('medicos-con-citas');
    const tiposConsultaContainer = document.getElementById('tipos-consulta-con-citas');
    var selectedMedicoFilter = null;
    var selectedTipoConsultaFilter = null;
    var psMedicos = null;
    var psTiposConsulta = null;if (!calendarEl) return;

    const calendarColors = {
        pendiente: '#ffc107',
        confirmada: '#0d6efd',
        en_curso: '#17a2b8',
        sala_espera: '#fd7e14',
        completada: '#28a745',
        cancelada: '#dc3545',
        no_asistio: '#6c757d'
    };

    function hexToRgba(hex, alpha) {
        try {
            hex = hex.replace('#', '');
            var r = parseInt(hex.substring(0, 2), 16);
            var g = parseInt(hex.substring(2, 4), 16);
            var b = parseInt(hex.substring(4, 6), 16);
            return 'rgba(' + r + ',' + g + ',' + b + ',' + (alpha != null ? alpha : 1) + ')';
        } catch (e) {
            return hex;
        }
    }

    let currentEvents = events || [];
    let isFormValid = false;
    let eventToUpdate = null;
    let inlineCalInstance = null;
    let prefetchCache = {};
    let prefetchCacheKeys = [];
    const PREFETCH_CACHE_MAX = 15;

    function cacheSet(key, value) {
        if (!prefetchCache[key]) prefetchCacheKeys.push(key);
        prefetchCache[key] = value;
        while (prefetchCacheKeys.length > PREFETCH_CACHE_MAX) {
            var oldest = prefetchCacheKeys.shift();
            delete prefetchCache[oldest];
        }
    }

    const bsAddEventSidebar = addEventSidebar ? new bootstrap.Offcanvas(addEventSidebar) : null;

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

    let selectedEspecialidadId = null;
    let selectedSubespecialidadId = null;
    let fechaFlatpickr = null;
    let currentSlotDuration = 30;

    if (eventPaciente.length) {
        eventPaciente.select2({
            placeholder: 'Buscar paciente...',
            dropdownParent: eventPaciente.parent(),
            allowClear: true,
            language: {
                noResults: function() { return 'No se encontraron pacientes'; },
                searching: function() { return 'Buscando...'; }
            }
        });
    }
    // Modal Paciente Rápido
    var btnModalPacienteRapido = document.getElementById('btnModalPacienteRapido');
    var modalPacienteRapidoEl = document.getElementById('modalPacienteRapido');
    var modalPacienteRapido = modalPacienteRapidoEl ? new bootstrap.Modal(modalPacienteRapidoEl) : null;
    var mpEsMenor = document.getElementById('mpEsMenor');
    var mpTutorFields = document.getElementById('mpTutorFields');
    var modalPacienteCreateBtn = document.getElementById('modalPacienteCreateBtn');
    if (btnModalPacienteRapido) {
        btnModalPacienteRapido.addEventListener('click', function() {
            if (modalPacienteRapido) {
                try {
                    modalPacienteRapido.show();
                    return;
                } catch (e) {}
            }
            var el = document.getElementById('modalPacienteRapido');
            if (!el) return;
            var backdrop = document.createElement('div');
            backdrop.className = 'modal-backdrop fade show';
            document.body.appendChild(backdrop);
            el.classList.add('show');
            el.style.display = 'block';
            el.removeAttribute('aria-hidden');
            el.setAttribute('aria-modal', 'true');
            el.setAttribute('role', 'dialog');
            function closeFallback() {
                el.classList.remove('show');
                el.style.display = 'none';
                el.setAttribute('aria-hidden', 'true');
                el.removeAttribute('aria-modal');
                if (backdrop && backdrop.parentNode) backdrop.parentNode.removeChild(backdrop);
            }
            var closeBtn = el.querySelector('.btn-close');
            var cancelBtn = el.querySelector('.btn-outline-secondary');
            if (closeBtn) closeBtn.addEventListener('click', closeFallback, { once: true });
            if (cancelBtn) cancelBtn.addEventListener('click', closeFallback, { once: true });
        });
    }
    if (mpEsMenor && mpTutorFields) {
        mpEsMenor.addEventListener('change', function() {
            mpTutorFields.style.display = mpEsMenor.checked ? '' : 'none';
        });
    }
    if (modalPacienteCreateBtn) {
        modalPacienteCreateBtn.addEventListener('click', function() {
            var data = {
                nombres: document.getElementById('mpNombres')?.value || '',
                apellidos: document.getElementById('mpApellidos')?.value || '',
                documento_identidad: document.getElementById('mpDocumento')?.value || '',
                telefono: document.getElementById('mpTelefono')?.value || '',
                fecha_nacimiento: document.getElementById('mpFechaNacimiento')?.value || '',
                es_menor: !!(mpEsMenor && mpEsMenor.checked),
                tutor: {
                    nombres: document.getElementById('mpTutorNombres')?.value || '',
                    apellidos: document.getElementById('mpTutorApellidos')?.value || '',
                    telefono: document.getElementById('mpTutorTelefono')?.value || ''
                }
            };
            var comp = getLivewireComponent();
            if (!comp) return;
            comp.call('crearPacienteRapido', data).then(function(resp) {
                var success = resp && resp.success;
                var msg = (resp && resp.message) ? resp.message : (success ? 'Paciente creado y seleccionado' : 'No se pudo crear el paciente');
                if (eventPaciente.length && success) {
                    var id = resp.paciente_id;
                    var nombre = resp.nombre || (data.nombres + ' ' + data.apellidos);
                    var exists = eventPaciente.find('option[value="' + id + '"]').length > 0;
                    if (!exists) {
                        var opt = new Option(nombre, id, true, true);
                        eventPaciente.append(opt).trigger('change.select2');
                    } else {
                        eventPaciente.val(String(id)).trigger('change.select2');
                    }
                }
                if (modalPacienteRapido) modalPacienteRapido.hide();
                if (window.toastr) {
                    success ? toastr.success(msg) : toastr.error(msg);
                }
            });
        });
    }

    if (eventEspecialidad.length) {
        eventEspecialidad.select2({
            placeholder: 'Seleccionar especialidad...',
            dropdownParent: eventEspecialidad.parent(),
            allowClear: true,
            language: {
                noResults: function() { return 'No se encontraron especialidades'; }
            }
        });
    }

    if (eventSubespecialidad.length) {
        eventSubespecialidad.select2({
            placeholder: 'Seleccionar subespecialidad...',
            dropdownParent: eventSubespecialidad.parent(),
            allowClear: true,
            language: {
                noResults: function() { return 'No se encontraron subespecialidades'; }
            }
        });
    }

    if (eventMedico.length) {
        eventMedico.select2({
            placeholder: 'Buscar médico...',
            dropdownParent: eventMedico.parent(),
            allowClear: true,
            language: {
                noResults: function() { return 'No se encontraron médicos'; },
                searching: function() { return 'Buscando...'; }
            }
        });
    }

    if (eventEstado.length) {
        function renderEstadoBadge(option) {
            if (!option.id) return option.text;
            var color = $(option.element).data('color') || 'secondary';
            return "<span class='badge badge-dot bg-" + color + " me-2'></span>" + option.text;
        }
        eventEstado.select2({
            placeholder: 'Seleccionar estado',
            dropdownParent: eventEstado.parent(),
            templateResult: renderEstadoBadge,
            templateSelection: renderEstadoBadge,
            minimumResultsForSearch: -1,
            escapeMarkup: function(es) { return es; }
        });
    }

    if (eventTipoConsulta.length) {
        function renderTipoConsultaOption(option) {
            if (!option.id) return option.text;
            var color = $(option.element).data('color') || '#6c757d';
            return "<span style='display:inline-block;width:12px;height:12px;border-radius:3px;background:" + color + ";margin-right:8px;vertical-align:middle;'></span>" + option.text;
        }
        eventTipoConsulta.select2({
            placeholder: 'Sin tipo de consulta',
            dropdownParent: eventTipoConsulta.parent(),
            allowClear: true,
            templateResult: renderTipoConsultaOption,
            templateSelection: renderTipoConsultaOption,
            escapeMarkup: function(es) { return es; }
        });
    }

    // ===== CASCADE LOGIC =====

    function resetCascadeFrom(level) {
        if (level <= 2) {
            eventEspecialidad.val('').trigger('change.select2');
            eventEspecialidad.prop('disabled', true);
            eventEspecialidad.empty().append('<option value="">Primero seleccione un paciente</option>');
            selectedEspecialidadId = null;
        }
        if (level <= 3) {
            eventSubespecialidad.val('').trigger('change.select2');
            if (subespecialidadContainer) subespecialidadContainer.style.display = 'none';
            selectedSubespecialidadId = null;
        }
        if (level <= 4) {
            eventMedico.val('').trigger('change.select2');
            eventMedico.prop('disabled', true);
            eventMedico.empty().append('<option value="">Primero seleccione una especialidad</option>');
        }
        if (level <= 5) {
            if (slotsContainer) slotsContainer.style.display = 'none';
            if (slotsList) slotsList.innerHTML = '';
            if (slotsMessage) { slotsMessage.classList.add('d-none'); slotsMessage.textContent = ''; }
            if (eventStartDate) eventStartDate.value = '';
            if (eventEndDate) eventEndDate.value = '';
        }
    }

    function loadSlots(medicoId, dateStr, selectStartFull) {
        if (!medicoId || !dateStr) return;
        var comp = getLivewireComponent();
        if (!comp) return;

        if (slotsList) slotsList.innerHTML = '<div class="text-center w-100 py-2"><div class="spinner-border spinner-border-sm" role="status"></div></div>';
        if (slotsContainer) slotsContainer.style.display = '';
        if (slotsMessage) slotsMessage.classList.add('d-none');

        comp.call('fetchHorariosDisponibles', parseInt(medicoId), dateStr).then(function(result) {
            if (slotsList) slotsList.innerHTML = '';

            if (!result || !result.disponible) {
                if (slotsMessage) {
                    slotsMessage.textContent = result ? result.mensaje : 'No hay horarios disponibles.';
                    slotsMessage.classList.remove('d-none');
                }
                return;
            }

            if (slotsMessage) slotsMessage.classList.add('d-none');
            currentSlotDuration = result.duracion_cita || 30;

            var matched = false;
            result.slots.forEach(function(slot) {
                var btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'btn btn-sm rounded-pill slot-btn ' + (slot.disponible ? 'btn-outline-primary' : 'btn-outline-secondary ocupado');
                btn.textContent = slot.label;
                btn.disabled = !slot.disponible;

                if (slot.disponible) {
                    btn.addEventListener('click', function() {
                        slotsList.querySelectorAll('.slot-btn').forEach(function(b) { b.classList.remove('selected', 'btn-primary'); b.classList.add('btn-outline-primary'); });
                        btn.classList.add('selected', 'btn-primary');
                        btn.classList.remove('btn-outline-primary');
                        if (eventStartDate) eventStartDate.value = slot.inicio_full;
                        if (eventEndDate) eventEndDate.value = slot.fin_full;
                        if (start) start.setDate(slot.inicio_full, true, 'Y-m-d H:i');
                        if (end) end.setDate(slot.fin_full, true, 'Y-m-d H:i');
                    });
                }

                slotsList.appendChild(btn);

                if (selectStartFull && slot.inicio_full === selectStartFull) {
                    btn.classList.add('selected', 'btn-primary');
                    btn.classList.remove('btn-outline-primary');
                    matched = true;
                }
            });

            if (!matched && !selectStartFull && result.primer_disponible) {
                var firstAvailable = slotsList.querySelector('.slot-btn:not(.ocupado)');
                if (firstAvailable) firstAvailable.click();
            }

            var nextBtn = document.getElementById('btnNextSlot');
            if (!nextBtn && slotsContainer) {
                nextBtn = document.createElement('button');
                nextBtn.id = 'btnNextSlot';
                nextBtn.type = 'button';
                nextBtn.className = 'btn btn-sm btn-outline-primary ms-2';
                nextBtn.textContent = 'Siguiente disponible';
                nextBtn.addEventListener('click', function() {
                    var selected = slotsList.querySelector('.slot-btn.selected');
                    var buttons = Array.from(slotsList.querySelectorAll('.slot-btn'));
                    if (!buttons.length) return;
                    var startIndex = selected ? buttons.indexOf(selected) + 1 : 0;
                    for (var i = startIndex; i < buttons.length; i++) {
                        if (!buttons[i].classList.contains('ocupado')) {
                            buttons[i].click();
                            break;
                        }
                    }
                });
                slotsContainer.appendChild(nextBtn);
            }
        });
    }

    function loadSlotsAndSelect(medicoId, dateStr, currentStart) {
        loadSlots(medicoId, dateStr, currentStart);
    }

    // 1. Paciente → load Especialidades
    if (eventPaciente.length) {
        eventPaciente.on('change', function() {
            var pacienteId = eventPaciente.val();
            if (!pacienteId) {
                resetCascadeFrom(2);
                return;
            }
            var comp = getLivewireComponent();
            if (!comp) return;

            resetCascadeFrom(3);
            comp.call('fetchEspecialidades').then(function(especialidades) {
                eventEspecialidad.empty().append('<option value="">Seleccionar especialidad</option>');
                especialidades.forEach(function(e) {
                    eventEspecialidad.append('<option value="' + e.id + '">' + e.nombre + '</option>');
                });
                eventEspecialidad.prop('disabled', false);
                eventEspecialidad.trigger('change.select2');
            });
        });
    }

    // 2. Especialidad → load Subespecialidades + Médicos
    if (eventEspecialidad.length) {
        eventEspecialidad.on('change', function() {
            var espId = eventEspecialidad.val();
            selectedEspecialidadId = espId || null;
            if (!espId) {
                resetCascadeFrom(3);
                return;
            }
            var comp = getLivewireComponent();
            if (!comp) return;

            resetCascadeFrom(4);

            comp.call('fetchSubespecialidades', parseInt(espId)).then(function(subs) {
                if (subs && subs.length > 0) {
                    eventSubespecialidad.empty().append('<option value="">Opcional - Seleccionar subespecialidad</option>');
                    subs.forEach(function(s) {
                        eventSubespecialidad.append('<option value="' + s.id + '">' + s.nombre + '</option>');
                    });
                    if (subespecialidadContainer) subespecialidadContainer.style.display = '';
                    eventSubespecialidad.trigger('change.select2');
                } else {
                    if (subespecialidadContainer) subespecialidadContainer.style.display = 'none';
                }
            });

            comp.call('fetchMedicos', parseInt(espId), null).then(function(medicos) {
                eventMedico.empty().append('<option value="">Seleccionar médico</option>');
                medicos.forEach(function(m) {
                    eventMedico.append('<option value="' + m.id + '">' + m.nombre + '</option>');
                });
                eventMedico.prop('disabled', false);
                eventMedico.trigger('change.select2');
            });
        });
    }

    // 3. Subespecialidad → reload Médicos filtered
    if (eventSubespecialidad.length) {
        eventSubespecialidad.on('change', function() {
            var subId = eventSubespecialidad.val();
            selectedSubespecialidadId = subId || null;
            if (!selectedEspecialidadId) return;

            var comp = getLivewireComponent();
            if (!comp) return;

            resetCascadeFrom(5);
            eventMedico.prop('disabled', true);

            comp.call('fetchMedicos', parseInt(selectedEspecialidadId), subId ? parseInt(subId) : null).then(function(medicos) {
                eventMedico.empty().append('<option value="">Seleccionar médico</option>');
                medicos.forEach(function(m) {
                    eventMedico.append('<option value="' + m.id + '">' + m.nombre + '</option>');
                });
                eventMedico.prop('disabled', false);
                eventMedico.trigger('change.select2');
            });
        });
    }

    // 4. Médico → auto-load slots if date already set
    if (eventMedico.length) {
        eventMedico.on('change', function() {
            var medicoId = eventMedico.val();
            resetCascadeFrom(5);
            var currentDate = eventFecha ? eventFecha.value : null;
            if (currentDate && medicoId) {
                loadSlots(medicoId, currentDate);
            }
        });
    }

    // 5. Fecha → load Horarios
    if (eventFecha) {
        fechaFlatpickr = flatpickr(eventFecha, {
            dateFormat: 'Y-m-d',
            minDate: 'today',
            locale: typeof flatpickr !== 'undefined' && flatpickr.l10ns && flatpickr.l10ns.es ? 'es' : 'default',
            onChange: function(selectedDates, dateStr) {
                if (!dateStr) {
                    resetCascadeFrom(6);
                    return;
                }
                var medicoId = eventMedico.val();
                if (!medicoId) return;
                loadSlots(medicoId, dateStr);
            }
        });
    }

    let start, end;
    if (eventStartDate) {
        start = eventStartDate.flatpickr({
            monthSelectorType: 'static',
            static: true,
            enableTime: true,
            dateFormat: 'Y-m-d H:i',
            time_24hr: true,
            altFormat: 'Y-m-dTH:i:S',
            onReady: function (selectedDates, dateStr, instance) {
                if (instance.isMobile) {
                    instance.mobileInput.setAttribute('step', null);
                }
            }
        });
    }

    if (eventEndDate) {
        end = eventEndDate.flatpickr({
            monthSelectorType: 'static',
            static: true,
            enableTime: true,
            dateFormat: 'Y-m-d H:i',
            time_24hr: true,
            altFormat: 'Y-m-dTH:i:S',
            onReady: function (selectedDates, dateStr, instance) {
                if (instance.isMobile) {
                    instance.mobileInput.setAttribute('step', null);
                }
            }
        });
    }

    if (eventTipoConsulta.length) {
        eventTipoConsulta.on('change', function() {
            var startVal = eventStartDate ? eventStartDate.value : '';
            if (!startVal || !currentSlotDuration) return;
            var d = new Date(startVal.replace(' ', 'T'));
            if (isNaN(d.getTime())) return;
            d.setMinutes(d.getMinutes() + currentSlotDuration);
            var yyyy = d.getFullYear();
            var mm = String(d.getMonth() + 1).padStart(2, '0');
            var dd = String(d.getDate()).padStart(2, '0');
            var hh = String(d.getHours()).padStart(2, '0');
            var mi = String(d.getMinutes()).padStart(2, '0');
            var endStr = yyyy + '-' + mm + '-' + dd + ' ' + hh + ':' + mi;
            if (eventEndDate) eventEndDate.value = endStr;
            if (end) end.setDate(endStr, true, 'Y-m-d H:i');
        });
    }
    if (inlineCalendar) {
        inlineCalInstance = inlineCalendar.flatpickr({
            monthSelectorType: 'static',
            static: true,
            inline: true
        });
    }

    function getLivewireComponent() {
        var wrapper = document.getElementById('citas-calendar-component');
        if (wrapper && window.Livewire) {
            var wireId = wrapper.getAttribute('wire:id');
            if (wireId) {
                return window.Livewire.find(wireId);
            }
        }
        return null;
    }

    function modifyToggler() {
        var fcSidebarToggleButton = document.querySelector('.fc-sidebarToggle-button');
        var fcPrevButton = document.querySelector('.fc-prev-button');
        var fcNextButton = document.querySelector('.fc-next-button');
        var fcHeaderToolbar = document.querySelector('.fc-header-toolbar');

        if (fcPrevButton) fcPrevButton.classList.add('btn', 'btn-sm', 'btn-icon', 'btn-outline-secondary', 'me-2');
        if (fcNextButton) fcNextButton.classList.add('btn', 'btn-sm', 'btn-icon', 'btn-outline-secondary', 'me-4');
        if (fcHeaderToolbar) fcHeaderToolbar.classList.add('row-gap-4', 'gap-2');

        if (fcSidebarToggleButton) {
            fcSidebarToggleButton.classList.remove('fc-button-primary');
            fcSidebarToggleButton.classList.add('d-lg-none', 'd-inline-block', 'ps-0');
            while (fcSidebarToggleButton.firstChild) {
                fcSidebarToggleButton.firstChild.remove();
            }
            fcSidebarToggleButton.setAttribute('data-bs-toggle', 'sidebar');
            fcSidebarToggleButton.setAttribute('data-overlay', '');
            fcSidebarToggleButton.setAttribute('data-target', '#app-calendar-sidebar');
            fcSidebarToggleButton.insertAdjacentHTML(
                'beforeend',
                '<i class="icon-base ri ri-menu-line icon-24px text-body"></i>'
            );
        }
    }

    function selectedCalendars() {
        var selected = [];
        var checked = document.querySelectorAll('.input-filter:checked');
        checked.forEach(function(item) {
            selected.push(item.getAttribute('data-value'));
        });
        return selected;
    }

    function fetchEvents(info, successCallback) {
        var comp = getLivewireComponent();
        var key = info.startStr + '|' + info.endStr;
        if (prefetchCache[key]) {
            currentEvents = prefetchCache[key];
            var calendars = selectedCalendars();
            var filtered = currentEvents.filter(function (event) {
                var matchEstado = calendars.includes(event.extendedProps.calendar);
                var matchMedico = !selectedMedicoFilter || String(event.extendedProps.medico_id) === String(selectedMedicoFilter);
                var matchTipoConsulta = !selectedTipoConsultaFilter || String(event.extendedProps.tipo_consulta_id) === String(selectedTipoConsultaFilter);
                return matchEstado && matchMedico && matchTipoConsulta;
            });
            successCallback(filtered);
            updateMedicosConCitas();
            updateTiposConsultaConCitas();
            updatePerfectScrollbars();
        } else if (comp) {
            comp.call('fetchEventosRango', info.startStr, info.endStr).then(function(events) {
                currentEvents = Array.isArray(events) ? events : [];
                cacheSet(key, currentEvents);
                var calendars = selectedCalendars();
                var filtered = currentEvents.filter(function (event) {
                    var matchEstado = calendars.includes(event.extendedProps.calendar);
                    var matchMedico = !selectedMedicoFilter || String(event.extendedProps.medico_id) === String(selectedMedicoFilter);
                    var matchTipoConsulta = !selectedTipoConsultaFilter || String(event.extendedProps.tipo_consulta_id) === String(selectedTipoConsultaFilter);
                    return matchEstado && matchMedico && matchTipoConsulta;
                });
                successCallback(filtered);
                updateMedicosConCitas();
                updateTiposConsultaConCitas();
                updatePerfectScrollbars();
            });
        } else {
            var calendars = selectedCalendars();
            var startRange = new Date(info.startStr);
            var endRange = new Date(info.endStr);
            var selectedEvents = currentEvents.filter(function (event) {
                var evStart = new Date(event.start);
                var evEnd = new Date(event.end || event.start);
                var inRange = evStart < endRange && evEnd > startRange;
                var matchEstado = calendars.includes(event.extendedProps.calendar);
                var matchMedico = !selectedMedicoFilter || String(event.extendedProps.medico_id) === String(selectedMedicoFilter);
                var matchTipoConsulta = !selectedTipoConsultaFilter || String(event.extendedProps.tipo_consulta_id) === String(selectedTipoConsultaFilter);
                return inRange && matchEstado && matchMedico && matchTipoConsulta;
            });
            successCallback(selectedEvents);
            updateMedicosConCitas();
            updateTiposConsultaConCitas();
            updatePerfectScrollbars();
        }
    }

    function updateMedicosConCitas() {
        if (!medicosConCitasContainer) return;
        var medicoMap = {};
        var calendars = selectedCalendars();
        currentEvents.forEach(function(ev) {
            if (!calendars.includes(ev.extendedProps.calendar)) return;
            var mid = ev.extendedProps.medico_id;
            var mname = ev.extendedProps.medico_full || ev.extendedProps.medico || '';
            if (mid && mname) {
                if (!medicoMap[mid]) medicoMap[mid] = { nombre: mname, count: 0 };
                medicoMap[mid].count++;
            }
        });

        var medicos = Object.keys(medicoMap).map(function(id) {
            return { id: id, nombre: medicoMap[id].nombre, count: medicoMap[id].count };
        }).sort(function(a, b) { return b.count - a.count; });

        if (medicos.length === 0) {
            medicosConCitasContainer.innerHTML = '<small class="text-muted">Sin citas en el período</small>';
            return;
        }

        var html = '';
        if (selectedMedicoFilter) {
            html += '<div class="medico-item mb-1 text-primary" onclick="window._clearMedicoFilter()" style="cursor:pointer;font-size:0.75rem;">' +
                '<i class="fas fa-times me-1"></i> Limpiar filtro</div>';
        }
        medicos.forEach(function(m) {
            var isActive = selectedMedicoFilter && String(selectedMedicoFilter) === String(m.id);
            html += '<div class="medico-item d-flex justify-content-between align-items-center mb-1' + (isActive ? ' active' : '') + '" onclick="window._filterByMedico(\'' + m.id + '\')">' +
                '<span><i class="q me-1 text-muted" style="font-size:0.7rem;"></i>' + m.nombre + '</span>' +
                '<span class="badge bg-label-primary rounded-pill" style="font-size:0.65rem;">' + m.count + '</span>' +
                '</div>';
        });
        medicosConCitasContainer.innerHTML = html;
        
        // Inicializar o actualizar PerfectScrollbar para médicos
        if (psMedicos) {
            psMedicos.update();
        } else if (medicosConCitasContainer && typeof PerfectScrollbar !== 'undefined') {
            psMedicos = new PerfectScrollbar(medicosConCitasContainer, {
                wheelPropagation: false,
                suppressScrollX: true
            });
        }
    }

    function updateTiposConsultaConCitas() {
        if (!tiposConsultaContainer) return;
        var tipoMap = {};
        var calendars = selectedCalendars();
        currentEvents.forEach(function(ev) {
            if (!calendars.includes(ev.extendedProps.calendar)) return;
            if (selectedMedicoFilter && String(ev.extendedProps.medico_id) !== String(selectedMedicoFilter)) return;
            var tid = ev.extendedProps.tipo_consulta_id;
            var tname = ev.extendedProps.tipo_consulta_nombre || '';
            var tcolor = ev.extendedProps.tipo_consulta_color || '#6c757d';
            if (tid && tname) {
                if (!tipoMap[tid]) tipoMap[tid] = { nombre: tname, count: 0, color: tcolor };
                tipoMap[tid].count++;
            }
        });

        var tipos = Object.keys(tipoMap).map(function(id) {
            return { id: id, nombre: tipoMap[id].nombre, count: tipoMap[id].count, color: tipoMap[id].color };
        }).sort(function(a, b) { return b.count - a.count; });

        if (tipos.length === 0) {
            tiposConsultaContainer.innerHTML = '<small class="text-muted">Sin citas en el período</small>';
            return;
        }

        var html = '';
        if (selectedTipoConsultaFilter) {
            html += '<div class="tipo-consulta-item-sidebar mb-1 text-primary" onclick="window._clearTipoConsultaFilter()" style="cursor:pointer;font-size:0.75rem;">' +
                '<i class="fas fa-times me-1"></i> Limpiar filtro</div>';
        }
        tipos.forEach(function(t) {
            var isActive = selectedTipoConsultaFilter && String(selectedTipoConsultaFilter) === String(t.id);
            html += '<div class="tipo-consulta-item-sidebar d-flex justify-content-between align-items-center mb-1' + (isActive ? ' active' : '') + '" onclick="window._filterByTipoConsulta(\'' + t.id + '\')">' +
                '<div class="d-flex align-items-center">' +
                '<div class="tipo-consulta-color-bar" style="background-color:' + t.color + '"></div>' +
                '<span>' + t.nombre + '</span>' +
                '</div>' +
                '<span class="badge bg-label-primary rounded-pill" style="font-size:0.65rem;">' + t.count + '</span>' +
                '</div>';
        });
        tiposConsultaContainer.innerHTML = html;
        
        // Inicializar o actualizar PerfectScrollbar para tipos de consulta
        if (psTiposConsulta) {
            psTiposConsulta.update();
        } else if (tiposConsultaContainer && typeof PerfectScrollbar !== 'undefined') {
            psTiposConsulta = new PerfectScrollbar(tiposConsultaContainer, {
                wheelPropagation: false,
                suppressScrollX: true
            });
        }
    }

    function applyTipoConsultaColors() {
        // Aplicar colores a los eventos existentes
        document.querySelectorAll('.fc-event').forEach(function(eventEl) {
            var event = calendar.getEventById(eventEl.getAttribute('data-event-id'));
            if (event && event.extendedProps.tipo_consulta_color) {
                eventEl.style.borderLeftColor = event.extendedProps.tipo_consulta_color;
                eventEl.style.borderLeftWidth = '4px';
            }
        });
    }

    function updatePerfectScrollbars() {
        // Actualizar PerfectScrollbar después de cambios en el DOM
        setTimeout(function() {
            if (psMedicos) {
                psMedicos.update();
            }
            if (psTiposConsulta) {
                psTiposConsulta.update();
            }
        }, 100);
    }

    function resetValues() {
        if (eventStartDate) eventStartDate.value = '';
        if (eventEndDate) eventEndDate.value = '';
        if (eventMotivo) eventMotivo.value = '';
        if (eventNotas) eventNotas.value = '';
        if (eventFecha) eventFecha.value = '';
        if (fechaFlatpickr) fechaFlatpickr.clear();
        if (eventPaciente.length) eventPaciente.val('').trigger('change.select2');
        if (eventEstado.length) eventEstado.val('pendiente').trigger('change');
        if (eventTipoConsulta.length) eventTipoConsulta.val('').trigger('change.select2');
        resetCascadeFrom(2);
        eventToUpdate = null;
    }

    function eventClick(info) {
        eventToUpdate = info.event;
        info.jsEvent.preventDefault();

        if (bsAddEventSidebar) bsAddEventSidebar.show();

        if (offcanvasTitle) offcanvasTitle.innerHTML = 'Editar Cita';
        if (btnSubmit) {
            btnSubmit.innerHTML = '<i class="ri ri-save-line me-1"></i> Actualizar';
            btnSubmit.classList.add('btn-update-event');
            btnSubmit.classList.remove('btn-add-event');
        }
        if (btnDeleteEvent) btnDeleteEvent.classList.remove('d-none');
        if (btnSendReminder) btnSendReminder.classList.remove('d-none');
        if (!btnConfirmEvent && btnSendReminder && btnSendReminder.parentNode) {
            btnConfirmEvent = document.createElement('button');
            btnConfirmEvent.type = 'button';
            btnConfirmEvent.className = 'btn btn-sm btn-success ms-2 btn-confirm-event';
            btnConfirmEvent.textContent = 'Confirmar';
            btnSendReminder.parentNode.insertBefore(btnConfirmEvent, btnSendReminder.nextSibling);
        }
        if (!btnCancelCita && btnSendReminder && btnSendReminder.parentNode) {
            btnCancelCita = document.createElement('button');
            btnCancelCita.type = 'button';
            btnCancelCita.className = 'btn btn-sm btn-outline-danger ms-2 btn-cancel-cita';
            btnCancelCita.textContent = 'Cancelar';
            btnSendReminder.parentNode.insertBefore(btnCancelCita, btnSendReminder.nextSibling);
        }
        if (!btnReagendar && btnSendReminder && btnSendReminder.parentNode) {
            btnReagendar = document.createElement('button');
            btnReagendar.type = 'button';
            btnReagendar.className = 'btn btn-sm btn-outline-primary ms-2 btn-reagendar';
            btnReagendar.textContent = 'Re-agendar';
            btnSendReminder.parentNode.insertBefore(btnReagendar, btnSendReminder.nextSibling);
        }
        if (!btnReagendarAuto && btnSendReminder && btnSendReminder.parentNode) {
            btnReagendarAuto = document.createElement('button');
            btnReagendarAuto.type = 'button';
            btnReagendarAuto.className = 'btn btn-sm btn-outline-secondary ms-2 btn-reagendar-auto';
            btnReagendarAuto.textContent = 'Auto';
            btnSendReminder.parentNode.insertBefore(btnReagendarAuto, btnSendReminder.nextSibling);
        }
        if (btnConfirmEvent) btnConfirmEvent.classList.remove('d-none');
        if (btnCancelCita) btnCancelCita.classList.remove('d-none');
        if (btnReagendar) btnReagendar.classList.remove('d-none');
        if (btnReagendarAuto) btnReagendarAuto.classList.remove('d-none');

        var ep = eventToUpdate.extendedProps;

        if (eventMotivo) eventMotivo.value = ep.motivo || '';
        if (eventNotas) eventNotas.value = ep.notas || '';
        if (eventEstado.length) eventEstado.val(ep.estado || 'pendiente').trigger('change');
        if (eventTipoConsulta.length) eventTipoConsulta.val(ep.tipo_consulta_id || '').trigger('change.select2');
        if (start) start.setDate(eventToUpdate.start, true, 'Y-m-d H:i');
        if (end) {
            eventToUpdate.end !== null
                ? end.setDate(eventToUpdate.end, true, 'Y-m-d H:i')
                : end.setDate(eventToUpdate.start, true, 'Y-m-d H:i');
        }
        if (eventStartDate) eventStartDate.value = formatDateForLivewire(eventToUpdate.start);
        if (eventEndDate) eventEndDate.value = eventToUpdate.end ? formatDateForLivewire(eventToUpdate.end) : formatDateForLivewire(eventToUpdate.start);

        var eventDate = eventToUpdate.start;
        var dateOnly = eventDate.getFullYear() + '-' +
            String(eventDate.getMonth() + 1).padStart(2, '0') + '-' +
            String(eventDate.getDate()).padStart(2, '0');
        if (fechaFlatpickr) fechaFlatpickr.setDate(dateOnly, false);

        var comp = getLivewireComponent();
        if (comp && eventPaciente.length) {
            eventPaciente.val(ep.paciente_id).trigger('change.select2');

            comp.call('fetchEspecialidades').then(function(especialidades) {
                eventEspecialidad.empty().append('<option value="">Seleccionar especialidad</option>');
                especialidades.forEach(function(e) {
                    eventEspecialidad.append('<option value="' + e.id + '">' + e.nombre + '</option>');
                });
                eventEspecialidad.prop('disabled', false);

                if (ep.especialidad_id) {
                    selectedEspecialidadId = String(ep.especialidad_id);
                    eventEspecialidad.val(ep.especialidad_id).trigger('change.select2');

                    comp.call('fetchSubespecialidades', parseInt(ep.especialidad_id)).then(function(subs) {
                        if (subs && subs.length > 0) {
                            eventSubespecialidad.empty().append('<option value="">Opcional - Seleccionar subespecialidad</option>');
                            subs.forEach(function(s) {
                                eventSubespecialidad.append('<option value="' + s.id + '">' + s.nombre + '</option>');
                            });
                            if (subespecialidadContainer) subespecialidadContainer.style.display = '';
                            if (ep.subespecialidad_id) {
                                selectedSubespecialidadId = String(ep.subespecialidad_id);
                                eventSubespecialidad.val(ep.subespecialidad_id).trigger('change.select2');
                            }
                        }
                    });

                    var subIdForMedicos = ep.subespecialidad_id ? parseInt(ep.subespecialidad_id) : null;
                    comp.call('fetchMedicos', parseInt(ep.especialidad_id), subIdForMedicos).then(function(medicos) {
                        eventMedico.empty().append('<option value="">Seleccionar médico</option>');
                        medicos.forEach(function(m) {
                            eventMedico.append('<option value="' + m.id + '">' + m.nombre + '</option>');
                        });
                        eventMedico.prop('disabled', false);
                        if (ep.medico_id) {
                            eventMedico.val(ep.medico_id).trigger('change.select2');
                            if (dateOnly) {
                                var currentStart = eventStartDate ? eventStartDate.value : null;
                                loadSlotsAndSelect(parseInt(ep.medico_id), dateOnly, currentStart);
                            }
                        }
                    });
                }
            });
        }
    }

    function formatDateForLivewire(date) {
        if (!date) return '';
        return date.getFullYear() + '-' +
            String(date.getMonth() + 1).padStart(2, '0') + '-' +
            String(date.getDate()).padStart(2, '0') + ' ' +
            String(date.getHours()).padStart(2, '0') + ':' +
            String(date.getMinutes()).padStart(2, '0');
    }

    var calendar = new Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        plugins: [dayGridPlugin, interactionPlugin, listPlugin, timegridPlugin],
        events: fetchEvents,
        editable: true,
        dragScroll: true,
        dayMaxEvents: 3,
        contentHeight: 'auto',
        expandRows: false,
        eventResizableFromStart: true,
        locale: 'es',
        buttonText: {
            today: 'Hoy',
            month: 'Mes',
            week: 'Semana',
            day: 'Día',
            list: 'Lista'
        },
        customButtons: {
            sidebarToggle: { text: 'Menú' }
        },
        headerToolbar: {
            start: 'sidebarToggle, prev,next, title',
            end: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
        },
        direction: direction,
        initialDate: new Date(),
        navLinks: true,
        eventClassNames: function (arg) {
            var classes = [];
            var ep = arg.event.extendedProps;
            
            // Aplicar color del tipo de consulta como borde izquierdo
            if (ep.tipo_consulta_color) {
                classes.push('border-start');
                classes.push('tipo-consulta-' + ep.tipo_consulta_id);
            }
            
            return classes;
        },
            eventContent: function(arg) {
                const view = arg.view;
                const ep = arg.event.extendedProps;
                const timeText = arg.timeText;
                let html = '';

                // Nombre del paciente
                var pacienteNombre = ep.paciente || arg.event.title || '';
                // Doctor
                var medicoNombre = ep.medico || ep.medico_full || '';
                // Estado con color
                var estadoLabel = ep.estado_label || ep.estado || '';
                var estadoColor = calendarColors[ep.estado] || '#6c757d';
                var estadoBadge = '<span class="badge rounded-pill" style="background:' + estadoColor + ';color:#fff;font-size:0.6rem;padding:1px 6px;white-space:nowrap;">' + estadoLabel + '</span>';

                // Tipo de consulta con efecto gota (punto-agua)
                var tipoConsultaHtml = '';
                if (ep.tipo_consulta_nombre) {
                    var tipoColor = ep.tipo_consulta_color || '#6c757d';
                    tipoConsultaHtml = '<div class="d-flex align-items-center gap-1" style="margin-top:1px;">' +
                        '<div class="punto-agua" style="--color-punto: ' + tipoColor + ';"></div>' +
                        '<span style="font-size:0.6rem;color:' + tipoColor + ';font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">' + ep.tipo_consulta_nombre + '</span>' +
                        '</div>';
                }

                if (view.type === 'dayGridMonth') {
                    html = '<div class="fc-event-main-frame">' +
                        '<div class="fc-event-time">' + timeText + '</div>' +
                        '<div class="fc-event-title-container">' +
                            '<div class="fc-event-title">' + pacienteNombre + '</div>' +
                            '<div class="fc-event-subtitle">Dr(a). ' + medicoNombre + '</div>' +
                            '<div style="display:flex;align-items:center;gap:4px;flex-wrap:wrap;margin-top:1px;">' + estadoBadge + tipoConsultaHtml + '</div>' +
                        '</div>' +
                    '</div>';
                } else if (view.type === 'timeGridWeek' || view.type === 'timeGridDay') {
                    html = '<div class="fc-event-main-frame" style="width:100%;">' +
                        '<div class="fc-event-title-container">' +
                            '<div class="fc-event-title">' + pacienteNombre + '</div>' +
                            '<div class="fc-event-subtitle">Dr(a). ' + medicoNombre + '</div>' +
                            '<div style="display:flex;align-items:center;gap:4px;flex-wrap:wrap;margin-top:2px;">' + estadoBadge + tipoConsultaHtml + '</div>' +
                        '</div>' +
                    '</div>';
                } else if (view.type === 'listMonth' || view.type === 'listWeek') {
                    html = '<div class="fc-list-event-main-frame" style="display:flex;align-items:center;gap:8px;width:100%;">' +
                        '<div class="fc-event-title-container" style="flex:1;min-width:0;">' +
                            '<div class="fc-event-title">' + pacienteNombre + '</div>' +
                            '<div class="fc-event-subtitle">Dr(a). ' + medicoNombre + '</div>' +
                        '</div>' +
                        '<div style="display:flex;align-items:center;gap:6px;flex-shrink:0;">' + estadoBadge + tipoConsultaHtml + '</div>' +
                    '</div>';
                } else {
                    return { html: '<div>' + arg.event.title + '</div>' };
                }

                return { html: html };
            },
        eventDidMount: function(info) {
            var ep = info.event.extendedProps || {};
            var stateHex = calendarColors[ep.estado] || null;
            if (stateHex) {
                info.el.style.backgroundColor = hexToRgba(stateHex, 0.12);
                info.el.style.borderTopColor = hexToRgba(stateHex, 0.4);
                info.el.style.borderRightColor = hexToRgba(stateHex, 0.4);
                info.el.style.borderBottomColor = hexToRgba(stateHex, 0.4);
            }
            if (ep.tipo_consulta_color) {
                info.el.style.borderLeftColor = ep.tipo_consulta_color;
                info.el.style.borderLeftWidth = '4px';
            }
            var startDate = info.event.start;
            var dateStr = startDate ? startDate.toLocaleDateString('es-VE', { weekday: 'short', day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' }) : '';
            var tooltipParts = [
                ep.paciente || info.event.title,
                'Dr(a). ' + (ep.medico_full || ep.medico || ''),
                'Fecha: ' + dateStr,
                'Estado: ' + (ep.estado_label || ep.estado || ''),
            ];
            if (ep.tipo_consulta_nombre) tooltipParts.push('Tipo: ' + ep.tipo_consulta_nombre);
            if (ep.motivo) tooltipParts.push('Motivo: ' + ep.motivo);
            info.el.title = tooltipParts.join('\n');
        },
        dateClick: function (info) {
            var dateOnly = info.dateStr.substring(0, 10);
            resetValues();
            if (bsAddEventSidebar) bsAddEventSidebar.show();

            if (offcanvasTitle) offcanvasTitle.innerHTML = 'Nueva Cita';
            if (btnSubmit) {
                btnSubmit.innerHTML = '<i class="ri ri-add-line me-1"></i> Agregar';
                btnSubmit.classList.remove('btn-update-event');
                btnSubmit.classList.add('btn-add-event');
            }
            if (btnDeleteEvent) btnDeleteEvent.classList.add('d-none');
            if (btnSendReminder) btnSendReminder.classList.add('d-none');
            if (btnConfirmEvent) btnConfirmEvent.classList.add('d-none');
            if (btnCancelCita) btnCancelCita.classList.add('d-none');
            if (btnReagendar) btnReagendar.classList.add('d-none');
            if (btnReagendarAuto) btnReagendarAuto.classList.add('d-none');

            if (fechaFlatpickr) fechaFlatpickr.setDate(dateOnly, false);
        },
        eventClick: eventClick,
        eventDrop: function(info) {
            var comp = getLivewireComponent();
            if (comp) {
                var startStr = formatDateForLivewire(info.event.start);
                var endStr = info.event.end ? formatDateForLivewire(info.event.end) : startStr;
                comp.call('updateCitaFechas', parseInt(info.event.id), startStr, endStr);
            }
        },
        eventResize: function(info) {
            var comp = getLivewireComponent();
            if (comp) {
                var startStr = formatDateForLivewire(info.event.start);
                var endStr = info.event.end ? formatDateForLivewire(info.event.end) : startStr;
                comp.call('updateCitaFechas', parseInt(info.event.id), startStr, endStr);
            }
        },
        datesSet: function() { 
            modifyToggler(); 
            updateMedicosConCitas(); 
            updateTiposConsultaConCitas(); 
            updatePerfectScrollbars(); 
            var comp = getLivewireComponent();
            if (comp) {
                var view = calendar.view;
                var currStart = new Date(view.activeStart);
                var currEnd = new Date(view.activeEnd);
                var rangeMs = currEnd.getTime() - currStart.getTime();
                var prevStart = new Date(currStart.getTime() - rangeMs);
                var prevEnd = new Date(currEnd.getTime() - rangeMs);
                var nextStart = new Date(currStart.getTime() + rangeMs);
                var nextEnd = new Date(currEnd.getTime() + rangeMs);
                var fmt = function(d) { return d.toISOString(); };
                var prevKey = fmt(prevStart) + '|' + fmt(prevEnd);
                var nextKey = fmt(nextStart) + '|' + fmt(nextEnd);
                if (!prefetchCache[prevKey]) {
                    comp.call('fetchEventosRango', fmt(prevStart), fmt(prevEnd)).then(function(events) {
                        cacheSet(prevKey, Array.isArray(events) ? events : []);
                    });
                }
                if (!prefetchCache[nextKey]) {
                    comp.call('fetchEventosRango', fmt(nextStart), fmt(nextEnd)).then(function(events) {
                        cacheSet(nextKey, Array.isArray(events) ? events : []);
                    });
                }
            }
        },
        viewDidMount: modifyToggler
    });

    calendar.render();
    modifyToggler();
    applyTipoConsultaColors();
    
    // Inicializar PerfectScrollbar para los contenedores del sidebar
    if (medicosConCitasContainer && typeof PerfectScrollbar !== 'undefined') {
        psMedicos = new PerfectScrollbar(medicosConCitasContainer, {
            wheelPropagation: false,
            suppressScrollX: true
        });
    }
    
    if (tiposConsultaContainer && typeof PerfectScrollbar !== 'undefined') {
        psTiposConsulta = new PerfectScrollbar(tiposConsultaContainer, {
            wheelPropagation: false,
            suppressScrollX: true
        });
    }

    var eventForm = document.getElementById('eventForm');
    if (eventForm && typeof FormValidation !== 'undefined') {
        var fv = FormValidation.formValidation(eventForm, {
            fields: {
                eventPaciente: {
                    validators: {
                        notEmpty: { message: 'Seleccione un paciente' }
                    }
                },
                eventEspecialidad: {
                    validators: {
                        notEmpty: { message: 'Seleccione una especialidad' }
                    }
                },
                eventMedico: {
                    validators: {
                        notEmpty: { message: 'Seleccione un médico' }
                    }
                },
                eventStartDate: {
                    validators: {
                        notEmpty: { message: 'Seleccione un horario' }
                    }
                },
                eventMotivo: {
                    validators: {
                        notEmpty: { message: 'Ingrese el motivo de la cita' }
                    }
                }
            },
            plugins: {
                trigger: new FormValidation.plugins.Trigger(),
                bootstrap5: new FormValidation.plugins.Bootstrap5({
                    eleValidClass: '',
                    rowSelector: function (field, ele) {
                        return '.form-control-validation';
                    }
                }),
                submitButton: new FormValidation.plugins.SubmitButton(),
                autoFocus: new FormValidation.plugins.AutoFocus()
            }
        })
        .on('core.form.valid', function () { isFormValid = true; })
        .on('core.form.invalid', function () { isFormValid = false; });

        if (eventPaciente.length) eventPaciente.on('change', function() { fv.revalidateField('eventPaciente'); });
        if (eventEspecialidad.length) eventEspecialidad.on('change', function() { fv.revalidateField('eventEspecialidad'); });
        if (eventMedico.length) eventMedico.on('change', function() { fv.revalidateField('eventMedico'); });
    }

    if (btnToggleSidebar) {
        btnToggleSidebar.addEventListener('click', function (e) {
            if (btnCancel) btnCancel.classList.remove('d-none');
        });
    }

    if (btnSubmit) {
        btnSubmit.addEventListener('click', function (e) {
            if (btnSubmit.classList.contains('btn-add-event')) {
                if (isFormValid) {
                    var eventData = {
                        paciente_id: eventPaciente.val(),
                        especialidad_id: selectedEspecialidadId,
                        subespecialidad_id: selectedSubespecialidadId,
                        medico_id: eventMedico.val(),
                        start: eventStartDate.value,
                        end: eventEndDate.value,
                        motivo: eventMotivo ? eventMotivo.value : '',
                        notas: eventNotas ? eventNotas.value : '',
                        estado: eventEstado.val() || 'pendiente',
                        tipo_consulta_id: eventTipoConsulta.val() || ''
                    };

                    var comp = getLivewireComponent();
                    if (comp) {
                        comp.call('saveCita', eventData);
                    }
                    if (bsAddEventSidebar) bsAddEventSidebar.hide();
                }
            } else {
                if (isFormValid) {
                    var eventData = {
                        id: eventToUpdate ? eventToUpdate.id : null,
                        paciente_id: eventPaciente.val(),
                        especialidad_id: selectedEspecialidadId,
                        subespecialidad_id: selectedSubespecialidadId,
                        medico_id: eventMedico.val(),
                        start: eventStartDate.value,
                        end: eventEndDate.value,
                        motivo: eventMotivo ? eventMotivo.value : '',
                        notas: eventNotas ? eventNotas.value : '',
                        estado: eventEstado.val() || 'pendiente',
                        tipo_consulta_id: eventTipoConsulta.val() || ''
                    };

                    var comp = getLivewireComponent();
                    if (comp) {
                        comp.set('citaId', eventToUpdate ? parseInt(eventToUpdate.id) : null);
                        comp.call('saveCita', eventData);
                    }
                    if (bsAddEventSidebar) bsAddEventSidebar.hide();
                }
            }
        });
    }

    if (btnDeleteEvent) {
        btnDeleteEvent.addEventListener('click', function (e) {
            if (eventToUpdate) {
                var ep = eventToUpdate.extendedProps || {};
                var paciente = ep.paciente || eventToUpdate.title || '';
                var medico = ep.medico_full || ep.medico || '';
                var fecha = eventToUpdate.start ? eventToUpdate.start.toLocaleDateString('es-VE', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : '';
                var msg = '¿Está seguro de eliminar esta cita?\n\nPaciente: ' + paciente + '\nMédico: Dr(a). ' + medico + '\nFecha: ' + fecha + '\n\nEsta acción no se puede deshacer.';
                if (!confirm(msg)) return;
                var comp = getLivewireComponent();
                if (comp) {
                    comp.call('deleteCita', parseInt(eventToUpdate.id));
                }
            }
            if (bsAddEventSidebar) bsAddEventSidebar.hide();
        });
    }

    if (btnSendReminder) {
        btnSendReminder.addEventListener('click', function(e) {
            if (eventToUpdate) {
                var comp = getLivewireComponent();
                if (comp) {
                    comp.call('enviarRecordatorio', parseInt(eventToUpdate.id));
                }
            }
        });
    }

    if (btnConfirmEvent) {
        btnConfirmEvent.addEventListener('click', function() {
            if (!eventToUpdate) return;
            var comp = getLivewireComponent();
            if (comp) {
                comp.call('cambiarEstado', parseInt(eventToUpdate.id), 'confirmada');
            }
        });
    }

    if (btnCancelCita) {
        btnCancelCita.addEventListener('click', function() {
            if (!eventToUpdate) return;
            var comp = getLivewireComponent();
            if (comp) {
                comp.call('cambiarEstado', parseInt(eventToUpdate.id), 'cancelada');
            }
        });
    }
    if (btnReagendar) {
        btnReagendar.addEventListener('click', function() {
            if (!eventToUpdate) return;
            var comp = getLivewireComponent();
            if (!comp) return;
            comp.call('sugerirReagendamiento', parseInt(eventToUpdate.id)).then(function(horarios) {
                if (!Array.isArray(horarios)) return;
                if (slotsList) {
                    slotsList.innerHTML = '';
                    horarios.forEach(function(h) {
                        var b = document.createElement('button');
                        b.type = 'button';
                        b.className = 'btn btn-sm btn-outline-primary rounded-pill mb-1';
                        b.textContent = 'Re-agendar ' + h.fecha_formateada;
                        b.addEventListener('click', function() {
                            comp.call('reagendarManualmenteDesdeCalendario', parseInt(eventToUpdate.id), h.fecha_hora);
                            if (bsAddEventSidebar) bsAddEventSidebar.hide();
                        });
                        slotsList.appendChild(b);
                    });
                    if (horarios.length === 0) {
                        var p = document.createElement('div');
                        p.className = 'text-muted small';
                        p.textContent = 'Sin horarios disponibles en los próximos días';
                        slotsList.appendChild(p);
                    }
                }
            });
        });
    }
    if (btnReagendarAuto) {
        btnReagendarAuto.addEventListener('click', function() {
            if (!eventToUpdate) return;
            var comp = getLivewireComponent();
            if (comp) {
                comp.call('reagendarAutomaticamenteDesdeCalendario', parseInt(eventToUpdate.id));
                if (bsAddEventSidebar) bsAddEventSidebar.hide();
            }
        });
    }

    if (addEventSidebar) {
        addEventSidebar.addEventListener('hidden.bs.offcanvas', function () {
            resetValues();
        });
    }

    if (btnToggleSidebar) {
        btnToggleSidebar.addEventListener('click', function (e) {
            if (offcanvasTitle) offcanvasTitle.innerHTML = 'Nueva Cita';
            if (btnSubmit) {
                btnSubmit.innerHTML = '<i class="ri ri-add-line me-1"></i> Agregar';
                btnSubmit.classList.remove('btn-update-event');
                btnSubmit.classList.add('btn-add-event');
            }
            if (btnDeleteEvent) btnDeleteEvent.classList.add('d-none');
            if (btnSendReminder) btnSendReminder.classList.add('d-none');
            if (btnConfirmEvent) btnConfirmEvent.classList.add('d-none');
            if (btnCancelCita) btnCancelCita.classList.add('d-none');
            if (btnReagendar) btnReagendar.classList.add('d-none');
            if (btnReagendarAuto) btnReagendarAuto.classList.add('d-none');
            if (appCalendarSidebar) appCalendarSidebar.classList.remove('show');
            if (appOverlay) appOverlay.classList.remove('show');
        });
    }

    if (selectAll) {
        selectAll.addEventListener('click', function (e) {
            var checked = e.currentTarget.checked;
            document.querySelectorAll('.input-filter').forEach(function (c) { c.checked = checked; });
            calendar.refetchEvents();
        });
    }

    if (filterInputs) {
        filterInputs.forEach(function (item) {
            item.addEventListener('click', function () {
                var checkedCount = document.querySelectorAll('.input-filter:checked').length;
                var totalCount = document.querySelectorAll('.input-filter').length;
                if (selectAll) selectAll.checked = checkedCount === totalCount;
                calendar.refetchEvents();
            });
        });
    }

    if (inlineCalInstance) {
        inlineCalInstance.config.onChange.push(function (date) {
            calendar.changeView(calendar.view.type, moment(date[0]).format('YYYY-MM-DD'));
            modifyToggler();
            if (appCalendarSidebar) appCalendarSidebar.classList.remove('show');
            if (appOverlay) appOverlay.classList.remove('show');
        });
    }

    window.addEventListener('cita-saved', function () {
        prefetchCache = {};
        prefetchCacheKeys = [];
        var comp = getLivewireComponent();
        if (comp) {
            comp.call('getEventosFresh').then(function(freshEvents) {
                currentEvents = freshEvents;
                calendar.refetchEvents();
            });
        }
    });

    window.addEventListener('show-toast', function (event) {
        var data = event.detail;
        if (Array.isArray(data) && data.length > 0) data = data[0];
        if (data && data.type && data.message && typeof window.showToast === 'function') {
            window.showToast(data.type, data.message, data.duration || 5000);
        }
    });

    window._filterByMedico = function(medicoId) {
        selectedMedicoFilter = (selectedMedicoFilter === medicoId) ? null : medicoId;
        calendar.refetchEvents();
        setTimeout(function() {
            applyTipoConsultaColors();
            updatePerfectScrollbars();
        }, 100);
    };

    window._clearMedicoFilter = function() {
        selectedMedicoFilter = null;
        calendar.refetchEvents();
        setTimeout(function() {
            applyTipoConsultaColors();
            updatePerfectScrollbars();
        }, 100);
    };

    window._filterByTipoConsulta = function(tipoConsultaId) {
        selectedTipoConsultaFilter = (selectedTipoConsultaFilter === tipoConsultaId) ? null : tipoConsultaId;
        calendar.refetchEvents();
        setTimeout(function() {
            applyTipoConsultaColors();
            updatePerfectScrollbars();
        }, 100);
    };

    window._clearTipoConsultaFilter = function() {
        selectedTipoConsultaFilter = null;
        calendar.refetchEvents();
        setTimeout(function() {
            applyTipoConsultaColors();
            updatePerfectScrollbars();
        }, 100);
    };
}