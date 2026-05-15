<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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
        $this->validate();

        $user = $this->findUser($this->identifier);

        if (!$user) {
            $this->addError('identifier', 'No encontramos ninguna cuenta asociada a este número de teléfono.');
            return;
        }

        if (!$user->phone) {
            $this->addError('identifier', 'Este usuario no tiene un número de teléfono registrado.');
            return;
        }

        if (!$user->empresa_id) {
            $this->addError('identifier', 'Usuario sin empresa asociada. Contacta al administrador.');
            return;
        }

        $this->sendTokenByWhatsApp($user);
    }

    private function findUser(string $identifier): ?User
    {
        // Dejar solo dígitos
        $digits = preg_replace('/\D/', '', trim($identifier));

        // Variantes del número que pueden estar guardadas en BD
        $sinCero    = ltrim($digits, '0');           // 4241703465
        $conCero    = '0' . $sinCero;                // 04241703465

        return User::where('phone', $digits)
            ->orWhere('phone', $sinCero)
            ->orWhere('phone', $conCero)
            ->first();
    }

    private function formatearTelefono(string $telefono, User $user): string
    {
        // 1. Dejar solo dígitos
        $digits = preg_replace('/\D/', '', $telefono);

        // 2. Obtener código del país desde la empresa del usuario
        $codigoPais = '58'; // Venezuela por defecto
        if ($user->empresa?->pais_id) {
            $pais = DB::table('pais')
                ->where('id', $user->empresa->pais_id)
                ->value('codigo_telefonico');
            if ($pais) {
                $codigoPais = ltrim(trim($pais), '+');
            }
        }

        // 3. Quitar 0 inicial si existe (04241703465 → 4241703465)
        if (str_starts_with($digits, '0')) {
            $digits = substr($digits, 1);
        }

        // 4. Quitar código de país si ya está incluido (evitar 5858...)
        if (str_starts_with($digits, $codigoPais)) {
            $digits = substr($digits, strlen($codigoPais));
        }

        // 5. Resultado: +584241703465
        return '+' . $codigoPais . $digits;
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

            $code     = User::find($user->id);
            $code->verification_code = $token;
            $code->save();

            $telefono = $this->formatearTelefono($user->phone, $user);
            $resetUrl = route('password.reset.token', ['token' => $token]);

            \Log::info('Password reset — número formateado', [
                'user_id'          => $user->id,
                'phone_original'   => $user->phone,
                'phone_formateado' => $telefono,
                'pais_id'          => $user->empresa?->pais_id,
            ]);

            $message  = "🔐 *Recuperación de Contraseña*\n\n";
            $message .= "Hola *{$user->name}*,\n\n";
            $message .= "Recibimos una solicitud para restablecer tu contraseña.\n\n";
            $message .= "🔗 *Enlace de recuperación:*\n{$resetUrl}\n\n";
            $message .= "⏱️ Este enlace expira en *15 minutos*.\n\n";
            $message .= "⚠️ Si no solicitaste este cambio, ignora este mensaje.\n\n";
            $message .= "¿Necesitas ayuda? Contacta al administrador del sistema.";

            $whatsApp = new WhatsAppService($user->empresa_id);

            if ($whatsApp->isConfigured()) {
                try {
                    $whatsApp->setTimeout(8);
                    $result = $whatsApp->sendMessage($telefono, $message, true);
                    \Log::info('Password reset WhatsApp enviado', [
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

        $this->successMessage = __('auth_ui.reset_link_sent_whatsapp');
        $this->identifier = '';
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
        ])->layout('components.layouts.auth-cover', ['title' => 'Recuperar Contraseña']);
    }
}
