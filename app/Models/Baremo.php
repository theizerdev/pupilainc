<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Multitenantable;

class Baremo extends Model
{
    use Multitenantable;

    protected $fillable = [
        'empresa_id',
        'sucursal_id',
        'especialidad_id',
        'categoria_id',
        'codigo',
        'nombre_servicio',
        'descripcion',
        'costo_usd',
        'costo_bs',
        'aplica_iva',
        'exento_iva',
        'duracion_minutos',
        'activo'
    ];

    protected $casts = [
        'costo_usd' => 'decimal:2',
        'costo_bs' => 'decimal:2',
        'aplica_iva' => 'boolean',
        'exento_iva' => 'boolean',
        'activo' => 'boolean'
    ];

    protected static function booted()
    {
        static::saving(function ($baremo) {
            // Calcular costo en Bs automáticamente con tasa del día
            if ($baremo->costo_usd) {
                $tasa = ExchangeRate::getLatestRate('USD') ?? 1;
                $baremo->costo_bs = $baremo->costo_usd * $tasa;
            }
        });
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function especialidad()
    {
        return $this->belongsTo(Especialidad::class);
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopePorEspecialidad($query, $especialidadId)
    {
        return $query->where('especialidad_id', $especialidadId);
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

    public function getCostoConIvaAttribute()
    {
        if (!$this->aplica_iva || $this->exento_iva) {
            return $this->costo_usd;
        }

        $ivaConfig = ImpuestoConfiguracion::where('empresa_id', $this->empresa_id)
            ->where('codigo', 'IVA')
            ->where('activo', true)
            ->first();

        if ($ivaConfig) {
            return $this->costo_usd * (1 + ($ivaConfig->porcentaje / 100));
        }

        return $this->costo_usd;
    }
}
