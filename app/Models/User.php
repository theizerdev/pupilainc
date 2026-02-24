<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Traits\HasSpanishActivityLog;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, HasRoles, LogsActivity, HasSpanishActivityLog;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'phone',
        'whatsapp_verification_enabled',
        'password',
        'verification_code',
        'verification_code_sent_at',
        'empresa_id',
        'sucursal_id',
        'status',
        'failed_login_attempts',
        'locked_until',
        'last_failed_login_at',
        'two_factor_enabled',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'avatar',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'verification_code',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_enabled' => 'boolean',
            'two_factor_recovery_codes' => 'array',
            'verification_code_sent_at' => 'datetime',
            'locked_until' => 'datetime',
            'last_failed_login_at' => 'datetime',
        ];
    }

    /**
     * Get user initials from their name
     */
    public function getInitialsAttribute()
    {
        $names = explode(' ', trim($this->name));
        
        if (count($names) < 2) {
            // Si solo hay un nombre, devolver la inicial de ese nombre
            return strtoupper(substr($names[0], 0, 1));
        }
        
        // Obtener la inicial del primer nombre
        $firstInitial = strtoupper(substr($names[0], 0, 1));
        
        // Obtener la inicial del primer apellido (última palabra)
        $lastInitial = strtoupper(substr(end($names), 0, 1));
        
        return $firstInitial . $lastInitial;
    }

    /**
     * Generar un código de verificación de 8 caracteres (alfanumérico)
     */
    public function generateVerificationCode()
    {
        // Generar código alfanumérico de 8 caracteres
        $code = strtoupper(Str::random(6));

        // Almacenar el código cifrado
        $this->verification_code = Hash::make($code);
        $this->verification_code_sent_at = Carbon::now();
        $this->save();

        // Devolver el código sin cifrar para enviar por correo
        return $code;
    }

    /**
     * Verificar si el código proporcionado es válido
     */
    public function isVerificationCodeValid($code)
    {
        // Verificar que el código exista y no haya expirado (15 minutos)
        if (!$this->verification_code || !$this->verification_code_sent_at) {
            return false;
        }

        if (Carbon::now()->diffInMinutes($this->verification_code_sent_at) > 15) {
            return false;
        }

        // Verificar que el código coincida
        return Hash::check($code, $this->verification_code);
    }

    /**
     * Marcar el correo electrónico como verificado
     */
    public function markEmailAsVerified()
    {
        $this->email_verified_at = Carbon::now();
        $this->verification_code = null;
        $this->verification_code_sent_at = null;
        $this->save();
    }

    /**
     * Get the active sessions for the user.
     */
    public function activeSessions()
    {
        return $this->hasMany(ActiveSession::class);
    }

    /**
     * Get the empresa that owns the user.
     */
    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    /**
     * Get the sucursal that belongs to the user.
     */
    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    /**
     * Get the user's common locations from active sessions
     */
    public function getCommonLocationsAttribute()
    {
        if (method_exists($this, 'sessions')) {
            return $this->sessions()
                ->whereNotNull('location')
                ->select('location')
                ->distinct()
                ->limit(5)
                ->get()
                ->toArray();
        }

        return [];
    }

    /**
     * Get the messages sent by the user.
     */
    public function mensajesEnviados()
    {
        return $this->hasMany(Mensaje::class, 'remitente_id');
    }

    /**
     * Get the messages received by the user.
     */
    public function mensajesRecibidos()
    {
        return $this->belongsToMany(Mensaje::class, 'mensaje_destinatarios', 'user_id', 'mensaje_id')
            ->withPivot('leido', 'leido_en', 'archivado', 'archivado_en')
            ->withTimestamps()
            ->orderBy('created_at', 'desc');
    }

    /**
     * Get the digital library files uploaded by the user.
     */
    public function archivosBiblioteca()
    {
        return $this->hasMany(BibliotecaArchivo::class, 'usuario_subida_id');
    }

    /**
     * Get the digital library files the user is authorized to access.
     */
    public function archivosBibliotecaAutorizados()
    {
        return $this->belongsToMany(BibliotecaArchivo::class, 'biblioteca_archivo_usuario', 'user_id', 'archivo_id')
            ->withTimestamps();
    }

    /**
     * Get the teacher profile for the user.
     */
    public function teacher()
    {
        return $this->hasOne(Teacher::class);
    }

    public function scopeForUser($query)
    {
        if (auth()->check() && !auth()->user()->hasRole('Super Administrador')) {
            if (auth()->user()->empresa_id) {
                $query->where('empresa_id', auth()->user()->empresa_id);
            }
            if (auth()->user()->sucursal_id) {
                $query->where('sucursal_id', auth()->user()->sucursal_id);
            }
        }
        return $query;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'empresa_id', 'sucursal_id', 'status', 'failed_login_attempts'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => static::getSpanishDescription($eventName));
    }
}