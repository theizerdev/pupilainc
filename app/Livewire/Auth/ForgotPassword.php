<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Services\WhatsAppService;

class ForgotPassword extends Component
{
    public string $identifier = '';
    public ?string $successMessage = null;

    protected function rules(): array
    {
        return [
            'identifier' => 'required|string|min:7',
        ];
    }

    protected $messages = [
        'identifier.required' => 'Ingresa tu número de teléfono.',
        'identifier.min'      => 'El número de teléfono no es válido.',
    ];

    public function sendResetLink()
    {
        $this->reset('successMessage');
        $this->resetValidation();

        $validated = $this->validate();

        $user = $this->findUser($this->identifier);

        // Respuesta genérica para no revelar si el usuario existe (OWASP)
        if (!$user || !$user->phone || !$user->empresa_id) {
            $this->successMessage = __('auth_ui.reset_link_sent_whatsapp');
            $this->identifier = '';
            return;
        }

        $this->sendTokenByWhatsApp($user);
    }

    private function findUser(string $identifier): ?User
    {
        $clean = trim($identifier);

        // Buscar solo por teléfono (se eliminó soporte email en este flujo)
        $digits = preg_replace('/\D/', '', $clean);

        // Intentar con y sin código de país
        return User::where('phone', $clean)
            ->orWhere('phone', $digits)
            ->orWhere('phone', ltrim($digits, '0'))
            ->first();
    }

    private function sendTokenByWhatsApp(User $user): void
    {
        try {
            $token = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            DB::table('password_reset_tokens')->upsert(
                [
                    'email'      => $user->email,
                    'token'      => Hash::make($token),
                    'created_at' => now(),
                ],
                ['email'],
                ['token', 'created_at']
            );

            $telefono = $this->formatearTelefono($user->phone, $user);
            $resetUrl = route('password.reset.token', ['token' => $token, 'email' => $user->email]);

            $message  = "🔐 *Recuperación de Contraseña*\n\n";
            $message .= "Hola *{$user->name}*,\n\n";
            $message .= "Recibimos una solicitud para restablecer tu contraseña.\n\n";
            $message .= "🔗 *Enlace de recuperación:*\n{$resetUrl}\n\n";
            $message .= "⏱️ Este enlace expira en *15 minutos*.\n\n";
            $message .= "⚠️ Si no solicitaste este cambio, ignora este mensaje.\n\n";
            $message .= "¿Necesitas ayuda? Contacta al administrador del sistema.";

            $whatsApp = new WhatsAppService($user->empresa_id);

            // Enviar solo si WhatsApp está configurado, sin bloquear si falla
            if ($whatsApp->isConfigured()) {
                try {
                    // Timeout reducido a 8s para no bloquear el formulario
                    $whatsApp->setTimeout(8);
                    $result = $whatsApp->sendMessage($telefono, $message, true);
                    \Log::info('Password reset WhatsApp sent', [
                        'user_id' => $user->id,
                        'phone'   => $telefono,
                        'result'  => $result,
                    ]);
                } catch (\Exception $e) {
                    \Log::warning('WhatsApp send failed (non-blocking)', [
                        'user_id' => $user->id,
                        'error'   => $e->getMessage(),
                    ]);
                }
            } else {
                \Log::warning('WhatsApp no configurado', ['empresa_id' => $user->empresa_id]);
            }

        } catch (\Exception $e) {
            \Log::error('Error in sendTokenByWhatsApp', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);
        }

        // Siempre mostrar mensaje genérico de éxito
        $this->successMessage = __('auth_ui.reset_link_sent_whatsapp');
        $this->identifier = '';
    }

    private function formatearTelefono(string $telefono, User $user): string
    {
        $limpio = preg_replace('/\D/', '', $telefono);

        if (str_starts_with($limpio, '0')) {
            $limpio = substr($limpio, 1);
        }

        $codigo = '58'; // default Venezuela
        if ($user->empresa?->pais_id) {
            $pais = DB::table('pais')->where('id', $user->empresa->pais_id)->value('codigo_telefonico');
            if ($pais) {
                $codigo = ltrim($pais, '+');
            }
        }

        if (!str_starts_with($limpio, $codigo)) {
            $limpio = $codigo . $limpio;
        }

        return '+' . $limpio;
    }

    public function hasError(string $field): bool
    {
        return $this->getErrorBag()->has($field);
    }

    public function getError(string $field): string
    {
        return $this->getErrorBag()->first($field);
    }

    public function render()
    {
        return view('livewire.auth.forgot-password', [
            'hasError' => $this->hasError(...),
            'getError' => $this->getError(...),
        ])->layout('components.layouts.auth-basic', ['title' => 'Recuperar Contraseña']);
    }
}
