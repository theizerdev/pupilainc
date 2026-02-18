<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Multitenantable;

class EnfermeroHorario extends Model
{
    use HasFactory, Multitenantable;

    protected $table = 'enfermero_horarios';

    protected $fillable = [
        'enfermero_id',
        'dia_semana',
        'hora_inicio',
        'hora_fin',
        'duracion_cita',
        'activo',
        'empresa_id',
        'sucursal_id'
    ];

    protected $casts = [
        'activo' => 'boolean',
        'duracion_cita' => 'integer',
    ];

    // Relaciones
    public function enfermero()
    {
        return $this->belongsTo(Enfermero::class);
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }
}