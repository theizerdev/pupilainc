<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsientoContable extends Model
{
    protected $table = 'asientos_contables';

    protected $fillable = [
        'numero', 'fecha', 'tipo', 'descripcion', 'estado',
        'referencia_tipo', 'referencia_id', 'user_id',
        'empresa_id', 'sucursal_id'
    ];

    protected $casts = [
        'fecha' => 'date'
    ];

    public function detalles()
    {
        return $this->hasMany(AsientoDetalle::class, 'asiento_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function scopeAprobados($query)
    {
        return $query->where('estado', 'aprobado');
    }

    public function scopePorFecha($query, $desde, $hasta)
    {
        return $query->whereBetween('fecha', [$desde, $hasta]);
    }

    public function getTotalDebeAttribute()
    {
        return $this->detalles->sum('debe');
    }

    public function getTotalHaberAttribute()
    {
        return $this->detalles->sum('haber');
    }

    public function getEstaBalanceadoAttribute()
    {
        return round($this->total_debe, 2) === round($this->total_haber, 2);
    }

    public static function generarNumero($empresaId)
    {
        $ultimo = self::where('empresa_id', $empresaId)
            ->whereYear('fecha', now()->year)
            ->orderBy('numero', 'desc')
            ->first();

        if (!$ultimo) {
            return now()->year . '-0001';
        }

        $partes = explode('-', $ultimo->numero);
        $siguiente = intval($partes[1] ?? 0) + 1;

        return now()->year . '-' . str_pad($siguiente, 4, '0', STR_PAD_LEFT);
    }
}
