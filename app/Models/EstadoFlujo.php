<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\Multitenantable;

class EstadoFlujo extends Model
{
    use Multitenantable;

    protected $table = 'estados_flujo';

    protected $fillable = ['codigo', 'nombre', 'color', 'activo', 'empresa_id', 'sucursal_id'];

    protected $casts = ['activo' => 'boolean'];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }
}
