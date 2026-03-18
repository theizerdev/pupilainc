<div>
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">📊 Analytics de Citas Médicas</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filtros -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="fechaInicio">Fecha Inicio:</label>
                            <input type="date" class="form-control" id="fechaInicio" wire:model="fechaInicio">
                            @error('fechaInicio') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-3">
                            <label for="fechaFin">Fecha Fin:</label>
                            <input type="date" class="form-control" id="fechaFin" wire:model="fechaFin">
                            @error('fechaFin') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-2">
                            <label>&nbsp;</label>
                            <div>
                                <button class="btn btn-primary btn-block" wire:click="cargarDatos">
                                    <i class="fas fa-sync-alt"></i> Actualizar
                                </button>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label>Rangos Rápidos:</label>
                            <div class="btn-group btn-block" role="group">
                                <button type="button" class="btn btn-outline-secondary" wire:click="actualizarFechas('hoy')">Hoy</button>
                                <button type="button" class="btn btn-outline-secondary" wire:click="actualizarFechas('semana')">Semana</button>
                                <button type="button" class="btn btn-outline-secondary" wire:click="actualizarFechas('mes')">Mes</button>
                                <button type="button" class="btn btn-outline-secondary" wire:click="actualizarFechas('anio')">Año</button>
                            </div>
                        </div>
                    </div>

                    <!-- Tarjetas de Resumen -->
                    @if(!empty($analyticsData))
                        <div class="row mb-4">
                            <div class="col-lg-3 col-6">
                                <div class="small-box bg-info">
                                    <div class="inner">
                                        <h3>{{ $analyticsData['resumen']['total'] ?? 0 }}</h3>
                                        <p>Total Citas</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-calendar-check"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-6">
                                <div class="small-box bg-success">
                                    <div class="inner">
                                        <h3>{{ $analyticsData['resumen']['completadas'] ?? 0 }}</h3>
                                        <p>Citas Completadas</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-6">
                                <div class="small-box bg-warning">
                                    <div class="inner">
                                        <h3>{{ $analyticsData['resumen']['canceladas'] ?? 0 }}</h3>
                                        <p>Citas Canceladas</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-times-circle"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-6">
                                <div class="small-box bg-danger">
                                    <div class="inner">
                                        <h3>{{ format_money($analyticsData['asistencia']['tasa_asistencia'] ?? 0, 1) }}%</h3>
                                        <p>Tasa de Asistencia</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-users"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Gráficos -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h4 class="card-title">📈 Distribución por Estados</h4>
                                    </div>
                                    <div class="card-body">
                                        <canvas id="estadosChart" width="400" height="200"></canvas>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h4 class="card-title">📅 Citas por Día de la Semana</h4>
                                    </div>
                                    <div class="card-body">
                                        <canvas id="tendenciasChart" width="400" height="200"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tablas de Datos -->
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h4 class="card-title">👨‍⚕️ Médicos Más Solicitados</h4>
                                    </div>
                                    <div class="card-body p-0">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Médico</th>
                                                    <th class="text-center">Citas</th>
                                                    <th class="text-center">% Completitud</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach(($analyticsData['medicos'] ?? []) as $medico)
                                                    <tr>
                                                        <td>{{ $medico['nombre'] }}</td>
                                                        <td class="text-center">{{ $medico['total_citas'] }}</td>
                                                        <td class="text-center">
                                                            <span class="badge badge-success">{{ $medico['tasa_completitud'] }}%</span>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h4 class="card-title">🏥 Especialidades Más Solicitadas</h4>
                                    </div>
                                    <div class="card-body p-0">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Especialidad</th>
                                                    <th class="text-center">Citas</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach(($analyticsData['especialidades'] ?? []) as $especialidad)
                                                    <tr>
                                                        <td>{{ $especialidad['nombre'] }}</td>
                                                        <td class="text-center">{{ $especialidad['total_citas'] }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Re-agendamientos Pendientes -->
                        @if(!empty($reagendamientosPendientes))
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h4 class="card-title">🔄 Re-agendamientos Pendientes</h4>
                                            <div class="card-tools">
                                                <button wire:click="procesarReagendamientoAutomatico" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-magic"></i> Procesar Automáticamente
                                                </button>
                                            </div>
                                        </div>
                                        <div class="card-body p-0">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Paciente</th>
                                                        <th>Médico</th>
                                                        <th>Fecha Cancelada</th>
                                                        <th>Motivo</th>
                                                        <th>Horarios Disponibles</th>
                                                        <th>Acciones</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($reagendamientosPendientes as $reagendamiento)
                                                        <tr>
                                                            <td>{{ $reagendamiento['paciente'] }}</td>
                                                            <td>{{ $reagendamiento['medico'] }}</td>
                                                            <td>{{ $reagendamiento['fecha_cancelada'] }}</td>
                                                            <td>
                                                                <span class="badge badge-warning">{{ $reagendamiento['motivo'] }}</span>
                                                            </td>
                                                            <td class="text-center">
                                                                <span class="badge badge-info">{{ $reagendamiento['horarios_disponibles'] }} disponibles</span>
                                                            </td>
                                                            <td>
                                                                <button class="btn btn-sm btn-outline-primary">
                                                                    <i class="fas fa-calendar-plus"></i> Ver Opciones
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No hay datos disponibles para el rango de fechas seleccionado.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts para gráficos -->
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('livewire:load', function () {
            // Datos del componente
            const analyticsData = @json($this->getChartData());
            
            // Gráfico de Estados
            if (analyticsData.estados && analyticsData.estados.length > 0) {
                const ctxEstados = document.getElementById('estadosChart').getContext('2d');
                new Chart(ctxEstados, {
                    type: 'doughnut',
                    data: {
                        labels: analyticsData.estados.map(e => e.label),
                        datasets: [{
                            data: analyticsData.estados.map(e => e.total),
                            backgroundColor: analyticsData.estados.map(e => {
                                const colors = {
                                    'success': '#28a745',
                                    'primary': '#007bff',
                                    'warning': '#ffc107',
                                    'danger': '#dc3545',
                                    'info': '#17a2b8',
                                    'secondary': '#6c757d'
                                };
                                return colors[e.color] || '#6c757d';
                            })
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                });
            }

            // Gráfico de Tendencias
            if (analyticsData.tendencias && analyticsData.tendencias.length > 0) {
                const ctxTendencias = document.getElementById('tendenciasChart').getContext('2d');
                new Chart(ctxTendencias, {
                    type: 'bar',
                    data: {
                        labels: analyticsData.tendencias.map(t => t.dia),
                        datasets: [{
                            label: 'Citas por Día',
                            data: analyticsData.tendencias.map(t => t.total_citas),
                            backgroundColor: 'rgba(54, 162, 235, 0.2)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }
        });
    </script>
    @endpush
</div>