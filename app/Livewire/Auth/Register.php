<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Models\User;
use App\Mail\VerificationCodeMail;
use Illuminate\Auth\Events\Registered;
use Illuminate\Validation\ValidationException;

class Register extends Component
{
    public $name = '';
    public $email = '';
    public $phone = '';
    public $password = '';
    public $password_confirmation = '';
    public $terms = false;

    // Propiedades para manejar errores de validación
    public $errors = [];
    
    // Propiedades para validación en tiempo real
    public $nameAvailable = null;
    public $emailAvailable = null;
    public $passwordStrength = 0;
    public $passwordFeedback = '';

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:20|regex:/^[0-9+()]+$/',
            'password' => 'required|string|confirmed|min:8',
            'terms' => 'required|accepted',
        ];
    }

    public function messages()
    {
        return [
            'terms.accepted' => 'You must accept the terms and conditions.',
        ];
    }

    public function updated($field)
    {
        // Limpiar errores cuando el usuario empieza a escribir
        if (isset($this->errors[$field])) {
            unset($this->errors[$field]);
        }
        
        // Validar campo individualmente cuando se actualiza
        if (in_array($field, ['name', 'email', 'password', 'password_confirmation'])) {
            $this->validateOnly($field);
        }
        
        // Verificar disponibilidad de nombre de usuario
        if ($field === 'name' && strlen($this->name) >= 3) {
            $this->checkNameAvailability();
        }
        
        // Verificar disponibilidad de correo electrónico
        if ($field === 'email' && filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $this->checkEmailAvailability();
        }
        
        // Verificar fortaleza de contraseña
        if ($field === 'password') {
            $this->checkPasswordStrength();
        }
    }

    // Verificar disponibilidad de nombre de usuario
    public function checkNameAvailability()
    {
        if (User::where('name', $this->name)->exists()) {
            $this->nameAvailable = false;
            $this->errors['name'] = ['This username is already taken.'];
        } else {
            $this->nameAvailable = true;
            unset($this->errors['name']);
        }
    }

    // Verificar disponibilidad de correo electrónico
    public function checkEmailAvailability()
    {
        if (User::where('email', $this->email)->exists()) {
            $this->emailAvailable = false;
            $this->errors['email'] = ['This email is already registered.'];
        } else {
            $this->emailAvailable = true;
            unset($this->errors['email']);
        }
    }

    // Verificar fortaleza de contraseña
    public function checkPasswordStrength()
    {
        $this->passwordStrength = 0;
        $feedback = [];
        
        if (strlen($this->password) >= 8) {
            $this->passwordStrength += 25;
        } else {
            $feedback[] = 'At least 8 characters';
        }
        
        if (preg_match('/[a-z]/', $this->password)) {
            $this->passwordStrength += 25;
        } else {
            $feedback[] = 'One lowercase letter';
        }
        
        if (preg_match('/[A-Z]/', $this->password)) {
            $this->passwordStrength += 25;
        } else {
            $feedback[] = 'One uppercase letter';
        }
        
        if (preg_match('/[0-9]/', $this->password) || preg_match('/[^a-zA-Z\d]/', $this->password)) {
            $this->passwordStrength += 25;
        } else {
            $feedback[] = 'One number or special character';
        }
        
        if (empty($feedback)) {
            $this->passwordFeedback = 'Strong password';
        } else {
            $this->passwordFeedback = 'Missing: ' . implode(', ', $feedback);
        }
    }

    public function register()
    {
        $this->errors = []; // Limpiar errores anteriores
        
        try {
            $this->validate();
        } catch (ValidationException $e) {
            $this->errors = $e->validator->errors()->messages();
            return;
        }

        $throttleKey = Str::transliterate(Str::lower($this->email).'|'.request()->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $this->errors['email'] = [trans('auth.throttle', [
                'seconds' => RateLimiter::availableIn($throttleKey, 3),
            ])];
            return;
        }

        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'password' => Hash::make($this->password),
        ]);

        // Generar y enviar código de verificación
        $verificationCode = $user->generateVerificationCode();
        Mail::to($user->email)->send(new VerificationCodeMail($verificationCode));

        event(new Registered($user));

        Auth::login($user, true);

        RateLimiter::clear($throttleKey);

        // Regenerar sesión para prevenir session fixation
        request()->session()->regenerate();

        return redirect()->route('verification.notice');
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
        return view('livewire.auth.register', [
            'hasError' => $this->hasError(...),
            'getError' => $this->getError(...),
            'nameAvailable' => $this->nameAvailable,
            'emailAvailable' => $this->emailAvailable,
            'passwordStrength' => $this->passwordStrength,
            'passwordFeedback' => $this->passwordFeedback,
        ])->layout('components.layouts.auth-basic', ['title' => 'Register']);
    }
}