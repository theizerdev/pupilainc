<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Multitenantable;

class ConsultaTratamiento extends Model
{
    use Multitenantable;

    protected $table = 'consulta_tratamientos';

    protected $fillable = [
        'consulta_id',
        'medicamento',
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
