<?php

namespace Tests\Feature;

use App\Jobs\ProcessScheduledWhatsAppMessages;
use App\Livewire\Admin\Whatsapp\WhatsAppNotifications;
use App\Models\Empresa;
use App\Models\User;
use App\Models\WhatsAppNotificationSetting;
use App\Models\WhatsAppScheduledMessage;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Tests\CreatesWhatsAppNotificationTestSchema;
use Tests\TestCase;

class WhatsAppNotificationsSettingsTest extends TestCase
{
    use CreatesWhatsAppNotificationTestSchema;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createWhatsAppNotificationTestSchema();
    }

    public function test_livewire_screen_saves_company_settings(): void
    {
        [$empresa, $user] = $this->userWithNotificationPermissions();

        Livewire::actingAs($user)
            ->test(WhatsAppNotifications::class)
            ->assertSee('Notificaciones WhatsApp')
            ->set('settings.citas.nueva_cita.paciente', false)
            ->call('save');

        $this->assertDatabaseHas('whatsapp_notification_settings', [
            'empresa_id' => $empresa->id,
            'module_key' => 'citas',
            'action_key' => 'nueva_cita',
            'recipient_key' => 'paciente',
            'enabled' => false,
        ]);
    }

    public function test_disabled_scheduled_notification_does_not_call_whatsapp_api(): void
    {
        $empresa = Empresa::create([
            'razon_social' => 'Clinica Test',
            'documento' => 'J-20000001',
        ]);

        WhatsAppNotificationSetting::setValue($empresa->id, 'citas', 'recordatorio_manual', 'paciente', false);

        $message = WhatsAppScheduledMessage::create([
            'empresa_id' => $empresa->id,
            'notification_type' => 'manual',
            'recipient_phone' => '+580000000000',
            'recipient_name' => 'Paciente',
            'message_content' => 'Recordatorio',
            'scheduled_at' => now()->subMinute(),
            'status' => 'pending',
        ]);

        Http::fake();

        (new ProcessScheduledWhatsAppMessages())->handle();

        Http::assertNothingSent();
        $this->assertSame('cancelled', $message->refresh()->status);
    }

    public function test_enabled_scheduled_notification_calls_whatsapp_api(): void
    {
        $empresa = Empresa::create([
            'razon_social' => 'Clinica Test',
            'documento' => 'J-20000002',
        ]);

        WhatsAppNotificationSetting::setValue($empresa->id, 'citas', 'recordatorio_manual', 'paciente', true);

        $message = WhatsAppScheduledMessage::create([
            'empresa_id' => $empresa->id,
            'notification_type' => 'manual',
            'recipient_phone' => '+580000000001',
            'recipient_name' => 'Paciente',
            'message_content' => 'Recordatorio',
            'scheduled_at' => now()->subMinute(),
            'status' => 'pending',
        ]);

        Http::fake([
            '*' => Http::response(['success' => true, 'messageId' => 'msg_test'], 200),
        ]);

        (new ProcessScheduledWhatsAppMessages())->handle();

        Http::assertSentCount(1);
        $this->assertSame('sent', $message->refresh()->status);
    }

    private function userWithNotificationPermissions(): array
    {
        $empresa = Empresa::create([
            'razon_social' => 'Clinica Test',
            'documento' => 'J-20000003',
        ]);

        $user = User::factory()->create([
            'empresa_id' => $empresa->id,
        ]);

        $permissions = collect([
            'access whatsapp',
            'view whatsapp notifications',
            'manage whatsapp notifications',
        ])->map(fn ($name) => Permission::firstOrCreate([
            'name' => $name,
            'guard_name' => 'web',
        ]));

        $user->givePermissionTo($permissions);

        return [$empresa, $user];
    }
}
