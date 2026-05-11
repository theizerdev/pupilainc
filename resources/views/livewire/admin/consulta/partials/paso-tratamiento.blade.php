{{-- Tratamiento --}}
<div>
    <h6 class="mb-3">
        <i class="ri ri-medicine-bottle-line me-2 text-primary"></i>
        Tratamientos y Medicamentos
    </h6>

    {{-- Formulario para agregar tratamiento --}}
    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-5">
                    <div class="form-floating form-floating-outline">
                        <input type="text" class="form-control" id="medicamento"
                            wire:model="medicamento" placeholder="Medicamento">
                        <label for="medicamento">Medicamento</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <button class="btn btn-primary w-100" wire:click="agregarTratamiento">
                        <i class="ri ri-add-line me-1"></i>Agregar Tratamiento
                    </button>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12">
                    <div class="form-floating form-floating-outline">
                        <textarea class="form-control" id="indicaciones_tratamiento"
                            wire:model="indicaciones_tratamiento"
                            style="height: 80px"
                            placeholder="Indicaciones"></textarea>
                        <label for="indicaciones_tratamiento">Indicaciones</label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Lista de tratamientos --}}
    @if(count($tratamientos) > 0)
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Medicamento</th>
                    <th>Indicaciones</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($tratamientos as $index => $tratamiento)
                <tr wire:key="tratamiento-{{ $index }}">
                    <td><strong>{{ $tratamiento['medicamento'] }}</strong></td>
                    <td>{{ $tratamiento['indicaciones'] ?? '—' }}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-danger"
                            wire:click="eliminarTratamiento({{ $index }})">
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
        No se han agregado tratamientos aún.
    </div>
    @endif
</div>
