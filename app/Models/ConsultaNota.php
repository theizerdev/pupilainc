<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConsultaNota extends Model
{
    protected $table = 'consulta_notas';

    protected $fillable = [
        'consulta_id', 'cita_id', 'nota', 'tipo',
        'estado_consulta', 'created_by', 'empresa_id', 'sucursal_id',
    ];

    public function consulta()   { return $this->belongsTo(Consulta::class); }
    public function cita()       { return $this->belongsTo(Cita::class); }
    public function creadoPor()  { return $this->belongsTo(User::class, 'created_by'); }
}
