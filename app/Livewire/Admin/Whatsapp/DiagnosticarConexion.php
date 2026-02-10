<?php

namespace App\Livewire\Admin\Whatsapp;

use Livewire\Component;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DiagnosticarConexion extends Component
{
    public $diagnostico = [];
    public $ejecutando = false;
    public $mostrarDetalles = false;
    public $configuracionDetectada = [];

    public function mount()
    {
        $this->detectarConfiguracion();
        $this->realizarDiagnostico();
    }

    public function detectarConfiguracion()
    {
        $empresa = auth()->user()->empresa ?? null;
        
        $this->configuracionDetectada = [
            'empresa_id' => $empresa ? $empresa->id : 'N/A',
            'api_key_configurada' => $empresa && !empty($empresa->whatsapp_api_key),
            'telefono_configurado' => $empresa && !empty($empresa->whatsapp_phone),
            'url_api_configurada' => config('services.whatsapp.api_url'),
            'usuario_autenticado' => auth()->check()
        ];
    }

    public function realizarDiagnostico()
    {
        $this->ejecutando = true;
        $this->diagnostico = [];

        try {
            // 1. Verificar configuración detectada
            $this->verificarConfiguracionDetectada();
            
            // 2. Verificar conexión directa
            $this->verificarConexionDirecta();
            
            // 3. Verificar conexión a través de diferentes métodos
            $this->verificarMetodosConexion();
            
            // 4. Verificar estado del servicio
            $this->verificarEstadoServicio();
            
            // 5. Verificar autenticación
            $this->verificarAutenticacionCompleta();
            
        } catch (\Exception $e) {
            $this->diagnostico['error_general'] = [
                'estado' => 'error',
                'mensaje' => 'Error durante el diagnóstico: ' . $e->getMessage(),
                'detalle' => $e->getTraceAsString()
            ];
            Log::error('Error en diagnóstico de conexión WhatsApp', [
                'exception' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
        }

        $this->ejecutando = false;
    }

    private function verificarConfiguracionDetectada()
    {
        $this->diagnostico['configuracion_detectada'] = [
            'empresa_correcta' => $this->configuracionDetectada['empresa_id'] !== 'N/A',
            'api_key_presente' => $this->configuracionDetectada['api_key_configurada'],
            'telefono_presente' => $this->configuracionDetectada['telefono_configurado'],
            'url_api_definida' => !empty($this->configuracionDetectada['url_api_configurada']),
            'usuario_valido' => $this->configuracionDetectada['usuario_autenticado']
        ];

        $problemas = [];
        if (!$this->configuracionDetectada['empresa_id'] !== 'N/A') {
            $problemas[] = 'Usuario sin empresa asignada';
        }
        if (!$this->configuracionDetectada['api_key_configurada']) {
            $problemas[] = 'API Key no configurada';
        }
        if (!$this->configuracionDetectada['url_api_configurada']) {
            $problemas[] = 'URL de API no definida';
        }

        if (empty($problemas)) {
            $this->diagnostico['configuracion_detectada']['estado'] = 'ok';
            $this->diagnostico['configuracion_detectada']['mensaje'] = 'Configuración básica detectada correctamente';
        } else {
            $this->diagnostico['configuracion_detectada']['estado'] = 'error';
            $this->diagnostico['configuracion_detectada']['mensaje'] = 'Problemas de configuración: ' . implode(', ', $problemas);
        }
    }

    private function verificarConexionDirecta()
    {
        $baseUrl = config('services.whatsapp.api_url', 'http://localhost:3000');
        
        $this->diagnostico['conexion_directa'] = [
            'url_base' => $baseUrl,
            'metodo_http' => 'GET',
            'endpoint' => '/health',
            'timeout_configurado' => 5,
            'respuesta_recibida' => false,
            'codigo_respuesta' => null,
            'tiempo_respuesta' => null
        ];

        $startTime = microtime(true);
        
        try {
            $response = Http::timeout(5)->get($baseUrl . '/health');
            
            $endTime = microtime(true);
            $responseTime = round(($endTime - $startTime) * 1000, 2);
            
            $this->diagnostico['conexion_directa']['respuesta_recibida'] = true;
            $this->diagnostico['conexion_directa']['codigo_respuesta'] = $response->status();
            $this->diagnostico['conexion_directa']['tiempo_respuesta'] = $responseTime;
            $this->diagnostico['conexion_directa']['body_respuesta'] = $response->body();
            
            if ($response->successful()) {
                $this->diagnostico['conexion_directa']['estado'] = 'ok';
                $this->diagnostico['conexion_directa']['mensaje'] = "Conexión exitosa en {$responseTime}ms";
            } else {
                $this->diagnostico['conexion_directa']['estado'] = 'advertencia';
                $this->diagnostico['conexion_directa']['mensaje'] = "Servidor responde con código {$response->status()}";
            }
            
        } catch (\Exception $e) {
            $endTime = microtime(true);
            $responseTime = round(($endTime - $startTime) * 1000, 2);
            
            $this->diagnostico['conexion_directa']['tiempo_respuesta'] = $responseTime;
            $this->diagnostico['conexion_directa']['estado'] = 'error';
            
            // Análisis específico del error
            if (strpos($e->getMessage(), 'cURL error 7') !== false) {
                $this->diagnostico['conexion_directa']['mensaje'] = 'No se puede conectar al servidor (Connection refused)';
                $this->diagnostico['conexion_directa']['posible_causa'] = 'Servidor apagado o puerto bloqueado';
            } elseif (strpos($e->getMessage(), 'cURL error 6') !== false) {
                $this->diagnostico['conexion_directa']['mensaje'] = 'No se puede resolver el nombre del host';
                $this->diagnostico['conexion_directa']['posible_causa'] = 'URL incorrecta o DNS no disponible';
            } elseif (strpos($e->getMessage(), 'cURL error 28') !== false) {
                $this->diagnostico['conexion_directa']['mensaje'] = 'Tiempo de espera agotado';
                $this->diagnostico['conexion_directa']['posible_causa'] = 'Servidor lento o inaccesible';
            } else {
                $this->diagnostico['conexion_directa']['mensaje'] = 'Error de conexión: ' . $e->getMessage();
                $this->diagnostico['conexion_directa']['posible_causa'] = 'Error desconocido de red';
            }
        }
    }

    private function verificarMetodosConexion()
    {
        $baseUrl = config('services.whatsapp.api_url', 'http://localhost:3000');
        $metodos = ['HEAD', 'GET'];
        $resultados = [];
        
        foreach ($metodos as $metodo) {
            try {
                $startTime = microtime(true);
                $response = Http::timeout(3)->{$metodo}($baseUrl . '/health');
                $endTime = microtime(true);
                
                $resultados[$metodo] = [
                    'exitoso' => $response->successful(),
                    'codigo' => $response->status(),
                    'tiempo_ms' => round(($endTime - $startTime) * 1000, 2)
                ];
            } catch (\Exception $e) {
                $resultados[$metodo] = [
                    'exitoso' => false,
                    'error' => $e->getMessage(),
                    'tiempo_ms' => 'N/A'
                ];
            }
        }
        
        $this->diagnostico['metodos_conexion'] = $resultados;
        
        // Determinar estado general
        $metodosExitosos = collect($resultados)->filter(fn($r) => $r['exitoso'] ?? false)->count();
        
        if ($metodosExitosos === count($metodos)) {
            $this->diagnostico['metodos_conexion_estado'] = 'ok';
            $this->diagnostico['metodos_conexion_mensaje'] = 'Todos los métodos de conexión funcionan correctamente';
        } elseif ($metodosExitosos > 0) {
            $this->diagnostico['metodos_conexion_estado'] = 'advertencia';
            $this->diagnostico['metodos_conexion_mensaje'] = 'Algunos métodos de conexión tienen problemas';
        } else {
            $this->diagnostico['metodos_conexion_estado'] = 'error';
            $this->diagnostico['metodos_conexion_mensaje'] = 'Todos los métodos de conexión fallaron';
        }
    }

    private function verificarEstadoServicio()
    {
        $baseUrl = config('services.whatsapp.api_url', 'http://localhost:3000');
        
        $this->diagnostico['estado_servicio'] = [
            'puerto_3000_escuchando' => false,
            'servicio_responde_health' => false,
            'version_api' => null,
            'uptime' => null
        ];

        try {
            // Verificar endpoint específico de estado
            $response = Http::timeout(5)->get($baseUrl . '/status');
            
            if ($response->successful()) {
                $data = $response->json();
                $this->diagnostico['estado_servicio']['servicio_responde_health'] = true;
                $this->diagnostico['estado_servicio']['version_api'] = $data['version'] ?? 'N/A';
                $this->diagnostico['estado_servicio']['uptime'] = $data['uptime'] ?? 'N/A';
                
                $this->diagnostico['estado_servicio']['estado'] = 'ok';
                $this->diagnostico['estado_servicio']['mensaje'] = 'Servicio WhatsApp operativo y respondiendo correctamente';
            } else {
                $this->diagnostico['estado_servicio']['estado'] = 'advertencia';
                $this->diagnostico['estado_servicio']['mensaje'] = 'Servicio responde pero con error: ' . $response->status();
            }
            
        } catch (\Exception $e) {
            $this->diagnostico['estado_servicio']['estado'] = 'error';
            $this->diagnostico['estado_servicio']['mensaje'] = 'No se puede obtener el estado del servicio: ' . $e->getMessage();
        }
    }

    private function verificarAutenticacionCompleta()
    {
        $empresa = auth()->user()->empresa ?? null;
        if (!$empresa || empty($empresa->whatsapp_api_key)) {
            $this->diagnostico['autenticacion_completa'] = [
                'estado' => 'error',
                'mensaje' => 'No hay API Key configurada para verificar autenticación'
            ];
            return;
        }

        $baseUrl = config('services.whatsapp.api_url', 'http://localhost:3000');
        
        $this->diagnostico['autenticacion_completa'] = [
            'token_longitud' => strlen($empresa->whatsapp_api_key),
            'token_muestra' => substr($empresa->whatsapp_api_key, 0, 10) . '...',
            'respuesta_autenticacion' => null,
            'datos_usuario' => null
        ];

        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $empresa->whatsapp_api_key,
                    'X-Company-Id' => (string) $empresa->id,
                    'Content-Type' => 'application/json'
                ])
                ->get($baseUrl . '/status');

            $this->diagnostico['autenticacion_completa']['respuesta_autenticacion'] = $response->status();
            
            if ($response->status() === 401) {
                $this->diagnostico['autenticacion_completa']['estado'] = 'error';
                $this->diagnostico['autenticacion_completa']['mensaje'] = 'Token inválido o expirado';
                $errorDetails = $response->json();
                $this->diagnostico['autenticacion_completa']['detalles_error'] = $errorDetails;
            } elseif ($response->status() === 403) {
                $this->diagnostico['autenticacion_completa']['estado'] = 'error';
                $this->diagnostico['autenticacion_completa']['mensaje'] = 'Token válido pero sin permisos suficientes';
            } elseif ($response->successful()) {
                $this->diagnostico['autenticacion_completa']['estado'] = 'ok';
                $this->diagnostico['autenticacion_completa']['mensaje'] = 'Autenticación exitosa';
                $this->diagnostico['autenticacion_completa']['datos_usuario'] = $response->json();
            } else {
                $this->diagnostico['autenticacion_completa']['estado'] = 'advertencia';
                $this->diagnostico['autenticacion_completa']['mensaje'] = 'Respuesta inesperada de autenticación: ' . $response->status();
            }
            
        } catch (\Exception $e) {
            $this->diagnostico['autenticacion_completa']['estado'] = 'error';
            $this->diagnostico['autenticacion_completa']['mensaje'] = 'Error al verificar autenticación: ' . $e->getMessage();
        }
    }

    public function toggleDetalles()
    {
        $this->mostrarDetalles = !$this->mostrarDetalles;
    }

    public function render()
    {
        return view('livewire.admin.whatsapp.diagnosticar-conexion');
    }
}