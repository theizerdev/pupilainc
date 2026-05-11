{{-- Estudios --}}
<div>
    <h6 class="mb-3">
        <i class="ri ri-microscope-line me-2 text-primary"></i>
        Estudios y Exámenes
    </h6>

    {{-- Formulario para agregar estudio --}}
    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="form-floating form-floating-outline">
                        <select class="form-select" id="tipo_estudio"
                            wire:model="tipo_estudio">
                            <option value="imagen">Imagen</option>
                            <option value="laboratorio">Laboratorio</option>
                            <option value="otros">Otros</option>
                        </select>
                        <label for="tipo_estudio">Tipo</label>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="form-floating form-floating-outline">
                        <input type="text" class="form-control" id="nombre_estudio"
                            wire:model="nombre_estudio" placeholder="Nombre del estudio">
                        <label for="nombre_estudio">Nombre del Estudio</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <button class="btn btn-primary w-100" wire:click="agregarEstudio">
                        <i class="ri ri-add-line me-1"></i>Agregar Estudio
                    </button>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12">
                    <div class="form-floating form-floating-outline">
                        <textarea class="form-control" id="indicaciones_estudio"
                            wire:model="indicaciones_estudio"
                            style="height: 80px"
                            placeholder="Indicaciones"></textarea>
                        <label for="indicaciones_estudio">Indicaciones (opcional)</label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Lista de estudios --}}
    @if(count($estudios) > 0)
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Tipo</th>
                    <th>Estudio</th>
                    <th>Indicaciones</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($estudios as $index => $estudio)
                <tr wire:key="estudio-{{ $index }}">
                    <td>
                        <span class="badge bg-{{ $estudio['tipo_estudio'] === 'imagen' ? 'primary' : ($estudio['tipo_estudio'] === 'laboratorio' ? 'success' : 'secondary') }}">
                            {{ ucfirst($estudio['tipo_estudio']) }}
                        </span>
                    </td>
                    <td><strong>{{ $estudio['nombre_estudio'] }}</strong></td>
                    <td>{{ $estudio['indicaciones'] ?? '—' }}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-danger"
                            wire:click="eliminarEstudio({{ $index }})">
                            <i class="ri ri-delete-bin-line"></i>
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="alert alert-info">
        <i class="ri ri-information-line me-2"></i>
        No se han agregado estudios aún.
    </div>
    @endif

    {{-- Navegación --}}
    <div class="d-flex justify-content-between mt-4 pt-3 border-top">
        <div>
            @if($pasoActualIndex > 0)
            <button class="btn btn-outline-secondary" wire:click="irPaso({{ $pasoActualIndex - 1 }})">
                <i class="ri ri-arrow-left-line me-1"></i>Anterior
            </button>
            @endif
        </div>
        <div class="d-flex gap-2">
            @if($haySiguientePaso)
            <button class="btn btn-success" wire:click="siguientePaso">
                <i class="ri ri-save-line me-1"></i>Guardar y Siguiente
                <span class="badge bg-light text-success ms-1">{{ $pasoSiguiente['nombre'] ?? '' }}</span>
            </button>
            @endif
        </div>
    </div>
</div>
