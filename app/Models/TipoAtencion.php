<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class TipoAtencion extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tipos_atencion';

    protected $fillable = [
        'nombre',
        'codigo',
        'color',
        'icono',
        'descripcion',
        'activo',
        'orden',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    /**
     * Scope para obtener solo tipos activos
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope para ordenar por campo orden
     */
    public function scopeOrdenados($query)
    {
        return $query->orderBy('orden')->orderBy('nombre');
    }

    /**
     * Obtener todas las citas con este tipo de atención
     */
    public function citas()
    {
        return $this->hasMany(Cita::class, 'tipo_atencion');
    }
}
