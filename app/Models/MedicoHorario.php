<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicoHorario extends Model
{
    use HasFactory;

    protected $table = 'medico_horarios';

    protected $fillable = [
        'medico_id',
        'dia_semana',
        'hora_inicio',
        'hora_fin',
        'duracion_cita',
        'activo',
        'fecha_inicio',
        'fecha_fin'
    ];

    protected $casts = [
        'activo' => 'boolean',
        'hora_inicio' => 'datetime:H:i',
        'hora_fin' => 'datetime:H:i',
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
    ];

    // Relaciones
    public function medico()
    {
        return $this->belongsTo(Medico::class);
    }

    // Accessores
    public function getNombreDiaAttribute()
    {
        $dias = [
            1 => 'Lunes',
            2 => 'Martes',
            3 => 'Miércoles',
            4 => 'Jueves',
            5 => 'Viernes',
            6 => 'Sábado',
            7 => 'Domingo'
        ];

        return $dias[$this->dia_semana] ?? 'Desconocido';
    }

    public function getRangoHorarioAttribute()
    {
        return $this->hora_inicio->format('H:i') . ' - ' . $this->hora_fin->format('H:i');
    }

    // Scopes
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopePorDia($query, $diaSemana)
    {
        return $query->where('dia_semana', $diaSemana);
    }

    public function scopeVigentes($query)
    {
        $hoy = now();
        return $query->where(function ($q) use ($hoy) {
            $q->whereNull('fecha_inicio')
              ->orWhere('fecha_inicio', '<=', $hoy);
        })->where(function ($q) use ($hoy) {
            $q->whereNull('fecha_fin')
              ->orWhere('fecha_fin', '>=', $hoy);
        });
    }
}