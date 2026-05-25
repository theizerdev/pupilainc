<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsientoDetalle extends Model
{
    protected $table = 'asientos_detalles';

    protected $fillable = [
        'asiento_id', 'cuenta_id', 'debe', 'haber', 'descripcion', 'conciliado'
    ];

    protected $casts = [
        'debe' => 'decimal:2',
        'haber' => 'decimal:2',
        'conciliado' => 'boolean'
    ];

    public function asiento()
    {
        return $this->belongsTo(AsientoContable::class, 'asiento_id');
    }

    public function cuenta()
    {
        return $this->belongsTo(CuentaContable::class, 'cuenta_id');
    }

    public function movimientoBancario()
    {
        return $this->hasOne(MovimientoBancario::class);
    }
}
