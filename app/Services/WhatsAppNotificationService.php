<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppNotificationService
{
    private $apiUrl;
    private $timeout;
    
    public function __construct()
    {
        $this->apiUrl = config('app.whatsapp_api_url', 'http://localhost:3002');
        $this->timeout = 30; // segundos
    }
    
    /**
     * Enviar mensaje de WhatsApp
     */
    public function sendMessage($phone, $message, $apiKey)
    {
        try {
            $url = $this->apiUrl . '/api/whatsapp/send';
            
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'X-API-Key' => $apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post($url, [
                    'phone' => $phone,
                    'message' => $message,
                ]);
            
            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['success']) && $data['success']) {
                    return [
                        'success' => true,
                        'message_id' => $data['message_id'] ?? null,
                    ];
                }
            }
            
            return [
                'success' => false,
                'error' => 'Error HTTP ' . $response->status() . ': ' . $response->body(),
            ];
            
        } catch (\Exception $e) {
            Log::error('Error enviando mensaje WhatsApp: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Obtener estado del servicio WhatsApp
     */
    public function getStatus($apiKey)
    {
        try {
            $url = $this->apiUrl . '/api/whatsapp/status';
            
            $response = Http::timeout(10)
                ->withHeaders([
                    'X-API-Key' => $apiKey,
                ])
                ->get($url);
            
            if ($response->successful()) {
                return $response->json();
            }
            
            return null;
            
        } catch (\Exception $e) {
            Log::error('Error obteniendo estado WhatsApp: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Formatear número de teléfono
     */
    public function formatPhoneNumber($telefono)
    {
        // Eliminar espacios y caracteres especiales
        $telefono = preg_replace('/[^0-9+]/', '', $telefono);
        
        // Si no tiene código de país, agregar +51 (Perú)
        if (strlen($telefono) === 9 && $telefono[0] !== '+') {
            $telefono = '+51' . $telefono;
        } elseif (strlen($telefono) === 11 && $telefono[0] === '5' && $telefono[1] === '1') {
            $telefono = '+' . $telefono;
        }
        
        // Validar formato
        if (!preg_match('/^\+[1-9]\d{1,14}$/', $telefono)) {
            return false;
        }
        
        return $telefono;
    }
    
    /**
     * Generar mensaje de bienvenida para médico
     */
    public function generateDoctorWelcomeMessage($medico)
    {
        $nombreCompleto = $medico->nombres . ' ' . $medico->apellidos;
        $especialidad = $medico->especialidades->first() ? $medico->especialidades->first()->nombre : 'Médico';
        $empresa = $medico->empresa ? $medico->empresa->razon_social : 'la empresa';
        
        return "¡Hola Dr(a). {$nombreCompleto}! 🏥\n\n" .
               "Bienvenido(a) al sistema de {$empresa}.\n\n" .
               "Como {$especialidad}, su cuenta ha sido activada exitosamente.\n\n" .
               "📋 Puede acceder al sistema con su documento: {$medico->documento_identidad}\n" .
               "🔒 Su cuenta está protegida con los más altos estándares de seguridad.\n\n" .
               "¡Gracias por formar parte de nuestro equipo! 🩺\n\n" .
               "Atentamente,\nEl equipo de {$empresa}";
    }
}