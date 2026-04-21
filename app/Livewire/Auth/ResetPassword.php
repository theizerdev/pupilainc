<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password as PasswordRule;
use App\Models\User;

class ResetPassword extends Component
{
    public string $token = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public ?string $successMessage = null;

    protected function rules(): array
    {
        return [
            'token'    => 'required|string|size:6',
            'email'    => 'required|email',
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
        ];
    }

    protected $messages = [
        'token.size' => 'El código debe tener 6 dígitos.',
    ];

    public function mount(string $token, string $email = ''): void
    {
        $this->token = $token;
        $this->email = $email;
        $user = User::where('verification_code', $token)->first();
        if ($user) {
            $this->email = $user->email;
        }
    }

    public function resetPassword()
    {
        $this->reset('successMessage');
        $this->resetValidation();
        $this->validate();

        $user = User::where('email', $this->email)->first();

        if (!$user) {
            $this->addError('email', 'Usuario no encontrado.');
            return;
        }

        // Buscar token en password_reset_tokens
        $resetRecord = DB::table('password_reset_tokens')
            ->where('email', $this->email)
            ->first();

        if (!$resetRecord) {
            $this->addError('token', 'Token inválido o expirado.');
            return;
        }

        // Validar caducidad (15 minutos)
        $createdAt = \Carbon\Carbon::parse($resetRecord->created_at);
        if ($createdAt->addMinutes(15)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $this->email)->delete();
            $this->addError('token', 'El token ha expirado. Solicita uno nuevo.');
            return;
        }

        // Validar token
        if (!Hash::check($this->token, $resetRecord->token)) {
            $this->addError('token', 'El código ingresado es incorrecto.');
            return;
        }

        // Actualizar contraseña
        $user->update([
            'password' => Hash::make($this->password),
            'verification_code' => null,
        ]);

        // Eliminar token usado
        DB::table('password_reset_tokens')->where('email', $this->email)->delete();

        session()->flash('status', 'Contraseña restablecida exitosamente.');


        return redirect()->route('login');
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
        return view('livewire.auth.reset-password', [
            'hasError' => $this->hasError(...),
            'getError' => $this->getError(...),
        ])->layout('components.layouts.auth-basic', ['title' => 'Restablecer Contraseña']);
    }
}
