<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Multitenantable;

class ValorVariante extends Model
{
    use HasFactory, Multitenantable;

    protected $table = 'valor_variantes';

    protected $fillable = [
        'tipo_variante_id', 'valor', 'codigo', 'color_hex', 'status', 'orden',
        'empresa_id', 'sucursal_id',
    ];

    protected $casts = [
        'status' => 'boolean',
        'orden'  => 'integer',
    ];

    public function tipoVariante()
    {
        return $this->belongsTo(TipoVariante::class);
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
