<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Multitenantable;

class TipoVariante extends Model
{
    use HasFactory, Multitenantable;

    protected $table = 'tipo_variantes';

    protected $fillable = [
        'nombre', 'slug', 'icono', 'status', 'orden',
        'empresa_id', 'sucursal_id',
    ];

    protected $casts = [
        'status' => 'boolean',
        'orden'  => 'integer',
    ];

    public function valores()
    {
        return $this->hasMany(ValorVariante::class)->orderBy('orden')->where('status', true);
    }

    public function scopeActivos($query) { return $query->where('status', true); }

    public function scopeForUser($query)
    {
        if (auth()->check() && !auth()->user()->hasRole('Super Administrador')) {
            $query->where('empresa_id', auth()->user()->empresa_id);
        }
        return $query;
    }
}
