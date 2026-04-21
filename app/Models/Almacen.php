<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Multitenantable;

class Almacen extends Model
{
    use HasFactory, Multitenantable;

    protected $table = 'almacenes';

    protected $fillable = [
        'nombre', 'descripcion', 'ubicacion',
        'es_principal', 'status', 'empresa_id', 'sucursal_id',
    ];

    protected $casts = [
        'es_principal' => 'boolean',
        'status'       => 'boolean',
    ];

    public function empresa()   { return $this->belongsTo(Empresa::class); }
    public function sucursal()  { return $this->belongsTo(Sucursal::class); }

    public function stocks()
    {
        return $this->hasMany(InventarioStock::class);
    }

    public function movimientos()
    {
        return $this->hasMany(InventarioMovimiento::class);
    }

    public function scopeForUser($query)
    {
        if (auth()->check() && !auth()->user()->hasRole('Super Administrador')) {
            $query->where('empresa_id', auth()->user()->empresa_id);
        }
        return $query;
    }

    public function scopeActivos($query)
    {
        return $query->where('status', true);
    }
}
