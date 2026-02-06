<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CitaRecordatorio extends Model
{
    use HasFactory;

    protected $table = 'cita_recordatorios';

    const TIPOS = ['24h', '2h', 'personalizado'];
    const ESTADOS = ['pendiente', 'enviado', 'fallido', 'cancelado'];
    const CANALES = ['whatsapp', 'email', 'sms'];

    protected $fillable = [
        'cita_id',
        'tipo',
        'fecha_envio_programado',
        'fecha_envio_real',
        'estado',
        'canal',
        'mensaje',
        'error_mensaje',
        'intentos',
        'confirmacion_respuesta',
        'fecha_respuesta'
    ];

    protected $casts = [
        'fecha_envio_programado' => 'datetime',
        'fecha_envio_real' => 'datetime',
        'fecha_respuesta' => 'datetime',
        'confirmacion_respuesta' => 'boolean',
    ];

    public function cita(): BelongsTo
    {
        return $this->belongsTo(Cita::class);
    }

    public function scopePendientes($query)
    {
        return $query->where('estado', 'pendiente')
                    ->where('fecha_envio_programado', '<=', now());
    }

    public function scopePorEnviar($query)
    {
        return $query->where('estado', 'pendiente')
                    ->where('fecha_envio_programado', '<=', now());
    }

    public function marcarComoEnviado(): void
    {
        $this->update([
            'estado' => 'enviado',
            'fecha_envio_real' => now()
        ]);
    }

    public function marcarComoFallido(string $error): void
    {
        $this->update([
            'estado' => 'fallido',
            'fecha_envio_real' => now(),
            'error_mensaje' => $error
        ]);
    }

    public function scopeCancelados($query)
    {
        return $query->where('estado', 'cancelado');
    }

    public function marcarComoCancelado(string $motivo = 'Cancelado manualmente'): void
    {
        $this->update([
            'estado' => 'cancelado',
            'error_mensaje' => $motivo,
        ]);
    }

    public function registrarConfirmacion(bool $confirmacion): void
    {
        $this->update([
            'confirmacion_respuesta' => $confirmacion,
            'fecha_respuesta' => now()
        ]);
    }
}