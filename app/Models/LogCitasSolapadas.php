<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogCitasSolapadas extends Model
{
    protected $table = 'log_citas_solapadas';

    public $timestamps = false;

    protected $fillable = [
        'usuario_id',
        'cita_original_id',
        'cita_nueva_id',
        'tipo_prioridad',
        'observacion',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function citaOriginal(): BelongsTo
    {
        return $this->belongsTo(Cita::class, 'cita_original_id');
    }

    public function citaNueva(): BelongsTo
    {
        return $this->belongsTo(Cita::class, 'cita_nueva_id');
    }
}