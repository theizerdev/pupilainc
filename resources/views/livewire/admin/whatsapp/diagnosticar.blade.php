<div>
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h4 class="card-title mb-0">
                <i class="ri ri-tools-line me-2 text-primary"></i>Diagnóstico de WhatsApp
            </h4>
            <button wire:click="realizarDiagnostico" 
                    class="btn btn-primary btn-sm"
                    wire:loading.attr="disabled">
                <span wire:loading.remove>
                    <i class="ri ri-refresh-line me-1"></i>Reiniciar Diagnóstico
                </span>
                <span wire:loading>
                    <span class="spinner-border spinner-border-sm me-1"></span>Analizando...
                </span>
            </button>
        </div>
        
        <div class="card-body">
            @if($ejecutando)
                <div class="text-center py-5">
                    <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                        <span class="visually-hidden">Ejecutando diagnóstico...</span>
                    </div>
                    <h5 class="mb-2">Realizando diagnóstico</h5>
                    <p class="text-muted">Por favor espere mientras verificamos la configuración...</p>
                </div>
            @else
                <div class="row g-4">
                    <!-- Configuración Básica -->
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-{{ $diagnostico['configuracion']['estado'] ?? 'secondary' }} text-white">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0 text-white">
                                        <i class="ri ri-settings-3-line me-2"></i>Configuración Básica
                                    </h5>
                                    <span class="badge bg-white text-{{ $diagnostico['configuracion']['estado'] ?? 'secondary' }}">
                                        {{ strtoupper($diagnostico['configuracion']['estado'] ?? 'N/A') }}
                                    </span>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <ul class="list-group list-group-flush">
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                Usuario autenticado
                                                <span class="badge bg-{{ $diagnostico['configuracion']['usuario_autenticado'] ? 'success' : 'danger' }}">
                                                    {{ $diagnostico['configuracion']['usuario_autenticado'] ? 'Sí' : 'No' }}
                                                </span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                Empresa asignada
                                                <span class="badge bg-{{ $diagnostico['configuracion']['empresa_asignada'] ? 'success' : 'danger' }}">
                                                    {{ $diagnostico['configuracion']['empresa_asignada'] ? 'Sí' : 'No' }}
                                                </span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                API Key configurada
                                                <span class="badge bg-{{ $diagnostico['configuracion']['whatsapp_api_key'] ? 'success' : 'danger' }}">
                                                    {{ $diagnostico['configuracion']['whatsapp_api_key'] ? 'Sí' : 'No' }}
                                                </span>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="alert alert-{{ $diagnostico['configuracion']['estado'] ?? 'secondary' }}" role="alert">
                                            <h6 class="alert-heading">
                                                <i class="ri ri-information-line me-2"></i>Resultado
                                            </h6>
                                            <p class="mb-0">{{ $diagnostico['configuracion']['mensaje'] ?? 'No diagnosticado' }}</p>
                                            @if(isset($diagnostico['configuracion']['company_id']))
                                                <hr>
                                                <p class="mb-0"><small>ID Empresa: {{ $diagnostico['configuracion']['company_id'] }}</small></p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Conexión API -->
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-{{ $diagnostico['conexion']['estado'] ?? 'secondary' }} text-white">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0 text-white">
                                        <i class="ri ri-wifi-line me-2"></i>Conexión con API
                                    </h5>
                                    <span class="badge bg-white text-{{ $diagnostico['conexion']['estado'] ?? 'secondary' }}">
                                        {{ strtoupper($diagnostico['conexion']['estado'] ?? 'N/A') }}
                                    </span>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <ul class="list-group list-group-flush">
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                URL Base
                                                <code>{{ $diagnostico['conexion']['url_base'] ?? 'N/A' }}</code>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                Puerto accesible
                                                <span class="badge bg-{{ $diagnostico['conexion']['puerto_accesible'] ? 'success' : 'danger' }}">
                                                    {{ $diagnostico['conexion']['puerto_accesible'] ? 'Sí' : 'No' }}
                                                </span>
                                            </li>
                                            @if(isset($diagnostico['conexion']['respuesta_servidor']))
                                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                                    Respuesta HTTP
                                                    <span class="badge bg-{{ $diagnostico['conexion']['respuesta_servidor'] >= 200 && $diagnostico['conexion']['respuesta_servidor'] < 300 ? 'success' : 'warning' }}">
                                                        {{ $diagnostico['conexion']['respuesta_servidor'] }}
                                                    </span>
                                                </li>
                                            @endif
                                        </ul>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="alert alert-{{ $diagnostico['conexion']['estado'] ?? 'secondary' }}" role="alert">
                                            <h6 class="alert-heading">
                                                <i class="ri ri-information-line me-2"></i>Resultado
                                            </h6>
                                            <p class="mb-0">{{ $diagnostico['conexion']['mensaje'] ?? 'No diagnosticado' }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Autenticación -->
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-{{ $diagnostico['autenticacion']['estado'] ?? 'secondary' }} text-white">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0 text-white">
                                        <i class="ri ri-shield-keyhole-line me-2"></i>Autenticación
                                    </h5>
                                    <span class="badge bg-white text-{{ $diagnostico['autenticacion']['estado'] ?? 'secondary' }}">
                                        {{ strtoupper($diagnostico['autenticacion']['estado'] ?? 'N/A') }}
                                    </span>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <ul class="list-group list-group-flush">
                                            @if(isset($diagnostico['autenticacion']['token_longitud']))
                                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                                    Longitud del token
                                                    <span class="badge bg-info">{{ $diagnostico['autenticacion']['token_longitud'] }} caracteres</span>
                                                </li>
                                            @endif
                                            @if(isset($diagnostico['autenticacion']['token_muestra']))
                                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                                    Muestra del token
                                                    <code>{{ $diagnostico['autenticacion']['token_muestra'] }}</code>
                                                </li>
                                            @endif
                                            @if(isset($diagnostico['autenticacion']['respuesta_autenticacion']))
                                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                                    Respuesta de autenticación
                                                    <span class="badge bg-{{ $diagnostico['autenticacion']['respuesta_autenticacion'] === 200 ? 'success' : ($diagnostico['autenticacion']['respuesta_autenticacion'] === 401 ? 'danger' : 'warning') }}">
                                                        {{ $diagnostico['autenticacion']['respuesta_autenticacion'] }}
                                                    </span>
                                                </li>
                                            @endif
                                        </ul>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="alert alert-{{ $diagnostico['autenticacion']['estado'] ?? 'secondary' }}" role="alert">
                                            <h6 class="alert-heading">
                                                <i class="ri ri-information-line me-2"></i>Resultado
                                            </h6>
                                            <p class="mb-0">{{ $diagnostico['autenticacion']['mensaje'] ?? 'No diagnosticado' }}</p>
                                            
                                            @if(isset($diagnostico['autenticacion']['detalles_error']) && $mostrarDetalles)
                                                <hr>
                                                <pre class="mb-0 small">{{ json_encode($diagnostico['autenticacion']['detalles_error'], JSON_PRETTY_PRINT) }}</pre>
                                            @endif
                                        </div>
                                        
                                        @if(isset($diagnostico['autenticacion']['detalles_error']))
                                            <button wire:click="toggleDetalles" class="btn btn-sm btn-outline-secondary">
                                                <i class="ri ri-eye-line me-1"></i>
                                                {{ $mostrarDetalles ? 'Ocultar' : 'Mostrar' }} detalles técnicos
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Permisos -->
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-{{ $diagnostico['permisos']['estado'] ?? 'secondary' }} text-white">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0 text-white">
                                        <i class="ri ri-lock-unlock-line me-2"></i>Permisos
                                    </h5>
                                    <span class="badge bg-white text-{{ $diagnostico['permisos']['estado'] ?? 'secondary' }}">
                                        {{ strtoupper($diagnostico['permisos']['estado'] ?? 'N/A') }}
                                    </span>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <ul class="list-group list-group-flush">
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                Permiso de envío
                                                <span class="badge bg-{{ $diagnostico['permisos']['permiso_envio'] ? 'success' : 'danger' }}">
                                                    {{ $diagnostico['permisos']['permiso_envio'] ? 'Concedido' : 'Denegado' }}
                                                </span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                Permiso de lectura
                                                <span class="badge bg-{{ $diagnostico['permisos']['permiso_lectura'] ? 'success' : 'danger' }}">
                                                    {{ $diagnostico['permisos']['permiso_lectura'] ? 'Concedido' : 'Denegado' }}
                                                </span>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="alert alert-{{ $diagnostico['permisos']['estado'] ?? 'secondary' }}" role="alert">
                                            <h6 class="alert-heading">
                                                <i class="ri ri-information-line me-2"></i>Resultado
                                            </h6>
                                            <p class="mb-0">{{ $diagnostico['permisos']['mensaje'] ?? 'No diagnosticado' }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Resumen General -->
                    @php
                        $totalTests = 4;
                        $passedTests = 0;
                        $failedTests = 0;
                        
                        foreach (['configuracion', 'conexion', 'autenticacion', 'permisos'] as $test) {
                            if (isset($diagnostico[$test]['estado'])) {
                                if ($diagnostico[$test]['estado'] === 'ok') {
                                    $passedTests++;
                                } elseif ($diagnostico[$test]['estado'] === 'error') {
                                    $failedTests++;
                                }
                            }
                        }
                        $warningTests = $totalTests - $passedTests - $failedTests;
                    @endphp

                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-{{ $failedTests > 0 ? 'danger' : ($warningTests > 0 ? 'warning' : 'success') }} text-white">
                                <h5 class="card-title mb-0 text-white">
                                    <i class="ri ri-pie-chart-line me-2"></i>Resumen del Diagnóstico
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="d-flex justify-content-around text-center">
                                            <div>
                                                <h2 class="text-success">{{ $passedTests }}</h2>
                                                <p class="mb-0">Pruebas pasadas</p>
                                            </div>
                                            <div>
                                                <h2 class="text-warning">{{ $warningTests }}</h2>
                                                <p class="mb-0">Advertencias</p>
                                            </div>
                                            <div>
                                                <h2 class="text-danger">{{ $failedTests }}</h2>
                                                <p class="mb-0">Errores</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="progress-stacked">
                                            <div class="progress" role="progressbar" style="height: 20px">
                                                <div class="progress-bar bg-success" style="width: {{ ($passedTests/$totalTests)*100 }}%"></div>
                                                <div class="progress-bar bg-warning" style="width: {{ ($warningTests/$totalTests)*100 }}%"></div>
                                                <div class="progress-bar bg-danger" style="width: {{ ($failedTests/$totalTests)*100 }}%"></div>
                                            </div>
                                        </div>
                                        
                                        @if($failedTests > 0)
                                            <div class="alert alert-danger mt-3 mb-0" role="alert">
                                                <h6 class="alert-heading">
                                                    <i class="ri ri-error-warning-line me-2"></i>Acción requerida
                                                </h6>
                                                <p class="mb-0">Hay {{ $failedTests }} errores críticos que deben ser resueltos antes de poder enviar mensajes.</p>
                                            </div>
                                        @elseif($warningTests > 0)
                                            <div class="alert alert-warning mt-3 mb-0" role="alert">
                                                <h6 class="alert-heading">
                                                    <i class="ri ri-alert-line me-2"></i>Advertencia
                                                </h6>
                                                <p class="mb-0">Hay {{ $warningTests }} advertencias que podrían afectar el funcionamiento.</p>
                                            </div>
                                        @else
                                            <div class="alert alert-success mt-3 mb-0" role="alert">
                                                <h6 class="alert-heading">
                                                    <i class="ri ri-checkbox-circle-line me-2"></i>¡Todo en orden!
                                                </h6>
                                                <p class="mb-0">Toda la configuración está correcta y lista para enviar mensajes.</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@push('styles')
<style>
.card {
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

.progress-stacked .progress {
    border-radius: 10px;
}

.list-group-item {
    border-left: none;
    border-right: none;
}

.list-group-item:first-child {
    border-top: none;
}

.list-group-item:last-child {
    border-bottom: none;
}
</style>
@endpush