<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SignosVitales extends Model
{
   

    protected $fillable = [
        'consulta_id',
        'enfermero_id',
        'paciente_id',
        'presion_arterial_sistolica',
        'presion_arterial_diastolica',
        'frecuencia_cardiaca',
        'frecuencia_respiratoria',
        'temperatura',
        'peso',
        'talla',
        'imc',
        'saturacion_oxigeno',
        'observaciones',
        'empresa_id',
        'sucursal_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'presion_arterial_sistolica' => 'decimal:1',
        'presion_arterial_diastolica' => 'decimal:1',
        'temperatura' => 'decimal:1',
        'peso' => 'decimal:2',
        'talla' => 'decimal:1',
        'imc' => 'decimal:1',
        'saturacion_oxigeno' => 'decimal:1',
    ];

    public function consulta(): BelongsTo
    {
        return $this->belongsTo(Consulta::class);
    }

    public function enfermero(): BelongsTo
    {
        return $this->belongsTo(Enfermero::class);
    }

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class);
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function creadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function actualizadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function getPresionArterialAttribute(): string
    {
        return "{$this->presion_arterial_sistolica}/{$this->presion_arterial_diastolica}";
    }

    public function getImcCompletoAttribute(): string
    {
        if (!$this->imc) {
            return 'No calculado';
        }

        $clasificacion = match(true) {
            $this->imc < 18.5 => 'Bajo peso',
            $this->imc < 25 => 'Normal',
            $this->imc < 30 => 'Sobrepeso',
            default => 'Obesidad'
        };

        return "{$this->imc} kg/m² ({$clasificacion})";
    }
}