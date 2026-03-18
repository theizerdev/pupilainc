<div>
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <div>
                <h4 class="card-title mb-1">
                    <i class="ri ri-wifi-line me-2 text-primary"></i>Diagnóstico de Conexión WhatsApp
                </h4>
                <p class="text-muted mb-0">Análisis detallado del problema de conexión aparentemente contradictorio</p>
            </div>
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
                    <h5 class="mb-2">Realizando diagnóstico detallado</h5>
                    <p class="text-muted">Analizando configuración, conexión y autenticación...</p>
                </div>
            @else
                <!-- Configuración Detectada -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-{{ $diagnostico['configuracion_detectada']['estado'] ?? 'secondary' }} text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0 text-white">
                                <i class="ri ri-settings-3-line me-2"></i>Configuración Detectada
                            </h5>
                            <span class="badge bg-white text-{{ $diagnostico['configuracion_detectada']['estado'] ?? 'secondary' }}">
                                {{ strtoupper($diagnostico['configuracion_detectada']['estado'] ?? 'N/A') }}
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Empresa ID
                                        <span class="badge bg-info">{{ $configuracionDetectada['empresa_id'] }}</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        API Key configurada
                                        <span class="badge bg-{{ $configuracionDetectada['api_key_configurada'] ? 'success' : 'danger' }}">
                                            {{ $configuracionDetectada['api_key_configurada'] ? 'Sí' : 'No' }}
                                        </span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Teléfono configurado
                                        <span class="badge bg-{{ $configuracionDetectada['telefono_configurado'] ? 'success' : 'warning' }}">
                                            {{ $configuracionDetectada['telefono_configurado'] ? 'Sí' : 'No' }}
                                        </span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        URL API definida
                                        <span class="badge bg-{{ $configuracionDetectada['url_api_configurada'] ? 'success' : 'danger' }}">
                                            {{ $configuracionDetectada['url_api_configurada'] ? 'Sí' : 'No' }}
                                        </span>
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <div class="alert alert-{{ $diagnostico['configuracion_detectada']['estado'] ?? 'secondary' }}" role="alert">
                                    <h6 class="alert-heading">
                                        <i class="ri ri-information-line me-2"></i>Resultado
                                    </h6>
                                    <p class="mb-0">{{ $diagnostico['configuracion_detectada']['mensaje'] ?? 'No diagnosticado' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Conexión Directa -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-{{ $diagnostico['conexion_directa']['estado'] ?? 'secondary' }} text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0 text-white">
                                <i class="ri ri-plug-line me-2"></i>Conexión Directa
                            </h5>
                            <span class="badge bg-white text-{{ $diagnostico['conexion_directa']['estado'] ?? 'secondary' }}">
                                {{ strtoupper($diagnostico['conexion_directa']['estado'] ?? 'N/A') }}
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        URL Base
                                        <code>{{ $diagnostico['conexion_directa']['url_base'] ?? 'N/A' }}</code>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Endpoint
                                        <span class="badge bg-secondary">{{ $diagnostico['conexion_directa']['endpoint'] ?? 'N/A' }}</code>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Timeout
                                        <span class="badge bg-info">{{ $diagnostico['conexion_directa']['timeout_configurado'] ?? 'N/A' }} segundos</span>
                                    </li>
                                    @if(isset($diagnostico['conexion_directa']['respuesta_recibida']))
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Respuesta recibida
                                            <span class="badge bg-{{ $diagnostico['conexion_directa']['respuesta_recibida'] ? 'success' : 'danger' }}">
                                                {{ $diagnostico['conexion_directa']['respuesta_recibida'] ? 'Sí' : 'No' }}
                                            </span>
                                        </li>
                                    @endif
                                    @if(isset($diagnostico['conexion_directa']['codigo_respuesta']))
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Código HTTP
                                            <span class="badge bg-{{ $diagnostico['conexion_directa']['codigo_respuesta'] >= 200 && $diagnostico['conexion_directa']['codigo_respuesta'] < 300 ? 'success' : 'warning' }}">
                                                {{ $diagnostico['conexion_directa']['codigo_respuesta'] }}
                                            </span>
                                        </li>
                                    @endif
                                    @if(isset($diagnostico['conexion_directa']['tiempo_respuesta']))
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Tiempo de respuesta
                                            <span class="badge bg-{{ $diagnostico['conexion_directa']['tiempo_respuesta'] < 1000 ? 'success' : ($diagnostico['conexion_directa']['tiempo_respuesta'] < 3000 ? 'warning' : 'danger') }}">
                                                {{ $diagnostico['conexion_directa']['tiempo_respuesta'] }} ms
                                            </span>
                                        </li>
                                    @endif
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <div class="alert alert-{{ $diagnostico['conexion_directa']['estado'] ?? 'secondary' }}" role="alert">
                                    <h6 class="alert-heading">
                                        <i class="ri ri-information-line me-2"></i>Resultado
                                    </h6>
                                    <p class="mb-0">{{ $diagnostico['conexion_directa']['mensaje'] ?? 'No diagnosticado' }}</p>
                                    
                                    @if(isset($diagnostico['conexion_directa']['posible_causa']))
                                        <hr>
                                        <h6 class="mb-2">Posible causa:</h6>
                                        <p class="mb-0">{{ $diagnostico['conexion_directa']['posible_causa'] }}</p>
                                    @endif
                                    
                                    @if(isset($diagnostico['conexion_directa']['body_respuesta']) && $mostrarDetalles)
                                        <hr>
                                        <h6 class="mb-2">Respuesta completa:</h6>
                                        <pre class="mb-0 small bg-light p-2 rounded">{{ $diagnostico['conexion_directa']['body_respuesta'] }}</pre>
                                    @endif
                                </div>
                                
                                @if(isset($diagnostico['conexion_directa']['body_respuesta']))
                                    <button wire:click="toggleDetalles" class="btn btn-sm btn-outline-secondary">
                                        <i class="ri ri-eye-line me-1"></i>
                                        {{ $mostrarDetalles ? 'Ocultar' : 'Mostrar' }} detalles técnicos
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Métodos de Conexión -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-{{ $diagnostico['metodos_conexion_estado'] ?? 'secondary' }} text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0 text-white">
                                <i class="ri ri-exchange-line me-2"></i>Métodos de Conexión
                            </h5>
                            <span class="badge bg-white text-{{ $diagnostico['metodos_conexion_estado'] ?? 'secondary' }}">
                                {{ strtoupper($diagnostico['metodos_conexion_estado'] ?? 'N/A') }}
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Método HTTP</th>
                                                <th>Exitoso</th>
                                                <th>Código</th>
                                                <th>Tiempo (ms)</th>
                                                <th>Observaciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($diagnostico['metodos_conexion'] ?? [] as $metodo => $resultado)
                                                <tr>
                                                    <td><code>{{ $metodo }}</code></td>
                                                    <td>
                                                        <span class="badge bg-{{ $resultado['exitoso'] ? 'success' : 'danger' }}">
                                                            {{ $resultado['exitoso'] ? 'Sí' : 'No' }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        @if(isset($resultado['codigo']))
                                                            <span class="badge bg-{{ $resultado['codigo'] >= 200 && $resultado['codigo'] < 300 ? 'success' : 'warning' }}">
                                                                {{ $resultado['codigo'] }}
                                                            </span>
                                                        @else
                                                            <span class="text-muted">N/A</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if(isset($resultado['tiempo_ms']) && $resultado['tiempo_ms'] !== 'N/A')
                                                            <span class="badge bg-{{ $resultado['tiempo_ms'] < 1000 ? 'success' : ($resultado['tiempo_ms'] < 3000 ? 'warning' : 'danger') }}">
                                                                {{ $resultado['tiempo_ms'] }}
                                                            </span>
                                                        @else
                                                            <span class="text-muted">N/A</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if(isset($resultado['error']))
                                                            <span class="text-danger small">{{ $resultado['error'] }}</span>
                                                        @else
                                                            <span class="text-success small">Conexión estable</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                
                                <div class="alert alert-{{ $diagnostico['metodos_conexion_estado'] ?? 'secondary' }}" role="alert">
                                    <p class="mb-0">{{ $diagnostico['metodos_conexion_mensaje'] ?? 'No diagnosticado' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Estado del Servicio -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-{{ $diagnostico['estado_servicio']['estado'] ?? 'secondary' }} text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0 text-white">
                                <i class="ri ri-server-line me-2"></i>Estado del Servicio
                            </h5>
                            <span class="badge bg-white text-{{ $diagnostico['estado_servicio']['estado'] ?? 'secondary' }}">
                                {{ strtoupper($diagnostico['estado_servicio']['estado'] ?? 'N/A') }}
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Puerto 3000 escuchando
                                        <span class="badge bg-{{ $diagnostico['estado_servicio']['puerto_3000_escuchando'] ? 'success' : 'danger' }}">
                                            {{ $diagnostico['estado_servicio']['puerto_3000_escuchando'] ? 'Sí' : 'No' }}
                                        </span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Servicio responde /health
                                        <span class="badge bg-{{ $diagnostico['estado_servicio']['servicio_responde_health'] ? 'success' : 'danger' }}">
                                            {{ $diagnostico['estado_servicio']['servicio_responde_health'] ? 'Sí' : 'No' }}
                                        </span>
                                    </li>
                                    @if(isset($diagnostico['estado_servicio']['version_api']))
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Versión API
                                            <span class="badge bg-info">{{ $diagnostico['estado_servicio']['version_api'] }}</span>
                                        </li>
                                    @endif
                                    @if(isset($diagnostico['estado_servicio']['uptime']))
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Uptime
                                            <span class="badge bg-success">{{ $diagnostico['estado_servicio']['uptime'] }}</span>
                                        </li>
                                    @endif
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <div class="alert alert-{{ $diagnostico['estado_servicio']['estado'] ?? 'secondary' }}" role="alert">
                                    <h6 class="alert-heading">
                                        <i class="ri ri-information-line me-2"></i>Resultado
                                    </h6>
                                    <p class="mb-0">{{ $diagnostico['estado_servicio']['mensaje'] ?? 'No diagnosticado' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Autenticación Completa -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-{{ $diagnostico['autenticacion_completa']['estado'] ?? 'secondary' }} text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0 text-white">
                                <i class="ri ri-shield-keyhole-line me-2"></i>Autenticación Completa
                            </h5>
                            <span class="badge bg-white text-{{ $diagnostico['autenticacion_completa']['estado'] ?? 'secondary' }}">
                                {{ strtoupper($diagnostico['autenticacion_completa']['estado'] ?? 'N/A') }}
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <ul class="list-group list-group-flush">
                                    @if(isset($diagnostico['autenticacion_completa']['token_longitud']))
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Longitud del token
                                            <span class="badge bg-info">{{ $diagnostico['autenticacion_completa']['token_longitud'] }} caracteres</span>
                                        </li>
                                    @endif
                                    @if(isset($diagnostico['autenticacion_completa']['token_muestra']))
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Muestra del token
                                            <code>{{ $diagnostico['autenticacion_completa']['token_muestra'] }}</code>
                                        </li>
                                    @endif
                                    @if(isset($diagnostico['autenticacion_completa']['respuesta_autenticacion']))
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Respuesta HTTP
                                            <span class="badge bg-{{ $diagnostico['autenticacion_completa']['respuesta_autenticacion'] === 200 ? 'success' : ($diagnostico['autenticacion_completa']['respuesta_autenticacion'] === 401 ? 'danger' : 'warning') }}">
                                                {{ $diagnostico['autenticacion_completa']['respuesta_autenticacion'] }}
                                            </span>
                                        </li>
                                    @endif
                                    @if(isset($diagnostico['autenticacion_completa']['datos_usuario']))
                                        <li class="list-group-item">
                                            <small class="text-muted">Datos del usuario autenticado:</small>
                                            <pre class="mb-0 mt-2 small bg-light p-2 rounded">{{ json_encode($diagnostico['autenticacion_completa']['datos_usuario'], JSON_PRETTY_PRINT) }}</pre>
                                        </li>
                                    @endif
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <div class="alert alert-{{ $diagnostico['autenticacion_completa']['estado'] ?? 'secondary' }}" role="alert">
                                    <h6 class="alert-heading">
                                        <i class="ri ri-information-line me-2"></i>Resultado
                                    </h6>
                                    <p class="mb-0">{{ $diagnostico['autenticacion_completa']['mensaje'] ?? 'No diagnosticado' }}</p>
                                    
                                    @if(isset($diagnostico['autenticacion_completa']['detalles_error']) && $mostrarDetalles)
                                        <hr>
                                        <h6 class="mb-2">Detalles del error:</h6>
                                        <pre class="mb-0 small bg-light p-2 rounded">{{ json_encode($diagnostico['autenticacion_completa']['detalles_error'], JSON_PRETTY_PRINT) }}</pre>
                                    @endif
                                </div>
                                
                                @if(isset($diagnostico['autenticacion_completa']['detalles_error']))
                                    <button wire:click="toggleDetalles" class="btn btn-sm btn-outline-secondary">
                                        <i class="ri ri-eye-line me-1"></i>
                                        {{ $mostrarDetalles ? 'Ocultar' : 'Mostrar' }} detalles técnicos
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Análisis Final -->
                @php
                    $analisisFinal = [];
                    $totalPruebas = 5;
                    $pruebasPasadas = 0;
                    
                    // Contar pruebas exitosas
                    foreach (['configuracion_detectada', 'conexion_directa', 'metodos_conexion_estado', 'estado_servicio', 'autenticacion_completa'] as $prueba) {
                        if (isset($diagnostico[$prueba])) {
                            $estado = is_array($diagnostico[$prueba]) ? ($diagnostico[$prueba]['estado'] ?? 'N/A') : $diagnostico[$prueba];
                            if ($estado === 'ok') {
                                $pruebasPasadas++;
                            }
                        } elseif ($prueba === 'metodos_conexion_estado' && isset($diagnostico['metodos_conexion_estado'])) {
                            if ($diagnostico['metodos_conexion_estado'] === 'ok') {
                                $pruebasPasadas++;
                            }
                        }
                    }
                    
                    $porcentajeExito = ($pruebasPasadas / $totalPruebas) * 100;
                @endphp

                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-{{ $pruebasPasadas === $totalPruebas ? 'success' : ($pruebasPasadas >= $totalPruebas/2 ? 'warning' : 'danger') }} text-white">
                        <h5 class="card-title mb-0 text-white">
                            <i class="ri ri-analyze-line me-2"></i>Análisis Final
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <h5>Resumen del Diagnóstico</h5>
                                <div class="progress mb-3" style="height: 25px;">
                                    <div class="progress-bar bg-{{ $pruebasPasadas === $totalPruebas ? 'success' : ($pruebasPasadas >= $totalPruebas/2 ? 'warning' : 'danger') }}" 
                                         role="progressbar" 
                                         style="width: {{ $porcentajeExito }}%">
                                        {{ format_money($porcentajeExito, 1) }}%
                                    </div>
                                </div>
                                
                                <p><strong>Pruebas superadas:</strong> {{ $pruebasPasadas }} de {{ $totalPruebas }}</p>
                                
                                @if($pruebasPasadas === $totalPruebas)
                                    <div class="alert alert-success" role="alert">
                                        <h6 class="alert-heading">
                                            <i class="ri ri-checkbox-circle-line me-2"></i>¡Conexión completamente funcional!
                                        </h6>
                                        <p class="mb-0">Todos los componentes de la conexión WhatsApp están trabajando correctamente. Si aún experimentas problemas, podría ser un problema de lógica en la aplicación.</p>
                                    </div>
                                @elseif($pruebasPasadas >= $totalPruebas/2)
                                    <div class="alert alert-warning" role="alert">
                                        <h6 class="alert-heading">
                                            <i class="ri ri-alert-line me-2"></i>Conexión parcialmente funcional
                                        </h6>
                                        <p class="mb-0">Algunos componentes funcionan, pero hay {{ $totalPruebas - $pruebasPasadas }} áreas que necesitan atención.</p>
                                    </div>
                                @else
                                    <div class="alert alert-danger" role="alert">
                                        <h6 class="alert-heading">
                                            <i class="ri ri-error-warning-line me-2"></i>Problemas críticos de conexión
                                        </h6>
                                        <p class="mb-0">{{ $totalPruebas - $pruebasPasadas }} de {{ $totalPruebas }} componentes críticos están fallando.</p>
                                    </div>
                                @endif
                            </div>
                            <div class="col-md-4">
                                <h6>Próximos pasos recomendados:</h6>
                                <ul class="list-group list-group-flush">
                                    @if($pruebasPasadas < $totalPruebas)
                                        <li class="list-group-item">
                                            <i class="ri ri-refresh-line text-primary me-2"></i>Reiniciar el servicio WhatsApp
                                        </li>
                                        <li class="list-group-item">
                                            <i class="ri ri-settings-line text-warning me-2"></i>Verificar configuración de red
                                        </li>
                                        <li class="list-group-item">
                                            <i class="ri ri-key-line text-danger me-2"></i>Validar API Key y permisos
                                        </li>
                                    @else
                                        <li class="list-group-item">
                                            <i class="ri ri-bug-line text-info me-2"></i>Revisar logs de la aplicación
                                        </li>
                                        <li class="list-group-item">
                                            <i class="ri ri-code-line text-success me-2"></i>Depurar código de envío
                                        </li>
                                    @endif
                                </ul>
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

.progress {
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

.table th {
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 1px;
}
</style>
@endpush