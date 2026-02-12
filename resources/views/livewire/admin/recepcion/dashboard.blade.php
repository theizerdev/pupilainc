<div>
    <div class="row g-4 mb-4 align-items-center">
        <div class="col-md">
            <h4 class="mb-1">Recepción</h4>
            <p class="text-muted mb-0">Gestión diaria de pacientes y citas</p>
        </div>
        <div class="col-md-auto d-flex gap-2">
            <a href="{{ route('admin.citas.index') }}" class="btn btn-primary">
                <i class="ri-calendar-add-line me-1"></i> Agendar Cita
            </a>
            <a href="{{ route('admin.recepcion.control-consultorios') }}" class="btn btn-outline-primary">
                <i class="ri-hospital-line me-1"></i> Control Consultorios
            </a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-2 col-sm-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pendientes</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['pendientes'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-hourglass-half fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-sm-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Confirmadas</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['confirmadas'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-sm-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">En curso</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['en_curso'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-sm-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Completadas</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['completadas'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-sm-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Canceladas</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['canceladas'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-sm-4">
            <div class="card border-left-secondary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">No asistió</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['no_asistio'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-slash fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mt-1">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">Buscar Pacientes</h5>
                </div>
                <div class="card-body">
                    <div class="input-group input-group-lg">
                        <span class="input-group-text" id="search-icon">
                            <i class="ri-search-line"></i>
                        </span>
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Nombre, apellido o documento" aria-label="Buscar paciente" aria-describedby="search-icon">
                    </div>
                    <small class="text-muted mt-2 d-block">Ingrese al menos 3 caracteres para buscar.</small>
                </div>
                @if(strlen($search) > 2)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Paciente</th>
                                    <th>Documento</th>
                                    <th>Teléfono</th>
                                    <th>Próxima Cita</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pacientes as $paciente)
                                    @php $proximaCita = $paciente->citas->first(); @endphp
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm me-2">
                                                    <span class="avatar-title rounded-circle bg-primary-subtle text-primary">
                                                        {{ substr($paciente->nombres, 0, 1) }}{{ substr($paciente->apellidos, 0, 1) }}
                                                    </span>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0">{{ $paciente->nombre_completo }}</h6>
                                                    <small class="text-muted">{{ $paciente->email ?? 'Sin email' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $paciente->documento_identidad ?? 'N/A' }}</td>
                                        <td>{{ $paciente->telefono ?? 'N/A' }}</td>
                                        <td>
                                            @if($proximaCita)
                                                <div>{{ $proximaCita->fecha_inicio->format('d/m/Y') }}</div>
                                                <small class="text-muted">{{ $proximaCita->fecha_inicio->format('h:i A') }}</small>
                                                <div class="small text-primary">{{ $proximaCita->medico->nombre_completo ?? 'Médico no asignado' }}</div>
                                            @else
                                                <span class="text-muted">Sin citas próximas</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($proximaCita)
                                                @php
                                                    $badges = [
                                                        'pendiente' => 'warning',
                                                        'confirmada' => 'primary',
                                                        'en_curso' => 'info',
                                                        'completada' => 'success',
                                                        'cancelada' => 'danger',
                                                        'no_asistio' => 'secondary',
                                                    ];
                                                    $labels = \App\Models\Cita::ESTADO_LABELS;
                                                    $color = $badges[$proximaCita->estado] ?? 'secondary';
                                                    $label = $labels[$proximaCita->estado] ?? ucfirst($proximaCita->estado);
                                                @endphp
                                                <span class="badge bg-{{ $color }}">{{ $label }}</span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('admin.pacientes.edit', $paciente->id) }}" class="btn btn-sm btn-outline-primary" title="Ver Perfil">
                                                    <i class="ri-user-line"></i>
                                                </a>
                                                @if($proximaCita)
                                                    <a href="{{ route('admin.citas.index') }}?cita_id={{ $proximaCita->id }}" class="btn btn-sm btn-outline-info" title="Ver Cita">
                                                        <i class="ri-calendar-check-line"></i>
                                                    </a>
                                                @else
                                                    <a href="{{ route('admin.citas.index') }}" class="btn btn-sm btn-outline-success" title="Agendar Cita">
                                                        <i class="ri-calendar-add-line"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">No se encontraron pacientes.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="mb-0">Citas de Hoy</h5>
                    <span class="badge bg-label-primary">{{ $stats['total'] }}</span>
                </div>
                <div class="card-body">
                    <div id="citas-states-bars" style="min-height: 240px;"></div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Hora</th>
                                <th>Paciente</th>
                                <th>Médico</th>
                                <th>Estado</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($citasHoy as $cita)
                                <tr>
                                    <td>
                                        <div>{{ $cita->fecha_inicio->format('h:i A') }}</div>
                                        <small class="text-muted">{{ $cita->especialidad->nombre ?? '' }}</small>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm me-2">
                                                <span class="avatar-title rounded-circle bg-primary-subtle text-primary">
                                                    {{ substr($cita->paciente->nombres, 0, 1) }}{{ substr($cita->paciente->apellidos, 0, 1) }}
                                                </span>
                                            </div>
                                            <div>
                                                <div class="fw-medium">{{ $cita->paciente->nombre_completo }}</div>
                                                <small class="text-muted">{{ $cita->paciente->documento_identidad ?? 'N/A' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-medium">{{ $cita->medico->nombre_completo ?? '' }}</div>
                                        <small class="text-muted">ID: {{ $cita->medico_id }}</small>
                                    </td>
                                    <td>
                                        @php
                                            $badges = [
                                                'pendiente' => 'warning',
                                                'confirmada' => 'primary',
                                                'en_curso' => 'info',
                                                'completada' => 'success',
                                                'cancelada' => 'danger',
                                                'no_asistio' => 'secondary',
                                            ];
                                            $labels = \App\Models\Cita::ESTADO_LABELS;
                                            $color = $badges[$cita->estado] ?? 'secondary';
                                            $label = $labels[$cita->estado] ?? ucfirst($cita->estado);
                                        @endphp
                                        <span class="badge bg-{{ $color }}">{{ $label }}</span>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group">
                                            <button class="btn btn-sm btn-outline-info" wire:click="marcarLlegada({{ $cita->id }})" title="Marcar llegada">
                                                <i class="ri-user-location-line"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-success" wire:click="completarAtencion({{ $cita->id }})" title="Completar atención">
                                                <i class="ri-check-line"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" wire:click="cancelarCita({{ $cita->id }})" title="Cancelar">
                                                <i class="ri-close-circle-line"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-primary" wire:click="enviarRecordatorio({{ $cita->id }})" title="Enviar recordatorio">
                                                <i class="ri-notification-2-line"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No hay citas registradas para hoy.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="mb-0">Disponibilidad de Consultorios</h5>
                    <a href="{{ route('admin.recepcion.control-consultorios') }}" class="btn btn-sm btn-outline-primary">
                        <i class="ri-edit-box-line me-1"></i> Gestionar
                    </a>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div id="consultorios-ocupacion-donut" style="min-height: 220px;"></div>
                    </div>
                    <div class="row g-3">
                        @php
                            $ocupadosIds = $ocupacionConsultorios->keys()->all();
                        @endphp
                        @forelse($consultorios as $c)
                            <div class="col-12">
                                @php
                                    $ocupado = in_array($c->id, $ocupadosIds);
                                    $bg = $ocupado ? 'bg-danger bg-opacity-10 text-danger' : 'bg-success bg-opacity-10 text-success';
                                    $badge = $ocupado ? 'danger' : 'success';
                                    $estado = $ocupado ? 'Ocupado' : 'Disponible';
                                    $medicoNombre = $ocupado ? ($ocupacionConsultorios[$c->id]->medico->nombre_completo ?? '') : '';
                                @endphp
                                <div class="d-flex align-items-center justify-content-between p-2 border rounded-3">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar me-2">
                                            <div class="avatar-initial {{ $bg }} rounded">
                                                <i class="ri-hospital-line ri-20px"></i>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="fw-medium">{{ $c->nombre }}</div>
                                            <small class="text-muted">
                                                {{ $c->ubicacion ?? 'Sin ubicación' }}
                                                @if($medicoNombre)
                                                    • {{ $medicoNombre }}
                                                @endif
                                            </small>
                                        </div>
                                    </div>
                                    <span class="badge bg-{{ $badge }}">{{ $estado }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="col-12 text-muted">No hay consultorios configurados.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@push('scripts')
<script>
    document.addEventListener('livewire:init', function () {
        var citasBarsOptions = {
            chart: { type: 'bar', height: 240 },
            series: [{ name: 'Citas', data: [{{ $stats['pendientes'] }}, {{ $stats['confirmadas'] }}, {{ $stats['en_curso'] }}, {{ $stats['completadas'] }}, {{ $stats['canceladas'] }}, {{ $stats['no_asistio'] }}] }],
            colors: ['#ffc107', '#0d6efd', '#17a2b8', '#28a745', '#dc3545', '#6c757d'],
            xaxis: { categories: ['Pendientes', 'Confirmadas', 'En curso', 'Completadas', 'Canceladas', 'No asistió'] },
            plotOptions: { bar: { columnWidth: '45%', borderRadius: 6 } },
            dataLabels: { enabled: true }
        };
        var citasBarsChart = new ApexCharts(document.querySelector('#citas-states-bars'), citasBarsOptions);
        citasBarsChart.render();

        var ocupacionDonutOptions = {
            chart: { type: 'donut', height: 220 },
            labels: ['Ocupados', 'Disponibles'],
            series: [{{ $stats['ocupados'] ?? 0 }}, {{ $stats['disponibles'] ?? 0 }}],
            colors: ['#dc3545', '#28a745'],
            legend: { position: 'bottom' },
            dataLabels: { enabled: true }
        };
        var consultoriosDonut = new ApexCharts(document.querySelector('#consultorios-ocupacion-donut'), ocupacionDonutOptions);
        consultoriosDonut.render();
    });
</script>
@endpush
</div>
