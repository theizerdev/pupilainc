<div>
@php
use Illuminate\Support\Facades\Storage;
@endphp

    @push('styles')
    <style>
        .mascota-header {
            background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);
            color: white;
            border-radius: 1rem;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        .mascota-avatar-large {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid white;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .info-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 0.75rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .info-card-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #f3f4f6;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .info-card-title i {
            color: #F59E0B;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 0;
            border-bottom: 1px solid #f3f4f6;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: 500;
            color: #6b7280;
            font-size: 0.875rem;
        }
        .info-value {
            font-weight: 600;
            color: #1f2937;
            text-align: right;
        }
        .stat-box {
            background: #f9fafb;
            border-radius: 0.5rem;
            padding: 1rem;
            text-align: center;
        }
        .stat-box-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #F59E0B;
        }
        .stat-box-label {
            font-size: 0.75rem;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
    </style>
    @endpush

    <!-- Header -->
    <div class="mascota-header">
        <div class="row align-items-center">
            <div class="col-auto">
                @if($mascota->foto)
                    <img src="{{ Storage::url($mascota->foto) }}" 
                         alt="{{ $mascota->nombre }}" 
                         class="mascota-avatar-large">
                @else
                    <div class="mascota-avatar-large d-flex align-items-center justify-content-center bg-white">
                        <i class="ri ri-paw-line fs-1" style="color: #F59E0B;"></i>
                    </div>
                @endif
            </div>
            <div class="col">
                <h2 class="mb-1">{{ $mascota->nombre }}</h2>
                <p class="mb-2 opacity-75">
                    {{ $mascota->especie->icono ?? '🐾' }} {{ $mascota->especie->nombre }}
                    @if($mascota->raza)
                        · {{ $mascota->raza->nombre }}
                    @endif
                </p>
                <div class="d-flex gap-2">
                    @if($mascota->sexo === 'macho')
                        <span class="badge bg-primary">
                            <i class="ri ri-male-line me-1"></i>Macho
                        </span>
                    @else
                        <span class="badge" style="background-color: #ec4899;">
                            <i class="ri ri-female-line me-1"></i>Hembra
                        </span>
                    @endif
                    @if($mascota->esterilizado)
                        <span class="badge bg-success">
                            <i class="ri ri-check-line me-1"></i>Esterilizado
                        </span>
                    @endif
                    @if($mascota->microchip)
                        <span class="badge bg-info">
                            <i class="ri ri-chip-line me-1"></i>{{ $mascota->microchip }}
                        </span>
                    @endif
                </div>
            </div>
            <div class="col-auto">
                <div class="btn-group">
                    <a href="{{ route('admin.mascotas.edit', $mascota->id) }}" 
                       class="btn btn-light">
                        <i class="ri ri-edit-line me-1"></i>Editar
                    </a>
                    <a href="{{ route('admin.mascotas.index') }}" 
                       class="btn btn-light">
                        <i class="ri ri-arrow-go-back-line me-1"></i>Volver
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas Rápidas -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stat-box">
                <div class="stat-box-value">{{ $stats['total_citas'] }}</div>
                <div class="stat-box-label">Total Citas</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-box">
                <div class="stat-box-value">{{ $stats['total_consultas'] }}</div>
                <div class="stat-box-label">Consultas</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-box">
                <div class="stat-box-value">{{ $mascota->edad_formateada ?? 'N/A' }}</div>
                <div class="stat-box-label">Edad</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-box">
                <div class="stat-box-value">{{ $mascota->peso_actual_kg ? number_format($mascota->peso_actual_kg, 2) . ' kg' : 'N/A' }}</div>
                <div class="stat-box-label">Peso Actual</div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Información Principal -->
        <div class="col-lg-8">
            <!-- Datos Generales -->
            <div class="info-card">
                <div class="info-card-title">
                    <i class="ri ri-information-line"></i>
                    Información General
                </div>
                <div class="info-row">
                    <span class="info-label">Nombre</span>
                    <span class="info-value">{{ $mascota->nombre }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Especie</span>
                    <span class="info-value">{{ $mascota->especie->icono }} {{ $mascota->especie->nombre }}</span>
                </div>
                @if($mascota->raza)
                <div class="info-row">
                    <span class="info-label">Raza</span>
                    <span class="info-value">{{ $mascota->raza->nombre }}</span>
                </div>
                @endif
                <div class="info-row">
                    <span class="info-label">Sexo</span>
                    <span class="info-value">{{ $mascota->sexo_label }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Fecha de Nacimiento</span>
                    <span class="info-value">{{ $mascota->fecha_nacimiento?->format('d/m/Y') ?? 'No registrada' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Edad</span>
                    <span class="info-value">{{ $mascota->edad_formateada ?? 'No disponible' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Color/Pelaje</span>
                    <span class="info-value">{{ $mascota->color_pelaje ?? 'No especificado' }}</span>
                </div>
            </div>

            <!-- Identificación -->
            <div class="info-card">
                <div class="info-card-title">
                    <i class="ri ri-id-card-line"></i>
                    Identificación
                </div>
                <div class="info-row">
                    <span class="info-label">Microchip</span>
                    <span class="info-value">{{ $mascota->microchip ?? 'No registrado' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Número de Registro</span>
                    <span class="info-value">{{ $mascota->numero_registro ?? 'No registrado' }}</span>
                </div>
            </div>

            <!-- Salud -->
            <div class="info-card">
                <div class="info-card-title">
                    <i class="ri ri-heart-pulse-line"></i>
                    Salud
                </div>
                <div class="info-row">
                    <span class="info-label">Esterilizado</span>
                    <span class="info-value">
                        @if($mascota->esterilizado)
                            <span class="text-success"><i class="ri ri-check-line me-1"></i>Sí</span>
                            @if($mascota->fecha_esterilizacion)
                                <small class="text-muted">({{ $mascota->fecha_esterilizacion->format('d/m/Y') }})</small>
                            @endif
                        @else
                            <span class="text-muted">No</span>
                        @endif
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Nivel de Agresividad</span>
                    <span class="info-value">{{ $mascota->nivel_agresividad_label }}</span>
                </div>
                @if($mascota->alergias_conocidas)
                <div class="info-row">
                    <span class="info-label">Alergias</span>
                    <span class="info-value text-danger">{{ $mascota->alergias_conocidas }}</span>
                </div>
                @endif
                @if($mascota->condiciones_cronicas)
                <div class="info-row">
                    <span class="info-label">Condiciones Crónicas</span>
                    <span class="info-value text-warning">{{ $mascota->condiciones_cronicas }}</span>
                </div>
                @endif
            </div>

            <!-- Notas Adicionales -->
            @if($mascota->marcas_distintivas || $mascota->notas_generales)
            <div class="info-card">
                <div class="info-card-title">
                    <i class="ri ri-sticky-note-line"></i>
                    Notas Adicionales
                </div>
                @if($mascota->marcas_distintivas)
                <div class="mb-3">
                    <strong class="d-block mb-1">Marcas Distintivas:</strong>
                    <p class="mb-0 text-muted">{{ $mascota->marcas_distintivas }}</p>
                </div>
                @endif
                @if($mascota->notas_generales)
                <div>
                    <strong class="d-block mb-1">Notas Generales:</strong>
                    <p class="mb-0 text-muted">{{ $mascota->notas_generales }}</p>
                </div>
                @endif
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Propietario -->
            @if($mascota->propietario)
            <div class="info-card">
                <div class="info-card-title">
                    <i class="ri ri-user-line"></i>
                    Propietario
                </div>
                <div class="text-center mb-3">
                    <div class="fw-bold fs-5">{{ $mascota->propietario->nombre_completo }}</div>
                    @if($mascota->propietario->documento_identidad)
                        <small class="text-muted">{{ $mascota->propietario->documento_identidad }}</small>
                    @endif
                </div>
                <div class="info-row">
                    <span class="info-label"><i class="ri ri-phone-line me-1"></i>Teléfono</span>
                    <span class="info-value">{{ $mascota->propietario->telefono ?? 'No registrado' }}</span>
                </div>
                @if($mascota->propietario->email)
                <div class="info-row">
                    <span class="info-label"><i class="ri ri-mail-line me-1"></i>Email</span>
                    <span class="info-value small">{{ $mascota->propietario->email }}</span>
                </div>
                @endif
                @if($mascota->propietario->direccion)
                <div class="mt-2">
                    <span class="info-label d-block mb-1"><i class="ri ri-map-pin-line me-1"></i>Dirección</span>
                    <small class="text-muted">{{ $mascota->propietario->direccion }}</small>
                </div>
                @endif
            </div>
            @endif

            <!-- Últimas Citas -->
            @if($citasRecientes->count() > 0)
            <div class="info-card">
                <div class="info-card-title">
                    <i class="ri ri-calendar-line"></i>
                    Últimas Citas
                </div>
                @foreach($citasRecientes as $cita)
                <div class="border-bottom pb-2 mb-2 last-child-no-border">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="fw-semibold small">{{ $cita->fecha_inicio->format('d/m/Y') }}</div>
                            <small class="text-muted">{{ $cita->medico->nombre_completo ?? 'Sin médico' }}</small>
                        </div>
                        <span class="badge" style="background: {{ $cita->color_estado }};">
                            {{ $cita->nombre_estado }}
                        </span>
                    </div>
                </div>
                @endforeach
                <a href="#" class="btn btn-sm btn-outline-primary w-100 mt-2">
                    Ver todas las citas
                </a>
            </div>
            @endif

            <!-- Últimas Consultas -->
            @if($consultasRecientes->count() > 0)
            <div class="info-card">
                <div class="info-card-title">
                    <i class="ri ri-file-text-line"></i>
                    Últimas Consultas
                </div>
                @foreach($consultasRecientes as $consulta)
                <div class="border-bottom pb-2 mb-2">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="fw-semibold small">{{ $consulta->fecha_consulta->format('d/m/Y') }}</div>
                            <small class="text-muted">{{ $consulta->medico->nombre_completo ?? 'Sin médico' }}</small>
                        </div>
                        <span class="badge" style="background: {{ $consulta->estado_color }};">
                            {{ $consulta->estado_label }}
                        </span>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</div>
