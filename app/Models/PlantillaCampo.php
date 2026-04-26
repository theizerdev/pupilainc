<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlantillaCampo extends Model
{
    protected $table = 'plantilla_campos';

    protected $fillable = [
        'seccion_id',
        'nombre_campo',
        'etiqueta',
        'descripcion',
        'tipo',
        'opciones',
        'obligatorio',
        'valor_defecto',
        'placeholder',
        'unidad',
        'min',
        'max',
        'orden',
        'activo',
        'ancho_columnas',
    ];

    protected $casts = [
        'opciones'       => 'array',
        'obligatorio'    => 'boolean',
        'activo'         => 'boolean',
        'orden'          => 'integer',
        'ancho_columnas' => 'integer',
        'min'            => 'decimal:2',
        'max'            => 'decimal:2',
    ];

    const TIPOS = [
        'text'     => 'Texto corto',
        'textarea' => 'Texto largo',
        'number'   => 'Número',
        'select'   => 'Lista desplegable',
        'radio'    => 'Opción única',
        'checkbox' => 'Opción múltiple',
        'range'    => 'Rango / Escala',
        'date'     => 'Fecha',
    ];

    public function seccion(): BelongsTo
    {
        return $this->belongsTo(PlantillaSeccion::class, 'seccion_id');
    }

    // Genera la regla de validación Laravel para este campo
    public function getReglaValidacion(): string
    {
        $reglas = [$this->obligatorio ? 'required' : 'nullable'];

        match ($this->tipo) {
            'number', 'range' => array_push($reglas, 'numeric',
                ...($this->min !== null ? ["min:{$this->min}"] : []),
                ...($this->max !== null ? ["max:{$this->max}"] : [])
            ),
            'date'     => array_push($reglas, 'date'),
            'select',
            'radio'    => array_push($reglas, 'string'),
            'checkbox' => array_push($reglas, 'array'),
            default    => array_push($reglas, 'string', 'max:1000'),
        };

        return implode('|', $reglas);
    }
}
