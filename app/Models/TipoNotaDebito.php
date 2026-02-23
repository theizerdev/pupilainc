<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoNotaDebito extends Model
{
    protected $table = 'tipos_nota_debito';

    protected $fillable = ['codigo', 'descripcion', 'activo'];

    protected $casts = ['activo' => 'boolean'];

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }
}
