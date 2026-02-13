<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Multitenantable;

class Consulta extends Model
{
    use HasFactory, SoftDeletes, Multitenantable;

    protected $table = 'consultas';

    const ESTADO_BORRADOR = 'borrador';
    const ESTADO_FINALIZADA = 'finalizada';
    const ESTADO_SALA_ESPERA = 'sala_espera';
    const ESTADO_EN_CONSULTORIO = 'en_consultorio';
    const ESTADO_EN_CONSULTORIO_OPTOMETRISTA = 'en_consultorio_optometrista';
    const ESTADO_EN_GOTAS = 'en_gotas';
    const ESTADO_EN_OPTICA = 'en_optica';
    const ESTADO_EN_ESTUDIO = 'en_estudio';

    protected $fillable = [
        'codigo',
        'cita_id',
        'paciente_id',
        'medico_id',
        'especialidad_id',
        'fecha_consulta',
        'preconsulta',
        'motivo_consulta',
        'enfermedad_actual',
        'antecedentes',
        'agudeza_visual',
        'refraccion',
        'biomicroscopia',
        'pio',
        'dilatacion_pupilar',
        'fondo_ojo',
        'diagnosticos',
        'plan_tratamiento',
        'medicamentos',
        'observaciones',
        'estado',
        'empresa_id',
        'sucursal_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'fecha_consulta' => 'datetime',
        'preconsulta' => 'boolean',
        'antecedentes' => 'array',
        'agudeza_visual' => 'array',
        'refraccion' => 'array',
        'biomicroscopia' => 'array',
        'pio' => 'array',
        'dilatacion_pupilar' => 'array',
        'fondo_ojo' => 'array',
        'diagnosticos' => 'array',
        'medicamentos' => 'array',
    ];

    protected static function booted()
    {
        static::creating(function (self $consulta) {
            if (empty($consulta->codigo)) {
                $candidate = null;
                for ($i = 0; $i < 5; $i++) {
                    $n = (string) random_int(10000000, 99999999);
                    if (!self::where('codigo', $n)->exists()) {
                        $candidate = $n;
                        break;
                    }
                }
                $consulta->codigo = $candidate ?? (string) random_int(10000000, 99999999);
            }
        });
    }

    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    public function medico()
    {
        return $this->belongsTo(Medico::class);
    }

    public function cita()
    {
        return $this->belongsTo(Cita::class);
    }

    public function especialidad()
    {
        return $this->belongsTo(Especialidad::class);
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function scopePorEstado($query, $estado)
    {
        return $query->where('estado', $estado);
    }

    public function scopePorMedico($query, $medicoId)
    {
        return $query->where('medico_id', $medicoId);
    }

    public function cambiarEstado($nuevoEstado)
    {
        $this->update(['estado' => $nuevoEstado]);
        return $this;
    }
}
