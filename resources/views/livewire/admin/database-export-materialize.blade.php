<div>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">
                            <i class="fas fa-database"></i> Exportador de Base de Datos
                        </h4>
                    </div>
                    <div class="card-body">
                        <!-- Selección de Tabla -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="tableSelect" class="form-label fw-bold">
                                    <i class="fas fa-table"></i> Seleccionar Tabla
                                </label>
                                <select wire:model="selectedTable" id="tableSelect" class="form-select form-select-lg">
                                    <option value="">-- Seleccione una tabla --</option>
                                    @if($exportFormat === 'sql')
                                        <option value="*">📦 TODA LA BASE DE DATOS</option>
                                    @endif
                                    @foreach($availableTables as $table)
                                        <option value="{{ $table }}">{{ ucfirst(str_replace('_', ' ', $table)) }}</option>
                                    @endforeach
                                </select>
                                @error('selectedTable')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="formatSelect" class="form-label fw-bold">
                                    <i class="fas fa-file-export"></i> Formato de Exportación
                                </label>
                                <select wire:model="exportFormat" id="formatSelect" class="form-select form-select-lg">
                                    <option value="xlsx">Excel (.xlsx)</option>
                                    <option value="csv">CSV (.csv)</option>
                                    <option value="pdf">PDF (.pdf)</option>
                                    <option value="sql">SQL (.sql)</option>
                                </select>
                            </div>
                        </div>

                        @if($selectedTable && $selectedTable !== '*')
                            <!-- Selección de Columnas -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="card border-info">
                                        <div class="card-header bg-info text-white">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h5 class="mb-0">
                                                    <i class="fas fa-columns"></i> Columnas a Exportar
                                                </h5>
                                                <div>
                                                    <button wire:click="selectAllColumns" class="btn btn-sm btn-light me-2">
                                                        <i class="fas fa-check-square"></i> Seleccionar Todo
                                                    </button>
                                                    <button wire:click="deselectAllColumns" class="btn btn-sm btn-light">
                                                        <i class="fas fa-square"></i> Deseleccionar Todo
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                @foreach($availableColumns as $column)
                                                    <div class="col-md-4 col-lg-3 mb-2">
                                                        <div class="form-check">
                                                            <input
                                                                wire:model="selectedColumns"
                                                                class="form-check-input"
                                                                type="checkbox"
                                                                id="column_{{ $column }}"
                                                                value="{{ $column }}"
                                                            >
                                                            <label class="form-check-label" for="column_{{ $column }}">
                                                                {{ ucfirst(str_replace('_', ' ', $column)) }}
                                                            </label>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                            @error('selectedColumns')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Condiciones de Exportación -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="card border-warning">
                                        <div class="card-header bg-warning text-dark">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h5 class="mb-0">
                                                    <i class="fas fa-filter"></i> Condiciones de Exportación (Opcional)
                                                </h5>
                                                <button wire:click="addCondition" class="btn btn-sm btn-dark">
                                                    <i class="fas fa-plus"></i> Añadir Condición
                                                </button>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            @if(count($conditions) === 0)
                                                <div class="text-center text-muted py-3">
                                                    <i class="fas fa-info-circle fa-2x mb-2"></i>
                                                    <p>No hay condiciones definidas. Puede exportar todos los registros o añadir condiciones para filtrar los datos.</p>
                                                </div>
                                            @else
                                                @foreach($conditions as $index => $condition)
                                                    <div class="row mb-3 align-items-end">
                                                        <div class="col-md-2">
                                                            @if($index > 0)
                                                                <label class="form-label">Lógica</label>
                                                                <select wire:model="conditions.{{ $index }}.logic" class="form-select form-select-sm">
                                                                    <option value="AND">Y (AND)</option>
                                                                    <option value="OR">O (OR)</option>
                                                                </select>
                                                            @endif
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label class="form-label">Columna</label>
                                                            <select wire:model="conditions.{{ $index }}.column" class="form-select form-select-sm">
                                                                <option value="">-- Seleccione --</option>
                                                                @foreach($availableColumns as $column)
                                                                    <option value="{{ $column }}">{{ ucfirst(str_replace('_', ' ', $column)) }}</option>
                                                                @endforeach
                                                            </select>
                                                            @error("conditions.{$index}.column")
                                                                <span class="text-danger small">{{ $message }}</span>
                                                            @enderror
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label class="form-label">Operador</label>
                                                            <select wire:model="conditions.{{ $index }}.operator" class="form-select form-select-sm">
                                                                <option value="=">Igual (=)</option>
                                                                <option value="!=">Diferente (!=)</option>
                                                                <option value="<">Menor que (&lt;)</option>
                                                                <option value="<=">Menor o igual (&lt;=)</option>
                                                                <option value=">">Mayor que (&gt;)</option>
                                                                <option value=">=">Mayor o igual (&gt;=)</option>
                                                                <option value="LIKE">Contiene (LIKE)</option>
                                                                <option value="NOT LIKE">No contiene (NOT LIKE)</option>
                                                                <option value="IN">En lista (IN)</option>
                                                                <option value="NOT IN">No en lista (NOT IN)</option>
                                                                <option value="IS NULL">Es nulo (IS NULL)</option>
                                                                <option value="IS NOT NULL">No es nulo (IS NOT NULL)</option>
                                                            </select>
                                                            @error("conditions.{$index}.operator")
                                                                <span class="text-danger small">{{ $message }}</span>
                                                            @enderror
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label class="form-label">Valor</label>
                                                            <input
                                                                wire:model="conditions.{{ $index }}.value"
                                                                type="text"
                                                                class="form-control form-control-sm"
                                                                placeholder="Valor a comparar"
                                                                {{ in_array($condition['operator'] ?? '', ['IS NULL', 'IS NOT NULL']) ? 'disabled' : '' }}
                                                            >
                                                            @error("conditions.{$index}.value")
                                                                <span class="text-danger small">{{ $message }}</span>
                                                            @enderror
                                                        </div>
                                                        <div class="col-md-1">
                                                            <button wire:click="removeCondition({{ $index }})" class="btn btn-sm btn-danger">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Opciones de Exportación -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="card border-success">
                                        <div class="card-header bg-success text-white">
                                            <h5 class="mb-0">
                                                <i class="fas fa-cog"></i> Opciones de Exportación
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label for="filename" class="form-label">Nombre del Archivo</label>
                                                <div class="input-group">
                                                    <input
                                                        wire:model="customFilename"
                                                        type="text"
                                                        id="filename"
                                                        class="form-control"
                                                        placeholder="Nombre personalizado (opcional)"
                                                    >
                                                    <span class="input-group-text">.{{ $exportFormat }}</span>
                                                </div>
                                                <small class="text-muted">
                                                    Si no especifica un nombre, se generará automáticamente.
                                                </small>
                                            </div>
                                            <div class="form-check">
                                                <input
                                                    wire:model="includeHeaders"
                                                    class="form-check-input"
                                                    type="checkbox"
                                                    id="includeHeaders"
                                                >
                                                <label class="form-check-label" for="includeHeaders">
                                                    Incluir encabezados en la exportación
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card border-info">
                                        <div class="card-header bg-info text-white">
                                            <h5 class="mb-0">
                                                <i class="fas fa-info-circle"></i> Información de Exportación
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-2">
                                                <strong>Tabla:</strong>
                                                @if($selectedTable === '*')
                                                    🗄️ TODA LA BASE DE DATOS
                                                @else
                                                    {{ ucfirst(str_replace('_', ' ', $selectedTable)) }}
                                                @endif
                                            </div>
                                            @if($selectedTable !== '*')
                                                <div class="mb-2">
                                                    <strong>Columnas seleccionadas:</strong> {{ count($selectedColumns) }}
                                                </div>
                                                <div class="mb-2">
                                                    <strong>Condiciones:</strong> {{ count($conditions) }}
                                                </div>
                                            @endif
                                            <div class="mb-2">
                                                <strong>Formato:</strong> {{ strtoupper($exportFormat) }}
                                            </div>
                                            <div class="mb-2">
                                                <strong>Nombre del archivo:</strong>
                                                {{ $customFilename ?: $this->generateDefaultFilename() }}.{{ $exportFormat }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Botones de Acción -->
                        <div class="row">
                            <div class="col-12 text-center">
                                <button
                                    wire:click="startExport"
                                    wire:loading.attr="disabled"
                                    class="btn btn-success btn-lg me-3"
                                    {{ !$selectedTable || ($selectedTable !== '*' && count($selectedColumns) === 0) ? 'disabled' : '' }}
                                >
                                    <i class="fas fa-download"></i>
                                    <span wire:loading.remove>
                                        @if($exportFormat === 'sql' && $selectedTable === '*')
                                            Exportar Base de Datos Completa
                                        @elseif($exportFormat === 'sql')
                                            Exportar SQL
                                        @else
                                            Exportar Datos
                                        @endif
                                    </span>
                                    <span wire:loading>Exportando...</span>
                                </button>
                                <button
                                    wire:click="resetForm"
                                    wire:loading.attr="disabled"
                                    class="btn btn-secondary btn-lg"
                                >
                                    <i class="fas fa-undo"></i>
                                    <span wire:loading.remove>Reiniciar Formulario</span>
                                    <span wire:loading>Reiniciando...</span>
                                </button>
                            </div>
                        </div>

                        <!-- Barra de Progreso -->
                        @if($exportProgress > 0)
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="progress" style="height: 25px;">
                                        <div
                                            class="progress-bar progress-bar-striped progress-bar-animated bg-success"
                                            role="progressbar"
                                            style="width: {{ $exportProgress }}%"
                                            aria-valuenow="{{ $exportProgress }}"
                                            aria-valuemin="0"
                                            aria-valuemax="100"
                                        >
                                            {{ $exportProgress }}%
                                        </div>
                                    </div>
                                    <div class="text-center mt-2">
                                        <small class="text-muted">
                                            {{ $exportStatus }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Mensajes de Estado -->
                        @if(session()->has('message'))
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="alert alert-{{ session('message_type', 'info') }} alert-dismissible fade show" role="alert">
                                        <i class="fas fa-{{ session('message_type') === 'success' ? 'check-circle' : 'exclamation-circle' }}"></i>
                                        {{ session('message') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .card {
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .card-header {
        border-radius: 10px 10px 0 0 !important;
        font-weight: 600;
    }

    .form-select-lg, .form-control-lg {
        border-radius: 8px;
        border: 2px solid #e9ecef;
        transition: all 0.3s ease;
    }

    .form-select-lg:focus, .form-control-lg:focus {
        border-color: #4F81BD;
        box-shadow: 0 0 0 0.2rem rgba(79, 129, 189, 0.25);
    }

    .btn-lg {
        border-radius: 8px;
        font-weight: 600;
        padding: 12px 24px;
        transition: all 0.3s ease;
    }

    .btn-success {
        background: linear-gradient(135deg, #28a745, #20c997);
        border: none;
    }

    .btn-success:hover {
        background: linear-gradient(135deg, #218838, #1e7e34);
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(40, 167, 69, 0.3);
    }

    .btn-secondary {
        background: linear-gradient(135deg, #6c757d, #5a6268);
        border: none;
    }

    .btn-secondary:hover {
        background: linear-gradient(135deg, #5a6268, #495057);
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(108, 117, 125, 0.3);
    }

    .progress {
        border-radius: 15px;
        overflow: hidden;
    }

    .progress-bar {
        font-weight: 600;
        font-size: 14px;
    }

    .form-check-input:checked {
        background-color: #4F81BD;
        border-color: #4F81BD;
    }

    .card-body {
        padding: 1.5rem;
    }

    .text-muted {
        color: #6c757d !important;
    }

    .alert {
        border-radius: 10px;
        border: none;
    }

    .input-group-text {
        background-color: #f8f9fa;
        border: 2px solid #e9ecef;
        border-left: none;
        font-weight: 600;
    }

    .form-control:focus + .input-group-text {
        border-color: #4F81BD;
    }

    .btn-sm {
        border-radius: 6px;
        font-weight: 500;
    }

    .btn-light {
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
    }

    .btn-light:hover {
        background-color: #e9ecef;
    }

    .btn-danger {
        background: linear-gradient(135deg, #dc3545, #c82333);
        border: none;
    }

    .btn-danger:hover {
        background: linear-gradient(135deg, #c82333, #bd2130);
    }

    .form-select-sm, .form-control-sm {
        border-radius: 6px;
    }

    .card-header.bg-primary {
        background: linear-gradient(135deg, #4F81BD, #3A66A7) !important;
    }

    .card-header.bg-info {
        background: linear-gradient(135deg, #17a2b8, #138496) !important;
    }

    .card-header.bg-warning {
        background: linear-gradient(135deg, #ffc107, #e0a800) !important;
    }

    .card-header.bg-success {
        background: linear-gradient(135deg, #28a745, #218838) !important;
    }

    .card-header.bg-dark {
        background: linear-gradient(135deg, #343a40, #23272b) !important;
    }
</style>
@endpush
