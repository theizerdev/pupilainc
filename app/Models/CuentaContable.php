<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CuentaContable extends Model
{
    protected $table = 'cuentas_contables';

    protected $fillable = [
        'codigo', 'nombre', 'tipo', 'naturaleza', 'nivel', 
        'cuenta_padre_id', 'acepta_movimientos', 'descripcion', 
        'activo', 'empresa_id', 'sucursal_id'
    ];

    protected $casts = [
        'acepta_movimientos' => 'boolean',
        'activo' => 'boolean',
        'nivel' => 'integer'
    ];

    public function cuentaPadre()
    {
        return $this->belongsTo(CuentaContable::class, 'cuenta_padre_id');
    }

    public function subcuentas()
    {
        return $this->hasMany(CuentaContable::class, 'cuenta_padre_id');
    }

    public function asientosDetalles()
    {
        return $this->hasMany(AsientoDetalle::class, 'cuenta_id');
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }

    public function scopeConMovimientos($query)
    {
        return $query->where('acepta_movimientos', true);
    }

    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    public function getCodigoCompletoAttribute()
    {
        return $this->codigo . ' - ' . $this->nombre;
    }

    public function getSaldoAttribute()
    {
        $debe = $this->asientosDetalles()
            ->whereHas('asiento', fn($q) => $q->where('estado', 'aprobado'))
            ->sum('debe');
        
        $haber = $this->asientosDetalles()
            ->whereHas('asiento', fn($q) => $q->where('estado', 'aprobado'))
            ->sum('haber');

        return $this->naturaleza === 'deudora' ? ($debe - $haber) : ($haber - $debe);
    }
}
