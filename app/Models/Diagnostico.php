<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Multitenantable;

class Diagnostico extends Model
{
    use Multitenantable;

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'activo',
        'empresa_id',
        'sucursal_id',
        'created_by',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function consultas()
    {
        return $this->belongsToMany(Consulta::class, 'consulta_diagnostico')
            ->withPivot('tipo', 'orden')
            ->withTimestamps();
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopeBuscar($query, $termino)
    {
        return $query->where(function($q) use ($termino) {
            $q->where('codigo', 'like', "%{$termino}%")
              ->orWhere('nombre', 'like', "%{$termino}%");
        });
    }
}
