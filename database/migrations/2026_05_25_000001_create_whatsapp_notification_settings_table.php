<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_notification_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->string('module_key', 80);
            $table->string('action_key', 120);
            $table->string('recipient_key', 80);
            $table->boolean('enabled')->default(true);
            $table->timestamps();

            $table->unique(
                ['empresa_id', 'module_key', 'action_key', 'recipient_key'],
                'wa_notif_settings_unique'
            );
            $table->index(['empresa_id', 'module_key'], 'wa_notif_settings_empresa_module_idx');
        });

        $this->copyLegacyAppointmentSettings();
    }

    private function copyLegacyAppointmentSettings(): void
    {
        if (! Schema::hasTable('configuracion_notificaciones')) {
            return;
        }

        $now = now();

        DB::table('configuracion_notificaciones')
            ->select(['empresa_id', 'tipo_destinatario', 'estado_cita', 'enviar_notificacion'])
            ->orderBy('id')
            ->get()
            ->each(function ($setting) use ($now) {
                DB::table('whatsapp_notification_settings')->updateOrInsert(
                    [
                        'empresa_id' => $setting->empresa_id,
                        'module_key' => 'citas',
                        'action_key' => 'estado_'.$setting->estado_cita,
                        'recipient_key' => $setting->tipo_destinatario,
                    ],
                    [
                        'enabled' => (bool) $setting->enviar_notificacion,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_notification_settings');
    }
};
