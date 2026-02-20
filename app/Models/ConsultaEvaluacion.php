<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Multitenantable;

class ConsultaEvaluacion extends Model
{
    use Multitenantable;

    protected $table = 'consulta_evaluaciones';

    protected $fillable = [
        'consulta_id',
        'enfermedad_actual',
        'examen_fisico',
        'conclusion',
        'observaciones_adicionales',
        'empresa_id',
        'sucursal_id',
        'created_by',
        'updated_by',
    ];

    public function consulta()
    {
        return $this->belongsTo(Consulta::class);
    }
}
