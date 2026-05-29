<?php

namespace App\Services\Messaging\Providers;

use App\Domain\Contracts\MessagingProviderInterface;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client;

/**
 * Twilio SMS provider
 */
class TwilioProvider implements MessagingProviderInterface
{
    private array $credentials;

    public function __construct(array $credentials)
    {
        $this->credentials = $credentials;
    }

    public function send(string $to, string $message, array $options = []): bool
    {
        $accountSid = (string) ($options['account_sid'] ?? $this->credentials['account_sid'] ?? '');
        $authToken = (string) ($options['auth_token'] ?? $this->credentials['auth_token'] ?? '');
        $channel = (string) ($options['channel'] ?? $this->credentials['channel'] ?? 'whatsapp'); // Cambiado a whatsapp por defecto
        $isWhatsApp = strtolower($channel) === 'whatsapp';

        // Validar que se proporcionen credenciales
        if (empty($accountSid) || empty($authToken)) {
            Log::error('Faltan credenciales de Twilio');
            return false;
        }

        // Normalizar el número de teléfono de destino a formato E.164 si es necesario
        $normalizedTo = $this->normalizeToE164($to);
        if (empty($normalizedTo)) {
            Log::error('Número de teléfono de destino inválido: ' . $to);
            return false;
        }

        // Preparar el número de destino para WhatsApp
        $whatsappTo = 'whatsapp:' . $normalizedTo;

        // Obtener el número de origen para WhatsApp desde las opciones o credenciales
        $fromWhatsApp = (string) ($options['from_whatsapp'] ?? $this->credentials['from_whatsapp'] ?? '');
        if (empty($fromWhatsApp)) {
            // Si no se proporciona un número específico para WhatsApp, usar el genérico 'from'
            $fromGeneric = (string) ($options['from'] ?? $this->credentials['from'] ?? $this->credentials['phone_number'] ?? '');
            if (!empty($fromGeneric)) {
                $normalizedFrom = $this->normalizeToE164($fromGeneric);
                $fromWhatsApp = 'whatsapp:' . $normalizedFrom;
            }
        } else {
            // Asegurar que el número de origen para WhatsApp tenga el prefijo correcto
            if (!Str::startsWith($fromWhatsApp, 'whatsapp:')) {
                $normalizedFrom = $this->normalizeToE164($fromWhatsApp);
                $fromWhatsApp = 'whatsapp:' . $normalizedFrom;
            }
        }

        if (empty($fromWhatsApp)) {
            Log::error('Número de teléfono de origen para WhatsApp no configurado');
            return false;
        }

        try {
            $twilio = new Client($accountSid, $authToken);

            // Verificar si se va a usar una plantilla con Content SID
            $contentSid = (string) ($options['content_sid'] ?? $this->credentials['content_sid'] ?? '');
            
            $params = [
                "from" => $fromWhatsApp,
                "body" => $message
            ];

            // Si se proporciona un Content SID, usarlo en lugar del body
            if (!empty($contentSid)) {
                $params["contentSid"] = $contentSid;
                
                // Si se proporcionan variables para la plantilla, añadirlas
                if (isset($options['content_variables']) && is_array($options['content_variables'])) {
                    $params["contentVariables"] = json_encode($options['content_variables']);
                }
            } else {
                $params["body"] = $message;
            }

            $params["to"] = $whatsappTo;

            $twilioMessage = $twilio->messages
                ->create($whatsappTo, $params);

            Log::info('Mensaje de WhatsApp enviado exitosamente', [
                'to' => $whatsappTo,
                'sid' => $twilioMessage->sid,
                'status' => $twilioMessage->status
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Error al enviar mensaje de WhatsApp con Twilio', [
                'error' => $e->getMessage(),
                'to' => $whatsappTo,
                'from' => $fromWhatsApp
            ]);
            return false;
        }
    }


public function sendWithTemplate(string $to, string $templateId, array $variables = []): bool
{
    // There is no template system in this provider; treat templateId as body.
    $message = $this->buildTemplateMessage($templateId, $variables);
    return $this->send($to, $message, ['is_template' => true]);
}

    public function getStatus(): array
    {
        $accountSid = (string) ($this->credentials['account_sid'] ?? '');
        $authToken = (string) ($this->credentials['auth_token'] ?? '');
        $from = (string) ($this->credentials['from'] ?? $this->credentials['phone_number'] ?? '');

        $configured = (bool) ($accountSid && $authToken && $from);

        return [
            'success' => $configured,
            'connected' => $configured,
            'status' => $configured ? 'configured' : 'missing_credentials',
        ];
    }

    public function testConnection(): array
    {
        $accountSid = (string) ($this->credentials['account_sid'] ?? '');
        $authToken = (string) ($this->credentials['auth_token'] ?? '');

        if (!$accountSid || !$authToken) {
            return ['success' => false, 'message' => 'Missing account_sid/auth_token'];
        }

        try {
            $url = "https://api.twilio.com/2010-04-01/Accounts/{$accountSid}/Messages.json?PageSize=1";

            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
                CURLOPT_USERPWD => $accountSid . ':' . $authToken,
                CURLOPT_TIMEOUT => (int) ($this->credentials['timeout'] ?? 20),
                CURLOPT_SSL_VERIFYPEER => true,
            ]);

            $raw = curl_exec($ch);
            $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlErr = curl_error($ch);
            curl_close($ch);

            if ($raw === false) {
                return ['success' => false, 'message' => 'curl error: ' . $curlErr];
            }

            if ($httpCode < 200 || $httpCode >= 300) {
                $data = json_decode($raw, true);
                return [
                    'success' => false,
                    'message' => 'Twilio HTTP error',
                    'http_code' => $httpCode,
                    'response' => is_array($data) ? $data : null,
                ];
            }

            return ['success' => true, 'message' => 'Conexión exitosa'];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => 'Error de conexión: ' . $e->getMessage()];
        }
    }

    public function getProviderName(): string
    {
        return 'Twilio';
    }

    public function getRequiredFields(): array
    {
        return [
            ['key' => 'account_sid', 'label' => 'Account SID', 'type' => 'text', 'required' => true],
            ['key' => 'auth_token', 'label' => 'Auth Token', 'type' => 'password', 'required' => true],
            ['key' => 'channel', 'label' => 'Canal (sms o whatsapp)', 'type' => 'select', 'options' => ['sms', 'whatsapp'], 'required' => false, 'default' => 'sms'],
            ['key' => 'from', 'label' => 'From para SMS (número Twilio verificado)', 'type' => 'text', 'required' => true],
            ['key' => 'from_whatsapp', 'label' => 'From para WhatsApp (número Twilio verificado)', 'type' => 'text', 'required' => false],
            ['key' => 'phone_number', 'label' => 'phone_number (fallback de From)', 'type' => 'text', 'required' => false],
            ['key' => 'content_sid', 'label' => 'Content SID (para templates)', 'type' => 'text', 'required' => false],
            ['key' => 'timeout', 'label' => 'Timeout (segundos)', 'type' => 'number', 'required' => false, 'default' => 20],
        ];
    }

    private function normalizeToE164(string $value): string
    {
        $value = trim($value);
        $value = preg_replace('/[^0-9+]/', '', $value);

        if ($value === '') {
            return '';
        }

        if (str_starts_with($value, '+')) {
            return $value;
        }

        return '+' . ltrim($value, '0');
    }

    private function buildTemplateMessage(string $templateId, array $variables): string
    {
        $message = (string) $templateId;
        foreach ($variables as $key => $value) {
            $message = str_replace('{' . $key . '}', (string) $value, $message);
            $message = str_replace('{{' . $key . '}}', (string) $value, $message);
        }
        return $message;
    }
}
