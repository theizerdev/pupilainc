<div>
    {{-- Header con gradiente estilo Materialize --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-gradient-primary shadow-primary">
                <div class="card-body py-3">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb breadcrumb-style1 mb-2">
                                    <li class="breadcrumb-item">
                                        <a href="{{ route('admin.recepcion.dashboard') }}" class="text-white-50">
                                            <i class="ri-home-line me-1"></i>Recepción
                                        </a>
                                    </li>
                                    <li class="breadcrumb-item text-white active" aria-current="page">Pre-consulta</li>
                                </ol>
                            </nav>
                            <h4 class="text-white mb-1 fw-bold">
                                <i class="ri-file-list-3-line me-2"></i>Pre-consulta Médica
                            </h4>
                            <p class="text-white-50 mb-0">Preparación de cita y envío de cuestionario al paciente</p>
                        </div>
                        <div class="col-md-4 text-md-end mt-3 mt-md-0">
                            <a href="{{ route('admin.recepcion.dashboard') }}" class="btn btn-outline-light btn-sm">
                                <i class="ri-arrow-left-line me-1"></i> Volver
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Stepper de progreso --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm me-2">
                                <span class="avatar-initial rounded-circle bg-label-success">
                                    <i class="ri-check-line ri-16px"></i>
                                </span>
                            </div>
                            <div>
                                <span class="fw-semibold text-success small">Paso 1</span>
                                <div class="text-muted small">Recepción</div>
                            </div>
                        </div>
                        <div class="flex-grow-1 mx-3">
                            <hr class="border-success m-0">
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm me-2">
                                <span class="avatar-initial rounded-circle bg-label-primary">
                                    <i class="ri-edit-line ri-16px"></i>
                                </span>
                            </div>
                            <div>
                                <span class="fw-bold text-primary small">Paso 2</span>
                                <div class="text-primary fw-semibold small">Pre-consulta</div>
                            </div>
                        </div>
                        <div class="flex-grow-1 mx-3">
                            <hr class="border-dashed m-0" style="border-style: dashed;">
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm me-2">
                                <span class="avatar-initial rounded-circle bg-label-secondary">
                                    <i class="ri-stethoscope-line ri-16px"></i>
                                </span>
                            </div>
                            <div>
                                <span class="fw-semibold text-muted small">Paso 3</span>
                                <div class="text-muted small">Consulta</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- Columna Izquierda: Tarjeta del Paciente --}}
        <div class="col-lg-4">
            {{-- Card del Paciente --}}
            <div class="card card-border-shadow-primary mb-4">
                <div class="card-body text-center pt-4 pb-3">
                    <div class="mx-auto mb-3" style="width: 80px; height: 80px;">
                        @if($paciente->foto)
                            <img src="{{ asset('storage/' . $paciente->foto) }}" 
                                 alt="{{ $paciente->nombre_completo }}" 
                                 class="w-100 h-100 rounded-circle object-fit-cover border border-3 border-primary">
                        @else
                            <span class="avatar-initial rounded-circle bg-label-primary d-flex align-items-center justify-content-center w-100 h-100" style="font-size: 1.5rem;">
                                {{ substr($paciente->nombres, 0, 1) }}{{ substr($paciente->apellidos, 0, 1) }}
                            </span>
                        @endif
                    </div>
                    <h5 class="mb-1 fw-bold">{{ $paciente->nombre_completo }}</h5>
                    <span class="text-muted d-block mb-3">
                        <i class="ri-id-card-line me-1"></i>{{ $paciente->documento_identidad ?? 'Sin documento' }}
                    </span>

                    <div class="d-flex justify-content-center gap-2 flex-wrap mb-3">
                        @if($paciente->edad)
                            <span class="badge bg-label-primary rounded-pill">
                                <i class="ri-user-heart-line me-1"></i>{{ $paciente->edad }} años
                            </span>
                        @endif
                        @if($paciente->genero)
                            <span class="badge bg-label-info rounded-pill">
                                {{ $paciente->genero === 'M' ? '♂ Masculino' : '♀ Femenino' }}
                            </span>
                        @endif
                        <span class="badge bg-label-{{ $totalCitas > 0 ? 'success' : 'warning' }} rounded-pill">
                            <i class="ri-history-line me-1"></i>{{ $totalCitas > 0 ? 'Recurrente' : 'Primera vez' }}
                        </span>
                    </div>

                    @if(!$paciente->isProfileComplete())
                        <div class="alert alert-warning py-2 px-3 mb-0 small">
                            <i class="ri-error-warning-line me-1"></i>
                            <a href="{{ route('admin.pacientes.edit', $paciente->id) }}" target="_blank" class="alert-link">
                                Perfil incompleto - Completar datos
                            </a>
                        </div>
                    @endif
                </div>

                <div class="card-footer py-3">
                    <div class="row text-center g-0">
                        <div class="col border-end">
                            <div class="d-flex flex-column">
                                <span class="text-heading fw-bold">{{ $totalCitas }}</span>
                                <small class="text-muted">Consultas</small>
                            </div>
                        </div>
                        <div class="col">
                            <div class="d-flex flex-column">
                                <span class="text-heading fw-bold">{{ $ultimaVisita ? $ultimaVisita->fecha_inicio->format('d/m/Y') : 'N/A' }}</span>
                                <small class="text-muted">Última visita</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Información de Contacto --}}
            <div class="card card-border-shadow-info mb-4">
                <div class="card-header pb-2">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-sm me-2">
                            <span class="avatar-initial rounded bg-label-info">
                                <i class="ri-contacts-book-line ri-16px"></i>
                            </span>
                        </div>
                        <h6 class="card-title mb-0">Información de Contacto</h6>
                    </div>
                </div>
                <div class="card-body pt-1">
                    <ul class="list-unstyled mb-0">
                        <li class="d-flex align-items-center py-2 border-bottom">
                            <div class="avatar avatar-xs me-3 flex-shrink-0">
                                <span class="avatar-initial rounded bg-label-success">
                                    <i class="ri-phone-line ri-14px"></i>
                                </span>
                            </div>
                            <div class="flex-grow-1">
                                <small class="text-muted d-block">Teléfono</small>
                                <span class="fw-medium">{{ $paciente->telefono ?? 'No registrado' }}</span>
                            </div>
                            @if($paciente->telefono)
                                <span class="badge bg-label-success rounded-pill">
                                    <i class="ri-whatsapp-line"></i>
                                </span>
                            @endif
                        </li>
                        <li class="d-flex align-items-center py-2 border-bottom">
                            <div class="avatar avatar-xs me-3 flex-shrink-0">
                                <span class="avatar-initial rounded bg-label-primary">
                                    <i class="ri-mail-line ri-14px"></i>
                                </span>
                            </div>
                            <div class="flex-grow-1">
                                <small class="text-muted d-block">Email</small>
                                <span class="fw-medium">{{ $paciente->email ?? 'No registrado' }}</span>
                            </div>
                        </li>
                        <li class="d-flex align-items-center py-2 {{ $paciente->tutor ? 'border-bottom' : '' }}">
                            <div class="avatar avatar-xs me-3 flex-shrink-0">
                                <span class="avatar-initial rounded bg-label-warning">
                                    <i class="ri-map-pin-line ri-14px"></i>
                                </span>
                            </div>
                            <div class="flex-grow-1">
                                <small class="text-muted d-block">Dirección</small>
                                <span class="fw-medium">{{ Str::limit($paciente->direccion, 40) ?? 'No registrada' }}</span>
                            </div>
                        </li>
                        @if($paciente->tutor)
                            <li class="d-flex align-items-center py-2">
                                <div class="avatar avatar-xs me-3 flex-shrink-0">
                                    <span class="avatar-initial rounded bg-label-danger">
                                        <i class="ri-parent-line ri-14px"></i>
                                    </span>
                                </div>
                                <div class="flex-grow-1">
                                    <small class="text-muted d-block">Tutor / Responsable</small>
                                    <span class="fw-medium">{{ $paciente->tutor->nombres }} {{ $paciente->tutor->apellidos }}</span>
                                    <div class="small text-muted">
                                        <i class="ri-phone-line me-1"></i>{{ $paciente->tutor->telefono ?? 'Sin teléfono' }}
                                        @if($paciente->tutor->parentesco)
                                            <span class="ms-2">• {{ $paciente->tutor->parentesco }}</span>
                                        @endif
                                    </div>
                                </div>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>

            {{-- Cita Actual (si existe) --}}
            @if($cita)
                <div class="card card-border-shadow-success">
                    <div class="card-header pb-2">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm me-2">
                                <span class="avatar-initial rounded bg-label-success">
                                    <i class="ri-calendar-check-line ri-16px"></i>
                                </span>
                            </div>
                            <h6 class="card-title mb-0">Cita de Hoy</h6>
                        </div>
                    </div>
                    <div class="card-body pt-1">
                        <div class="d-flex align-items-center mb-2">
                            <i class="ri-time-line me-2 text-muted"></i>
                            <span class="fw-medium">{{ $cita->fecha_inicio->format('h:i A') }}</span>
                            <span class="badge bg-label-{{ $cita->estado === 'pendiente' ? 'warning' : ($cita->estado === 'confirmada' ? 'primary' : 'info') }} ms-2">
                                {{ ucfirst($cita->estado) }}
                            </span>
                        </div>
                        @if($cita->especialidad)
                            <div class="d-flex align-items-center mb-2">
                                <i class="ri-hospital-line me-2 text-muted"></i>
                                <span>{{ $cita->especialidad->nombre }}</span>
                            </div>
                        @endif
                        @if($cita->medico)
                            <div class="d-flex align-items-center">
                                <i class="ri-stethoscope-line me-2 text-muted"></i>
                                <span>{{ $cita->medico->nombre_completo }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            @else
                <div class="card card-border-shadow-warning">
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm me-3">
                                <span class="avatar-initial rounded bg-label-warning">
                                    <i class="ri-calendar-close-line ri-16px"></i>
                                </span>
                            </div>
                            <div>
                                <h6 class="mb-0">Sin cita programada</h6>
                                <small class="text-muted">Se creará una cita automáticamente al enviar</small>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Columna Derecha: Formulario --}}
        <div class="col-lg-8">
            <div class="card card-border-shadow-primary">
                <div class="card-header border-bottom">
                    <div class="d-flex align-items-center">
                        <div class="avatar me-3">
                            <span class="avatar-initial rounded bg-label-primary">
                                <i class="ri-file-list-3-line ri-20px"></i>
                            </span>
                        </div>
                        <div>
                            <h5 class="card-title mb-0">Configuración de la Sesión</h5>
                            <p class="card-subtitle text-muted small mb-0 mt-1">Configure la especialidad, médico y motivo de consulta</p>
                        </div>
                    </div>
                </div>
                <div class="card-body pt-4">
                    <form wire:submit="iniciarCuestionario">
                        <div class="row g-4">
                            {{-- Especialidad --}}
                            <div class="col-md-6">
                                <label class="form-label fw-medium" for="especialidad_id">
                                    Especialidad <span class="text-danger">*</span>
                                </label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text"><i class="ri-hospital-line"></i></span>
                                    <select wire:model.live="especialidad_id" id="especialidad_id"
                                            class="form-select @error('especialidad_id') is-invalid @enderror">
                                        <option value="">Seleccione especialidad...</option>
                                        @foreach($especialidades as $especialidad)
                                            <option value="{{ $especialidad->id }}">{{ $especialidad->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('especialidad_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Médico --}}
                            <div class="col-md-6">
                                <label class="form-label fw-medium" for="medico_id">
                                    Médico Tratante <span class="text-danger">*</span>
                                </label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text"><i class="ri-stethoscope-line"></i></span>
                                    <select wire:model="medico_id" id="medico_id"
                                            class="form-select @error('medico_id') is-invalid @enderror"
                                            @if(empty($medicos)) disabled @endif>
                                        <option value="">{{ empty($medicos) ? 'Seleccione especialidad primero...' : 'Seleccione médico...' }}</option>
                                        @foreach($medicos as $medico)
                                            <option value="{{ $medico->id }}">{{ $medico->nombre_completo }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('medico_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                @if(empty($medicos) && $especialidad_id)
                                    <small class="text-warning mt-1 d-block">
                                        <i class="ri-error-warning-line me-1"></i>No hay médicos disponibles para esta especialidad
                                    </small>
                                @endif
                            </div>

                            {{-- Motivo de Consulta --}}
                            <div class="col-12">
                                <label class="form-label fw-medium" for="motivo_consulta">
                                    Motivo de la Consulta <span class="text-danger">*</span>
                                </label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text align-self-start mt-1"><i class="ri-chat-3-line"></i></span>
                                    <textarea wire:model="motivo_consulta" id="motivo_consulta"
                                              class="form-control @error('motivo_consulta') is-invalid @enderror"
                                              rows="4"
                                              placeholder="Describa los síntomas principales o razón de la visita..."></textarea>
                                </div>
                                @error('motivo_consulta')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    <i class="ri-information-line me-1"></i>Esta información será visible para el médico antes de iniciar la consulta.
                                </div>
                            </div>
                        </div>

                        {{-- Información de WhatsApp --}}
                        <div class="divider">
                            <div class="divider-text">
                                <i class="ri-whatsapp-line text-success me-1"></i> Envío por WhatsApp
                            </div>
                        </div>

                        <div class="card bg-lighter border shadow-none mb-4">
                            <div class="card-body py-3">
                                <div class="d-flex align-items-start">
                                    <div class="avatar me-3 flex-shrink-0">
                                        <span class="avatar-initial rounded bg-label-success">
                                            <i class="ri-whatsapp-line ri-20px"></i>
                                        </span>
                                    </div>
                                    <div>
                                        <h6 class="mb-1 fw-bold">Cuestionario Digital</h6>
                                        <p class="text-muted small mb-2">
                                            Al confirmar, se enviará un enlace seguro por WhatsApp para que el paciente complete 
                                            sus antecedentes médicos mientras espera.
                                        </p>
                                        <div class="d-flex align-items-center flex-wrap gap-2">
                                            @php
                                                $telefonoDestino = $paciente->telefono ?? ($paciente->tutor->telefono ?? null);
                                            @endphp
                                            @if($telefonoDestino)
                                                <span class="badge bg-label-success rounded-pill">
                                                    <i class="ri-check-line me-1"></i>Número destino: <strong>{{ $telefonoDestino }}</strong>
                                                </span>
                                                @if(!$paciente->telefono && $paciente->tutor)
                                                    <span class="badge bg-label-info rounded-pill">
                                                        <i class="ri-parent-line me-1"></i>Teléfono del tutor
                                                    </span>
                                                @endif
                                            @else
                                                <span class="badge bg-label-danger rounded-pill">
                                                    <i class="ri-error-warning-line me-1"></i>Sin número de teléfono registrado
                                                </span>
                                                <small class="text-muted">Se generará un link para copiar manualmente</small>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Botones de acción --}}
                        <div class="d-flex justify-content-between align-items-center pt-2">
                            <a href="{{ route('admin.recepcion.dashboard') }}" class="btn btn-label-secondary">
                                <i class="ri-arrow-left-line me-1"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="iniciarCuestionario">
                                    <i class="ri-send-plane-fill me-2"></i>Enviar Cuestionario
                                </span>
                                <span wire:loading wire:target="iniciarCuestionario">
                                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                    Procesando...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
