<?php

namespace Tests\Unit;

use App\Models\ConfiguracionNotificacion;
use App\Models\Empresa;
use App\Models\WhatsAppNotificationSetting;
use App\Services\WhatsAppNotificationGate;
use Tests\CreatesWhatsAppNotificationTestSchema;
use Tests\TestCase;

class WhatsAppNotificationGateTest extends TestCase
{
    use CreatesWhatsAppNotificationTestSchema;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createWhatsAppNotificationTestSchema();
    }

    public function test_stored_setting_disables_event(): void
    {
        $empresa = Empresa::create([
            'razon_social' => 'Clinica Test',
            'documento' => 'J-10000001',
        ]);

        WhatsAppNotificationSetting::setValue($empresa->id, 'citas', 'nueva_cita', 'paciente', false);

        $this->assertFalse(WhatsAppNotificationGate::allows($empresa->id, 'citas', 'nueva_cita', 'paciente'));
    }

    public function test_legacy_appointment_setting_is_respected_when_new_setting_is_missing(): void
    {
        $empresa = Empresa::create([
            'razon_social' => 'Clinica Test',
            'documento' => 'J-10000002',
        ]);

        ConfiguracionNotificacion::create([
            'empresa_id' => $empresa->id,
            'tipo_destinatario' => 'doctor',
            'estado_cita' => 'confirmada',
            'enviar_notificacion' => false,
        ]);

        $this->assertFalse(WhatsAppNotificationGate::allows($empresa->id, 'citas', 'estado_confirmada', 'doctor'));
    }

    public function test_connected_events_preserve_current_behavior_by_default(): void
    {
        $empresa = Empresa::create([
            'razon_social' => 'Clinica Test',
            'documento' => 'J-10000003',
        ]);

        $this->assertTrue(WhatsAppNotificationGate::allows($empresa->id, 'usuarios', 'bienvenida', 'usuario'));
    }

    public function test_non_whatsapp_events_are_not_listed_in_catalog(): void
    {
        $empresa = Empresa::create([
            'razon_social' => 'Clinica Test',
            'documento' => 'J-10000004',
        ]);

        $this->assertNull(\App\Services\WhatsAppNotificationCatalog::event('pagos', 'create', 'cliente'));
        $this->assertTrue(WhatsAppNotificationGate::allows($empresa->id, 'pagos', 'create', 'cliente'));
    }
}
