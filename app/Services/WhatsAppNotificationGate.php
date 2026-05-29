<?php

namespace App\Services;

use App\Models\ConfiguracionNotificacion;
use App\Models\WhatsAppNotificationSetting;
use App\Models\ModuleNotificationChannel;
use Illuminate\Support\Facades\Schema;

class WhatsAppNotificationGate
{
    public static function allows(
        ?int $empresaId,
        string $module,
        string $action,
        string $recipient
    ): bool {
        if (! $empresaId) {
            return true;
        }

        if (Schema::hasTable('whatsapp_notification_settings')) {
            $setting = WhatsAppNotificationSetting::forEvent($empresaId, $module, $action, $recipient)->first();

            if ($setting) {
                return $setting->enabled;
            }
        }

        // Si existe configuración de canal específico para este módulo/evento,
        // respetarla (aunque esté deshabilitada).
        if (Schema::hasTable('module_notification_channels')) {
            $channel = ModuleNotificationChannel::forEmpresa($empresaId)
                ->forEvent($module, $action, $recipient)
                ->first();

            if ($channel) {
                return (bool) $channel->enabled;
            }
        }

        if ($module === 'citas' && str_starts_with($action, 'estado_')) {
            $estado = substr($action, strlen('estado_'));

            return ConfiguracionNotificacion::debeEnviar($empresaId, $recipient, $estado);
        }

        return self::defaultEnabled($module, $action, $recipient);
    }

    public static function defaultEnabled(string $module, string $action, string $recipient): bool
    {
        $event = WhatsAppNotificationCatalog::event($module, $action, $recipient);

        if (! $event) {
            return true;
        }

        return (bool) $event['default_enabled'];
    }
}
