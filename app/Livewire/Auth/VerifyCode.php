<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Mail\VerificationCodeMail;
use App\Services\WhatsAppService;
use App\Models\Medico;

class VerifyCode extends Component
{
    public $code = '';
    public $codeInputs = ['', '', '', '', '', '']; // 6 campos separados
    public $resent = false;
    public $errors = [];
    public $canResend = true;
    public $resendCountdown = 0;
    public $codigoPais;

    // Reglas de validación
    protected $rules = [
        'code' => 'required|string|size:6',
    ];

    public function mount()
    {
        // Si el usuario ya ha verificado su correo, redirigir
        if (Auth::user()->hasVerifiedEmail()) {
            return redirect()->intended('/');
        }

        // Verificar si hay un límite de reenvío
        $throttleKey = $this->throttleKey();
        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $this->canResend = false;
            $this->resendCountdown = RateLimiter::availableIn($throttleKey, 3);
        }
    }

    // Generar clave para limitar intentos
    protected function throttleKey()
    {
        return 'resend-verification-code|' . Auth::id();
    }

    // Actualizar código cuando cambian los inputs individuales
    public function updatedCodeInputs($value, $index)
    {
        // Limpiar errores cuando el usuario empieza a escribir
        unset($this->errors['code']);

        // Convertir a mayúsculas
        $this->codeInputs[$index] = strtoupper($value);

        // Combinar todos los inputs en un solo código
        $this->code = implode('', $this->codeInputs);

        // Si todos los campos están llenos, verificar automáticamente
        if (strlen($this->code) == 6 && !in_array('', $this->codeInputs)) {
            $this->verifyCode();
        }

        // Mover foco al siguiente campo si se llena uno
        if (strlen($value) == 1 && $index < 5) {
            $this->dispatch('focus-next', $index + 1);
        }
    }

    // Enviar o reenviar el código de verificación
    public function sendCode()
    {
        // Verificar límite de intentos
        $throttleKey = $this->throttleKey();
        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $this->canResend = false;
            $this->resendCountdown = RateLimiter::availableIn($throttleKey, 3);
            return;
        }

        // Registrar intento
        RateLimiter::hit($throttleKey, 300); // 5 minutos

        // Generar un nuevo código y obtener el código en texto plano
        $plainCode = Auth::user()->generateVerificationCode();

         $user = Auth::user();
         $telefono = $this->formatearTelefono($user->phone);

        // Verificar si el usuario tiene teléfono y WhatsApp habilitado
        $hasWhatsApp = !empty($telefono) && $user->whatsapp_verification_enabled;

        if ($hasWhatsApp && \App\Services\WhatsAppNotificationGate::allows($user->empresa_id, 'usuarios', 'codigo_verificacion', 'usuario')) {
            // Enviar por WhatsApp
            try {
                $whatsAppService = new WhatsAppService();
                $mensaje = "🔐 *Código de Verificación* 🔐\n\n";
                $mensaje .= "Tu código de verificación es: *{$plainCode}*\n\n";
                $mensaje .= "Este código expira en 15 minutos.\n\n";
                $mensaje .= "Si no solicitaste este código, por favor ignora este mensaje.";

                $whatsAppService->sendMessage($telefono, $mensaje);

                session()->flash('resent', 'Se ha enviado un nuevo código de verificación a tu WhatsApp.');
            } catch (\Exception $e) {
                // Si falla WhatsApp, enviar por correo como respaldo
                Mail::to($user->email)->send(new VerificationCodeMail($plainCode));
                session()->flash('resent', 'Se ha enviado un nuevo código de verificación a tu correo electrónico (WhatsApp no disponible).');
            }
        } else {
            // Enviar por correo si no tiene teléfono o WhatsApp deshabilitado
            Mail::to($user->email)->send(new VerificationCodeMail($plainCode));
            session()->flash('resent', 'Se ha enviado un nuevo código de verificación a tu correo electrónico.');
        }

        $this->resent = true;
        $this->canResend = false;
        // Limpiar campos de código
        $this->codeInputs = ['', '', '', '', '', ''];
        $this->code = '';

        // Iniciar contador regresivo
        $this->resendCountdown = 300; // 5 minutos en segundos

        // Actualizar estado de reenvío cada segundo
        $this->js('
            let countdown = ' . $this->resendCountdown . ';
            const interval = setInterval(() => {
                countdown--;
                if (countdown <= 0) {
                    clearInterval(interval);
                    window.livewire.emit("countdownFinished");
                }
            }, 1000);
        ');
    }

    // Escuchar cuando termina el contador
    protected $listeners = ['countdownFinished' => 'enableResend'];

    public function enableResend()
    {
        $this->canResend = true;
        $this->resendCountdown = 0;
    }

    // Verificar el código ingresado
    public function verifyCode()
    {
        $this->errors = [];

        // Validar entrada
        if (strlen($this->code) != 6) {
            $this->errors['code'] = 'El código debe tener 6 caracteres.';
            return;
        }

        // Verificar si el código es válido
        if (Auth::user()->isVerificationCodeValid($this->code)) {
            // Marcar el correo como verificado
            Auth::user()->markEmailAsVerified();

            // Regenerar sesión para prevenir session fixation
            request()->session()->regenerate();

            // Redirigir al dashboard o a la página anterior
            return redirect()->intended('/');
        } else {
            $this->errors['code'] = 'El código ingresado no es válido o ha expirado.';
        }
    }

    // Método para verificar si un campo tiene error
    public function hasError($field)
    {
        return isset($this->errors[$field]) && !empty($this->errors[$field]);
    }

    // Método para obtener los mensajes de error de un campo
    public function getError($field)
    {
        return $this->hasError($field) ? $this->errors[$field][0] : '';
    }

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

     protected function obtenerCodigoPais(): string
    {
        if ($this->codigoPais !== null) {
            return $this->codigoPais;
        }

        $this->codigoPais = '58';

        $empId = auth()->user()->empresa_id;
        if (!$empId && auth()->check() && auth()->user()->empresa_id) {
            $empId = auth()->user()->empresa_id;
        }

        if ($empId) {
            $empresa = DB::table('empresas')->where('id', $empId)->first();
            if ($empresa && $empresa->pais_id) {
                $pais = DB::table('pais')->where('id', $empresa->pais_id)->first();
                if ($pais && $pais->codigo_telefonico) {
                    $this->codigoPais = ltrim($pais->codigo_telefonico, '+');
                }
            }
        }

        return $this->codigoPais;
    }

    public function render()
    {
        return view('livewire.auth.verify-code', [
            'hasError' => $this->hasError(...),
            'getError' => $this->getError(...),
            'canResend' => $this->canResend,
            'resendCountdown' => $this->resendCountdown,
        ])->layout('components.layouts.auth-cover', ['title' => 'Verificar Código']);
    }
}
