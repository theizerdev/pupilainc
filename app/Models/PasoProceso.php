<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\Multitenantable;

class PasoProceso extends Model
{
    use Multitenantable;

    protected $table = 'pasos_proceso';

    protected $fillable = ['codigo', 'nombre', 'icono', 'activo', 'empresa_id', 'sucursal_id'];

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
