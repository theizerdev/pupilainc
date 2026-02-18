<div id="consultas-calendar-component">
    @push('styles')
    <link rel="stylesheet" href="/materialize/assets/vendor/libs/fullcalendar/fullcalendar.css" />
    <link rel="stylesheet" href="/materialize/assets/vendor/libs/flatpickr/flatpickr.css" />
    <link rel="stylesheet" href="/materialize/assets/vendor/css/pages/app-calendar.css" />
    <style>
        .fc .fc-event {
            border-radius: 4px !important;
            border-left: 4px solid transparent !important;
            transition: box-shadow 0.15s ease;
        }
        .fc .fc-event:hover {
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
            z-index: 10;
        }
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
        .fc .fc-list-event-title {
            font-size: 0.85rem;
        }
        .calendar-legend { font-size: 0.8rem; }
        .legend-dot { width: 10px; height: 10px; border-radius: 50%; display: inline-block; }
        .legend-bar { width: 4px; height: 16px; border-radius: 2px; display: inline-block; }

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

        .punto-agua {
            position: relative;
            width: 10px;
            height: 10px;
            flex-shrink: 0;
            margin-left: 5px;
        }
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
            100% { transform: scale(3.5); opacity: 0; }
        }

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
        .fc-event-title-container {
            min-width: 0;
            flex: 1;
        }

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
    </style>
    @endpush

    <div class="card app-calendar-wrapper" wire:ignore>
        <div class="row g-0">
            <!-- Calendar Sidebar -->
            <div class="col app-calendar-sidebar border-end" id="app-calendar-sidebar">
                <div class="px-4">
                    <!-- Stats -->
                    <div class="border-bottom my-sm-0 mb-4 p-5">
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="text-center">
                                    <h4 class="mb-0">{{ $stats['total_hoy'] }}</h4>
                                    <small class="text-muted">Hoy</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center">
                                    <h4 class="mb-0">{{ $stats['sala_espera'] }}</h4>
                                    <small class="text-muted">En Espera</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center">
                                    <h4 class="mb-0">{{ $stats['en_consultorio'] }}</h4>
                                    <small class="text-muted">En Consultorio</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center">
                                    <h4 class="mb-0">{{ $stats['finalizadas_hoy'] }}</h4>
                                    <small class="text-muted">Finalizadas</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Inline Mini Calendar -->
                    <div class="inline-calendar"></div>

                    <hr class="mb-5 mx-n4 mt-3" />

                    <!-- Filtrar por Estado -->
                    <div class="d-flex justify-content-between align-items-center mb-4 ms-1">
                        <h5 class="mb-0">Filtrar por Estado</h5>
                    </div>

                    <div class="form-check form-check-secondary mb-5 ms-3">
                        <input class="form-check-input select-all" type="checkbox" id="selectAll" data-value="all" checked />
                        <label class="form-check-label" for="selectAll">Ver Todos</label>
                    </div>

                    <div class="app-calendar-events-filter text-heading">
                        @foreach($estados as $estado)
                            <div class="form-check mb-3 ms-3">
                                <input class="form-check-input input-filter" type="checkbox"
                                       id="select-{{ $estado }}"
                                       data-value="{{ $estado }}"
                                       checked />
                                <label class="form-check-label d-flex align-items-center" for="select-{{ $estado }}">
                                    <span class="legend-bar me-2" style="background-color: {{ $estadoColores[$estado] }}"></span>
                                    {{ $estadoLabels[$estado] }}
                                </label>
                            </div>
                        @endforeach
                    </div>

                    <!-- Médicos con Consultas -->
                    <hr class="mb-5 mx-n4 mt-3" />
                    <div class="mb-3 ms-1 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Médicos con Consultas</h5>
                    </div>
                    <div id="medicos-con-consultas" class="ms-1 perfect-scrollbar-container" style="max-height: 200px; position: relative;">
                        <small class="text-muted">Cargando...</small>
                    </div>
                </div>
            </div>

            <!-- Calendar & Offcanvas -->
            <div class="col app-calendar-content">
                <div class="card shadow-none border-0">
                    <div class="card-body pb-0">
                        <div id="calendar"></div>
                    </div>
                </div>
                <div class="app-overlay"></div>

                <!-- Offcanvas Detalle -->
                <div class="offcanvas offcanvas-end event-sidebar" tabindex="-1" id="consultaDetailSidebar">
                    <div class="offcanvas-header border-bottom">
                        <h5 class="offcanvas-title" id="consultaDetailTitle">Detalle de Consulta</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
                    </div>
                    <div class="offcanvas-body" id="consultaDetailBody">
                        <p class="text-muted">Seleccione una consulta para ver los detalles</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="/materialize/assets/vendor/libs/fullcalendar/fullcalendar.js"></script>
    <script src="/materialize/assets/vendor/libs/moment/moment.js"></script>
    <script src="/materialize/assets/vendor/libs/flatpickr/flatpickr.js"></script>
    <script src="{{ asset('js/app-calendar-consultas.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            initConsultasCalendar(@json($eventos), @json($estadoColores));
        });
    </script>
    @endpush
</div>
