<div>
    <div class="container py-4">
        {{-- Encabezado --}}
        <div class="row mb-4">
            <div class="col-12">
                <div class="card bg-gradient-primary shadow-primary">
                    <div class="card-body text-center text-white">
                        @if($tipoFormulario === 'subsecuente')
                            <h2 class="mb-0">📋 Formulario B — Paciente Subsecuente</h2>
                            <small class="text-white-50">Oftalmología - Seguimiento</small>
                        @else
                            <h2 class="mb-0">📋 Formulario A — Paciente de Primera Vez</h2>
                            <small class="text-white-50">Oftalmología - Primera Consulta</small>
                        @endif
                        <p class="mb-0 mt-2">{{ $empresa->razon_social ?? config('app.name') }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- SECCIÓN 1: PRE-LLENADO CRM (Datos automáticos) --}}
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-info">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-database me-2"></i>Información de la Cita (Pre-llenado desde CRM)</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold text-muted small">Nombre completo del paciente:</label>
                                <p class="form-control-static mb-0 fs-5">{{ $nombre_paciente }}</p>
                            </div>
                            
                            @if($tipoFormulario === 'primera_vez')
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold text-muted small">Edad del paciente:</label>
                                    <p class="form-control-static mb-0 fs-5">{{ $edad_paciente }} años</p>
                                </div>
                            @endif
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold text-muted small">Fecha y hora de la cita:</label>
                                <p class="form-control-static mb-0 fs-5">{{ $fecha_hora_cita }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold text-muted small">Tipo de servicio agendado:</label>
                                <p class="form-control-static mb-0 fs-5">{{ $tipo_servicio }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold text-muted small">Nombre del médico asignado:</label>
                                <p class="form-control-static mb-0 fs-5">{{ $nombre_medico }}</p>
                            </div>
                            
                            @if($tipoFormulario === 'subsecuente')
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold text-muted small">Fecha de última consulta:</label>
                                    <p class="form-control-static mb-0 fs-5">{{ $fecha_ultima_consulta }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Información del Paciente y Progreso --}}
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h5 class="mb-1">Cuestionario de Preconsulta</h5>
                                <p class="text-muted mb-0">
                                    <small><i class="fas fa-info-circle"></i> Complete todas las secciones. Los campos marcados con <span class="text-danger">*</span> son obligatorios.</small>
                                </p>
                            </div>
                            <div class="col-md-4 text-end">
                                <div class="progress mb-2" style="height: 8px;">
                                    <div class="progress-bar bg-primary"
                                         role="progressbar"
                                         style="width: {{ ($paso / $totalPasos) * 100 }}%"
                                         aria-valuenow="{{ $paso }}"
                                         aria-valuemin="1"
                                         aria-valuemax="{{ $totalPasos }}">
                                    </div>
                                </div>
                                <small class="text-muted">Paso {{ $paso }} de {{ $totalPasos }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Alertas --}}
        @if (session()->has('success'))
            <div class="row mb-4">
                <div class="col-12">
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <strong>✅ ¡Éxito!</strong> {{ session('success') }}
                        @if($consultaCodigo)
                            <div class="mt-2">
                                <span class="badge bg-dark">Código de consulta: {{ $consultaCodigo }}</span>
                            </div>
                        @endif
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            </div>
        @endif

        {{-- Cuestionario Completado --}}
        @if($respuestasPreconsulta->first()->completado)
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card bg-success text-white text-center">
                        <div class="card-body">
                            <div class="mb-3">
                                <i class="fas fa-check-circle fa-3x"></i>
                            </div>
                            <h4>¡Cuestionario Completado!</h4>
                            <p class="mb-0">Gracias por completar el cuestionario. Su información ha sido registrada exitosamente.</p>
                            @if($consultaCodigo)
                                <div class="mt-3">
                                    <span class="badge bg-dark">Código de consulta: {{ $consultaCodigo }}</span>
                                </div>
                            @endif
                            <small class="d-block mt-2">Puede cerrar esta ventana.</small>
                        </div>
                    </div>
                </div>
            </div>
        @else
            {{-- Formulario --}}
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">{{ $cuestionario->titulo }}</h5>
                            @if($cuestionario->descripcion)
                                <small>{{ $cuestionario->descripcion }}</small>
                            @endif
                        </div>
                        <div class="card-body">
                            <form wire:submit.prevent="finalizarCuestionario">
                                @foreach($preguntasActuales as $respuesta)
                                    <div class="mb-4" wire:key="pregunta_{{ $respuesta->id }}">
                                        <label class="form-label fw-bold">
                                            {{ $respuesta->pregunta->titulo }}
                                            @if($respuesta->pregunta->obligatorio)
                                                <span class="text-danger">*</span>
                                            @endif
                                        </label>

                                        @if($respuesta->pregunta->descripcion)
                                            <small class="form-text text-muted d-block mb-2">{{ $respuesta->pregunta->descripcion }}</small>
                                        @endif

                                        @switch($respuesta->pregunta->tipo)
                                            @case('texto')
                                                <textarea
                                                    class="form-control @error('respuestas.' . $respuesta->id) is-invalid @enderror"
                                                    rows="3"
                                                    placeholder="Escriba su respuesta..."
                                                    wire:model.live="respuestas.{{ $respuesta->id }}"
                                                    @if($respuesta->pregunta->obligatorio) required @endif
                                                ></textarea>
                                                @break

                                            @case('si_no')
                                                <div class="btn-group" role="group">
                                                    <input type="radio"
                                                           class="btn-check"
                                                           name="respuesta_{{ $respuesta->id }}"
                                                           id="si_{{ $respuesta->id }}"
                                                           value="Sí"
                                                           wire:model.live="respuestas.{{ $respuesta->id }}"
                                                           @if($respuesta->pregunta->obligatorio) required @endif>
                                                    <label class="btn btn-outline-success" for="si_{{ $respuesta->id }}">Sí</label>

                                                    <input type="radio"
                                                           class="btn-check"
                                                           name="respuesta_{{ $respuesta->id }}"
                                                           id="no_{{ $respuesta->id }}"
                                                           value="No"
                                                           wire:model.live="respuestas.{{ $respuesta->id }}"
                                                           @if($respuesta->pregunta->obligatorio) required @endif>
                                                    <label class="btn btn-outline-danger" for="no_{{ $respuesta->id }}">No</label>
                                                </div>
                                                @break

                                            @case('si_no_detalle')
                                                <div class="btn-group" role="group">
                                                    <input type="radio"
                                                           class="btn-check"
                                                           name="respuesta_{{ $respuesta->id }}"
                                                           id="si_{{ $respuesta->id }}"
                                                           value="Sí"
                                                           wire:model.live="respuestas.{{ $respuesta->id }}"
                                                           @if($respuesta->pregunta->obligatorio) required @endif>
                                                    <label class="btn btn-outline-success" for="si_{{ $respuesta->id }}">Sí</label>

                                                    <input type="radio"
                                                           class="btn-check"
                                                           name="respuesta_{{ $respuesta->id }}"
                                                           id="no_{{ $respuesta->id }}"
                                                           value="No"
                                                           wire:model.live="respuestas.{{ $respuesta->id }}"
                                                           @if($respuesta->pregunta->obligatorio) required @endif>
                                                    <label class="btn btn-outline-danger" for="no_{{ $respuesta->id }}">No</label>
                                                </div>

                                                {{-- Mostrar campo de detalle si es "Sí" --}}
                                                @if(isset($respuestas[$respuesta->id]) && $respuestas[$respuesta->id] === 'Sí')
                                                    <div class="mt-3" wire:key="detalle_{{ $respuesta->id }}">
                                                        <label class="form-label text-primary">Especifique:</label>
                                                        <textarea
                                                            class="form-control @error('detalles.' . $respuesta->id) is-invalid @enderror"
                                                            rows="3"
                                                            placeholder="Detalles..."
                                                            wire:model.live="detalles.{{ $respuesta->id }}"
                                                            @if($respuesta->pregunta->obligatorio) required @endif
                                                        ></textarea>
                                                        @error('detalles.' . $respuesta->id)
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                @endif
                                                @break

                                            @case('si_no_no_se')
                                                <div class="btn-group" role="group">
                                                    <input type="radio"
                                                           class="btn-check"
                                                           name="respuesta_{{ $respuesta->id }}"
                                                           id="si_{{ $respuesta->id }}"
                                                           value="Sí"
                                                           wire:model.live="respuestas.{{ $respuesta->id }}">
                                                    <label class="btn btn-outline-success" for="si_{{ $respuesta->id }}">Sí</label>

                                                    <input type="radio"
                                                           class="btn-check"
                                                           name="respuesta_{{ $respuesta->id }}"
                                                           id="no_{{ $respuesta->id }}"
                                                           value="No"
                                                           wire:model.live="respuestas.{{ $respuesta->id }}">
                                                    <label class="btn btn-outline-danger" for="no_{{ $respuesta->id }}">No</label>

                                                    <input type="radio"
                                                           class="btn-check"
                                                           name="respuesta_{{ $respuesta->id }}"
                                                           id="nose_{{ $respuesta->id }}"
                                                           value="No sé"
                                                           wire:model.live="respuestas.{{ $respuesta->id }}">
                                                    <label class="btn btn-outline-secondary" for="nose_{{ $respuesta->id }}">No sé</label>
                                                </div>
                                                @break

                                            @case('si_no_factura')
                                                <div class="btn-group" role="group">
                                                    <input type="radio"
                                                           class="btn-check"
                                                           name="respuesta_{{ $respuesta->id }}"
                                                           id="si_{{ $respuesta->id }}"
                                                           value="Sí"
                                                           wire:model.live="respuestas.{{ $respuesta->id }}"
                                                           @if($respuesta->pregunta->obligatorio) required @endif>
                                                    <label class="btn btn-outline-success" for="si_{{ $respuesta->id }}">Sí</label>

                                                    <input type="radio"
                                                           class="btn-check"
                                                           name="respuesta_{{ $respuesta->id }}"
                                                           id="no_{{ $respuesta->id }}"
                                                           value="No"
                                                           wire:model.live="respuestas.{{ $respuesta->id }}"
                                                           @if($respuesta->pregunta->obligatorio) required @endif>
                                                    <label class="btn btn-outline-danger" for="no_{{ $respuesta->id }}">No</label>
                                                </div>

                                                {{-- Mostrar campos de RFC y razón social si es "Sí" --}}
                                                @if(isset($respuestas[$respuesta->id]) && $respuestas[$respuesta->id] === 'Sí')
                                                    <div class="mt-3 row" wire:key="factura_{{ $respuesta->id }}">
                                                        <div class="col-md-6 mb-2">
                                                            <label class="form-label text-primary">RFC:</label>
                                                            <input type="text"
                                                                   class="form-control @error('detalles.' . $respuesta->id . '_rfc') is-invalid @enderror"
                                                                   placeholder="XXXX000000XXX"
                                                                   wire:model.live="detalles.{{ $respuesta->id }}_rfc"
                                                                   maxlength="18"
                                                                   @if($respuesta->pregunta->obligatorio) required @endif>
                                                            @error('detalles.' . $respuesta->id . '_rfc')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                        <div class="col-md-6 mb-2">
                                                            <label class="form-label text-primary">Razón Social:</label>
                                                            <input type="text"
                                                                   class="form-control @error('detalles.' . $respuesta->id . '_razon_social') is-invalid @enderror"
                                                                   placeholder="Nombre o razón social"
                                                                   wire:model.live="detalles.{{ $respuesta->id }}_razon_social"
                                                                   @if($respuesta->pregunta->obligatorio) required @endif>
                                                            @error('detalles.' . $respuesta->id . '_razon_social')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                @endif
                                                @break

                                            @case('opcion')
                                                <select
                                                    class="form-select @error('respuestas.' . $respuesta->id) is-invalid @enderror"
                                                    wire:model.live="respuestas.{{ $respuesta->id }}"
                                                    @if($respuesta->pregunta->obligatorio) required @endif
                                                >
                                                    <option value="">Seleccione una opción...</option>
                                                    @foreach($respuesta->pregunta->opciones ?? [] as $opcion)
                                                        <option value="{{ $opcion }}">{{ $opcion }}</option>
                                                    @endforeach
                                                </select>
                                                @break

                                            @case('multiple')
                                                <div class="border rounded p-3 bg-light">
                                                    @foreach($respuesta->pregunta->opciones ?? [] as $opcion)
                                                        <div class="form-check mb-2" wire:key="opcion_{{ $respuesta->id }}_{{ $loop->index }}">
                                                            <input class="form-check-input"
                                                                   type="checkbox"
                                                                   id="opcion_{{ $respuesta->id }}_{{ $loop->index }}"
                                                                   value="{{ $opcion }}"
                                                                   wire:model.live="respuestas.{{ $respuesta->id }}">
                                                            <label class="form-check-label" for="opcion_{{ $respuesta->id }}_{{ $loop->index }}">
                                                                {{ $opcion }}
                                                            </label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                                @break

                                            @case('tiempo_evolucion')
                                                <div class="row">
                                                    <div class="col-md-6 mb-2">
                                                        <label class="form-label text-muted small">Cantidad:</label>
                                                        <input type="number"
                                                               class="form-control @error('respuestas.' . $respuesta->id . '_cantidad') is-invalid @enderror"
                                                               placeholder="Ej: 2"
                                                               min="1"
                                                               wire:model.live="respuestas.{{ $respuesta->id }}_cantidad"
                                                               @if($respuesta->pregunta->obligatorio) required @endif>
                                                        @error('respuestas.' . $respuesta->id . '_cantidad')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                    <div class="col-md-6 mb-2">
                                                        <label class="form-label text-muted small">Unidad de tiempo:</label>
                                                        <select
                                                            class="form-select @error('respuestas.' . $respuesta->id . '_unidad') is-invalid @enderror"
                                                            wire:model.live="respuestas.{{ $respuesta->id }}_unidad"
                                                            @if($respuesta->pregunta->obligatorio) required @endif>
                                                            <option value="">Seleccione...</option>
                                                            <option value="Días">Días</option>
                                                            <option value="Semanas">Semanas</option>
                                                            <option value="Meses">Meses</option>
                                                            <option value="Años">Años</option>
                                                        </select>
                                                        @error('respuestas.' . $respuesta->id . '_unidad')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                                @break

                                            @case('escala')
                                                <select
                                                    class="form-select @error('respuestas.' . $respuesta->id) is-invalid @enderror"
                                                    wire:model.live="respuestas.{{ $respuesta->id }}"
                                                    @if($respuesta->pregunta->obligatorio) required @endif
                                                >
                                                    <option value="">Seleccione el nivel de dolor...</option>
                                                    <option value="0">0 - Sin dolor</option>
                                                    <option value="1">1 - Muy leve</option>
                                                    <option value="2">2 - Leve</option>
                                                    <option value="3">3 - Molesto</option>
                                                    <option value="4">4 - Moderado</option>
                                                    <option value="5">5 - Incómodo</option>
                                                    <option value="6">6 - Angustiante</option>
                                                    <option value="7">7 - Muy angustiante</option>
                                                    <option value="8">8 - Intenso</option>
                                                    <option value="9">9 - Muy intenso</option>
                                                    <option value="10">10 - Insoportable</option>
                                                </select>
                                                @break

                                            @case('estudios_subsecuente')
                                                <div class="border rounded p-3 bg-light">
                                                    <p class="mb-2 text-muted small"><strong>Opciones:</strong></p>
                                                    
                                                    {{-- Opción 1: No --}}
                                                    <div class="form-check mb-2">
                                                        <input class="form-check-input"
                                                               type="radio"
                                                               id="estudios_no_{{ $respuesta->id }}"
                                                               name="estudios_{{ $respuesta->id }}"
                                                               value="No"
                                                               wire:model.live="respuestas.{{ $respuesta->id }}">
                                                        <label class="form-check-label" for="estudios_no_{{ $respuesta->id }}">
                                                            No, no traigo estudios
                                                        </label>
                                                    </div>

                                                    {{-- Opción 2: Sí, los traigo el día de la cita --}}
                                                    <div class="form-check mb-2">
                                                        <input class="form-check-input"
                                                               type="radio"
                                                               id="estudios_traigo_{{ $respuesta->id }}"
                                                               name="estudios_{{ $respuesta->id }}"
                                                               value="Sí, los traigo el día de la cita"
                                                               wire:model.live="respuestas.{{ $respuesta->id }}">
                                                        <label class="form-check-label" for="estudios_traigo_{{ $respuesta->id }}">
                                                            Sí, los traigo el día de la cita
                                                        </label>
                                                    </div>

                                                    {{-- Opción 3: Sí, los adjunto aquí ahora --}}
                                                    <div class="form-check mb-2">
                                                        <input class="form-check-input"
                                                               type="radio"
                                                               id="estudios_adjunto_{{ $respuesta->id }}"
                                                               name="estudios_{{ $respuesta->id }}"
                                                               value="Sí, los adjunto aquí ahora"
                                                               wire:model.live="respuestas.{{ $respuesta->id }}">
                                                        <label class="form-check-label" for="estudios_adjunto_{{ $respuesta->id }}">
                                                            Sí, los adjunto aquí ahora (subir archivo)
                                                        </label>
                                                    </div>

                                                    {{-- Mostrar input de archivo si selecciona "Sí, los adjunto aquí ahora" --}}
                                                    @if(isset($respuestas[$respuesta->id]) && $respuestas[$respuesta->id] === 'Sí, los adjunto aquí ahora')
                                                        <div class="mt-3" wire:key="archivo_{{ $respuesta->id }}">
                                                            <label class="form-label text-primary">Subir archivo:</label>
                                                            
                                                            {{-- Mostrar archivo ya subido si existe --}}
                                                            @if(isset($archivosSubidos[$respuesta->id]))
                                                                <div class="alert alert-success mb-2">
                                                                    <i class="fas fa-check-circle"></i> Archivo subido: 
                                                                    <strong>{{ $archivosSubidos[$respuesta->id] }}</strong>
                                                                </div>
                                                            @elseif($respuesta->detalle)
                                                                <div class="alert alert-info mb-2">
                                                                    <i class="fas fa-file"></i> Archivo previamente subido: 
                                                                    <strong>{{ basename($respuesta->detalle) }}</strong>
                                                                </div>
                                                            @endif

                                                            <div class="input-group">
                                                                <input type="file"
                                                                       class="form-control @error('archivoEstudios') is-invalid @enderror"
                                                                       accept=".pdf,.jpg,.jpeg,.png"
                                                                       wire:model="archivoEstudios"
                                                                       id="archivo_{{ $respuesta->id }}">
                                                                <button type="button"
                                                                        class="btn btn-primary"
                                                                        wire:click="subirArchivoEstudios({{ $respuesta->id }})"
                                                                        wire:loading.attr="disabled">
                                                                    <span wire:loading.remove wire:target="subirArchivoEstudios({{ $respuesta->id }})">
                                                                        <i class="fas fa-upload"></i> Subir
                                                                    </span>
                                                                    <span wire:loading wire:target="subirArchivoEstudios({{ $respuesta->id }})">
                                                                        <i class="fas fa-spinner fa-spin"></i> Subiendo...
                                                                    </span>
                                                                </button>
                                                            </div>
                                                            <small class="form-text text-muted">
                                                                Formatos permitidos: PDF, JPG, PNG (máx. 10MB)
                                                            </small>
                                                            @error('archivoEstudios')
                                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    @endif
                                                </div>
                                                @break

                                            @default
                                                <input type="text"
                                                       class="form-control @error('respuestas.' . $respuesta->id) is-invalid @enderror"
                                                       placeholder="Escriba su respuesta..."
                                                       wire:model.live="respuestas.{{ $respuesta->id }}"
                                                       @if($respuesta->pregunta->obligatorio) required @endif>
                                        @endswitch

                                        @error('respuestas.' . $respuesta->id)
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                @endforeach

                                {{-- Navegación --}}
                                <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                                    @if($paso > 1)
                                        <button type="button" class="btn btn-outline-secondary" wire:click="pasoAnterior">
                                            <i class="fas fa-arrow-left"></i> Anterior
                                        </button>
                                    @else
                                        <div></div>
                                    @endif

                                    @if($paso < $totalPasos)
                                        <button type="button" class="btn btn-primary" wire:click="siguientePaso">
                                            Siguiente <i class="fas fa-arrow-right"></i>
                                        </button>
                                    @else
                                        <button type="submit" class="btn btn-success btn-lg" wire:loading.attr="disabled">
                                            <span wire:loading.remove>
                                                <i class="fas fa-check-circle"></i> Finalizar Cuestionario
                                            </span>
                                            <span wire:loading>
                                                <i class="fas fa-spinner fa-spin"></i> Guardando...
                                            </span>
                                        </button>
                                    @endif
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Footer informativo --}}
        <div class="row mt-4">
            <div class="col-12 text-center">
                <small class="text-muted">
                    <i class="fas fa-lock"></i> Su información es confidencial y está protegida.
                </small>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('cerrar-ventana', () => {
            setTimeout(() => {
                window.close();
            }, 6000);
        });
    });
</script>
