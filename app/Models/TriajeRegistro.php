<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TriajeRegistro extends Model
{
    protected $fillable = [
        'consulta_id',
        'tipo_emergencia',
        'prioridad',
        'motivo_ingreso',
        'duracion_sintomas',
        'hemorragias_controladas',
        'heridas_abiertas',
        'fractura_sospechada',
        'observaciones',
        'empresa_id',
        'sucursal_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'hemorragias_controladas' => 'boolean',
        'heridas_abiertas' => 'boolean',
        'fractura_sospechada' => 'boolean',
    ];

    public function consulta(): BelongsTo
    {
        return $this->belongsTo(Consulta::class);
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
}
