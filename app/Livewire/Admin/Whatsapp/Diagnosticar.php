<?php

namespace App\Livewire\Admin\Whatsapp;

use Livewire\Component;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Diagnosticar extends Component
{
    public $diagnostico = [];
    public $ejecutando = false;
    public $mostrarDetalles = false;

    public function mount()
    {
        $this->realizarDiagnostico();
    }

    public function realizarDiagnostico()
    {
        $this->ejecutando = true;
        $this->diagnostico = [];

        try {
            // 1. Verificar configuración básica
            $this->verificarConfiguracion();
            
            // 2. Verificar conexión con API
            $this->verificarConexionAPI();
            
            // 3. Verificar autenticación
            $this->verificarAutenticacion();
            
            // 4. Verificar permisos
            $this->verificarPermisos();
            
        } catch (\Exception $e) {
            $this->diagnostico['error_general'] = [
                'estado' => 'error',
                'mensaje' => 'Error durante el diagnóstico: ' . $e->getMessage(),
                'detalle' => $e->getTraceAsString()
            ];
            Log::error('Error en diagnóstico de WhatsApp', [
                'exception' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
        }

        $this->ejecutando = false;
    }

    private function verificarConfiguracion()
    {
        $empresa = auth()->user()->empresa ?? null;
        
        $this->diagnostico['configuracion'] = [
            'usuario_autenticado' => auth()->check(),
            'empresa_asignada' => $empresa !== null,
            'whatsapp_api_key' => $empresa && !empty($empresa->whatsapp_api_key),
            'whatsapp_phone' => $empresa && !empty($empresa->whatsapp_phone),
            'company_id' => $empresa ? $empresa->id : 'N/A'
        ];

        if (!$empresa) {
            $this->diagnostico['configuracion']['estado'] = 'error';
            $this->diagnostico['configuracion']['mensaje'] = 'Usuario no tiene empresa asignada';
            return;
        }

        if (empty($empresa->whatsapp_api_key)) {
            $this->diagnostico['configuracion']['estado'] = 'advertencia';
            $this->diagnostico['configuracion']['mensaje'] = 'Empresa no tiene API Key de WhatsApp configurada';
            return;
        }

        $this->diagnostico['configuracion']['estado'] = 'ok';
        $this->diagnostico['configuracion']['mensaje'] = 'Configuración básica correcta';
    }

    private function verificarConexionAPI()
    {
        $baseUrl = config('services.whatsapp.api_url', 'http://localhost:3000');
        
        $this->diagnostico['conexion'] = [
            'url_base' => $baseUrl,
            'puerto_accesible' => false,
            'respuesta_servidor' => null
        ];

        try {
            $response = Http::timeout(5)->get($baseUrl . '/health');
            
            $this->diagnostico['conexion']['puerto_accesible'] = true;
            $this->diagnostico['conexion']['respuesta_servidor'] = $response->status();
            $this->diagnostico['conexion']['body'] = $response->body();
            
            if ($response->successful()) {
                $this->diagnostico['conexion']['estado'] = 'ok';
                $this->diagnostico['conexion']['mensaje'] = 'Conexión con API exitosa';
            } else {
                $this->diagnostico['conexion']['estado'] = 'advertencia';
                $this->diagnostico['conexion']['mensaje'] = 'Servidor responde pero con error: ' . $response->status();
            }
            
        } catch (\Exception $e) {
            $this->diagnostico['conexion']['estado'] = 'error';
            $this->diagnostico['conexion']['mensaje'] = 'No se puede conectar al servidor: ' . $e->getMessage();
        }
    }

    private function verificarAutenticacion()
    {
        $empresa = auth()->user()->empresa ?? null;
        if (!$empresa || empty($empresa->whatsapp_api_key)) {
            $this->diagnostico['autenticacion'] = [
                'estado' => 'error',
                'mensaje' => 'No hay API Key configurada para verificar autenticación'
            ];
            return;
        }

        $baseUrl = config('services.whatsapp.api_url', 'http://localhost:3000');
        
        $this->diagnostico['autenticacion'] = [
            'token_longitud' => strlen($empresa->whatsapp_api_key),
            'token_muestra' => substr($empresa->whatsapp_api_key, 0, 10) . '...',
            'respuesta_autenticacion' => null
        ];

        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $empresa->whatsapp_api_key,
                    'X-Company-Id' => (string) $empresa->id,
                    'Content-Type' => 'application/json'
                ])
                ->get($baseUrl . '/status');

            $this->diagnostico['autenticacion']['respuesta_autenticacion'] = $response->status();
            
            if ($response->status() === 401) {
                $this->diagnostico['autenticacion']['estado'] = 'error';
                $this->diagnostico['autenticacion']['mensaje'] = 'Token inválido o expirado';
                $errorDetails = $response->json();
                $this->diagnostico['autenticacion']['detalles_error'] = $errorDetails;
            } elseif ($response->status() === 403) {
                $this->diagnostico['autenticacion']['estado'] = 'error';
                $this->diagnostico['autenticacion']['mensaje'] = 'Token válido pero sin permisos suficientes';
            } elseif ($response->successful()) {
                $this->diagnostico['autenticacion']['estado'] = 'ok';
                $this->diagnostico['autenticacion']['mensaje'] = 'Autenticación exitosa';
                $this->diagnostico['autenticacion']['datos_usuario'] = $response->json();
            } else {
                $this->diagnostico['autenticacion']['estado'] = 'advertencia';
                $this->diagnostico['autenticacion']['mensaje'] = 'Respuesta inesperada: ' . $response->status();
            }
            
        } catch (\Exception $e) {
            $this->diagnostico['autenticacion']['estado'] = 'error';
            $this->diagnostico['autenticacion']['mensaje'] = 'Error al verificar autenticación: ' . $e->getMessage();
        }
    }

    private function verificarPermisos()
    {
        $empresa = auth()->user()->empresa ?? null;
        if (!$empresa || empty($empresa->whatsapp_api_key)) {
            $this->diagnostico['permisos'] = [
                'estado' => 'error',
                'mensaje' => 'No hay API Key configurada para verificar permisos'
            ];
            return;
        }

        $baseUrl = config('services.whatsapp.api_url', 'http://localhost:3000');
        
        $this->diagnostico['permisos'] = [
            'permiso_envio' => false,
            'permiso_lectura' => false,
            'permiso_estado' => false
        ];

        // Verificar permiso de envío
        try {
            $response = Http::timeout(5)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $empresa->whatsapp_api_key,
                    'X-Company-Id' => (string) $empresa->id,
                    'Content-Type' => 'application/json'
                ])
                ->post($baseUrl . '/messages/send', [
                    'to' => '59170000000', // Número de prueba
                    'message' => 'Test message',
                    'type' => 'text'
                ]);

            $this->diagnostico['permisos']['permiso_envio'] = $response->status() !== 403;
        } catch (\Exception $e) {
            // Silencioso
        }

        // Verificar permiso de lectura
        try {
            $response = Http::timeout(5)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $empresa->whatsapp_api_key,
                    'X-Company-Id' => (string) $empresa->id,
                    'Content-Type' => 'application/json'
                ])
                ->get($baseUrl . '/messages/recent');

            $this->diagnostico['permisos']['permiso_lectura'] = $response->status() !== 403;
        } catch (\Exception $e) {
            // Silencioso
        }

        $permisosOk = $this->diagnostico['permisos']['permiso_envio'] && 
                     $this->diagnostico['permisos']['permiso_lectura'];
        
        $this->diagnostico['permisos']['estado'] = $permisosOk ? 'ok' : 'advertencia';
        $this->diagnostico['permisos']['mensaje'] = $permisosOk ? 
            'Todos los permisos verificados correctamente' : 
            'Algunos permisos pueden estar restringidos';
    }

    public function toggleDetalles()
    {
        $this->mostrarDetalles = !$this->mostrarDetalles;
    }

    public function render()
    {
        return view('livewire.admin.whatsapp.diagnosticar');
    }
}