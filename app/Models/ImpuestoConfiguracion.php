<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Multitenantable;

class ImpuestoConfiguracion extends Model
{
    use Multitenantable;

    protected $table = 'impuestos_configuracion';

    protected $fillable = [
        'empresa_id',
        'nombre',
        'codigo',
        'tipo',
        'porcentaje',
        'monto_fijo',
        'aplica_servicios',
        'aplica_productos',
        'metodos_pago_aplicables',
        'coletilla_fiscal',
        'activo',
        'orden'
    ];

    protected $casts = [
        'porcentaje' => 'decimal:2',
        'monto_fijo' => 'decimal:2',
        'aplica_servicios' => 'boolean',
        'aplica_productos' => 'boolean',
        'metodos_pago_aplicables' => 'array',
        'activo' => 'boolean'
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopePorCodigo($query, $codigo)
    {
        return $query->where('codigo', $codigo);
    }
}
