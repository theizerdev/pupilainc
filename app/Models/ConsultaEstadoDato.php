<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\Multitenantable;

class ConsultaEstadoDato extends Model
{
    use Multitenantable;

    protected $table = 'consulta_estado_datos';

    protected $fillable = [
        'consulta_id',
        'estado',
        'datos',
        'empresa_id',
        'sucursal_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'datos' => 'array',
    ];

    public function consulta(): BelongsTo
    {
        return $this->belongsTo(Consulta::class);
    }
}
