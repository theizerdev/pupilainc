<div>
    @section('title', 'Detalles de Especialidad')

    @push('styles')
    <style>
        .esp-hero { background: linear-gradient(135deg, var(--esp-color, #3B82F6) 0%, var(--esp-color-2, #6366F1) 100%);
                    color:#fff; border-radius:.75rem; padding:1.5rem 1.75rem; }
        .esp-hero .hero-icon { width:64px; height:64px; border-radius:16px; background:rgba(255,255,255,.18);
                               display:flex; align-items:center; justify-content:center; font-size:1.75rem; }
        .esp-hero h2, .esp-hero p { color:#fff; margin:0; }
        .esp-hero .hero-meta { opacity:.92; font-size:.83rem; }
        .esp-hero .hero-meta .badge { font-weight:500; }

        .stat-card { border:1px solid rgba(0,0,0,.06); border-radius:.65rem; padding:.95rem 1.05rem;
                     transition:all .2s; height:100%; display:flex; align-items:center; gap:.85rem; background:#fff; }
        .stat-card:hover { box-shadow:0 6px 18px rgba(0,0,0,.07); transform:translateY(-1px); }
        .stat-card .stat-icon { width:44px; height:44px; border-radius:11px; flex:0 0 44px;
                                display:flex; align-items:center; justify-content:center; font-size:1.15rem; }
        .stat-card .stat-value { font-size:1.4rem; font-weight:600; line-height:1; }
        .stat-card .stat-label { font-size:.72rem; color:var(--bs-secondary-color);
                                 text-transform:uppercase; letter-spacing:.4px; font-weight:600; }

        .info-row { padding:.7rem 0; border-bottom:1px solid #f1f3f5; }
        .info-row:last-child { border-bottom:0; }
        .info-row .label { font-size:.7rem; text-transform:uppercase; color:var(--bs-secondary-color);
                           letter-spacing:.4px; margin-bottom:.2rem; font-weight:600; }
        .info-row .value { font-size:.93rem; font-weight:500; color:var(--bs-body-color); }

        .quick-action { border:1px solid rgba(0,0,0,.07); border-radius:.55rem; padding:.7rem .9rem;
                        transition:all .15s; display:flex; align-items:center; gap:.75rem;
                        text-decoration:none; color:var(--bs-body-color); }
        .quick-action:hover { background:var(--bs-tertiary-bg); border-color:var(--qa-color, var(--bs-primary));
                              color:var(--qa-color, var(--bs-primary)); transform:translateX(2px); }
        .quick-action .qa-icon { width:38px; height:38px; border-radius:10px; flex:0 0 38px;
                                 display:flex; align-items:center; justify-content:center; font-size:1rem;
                                 background:color-mix(in srgb, var(--qa-color, #6366F1) 14%, transparent);
                                 color:var(--qa-color, #6366F1); }
        .quick-action .qa-title { font-weight:600; font-size:.87rem; line-height:1.2; }
        .quick-action .qa-sub { font-size:.72rem; color:var(--bs-secondary-color); }

        .config-status { border-left:3px solid var(--cs-color, #6c757d); padding:.6rem .9rem;
                         background:var(--bs-tertiary-bg); border-radius:0 .35rem .35rem 0; }
        .config-status .cs-title { font-weight:600; font-size:.85rem; }

        .medico-avatar { width:34px; height:34px; border-radius:50%; display:inline-flex;
                         align-items:center; justify-content:center; font-size:.78rem;
                         font-weight:600; color:#fff; background:var(--av-color, #6366F1); }

        .visual-preview { width:130px; height:130px; margin:0 auto; border-radius:24px;
                          display:flex; align-items:center; justify-content:center;
                          font-size:3.4rem; color:#fff; box-shadow:0 8px 22px rgba(0,0,0,.12); }

        .subesp-card { border:1px solid rgba(0,0,0,.07); border-radius:.55rem; padding:.8rem .95rem;
                       transition:all .2s; border-left:4px solid var(--se-color, #3B82F6); height:100%; }
        .subesp-card:hover { box-shadow:0 4px 14px rgba(0,0,0,.08); transform:translateY(-1px); }
    </style>
    @endpush

    <div class="container-xxl flex-grow-1 container-p-y">
        @php
            $color  = $especialidad->color ?: '#3B82F6';
            $color2 = $especialidad->color ?: '#6366F1';
            $plantillaActiva   = $especialidad->plantillaActiva()->exists();
            $cuestionarioCount = $especialidad->cuestionarios()->where('activo', true)->count();
            $subespCount       = $especialidad->subespecialidades()->count();
        @endphp

        {{-- Breadcrumb --}}
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb breadcrumb-style1 mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.especialidades.index') }}"><i class="ri ri-stethoscope-line me-1"></i>Especialidades</a></li>
                <li class="breadcrumb-item active">{{ $especialidad->nombre }}</li>
            </ol>
        </nav>

        {{-- HERO --}}
        <div class="esp-hero mb-4 d-flex flex-wrap align-items-center justify-content-between gap-3"
             style="--esp-color: {{ $color }}; --esp-color-2: {{ $color2 }}">
            <div class="d-flex align-items-center gap-3 flex-grow-1">
                <div class="hero-icon"><i class="fas {{ $especialidad->icono ?? 'fa-stethoscope' }}"></i></div>
                <div>
                    <h2 class="mb-1 fw-semibold">{{ $especialidad->nombre }}</h2>
                    <div class="hero-meta d-flex align-items-center gap-2 flex-wrap">
                        <span class="badge bg-white text-dark"><i class="ri ri-hashtag me-1"></i>{{ $especialidad->codigo }}</span>
                        @if($especialidad->status)
                            <span class="badge bg-success"><i class="ri ri-checkbox-circle-fill me-1"></i>Activa</span>
                        @else
                            <span class="badge bg-secondary"><i class="ri ri-close-circle-fill me-1"></i>Inactiva</span>
                        @endif
                        @if($especialidad->requiere_cita_previa)
                            <span class="badge bg-white bg-opacity-25"><i class="ri ri-calendar-check-line me-1"></i>Requiere cita previa</span>
                        @endif
                        <span><i class="ri ri-time-line me-1"></i>Actualizada {{ $especialidad->updated_at->diffForHumans() }}</span>
                    </div>
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @can('admin.especialidades.edit')
                    <a href="{{ route('admin.especialidades.edit', $especialidad) }}" class="btn btn-light">
                        <i class="ri ri-pencil-line me-1"></i>Editar
                    </a>
                @endcan
                <a href="{{ route('admin.especialidades.index') }}" class="btn btn-outline-light">
                    <i class="ri ri-arrow-left-line me-1"></i>Volver
                </a>
            </div>
        </div>

        {{-- KPI strip --}}
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background:#dcfce7;color:#16a34a;"><i class="ri ri-user-heart-line"></i></div>
                    <div>
                        <div class="stat-label">Médicos activos</div>
                        <div class="stat-value">{{ $estadisticas['total_medicos'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background:#dbeafe;color:#2563eb;"><i class="ri ri-calendar-event-line"></i></div>
                    <div>
                        <div class="stat-label">Citas del mes</div>
                        <div class="stat-value">{{ $estadisticas['citas_mes'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background:#fef3c7;color:#d97706;"><i class="ri ri-calendar-todo-line"></i></div>
                    <div>
                        <div class="stat-label">Citas hoy</div>
                        <div class="stat-value">{{ $estadisticas['citas_hoy'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background:#cffafe;color:#0891b2;"><i class="ri ri-money-dollar-circle-line"></i></div>
                    <div>
                        <div class="stat-label">Ingresos del mes</div>
                        <div class="stat-value">{{ format_money($estadisticas['ingresos_mes'] ?? 0, 2) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            {{-- ── COLUMNA IZQUIERDA ─────────────────────────────────── --}}
            <div class="col-lg-8">
                {{-- Información general --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent border-0 pb-0">
                        <h6 class="mb-0"><i class="ri ri-information-line me-2 text-primary"></i>Información general</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="info-row">
                                    <div class="label">Descripción</div>
                                    <div class="value">{{ $especialidad->descripcion ?: 'Sin descripción' }}</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-row">
                                    <div class="label"><i class="ri ri-money-dollar-circle-line me-1"></i>Costo de consulta</div>
                                    <div class="value">{{ format_money($especialidad->costo_consulta) }}</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-row">
                                    <div class="label"><i class="ri ri-time-line me-1"></i>Duración</div>
                                    <div class="value">{{ $especialidad->duracion_consulta }} minutos</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-row">
                                    <div class="label"><i class="ri ri-calendar-check-line me-1"></i>Cita previa</div>
                                    <div class="value">{{ $especialidad->requiere_cita_previa ? 'Requerida' : 'Opcional' }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-row">
                                    <div class="label"><i class="ri ri-building-line me-1"></i>Empresa</div>
                                    <div class="value">{{ $especialidad->empresa->razon_social ?? '—' }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-row">
                                    <div class="label"><i class="ri ri-map-pin-line me-1"></i>Sucursal</div>
                                    <div class="value">{{ $especialidad->sucursal->nombre ?? '—' }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-row">
                                    <div class="label"><i class="ri ri-calendar-line me-1"></i>Creada</div>
                                    <div class="value">{{ $especialidad->created_at->format('d/m/Y H:i') }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-row">
                                    <div class="label"><i class="ri ri-history-line me-1"></i>Última actualización</div>
                                    <div class="value">{{ $especialidad->updated_at->format('d/m/Y H:i') }}
                                        <small class="text-muted">({{ $especialidad->updated_at->diffForHumans() }})</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Médicos asignados --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent border-0 pb-0 d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <h6 class="mb-0">
                            <i class="ri ri-user-heart-line me-2 text-primary"></i>Médicos asignados
                            <span class="badge bg-label-primary ms-1">{{ $especialidad->medicos->count() }}</span>
                        </h6>
                    </div>
                    <div class="card-body">
                        @if($especialidad->medicos->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Médico</th>
                                            <th>Email</th>
                                            <th>Teléfono</th>
                                            <th class="text-center">Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($especialidad->medicos as $medico)
                                            @php
                                                $iniciales = strtoupper(mb_substr($medico->nombres,0,1).mb_substr($medico->apellidos,0,1));
                                                $avColor   = '#'.substr(md5($medico->id),0,6);
                                            @endphp
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <span class="medico-avatar" style="--av-color: {{ $avColor }}">{{ $iniciales }}</span>
                                                        <div>
                                                            <div class="fw-semibold">{{ $medico->nombres }} {{ $medico->apellidos }}</div>
                                                            @if($medico->licencia_medica)
                                                                <small class="text-muted">Lic. {{ $medico->licencia_medica }}</small>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><small>{{ $medico->user->email ?? '—' }}</small></td>
                                                <td><small>{{ $medico->telefono ?? '—' }}</small></td>
                                                <td class="text-center">
                                                    @if($medico->status)
                                                        <span class="badge bg-label-success">Activo</span>
                                                    @else
                                                        <span class="badge bg-label-secondary">Inactivo</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="ri ri-user-heart-line text-muted" style="font-size:2.5rem;opacity:.3"></i>
                                <p class="text-muted mb-0 mt-2">No hay médicos asignados a esta especialidad</p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Subespecialidades --}}
                @if($subespCount > 0)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent border-0 pb-0">
                        <h6 class="mb-0">
                            <i class="ri ri-node-tree me-2 text-primary"></i>Subespecialidades
                            <span class="badge bg-label-primary ms-1">{{ $subespCount }}</span>
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-2">
                            @foreach($especialidad->subespecialidades as $sub)
                                <div class="col-md-6">
                                    <a href="{{ route('admin.subespecialidades.show', $sub->id) }}" class="text-decoration-none text-body">
                                        <div class="subesp-card" style="--se-color: {{ $sub->color ?? '#3B82F6' }}">
                                            <div class="d-flex align-items-center justify-content-between gap-2">
                                                <div class="text-truncate">
                                                    <div class="fw-semibold text-truncate">{{ $sub->nombre }}</div>
                                                    <small class="text-muted">{{ $sub->codigo }} · {{ $sub->duracion_consulta }} min</small>
                                                </div>
                                                <span class="badge bg-label-{{ $sub->status ? 'success' : 'secondary' }}">{{ $sub->status ? 'Activa' : 'Inactiva' }}</span>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif
            </div>

            {{-- ── COLUMNA DERECHA ───────────────────────────────────── --}}
            <div class="col-lg-4">
                {{-- Acciones rápidas --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent border-0 pb-0">
                        <h6 class="mb-0"><i class="ri ri-flashlight-line me-2 text-primary"></i>Acciones rápidas</h6>
                    </div>
                    <div class="card-body d-flex flex-column gap-2">
                        @can('admin.especialidades.edit')
                            <a href="{{ route('admin.especialidades.edit', $especialidad) }}" class="quick-action" style="--qa-color:#f59e0b;">
                                <div class="qa-icon"><i class="ri ri-pencil-line"></i></div>
                                <div>
                                    <div class="qa-title">Editar especialidad</div>
                                    <div class="qa-sub">Modifica datos básicos</div>
                                </div>
                            </a>
                        @endcan
                        <a href="{{ route('admin.especialidades.plantilla', $especialidad) }}" class="quick-action" style="--qa-color:#6366F1;">
                            <div class="qa-icon"><i class="ri ri-layout-grid-line"></i></div>
                            <div>
                                <div class="qa-title">Plantilla de consulta</div>
                                <div class="qa-sub">Configura secciones y campos</div>
                            </div>
                        </a>
                        <a href="{{ route('admin.especialidades.cuestionario', $especialidad) }}" class="quick-action" style="--qa-color:#0891b2;">
                            <div class="qa-icon"><i class="ri ri-questionnaire-line"></i></div>
                            <div>
                                <div class="qa-title">Cuestionario</div>
                                <div class="qa-sub">Preguntas pre-consulta</div>
                            </div>
                        </a>
                    </div>
                </div>

                {{-- Estado de configuración --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent border-0 pb-0">
                        <h6 class="mb-0"><i class="ri ri-checkbox-multiple-line me-2 text-primary"></i>Estado de configuración</h6>
                    </div>
                    <div class="card-body d-flex flex-column gap-2">
                        <div class="config-status" style="--cs-color: {{ $plantillaActiva ? '#10b981' : '#f59e0b' }}">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="cs-title">Plantilla de consulta</div>
                                    <small class="text-muted">{{ $plantillaActiva ? 'Activa y configurada' : 'Sin plantilla activa' }}</small>
                                </div>
                                <i class="ri {{ $plantillaActiva ? 'ri-checkbox-circle-fill text-success' : 'ri-error-warning-line text-warning' }}" style="font-size:1.4rem;"></i>
                            </div>
                        </div>
                        <div class="config-status" style="--cs-color: {{ $cuestionarioCount > 0 ? '#10b981' : '#f59e0b' }}">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="cs-title">Cuestionarios</div>
                                    <small class="text-muted">{{ $cuestionarioCount > 0 ? $cuestionarioCount.' activo(s)' : 'Sin cuestionarios' }}</small>
                                </div>
                                <i class="ri {{ $cuestionarioCount > 0 ? 'ri-checkbox-circle-fill text-success' : 'ri-error-warning-line text-warning' }}" style="font-size:1.4rem;"></i>
                            </div>
                        </div>
                        <div class="config-status" style="--cs-color: {{ $subespCount > 0 ? '#10b981' : '#94a3b8' }}">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="cs-title">Subespecialidades</div>
                                    <small class="text-muted">{{ $subespCount }} registrada(s)</small>
                                </div>
                                <i class="ri ri-node-tree {{ $subespCount > 0 ? 'text-success' : 'text-muted' }}" style="font-size:1.4rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Identificación visual --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent border-0 pb-0">
                        <h6 class="mb-0"><i class="ri ri-palette-line me-2 text-primary"></i>Identificación visual</h6>
                    </div>
                    <div class="card-body text-center">
                        <div class="visual-preview mb-3" style="background: linear-gradient(135deg, {{ $color }} 0%, {{ $color2 }} 100%);">
                            <i class="fas {{ $especialidad->icono }}"></i>
                        </div>
                        <div class="d-flex align-items-center justify-content-center gap-2 mb-2 flex-wrap">
                            <code class="bg-light text-dark px-2 py-1 rounded small">{{ $color }}</code>
                            <span class="badge" style="background: {{ $color }}; color:#fff;">{{ $especialidad->codigo }}</span>
                        </div>
                        <small class="text-muted d-block">Color e icono usados en calendarios y citas</small>
                    </div>
                </div>
            </div>
        </div>{{-- /row --}}
    </div>{{-- /container --}}
</div>
