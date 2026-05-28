<?php

namespace App\Services\Messaging;

use App\Models\Empresa;
use App\Models\MessagingConnection;
use App\Models\MessagingProvider;
use App\Models\ModuleNotificationChannel;
use Illuminate\Support\Facades\Cache;

class MessagingConnectionManager
{
    private const CACHE_TTL = 300; // 5 minutos

    /**
     * Obtener conexión activa para una empresa
     */
    public function getConnection(int $empresaId, ?string $moduleKey = null): ?MessagingConnection
    {
        $cacheKey = "messaging_connection_{$empresaId}_{$moduleKey}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($empresaId, $moduleKey) {
            // Si se especifica módulo, buscar si hay un default para ese módulo
            if ($moduleKey) {
                $connection = MessagingConnection::forEmpresa($empresaId)
                    ->active()
                    ->get()
                    ->filter(function ($conn) use ($moduleKey) {
                        return $conn->isDefaultFor($moduleKey);
                    })
                    ->first();

                if ($connection) {
                    return $connection;
                }
            }

            // Otherwise, return the first active connection
            return MessagingConnection::forEmpresa($empresaId)
                ->active()
                ->first();
        });
    }

    /**
     * Obtener proveedor para un módulo específico
     */
    public function getProviderForModule(
        int $empresaId,
        string $module,
        string $action,
        string $recipient
    ): ?object {
        // Primero buscar configuración específica para el evento
        $channel = ModuleNotificationChannel::forEmpresa($empresaId)
            ->forEvent($module, $action, $recipient)
            ->enabled()
            ->byPriority()
            ->first();

        if ($channel && $channel->connection) {
            return $this->buildProviderFromConnection($channel->connection);
        }

        // Fallback a la conexión default para la empresa
        $connection = $this->getConnection($empresaId, $module);

        if ($connection) {
            return $this->buildProviderFromConnection($connection);
        }

        return null;
    }

    /**
     * Construir proveedor desde una conexión
     */
    public function buildProviderFromConnection(MessagingConnection $connection): object
    {
        $provider = MessagingProvider::find($connection->provider_id);

        if (!$provider) {
            throw new \RuntimeException("Proveedor no encontrado: {$connection->provider_id}");
        }

        // Las credenciales ya están desencriptadas gracias al cast 'encrypted:array'
        $credentials = $connection->credentials;
        $credentials['empresa_id'] = $connection->empresa_id;

        // Combinar con configuración
        $config = array_merge($connection->configuration ?? [], $credentials);

        return MessagingProviderFactory::create($provider->slug, $config);
    }

    /**
     * Obtener todas las conexiones de una empresa
     */
    public function getConnections(int $empresaId): array
    {
        return MessagingConnection::forEmpresa($empresaId)->get()->toArray();
    }

    /**
     * Obtener canales configurados para una empresa
     */
    public function getChannels(int $empresaId, ?string $moduleKey = null): array
    {
        $query = ModuleNotificationChannel::forEmpresa($empresaId);

        if ($moduleKey) {
            $query->forModule($moduleKey);
        }

        return $query->orderBy('priority')->get()->toArray();
    }

    /**
     * Guardar canal de notificación
     */
    public function saveChannel(
        int $empresaId,
        int $connectionId,
        string $moduleKey,
        string $actionKey,
        string $recipientType,
        bool $enabled = true,
        int $priority = 0
    ): ModuleNotificationChannel {
        return ModuleNotificationChannel::updateOrCreate(
            [
                'empresa_id' => $empresaId,
                'module_key' => $moduleKey,
                'action_key' => $actionKey,
                'recipient_type' => $recipientType,
            ],
            [
                'connection_id' => $connectionId,
                'enabled' => $enabled,
                'priority' => $priority,
            ]
        );
    }

    /**
     * Probar conexión
     */
    public function testConnection(MessagingConnection $connection): array
    {
        try {
            $provider = $this->buildProviderFromConnection($connection);

            return $provider->testConnection();
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Limpiar cache de conexiones
     */
    public function clearCache(int $empresaId): void
    {
        Cache::forget("messaging_connection_{$empresaId}_");
        // Limpiar todos los módulos (aproximado)
        for ($i = 0; $i < 20; $i++) {
            Cache::forget("messaging_connection_{$empresaId}_{$i}");
        }
    }

    /**
     * Migrar configuración existente de WhatsApp desde empresa
     */
    public function migrateFromEmpresa(Empresa $empresa): ?MessagingConnection
    {
        // Verificar si ya existe una conexión
        $existing = MessagingConnection::forEmpresa($empresa->id)
            ->whereHas('provider', fn($q) => $q->where('slug', 'whatsapp_lite'))
            ->first();

        if ($existing) {
            return $existing;
        }

        // Si la empresa tiene configuración de WhatsApp, migrar
        if ($empresa->whatsapp_api_key) {
            $provider = MessagingProvider::findBySlug('whatsapp_lite');

            if (!$provider) {
                return null;
            }

            $connection = MessagingConnection::create([
                'empresa_id' => $empresa->id,
                'provider_id' => $provider->id,
                'name' => 'WhatsApp Principal',
                'credentials' => [
                    'api_url' => config('whatsapp.api_url', 'http://82.165.213.124:8092'),
                    'api_key' => $empresa->whatsapp_api_key,
                    'timeout' => 30,
                ],
                'configuration' => [],
                'status' => 'active',
                'is_default_for' => ['citas', 'usuarios', 'consultas', 'doctores', 'enfermeros', 'pedidos'],
            ]);

            return $connection;
        }

        return null;
    }
}