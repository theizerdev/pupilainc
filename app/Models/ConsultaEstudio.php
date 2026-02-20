<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Multitenantable;

class ConsultaEstudio extends Model
{
    use Multitenantable;

    protected $table = 'consulta_estudios';

    const TIPO_IMAGEN = 'imagen';
    const TIPO_LABORATORIO = 'laboratorio';
    const TIPO_OTROS = 'otros';

    protected $fillable = [
        'consulta_id',
        'tipo_estudio',
        'nombre_estudio',
        'indicaciones',
        'orden',
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
