<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Models\ActiveSession;
use PragmaRX\Google2FA\Google2FA;

class Login extends Component
{
    public $email = '';
    public $password = '';
    public $remember = false;

    public $latitude;
    public $longitude;


    // Define un oyente para el evento 'set-coordinates'
    protected $listeners = ['setCoordinates' => 'setCoordinates'];

    // Propiedades para manejar errores de validaciรณn
    public $errors = [];

    public function rules()
    {
        return [
            'email' => 'required|string',
            'password' => 'required|string',
        ];
    }

    public function updated($field)
    {
        // Limpiar errores cuando el usuario empieza a escribir
        if (isset($this->errors[$field])) {
            unset($this->errors[$field]);
        }
    }

    public function authenticate()
    {
        $this->errors = []; // Limpiar errores anteriores

        try {
            $this->validate();
        } catch (ValidationException $e) {
            $this->errors = $e->validator->errors()->messages();
            return;
        }

        $throttleKey = Str::transliterate(Str::lower($this->email).'|'.request()->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $this->errors['email'] = [trans('auth.throttle', [
                'seconds' => RateLimiter::availableIn($throttleKey, 5),
            ])];
            return;
        }

        // Intentar autenticar al usuario con email o username
        $credentials = filter_var($this->email, FILTER_VALIDATE_EMAIL) 
            ? ['email' => $this->email, 'password' => $this->password]
            : ['username' => $this->email, 'password' => $this->password];
            
        if (!Auth::attempt($credentials, $this->remember)) {
            RateLimiter::hit($throttleKey);

            $this->errors['email'] = [trans('auth.failed')];
            return;
        }

        RateLimiter::clear($throttleKey);

        // Obtener el usuario autenticado
        $user = Auth::user();

        // Verificar roles y redirigir según corresponda
        if ($user->id === 1) {
            // Super Administrador
            $this->trackUserLogin();
            request()->session()->regenerate();
            return redirect()->route('superadmin.dashboard');
        }

        // Verificar si el usuario es un médico
        $medico = \App\Models\Medico::where('user_id', $user->id)->first();
        if ($medico) {
            // Registrar la sesión activa
            $this->trackUserLogin();

            // Regenerar sesión para prevenir session fixation
            request()->session()->regenerate();

            // Redirigir al dashboard del médico
            return redirect()->route('doctor.dashboard', ['id' => $medico->id]);
        }

        // Verificar si el usuario tiene rol de administrador
        if ($user->hasRole('admin')) {
            // Registrar la sesión activa
            $this->trackUserLogin();

            // Regenerar sesión para prevenir session fixation
            request()->session()->regenerate();

            // Redirigir al dashboard de administrador
            return redirect()->route('admin.dashboard');
        }

        // Verificar si el usuario tiene 2FA habilitado
        if ($user->two_factor_enabled) {
            // Cerrar la sesiรณn temporalmente
            Auth::logout();

            // Guardar informaciรณn del usuario en la sesiรณn para la verificaciรณn 2FA
            session([
                '2fa:user:id' => $user->id,
                '2fa:user:email' => $user->email
            ]);

            // Redirigir a la pรกgina de verificaciรณn 2FA
            return redirect()->route('two-factor.login');
        }

        // Registrar la sesiรณn activa
        $this->trackUserLogin();

        // Regenerar sesiรณn para prevenir session fixation
        request()->session()->regenerate();

        return redirect()->intended('/');
    }

    /**
     * Registrar la sesiรณn activa del usuario
     */
    private function trackUserLogin()
    {
        $user = Auth::user();
        $request = request();
        $sessionId = $request->session()->getId();
        $ipAddress = $request->ip();

        // Obtener informaciรณn de geolocalizaciรณn (preferir datos de la sesiรณn si existen)
        $locationData = $this->getLocationData($ipAddress);

        // Marcar cualquier sesiรณn existente como no actual
        ActiveSession::where('user_id', $user->id)
            ->update(['is_current' => false]);

        // Verificar si ya existe un registro para esta sesiรณn
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
            // Actualizar la sesiรณn existente
            $activeSession->update($sessionData);
        } else {
            // Crear un nuevo registro de sesiรณn
            $sessionData['user_id'] = $user->id;
            $sessionData['session_id'] = $sessionId;
            ActiveSession::create($sessionData);
        }
    }

    /**
     * Obtener informaciรณn de geolocalizaciรณn basada en coordenadas
     */
    private function getLocationData($ipAddress)
    {
        // Datos por defecto
        $locationData = [
            'location' => null,
            'latitude' => null,
            'longitude' => null,
        ];

        // Verificar si hay coordenadas en el componente
        if ($this->latitude && $this->longitude) {
            // Usar coordenadas del componente y hacer geocodificaciรณn inversa
            return $this->reverseGeocode($this->latitude, $this->longitude);
        }

        // Para IPs locales o cuando no hay coordenadas
        if ($ipAddress === '127.0.0.1' || $ipAddress === '::1' || strpos($ipAddress, '192.168.') === 0) {
            $locationData['location'] = 'Local';
            return $locationData;
        }

        // Si no hay coordenadas disponibles, usar valores predeterminados
        $locationData['location'] = 'Ubicaciรณn desconocida';
        return $locationData;
    }

    /**
     * Reverse geocode coordinates to get location information using Nominatim
     */
    private function reverseGeocode($lat, $lon)
    {
        $locationData = [
            'latitude' => $lat,
            'longitude' => $lon,
            'location' => null,
        ];

        try {
            // Usar Nominatim para geocodificaciรณn inversa con las coordenadas
            $url = "https://nominatim.openstreetmap.org/reverse?format=json&lat={$lat}&lon={$lon}&addressdetails=1";

            // Configurar contexto para la solicitud con User-Agent requerido
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
            \Log::warning("Error obteniendo geolocalizaciรณn para coordenadas ({$lat}, {$lon}): " . $e->getMessage());
            $locationData['location'] = "Lat: {$lat}, Lon: {$lon}";
        }

        return $locationData;
    }

    // Mรฉtodo para verificar si un campo tiene error
    public function hasError($field)
    {
        return isset($this->errors[$field]) && !empty($this->errors[$field]);
    }

    // Mรฉtodo para obtener los mensajes de error de un campo
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