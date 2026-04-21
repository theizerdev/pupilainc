<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Multitenantable;

class Proveedor extends Model
{
    use HasFactory, Multitenantable;

    protected $table = 'proveedores';

    protected $fillable = [
        'nombre', 'documento', 'contacto', 'telefono',
        'email', 'direccion', 'latitud', 'longitud', 'condiciones_pago',
        'status', 'empresa_id', 'sucursal_id',
    ];

    protected $casts = [
        'status'   => 'boolean',
        'latitud'  => 'decimal:7',
        'longitud' => 'decimal:7',
    ];

    public function empresa()  { return $this->belongsTo(Empresa::class); }
    public function sucursal() { return $this->belongsTo(Sucursal::class); }

    public function productos()
    {
        return $this->hasMany(Producto::class);
    }

    public function ordenesCompra()
    {
        return $this->hasMany(OrdenCompra::class);
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
