<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoNotaCredito extends Model
{
    protected $table = 'tipos_nota_credito';

    protected $fillable = ['codigo', 'descripcion', 'activo'];

    protected $casts = ['activo' => 'boolean'];

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }
}
