<div>
    <div class="">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0 text-gray-800">Anulación de Talonarios</h1>
                <p class="text-muted">Registro de anulación según normativa SENIAT (Providencia SNAT/2011/0071)</p>
            </div>
            <div>
                <button wire:click="openModal" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Registrar Anulación
                </button>
            </div>
        </div>

        @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <!-- Filters -->
        <div class="card shadow mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="search">Búsqueda:</label>
                            <input type="text" class="form-control" id="search" wire:model.live.debounce.300ms="search" placeholder="Buscar por serie, N° control...">
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="tipo_documento">Tipo de Documento:</label>
                            <select class="form-control" id="tipo_documento" wire:model.live="tipo_documento">
                                <option value="">Todos los tipos</option>
                                @foreach($tiposDocumento as $key => $value)
                                    <option value="{{ $key }}">{{ $value }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="estado">Estado:</label>
                            <select class="form-control" id="estado" wire:model.live="estado">
                                <option value="">Todos los estados</option>
                                @foreach($estados as $key => $value)
                                    <option value="{{ $key }}">{{ $value }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="button" class="btn btn-secondary btn-block" wire:click="$set('search', ''); $set('tipo_documento', ''); $set('estado', '')">
                                <i class="fas fa-refresh"></i> Limpiar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Listado de Anulaciones</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Tipo</th>
                                <th>Serie</th>
                                <th>Rango Control Fiscal</th>
                                <th>Rango Correlativo</th>
                                <th class="text-center">Cant.</th>
                                <th>Motivo</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($anulaciones as $anulacion)
                            <tr>
                                <td>{{ $anulacion->fecha_anulacion->format('d/m/Y') }}</td>
                                <td>
                                    @php
                                        $iconos = [
                                            'factura' => 'fas fa-file-invoice text-primary',
                                            'boleta' => 'fas fa-receipt text-info',
                                            'nota_credito' => 'fas fa-file-minus text-warning',
                                            'nota_debito' => 'fas fa-file-plus text-danger',
                                            'recibo' => 'fas fa-file-alt text-success'
                                        ];
                                    @endphp
                                    <i class="{{ $iconos[$anulacion->tipo_documento] ?? 'fas fa-file' }} me-1"></i>
                                    {{ $tiposDocumento[$anulacion->tipo_documento] ?? $anulacion->tipo_documento }}
                                </td>
                                <td><span class="badge bg-dark">{{ $anulacion->serie_afectada }}</span></td>
                                <td>
                                    <div>{{ $anulacion->numero_control_desde }}</div>
                                    <small class="text-muted">hasta {{ $anulacion->numero_control_hasta }}</small>
                                </td>
                                <td>
                                    <div>{{ $anulacion->correlativo_desde }}</div>
                                    <small class="text-muted">hasta {{ $anulacion->correlativo_hasta }}</small>
                                </td>
                                <td class="text-center"><span class="badge bg-info">{{ $anulacion->cantidad_documentos }}</span></td>
                                <td>
                                    @php
                                        $motivoColors = [
                                            'dano_fisico' => 'warning',
                                            'robo' => 'danger',
                                            'extravio' => 'danger',
                                            'error_impresion' => 'info',
                                            'cambio_datos_fiscales' => 'primary',
                                            'fin_actividad' => 'secondary',
                                            'otro' => 'dark',
                                        ];
                                    @endphp
                                    <span class="badge bg-{{ $motivoColors[$anulacion->motivo] ?? 'secondary' }}">
                                        {{ $motivos[$anulacion->motivo] ?? $anulacion->motivo }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @php
                                        $estadoColors = [
                                            'registrado' => 'warning',
                                            'reportado_seniat' => 'info',
                                            'confirmado' => 'success',
                                        ];
                                    @endphp
                                    <span class="badge bg-{{ $estadoColors[$anulacion->estado] ?? 'secondary' }}">
                                        {{ $estados[$anulacion->estado] ?? $anulacion->estado }}
                                    </span>
                                    @if($anulacion->acta_destruccion)
                                        <br><small class="text-success"><i class="fas fa-check-circle"></i> Acta</small>
                                    @endif
                                    @if(in_array($anulacion->motivo, ['robo', 'extravio']))
                                        <br><small class="text-danger"><i class="fas fa-exclamation-triangle"></i> Requiere denuncia</small>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="dropdown">
                                        <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                            <i class="ri ri-more-2-line"></i>
                                        </button>
                                        <div class="dropdown-menu">
                                            @if($anulacion->estado === 'registrado')
                                                <button wire:click="edit({{ $anulacion->id }})" class="dropdown-item">
                                                    <i class="ri ri-pencil-line me-1"></i> Editar
                                                </button>
                                                <button wire:click="marcarReportado({{ $anulacion->id }})" 
                                                        wire:confirm="¿Marcar como reportado al SENIAT?" 
                                                        class="dropdown-item">
                                                    <i class="ri ri-send-plane-line me-1"></i> Marcar Reportado
                                                </button>
                                                <button wire:click="delete({{ $anulacion->id }})" 
                                                        wire:confirm="¿Eliminar este registro de anulación?" 
                                                        class="dropdown-item text-danger">
                                                    <i class="ri ri-delete-bin-line me-1"></i> Eliminar
                                                </button>
                                            @elseif($anulacion->estado === 'reportado_seniat')
                                                <button wire:click="marcarConfirmado({{ $anulacion->id }})" 
                                                        wire:confirm="¿Confirmar la anulación?" 
                                                        class="dropdown-item">
                                                    <i class="ri ri-checkbox-circle-line me-1"></i> Confirmar
                                                </button>
                                            @else
                                                <span class="dropdown-item text-muted disabled">
                                                    <i class="ri ri-lock-line me-1"></i> Sin acciones
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center py-4">No hay anulaciones registradas</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $anulaciones->links() }}
                </div>

                <div class="mt-3 p-3 bg-light rounded">
                    <small class="text-muted">
                        <i class="fas fa-gavel me-1"></i>
                        <strong>Base Legal:</strong> Providencia Administrativa SNAT/2011/0071, Art. 21-22. 
                        Los contribuyentes deben informar al SENIAT sobre la anulación de documentos fiscales.
                        En caso de robo o extravío, se debe interponer denuncia ante las autoridades competentes 
                        y notificar al SENIAT dentro de los 3 días hábiles siguientes (Art. 58 COT).
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Registro/Edición -->
    @if($showModal)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="ri-file-damage-line me-2"></i>
                        {{ $editingId ? 'Editar' : 'Registrar' }} Anulación de Talonario
                    </h5>
                    <button type="button" class="btn-close" wire:click="closeModal"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit="save">
                        <!-- Tipo de documento y Serie -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Tipo de Documento <span class="text-danger">*</span></label>
                                <select wire:model.live="form_tipo_documento" class="form-select @error('form_tipo_documento') is-invalid @enderror">
                                    <option value="">Seleccione...</option>
                                    @foreach($tiposDocumento as $key => $value)
                                        <option value="{{ $key }}">{{ $value }}</option>
                                    @endforeach
                                </select>
                                @error('form_tipo_documento') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Serie Asociada</label>
                                <select wire:model="serie_id" class="form-select">
                                    <option value="">Seleccione (opcional)...</option>
                                    @foreach($series as $serie)
                                        <option value="{{ $serie->id }}">{{ $serie->serie }} - {{ $tiposDocumento[$serie->tipo_documento] ?? $serie->tipo_documento }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Serie Afectada <span class="text-danger">*</span></label>
                                <input type="text" wire:model="serie_afectada" class="form-control @error('serie_afectada') is-invalid @enderror"
                                       placeholder="Ej: F001">
                                @error('serie_afectada') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Fecha de Anulación <span class="text-danger">*</span></label>
                                <input type="date" wire:model="fecha_anulacion" class="form-control @error('fecha_anulacion') is-invalid @enderror">
                                @error('fecha_anulacion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Cantidad de Documentos</label>
                                <input type="number" wire:model="cantidad_documentos" class="form-control" readonly>
                                <small class="text-muted">Calculado automáticamente</small>
                            </div>
                        </div>

                        <!-- Rango de Números de Control Fiscal -->
                        <div class="card bg-light mb-3">
                            <div class="card-body py-2">
                                <h6 class="card-title mb-2"><i class="ri-government-line me-1"></i>Rango de Control Fiscal</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="form-label">N° Control Desde <span class="text-danger">*</span></label>
                                        <input type="text" wire:model="numero_control_desde" class="form-control @error('numero_control_desde') is-invalid @enderror"
                                               placeholder="Ej: 00-00000001">
                                        @error('numero_control_desde') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">N° Control Hasta <span class="text-danger">*</span></label>
                                        <input type="text" wire:model="numero_control_hasta" class="form-control @error('numero_control_hasta') is-invalid @enderror"
                                               placeholder="Ej: 00-00000050">
                                        @error('numero_control_hasta') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Rango de Correlativos -->
                        <div class="card bg-light mb-3">
                            <div class="card-body py-2">
                                <h6 class="card-title mb-2"><i class="ri-sort-number-asc me-1"></i>Rango de Correlativos</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="form-label">Correlativo Desde <span class="text-danger">*</span></label>
                                        <input type="text" wire:model="correlativo_desde" class="form-control @error('correlativo_desde') is-invalid @enderror"
                                               placeholder="Ej: 00000001">
                                        @error('correlativo_desde') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Correlativo Hasta <span class="text-danger">*</span></label>
                                        <input type="text" wire:model="correlativo_hasta" class="form-control @error('correlativo_hasta') is-invalid @enderror"
                                               placeholder="Ej: 00000050">
                                        @error('correlativo_hasta') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Motivo -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Motivo de Anulación <span class="text-danger">*</span></label>
                                <select wire:model="motivo" class="form-select @error('motivo') is-invalid @enderror">
                                    <option value="">Seleccione motivo...</option>
                                    @foreach($motivos as $key => $value)
                                        <option value="{{ $key }}">{{ $value }}</option>
                                    @endforeach
                                </select>
                                @error('motivo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">
                                    <div class="form-check mt-4">
                                        <input type="checkbox" wire:model="acta_destruccion" class="form-check-input" id="actaDestruccion">
                                        <label class="form-check-label" for="actaDestruccion">
                                            <i class="ri-file-shield-2-line me-1"></i> Se levantó Acta de Destrucción
                                        </label>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Descripción / Justificación <span class="text-danger">*</span></label>
                            <textarea wire:model="descripcion_motivo" class="form-control @error('descripcion_motivo') is-invalid @enderror"
                                      rows="3" placeholder="Describa detalladamente el motivo de la anulación del talonario/lote..."></textarea>
                            @error('descripcion_motivo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <!-- Reporte SENIAT -->
                        <div class="card border-warning mb-3">
                            <div class="card-body py-2">
                                <h6 class="card-title mb-2">
                                    <i class="ri-government-line me-1 text-warning"></i>
                                    Reporte al SENIAT
                                    <small class="text-muted">(Obligatorio en caso de robo o extravío según Art. 58 COT)</small>
                                </h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="form-label">Fecha de Reporte</label>
                                        <input type="date" wire:model="fecha_reporte_seniat" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">N° Reporte / Ticket SENIAT</label>
                                        <input type="text" wire:model="numero_reporte_seniat" class="form-control"
                                               placeholder="Número de reporte">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Observaciones</label>
                            <textarea wire:model="observaciones" class="form-control" rows="2"
                                      placeholder="Observaciones adicionales..."></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeModal">Cancelar</button>
                    <button type="button" class="btn btn-primary" wire:click="save">
                        <i class="ri-save-line me-1"></i>
                        {{ $editingId ? 'Actualizar' : 'Registrar' }} Anulación
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
