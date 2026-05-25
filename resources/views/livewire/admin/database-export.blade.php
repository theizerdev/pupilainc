<div class="w-100">
    @section('title', 'Exportar e Importar Base de Datos')

    @push('styles')
    <style>
        .database-hero { background: linear-gradient(135deg, #0ea5e9 0%, #6366f1 100%); color:#fff; border-radius:.75rem; padding:1.4rem 1.6rem; }
        .database-hero h2 { color:#fff; margin:0; }
        .database-hero p { opacity:.9; margin:0; }

        .stat-card { border:1px solid rgba(0,0,0,.06); border-radius:.65rem; padding:.9rem 1rem;
                     transition:all .2s; display:flex; align-items:center; gap:.85rem; height:100%; background:#fff; }
        .stat-card:hover { box-shadow:0 6px 18px rgba(0,0,0,.07); transform:translateY(-1px); }
        .stat-card .stat-icon { width:44px; height:44px; border-radius:11px; flex:0 0 44px;
                                display:flex; align-items:center; justify-content:center; font-size:1.15rem; }
        .stat-card .stat-value { font-size:1.35rem; font-weight:600; line-height:1; }
        .stat-card .stat-label { font-size:.72rem; color:var(--bs-secondary-color);
                                 text-transform:uppercase; letter-spacing:.4px; font-weight:600; }

        .tab-btn { transition:all .2s; border-radius:.5rem; }
        .tab-btn.active { background:linear-gradient(135deg, #0ea5e9 0%, #6366f1 100%); color:white !important; box-shadow:0 4px 12px rgba(99,102,241,.3); }
        .tab-btn:not(.active):hover { background:#f1f5f9; }

        .wizard-step { transition:all .3s; }
        .wizard-step.active .step-circle { background:linear-gradient(135deg, #0ea5e9 0%, #6366f1 100%); color:white; }
        .wizard-step.completed .step-circle { background:#10b981; color:white; }

        .step-circle { width:40px; height:40px; border-radius:50%; display:flex; align-items:center; justify-content:center;
                       font-weight:600; transition:all .3s; background:#e2e8f0; color:#64748b; }

        .upload-zone { border:2px dashed #6366f1; border-radius:.75rem; padding:3rem 2rem; transition:all .2s; background:#f8faff; }
        .upload-zone:hover { border-color:#0ea5e9; background:#f0f9ff; }

        .progress-bar-animated { background-image:linear-gradient(45deg, rgba(255,255,255,.15) 25%, transparent 25%, transparent 50%, rgba(255,255,255,.15) 50%, rgba(255,255,255,.15) 75%, transparent 75%, transparent);
                                 background-size:1rem 1rem; animation:progress-bar-stripes 1s linear infinite; }

        @keyframes progress-bar-stripes {
            0% { background-position:1rem 0; }
            100% { background-position:0 0; }
        }

        .option-card { border:1px solid #e2e8f0; border-radius:.5rem; padding:1rem; transition:all .2s; }
        .option-card:hover { border-color:#6366f1; box-shadow:0 2px 8px rgba(99,102,241,.1); }
        .option-card.active { border-color:#6366f1; background:#f8f9ff; }

        .warning-badge { background:#fef3c7; color:#92400e; border:1px solid #fde68a; }
    </style>
    @endpush

    {{-- Hero Section --}}
    <div class="database-hero mb-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <h2 class="fw-semibold"><i class="ri ri-database-2-line me-2"></i>Exportar e Importar Base de Datos</h2>
            <p class="mt-1">Gestiona respaldos y restauración del sistema de forma segura</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-light btn-sm" wire:click="resetExport" wire:click.prevent="resetImport">
                <i class="ri ri-refresh-line me-1"></i>Reiniciar
            </button>
        </div>
    </div>

    {{-- Success/Error Messages --}}
    @if($successMessage)
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <div class="d-flex align-items-start gap-2">
                <i class="ri ri-checkbox-circle-line ri-lg"></i>
                <div class="flex-grow-1">
                    <strong>¡Éxito!</strong>
                    <p class="mb-0">{{ $successMessage }}</p>
                </div>
            </div>
            <button type="button" class="btn-close" wire:click="$set('successMessage', '')" aria-label="Close"></button>
        </div>
    @endif

    @if($errorMessage)
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <div class="d-flex align-items-start gap-2">
                <i class="ri ri-error-warning-line ri-lg"></i>
                <div class="flex-grow-1">
                    <strong>Error</strong>
                    <p class="mb-0">{{ $errorMessage }}</p>
                </div>
            </div>
            <button type="button" class="btn-close" wire:click="$set('errorMessage', '')" aria-label="Close"></button>
        </div>
    @endif

    {{-- Stat Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#dbeafe;color:#2563eb;"><i class="ri ri-database-2-line"></i></div>
                <div>
                    <div class="stat-label">Total tablas</div>
                    <div class="stat-value">{{ $totalTables }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#dcfce7;color:#16a34a;"><i class="ri ri-hard-drive-2-line"></i></div>
                <div>
                    <div class="stat-label">Tamaño estimado</div>
                    <div class="stat-value">{{ $estimatedFileSize }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#fef3c7;color:#d97706;"><i class="ri ri-upload-2-line"></i></div>
                <div>
                    <div class="stat-label">Exportar</div>
                    <div class="stat-value">SQL</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#fee2e2;color:#dc2626;"><i class="ri ri-download-2-line"></i></div>
                <div>
                    <div class="stat-label">Importar</div>
                    <div class="stat-value">.sql</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Tabs --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-0 pt-3">
            <div class="d-flex gap-2">
                <button wire:click="switchTab('export')"
                        class="tab-btn flex-grow-1 btn {{ $activeTab === 'export' ? 'active' : 'btn-outline-primary' }}">
                    <i class="ri ri-upload-2-line me-2"></i>Exportar Base de Datos
                </button>
                <button wire:click="switchTab('import')"
                        class="tab-btn flex-grow-1 btn {{ $activeTab === 'import' ? 'active' : 'btn-outline-primary' }}">
                    <i class="ri ri-download-2-line me-2"></i>Importar Base de Datos
                </button>
            </div>
        </div>
    </div>

    @if($activeTab === 'export')
        {{-- ==================== EXPORT TAB ==================== --}}

        {{-- Wizard Steps Indicator --}}
        <div class="row g-3 mb-4">
            <div class="col-3">
                <div class="wizard-step {{ $exportStep >= 1 ? 'active' : '' }} {{ $exportStep > 1 ? 'completed' : '' }}">
                    <div class="text-center">
                        <div class="step-circle mx-auto mb-2">
                            @if($exportStep > 1)<i class="ri ri-check-line"></i>@else 1 @endif
                        </div>
                        <small class="fw-semibold">Opciones</small>
                    </div>
                </div>
            </div>
            <div class="col-3">
                <div class="wizard-step {{ $exportStep >= 2 ? 'active' : '' }} {{ $exportStep > 2 ? 'completed' : '' }}">
                    <div class="text-center">
                        <div class="step-circle mx-auto mb-2">
                            @if($exportStep > 2)<i class="ri ri-check-line"></i>@else 2 @endif
                        </div>
                        <small class="fw-semibold">Vista previa</small>
                    </div>
                </div>
            </div>
            <div class="col-3">
                <div class="wizard-step {{ $exportStep >= 3 ? 'active' : '' }} {{ $exportStep > 3 ? 'completed' : '' }}">
                    <div class="text-center">
                        <div class="step-circle mx-auto mb-2">
                            @if($exportStep > 3)<i class="ri ri-check-line"></i>@else 3 @endif
                        </div>
                        <small class="fw-semibold">Confirmación</small>
                    </div>
                </div>
            </div>
            <div class="col-3">
                <div class="wizard-step {{ $exportStep >= 4 ? 'active' : '' }}">
                    <div class="text-center">
                        <div class="step-circle mx-auto mb-2">
                            4
                        </div>
                        <small class="fw-semibold">Progreso</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- Step 1: Configuration Options --}}
        @if($exportStep === 1)
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="fw-semibold mb-4">
                        <i class="ri ri-settings-3-line me-2 text-primary"></i>
                        Configuración de Exportación
                    </h5>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="option-card">
                                <label class="form-check-label d-block">
                                    <input type="checkbox" wire:model="exportOptions.include_structure" class="form-check-input me-2">
                                    <strong><i class="ri ri-building-line me-1"></i>Incluir estructura</strong>
                                    <small class="d-block text-muted mt-1">CREATE TABLE statements con definiciones completas</small>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="option-card">
                                <label class="form-check-label d-block">
                                    <input type="checkbox" wire:model="exportOptions.include_data" class="form-check-input me-2">
                                    <strong><i class="ri ri-database-line me-1"></i>Incluir datos</strong>
                                    <small class="d-block text-muted mt-1">INSERT statements con todos los registros</small>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="option-card">
                                <label class="form-check-label d-block">
                                    <input type="checkbox" wire:model="exportOptions.add_drop_table" class="form-check-input me-2">
                                    <strong><i class="ri ri-delete-bin-line me-1"></i>Agregar DROP TABLE</strong>
                                    <small class="d-block text-muted mt-1">Eliminar tablas existentes antes de crearlas</small>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="option-card">
                                <label class="form-check-label d-block">
                                    <input type="checkbox" wire:model="exportOptions.add_if_not_exists" class="form-check-input me-2">
                                    <strong><i class="ri ri-shield-check-line me-1"></i>IF NOT EXISTS</strong>
                                    <small class="d-block text-muted mt-1">Solo crear si la tabla no existe</small>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="option-card">
                                <label class="form-check-label d-block">
                                    <input type="checkbox" wire:model="exportOptions.compress" class="form-check-input me-2">
                                    <strong><i class="ri ri-file-zip-line me-1"></i>Comprimir archivo</strong>
                                    <small class="d-block text-muted mt-1">Generar archivo .sql.gz (gzip)</small>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-top d-flex justify-content-between">
                        <div>
                            <small class="text-muted">
                                <i class="ri ri-information-line me-1"></i>
                                Se exportarán {{ $totalTables }} tablas ({{ $estimatedFileSize }} estimado)
                            </small>
                        </div>
                        <button wire:click="nextExportStep" class="btn btn-primary">
                            Siguiente <i class="ri ri-arrow-right-line ms-1"></i>
                        </button>
                    </div>
                </div>
            </div>
        @endif

        {{-- Step 2: Preview --}}
        @if($exportStep === 2)
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="fw-semibold mb-4">
                        <i class="ri ri-eye-line me-2 text-primary"></i>
                        Vista Previa de Exportación
                    </h5>

                    <div class="alert alert-info">
                        <div class="d-flex align-items-start gap-2">
                            <i class="ri ri-information-line ri-lg"></i>
                            <div>
                                <strong>Resumen de configuración:</strong>
                                <ul class="mb-0 mt-2">
                                    <li>Estructura: {{ $exportOptions['include_structure'] ? 'Sí' : 'No' }}</li>
                                    <li>Datos: {{ $exportOptions['include_data'] ? 'Sí' : 'No' }}</li>
                                    <li>Tablas a exportar: {{ $totalTables }}</li>
                                    <li>Tamaño estimado: {{ $estimatedFileSize }}</li>
                                    <li>Compresión: {{ $exportOptions['compress'] ? 'Sí (.sql.gz)' : 'No (.sql)' }}</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <h6 class="fw-semibold mb-3">Tablas incluidas:</h6>
                    <div class="table-responsive" style="max-height:300px; overflow-y:auto;">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Tabla</th>
                                    <th class="text-center">Estructura</th>
                                    <th class="text-center">Datos</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($availableTables as $table => $label)
                                    <tr>
                                        <td><code>{{ $table }}</code></td>
                                        <td class="text-center">
                                            @if($exportOptions['include_structure'])
                                                <i class="ri ri-check-line text-success"></i>
                                            @else
                                                <i class="ri ri-close-line text-danger"></i>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($exportOptions['include_data'])
                                                <i class="ri ri-check-line text-success"></i>
                                            @else
                                                <i class="ri ri-close-line text-danger"></i>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 pt-3 border-top d-flex justify-content-between">
                        <button wire:click="previousExportStep" class="btn btn-outline-secondary">
                            <i class="ri ri-arrow-left-line me-1"></i>Anterior
                        </button>
                        <button wire:click="nextExportStep" class="btn btn-primary">
                            Siguiente <i class="ri ri-arrow-right-line ms-1"></i>
                        </button>
                    </div>
                </div>
            </div>
        @endif

        {{-- Step 3: Confirmation --}}
        @if($exportStep === 3)
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <div class="mb-4">
                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                             style="width:80px; height:80px; background:linear-gradient(135deg, #0ea5e9 0%, #6366f1 100%);">
                            <i class="ri ri-shield-check-line ri-3x text-white"></i>
                        </div>
                        <h4 class="fw-semibold mb-2">¿Confirmar exportación?</h4>
                        <p class="text-muted">Esta operación generará un respaldo completo de la base de datos</p>
                    </div>

                    <div class="alert alert-warning text-start">
                        <div class="d-flex align-items-start gap-2">
                            <i class="ri ri-error-warning-line ri-lg"></i>
                            <div>
                                <strong>Información importante:</strong>
                                <ul class="mb-0 mt-2">
                                    <li>Se solicitará tu contraseña para confirmar</li>
                                    <li>La acción será registrada en el log de auditoría</li>
                                    <li>El archivo se descargará automáticamente</li>
                                    <li>Tiempo estimado: 1-5 minutos</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-top d-flex justify-content-between">
                        <button wire:click="previousExportStep" class="btn btn-outline-secondary">
                            <i class="ri ri-arrow-left-line me-1"></i>Anterior
                        </button>
                        <button wire:click="requestPasswordVerification('export')" class="btn btn-warning">
                            <i class="ri ri-lock-line me-1"></i>Confirmar con Contraseña
                        </button>
                    </div>
                </div>
            </div>
        @endif

        {{-- Step 4: Progress --}}
        @if($exportStep === 4 && $isExporting)
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <div class="spinner-border mb-3" style="width: 3rem; height: 3rem; border-width: 3px; color:#6366f1;"></div>
                    <h5 class="fw-semibold mb-2">Exportando base de datos...</h5>
                    <p class="text-muted mb-4">Esto puede tomar varios minutos dependiendo del tamaño</p>

                    <div class="progress mb-3" style="height: 12px; max-width:400px; margin:0 auto;">
                        <div class="progress-bar progress-bar-animated"
                             role="progressbar"
                             style="width: {{ $exportProgress }}%; background:linear-gradient(135deg, #0ea5e9 0%, #6366f1 100%);">
                        </div>
                    </div>
                    <small class="text-muted fw-semibold">{{ $exportProgress }}% completado</small>

                    <div class="mt-4">
                        <small class="text-muted">
                            <i class="ri ri-time-line me-1"></i>
                            Por favor no cierres esta ventana
                        </small>
                    </div>
                </div>
            </div>
        @endif

        {{-- Empty state when not exporting --}}
        @if($exportStep === 4 && !$isExporting)
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <div class="mb-3">
                        <i class="ri ri-check-line ri-5x text-success mb-3 d-block"></i>
                        <h5 class="fw-semibold mb-2">Exportación completada</h5>
                        <p class="text-muted">El archivo se ha descargado exitosamente</p>
                    </div>
                    <button wire:click="resetExport" class="btn btn-primary">
                        <i class="ri ri-refresh-line me-1"></i>Nueva exportación
                    </button>
                </div>
            </div>
        @endif
    @endif

    @if($activeTab === 'import')
        {{-- ==================== IMPORT TAB ==================== --}}

        {{-- Wizard Steps Indicator --}}
        <div class="row g-3 mb-4">
            <div class="col-3">
                <div class="wizard-step {{ $importStep >= 1 ? 'active' : '' }} {{ $importStep > 1 ? 'completed' : '' }}">
                    <div class="text-center">
                        <div class="step-circle mx-auto mb-2">
                            @if($importStep > 1)<i class="ri ri-check-line"></i>@else 1 @endif
                        </div>
                        <small class="fw-semibold">Subir archivo</small>
                    </div>
                </div>
            </div>
            <div class="col-3">
                <div class="wizard-step {{ $importStep >= 2 ? 'active' : '' }} {{ $importStep > 2 ? 'completed' : '' }}">
                    <div class="text-center">
                        <div class="step-circle mx-auto mb-2">
                            @if($importStep > 2)<i class="ri ri-check-line"></i>@else 2 @endif
                        </div>
                        <small class="fw-semibold">Validación</small>
                    </div>
                </div>
            </div>
            <div class="col-3">
                <div class="wizard-step {{ $importStep >= 3 ? 'active' : '' }} {{ $importStep > 3 ? 'completed' : '' }}">
                    <div class="text-center">
                        <div class="step-circle mx-auto mb-2">
                            @if($importStep > 3)<i class="ri ri-check-line"></i>@else 3 @endif
                        </div>
                        <small class="fw-semibold">Confirmación</small>
                    </div>
                </div>
            </div>
            <div class="col-3">
                <div class="wizard-step {{ $importStep >= 4 ? 'active' : '' }}">
                    <div class="text-center">
                        <div class="step-circle mx-auto mb-2">
                            4
                        </div>
                        <small class="fw-semibold">Progreso</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- Step 1: Upload File --}}
        @if($importStep === 1)
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="fw-semibold mb-4">
                        <i class="ri ri-upload-cloud-2-line me-2 text-primary"></i>
                        Subir Archivo SQL
                    </h5>

                    <div class="upload-zone text-center" @if(!$uploadedFile) wire:click="$dispatch('trigger-file-upload')" @endif>
                        <i class="ri ri-file-upload-line ri-3x text-primary mb-3 d-block"></i>
                        <h5 class="fw-semibold">Arrastra tu archivo .sql aquí</h5>
                        <p class="text-muted mb-3">o</p>
                        <input type="file" wire:model="uploadedFile" accept=".sql,.sql.gz" class="form-control" style="max-width:400px; margin:0 auto;">
                        <small class="text-muted d-block mt-2">
                            <i class="ri ri-information-line me-1"></i>
                            Formatos aceptados: .sql, .sql.gz (máx. 100MB)
                        </small>
                    </div>

                    @if($uploadedFile)
                        <div class="alert alert-info mt-3">
                            <div class="d-flex align-items-center gap-2">
                                <i class="ri ri-file-text-line ri-lg"></i>
                                <div class="flex-grow-1">
                                    <strong>Archivo seleccionado:</strong> {{ $importFileName }}<br>
                                    <small>Tamaño: {{ number_format($importFileSize / 1024 / 1024, 2) }} MB</small>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 d-flex justify-content-between">
                            <button wire:click="$set('uploadedFile', null)" class="btn btn-outline-secondary">
                                <i class="ri ri-close-line me-1"></i>Cancelar
                            </button>
                            <button wire:click="validateImportFile" class="btn btn-primary">
                                Validar Archivo <i class="ri ri-check-line ms-1"></i>
                            </button>
                        </div>
                    @endif

                    @error('uploadedFile')
                        <div class="alert alert-danger mt-3">
                            <i class="ri ri-error-warning-line me-1"></i>{{ $message }}
                        </div>
                    @enderror
                </div>
            </div>
        @endif

        {{-- Step 2: Validation Results --}}
        @if($importStep === 2 && $importValidationResults)
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="fw-semibold mb-4">
                        <i class="ri ri-shield-check-line me-2 text-success"></i>
                        Resultados de Validación
                    </h5>

                    <div class="alert alert-success">
                        <div class="d-flex align-items-start gap-2">
                            <i class="ri ri-check-line ri-lg"></i>
                            <div>
                                <strong>Archivo válido:</strong> El archivo SQL ha sido validado exitosamente
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="option-card text-center">
                                <i class="ri ri-database-2-line ri-2x text-primary mb-2 d-block"></i>
                                <h6 class="fw-semibold mb-1">Tablas detectadas</h6>
                                <h3 class="mb-0 text-primary">{{ $importValidationResults['total_tables'] }}</h3>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="option-card text-center">
                                <i class="ri ri-code-line ri-2x text-success mb-2 d-block"></i>
                                <h6 class="fw-semibold mb-1">Sentencias</h6>
                                <h3 class="mb-0 text-success">{{ $importValidationResults['total_statements'] }}</h3>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="option-card text-center">
                                <i class="ri ri-hard-drive-2-line ri-2x text-info mb-2 d-block"></i>
                                <h6 class="fw-semibold mb-1">Tamaño archivo</h6>
                                <h3 class="mb-0 text-info">{{ number_format($importFileSize / 1024 / 1024, 2) }} MB</h3>
                            </div>
                        </div>
                    </div>

                    @if($importValidationResults['has_structure'])
                        <div class="alert alert-info">
                            <i class="ri ri-building-line me-1"></i>
                            <strong>Estructura:</strong> El archivo contiene definiciones de tablas (CREATE TABLE)
                        </div>
                    @endif

                    @if($importValidationResults['has_data'])
                        <div class="alert alert-info">
                            <i class="ri ri-database-line me-1"></i>
                            <strong>Datos:</strong> El archivo contiene registros de datos (INSERT INTO)
                        </div>
                    @endif

                    @if(count($importValidationResults['warnings']) > 0)
                        <div class="alert alert-warning">
                            <h6 class="fw-semibold mb-2">
                                <i class="ri ri-error-warning-line me-1"></i>Advertencias:
                            </h6>
                            <ul class="mb-0">
                                @foreach($importValidationResults['warnings'] as $warning)
                                    <li>{{ $warning }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="mt-4 pt-3 border-top d-flex justify-content-between">
                        <button wire:click="previousImportStep" class="btn btn-outline-secondary">
                            <i class="ri ri-arrow-left-line me-1"></i>Anterior
                        </button>
                        <button wire:click="nextImportStep" class="btn btn-primary">
                            Siguiente <i class="ri ri-arrow-right-line ms-1"></i>
                        </button>
                    </div>
                </div>
            </div>
        @endif

        {{-- Step 3: Confirmation --}}
        @if($importStep === 3)
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <div class="mb-4">
                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                             style="width:80px; height:80px; background:linear-gradient(135deg, #dc2626 0%, #f97316 100%);">
                            <i class="ri ri-alert-line ri-3x text-white"></i>
                        </div>
                        <h4 class="fw-semibold mb-2">¿Confirmar importación?</h4>
                        <p class="text-muted">Esta operación restaurará la base de datos con el archivo seleccionado</p>
                    </div>

                    <div class="alert alert-danger text-start">
                        <div class="d-flex align-items-start gap-2">
                            <i class="ri ri-error-warning-line ri-lg"></i>
                            <div>
                                <strong>¡ADVERTENCIA CRÍTICA!</strong>
                                <ul class="mb-0 mt-2">
                                    <li>Se solicitará tu contraseña para confirmar</li>
                                    <li>La acción será registrada en el log de auditoría</li>
                                    <li>Las tablas existentes serán reemplazadas</li>
                                    <li>Esta operación NO se puede deshacer</li>
                                    <li>Asegúrate de tener un respaldo antes de continuar</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-top d-flex justify-content-between">
                        <button wire:click="previousImportStep" class="btn btn-outline-secondary">
                            <i class="ri ri-arrow-left-line me-1"></i>Anterior
                        </button>
                        <button wire:click="requestPasswordVerification('import')" class="btn btn-danger">
                            <i class="ri ri-lock-line me-1"></i>Confirmar Importación
                        </button>
                    </div>
                </div>
            </div>
        @endif

        {{-- Step 4: Progress --}}
        @if($importStep === 4 && $isImporting)
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <div class="spinner-border mb-3" style="width: 3rem; height: 3rem; border-width: 3px; color:#dc2626;"></div>
                    <h5 class="fw-semibold mb-2">Importando base de datos...</h5>
                    <p class="text-muted mb-4">Ejecutando sentencias SQL</p>

                    <div class="progress mb-3" style="height: 12px; max-width:400px; margin:0 auto;">
                        <div class="progress-bar progress-bar-animated bg-danger"
                             role="progressbar"
                             style="width: {{ $importProgress }}%;">
                        </div>
                    </div>
                    <small class="text-muted fw-semibold">{{ $importProgress }}% completado</small>

                    <div class="mt-4">
                        <small class="text-muted">
                            <i class="ri ri-time-line me-1"></i>
                            Por favor no cierres esta ventana
                        </small>
                    </div>
                </div>
            </div>
        @endif

        {{-- Empty state when not importing --}}
        @if($importStep === 4 && !$isImporting)
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <div class="mb-3">
                        <i class="ri ri-check-line ri-5x text-success mb-3 d-block"></i>
                        <h5 class="fw-semibold mb-2">Importación completada</h5>
                        <p class="text-muted">La base de datos ha sido restaurada exitosamente</p>
                    </div>
                    <button wire:click="resetImport" class="btn btn-primary">
                        <i class="ri ri-refresh-line me-1"></i>Nueva importación
                    </button>
                </div>
            </div>
        @endif
    @endif

    {{-- Password Confirmation Modal --}}
    <div wire:ignore.self class="modal fade" id="passwordModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-warning text-dark border-0">
                    <h5 class="modal-title fw-semibold">
                        <i class="ri ri-lock-line me-2"></i>
                        Confirmación de Seguridad
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning mb-3">
                        <div class="d-flex align-items-start gap-2">
                            <i class="ri ri-error-warning-line ri-lg"></i>
                            <div>
                                <strong>Advertencia:</strong> Esta es una operación crítica del sistema que modificará la base de datos.
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            <i class="ri ri-lock-password-line me-1"></i>
                            Ingresa tu contraseña para continuar
                        </label>
                        <input type="password" wire:model="password" class="form-control"
                               placeholder="Tu contraseña de administrador"
                               id="passwordInput">
                        @error('password')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="alert alert-info">
                        <small>
                            <i class="ri ri-information-line me-1"></i>
                            Esta acción será registrada en el log de auditoría.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button wire:click="verifyPassword" class="btn btn-warning">
                        <i class="ri ri-check-line me-1"></i>
                        Confirmar y Continuar
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('livewire:init', function () {
            Livewire.on('show-password-modal', () => {
                const modal = new bootstrap.Modal(document.getElementById('passwordModal'));
                modal.show();

                // Focus on password input
                setTimeout(() => {
                    const passwordInput = document.getElementById('passwordInput');
                    if (passwordInput) {
                        passwordInput.focus();
                    }
                }, 500);
            });

            Livewire.on('hide-password-modal', () => {
                const modalElement = document.getElementById('passwordModal');
                const modal = bootstrap.Modal.getInstance(modalElement);
                if (modal) {
                    modal.hide();
                }
            });

            // Listen for Enter key in password input
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && document.activeElement.id === 'passwordInput') {
                    Livewire.dispatch('verifyPassword');
                }
            });

            // Handle download trigger
            Livewire.on('trigger-download', ({ url }) => {
                // Create a temporary link and click it to trigger download
                const link = document.createElement('a');
                link.href = url;
                link.download = '';
                link.style.display = 'none';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            });
        });
    </script>
    @endpush
</div>
