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
    const ESTADO_EN_ENFERMERIA = 'en_enfermeria';
    const ESTADO_EN_CONSULTORIO = 'en_consultorio';
    const ESTADO_EN_CONSULTORIO_OPTOMETRISTA = 'en_consultorio_optometrista';
    const ESTADO_EN_GOTAS = 'en_gotas';
    const ESTADO_EN_OPTICA = 'en_optica';
    const ESTADO_EN_ESTUDIO = 'en_estudio';

    const ESTADOS = [
        self::ESTADO_SALA_ESPERA,
        self::ESTADO_EN_ENFERMERIA,
        self::ESTADO_EN_CONSULTORIO,
        self::ESTADO_EN_CONSULTORIO_OPTOMETRISTA,
        self::ESTADO_EN_GOTAS,
        self::ESTADO_EN_OPTICA,
        self::ESTADO_EN_ESTUDIO,
        self::ESTADO_FINALIZADA,
        self::ESTADO_BORRADOR,
    ];

    const ESTADO_LABELS = [
        self::ESTADO_SALA_ESPERA => 'Sala de Espera',
        self::ESTADO_EN_ENFERMERIA => 'En Enfermería',
        self::ESTADO_EN_CONSULTORIO => 'En Consultorio',
        self::ESTADO_EN_CONSULTORIO_OPTOMETRISTA => 'En Consultorio Optometrista',
        self::ESTADO_EN_GOTAS => 'En Gotas',
        self::ESTADO_EN_OPTICA => 'En Óptica',
        self::ESTADO_EN_ESTUDIO => 'En Estudio',
        self::ESTADO_FINALIZADA => 'Finalizada',
        self::ESTADO_BORRADOR => 'Borrador',
    ];

    const ESTADO_COLORES = [
        self::ESTADO_SALA_ESPERA => '#FFA726',
        self::ESTADO_EN_ENFERMERIA => '#EF5350',
        self::ESTADO_EN_CONSULTORIO => '#42A5F5',
        self::ESTADO_EN_CONSULTORIO_OPTOMETRISTA => '#7E57C2',
        self::ESTADO_EN_GOTAS => '#26C6DA',
        self::ESTADO_EN_OPTICA => '#AB47BC',
        self::ESTADO_EN_ESTUDIO => '#EC407A',
        self::ESTADO_FINALIZADA => '#66BB6A',
        self::ESTADO_BORRADOR => '#BDBDBD',
    ];

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
        'estado_changed_at',
        'empresa_id',
        'sucursal_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'fecha_consulta' => 'datetime',
        'estado_changed_at' => 'datetime',
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

    public function signosVitales()
    {
        return $this->hasMany(SignosVitales::class);
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
        $this->update([
            'estado' => $nuevoEstado,
            'estado_changed_at' => now(),
        ]);
        return $this;
    }
}