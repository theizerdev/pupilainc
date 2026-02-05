<div>
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Detalles de la Subespecialidad</h6>
            <div class="btn-group">
                @can('edit subespecialidades')
                    <a href="{{ route('admin.subespecialidades.edit', $subespecialidad->id) }}" 
                       class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                @endcan
                <a href="{{ route('admin.subespecialidades.index') }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="font-weight-bold">Código:</label>
                        <p class="form-control-plaintext">{{ $subespecialidad->codigo }}</p>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="font-weight-bold">Nombre:</label>
                        <p class="form-control-plaintext">{{ $subespecialidad->nombre }}</p>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="font-weight-bold">Especialidad:</label>
                        <p class="form-control-plaintext">
                            {{ $subespecialidad->especialidad->nombre ?? 'N/A' }}
                        </p>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="font-weight-bold">Empresa:</label>
                        <p class="form-control-plaintext">
                            {{ $subespecialidad->empresa->razon_social ?? 'N/A' }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="font-weight-bold">Sucursal:</label>
                        <p class="form-control-plaintext">
                            {{ $subespecialidad->sucursal->nombre ?? 'N/A' }}
                        </p>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="font-weight-bold">Costo Consulta:</label>
                        <p class="form-control-plaintext">
                            {{ format_money($subespecialidad->costo_consulta) }}
                        </p>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="font-weight-bold">Duración:</label>
                        <p class="form-control-plaintext">
                            {{ $subespecialidad->duracion_consulta }} minutos
                        </p>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label class="font-weight-bold">Descripción:</label>
                        <p class="form-control-plaintext">
                            {{ $subespecialidad->descripcion ?? 'Sin descripción' }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="font-weight-bold">Estado:</label>
                        <p class="form-control-plaintext">
                            <span class="badge badge-{{ $subespecialidad->status ? 'success' : 'secondary' }}">
                                {{ $subespecialidad->status ? 'Activo' : 'Inactivo' }}
                            </span>
                        </p>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="font-weight-bold">Fecha de Creación:</label>
                        <p class="form-control-plaintext">
                            {{ $subespecialidad->created_at->format('d/m/Y H:i') }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="font-weight-bold">Última Actualización:</label>
                        <p class="form-control-plaintext">
                            {{ $subespecialidad->updated_at->format('d/m/Y H:i') }}
                        </p>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="font-weight-bold">Creado por:</label>
                        <p class="form-control-plaintext">
                            {{ $subespecialidad->user->name ?? 'Sistema' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>