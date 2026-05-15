<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;
use App\Models\ActiveSession;

class TwoFactorLogin extends Component
{
    public $email = '';
    public $password = '';
    public $code = '';
    public $remember = false;
    public $user = null;

    public $latitude;
    public $longitude;

    // Define un oyente para el evento 'set-coordinates'
    protected $listeners = ['setCoordinates' => 'setCoordinates'];

    public function rules()
    {
        return [
            'code' => 'required|string|size:6',
        ];
    }

    public function mount()
    {
        // Verificar que se haya pasado un usuario desde el login
        if (!session()->has('2fa:user:id')) {
            return redirect()->route('login');
        }

        $this->email = session('2fa:user:email');
    }

    public function verifyCode()
    {
        $this->validate();

        $throttleKey = Str::transliterate(Str::lower($this->email).'|'.request()->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            session()->flash('error', trans('auth.throttle', [
                'seconds' => RateLimiter::availableIn($throttleKey, 5),
            ]));
            return;
        }

        // Obtener el usuario
        $userId = session('2fa:user:id');
        $this->user = \App\Models\User::find($userId);

        if (!$this->user) {
            session()->flash('error', trans('auth.failed'));
            return redirect()->route('login');
        }

        // Verificar que el usuario tenga 2FA configurado
        if (!$this->user->two_factor_secret) {
            session()->flash('error', 'La autenticación en dos pasos no está configurada correctamente para este usuario.');
            return redirect()->route('login');
        }

        try {
            // Verificar el código 2FA
            $google2fa = new Google2FA();
            $valid = $google2fa->verifyKey(
                decrypt($this->user->two_factor_secret),
                $this->code
            );
        } catch (\Exception $e) {
            session()->flash('error', 'Error al verificar el código. Por favor, inténtelo de nuevo.');
            return;
        }

        if (!$valid) {
            RateLimiter::hit($throttleKey);
            session()->flash('error', 'Código de verificación inválido.');
            return;
        }

        RateLimiter::clear($throttleKey);

        // Iniciar sesión
        Auth::login($this->user, $this->remember);

        // Registrar la sesión activa
        $this->trackUserLogin();

        // Limpiar datos de sesión 2FA
        session()->forget(['2fa:user:id', '2fa:user:email']);

        // Regenerar sesión para prevenir session fixation
        request()->session()->regenerate();

        return redirect()->intended('/');
    }

    /**
     * Registrar la sesión activa del usuario
     */
    private function trackUserLogin()
    {
        $user = Auth::user();
        $request = request();
        $sessionId = $request->session()->getId();
        $ipAddress = $request->ip();

        // Obtener información de geolocalización (preferir datos de la sesión si existen)
        $locationData = $this->getLocationData($ipAddress);

        // Marcar cualquier sesión existente como no actual
        ActiveSession::where('user_id', $user->id)
            ->update(['is_current' => false]);

        // Verificar si ya existe un registro para esta sesión
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
            // Actualizar la sesión existente
            $activeSession->update($sessionData);
        } else {
            // Crear un nuevo registro de sesión
            $sessionData['user_id'] = $user->id;
            $sessionData['session_id'] = $sessionId;
            ActiveSession::create($sessionData);
        }
    }

    /**
     * Obtener información de geolocalización basada en coordenadas
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
            // Usar coordenadas del componente y hacer geocodificación inversa
            return $this->reverseGeocode($this->latitude, $this->longitude);
        }

        // Para IPs locales o cuando no hay coordenadas
        if ($ipAddress === '127.0.0.1' || $ipAddress === '::1' || strpos($ipAddress, '192.168.') === 0) {
            $locationData['location'] = 'Local';
            return $locationData;
        }

        // Si no hay coordenadas disponibles, usar valores predeterminados
        $locationData['location'] = 'Ubicación desconocida';
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
            // Usar Nominatim para geocodificación inversa con las coordenadas
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
            \Log::warning("Error obteniendo geolocalización para coordenadas ({$lat}, {$lon}): " . $e->getMessage());
            $locationData['location'] = "Lat: {$lat}, Lon: {$lon}";
        }

        return $locationData;
    }

    public function render()
    {
        return view('livewire.auth.two-factor-login')
            ->layout('components.layouts.auth-cover', ['title' => 'Verificación 2FA']);
    }
}
