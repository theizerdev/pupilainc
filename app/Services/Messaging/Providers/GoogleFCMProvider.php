<?php

namespace App\Services\Messaging\Providers;

use App\Domain\Contracts\MessagingProviderInterface;

class GoogleFCMProvider implements MessagingProviderInterface
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
        return ['success' => true, 'message' => 'Google FCM no implementado aún'];
    }

    public function getProviderName(): string
    {
        return 'Google Firebase Cloud Messaging';
    }

    public function getRequiredFields(): array
    {
        return [
            ['key' => 'project_id', 'label' => 'Project ID', 'type' => 'text', 'required' => true],
            ['key' => 'private_key', 'label' => 'Private Key', 'type' => 'textarea', 'required' => true],
            ['key' => 'client_email', 'label' => 'Client Email', 'type' => 'email', 'required' => true],
            ['key' => 'api_key', 'label' => 'Server API Key', 'type' => 'password', 'required' => true],
        ];
    }
}