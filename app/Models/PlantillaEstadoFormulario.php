<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlantillaEstadoFormulario extends Model
{
    protected $table = 'plantilla_estado_formularios';

    protected $fillable = [
        'plantilla_id',
        'estado',
        'titulo',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function plantilla(): BelongsTo
    {
        return $this->belongsTo(EspecialidadPlantilla::class, 'plantilla_id');
    }

    public function secciones(): HasMany
    {
        return $this->hasMany(PlantillaSeccion::class, 'estado_formulario_id')
            ->where('activo', true)
            ->orderBy('orden');
    }

    public function todasLasSecciones(): HasMany
    {
        return $this->hasMany(PlantillaSeccion::class, 'estado_formulario_id')
            ->orderBy('orden');
    }
}
