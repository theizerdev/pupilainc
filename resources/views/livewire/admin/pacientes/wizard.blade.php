<div>
@php
use Illuminate\Support\Facades\Storage;
@endphp
    @section('title', $this->getPageTitle())

    @push('styles')
    <link rel="stylesheet" href="/materialize/assets/vendor/libs/bs-stepper/bs-stepper.css" />
    <style>
        .wz-photo-zone {
            width: 180px;
            height: 180px;
            border-radius: 50%;
            border: 3px dashed var(--bs-border-color);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all .25s ease;
            overflow: hidden;
            position: relative;
        }
        .wz-photo-zone:hover { border-color: var(--bs-primary); background: rgba(var(--bs-primary-rgb), .04); }
        .wz-photo-zone img { width: 100%; height: 100%; object-fit: cover; }
        .wz-photo-zone .wz-photo-overlay {
            position: absolute;
            inset: 0;
            background: rgba(0,0,0,.45);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity .2s;
            border-radius: 50%;
        }
        .wz-photo-zone:hover .wz-photo-overlay { opacity: 1; }
        .wz-photo-remove {
            position: absolute;
            top: 4px;
            right: 4px;
            z-index: 2;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .wz-summary-label { font-size: .75rem; color: var(--bs-secondary-color); text-transform: uppercase; letter-spacing: .5px; margin-bottom: 2px; }
        .wz-summary-value { font-weight: 500; margin-bottom: .75rem; }

        /* Estilos del Stepper Moderno */
        .step-indicator {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            position: relative;
            transition: all 0.3s ease;
            border: 3px solid;
        }

        .step-indicator.pending {
            background-color: var(--bs-light);
            border-color: var(--bs-border-color);
            color: var(--bs-secondary);
        }

        .step-indicator.current {
            background-color: var(--bs-primary);
            border-color: var(--bs-primary);
            color: white;
            transform: scale(1.1);
            box-shadow: 0 0 0 8px rgba(var(--bs-primary-rgb), 0.15);
        }

        .step-indicator.completed {
            background-color: var(--bs-success);
            border-color: var(--bs-success);
            color: white;
            cursor: pointer;
        }

        .step-indicator.completed:hover {
            transform: scale(1.05);
            box-shadow: 0 0 0 6px rgba(var(--bs-success-rgb), 0.15);
        }

        .step-icon {
            font-size: 1.25rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .step-connector {
            position: absolute;
            top: 50%;
            left: -50%;
            width: 100%;
            height: 3px;
            background-color: var(--bs-border-color);
            transform: translateY(-50%);
            transition: all 0.3s ease;
            z-index: -1;
        }

        .step-connector.completed {
            background-color: var(--bs-success);
        }

        .step-label h6 {
            font-size: 0.9rem;
            margin-bottom: 0.25rem;
            transition: all 0.3s ease;
        }

        .step-label small {
            font-size: 0.75rem;
            opacity: 0.8;
        }

        /* Animaciones de entrada */
        @keyframes stepIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 8px rgba(var(--bs-primary-rgb), 0.15);
            }
            50% {
                box-shadow: 0 0 0 12px rgba(var(--bs-primary-rgb), 0.1);
            }
            100% {
                box-shadow: 0 0 0 8px rgba(var(--bs-primary-rgb), 0.15);
            }
        }

        .step-indicator {
            animation: stepIn 0.5s ease forwards;
        }

        .step-indicator.current {
            animation: pulse 2s infinite;
        }

        /* Mejoras en los conectores */
        .step-connector::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 0;
            height: 100%;
            background-color: var(--bs-success);
            transition: width 0.5s ease;
        }

        .step-connector.completed::before {
            width: 100%;
        }

        /* Efectos hover mejorados */
        .step-indicator:hover {
            transform: translateY(-2px);
        }

        .step-indicator.completed:hover {
            transform: translateY(-2px) scale(1.05);
        }

        /* Círculo de progreso */
        .progress-circle {
            position: relative;
            display: inline-block;
        }

        .progress-circle svg {
            transform: rotate(-90deg);
        }

        .progress-circle circle {
            transition: stroke-dashoffset 0.5s ease;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .step-indicator {
                width: 50px;
                height: 50px;
            }
            
            .step-icon {
                font-size: 1rem;
            }
            
            .step-label h6 {
                font-size: 0.8rem;
            }
            
            .step-label small {
                font-size: 0.7rem;
            }
        }
    </style>
    @endpush

    <div class="row">
        <div class="col-12">
            <!-- Wizard Header Moderno -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <!-- Progress Bar Superior -->
                    <div class="progress mb-4" style="height: 8px;">
                        <div class="progress-bar bg-primary" role="progressbar" 
                             style="width: {{ $progreso }}%" 
                             aria-valuenow="{{ $progreso }}" 
                             aria-valuemin="0" 
                             aria-valuemax="100">
                        </div>
                    </div>

                    <!-- Steps Navigation -->
                    <div class="d-flex justify-content-between align-items-center">
                        <!-- Paso 1: Foto -->
                        <div class="text-center flex-fill position-relative">
                            <div class=" {{ $pasoActual > 1 ? 'completed' : '' }}"></div>
                            <div class="step-indicator {{ $pasoActual >= 1 ? ($pasoActual === 1 ? 'current' : 'completed') : 'pending' }}"
                                 @if($pasoActual > 1) wire:click="irAPaso(1)" style="cursor: pointer;" @endif
                                 data-bs-toggle="tooltip" data-bs-placement="top" title="Foto del paciente">
                                <div class="step-icon">
                                    @if($pasoActual > 1)
                                        <i class="fas fa-check"></i>
                                    @else
                                        <i class="fas fa-camera"></i>
                                    @endif
                                </div>
                            </div>
                            <div class="step-label mt-2">
                                <h6 class="mb-0 {{ $pasoActual === 1 ? 'text-primary fw-bold' : 'text-muted' }}">
                                    Foto
                                </h6>
                                <small class="text-muted d-block">Imagen del paciente</small>
                            </div>
                        </div>

                        <!-- Paso 2: Datos Personales -->
                        <div class="text-center flex-fill position-relative">
                            <div class="step-connector {{ $pasoActual > 2 ? 'completed' : '' }}"></div>
                            <div class="step-indicator {{ $pasoActual >= 2 ? ($pasoActual === 2 ? 'current' : 'completed') : 'pending' }}"
                                 @if($pasoActual > 2) wire:click="irAPaso(2)" style="cursor: pointer;" @endif
                                 data-bs-toggle="tooltip" data-bs-placement="top" title="Datos personales">
                                <div class="step-icon">
                                    @if($pasoActual > 2)
                                        <i class="fas fa-check"></i>
                                    @else
                                        <i class="fas fa-user"></i>
                                    @endif
                                </div>
                            </div>
                            <div class="step-label mt-2">
                                <h6 class="mb-0 {{ $pasoActual === 2 ? 'text-primary fw-bold' : 'text-muted' }}">
                                    Datos Personales
                                </h6>
                                <small class="text-muted d-block">InformaciÃ³n del paciente</small>
                            </div>
                        </div>

                        <!-- Paso 3: Tutor (condicional) -->
                        @if($esMenorEdad)
                        <div class="text-center flex-fill position-relative">
                            <div class="step-indicator {{ $pasoActual >= 3 ? ($pasoActual === 3 ? 'current' : 'completed') : 'pending' }}"
                                 data-bs-toggle="tooltip" data-bs-placement="top" title="Datos del tutor">
                                <div class="step-icon">
                                    @if($pasoActual > 3)
                                        <i class="fas fa-check"></i>
                                    @else
                                        <i class="fas fa-user-shield"></i>
                                    @endif
                                </div>
                            </div>
                            <div class="step-label mt-2">
                                <h6 class="mb-0 {{ $pasoActual === 3 ? 'text-primary fw-bold' : 'text-muted' }}">
                                    Tutor
                                </h6>
                                <small class="text-muted d-block">Responsable legal</small>
                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- InformaciÃ³n del paso actual -->
                    <div class="text-center mt-4">
                        <div class="d-inline-flex align-items-center bg-primary bg-opacity-10 text-primary px-4 py-2 rounded-pill">
                            <div class="me-3">
                                <div class="progress-circle" style="width: 40px; height: 40px;">
                                    <svg width="40" height="40" viewBox="0 0 40 40">
                                        <circle cx="20" cy="20" r="18" fill="none" stroke="currentColor" stroke-width="2" opacity="0.2"/>
                                        <circle cx="20" cy="20" r="18" fill="none" stroke="currentColor" stroke-width="2" 
                                                stroke-dasharray="{{ 2 * pi() * 18 }}" 
                                                stroke-dashoffset="{{ 2 * pi() * 18 * (1 - $progreso / 100) }}"
                                                transform="rotate(-90 20 20)"
                                                style="transition: stroke-dashoffset 0.5s ease;"/>
                                    </svg>
                                    <div class="position-absolute top-50 start-50 translate-middle">
                                        <small class="fw-bold">{{ $progreso }}%</small>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <div class="small opacity-75">Paso {{ $pasoActual }} de {{ $pasoTotal }}</div>
                                <div class="fw-bold">
                                    @if($pasoActual === 1)
                                        Foto del paciente
                                    @elseif($pasoActual === 2)
                                        Datos personales
                                    @elseif($pasoActual === 3)
                                        Datos del tutor
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            
                       
                  

                <div class="bs-stepper-content">

                    {{-- ============================================ --}}
                    {{-- PASO 1 - FOTO --}}
                    {{-- ============================================ --}}
                    @if($pasoActual === 1)
                    <div wire:key="paso-1" class="content active">
                        <div class="content-header mb-4">
                            <h6 class="mb-0">Foto del Paciente</h6>
                            <small>Opcional. Sube o captura una foto para el expediente.</small>
                        </div>

                        <div class="row justify-content-center">
                            <div class="col-md-6 col-lg-4 text-center">
                                <label for="wz-foto-input" class="wz-photo-zone mx-auto mb-4">
                                    @if($foto)
                                        <img src="{{ $foto->temporaryUrl() }}" alt="Preview">
                                        <div class="wz-photo-overlay">
                                            <i class="ri ri-camera-line text-white ri-2x"></i>
                                        </div>
                                    @elseif($fotoExistente)
                                        <img src="{{ filter_var($fotoExistente, FILTER_VALIDATE_URL) ? $fotoExistente : Storage::url($fotoExistente) }}" alt="Foto actual">
                                        <div class="wz-photo-overlay">
                                            <i class="ri ri-camera-line text-white ri-2x"></i>
                                        </div>
                                    @else
                                        <div class="text-center text-muted">
                                            <i class="ri ri-camera-line ri-2x mb-2 d-block"></i>
                                            <small>Clic para subir</small>
                                        </div>
                                    @endif
                                </label>

                                @if($foto || $fotoExistente)
                                    <div class="mb-3">
                                        <button type="button" class="btn btn-sm btn-outline-danger rounded-pill" 
                                                wire:click="{{ $foto ? "\$set('foto', null)" : 'eliminarFoto' }}">
                                            <i class="ri ri-delete-bin-line me-1"></i> Quitar foto
                                        </button>
                                    </div>
                                @endif

                                <input type="file" wire:model="foto" id="wz-foto-input" accept="image/*" class="d-none">

                                <div wire:loading wire:target="foto" class="mb-3">
                                    <div class="d-inline-flex align-items-center text-primary">
                                        <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                                        <small>Cargando imagen...</small>
                                    </div>
                                </div>

                                <p class="text-muted small mb-0">
                                    <i class="ri ri-information-line me-1"></i>
                                    JPG, PNG o GIF. MÃ¡ximo 2MB.
                                </p>

                                @error('foto')
                                    <div class="alert alert-danger alert-dismissible mt-3 py-2 px-3" role="alert">
                                        <small>{{ $message }}</small>
                                        <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="alert"></button>
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-12 d-flex justify-content-between mt-5">
                            <a href="{{ route('admin.pacientes.index') }}" class="btn btn-outline-secondary">
                                <i class="ri ri-arrow-left-line me-1"></i>
                                <span class="align-middle d-sm-inline-block d-none">Volver</span>
                            </a>
                            <button type="button" class="btn btn-primary" wire:click="siguientePaso"
                                    wire:loading.attr="disabled" wire:target="siguientePaso">
                                <span wire:loading.remove wire:target="siguientePaso">
                                    <span class="align-middle d-sm-inline-block d-none me-1">Siguiente</span>
                                    <i class="ri ri-arrow-right-line"></i>
                                </span>
                                <span wire:loading wire:target="siguientePaso">
                                    <span class="spinner-border spinner-border-sm me-1" role="status"></span>
                                </span>
                            </button>
                        </div>
                    </div>
                    @endif

                    {{-- ============================================ --}}
                    {{-- PASO 2 - DATOS PERSONALES --}}
                    {{-- ============================================ --}}
                    @if($pasoActual === 2)
                    <div wire:key="paso-2" class="content active">
                        <div class="content-header mb-4">
                            <h6 class="mb-0">Datos Personales</h6>
                            <small>Completa la informaciÃ³n del paciente.</small>
                        </div>

                        <div class="row g-5">
                            {{-- IdentificaciÃ³n --}}
                            <div class="col-sm-6">
                                <div class="form-floating form-floating-outline">
                                    <input type="text" class="form-control @error('nombres') is-invalid @enderror"
                                           wire:model="nombres" placeholder="Juan Carlos" id="wz-nombres">
                                    <label for="wz-nombres">Nombres <span class="text-danger">*</span></label>
                                    @error('nombres') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-floating form-floating-outline">
                                    <input type="text" class="form-control @error('apellidos') is-invalid @enderror"
                                           wire:model="apellidos" placeholder="PÃ©rez GarcÃ­a" id="wz-apellidos">
                                    <label for="wz-apellidos">Apellidos <span class="text-danger">*</span></label>
                                    @error('apellidos') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-floating form-floating-outline">
                                    <input type="text" class="form-control @error('documento_identidad') is-invalid @enderror"
                                           wire:model.live.debounce.500ms="documento_identidad" placeholder="12345678" id="wz-doc">
                                    <label for="wz-doc">Documento de Identidad <span class="text-danger">*</span></label>
                                    <div wire:loading wire:target="documento_identidad">
                                        <small class="text-muted"><span class="spinner-border spinner-border-sm me-1"></span>Verificando...</small>
                                    </div>
                                    @error('documento_identidad') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-floating form-floating-outline">
                                    <input type="text" class="form-control @error('nickname') is-invalid @enderror"
                                           wire:model="nickname" placeholder="Juanito" id="wz-nickname">
                                    <label for="wz-nickname">Apodo / Nombre preferido</label>
                                    @error('nickname') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            {{-- Fecha nacimiento y Edad --}}
                            <div class="col-sm-6">
                                <div class="form-floating form-floating-outline">
                                    <input type="date" class="form-control @error('fecha_nacimiento') is-invalid @enderror"
                                           wire:model.live="fecha_nacimiento" max="{{ date('Y-m-d') }}" id="wz-fnac">
                                    <label for="wz-fnac">Fecha de Nacimiento <span class="text-danger">*</span></label>
                                    @error('fecha_nacimiento') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                @if($edadFormateada)
                                    <div class="mt-2">
                                        <span class="badge {{ $esMenorEdad ? 'bg-label-warning' : 'bg-label-success' }} rounded-pill">
                                            <i class="ri {{ $esMenorEdad ? 'ri-emotion-line' : 'ri-user-line' }} me-1"></i>{{ $edadFormateada }}
                                            @if($esMenorEdad) &mdash; Menor de edad @endif
                                        </span>
                                    </div>
                                @endif
                            </div>

                            <div class="col-sm-6">
                                <div class="form-floating form-floating-outline">
                                    <select class="form-select @error('genero') is-invalid @enderror" wire:model="genero" id="wz-genero">
                                        <option value="">Seleccione...</option>
                                        @foreach($generos as $g)
                                            <option value="{{ $g }}">{{ $g }}</option>
                                        @endforeach
                                    </select>
                                    <label for="wz-genero">GÃ©nero</label>
                                    @error('genero') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            {{-- Contacto --}}
                            <div class="col-12">
                                <hr class="my-0">
                            </div>

                            <div class="col-sm-6">
                                <div class="form-floating form-floating-outline">
                                    <input type="text" class="form-control @error('telefono') is-invalid @enderror"
                                           wire:model="telefono" placeholder="+58 412 1234567" id="wz-tel">
                                    <label for="wz-tel"><i class="ri ri-phone-line me-1"></i>TelÃ©fono</label>
                                    @error('telefono') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-floating form-floating-outline">
                                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                                           wire:model="email" placeholder="paciente@email.com" id="wz-email">
                                    <label for="wz-email"><i class="ri ri-mail-line me-1"></i>Correo ElectrÃ³nico</label>
                                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="form-floating form-floating-outline">
                                    <textarea class="form-control @error('direccion') is-invalid @enderror"
                                              wire:model="direccion" placeholder="Av. Principal, Edificio..." id="wz-dir" style="height: 80px"></textarea>
                                    <label for="wz-dir"><i class="ri ri-map-pin-line me-1"></i>DirecciÃ³n</label>
                                    @error('direccion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            {{-- InformaciÃ³n adicional --}}
                            <div class="col-12">
                                <hr class="my-0">
                            </div>

                            <div class="col-sm-4">
                                <div class="form-floating form-floating-outline">
                                    <select class="form-select @error('estado_civil') is-invalid @enderror" wire:model="estado_civil" id="wz-ecivil">
                                        <option value="">Seleccione...</option>
                                        @foreach($estadosCiviles as $ec)
                                            <option value="{{ $ec }}">{{ $ec }}</option>
                                        @endforeach
                                    </select>
                                    <label for="wz-ecivil">Estado Civil</label>
                                    @error('estado_civil') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="col-sm-4">
                                <div class="form-floating form-floating-outline">
                                    <input type="text" class="form-control @error('ocupacion') is-invalid @enderror"
                                           wire:model="ocupacion" placeholder="Ingeniero, Docente..." id="wz-ocup">
                                    <label for="wz-ocup">OcupaciÃ³n</label>
                                    @error('ocupacion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="col-sm-4">
                                <div class="form-floating form-floating-outline">
                                    <input type="text" class="form-control @error('nacionalidad') is-invalid @enderror"
                                           wire:model="nacionalidad" placeholder="Venezolano/a" id="wz-nac">
                                    <label for="wz-nac">Nacionalidad</label>
                                    @error('nacionalidad') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            @if(auth()->user()->hasRole('Super Administrador'))
                            <div class="col-sm-6">
                                <div class="form-floating form-floating-outline">
                                    <select class="form-select @error('empresa_id') is-invalid @enderror" wire:model="empresa_id" id="wz-empresa">
                                        <option value="">Seleccione...</option>
                                        @foreach($empresas as $empresa)
                                            <option value="{{ $empresa->id }}">{{ $empresa->nombre }}</option>
                                        @endforeach
                                    </select>
                                    <label for="wz-empresa">Empresa <span class="text-danger">*</span></label>
                                    @error('empresa_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-floating form-floating-outline">
                                    <select class="form-select @error('sucursal_id') is-invalid @enderror" wire:model="sucursal_id" id="wz-sucursal">
                                        <option value="">Seleccione...</option>
                                        @foreach($sucursales as $sucursal)
                                            <option value="{{ $sucursal->id }}">{{ $sucursal->nombre }}</option>
                                        @endforeach
                                    </select>
                                    <label for="wz-sucursal">Sucursal <span class="text-danger">*</span></label>
                                    @error('sucursal_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            @endif
                        </div>

                        @if($esMenorEdad)
                        <div class="alert alert-warning d-flex align-items-center mt-4 py-3" role="alert">
                            <i class="ri ri-error-warning-line ri-lg me-3"></i>
                            <div>
                                <strong>Paciente menor de edad.</strong> En el siguiente paso se solicitarÃ¡n los datos del tutor o responsable legal.
                            </div>
                        </div>
                        @endif

                        {{-- Resumen (solo si es Ãºltimo paso = adulto sin tutor) --}}
                        @if(!$esMenorEdad && ($nombres || $apellidos))
                        <div class="card bg-label-secondary border-0 mt-4">
                            <div class="card-body py-3">
                                <div class="d-flex align-items-center mb-3">
                                    <i class="ri ri-file-list-3-line ri-lg me-2 text-primary"></i>
                                    <h6 class="mb-0">Resumen</h6>
                                </div>
                                <div class="row">
                                    <div class="col-sm-4">
                                        <div class="wz-summary-label">Paciente</div>
                                        <div class="wz-summary-value">{{ $nombres }} {{ $apellidos }}</div>
                                        <div class="wz-summary-label">Documento</div>
                                        <div class="wz-summary-value">{{ $documento_identidad ?: 'â€”' }}</div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="wz-summary-label">Fecha Nac.</div>
                                        <div class="wz-summary-value">{{ $fecha_nacimiento ? \Carbon\Carbon::parse($fecha_nacimiento)->format('d/m/Y') : 'â€”' }}</div>
                                        <div class="wz-summary-label">Edad</div>
                                        <div class="wz-summary-value">{{ $edadFormateada ?? 'â€”' }}</div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="wz-summary-label">TelÃ©fono</div>
                                        <div class="wz-summary-value">{{ $telefono ?: 'â€”' }}</div>
                                        <div class="wz-summary-label">Email</div>
                                        <div class="wz-summary-value">{{ $email ?: 'â€”' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <div class="col-12 d-flex justify-content-between mt-5">
                            <button type="button" class="btn btn-outline-secondary" wire:click="pasoAnterior">
                                <i class="ri ri-arrow-left-line me-1"></i>
                                <span class="align-middle d-sm-inline-block d-none">Anterior</span>
                            </button>
                            @if($pasoActual === $pasoTotal)
                                <button type="button" class="btn btn-success" wire:click="guardar"
                                        wire:loading.attr="disabled" wire:target="guardar">
                                    <span wire:loading.remove wire:target="guardar">
                                        <i class="ri ri-save-line me-1"></i>
                                        <span class="align-middle">{{ $modoEdicion ? 'Actualizar' : 'Guardar Paciente' }}</span>
                                    </span>
                                    <span wire:loading wire:target="guardar">
                                        <span class="spinner-border spinner-border-sm me-1" role="status"></span>Guardando...
                                    </span>
                                </button>
                            @else
                                <button type="button" class="btn btn-primary" wire:click="siguientePaso"
                                        wire:loading.attr="disabled" wire:target="siguientePaso">
                                    <span wire:loading.remove wire:target="siguientePaso">
                                        <span class="align-middle d-sm-inline-block d-none me-1">Siguiente</span>
                                        <i class="ri ri-arrow-right-line"></i>
                                    </span>
                                    <span wire:loading wire:target="siguientePaso">
                                        <span class="spinner-border spinner-border-sm me-1" role="status"></span>
                                    </span>
                                </button>
                            @endif
                        </div>
                    </div>
                    @endif

                    {{-- ============================================ --}}
                    {{-- PASO 3 - TUTOR (solo menores) --}}
                    {{-- ============================================ --}}
                    @if($pasoActual === 3 && $esMenorEdad)
                    <div wire:key="paso-3" class="content active">
                        <div class="content-header mb-4">
                            <h6 class="mb-0">Datos del Tutor / Responsable Legal</h6>
                            <small>InformaciÃ³n de contacto del responsable del paciente menor de edad.</small>
                        </div>

                        <div class="row g-5">
                            <div class="col-sm-6">
                                <div class="form-floating form-floating-outline">
                                    <input type="text" class="form-control @error('tutor.nombres') is-invalid @enderror"
                                           wire:model="tutor.nombres" placeholder="MarÃ­a Elena" id="wz-tut-nom">
                                    <label for="wz-tut-nom">Nombres del Tutor <span class="text-danger">*</span></label>
                                    @error('tutor.nombres') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-floating form-floating-outline">
                                    <input type="text" class="form-control @error('tutor.apellidos') is-invalid @enderror"
                                           wire:model="tutor.apellidos" placeholder="RodrÃ­guez LÃ³pez" id="wz-tut-ape">
                                    <label for="wz-tut-ape">Apellidos del Tutor <span class="text-danger">*</span></label>
                                    @error('tutor.apellidos') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-floating form-floating-outline">
                                    <input type="text" class="form-control @error('tutor.documento_identidad') is-invalid @enderror"
                                           wire:model="tutor.documento_identidad" placeholder="87654321" id="wz-tut-doc">
                                    <label for="wz-tut-doc">Documento de Identidad <span class="text-danger">*</span></label>
                                    @error('tutor.documento_identidad') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-floating form-floating-outline">
                                    <select class="form-select @error('tutor.parentesco') is-invalid @enderror" wire:model="tutor.parentesco" id="wz-tut-par">
                                        <option value="">Seleccione...</option>
                                        @foreach($parentescos as $p)
                                            <option value="{{ $p }}">{{ $p }}</option>
                                        @endforeach
                                    </select>
                                    <label for="wz-tut-par">Parentesco <span class="text-danger">*</span></label>
                                    @error('tutor.parentesco') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="col-12">
                                <hr class="my-0">
                            </div>

                            <div class="col-sm-6">
                                <div class="form-floating form-floating-outline">
                                    <input type="text" class="form-control @error('tutor.telefono') is-invalid @enderror"
                                           wire:model="tutor.telefono" placeholder="+58 412 9876543" id="wz-tut-tel">
                                    <label for="wz-tut-tel"><i class="ri ri-phone-line me-1"></i>TelÃ©fono <span class="text-danger">*</span></label>
                                    @error('tutor.telefono') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-floating form-floating-outline">
                                    <input type="email" class="form-control @error('tutor.email') is-invalid @enderror"
                                           wire:model="tutor.email" placeholder="tutor@email.com" id="wz-tut-email">
                                    <label for="wz-tut-email"><i class="ri ri-mail-line me-1"></i>Correo ElectrÃ³nico</label>
                                    @error('tutor.email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="form-floating form-floating-outline">
                                    <textarea class="form-control @error('tutor.direccion') is-invalid @enderror"
                                              wire:model="tutor.direccion" placeholder="DirecciÃ³n del tutor..." id="wz-tut-dir" style="height: 80px"></textarea>
                                    <label for="wz-tut-dir"><i class="ri ri-map-pin-line me-1"></i>DirecciÃ³n</label>
                                    @error('tutor.direccion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info d-flex align-items-center mt-4 py-3" role="alert">
                            <i class="ri ri-information-line ri-lg me-3"></i>
                            <div>El tutor/responsable debe presentar su documento de identidad vÃ¡lido en la primera cita.</div>
                        </div>

                        {{-- Resumen completo --}}
                        @if($nombres || $apellidos)
                        <div class="card bg-label-secondary border-0 mt-4">
                            <div class="card-body py-3">
                                <div class="d-flex align-items-center mb-3">
                                    <i class="ri ri-file-list-3-line ri-lg me-2 text-primary"></i>
                                    <h6 class="mb-0">Resumen del Registro</h6>
                                </div>
                                <div class="row">
                                    <div class="col-sm-4">
                                        <div class="wz-summary-label">Paciente</div>
                                        <div class="wz-summary-value">{{ $nombres }} {{ $apellidos }}</div>
                                        <div class="wz-summary-label">Documento</div>
                                        <div class="wz-summary-value">{{ $documento_identidad ?: 'â€”' }}</div>
                                        <div class="wz-summary-label">Fecha Nac.</div>
                                        <div class="wz-summary-value">{{ $fecha_nacimiento ? \Carbon\Carbon::parse($fecha_nacimiento)->format('d/m/Y') : 'â€”' }} ({{ $edadFormateada ?? 'â€”' }})</div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="wz-summary-label">TelÃ©fono</div>
                                        <div class="wz-summary-value">{{ $telefono ?: 'â€”' }}</div>
                                        <div class="wz-summary-label">Email</div>
                                        <div class="wz-summary-value">{{ $email ?: 'â€”' }}</div>
                                        <div class="wz-summary-label">GÃ©nero</div>
                                        <div class="wz-summary-value">{{ $genero ?: 'â€”' }}</div>
                                    </div>
                                    <div class="col-sm-4">
                                        @if(!empty($tutor['nombres']))
                                        <div class="wz-summary-label">Tutor</div>
                                        <div class="wz-summary-value">{{ $tutor['nombres'] }} {{ $tutor['apellidos'] }}</div>
                                        <div class="wz-summary-label">Parentesco</div>
                                        <div class="wz-summary-value">{{ $tutor['parentesco'] ?: 'â€”' }}</div>
                                        <div class="wz-summary-label">Tel. Tutor</div>
                                        <div class="wz-summary-value">{{ $tutor['telefono'] ?: 'â€”' }}</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <div class="col-12 d-flex justify-content-between mt-5">
                            <button type="button" class="btn btn-outline-secondary" wire:click="pasoAnterior">
                                <i class="ri ri-arrow-left-line me-1"></i>
                                <span class="align-middle d-sm-inline-block d-none">Anterior</span>
                            </button>
                            <button type="button" class="btn btn-success" wire:click="guardar"
                                    wire:loading.attr="disabled" wire:target="guardar">
                                <span wire:loading.remove wire:target="guardar">
                                    <i class="ri ri-save-line me-1"></i>
                                    <span class="align-middle">{{ $modoEdicion ? 'Actualizar' : 'Guardar Paciente' }}</span>
                                </span>
                                <span wire:loading wire:target="guardar">
                                    <span class="spinner-border spinner-border-sm me-1" role="status"></span>Guardando...
                                </span>
                            </button>
                        </div>
                    </div>
                    @endif

                </div>
            </div>

        </div>
    </div>

@push('scripts')
<script>
    // Inicializar tooltips de Bootstrap
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });

    // Reinicializar tooltips cuando Livewire actualiza el DOM
    Livewire.hook('message.processed', (message, component) => {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });

    // Transiciones suaves al cambiar de paso
    Livewire.on('paso-cambiando', (event) => {
        const paso = event[0].paso;
        
        // Animar el indicador de progreso
        const progressBar = document.querySelector('.progress-bar');
        if (progressBar) {
            progressBar.style.transition = 'width 0.5s ease';
        }
        
        // Animar el círculo de progreso
        const progressCircle = document.querySelector('.progress-circle circle:last-child');
        if (progressCircle) {
            setTimeout(() => {
                progressCircle.style.transition = 'stroke-dashoffset 0.5s ease';
            }, 100);
        }
        
        // Efecto de celebración al completar un paso
        const stepIndicators = document.querySelectorAll('.step-indicator');
        stepIndicators.forEach((indicator, index) => {
            if (indicator.classList.contains('completed') && !indicator.dataset.animated) {
                indicator.dataset.animated = 'true';
                indicator.style.animation = 'stepComplete 0.6s ease';
            }
        });
    });

    // Animación de celebración al completar un paso
    const style = document.createElement('style');
    style.textContent = `
        @keyframes stepComplete {
            0% { transform: scale(1); }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); }
        }
    `;
    document.head.appendChild(style);
</script>
@endpush

</div>