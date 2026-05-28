<?php

namespace App\Services\Messaging\Providers;

use App\Domain\Contracts\MessagingProviderInterface;

class WhatsAppMetaProvider implements MessagingProviderInterface
{
    private array $credentials;

    public function __construct(array $credentials)
    {
        $this->credentials = $credentials;
    }

    public function send(string $to, string $message, array $options = []): bool
    {
        // Implementar API de Meta
        return true;
    }

    public function sendWithTemplate(string $to, string $templateId, array $variables = []): bool
    {
        // Implementar plantillas de Meta
        return true;
    }

    public function getStatus(): array
    {
        return [
            'success' => true,
            'connected' => true,
        ];
    }

    public function testConnection(): array
    {
        return [
            'success' => true,
            'message' => 'WhatsApp Meta no implementado aún',
        ];
    }

    public function getProviderName(): string
    {
        return 'WhatsApp Business API (Meta)';
    }

    public function getRequiredFields(): array
    {
        return [
            ['key' => 'phone_number_id', 'label' => 'Phone Number ID', 'type' => 'text', 'required' => true],
            ['key' => 'access_token', 'label' => 'Access Token', 'type' => 'password', 'required' => true],
            ['key' => 'business_account_id', 'label' => 'Business Account ID', 'type' => 'text', 'required' => true],
        ];
    }
}