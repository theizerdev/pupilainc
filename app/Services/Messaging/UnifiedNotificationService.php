<?php

namespace App\Services\Messaging;

use App\Models\Empresa;
use App\Services\WhatsAppNotificationGate;
use Illuminate\Support\Facades\Log;

class UnifiedNotificationService
{
    private MessagingConnectionManager $connectionManager;
    private CredentialEncryptionService $encryption;

    public function __construct()
    {
        $this->connectionManager = new MessagingConnectionManager();
        $this->encryption = new CredentialEncryptionService();
    }

    /**
     * Enviar notificación a un destinatario
     */
    public function notify(
        int $empresaId,
        string $module,
        string $action,
        string $recipientType,
        string $phoneNumber,
        string $message,
        array $context = []
    ): bool {
        // Verificar permisos usando el gate existente
        if (!WhatsAppNotificationGate::allows($empresaId, $module, $action, $recipientType)) {
            Log::info("Notificación bloqueada por WhatsAppNotificationGate", [
                'empresa_id' => $empresaId,
                'module' => $module,
                'action' => $action,
                'recipient' => $recipientType,
            ]);
            return false;
        }

        // Obtener proveedor para el módulo/acción
        $provider = $this->connectionManager->getProviderForModule(
            $empresaId,
            $module,
            $action,
            $recipientType
        );

        if (!$provider) {
            Log::warning("No se encontró proveedor de mensajería", [
                'empresa_id' => $empresaId,
                'module' => $module,
                'action' => $action,
            ]);
            return false;
        }

        // Formatear número de teléfono
        $formattedPhone = $this->formatPhoneNumber($phoneNumber, $empresaId);

        try {
            $result = $provider->send($formattedPhone, $message, $context);

            Log::info("Notificación enviada", [
                'empresa_id' => $empresaId,
                'module' => $module,
                'action' => $action,
                'recipient' => $recipientType,
                'phone' => $formattedPhone,
                'success' => $result,
            ]);

            return $result;
        } catch (\Exception $e) {
            Log::error("Error enviando notificación: " . $e->getMessage(), [
                'empresa_id' => $empresaId,
                'module' => $module,
                'action' => $action,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Notificación con fallback (intenta canal primario, si falla usa secundario)
     */
    public function notifyWithFallback(
        int $empresaId,
        string $module,
        string $action,
        string $recipientType,
        string $phoneNumber,
        string $message
    ): bool {
        // Intentar con el canal principal
        if ($this->notify($empresaId, $module, $action, $recipientType, $phoneNumber, $message)) {
            return true;
        }

        // Aquí se podría implementar lógica de fallback a otro canal
        // Por ahora, retornar false
        Log::info("Notificación con fallback fallida", [
            'empresa_id' => $empresaId,
            'module' => $module,
            'action' => $action,
        ]);

        return false;
    }

    /**
     * Enviar notificación simple (sin módulo/acción específica)
     */
    public function sendSimple(
        int $empresaId,
        string $phoneNumber,
        string $message
    ): bool {
        $connection = $this->connectionManager->getConnection($empresaId);

        if (!$connection) {
            return false;
        }

        $provider = $this->connectionManager->buildProviderFromConnection($connection);
        $formattedPhone = $this->formatPhoneNumber($phoneNumber, $empresaId);

        return $provider->send($formattedPhone, $message);
    }

    /**
     * Formatear número de teléfono
     */
    private function formatPhoneNumber(string $phone, int $empresaId): string
    {
        // Eliminar espacios y caracteres especiales
        $phone = preg_replace('/[^0-9+]/', '', $phone);

        // Si ya tiene +, retornarlo
        if (str_starts_with($phone, '+')) {
            return $phone;
        }

        // Obtener configuración de país de la empresa
        $empresa = Empresa::find($empresaId);
        $countryCode = $empresa?->pais?->codigo_telefonico ?? '51';

        // Agregar código de país
        return '+' . $countryCode . ltrim($phone, '0');
    }

    /**
     * Obtener estado del servicio de mensajería
     */
    public function getStatus(int $empresaId): array
    {
        $connection = $this->connectionManager->getConnection($empresaId);

        if (!$connection) {
            return [
                'configured' => false,
                'message' => 'No hay conexión configurada',
            ];
        }

        try {
            $provider = $this->connectionManager->buildProviderFromConnection($connection);
            $status = $provider->getStatus();

            return [
                'configured' => true,
                'provider' => $provider->getProviderName(),
                'connection_name' => $connection->name,
                'status' => $status,
            ];
        } catch (\Exception $e) {
            return [
                'configured' => true,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Probar conexión
     */
    public function testConnection(int $empresaId, int $connectionId): array
    {
        $connection = \App\Models\MessagingConnection::find($connectionId);

        if (!$connection || $connection->empresa_id !== $empresaId) {
            return ['success' => false, 'message' => 'Conexión no encontrada'];
        }

        return $this->connectionManager->testConnection($connection);
    }
}