<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;



class ConsultaGota extends Model
{
    use HasFactory;

    protected $fillable = [
        'consulta_id',
        'user_id',
        'tipo_gota',
        'gotas_od',
        'gotas_oi',
        'observaciones',
        'tiempo_espera',
        'estado',
        'notificado',
    ];

    public function consulta()
    {
        return $this->belongsTo(Consulta::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
