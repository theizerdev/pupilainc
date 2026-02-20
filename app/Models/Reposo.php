<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Multitenantable;

class Reposo extends Model
{
    use Multitenantable;

    protected $fillable = [
        'consulta_id',
        'paciente_id',
        'medico_id',
        'motivo',
        'dias_reposo',
        'fecha_inicio',
        'fecha_fin',
        'observaciones',
        'empresa_id',
        'sucursal_id',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
    ];

    public function consulta()
    {
        return $this->belongsTo(Consulta::class);
    }

    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    public function medico()
    {
        return $this->belongsTo(Medico::class);
    }
}
