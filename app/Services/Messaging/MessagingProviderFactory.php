<?php

namespace App\Services\Messaging;

use App\Domain\Contracts\MessagingProviderInterface;
use App\Services\Messaging\Providers\GoogleFCMProvider;
use App\Services\Messaging\Providers\TwilioProvider;
use App\Services\Messaging\Providers\WhatsAppLiteProvider;
use App\Services\Messaging\Providers\WhatsAppMetaProvider;
use Illuminate\Support\Facades\Cache;

class MessagingProviderFactory
{
    private static array $cache = [];

    /**
     * Crear instancia del proveedor según el slug
     */
    public static function create(string $providerSlug, array $credentials): MessagingProviderInterface
    {
        $cacheKey = $providerSlug . '_' . md5(json_encode($credentials));

        if (isset(self::$cache[$cacheKey])) {
            return self::$cache[$cacheKey];
        }

        $provider = match ($providerSlug) {
            'whatsapp_lite' => new WhatsAppLiteProvider($credentials),
            'whatsapp_meta' => new WhatsAppMetaProvider($credentials),
            'twilio' => new TwilioProvider($credentials),
            'google_fcm' => new GoogleFCMProvider($credentials),
            default => throw new \InvalidArgumentException("Proveedor desconocido: {$providerSlug}"),
        };

        self::$cache[$cacheKey] = $provider;

        return $provider;
    }

    /**
     * Obtener lista de providers disponibles
     */
    public static function getAvailableProviders(): array
    {
        return [
            'whatsapp_lite' => WhatsAppLiteProvider::class,
            'whatsapp_meta' => WhatsAppMetaProvider::class,
            'twilio' => TwilioProvider::class,
            'google_fcm' => GoogleFCMProvider::class,
        ];
    }

    /**
     * Limpiar cache de proveedores
     */
    public static function clearCache(): void
    {
        self::$cache = [];
    }
}