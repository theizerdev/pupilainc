<?php

namespace App\Domain\Contracts;

interface MessagingProviderInterface
{
    /**
     * Enviar mensaje de texto
     */
    public function send(string $to, string $message, array $options = []): bool;

    /**
     * Enviar mensaje con plantilla
     */
    public function sendWithTemplate(string $to, string $templateId, array $variables = []): bool;

    /**
     * Obtener estado del proveedor
     */
    public function getStatus(): array;

    /**
     * Probar conexión
     */
    public function testConnection(): array;

    /**
     * Obtener nombre del proveedor
     */
    public function getProviderName(): string;

    /**
     * Obtener campos requeridos para configuración
     */
    public function getRequiredFields(): array;
}