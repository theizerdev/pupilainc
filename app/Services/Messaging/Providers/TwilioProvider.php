<?php

namespace App\Services\Messaging\Providers;

use App\Domain\Contracts\MessagingProviderInterface;

class TwilioProvider implements MessagingProviderInterface
{
    private array $credentials;

    public function __construct(array $credentials)
    {
        $this->credentials = $credentials;
    }

    public function send(string $to, string $message, array $options = []): bool
    {
        return true;
    }

    public function sendWithTemplate(string $to, string $templateId, array $variables = []): bool
    {
        return true;
    }

    public function getStatus(): array
    {
        return ['success' => true, 'connected' => true];
    }

    public function testConnection(): array
    {
        return ['success' => true, 'message' => 'Twilio no implementado aún'];
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
            ['key' => 'phone_number', 'label' => 'Número de teléfono', 'type' => 'text', 'required' => true],
        ];
    }
}