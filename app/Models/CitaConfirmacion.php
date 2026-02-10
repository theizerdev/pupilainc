<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Traits\Multitenantable;

class CitaConfirmacion extends Model
{
    use HasFactory, LogsActivity, Multitenantable;

    protected $table = 'cita_confirmaciones';

    const METODO_WHATSAPP = 'whatsapp';
    const METODO_EMAIL = 'email';
    const METODO_SMS = 'sms';

    const ESTADO_PENDIENTE = 'pendiente';
    const ESTADO_CONFIRMADO = 'confirmado';
    const ESTADO_RECHAZADO = 'rechazado';
    const ESTADO_SIN_RESPUESTA = 'sin_respuesta';
    const ESTADO_EXPIRADO = 'expirado';

    const ESTADOS = [
        self::ESTADO_PENDIENTE,
        self::ESTADO_CONFIRMADO,
        self::ESTADO_RECHAZADO,
        self::ESTADO_SIN_RESPUESTA,
        self::ESTADO_EXPIRADO,
    ];

    protected $fillable = [
        'cita_id',
        'metodo',
        'destinatario',
        'mensaje_enviado',
        'token_confirmacion',
        'estado',
        'respuesta_recibida',
        'fecha_envio',
        'fecha_respuesta',
        'intentos',
        'max_intentos',
        'empresa_id',
        'sucursal_id',
        'created_by',
        'fecha_envio',
    ];

    protected $casts = [
        'fecha_envio' => 'datetime',
        'fecha_respuesta' => 'datetime',
        'intentos' => 'integer',
        'max_intentos' => 'integer',
    ];

    protected $attributes = [
        'estado' => self::ESTADO_PENDIENTE,
        'intentos' => 0,
        'max_intentos' => 3,
    ];

    // Relationships
    public function cita(): BelongsTo
    {
        return $this->belongsTo(Cita::class);
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopePendientes($query)
    {
        return $query->where('estado', self::ESTADO_PENDIENTE);
    }

    public function scopeExpiradas($query)
    {
        return $query->where('estado', self::ESTADO_PENDIENTE)
                    ->where('fecha_envio', '<', now()->subHours(24));
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

    // Methods
    public function estaExpirada(): bool
    {
        return $this->fecha_envio && $this->fecha_envio->addHours(24)->isPast();
    }

    public function puedeReintentar(): bool
    {
        return $this->estado === self::ESTADO_PENDIENTE &&
               $this->intentos < $this->max_intentos &&
               !$this->estaExpirada();
    }

    public function incrementarIntento(): void
    {
        $this->increment('intentos');
    }

    public function marcarComoConfirmada(string $respuesta = null): void
    {
        $this->update([
            'estado' => self::ESTADO_CONFIRMADO,
            'respuesta_recibida' => $respuesta,
            'fecha_respuesta' => now(),
        ]);
    }

    public function marcarComoRechazada(string $respuesta = null): void
    {
        $this->update([
            'estado' => self::ESTADO_RECHAZADO,
            'respuesta_recibida' => $respuesta,
            'fecha_respuesta' => now(),
        ]);
    }

    public function marcarComoExpirada(): void
    {
        $this->update(['estado' => self::ESTADO_EXPIRADO]);
    }

    public function getTiempoRespuestaAttribute(): ?int
    {
        if (!$this->fecha_envio || !$this->fecha_respuesta) {
            return null;
        }

        return $this->fecha_envio->diffInMinutes($this->fecha_respuesta);
    }

    public function getEstadoLabelAttribute(): string
    {
        return match($this->estado) {
            self::ESTADO_PENDIENTE => 'Pendiente',
            self::ESTADO_CONFIRMADO => 'Confirmada',
            self::ESTADO_RECHAZADO => 'Rechazada',
            self::ESTADO_SIN_RESPUESTA => 'Sin Respuesta',
            self::ESTADO_EXPIRADO => 'Expirada',
            default => $this->estado,
        };
    }

    public function getEstadoColorAttribute(): string
    {
        return match($this->estado) {
            self::ESTADO_PENDIENTE => 'warning',
            self::ESTADO_CONFIRMADO => 'success',
            self::ESTADO_RECHAZADO => 'danger',
            self::ESTADO_SIN_RESPUESTA => 'secondary',
            self::ESTADO_EXPIRADO => 'dark',
            default => 'secondary',
        };
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['cita_id', 'metodo', 'destinatario', 'estado', 'respuesta_recibida', 'intentos'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // Boot method for automatic expiration
    protected static function booted()
    {
        static::creating(function ($confirmacion) {
            if (!$confirmacion->token_confirmacion) {
                $confirmacion->token_confirmacion = Str::random(32);
            }
        });
    }
}
