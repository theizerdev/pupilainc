<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Multitenantable;

class Marca extends Model
{
    use HasFactory, Multitenantable;

    protected $table = 'marcas';

    protected $fillable = [
        'nombre',
        'descripcion',
        'sitio_web',
        'status',
        'empresa_id',
        'sucursal_id',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    protected $attributes = [
        'status' => true,
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function scopeForUser($query)
    {
        if (auth()->check() && !auth()->user()->hasRole('Super Administrador')) {
            $query->where('empresa_id', auth()->user()->empresa_id);
        }
        return $query;
    }

    public function scopeActivas($query)
    {
        return $query->where('status', true);
    }
}
