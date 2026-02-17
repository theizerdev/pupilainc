<div>
    @php
        use Illuminate\Support\Facades\Storage;
    @endphp

    @section('title', $this->getPageTitle())

    @push('styles')
        <link rel="stylesheet" href="/materialize/assets/vendor/libs/bs-stepper/bs-stepper.css" />
        <style>
            .wz-shell { max-width: 1200px; margin: 0 auto; }
            .wz-card { border-radius: 14px; }
            .wz-topbar { gap: 12px; }
            .wz-progress { background: rgba(var(--bs-primary-rgb), .08); }

            .wz-step { width: 44px; height: 44px; border-radius: 999px; display: inline-flex; align-items: center; justify-content: center; border: 1px solid var(--bs-border-color); background: var(--bs-body-bg); color: var(--bs-secondary); transition: all .2s ease; }
            .wz-step.is-current { background: var(--bs-primary); border-color: var(--bs-primary); color: #fff; box-shadow: 0 0 0 6px rgba(var(--bs-primary-rgb), .12); }
            .wz-step.is-done { background: var(--bs-success); border-color: var(--bs-success); color: #fff; cursor: pointer; }
            .wz-step.is-done:hover { transform: translateY(-1px); box-shadow: 0 0 0 6px rgba(var(--bs-success-rgb), .12); }
            .wz-step-label { font-size: .85rem; line-height: 1.1; }
            .wz-step-hint { font-size: .72rem; color: var(--bs-secondary-color); }

            .wz-photo { width: 180px; height: 180px; border-radius: 999px; border: 2px dashed var(--bs-border-color); display: flex; align-items: center; justify-content: center; overflow: hidden; background: rgba(var(--bs-primary-rgb), .02); transition: all .2s ease; cursor: pointer; position: relative; }
            .wz-photo:hover { border-color: var(--bs-primary); background: rgba(var(--bs-primary-rgb), .05); }
            .wz-photo img { width: 100%; height: 100%; object-fit: cover; }
            .wz-photo-overlay { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; background: rgba(0,0,0,.45); opacity: 0; transition: opacity .2s ease; }
            .wz-photo:hover .wz-photo-overlay { opacity: 1; }

            .wz-section-title { font-weight: 600; }
            .wz-summary { position: sticky; top: 90px; }
            .wz-summary-k { font-size: .72rem; color: var(--bs-secondary-color); text-transform: uppercase; letter-spacing: .04em; margin-bottom: 2px; }
            .wz-summary-v { font-weight: 500; margin-bottom: .75rem; }
            .wz-divider { height: 1px; background: rgba(var(--bs-body-color-rgb), .08); }
        </style>
    @endpush

    <div >
        <div class="d-flex flex-wrap align-items-center justify-content-between wz-topbar mb-4">
            <div>
                <h4 class="mb-1">
                    <i class="ri ri-user-3-line me-2 text-primary"></i>{{ $this->getPageTitle() }}
                </h4>
                <div class="text-muted small">Registro guiado del paciente con validación por pasos.</div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('admin.pacientes.index') }}" class="btn btn-outline-secondary">
                    <i class="ri ri-arrow-left-line me-1"></i>Volver
                </a>
            </div>
        </div>

        <div class="card border-0 shadow-sm wz-card mb-4">
            <div class="card-body p-4">
                <div class="progress wz-progress mb-4" style="height: 8px;">
                    <div class="progress-bar bg-primary" role="progressbar"
                         style="width: {{ $progreso }}%"
                         aria-valuenow="{{ $progreso }}"
                         aria-valuemin="0"
                         aria-valuemax="100"></div>
                </div>

                <div class="row g-3 align-items-start">
                    <div class="col-12 col-md-4">
                        <div class="d-flex align-items-start gap-3">
                            <div class="wz-step {{ $pasoActual === 1 ? 'is-current' : ($pasoActual > 1 ? 'is-done' : '') }}"
                                 @if($pasoActual > 1) wire:click="irAPaso(1)" @endif
                                 @if($pasoActual > 1) role="button" @endif>
                                @if($pasoActual > 1)
                                    <i class="ri ri-check-line"></i>
                                @else
                                    <i class="ri ri-camera-line"></i>
                                @endif
                            </div>
                            <div class="flex-grow-1">
                                <div class="wz-step-label {{ $pasoActual === 1 ? 'text-primary fw-semibold' : '' }}">Foto</div>
                                <div class="wz-step-hint">Captura o sube una imagen</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-4">
                        <div class="d-flex align-items-start gap-3">
                            <div class="wz-step {{ $pasoActual === 2 ? 'is-current' : ($pasoActual > 2 ? 'is-done' : ($pasoActual > 1 ? '' : '')) }}"
                                 @if($pasoActual > 2) wire:click="irAPaso(2)" @endif
                                 @if($pasoActual > 2) role="button" @endif>
                                @if($pasoActual > 2)
                                    <i class="ri ri-check-line"></i>
                                @else
                                    <i class="ri ri-user-line"></i>
                                @endif
                            </div>
                            <div class="flex-grow-1">
                                <div class="wz-step-label {{ $pasoActual === 2 ? 'text-primary fw-semibold' : '' }}">Datos personales</div>
                                <div class="wz-step-hint">Identidad y contacto</div>
                            </div>
                        </div>
                    </div>

                    @if($esMenorEdad)
                        <div class="col-12 col-md-4">
                            <div class="d-flex align-items-start gap-3">
                                <div class="wz-step {{ $pasoActual === 3 ? 'is-current' : ($pasoActual > 3 ? 'is-done' : ($pasoActual > 2 ? '' : '')) }}">
                                    @if($pasoActual > 3)
                                        <i class="ri ri-check-line"></i>
                                    @else
                                        <i class="ri ri-shield-user-line"></i>
                                    @endif
                                </div>
                                <div class="flex-grow-1">
                                    <div class="wz-step-label {{ $pasoActual === 3 ? 'text-primary fw-semibold' : '' }}">Tutor</div>
                                    <div class="wz-step-hint">Responsable legal</div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="d-flex flex-wrap align-items-center justify-content-between mt-4">
                    <div class="d-inline-flex align-items-center gap-2">
                        <span class="badge bg-label-primary rounded-pill px-3 py-2">
                            Paso {{ $pasoActual }} de {{ $pasoTotal }}
                        </span>
                        <span class="text-muted small">{{ $progreso }}%</span>
                    </div>
                    @if($edadFormateada)
                        <span class="badge {{ $esMenorEdad ? 'bg-label-warning' : 'bg-label-success' }} rounded-pill px-3 py-2">
                            <i class="ri {{ $esMenorEdad ? 'ri-emotion-line' : 'ri-user-line' }} me-1"></i>{{ $edadFormateada }}
                            @if($esMenorEdad) &mdash; Menor de edad @endif
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-12 col-lg-8">
                <div class="card border-0 shadow-sm wz-card">
                    <div class="card-body p-4">
                        @if($pasoActual === 1)
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div>
                                    <h5 class="mb-1 wz-section-title">Foto del paciente</h5>
                                    <div class="text-muted small">Opcional. Puedes tomarla con cámara (webcam/USB) o subirla.</div>
                                </div>
                                @if($foto || $fotoExistente)
                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                            wire:click="{{ $foto ? "\$set('foto', null)" : 'eliminarFoto' }}">
                                        <i class="ri ri-delete-bin-6-line me-1"></i>Quitar
                                    </button>
                                @endif
                            </div>

                            <div class="d-flex flex-column align-items-center text-center">
                                <label for="wz-foto-input" class="wz-photo mb-3">
                                    @if($foto)
                                        <img src="{{ $foto->temporaryUrl() }}" alt="Foto del paciente">
                                        <div class="wz-photo-overlay"><i class="ri ri-camera-line ri-2x text-white"></i></div>
                                    @elseif($fotoExistente)
                                        <img src="{{ filter_var($fotoExistente, FILTER_VALIDATE_URL) ? $fotoExistente : Storage::url($fotoExistente) }}" alt="Foto del paciente">
                                        <div class="wz-photo-overlay"><i class="ri ri-camera-line ri-2x text-white"></i></div>
                                    @else
                                        <div class="text-muted">
                                            <i class="ri ri-camera-line ri-2x d-block mb-2"></i>
                                            <div class="small">Clic para capturar o subir</div>
                                        </div>
                                    @endif
                                </label>

                                <input type="file" wire:model="foto" id="wz-foto-input" accept="image/*" class="d-none">

                                <div class="d-flex flex-wrap justify-content-center gap-2 mb-2">
                                    <button type="button" class="btn btn-sm btn-primary" data-wz-camera-start>
                                        <i class="ri ri-camera-3-line me-1"></i>Tomar foto
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-primary" data-wz-upload-open>
                                        <i class="ri ri-upload-2-line me-1"></i>Subir archivo
                                    </button>
                                </div>

                                <div id="wz-camera-panel" class="w-100 mt-3" style="max-width: 520px; display: none;">
                                    <div class="card border-0 shadow-sm">
                                        <div class="card-body p-3">
                                            <div class="ratio ratio-16x9 rounded overflow-hidden bg-dark">
                                                <video id="wz-camera-video" autoplay playsinline muted></video>
                                            </div>
                                            <canvas id="wz-camera-canvas" class="d-none"></canvas>
                                            <div id="wz-camera-error" class="alert alert-danger mt-3 mb-0 py-2 px-3 d-none" role="alert"></div>
                                            <div class="d-flex justify-content-end gap-2 mt-3">
                                                <button type="button" class="btn btn-outline-secondary btn-sm" data-wz-camera-stop>
                                                    Cerrar
                                                </button>
                                                <button type="button" class="btn btn-success btn-sm" data-wz-camera-capture>
                                                    <i class="ri ri-camera-lens-line me-1"></i>Capturar
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="text-muted small">
                                    JPG, PNG o GIF. Máximo 2MB.
                                </div>

                                <div wire:loading wire:target="foto" class="mt-3">
                                    <div class="d-inline-flex align-items-center text-primary">
                                        <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                        <small>Cargando imagen...</small>
                                    </div>
                                </div>

                                @error('foto')
                                    <div class="alert alert-danger mt-3 mb-0 py-2 px-3" role="alert">
                                        <small>{{ $message }}</small>
                                    </div>
                                @enderror
                            </div>
                        @endif

                        @if($pasoActual === 2)
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div>
                                    <h5 class="mb-1 wz-section-title">Datos personales</h5>
                                    <div class="text-muted small">Completa la información principal del paciente.</div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <i class="ri ri-id-card-line text-primary"></i>
                                    <div class="wz-section-title">Identificación</div>
                                </div>

                                <div class="row g-3">
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
                                                   wire:model="apellidos" placeholder="Pérez García" id="wz-apellidos">
                                            <label for="wz-apellidos">Apellidos <span class="text-danger">*</span></label>
                                            @error('apellidos') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-floating form-floating-outline">
                                            <input type="text" class="form-control @error('documento_identidad') is-invalid @enderror"
                                                   wire:model.live.debounce.500ms="documento_identidad" placeholder="12345678" id="wz-doc">
                                            <label for="wz-doc">Documento de Identidad <span class="text-danger">*</span></label>
                                            <div wire:loading wire:target="documento_identidad" class="mt-1">
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
                                    <div class="col-sm-6">
                                        <div class="form-floating form-floating-outline">
                                            <input type="date" class="form-control @error('fecha_nacimiento') is-invalid @enderror"
                                                   wire:model.live="fecha_nacimiento" max="{{ date('Y-m-d') }}" id="wz-fnac">
                                            <label for="wz-fnac">Fecha de Nacimiento <span class="text-danger">*</span></label>
                                            @error('fecha_nacimiento') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-floating form-floating-outline">
                                            <select class="form-select @error('genero') is-invalid @enderror" wire:model="genero" id="wz-genero">
                                                <option value="">Seleccione...</option>
                                                @foreach($generos as $g)
                                                    <option value="{{ $g }}">{{ $g }}</option>
                                                @endforeach
                                            </select>
                                            <label for="wz-genero">Género</label>
                                            @error('genero') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <i class="ri ri-contacts-line text-primary"></i>
                                    <div class="wz-section-title">Contacto</div>
                                </div>

                                <div class="row g-3">
                                    <div class="col-sm-6">
                                        <div class="form-floating form-floating-outline">
                                            <input type="text" class="form-control @error('telefono') is-invalid @enderror"
                                                   wire:model="telefono" placeholder="+58 412 1234567" id="wz-tel">
                                            <label for="wz-tel"><i class="ri ri-phone-line me-1"></i>Teléfono</label>
                                            @error('telefono') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-floating form-floating-outline">
                                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                                   wire:model="email" placeholder="paciente@email.com" id="wz-email">
                                            <label for="wz-email"><i class="ri ri-mail-line me-1"></i>Correo Electrónico</label>
                                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-floating form-floating-outline">
                                            <textarea class="form-control @error('direccion') is-invalid @enderror"
                                                      wire:model="direccion" placeholder="Av. Principal, Edificio..." id="wz-dir" style="height: 88px"></textarea>
                                            <label for="wz-dir"><i class="ri ri-map-pin-line me-1"></i>Dirección</label>
                                            @error('direccion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-0">
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <i class="ri ri-more-2-line text-primary"></i>
                                    <div class="wz-section-title">Información adicional</div>
                                </div>

                                <div class="row g-3">
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
                                            <label for="wz-ocup">Ocupación</label>
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
                                    <div class="alert alert-warning d-flex align-items-center mt-4 mb-0 py-3" role="alert">
                                        <i class="ri ri-error-warning-line ri-lg me-3"></i>
                                        <div><strong>Paciente menor de edad.</strong> En el siguiente paso se solicitarán los datos del tutor o responsable legal.</div>
                                    </div>
                                @endif
                            </div>
                        @endif

                        @if($pasoActual === 3 && $esMenorEdad)
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div>
                                    <h5 class="mb-1 wz-section-title">Datos del tutor / responsable legal</h5>
                                    <div class="text-muted small">Información de contacto del responsable del paciente menor de edad.</div>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <div class="form-floating form-floating-outline">
                                        <input type="text" class="form-control @error('tutor.nombres') is-invalid @enderror"
                                               wire:model="tutor.nombres" placeholder="María Elena" id="wz-tut-nom">
                                        <label for="wz-tut-nom">Nombres del Tutor <span class="text-danger">*</span></label>
                                        @error('tutor.nombres') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-floating form-floating-outline">
                                        <input type="text" class="form-control @error('tutor.apellidos') is-invalid @enderror"
                                               wire:model="tutor.apellidos" placeholder="Rodríguez López" id="wz-tut-ape">
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
                                <div class="col-sm-6">
                                    <div class="form-floating form-floating-outline">
                                        <input type="text" class="form-control @error('tutor.telefono') is-invalid @enderror"
                                               wire:model="tutor.telefono" placeholder="+58 412 9876543" id="wz-tut-tel">
                                        <label for="wz-tut-tel"><i class="ri ri-phone-line me-1"></i>Teléfono <span class="text-danger">*</span></label>
                                        @error('tutor.telefono') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-floating form-floating-outline">
                                        <input type="email" class="form-control @error('tutor.email') is-invalid @enderror"
                                               wire:model="tutor.email" placeholder="tutor@email.com" id="wz-tut-email">
                                        <label for="wz-tut-email"><i class="ri ri-mail-line me-1"></i>Correo Electrónico</label>
                                        @error('tutor.email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-floating form-floating-outline">
                                        <textarea class="form-control @error('tutor.direccion') is-invalid @enderror"
                                                  wire:model="tutor.direccion" placeholder="Dirección del tutor..." id="wz-tut-dir" style="height: 88px"></textarea>
                                        <label for="wz-tut-dir"><i class="ri ri-map-pin-line me-1"></i>Dirección</label>
                                        @error('tutor.direccion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-info d-flex align-items-center mt-4 mb-0 py-3" role="alert">
                                <i class="ri ri-information-line ri-lg me-3"></i>
                                <div>El tutor/responsable debe presentar su documento de identidad válido en la primera cita.</div>
                            </div>
                        @endif
                    </div>

                    <div class="card-footer bg-white border-0 p-4 pt-0">
                        <div class="d-flex justify-content-between">
                            @if($pasoActual === 1)
                                <a href="{{ route('admin.pacientes.index') }}" class="btn btn-outline-secondary">
                                    <i class="ri ri-arrow-left-line me-1"></i>Volver
                                </a>
                            @else
                                <button type="button" class="btn btn-outline-secondary" wire:click="pasoAnterior">
                                    <i class="ri ri-arrow-left-line me-1"></i>Anterior
                                </button>
                            @endif

                            @if($pasoActual === $pasoTotal)
                                <button type="button" class="btn btn-success" wire:click="guardar"
                                        wire:loading.attr="disabled" wire:target="guardar">
                                    <span wire:loading.remove wire:target="guardar">
                                        <i class="ri ri-save-line me-1"></i>{{ $modoEdicion ? 'Actualizar' : 'Guardar Paciente' }}
                                    </span>
                                    <span wire:loading wire:target="guardar">
                                        <span class="spinner-border spinner-border-sm me-1" role="status"></span>Guardando...
                                    </span>
                                </button>
                            @else
                                <button type="button" class="btn btn-primary" wire:click="siguientePaso"
                                        wire:loading.attr="disabled" wire:target="siguientePaso">
                                    <span wire:loading.remove wire:target="siguientePaso">
                                        Siguiente <i class="ri ri-arrow-right-line ms-1"></i>
                                    </span>
                                    <span wire:loading wire:target="siguientePaso">
                                        <span class="spinner-border spinner-border-sm me-1" role="status"></span>
                                    </span>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-4">
                <div class="card border-0 shadow-sm wz-card wz-summary">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h6 class="mb-0"><i class="ri ri-file-list-3-line me-1 text-primary"></i>Resumen</h6>
                            <span class="badge bg-label-primary rounded-pill">{{ $modoEdicion ? 'Edición' : 'Nuevo' }}</span>
                        </div>
                        <div class="wz-divider mb-3"></div>

                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="avatar avatar-md">
                                <span class="avatar-initial rounded-circle bg-primary-subtle text-primary">
                                    {{ mb_substr(($nombres ?: 'P'), 0, 1) }}{{ mb_substr(($apellidos ?: 'A'), 0, 1) }}
                                </span>
                            </div>
                            <div class="min-w-0">
                                <div class="fw-semibold text-truncate">{{ trim(($nombres ?? '') . ' ' . ($apellidos ?? '')) ?: 'Paciente' }}</div>
                                <div class="text-muted small text-truncate">{{ $documento_identidad ?: 'Documento: —' }}</div>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-12">
                                <div class="wz-summary-k">Fecha de nacimiento</div>
                                <div class="wz-summary-v">{{ $fecha_nacimiento ? \Carbon\Carbon::parse($fecha_nacimiento)->format('d/m/Y') : '—' }}</div>
                            </div>
                            <div class="col-12">
                                <div class="wz-summary-k">Teléfono</div>
                                <div class="wz-summary-v">{{ $telefono ?: '—' }}</div>
                            </div>
                            <div class="col-12">
                                <div class="wz-summary-k">Email</div>
                                <div class="wz-summary-v">{{ $email ?: '—' }}</div>
                            </div>
                            <div class="col-12">
                                <div class="wz-summary-k">Género</div>
                                <div class="wz-summary-v">{{ $genero ?: '—' }}</div>
                            </div>
                            @if($esMenorEdad)
                                <div class="col-12">
                                    <div class="wz-summary-k">Tutor</div>
                                    <div class="wz-summary-v">{{ trim(($tutor['nombres'] ?? '') . ' ' . ($tutor['apellidos'] ?? '')) ?: '—' }}</div>
                                </div>
                                <div class="col-12">
                                    <div class="wz-summary-k">Parentesco</div>
                                    <div class="wz-summary-v">{{ $tutor['parentesco'] ?? '—' }}</div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    (function () {
        function getEl(id) {
            return document.getElementById(id);
        }

        function stopStream() {
            const stream = window.__wzCameraStream;
            if (stream && stream.getTracks) {
                stream.getTracks().forEach((t) => t.stop());
            }
            window.__wzCameraStream = null;
        }

        function setCameraError(message) {
            const el = getEl('wz-camera-error');
            if (!el) return;
            if (message) {
                el.textContent = message;
                el.classList.remove('d-none');
            } else {
                el.textContent = '';
                el.classList.add('d-none');
            }
        }

        async function startCamera() {
            const panel = getEl('wz-camera-panel');
            const video = getEl('wz-camera-video');

            if (!panel || !video) return;

            setCameraError(null);
            panel.style.display = '';

            try {
                stopStream();

                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                    setCameraError('Este navegador no soporta captura por cámara.');
                    return;
                }

                const stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: false });
                window.__wzCameraStream = stream;
                video.srcObject = stream;
                await video.play();
            } catch (e) {
                setCameraError('No se pudo acceder a la cámara. Verifica permisos o conexión.');
            }
        }

        function stopCamera() {
            stopStream();
            setCameraError(null);
            const panel = getEl('wz-camera-panel');
            const video = getEl('wz-camera-video');
            if (video) video.srcObject = null;
            if (panel) panel.style.display = 'none';
        }

        function captureCameraFrame() {
            const video = getEl('wz-camera-video');
            const canvas = getEl('wz-camera-canvas');
            const input = getEl('wz-foto-input');

            if (!video || !canvas || !input) return;

            const width = video.videoWidth || 1280;
            const height = video.videoHeight || 720;
            canvas.width = width;
            canvas.height = height;

            const ctx = canvas.getContext('2d');
            if (!ctx) return;

            ctx.drawImage(video, 0, 0, width, height);

            canvas.toBlob((blob) => {
                if (!blob) return;
                const file = new File([blob], 'paciente.jpg', { type: 'image/jpeg' });
                const dt = new DataTransfer();
                dt.items.add(file);
                input.files = dt.files;
                input.dispatchEvent(new Event('change', { bubbles: true }));
                stopCamera();
            }, 'image/jpeg', 0.9);
        }

        document.addEventListener('click', (e) => {
            const startBtn = e.target.closest('[data-wz-camera-start]');
            if (startBtn) {
                e.preventDefault();
                startCamera();
                return;
            }

            const stopBtn = e.target.closest('[data-wz-camera-stop]');
            if (stopBtn) {
                e.preventDefault();
                stopCamera();
                return;
            }

            const captureBtn = e.target.closest('[data-wz-camera-capture]');
            if (captureBtn) {
                e.preventDefault();
                captureCameraFrame();
                return;
            }

            const uploadBtn = e.target.closest('[data-wz-upload-open]');
            if (uploadBtn) {
                e.preventDefault();
                const input = getEl('wz-foto-input');
                if (input) input.click();
            }
        });

        document.addEventListener('visibilitychange', () => {
            if (document.hidden) stopCamera();
        });

        if (window.Livewire && Livewire.hook) {
            Livewire.hook('message.processed', () => {
                const panel = getEl('wz-camera-panel');
                if (!panel) {
                    stopCamera();
                    return;
                }
                if (panel.style.display === 'none') {
                    stopStream();
                }
            });
        }
    })();
</script>
@endpush
