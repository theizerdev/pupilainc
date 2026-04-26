<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cuestionario extends Model
{
    protected $fillable = [
        'titulo',
        'descripcion',
        'tipo',
        'activo',
        'empresa_id',
        'especialidad_id',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function especialidad(): BelongsTo
    {
        return $this->belongsTo(Especialidad::class);
    }

    public function preguntas(): HasMany
    {
        return $this->hasMany(Pregunta::class)->where('activo', true)->orderBy('orden');
    }

    public function respuestas(): HasMany
    {
        return $this->hasMany(RespuestaPreconsulta::class);
    }
}
