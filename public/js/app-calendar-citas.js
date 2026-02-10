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
        pendiente: 'warning',
        confirmada: 'primary',
        en_curso: 'info',
        completada: 'success',
        cancelada: 'danger',
        no_asistio: 'secondary'
    };

    let currentEvents = events || [];
    let isFormValid = false;
    let eventToUpdate = null;
    let inlineCalInstance = null;

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
        var calendars = selectedCalendars();
        var selectedEvents = currentEvents.filter(function (event) {
            var matchEstado = calendars.includes(event.extendedProps.calendar);
            var matchMedico = !selectedMedicoFilter || String(event.extendedProps.medico_id) === String(selectedMedicoFilter);
            var matchTipoConsulta = !selectedTipoConsultaFilter || String(event.extendedProps.tipo_consulta_id) === String(selectedTipoConsultaFilter);
            return matchEstado && matchMedico && matchTipoConsulta;
        });
        successCallback(selectedEvents);
        updateMedicosConCitas();
        updateTiposConsultaConCitas();
        updatePerfectScrollbars();
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
        dayMaxEvents: 2,
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
            var ep = arg.event.extendedProps;
            console.log(ep)
            var timeText = arg.timeText || '';
            var html = '';
            var menorIcon = ep.es_menor ? '<span style="font-size:0.6em;margin-left:2px;" title="Menor de edad">👶</span>' : '';
            var color = ep.tipo_consulta_color ? ep.tipo_consulta_color : '';
            var tipoTag = ep.tipo_consulta_nombre ? '<span style="font-size:0.6em;opacity:0.7;margin-left:3px;">(' + ep.tipo_consulta_nombre + ')</span>' : '';
         
            if (arg.view.type === 'listMonth') {
                html = '<div style="line-height:1.4;">' +
                    '<strong style="font-size:0.85rem;">' + arg.event.title + '' + '</strong>' + tipoTag +
                    '<br><small class="text-muted"><i class="q" style="font-size:0.65em;"></i> ' + (ep.medico || '') + '</small>' +
                    (ep.motivo ? '<br><small class="text-muted">' + ep.motivo + '</small>' : '') +
                    '</div>';
            } else if (arg.view.type === 'dayGridMonth') {
              html = '<div class="fc-event-main-frame" style="line-height:1.2; display: flex; align-items: center; gap: 15px; padding: 4px; width: 100%; overflow: visible;">' +
                // El punto ahora usa la clase y la variable, permitiendo que el CSS haga el efecto agua
                '<div class="punto-agua" style="--color-punto: ' + color + ';"></div>' +
                
                '<div class="fc-event-title-container" style="display: flex; flex-direction: column; overflow: hidden;">' +
                    '<div class="fc-event-title fc-sticky" style="font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">' 
                        + arg.event.title + 
                    '</div>' +
                    '<div class="fc-event-title fc-sticky badge bg-label-primary" style="font-size: 70%; width: fit-content; max-width: 100%; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">' 
                        + 'Dr. ' + ep.medico_full + 
                    '</div>' +
                '</div>' +
            '</div>';


            } else {
                html = '<div class="fc-event-main-frame" style="line-height:1.3;">' +
                    '<div class="fc-event-time">' + timeText + '</div>' +
                    '<div class="fc-event-title-container">' +
                        '<div class="fc-event-title fc-sticky">' + arg.event.title + '' + '</div>' +
                        '<div class="fc-event-subtitle"><i class="q" style="font-size:0.6em;"></i> ' + (ep.medico || '') + '</div>' +
                    '</div>' +
                    '</div>';
            }
            return { html: html };
        },
        eventDidMount: function(info) {
            var ep = info.event.extendedProps || {};
            if (ep.tipo_consulta_color) {
                info.el.style.borderLeftColor = ep.tipo_consulta_color;
           
            }
            var tooltipParts = [
                ep.paciente || info.event.title,
                'Dr(a). ' + (ep.medico_full || ep.medico || ''),
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