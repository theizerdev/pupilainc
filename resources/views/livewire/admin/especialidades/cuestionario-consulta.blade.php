<div>
    @section('title', 'Cuestionario de Preconsulta')

    @push('styles')
    <style>
        .pregunta-row { border-left: 3px solid #e9ecef; padding-left: 10px; transition: all .2s; }
        .pregunta-row:hover { border-left-color: var(--bs-primary); background: #f8f9ff; }
        .pregunta-row.inactiva { opacity: .45; }
        .tipo-badge { font-size: .7rem; }
    </style>
    @endpush

    <div class="container-xxl flex-grow-1 container-p-y">

        {{-- ── HEADER ──────────────────────────────────────────────────────── --}}
        <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
            <div>
                <h4 class="mb-1">
                    <span class="badge me-2" style="background-color: {{ $especialidad->color }}">
                        <i class="fas {{ $especialidad->icono }}"></i>
                    </span>
                    Cuestionario de Preconsulta — {{ $especialidad->nombre }}
                </h4>
                <p class="text-muted mb-0">
                    Este cuestionario se enviará al paciente antes de su consulta con esta especialidad.
                </p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.especialidades.show', $especialidad) }}" class="btn btn-outline-secondary">
                    <i class="ri ri-arrow-left-line me-1"></i>Volver
                </a>
                @if($cuestionario)
                    <span class="badge bg-label-{{ $cuestionario->activo ? 'success' : 'warning' }} align-self-center">
                        {{ $cuestionario->activo ? 'Activo' : 'Inactivo' }}
                    </span>
                @endif
            </div>
        </div>

        {{-- ── DATOS DEL CUESTIONARIO ───────────────────────────────────────── --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent border-0">
                <h6 class="mb-0"><i class="ri ri-questionnaire-line me-2 text-primary"></i>Datos del Cuestionario</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-8">
                        <div class="form-floating form-floating-outline">
                            <input type="text" class="form-control" wire:model="titulo" id="titulo"
                                   placeholder="Título del cuestionario">
                            <label for="titulo">Título <span class="text-danger">*</span></label>
                        </div>
                        @error('titulo') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4 d-flex align-items-center gap-3">
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" wire:model="activo" id="activo">
                            <label class="form-check-label" for="activo">Cuestionario activo</label>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-floating form-floating-outline">
                            <textarea class="form-control" wire:model="descripcion" id="descripcion"
                                      style="height:70px" placeholder="Descripción"></textarea>
                            <label for="descripcion">Descripción (opcional)</label>
                        </div>
                    </div>
                    <div class="col-12 d-flex justify-content-end">
                        <button class="btn btn-primary" wire:click="guardarCuestionario">
                            <i class="ri ri-save-line me-1"></i>
                            {{ $cuestionario ? 'Guardar Cambios' : 'Crear Cuestionario' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── PREGUNTAS ────────────────────────────────────────────────────── --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 d-flex align-items-center justify-content-between">
                <h6 class="mb-0">
                    <i class="ri ri-list-check me-2 text-primary"></i>
                    Preguntas
                    @if($cuestionario)
                        <span class="badge bg-label-secondary ms-1">{{ count($preguntas) }}</span>
                    @endif
                </h6>
                @if($cuestionario)
                <button class="btn btn-sm btn-primary" wire:click="abrirModalPregunta()">
                    <i class="ri ri-add-line me-1"></i>Nueva Pregunta
                </button>
                @endif
            </div>
            <div class="card-body">

                @if(!$cuestionario)
                <div class="text-center py-4">
                    <i class="ri ri-questionnaire-line ri-3x text-muted mb-3 d-block"></i>
                    <p class="text-muted">Primero guarda los datos del cuestionario para agregar preguntas.</p>
                </div>

                @elseif(count($preguntas) === 0)
                <div class="text-center py-4">
                    <i class="ri ri-chat-question-line ri-3x text-muted mb-3 d-block"></i>
                    <p class="text-muted">No hay preguntas aún. Agrega la primera.</p>
                </div>

                @else
                <div class="row g-2">
                    @foreach($preguntas as $pi => $pregunta)
                    <div class="col-12" wire:key="preg-{{ $pregunta['id'] }}">
                        <div class="pregunta-row rounded p-3 {{ !$pregunta['activo'] ? 'inactiva' : '' }}">
                            <div class="d-flex align-items-start justify-content-between gap-2">

                                {{-- Número y contenido --}}
                                <div class="d-flex align-items-start gap-3 flex-grow-1">
                                    <span class="badge bg-primary rounded-circle"
                                          style="width:26px;height:26px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                                        {{ $pi + 1 }}
                                    </span>
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                            <strong class="small">{{ $pregunta['titulo'] }}</strong>
                                            @if($pregunta['obligatorio'])
                                                <span class="badge bg-label-danger" style="font-size:.65rem">Obligatoria</span>
                                            @endif
                                            @if(!$pregunta['activo'])
                                                <span class="badge bg-label-warning" style="font-size:.65rem">Inactiva</span>
                                            @endif
                                        </div>
                                        <div class="d-flex align-items-center gap-2 flex-wrap">
                                            <span class="badge bg-label-primary tipo-badge">
                                                {{ $tipos[$pregunta['tipo']] ?? $pregunta['tipo'] }}
                                            </span>
                                            @if($pregunta['descripcion'])
                                                <small class="text-muted fst-italic">{{ $pregunta['descripcion'] }}</small>
                                            @endif
                                            @if(!empty($pregunta['opciones']))
                                                <small class="text-muted">
                                                    {{ count($pregunta['opciones']) }} opciones:
                                                    {{ implode(', ', array_slice($pregunta['opciones'], 0, 3)) }}
                                                    {{ count($pregunta['opciones']) > 3 ? '...' : '' }}
                                                </small>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                {{-- Acciones --}}
                                <div class="d-flex gap-1 flex-shrink-0">
                                    <button class="btn btn-xs btn-icon btn-outline-secondary"
                                            wire:click="moverPregunta({{ $pregunta['id'] }}, 'up')"
                                            @if($pi === 0) disabled @endif title="Subir">
                                        <i class="ri ri-arrow-up-s-line"></i>
                                    </button>
                                    <button class="btn btn-xs btn-icon btn-outline-secondary"
                                            wire:click="moverPregunta({{ $pregunta['id'] }}, 'down')"
                                            @if($pi === count($preguntas) - 1) disabled @endif title="Bajar">
                                        <i class="ri ri-arrow-down-s-line"></i>
                                    </button>
                                    <button class="btn btn-xs btn-icon btn-outline-primary"
                                            wire:click="abrirModalPregunta({{ $pregunta['id'] }})" title="Editar">
                                        <i class="ri ri-edit-line"></i>
                                    </button>
                                    <button class="btn btn-xs btn-icon {{ $pregunta['activo'] ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                            wire:click="togglePregunta({{ $pregunta['id'] }})"
                                            title="{{ $pregunta['activo'] ? 'Desactivar' : 'Activar' }}">
                                        <i class="ri ri-{{ $pregunta['activo'] ? 'eye-off' : 'eye' }}-line"></i>
                                    </button>
                                    <button class="btn btn-xs btn-icon btn-outline-danger"
                                            wire:click="eliminarPregunta({{ $pregunta['id'] }})"
                                            wire:confirm="¿Eliminar esta pregunta?" title="Eliminar">
                                        <i class="ri ri-delete-bin-line"></i>
                                    </button>
                                </div>

                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif

            </div>
        </div>

    </div>{{-- /container --}}

    {{-- ── MODAL PREGUNTA ──────────────────────────────────────────────────── --}}
    @if($modalPregunta)
    <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5)">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="ri ri-chat-question-line me-2"></i>
                        {{ $preguntaEditId ? 'Editar Pregunta' : 'Nueva Pregunta' }}
                    </h5>
                    <button type="button" class="btn-close" wire:click="$set('modalPregunta', false)"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">

                        <div class="col-12">
                            <div class="form-floating form-floating-outline">
                                <input type="text" class="form-control" wire:model="preguntaTitulo"
                                       id="pregTitulo" placeholder="Pregunta">
                                <label for="pregTitulo">Pregunta <span class="text-danger">*</span></label>
                            </div>
                            @error('preguntaTitulo') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12">
                            <div class="form-floating form-floating-outline">
                                <input type="text" class="form-control" wire:model="preguntaDesc"
                                       id="pregDesc" placeholder="Descripción o ayuda">
                                <label for="pregDesc">Descripción / Ayuda (opcional)</label>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-floating form-floating-outline">
                                <select class="form-select" wire:model.live="preguntaTipo" id="pregTipo">
                                    @foreach($tipos as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                <label for="pregTipo">Tipo de Respuesta</label>
                            </div>
                        </div>

                        <div class="col-md-6 d-flex align-items-center">
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox"
                                       wire:model="preguntaObligatorio" id="pregOblig">
                                <label class="form-check-label" for="pregOblig">Pregunta obligatoria</label>
                            </div>
                        </div>

                        @if(in_array($preguntaTipo, ['opcion', 'multiple']))
                        <div class="col-12">
                            <label class="form-label">
                                Opciones <small class="text-muted">(una por línea)</small>
                                <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control" wire:model="preguntaOpciones" rows="5"
                                      placeholder="Opción 1&#10;Opción 2&#10;Opción 3"></textarea>
                        </div>
                        @endif

                        @if($preguntaTipo === 'escala')
                        <div class="col-12">
                            <div class="alert alert-info mb-0 small">
                                <i class="ri ri-information-line me-1"></i>
                                La escala va de 1 a 10. El paciente deslizará un control para seleccionar el valor.
                            </div>
                        </div>
                        @endif

                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-outline-secondary" wire:click="$set('modalPregunta', false)">Cancelar</button>
                    <button class="btn btn-primary" wire:click="guardarPregunta">
                        <i class="ri ri-save-line me-1"></i>Guardar Pregunta
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    @push('scripts')
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('notify', (e) => {
                const d = e[0] || e;
                const type = d.type === 'success' ? 'success' : 'danger';
                const container = document.querySelector('.toast-container') || (() => {
                    const el = document.createElement('div');
                    el.className = 'toast-container position-fixed top-0 end-0 p-3';
                    document.body.appendChild(el);
                    return el;
                })();
                container.insertAdjacentHTML('beforeend', `
                    <div class="toast show bg-${type} text-white" role="alert">
                        <div class="toast-body d-flex justify-content-between align-items-center">
                            ${d.message}
                            <button type="button" class="btn-close btn-close-white ms-2" data-bs-dismiss="toast"></button>
                        </div>
                    </div>`);
                setTimeout(() => container.lastElementChild?.remove(), 3000);
            });
        });
    </script>
    @endpush
</div>
