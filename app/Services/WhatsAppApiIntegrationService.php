<?php

namespace App\Services;

use App\Models\Empresa;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Firebase\JWT\JWT;

class WhatsAppApiIntegrationService
{
    private $apiUrl;
    private $jwtSecret;

    public function __construct()
    {
        $this->apiUrl = config('whatsapp.api_url', 'http://82.165.213.124:8092');
        $this->jwtSecret = config('whatsapp.jwt_secret', env('JWT_SECRET', 'base64:ItiVlmjSSgrh2LFDfR0JGtPXHRAthPOWSMw6WyrgwIk='));
    }

    /**
     * Genera un token JWT para autenticación administrativa
     */
    private function generateAdminToken(): string
    {
        $payload = [
            'iat' => time(),
            'exp' => time() + 3600, // 1 hora
            'role' => 'admin',
            'action' => 'company_management'
        ];

        return JWT::encode($payload, $this->jwtSecret, 'HS256');
    }

    /**
     * Genera una API key única para la empresa
     */
    private function generateApiKey(Empresa $empresa): string
    {
        return 'wa_' . $empresa->id . '_' . bin2hex(random_bytes(16));
    }

    /**
     * Crea/registra una empresa en la API de WhatsApp
     */
    public function createCompany($empresa)
    {
        try {
            // Generar API key para la empresa
            $apiKey = $this->generateApiKey($empresa);
            
            Log::info('Iniciando creación de empresa en WhatsApp API', [
                'empresa_id' => $empresa->id,
                'empresa_nombre' => $empresa->razon_social,
                'api_url' => $this->apiUrl
            ]);

            // Registrar la empresa en el servicio de WhatsApp
            $response = Http::timeout(10)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $this->generateAdminToken()
                ])
                ->post($this->apiUrl . '/api/companies/register', [
                    'name' => $empresa->razon_social,
                    'api_key' => $apiKey,
                    'webhook_url' => config('app.url') . '/api/whatsapp/webhook/' . $empresa->id,
                    'rate_limit_per_minute' => $empresa->whatsapp_rate_limit ?? 60
                ]);

            Log::info('Respuesta de WhatsApp API', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                // Guardar la API key y configuración en la empresa
                $empresa->update([
                    'whatsapp_api_key' => $apiKey,
                    'whatsapp_active' => true
                ]);

                Log::info('Empresa sincronizada con WhatsApp API', [
                    'empresa_id' => $empresa->id,
                    'whatsapp_company_id' => $data['company_id'] ?? null
                ]);

                return $apiKey;
            }

            // Si falla el registro remoto, igual guardamos la API key localmente
            // para que pueda usarse cuando el servicio esté disponible
            $empresa->update([
                'whatsapp_api_key' => $apiKey,
                'whatsapp_active' => false
            ]);

            Log::warning('WhatsApp API no disponible, API key guardada localmente', [
                'empresa_id' => $empresa->id,
                'status' => $response->status(),
                'response' => $response->body()
            ]);

            return $apiKey;

        } catch (\Exception $e) {
            // En caso de error, generar y guardar API key localmente
            $apiKey = $this->generateApiKey($empresa);
            
            $empresa->update([
                'whatsapp_api_key' => $apiKey,
                'whatsapp_active' => false
            ]);

            Log::error('Excepción creando empresa en WhatsApp API', [
                'error' => $e->getMessage(),
                'empresa_id' => $empresa->id ?? 'unknown',
                'api_key_generated' => $apiKey
            ]);

            return $apiKey;
        }
    }

    /**
     * Actualiza una empresa en la API de WhatsApp
     */
    public function updateCompany($empresa)
    {
        try {
            if (empty($empresa->whatsapp_api_key)) {
                return $this->createCompany($empresa);
            }

            $response = Http::timeout(10)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $this->generateAdminToken()
                ])
                ->put($this->apiUrl . '/api/companies/' . $empresa->id, [
                    'name' => $empresa->razon_social,
                    'rate_limit_per_minute' => $empresa->whatsapp_rate_limit ?? 60,
                    'is_active' => $empresa->whatsapp_active ?? true
                ]);

            if ($response->successful()) {
                Log::info('Empresa actualizada en WhatsApp API', [
                    'empresa_id' => $empresa->id
                ]);
                return true;
            }

            Log::warning('Error actualizando empresa en WhatsApp API', [
                'empresa_id' => $empresa->id,
                'status' => $response->status()
            ]);

            return false;

        } catch (\Exception $e) {
            Log::error('Excepción actualizando empresa en WhatsApp API', [
                'error' => $e->getMessage(),
                'empresa_id' => $empresa->id
            ]);
            return false;
        }
    }

    /**
     * Elimina una empresa de la API de WhatsApp
     */
    public function deleteCompany($empresa)
    {
        try {
            if (empty($empresa->whatsapp_api_key)) {
                return true;
            }

            $response = Http::timeout(10)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $this->generateAdminToken()
                ])
                ->delete($this->apiUrl . '/api/companies/' . $empresa->id);

            if ($response->successful()) {
                Log::info('Empresa eliminada de WhatsApp API', [
                    'empresa_id' => $empresa->id
                ]);
                return true;
            }

            Log::warning('Error eliminando empresa de WhatsApp API', [
                'empresa_id' => $empresa->id,
                'status' => $response->status()
            ]);

            return false;

        } catch (\Exception $e) {
            Log::error('Excepción eliminando empresa de WhatsApp API', [
                'error' => $e->getMessage(),
                'empresa_id' => $empresa->id
            ]);
            return false;
        }
    }

    /**
     * Sincroniza todas las empresas existentes con la API de WhatsApp
     */
    public function syncAllCompanies()
    {
        $empresas = Empresa::whereNull('whatsapp_api_key')
            ->orWhere('whatsapp_api_key', '')
            ->get();

        $results = [
            'success' => 0,
            'failed' => 0,
            'total' => $empresas->count()
        ];

        foreach ($empresas as $empresa) {
            $result = $this->createCompany($empresa);
            if ($result) {
                $results['success']++;
            } else {
                $results['failed']++;
            }
        }

        Log::info('Sincronización de empresas completada', $results);

        return $results;
    }
}