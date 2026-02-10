<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;

class ResetPassword extends Component
{
    public $token;
    public $email = '';
    public $password = '';
    public $password_confirmation = '';
    public $successMessage;

    // Propiedades para manejar errores de validación
    public $errors = [];

    public function rules()
    {
        return [
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
        ];
    }

    public function mount($token)
    {
        $this->token = $token;
    }

    public function resetPassword()
    {
        $this->errors = []; // Limpiar errores anteriores

        try {
            $this->validate();
        } catch (ValidationException $e) {
            $this->errors = $e->validator->errors()->messages();
            return;
        }

        // Buscar el usuario por email o teléfono
        $user = $this->findUserByEmailOrPhone($this->email);

        if (!$user) {
            $this->errors['email'] = [__('auth_ui.user_not_found')];
            return;
        }

        $status = Password::reset(
            [
                'token' => $this->token,
                'email' => $user->email, // Usar el email del usuario encontrado
                'password' => $this->password,
                'password_confirmation' => $this->password_confirmation,
            ],
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        if ($status == Password::PASSWORD_RESET) {
            $this->successMessage = __($status);
            $this->email = '';
            $this->password = '';
            $this->password_confirmation = '';

            session()->flash('status', __($status));

            return redirect()->route('login');
        } else {
            $this->errors['email'] = [__($status)];
        }
    }

    /**
     * Buscar usuario por email o teléfono
     */
    private function findUserByEmailOrPhone($identifier)
    {
        // Limpiar el identificador
        $cleanIdentifier = trim($identifier);

        // Buscar por email primero
        $user = \App\Models\User::where('email', $cleanIdentifier)->first();
        if ($user) {
            return $user;
        }

        // Si no es email, buscar por teléfono
        // Limpiar el teléfono (remover espacios, guiones, etc.)
        $cleanPhone = preg_replace('/[^\d]/', '', $cleanIdentifier);

        // Si no comienza con código de país, agregar el código de Venezuela por defecto
        if (!preg_match('/^58/', $cleanPhone)) {
            $cleanPhone = '58' . $cleanPhone;
        }

        return \App\Models\User::where('phone', $cleanPhone)->first();
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

    public function render()
    {
        return view('livewire.auth.reset-password', [
            'hasError' => $this->hasError(...),
            'getError' => $this->getError(...),
        ])->layout('components.layouts.auth-basic', ['title' => 'Reset Password']);
    }
}