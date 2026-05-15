<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Verify extends Component
{
    // Propiedades para manejar errores de validación
    public $errors = [];
    public $resent = false;

    public function resend()
    {
        if (Auth::user()->hasVerifiedEmail()) {
            return redirect()->intended('/');
        }

        Auth::user()->sendEmailVerificationNotification();

        $this->resent = true;
        session()->flash('resent', 'A fresh verification link has been sent to your email address.');
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
        return view('livewire.auth.verify', [
            'hasError' => $this->hasError(...),
            'getError' => $this->getError(...),
        ])->layout('components.layouts.auth-cover', ['title' => 'Verify Email']);
    }
}
