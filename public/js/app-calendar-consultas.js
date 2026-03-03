/**
 * App Calendar - Gestión de Consultas
 * Integración con FullCalendar y Livewire para gestión de consultas médicas
 */

'use strict';

function initConsultasCalendar(events, estadoColores) {
    const direction = document.documentElement.getAttribute('dir') === 'rtl' ? 'rtl' : 'ltr';

    const calendarEl = document.getElementById('calendar');
    const appCalendarSidebar = document.querySelector('.app-calendar-sidebar');
    const appOverlay = document.querySelector('.app-overlay');
    const selectAll = document.querySelector('.select-all');
    const filterInputs = Array.from(document.querySelectorAll('.input-filter'));
    const inlineCalendar = document.querySelector('.inline-calendar');
    const medicosConCitasContainer = document.getElementById('medicos-con-consultas');

    var selectedMedicoFilter = null;
    var psMedicos = null;

    if (!calendarEl) return;

    const calendarColors = estadoColores || {};

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
    let inlineCalInstance = null;
    let prefetchCache = {};

    // Inline mini calendar
    if (inlineCalendar) {
        inlineCalInstance = inlineCalendar.flatpickr({
            monthSelectorType: 'static',
            static: true,
            inline: true
        });
    }

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
        if (hours < 24) {
            return hours + 'h ' + (mins > 0 ? mins + 'min' : '');
        }
        var days = Math.floor(hours / 24);
        return days + 'd ' + (hours % 24) + 'h';
    }

    function getLivewireComponent() {
        var wrapper = document.getElementById('consultas-calendar-component');
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
                return matchEstado && matchMedico;
            });
            successCallback(filtered);
            updateMedicosConConsultas();
        } else if (comp) {
            comp.call('fetchEventosRango', info.startStr, info.endStr).then(function(events) {
                currentEvents = Array.isArray(events) ? events : [];
                prefetchCache[key] = currentEvents;
                var calendars = selectedCalendars();
                var filtered = currentEvents.filter(function (event) {
                    var matchEstado = calendars.includes(event.extendedProps.calendar);
                    var matchMedico = !selectedMedicoFilter || String(event.extendedProps.medico_id) === String(selectedMedicoFilter);
                    return matchEstado && matchMedico;
                });
                successCallback(filtered);
                updateMedicosConConsultas();
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
                return inRange && matchEstado && matchMedico;
            });
            successCallback(selectedEvents);
            updateMedicosConConsultas();
        }
    }

    function updateMedicosConConsultas() {
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
            medicosConCitasContainer.innerHTML = '<small class="text-muted">Sin consultas en el período</small>';
            return;
        }

        var html = '';
        if (selectedMedicoFilter) {
            html += '<div class="medico-item mb-1 text-primary" onclick="window._clearMedicoFilterConsultas()" style="cursor:pointer;font-size:0.75rem;">' +
                '<i class="ri ri-close-line me-1"></i> Limpiar filtro</div>';
        }
        medicos.forEach(function(m) {
            var isActive = selectedMedicoFilter && String(selectedMedicoFilter) === String(m.id);
            html += '<div class="medico-item d-flex justify-content-between align-items-center mb-1' + (isActive ? ' active' : '') + '" onclick="window._filterByMedicoConsultas(\'' + m.id + '\')">' +
                '<span>' + m.nombre + '</span>' +
                '<span class="badge bg-label-primary rounded-pill" style="font-size:0.65rem;">' + m.count + '</span>' +
                '</div>';
        });
        medicosConCitasContainer.innerHTML = html;

        if (psMedicos) {
            psMedicos.update();
        } else if (medicosConCitasContainer && typeof PerfectScrollbar !== 'undefined') {
            psMedicos = new PerfectScrollbar(medicosConCitasContainer, {
                wheelPropagation: false,
                suppressScrollX: true
            });
        }
    }

    // Detail offcanvas
    const detailSidebarEl = document.getElementById('consultaDetailSidebar');
    const bsDetailSidebar = detailSidebarEl ? new bootstrap.Offcanvas(detailSidebarEl) : null;

    function eventClick(info) {
        var event = info.event;
        var props = event.extendedProps;
        info.jsEvent.preventDefault();

        if (!bsDetailSidebar) return;

        document.getElementById('consultaDetailTitle').textContent = 'Consulta #' + props.codigo;

        var estadoHex = calendarColors[props.estado] || '#78909C';
        var estadosSelect = '';
        var allEstados = document.querySelectorAll('.input-filter');
        allEstados.forEach(function(input) {
            var val = input.getAttribute('data-value');
            var label = input.closest('.form-check').querySelector('.form-check-label').textContent.trim();
            estadosSelect += '<option value="' + val + '"' + (props.estado === val ? ' selected' : '') + '>' + label + '</option>';
        });

        var tiempoEnEstado = formatTiempoEstado(props.estado_changed_at);

        document.getElementById('consultaDetailBody').innerHTML =
            '<div class="mb-3">' +
                '<label class="form-label fw-bold">Paciente</label>' +
                '<p>' + props.paciente + '</p>' +
            '</div>' +
            '<div class="mb-3">' +
                '<label class="form-label fw-bold">Médico</label>' +
                '<p>' + props.medico + '</p>' +
            '</div>' +
            '<div class="mb-3">' +
                '<label class="form-label fw-bold">Especialidad</label>' +
                '<p>' + props.especialidad + '</p>' +
            '</div>' +
            '<div class="mb-3">' +
                '<label class="form-label fw-bold">Estado</label>' +
                '<p><span class="badge" style="background-color:' + estadoHex + '">' + props.estadoLabel + '</span>' +
                (tiempoEnEstado ? ' <span class="text-muted ms-2" style="font-size:0.8rem;"><i class="ri ri-time-line"></i> ' + tiempoEnEstado + '</span>' : '') +
                '</p>' +
            '</div>' +
            '<div class="mb-3">' +
                '<label class="form-label fw-bold">Motivo</label>' +
                '<p>' + (props.motivo || 'Sin motivo') + '</p>' +
            '</div>' +
            '<div class="mb-3">' +
                '<label class="form-label fw-bold">Preconsulta</label>' +
                '<p>' + (props.preconsulta ? 'Sí' : 'No') + '</p>' +
            '</div>' +
            '<hr/>' +
            '<div class="mb-3">' +
                '<label class="form-label fw-bold">Cambiar Estado</label>' +
                '<select class="form-select" id="cambiarEstado">' +
                    '<option value="">Seleccionar...</option>' +
                    estadosSelect +
                '</select>' +
            '</div>' +
            '<button class="btn btn-primary w-100" id="btnActualizarEstado">Actualizar Estado</button>';

        document.getElementById('btnActualizarEstado').addEventListener('click', function() {
            var nuevoEstado = document.getElementById('cambiarEstado').value;
            if (!nuevoEstado) {
                alert('Seleccione un estado');
                return;
            }
            var comp = getLivewireComponent();
            if (comp) {
                comp.call('cambiarEstado', parseInt(event.id), nuevoEstado).then(function() {
                    bsDetailSidebar.hide();
                    prefetchCache = {};
                    calendar.refetchEvents();
                });
            }
        });

        bsDetailSidebar.show();
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
            
            // Aplicar color del estado como estilo
            if (ep.estado && calendarColors[ep.estado]) {
                classes.push('estado-' + ep.estado.replace('_', '-'));
            }
            
            return classes;
        },
        eventContent: function(arg) {
            const view = arg.view;
            const ep = arg.event.extendedProps;
            const timeText = arg.timeText || '';
            let html = '';

            // Nombre del paciente con nickname entre paréntesis si está disponible
            var pacienteNombre = ep.paciente || arg.event.title || '';
            if (ep.nickname) {
                pacienteNombre = pacienteNombre + ' (' + ep.nickname + ')';
            }
            // Doctor
            var medicoNombre = ep.medico || ep.medico_full || '';
            // Estado con color
            var estadoLabel = ep.estado_label || ep.estado || '';
            var estadoColor = calendarColors[ep.estado] || '#78909C';
            var estadoBadge = '<span class="badge rounded-pill" style="background:' + estadoColor + ';color:#fff;font-size:0.6rem;padding:1px 6px;white-space:nowrap;">' + estadoLabel + '</span>';

            // Tiempo en estado actual
            var tiempoEstado = formatTiempoEstado(ep.estado_changed_at);
            var tiempoBadge = tiempoEstado
                ? '<span style="background:rgba(0,0,0,0.08);color:#555;border-radius:4px;padding:1px 5px;font-size:65%;margin-left:4px;" title="Tiempo en estado actual"><i class="ri ri-time-line" style="font-size:0.6rem;vertical-align:middle;margin-right:2px;"></i>' + tiempoEstado + '</span>'
                : '';

            if (view.type === 'dayGridMonth') {
                html = '<div class="fc-event-main-frame">' +
                    '<div class="fc-event-time">' + timeText + '</div>' +
                    '<div class="fc-event-title-container">' +
                        '<div class="fc-event-title">' + pacienteNombre + '</div>' +
                        '<div class="fc-event-subtitle">Dr(a). ' + medicoNombre + '</div>' +
                        '<div style="display:flex;align-items:center;gap:4px;flex-wrap:wrap;margin-top:1px;">' + estadoBadge + tiempoBadge + '</div>' +
                    '</div>' +
                '</div>';
            } else if (view.type === 'timeGridWeek' || view.type === 'timeGridDay') {
                html = '<div class="fc-event-main-frame" style="width:100%;">' +
                    '<div class="fc-event-title-container">' +
                        '<div class="fc-event-title">' + pacienteNombre + '</div>' +
                        '<div class="fc-event-subtitle">Dr(a). ' + medicoNombre + '</div>' +
                        '<div style="display:flex;align-items:center;gap:4px;flex-wrap:wrap;margin-top:2px;">' + estadoBadge + tiempoBadge + '</div>' +
                    '</div>' +
                '</div>';
            } else if (view.type === 'listMonth' || view.type === 'listWeek') {
                html = '<div class="fc-list-event-main-frame" style="display:flex;align-items:center;gap:8px;width:100%;">' +
                    '<div class="fc-event-title-container" style="flex:1;min-width:0;">' +
                        '<div class="fc-event-title">' + pacienteNombre + '</div>' +
                        '<div class="fc-event-subtitle">Dr(a). ' + medicoNombre + '</div>' +
                    '</div>' +
                    '<div style="display:flex;align-items:center;gap:6px;flex-shrink:0;">' + estadoBadge + tiempoBadge + '</div>' +
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
            var startDate = info.event.start;
            var dateStr = startDate ? startDate.toLocaleDateString('es-VE', { weekday: 'short', day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' }) : '';
            var tooltipParts = [
                ep.paciente || info.event.title,
                ep.nickname ? 'Cómo le gusta que le digan: ' + ep.nickname : '',
                'Dr(a). ' + (ep.medico_full || ep.medico || ''),
                'Fecha: ' + dateStr,
                'Estado: ' + (ep.estado_label || ep.estado || ''),
            ];
            var tt = formatTiempoEstado(ep.estado_changed_at);
            if (tt) tooltipParts.push('Tiempo en estado: ' + tt);
            if (ep.motivo) tooltipParts.push('Motivo: ' + ep.motivo);
            info.el.title = tooltipParts.join('\n');
        },
        eventClick: eventClick,
        datesSet: function() {
            modifyToggler();
            updateMedicosConConsultas();
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
                        prefetchCache[prevKey] = Array.isArray(events) ? events : [];
                    });
                }
                if (!prefetchCache[nextKey]) {
                    comp.call('fetchEventosRango', fmt(nextStart), fmt(nextEnd)).then(function(events) {
                        prefetchCache[nextKey] = Array.isArray(events) ? events : [];
                    });
                }
            }
        },
        viewDidMount: modifyToggler
    });

    calendar.render();
    modifyToggler();

    // Initialize PerfectScrollbar
    if (medicosConCitasContainer && typeof PerfectScrollbar !== 'undefined') {
        psMedicos = new PerfectScrollbar(medicosConCitasContainer, {
            wheelPropagation: false,
            suppressScrollX: true
        });
    }

    // Sidebar filter: Select All
    if (selectAll) {
        selectAll.addEventListener('click', function (e) {
            var checked = e.currentTarget.checked;
            document.querySelectorAll('.input-filter').forEach(function (c) { c.checked = checked; });
            prefetchCache = {};
            calendar.refetchEvents();
        });
    }

    // Sidebar filter: Individual estado checkboxes
    if (filterInputs) {
        filterInputs.forEach(function (item) {
            item.addEventListener('click', function () {
                var checkedCount = document.querySelectorAll('.input-filter:checked').length;
                var totalCount = document.querySelectorAll('.input-filter').length;
                if (selectAll) selectAll.checked = checkedCount === totalCount;
                prefetchCache = {};
                calendar.refetchEvents();
            });
        });
    }

    // Inline calendar navigation
    if (inlineCalInstance) {
        inlineCalInstance.config.onChange.push(function (date) {
            calendar.changeView(calendar.view.type, moment(date[0]).format('YYYY-MM-DD'));
            modifyToggler();
            if (appCalendarSidebar) appCalendarSidebar.classList.remove('show');
            if (appOverlay) appOverlay.classList.remove('show');
        });
    }

    // Livewire events
    window.addEventListener('consulta-saved', function () {
        var comp = getLivewireComponent();
        if (comp) {
            comp.call('getEventosFresh').then(function(freshEvents) {
                currentEvents = freshEvents;
                prefetchCache = {};
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

    // Medico filter global functions
    window._filterByMedicoConsultas = function(medicoId) {
        selectedMedicoFilter = (selectedMedicoFilter === medicoId) ? null : medicoId;
        prefetchCache = {};
        calendar.refetchEvents();
    };

    window._clearMedicoFilterConsultas = function() {
        selectedMedicoFilter = null;
        prefetchCache = {};
        calendar.refetchEvents();
    };
}
