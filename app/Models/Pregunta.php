<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pregunta extends Model
{
    protected $fillable = [
        'cuestionario_id',
        'titulo',
        'descripcion',
        'tipo',
        'opciones',
        'obligatorio',
        'orden',
        'activo',
    ];

    protected $casts = [
        'opciones' => 'array',
        'obligatorio' => 'boolean',
        'activo' => 'boolean',
        'orden' => 'integer',
    ];

    public function cuestionario(): BelongsTo
    {
        return $this->belongsTo(Cuestionario::class);
    }
}
