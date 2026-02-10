<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use App\Services\WhatsAppService;

class ForgotPassword extends Component
{
    public $identifier = '';
    public $method = 'phone'; // 'email' o 'phone'
    public $successMessage;
    public $user;

    // Propiedades para manejar errores de validación
    public $errors = [];

    public function rules()
    {
        return [
            'identifier' => 'required|string',
            'method' => 'required|in:email,phone',
        ];
    }

    public function sendResetLink()
    {
        $this->reset(['successMessage']);
        $this->resetValidation();

        $this->validate();

        // Buscar usuario por email o teléfono
        $user = $this->findUserByIdentifier($this->identifier);
   
        if (!$user) {
            $this->addError('identifier', __('auth_ui.user_not_found'));
            return;
        }

        if ($this->method === 'email') {
            $this->sendResetLinkByEmail($user);
        } else {
            $this->sendResetLinkByWhatsApp($user);
        }

        // Forzar re-renderizado para mostrar mensajes
        $this->js('$wire.$refresh()');
    }

    /**
     * Buscar usuario por email o teléfono
     */
    private function findUserByIdentifier($identifier)
    {
        // Limpiar el identificador
        $cleanIdentifier = trim($identifier);
   
        // Buscar por email primero
        $user = User::where('email', $cleanIdentifier)->first();
        if ($user) {
            return $user;
        }

        return User::where('phone', $cleanIdentifier)->first();
    }

    /**
     * Enviar enlace de restablecimiento por email
     */
    private function sendResetLinkByEmail(User $user)
    {
        $status = Password::sendResetLink(
            ['email' => $user->email]
        );

        if ($status == Password::RESET_LINK_SENT) {
            $this->successMessage = __('auth_ui.reset_link_sent_email');
            $this->identifier = '';
        } else {
            $this->addError('identifier', __($status));
        }
    }

    /**
     * Enviar enlace de restablecimiento por WhatsApp
     */
    private function sendResetLinkByWhatsApp(User $user)
    {   

        $this->user = $user;
        
        if (!$user->phone) {
            $this->addError('identifier', __('auth_ui.no_phone_registered'));
            return;
        }

        // Validar que el usuario tenga empresa configurada
        if (!$user->empresa_id) {
            $this->addError('identifier', 'Usuario no tiene empresa asociada. Contacte al administrador.');
            return;
        }

        // Validar que la empresa tenga WhatsApp configurado
        $empresa = $user->empresa;
        if (!$empresa || !$empresa->whatsapp_api_key) {
            $this->addError('identifier', 'La empresa no tiene WhatsApp configurado. Contacte al administrador.');
            return;
        }

        try {

            $telefonoFormateado = $this->formatearTelefono($user->phone);
          
            // Generar nueva contraseña de 8 dígitos numéricos
            $newPassword = str_pad(mt_rand(0, 99999999), 8, '0', STR_PAD_LEFT);

            // Actualizar la contraseña del usuario directamente
            $user->update([
                'password' => Hash::make($newPassword)
            ]);

            // Preparar mensaje de WhatsApp con la nueva contraseña
            $message = "🏥 *Nueva Contraseña Generada*\n\n";
            $message .= "Hola *{$user->name}*,\n\n";
            $message .= "✅ Hemos generado una nueva contraseña para tu cuenta.\n\n";
            $message .= "🔑 *Contraseña temporal:* {$newPassword}\n\n";
            $message .= "📋 *Importante:*\n";
            $message .= "• Ingresa con esta contraseña\n";
            $message .= "• Cámbiala inmediatamente en tu perfil\n";
            $message .= "• Esta contraseña es temporal por seguridad\n\n";
            $message .= "¿Necesitas ayuda? Contacta a soporte técnico.\n\n";
            $message .= "Gracias por confiar en nosotros. 😊";


            
           

            // Enviar por WhatsApp - usar empresa del usuario
            $whatsApp = new WhatsAppService($user->empresa_id);
            $result = $whatsApp->sendMessage($telefonoFormateado, $message, true);

            // Log detallado para debugging
            \Log::info('WhatsApp password reset attempt', [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_phone' => $user->phone,
                'phone_formatted' => $telefonoFormateado,
                'message_length' => strlen($message),
                'result' => $result,
                'result_type' => gettype($result),
                'result_keys' => is_array($result) ? array_keys($result) : null,
                'api_url' => config('whatsapp.api_url'),
                'company_id' => $whatsApp->getCompanyId(),
                'service_configured' => $whatsApp->isConfigured(),
                'empresa_id' => $user->empresa_id
            ]);

            if ($result && is_array($result) && isset($result['success']) && $result['success']) {
                $this->successMessage = __('auth_ui.password_reset_success_whatsapp');
                $this->identifier = '';
            } else {
                // Log the error details
                \Log::error('WhatsApp password reset failed', [
                    'user_id' => $user->id,
                    'result' => $result,
                    'result_type' => gettype($result)
                ]);
                $this->addError('identifier', __('auth_ui.whatsapp_send_failed'));
            }

        } catch (\Exception $e) {
            \Log::error('Error sending WhatsApp password reset', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            $this->addError('identifier', __('auth_ui.whatsapp_send_failed'));
        }
    }

    
    /**
     * Obtiene el código de país de la empresa
     */
    protected function obtenerCodigoPais(): string
    {
      
        $codigoPais = '58';

            $empresa = $this->user->empresa;
            if ($empresa && $empresa->pais_id) {
                $pais = \DB::table('pais')->where('id', $empresa->pais_id)->first();
                if ($pais && $pais->codigo_telefonico) {
                    $codigoPais = ltrim($pais->codigo_telefonico, '+');
                    \Log::info('Código de país encontrado', [
                        'empresa_id' =>  $empresa->id,
                        'pais_id' => $empresa->pais_id,
                        'codigo_telefonico' => $pais->codigo_telefonico,
                        'codigo_pais' => $codigoPais
                    ]);
                }
            }
        

        return $codigoPais;
    }

    /**
     * Formatea el número de teléfono al formato internacional
     */
    protected function formatearTelefono(string $telefono): string
    {
        $limpio = preg_replace('/\D/', '', $telefono);

        if (str_starts_with($limpio, '0')) {
            $limpio = substr($limpio, 1);
        }

        $codigo = $this->obtenerCodigoPais();

        if (!str_starts_with($limpio, $codigo) && strlen($limpio) >= 7 && strlen($limpio) <= 12) {
            $limpio = $codigo . $limpio;
        }

        return '+' . $limpio;
    }

    // Método para verificar si un campo tiene error
    public function hasError($field)
    {
        return $this->getErrorBag()->has($field);
    }

    // Método para obtener los mensajes de error de un campo
    public function getError($field)
    {
        return $this->getErrorBag()->first($field);
    }

    public function render()
    {
        return view('livewire.auth.forgot-password', [
            'hasError' => $this->hasError(...),
            'getError' => $this->getError(...),
        ])->layout('components.layouts.auth-basic', ['title' => 'Forgot Password']);
    }
}