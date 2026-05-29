<?php

namespace App\Services\Messaging\Providers;

use App\Domain\Contracts\MessagingProviderInterface;
use App\Models\Empresa;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppLiteProvider implements MessagingProviderInterface
{
    private array $credentials;
    private ?int $empresaId;
    private int $timeout;

    public function __construct(array $credentials)
    {
        $this->credentials = $credentials;
        $this->empresaId = $credentials['empresa_id'] ?? null;
        $this->timeout = $credentials['timeout'] ?? 30;
    }

    /**
     * Enviar mensaje de texto
     */
    public function send(string $to, string $message, array $options = []): bool
    {
        $isWelcome = $options['is_welcome'] ?? false;

        $result = $this->getWhatsAppService()->sendMessage($to, $message, $isWelcome);

        return $result !== null;
    }

    /**
     * Enviar mensaje con plantilla
     */
    public function sendWithTemplate(string $to, string $templateId, array $variables = []): bool
    {
        // Por ahora, construir mensaje desde variables
        $message = $this->buildTemplateMessage($templateId, $variables);

        return $this->send($to, $message);
    }

    /**
     * Obtener estado del proveedor
     */
    public function getStatus(): array
    {
        try {
            $status = $this->getWhatsAppService()->getStatus();

            return [
                'success' => true,
                'connected' => $status !== null,
                'status' => $status,
            ];
        } catch (\Exception $e) {
            Log::error('WhatsAppLiteProvider status error: ' . $e->getMessage());

            return [
                'success' => false,
                'connected' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Probar conexión
     */
    public function testConnection(): array
    {
        try {
            $status = $this->getWhatsAppService()->getStatus();

            if ($status) {
                return [
                    'success' => true,
                    'message' => 'Conexión exitosa',
                    'details' => $status,
                ];
            }

            return [
                'success' => false,
                'message' => 'No se pudo conectar al servicio',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error de conexión: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Obtener nombre del proveedor
     */
    public function getProviderName(): string
    {
        return 'WhatsApp Lite';
    }

    /**
     * Obtener campos requeridos para configuración
     */
    public function getRequiredFields(): array
    {
        return [
            [
                'key' => 'api_url',
                'label' => 'URL del API',
                'type' => 'text',
                'required' => true,
            ],
            [
                'key' => 'api_key',
                'label' => 'API Key',
                'type' => 'password',
                'required' => true,
            ],
            [
                'key' => 'timeout',
                'label' => 'Timeout (segundos)',
                'type' => 'number',
                'required' => false,
                'default' => 30,
            ],
        ];
    }

    /**
     * Obtener instancia del servicio de WhatsApp
     */
    private function getWhatsAppService(): WhatsAppService
    {
        return WhatsAppService::forCredentials([
            'api_url' => $this->credentials['api_url'] ?? null,
            'api_key' => $this->credentials['api_key'] ?? null,
            'empresa_id' => $this->empresaId,
            'timeout' => $this->timeout,
        ]);
    }

    /**
     * Construir mensaje desde plantilla
     */
    private function buildTemplateMessage(string $templateId, array $variables): string
    {
        // Por ahora, simple concatenación
        // En producción, esto leería la plantilla de BD
        $message = $templateId;

        foreach ($variables as $key => $value) {
            $message = str_replace("{{$key}}", $value, $message);
        }

        return $message;
    }

    /**
     * Obtener las credenciales desencriptadas
     */
    public function getCredentials(): array
    {
        return $this->credentials;
    }

    /**
     * Enviar documento
     */
    public function sendDocument(string $to, string $filePath, string $caption = ''): ?array
    {
        return $this->getWhatsAppService()->sendDocument($to, $filePath, $caption);
    }

    /**
     * Enviar imagen
     */
    public function sendImage(string $to, string $filePath, string $caption = ''): ?array
    {
        return $this->getWhatsAppService()->sendImage($to, $filePath, $caption);
    }

    /**
     * Obtener código QR
     */
    public function getQRCode(): ?array
    {
        return $this->getWhatsAppService()->getQRCode();
    }

    /**
     * Conectar WhatsApp
     */
    public function connect(): ?array
    {
        return $this->getWhatsAppService()->connect();
    }

    /**
     * Desconectar WhatsApp
     */
    public function disconnect(): ?array
    {
        return $this->getWhatsAppService()->disconnect();
    }
}