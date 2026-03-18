<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Multitenantable;

class Categoria extends Model
{
    use HasFactory, Multitenantable;

    protected $fillable = [
        'nombre',
        'descripcion',
        'color',
        'icono',
        'activo',
        'orden',
        'empresa_id',
        'sucursal_id'
    ];

    protected $casts = [
        'activo' => 'boolean',
        'orden' => 'integer',
    ];

    // Relaciones
    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    // Scopes
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopeOrdenados($query)
    {
        return $query->orderBy('orden')->orderBy('nombre');
    }

    public function scopeForUser($query, $user = null)
    {
        $user = $user ?? auth()->user();
        
        if (!$user) {
            return $query;
        }

        if ($user->hasRole('Super Administrador')) {
            return $query;
        }

        return $query->where('empresa_id', $user->empresa_id)
                    ->where('sucursal_id', $user->sucursal_id);
    }
}
