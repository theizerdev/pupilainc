<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlantillaSeccion extends Model
{
    protected $table = 'plantilla_secciones';

    protected $fillable = [
        'plantilla_id',
        'estado_formulario_id',
        'nombre',
        'icono',
        'color',
        'orden',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'orden'  => 'integer',
    ];

    public function plantilla(): BelongsTo
    {
        return $this->belongsTo(EspecialidadPlantilla::class, 'plantilla_id');
    }

    public function estadoFormulario(): BelongsTo
    {
        return $this->belongsTo(PlantillaEstadoFormulario::class, 'estado_formulario_id');
    }

    public function campos(): HasMany
    {
        return $this->hasMany(PlantillaCampo::class, 'seccion_id')
            ->where('activo', true)
            ->orderBy('orden');
    }

    public function todosLosCampos(): HasMany
    {
        return $this->hasMany(PlantillaCampo::class, 'seccion_id')->orderBy('orden');
    }
}
