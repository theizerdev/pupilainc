<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RespuestaPreconsulta extends Model
{
    protected $fillable = [
        'paciente_id',
        'cita_id',
        'pregunta_id',
        'respuesta',
        'respuesta_multiple',
        'token_unico',
        'completado',
        'fecha_completado',
        'empresa_id',
        'sucursal_id',
    ];

    protected $casts = [
        'respuesta_multiple' => 'array',
        'completado' => 'boolean',
        'fecha_completado' => 'datetime',
    ];

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class);
    }

    public function cita(): BelongsTo
    {
        return $this->belongsTo(Cita::class);
    }

    public function pregunta(): BelongsTo
    {
        return $this->belongsTo(Pregunta::class);
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class);
    }
}
