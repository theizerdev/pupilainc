<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Models\ActiveSession;
use App\Models\AuditLog;
use App\Models\User;
use Ramsey\Uuid\Uuid;

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

            $this->registrarEventoSeguridad('Exceso de intentos de acceso', [
                'identificador' => $this->email,
                'segundos_restantes' => $seconds,
            ], 'restriccion');

            $this->errors['email'] = ["Demasiados intentos. Intenta de nuevo en {$seconds} segundos."];
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => "Demasiados intentos. Espera {$seconds} segundos.",
            ]);
            return;
        }

        // Verificar si la cuenta está bloqueada
        $userRecord = User::where('email', $this->email)
            ->orWhere('username', $this->email)
            ->first();

        if ($userRecord && $userRecord->locked_until && $userRecord->locked_until->isFuture()) {
            
            $this->registrarEventoSeguridad('Intento de acceso a cuenta bloqueada', [
                'identificador' => $this->email,
                'bloqueado_hasta' => 'Permanente',
                'user_id' => $userRecord->id,
            ], 'acceso_bloqueado');

            $this->errors['email'] = ["Cuenta bloqueada por seguridad. Contacta al personal de soporte técnico para habilitar tu usuario."];
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => "Cuenta bloqueada. Contacta a soporte técnico.",
            ]);
            return;
        }

        $credentials = filter_var($this->email, FILTER_VALIDATE_EMAIL)
            ? ['email' => $this->email, 'password' => $this->password]
            : ['username' => $this->email, 'password' => $this->password];

        if (!Auth::attempt($credentials, $this->remember)) {
            RateLimiter::hit($throttleKey);

            // Incrementar contador de intentos fallidos
            if ($userRecord) {
                $userRecord->increment('failed_login_attempts');
                $userRecord->update(['last_failed_login_at' => now()]);
                $intentos = $userRecord->fresh()->failed_login_attempts;

                $this->registrarEventoSeguridad('Intento de acceso fallido', [
                    'identificador' => $this->email,
                    'intentos_fallidos' => $intentos,
                    'user_id' => $userRecord->id,
                    'usuario_nombre' => $userRecord->name,
                ], 'login_fallido');

                // Bloquear usuario después de 5 intentos fallidos consecutivos
                if ($intentos >= 5) {
                    $userRecord->update([
                        'status' => false,
                        'locked_until' => now()->addYears(100), // Bloqueo permanente
                    ]);

                    $this->registrarEventoSeguridad('Usuario bloqueado por datos erróneos', [
                        'identificador' => $this->email,
                        'intentos_fallidos' => $intentos,
                        'user_id' => $userRecord->id,
                        'usuario_nombre' => $userRecord->name,
                        'bloqueado_hasta' => 'Permanente',
                        'motivo' => 'Exceso de intentos fallidos de autenticación',
                    ], 'usuario_bloqueado');

                    $this->errors['email'] = ['Cuenta bloqueada por seguridad. Contacta a soporte técnico.'];
                    $this->dispatch('notify', [
                        'type' => 'error',
                        'message' => 'Cuenta bloqueada. Contacta a soporte técnico.',
                    ]);
                    return;
                }
            } else {
                $this->registrarEventoSeguridad('Intento de acceso con usuario inexistente', [
                    'identificador' => $this->email,
                ], 'login_fallido');
            }

            $this->errors['email'] = ['Credenciales incorrectas'];
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Usuario o contraseña incorrectos',
            ]);
            return;
        }

        RateLimiter::clear($throttleKey);
        $user = Auth::user();

        // Resetear contador de intentos fallidos al loguearse exitosamente
        $user->update([
            'failed_login_attempts' => 0,
            'locked_until' => null,
            'last_failed_login_at' => null,
        ]);

        if (!$user->status) {
            Auth::logout();
            $this->registrarEventoSeguridad('Intento de acceso a cuenta desactivada', [
                'identificador' => $this->email,
                'user_id' => $user->id,
                'usuario_nombre' => $user->name,
            ], 'acceso_denegado');

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
            return redirect()->to('admin/gestion/consultas/en-consultorio');
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

    private function registrarEventoSeguridad(string $descripcion, array $datos = [], string $tipo = 'seguridad'): void
    {
        try {
            AuditLog::create([
                'id' => Uuid::uuid4()->toString(),
                'user_id' => $datos['user_id'] ?? null,
                'action' => "seguridad.{$tipo}",
                'auditable_type' => 'EventoSeguridad',
                'auditable_id' => $datos['user_id'] ?? 0,
                'old_values' => [],
                'new_values' => $datos,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'url' => request()->fullUrl(),
                'method' => request()->method(),
                'tags' => ['seguridad', $tipo, 'auth'],
                'metadata' => [
                    'descripcion' => $descripcion,
                    'tipo_evento' => $tipo,
                    'fecha_hora' => now()->format('Y-m-d H:i:s'),
                    'ip' => request()->ip(),
                ],
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error registrando evento de seguridad: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.auth.login', [
            'hasError' => $this->hasError(...),
            'getError' => $this->getError(...),
        ])->layout('components.layouts.auth-basic', ['title' => 'Login']);
    }
}
