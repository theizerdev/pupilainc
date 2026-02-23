<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Models\ActiveSession;

class Login extends Component
{
    public $email = '';
    public $password = '';
    public $remember = false;
    public $latitude;
    public $longitude;
    public $errors = [];

    protected $listeners = ['setCoordinates' => 'setCoordinates'];

    public function rules()
    {
        return [
            'email' => 'required|string',
            'password' => 'required|string|min:6',
        ];
    }

    public function messages()
    {
        return [
            'email.required' => 'El usuario o email es obligatorio',
            'password.required' => 'La contraseña es obligatoria',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres',
        ];
    }

    public function updated($field)
    {
        if (isset($this->errors[$field])) {
            unset($this->errors[$field]);
        }
        $this->validateOnly($field);
    }

    public function authenticate()
    {
        $this->errors = [];

        try {
            $this->validate();
        } catch (ValidationException $e) {
            $this->errors = $e->validator->errors()->messages();
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Por favor corrige los errores en el formulario',
            ]);
            return;
        }

        $throttleKey = Str::transliterate(Str::lower($this->email).'|'.request()->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $this->errors['email'] = ["Demasiados intentos. Intenta de nuevo en {$seconds} segundos."];
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => "Demasiados intentos. Espera {$seconds} segundos.",
            ]);
            return;
        }

        $credentials = filter_var($this->email, FILTER_VALIDATE_EMAIL) 
            ? ['email' => $this->email, 'password' => $this->password]
            : ['username' => $this->email, 'password' => $this->password];
            
        if (!Auth::attempt($credentials, $this->remember)) {
            RateLimiter::hit($throttleKey);
            $this->errors['email'] = ['Credenciales incorrectas'];
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Usuario o contraseña incorrectos',
            ]);
            return;
        }

        RateLimiter::clear($throttleKey);
        $user = Auth::user();

        if (!$user->status) {
            Auth::logout();
            $this->errors['email'] = ['Tu cuenta está desactivada. Contacta al administrador.'];
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Cuenta desactivada',
            ]);
            return;
        }

        if ($user->two_factor_enabled) {
            Auth::logout();
            session([
                '2fa:user:id' => $user->id,
                '2fa:user:email' => $user->email
            ]);
            return redirect()->route('two-factor.login');
        }

        $this->trackUserLogin();
        request()->session()->regenerate();

        if ($user->id === 1) {
            return redirect()->route('superadmin.dashboard');
        }

        $medico = \App\Models\Medico::where('user_id', $user->id)->first();
        if ($medico) {
            return redirect()->to('admin/citas');
        }

        if ($user->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->intended('/');
    }

    private function trackUserLogin()
    {
        $user = Auth::user();
        $request = request();
        $sessionId = $request->session()->getId();
        $ipAddress = $request->ip();
        $locationData = $this->getLocationData($ipAddress);

        ActiveSession::where('user_id', $user->id)->update(['is_current' => false]);

        $activeSession = ActiveSession::where('user_id', $user->id)
            ->where('session_id', $sessionId)
            ->first();

        $sessionData = [
            'last_activity' => now(),
            'ip_address' => $ipAddress,
            'user_agent' => $request->userAgent(),
            'is_current' => true,
            'is_active' => true,
            'login_at' => now(),
            'location' => $locationData['location'] ?? null,
            'latitude' => $locationData['latitude'] ?? null,
            'longitude' => $locationData['longitude'] ?? null,
        ];

        if ($activeSession) {
            $activeSession->update($sessionData);
        } else {
            $sessionData['user_id'] = $user->id;
            $sessionData['session_id'] = $sessionId;
            ActiveSession::create($sessionData);
        }
    }

    private function getLocationData($ipAddress)
    {
        $locationData = [
            'location' => null,
            'latitude' => null,
            'longitude' => null,
        ];

        if ($this->latitude && $this->longitude) {
            return $this->reverseGeocode($this->latitude, $this->longitude);
        }

        if ($ipAddress === '127.0.0.1' || $ipAddress === '::1' || strpos($ipAddress, '192.168.') === 0) {
            $locationData['location'] = 'Local';
            return $locationData;
        }

        $locationData['location'] = 'Ubicación desconocida';
        return $locationData;
    }

    private function reverseGeocode($lat, $lon)
    {
        $locationData = [
            'latitude' => $lat,
            'longitude' => $lon,
            'location' => null,
        ];

        try {
            $url = "https://nominatim.openstreetmap.org/reverse?format=json&lat={$lat}&lon={$lon}&addressdetails=1";
            $context = stream_context_create([
                "http" => [
                    "header" => "User-Agent: larawire/1.0\r\n",
                    "timeout" => 10
                ]
            ]);

            $response = file_get_contents($url, false, $context);
            $data = json_decode($response, true);

            if ($data && isset($data['address'])) {
                $city = $data['address']['city'] ?? $data['address']['town'] ?? $data['address']['village'] ?? 'Desconocido';
                $state = $data['address']['state'] ?? $data['address']['region'] ?? 'Desconocido';
                $country = $data['address']['country'] ?? 'Desconocido';
                $locationData['location'] = "{$city}, {$state}, {$country}";
            } else {
                $locationData['location'] = "Lat: {$lat}, Lon: {$lon}";
            }
        } catch (\Exception $e) {
            \Log::warning("Error obteniendo geolocalización: " . $e->getMessage());
            $locationData['location'] = "Lat: {$lat}, Lon: {$lon}";
        }

        return $locationData;
    }

    public function hasError($field)
    {
        return isset($this->errors[$field]) && !empty($this->errors[$field]);
    }

    public function getError($field)
    {
        return $this->hasError($field) ? $this->errors[$field][0] : '';
    }

    public function render()
    {
        return view('livewire.auth.login', [
            'hasError' => $this->hasError(...),
            'getError' => $this->getError(...),
        ])->layout('components.layouts.auth-basic', ['title' => 'Login']);
    }
}
