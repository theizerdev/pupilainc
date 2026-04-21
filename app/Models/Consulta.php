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
    const ESTADO_POR_LLEGAR = 'por_llegar';
    const ESTADO_FINALIZADA = 'finalizada';
    const ESTADO_PAGADA = 'pagada';
    const ESTADO_SALA_ESPERA = 'sala_espera';
    const ESTADO_EN_ENFERMERIA = 'en_enfermeria';
    const ESTADO_EN_CONSULTORIO = 'en_consultorio';
    const ESTADO_EN_CONSULTORIO_OPTOMETRISTA = 'en_consultorio_optometrista';
    const ESTADO_EN_GOTAS = 'en_gotas';
    const ESTADO_DILATADO = 'dilatado';
    const ESTADO_EN_OPTICA = 'en_optica';
    const ESTADO_EN_ESTUDIO = 'en_estudio';

    const ESTADOS = [
        self::ESTADO_POR_LLEGAR,
        self::ESTADO_SALA_ESPERA,
        self::ESTADO_EN_ENFERMERIA,
        self::ESTADO_EN_CONSULTORIO,
        self::ESTADO_EN_CONSULTORIO_OPTOMETRISTA,
        self::ESTADO_EN_GOTAS,
        self::ESTADO_DILATADO,
        self::ESTADO_EN_OPTICA,
        self::ESTADO_EN_ESTUDIO,
        self::ESTADO_FINALIZADA,
        self::ESTADO_PAGADA,
        self::ESTADO_BORRADOR,
    ];

    const ESTADO_LABELS = [
        self::ESTADO_POR_LLEGAR => 'Por llegar',
        self::ESTADO_SALA_ESPERA => 'Sala de Espera',
        self::ESTADO_EN_ENFERMERIA => 'En Enfermería',
        self::ESTADO_EN_CONSULTORIO => 'En Consultorio',
        self::ESTADO_EN_CONSULTORIO_OPTOMETRISTA => 'En Consultorio Optometrista',
        self::ESTADO_EN_GOTAS => 'En Gotas',
        self::ESTADO_DILATADO => 'Dilatado',
        self::ESTADO_EN_OPTICA => 'En Óptica',
        self::ESTADO_EN_ESTUDIO => 'En Estudio',
        self::ESTADO_FINALIZADA => 'Finalizada',
        self::ESTADO_PAGADA => 'Pagada',
        self::ESTADO_BORRADOR => 'Borrador',
    ];

    const ESTADO_COLORES = [
        self::ESTADO_POR_LLEGAR => '#9E9E9E',
        self::ESTADO_SALA_ESPERA => '#FFA726',
        self::ESTADO_EN_ENFERMERIA => '#EF5350',
        self::ESTADO_EN_CONSULTORIO => '#42A5F5',
        self::ESTADO_EN_CONSULTORIO_OPTOMETRISTA => '#7E57C2',
        self::ESTADO_EN_GOTAS => '#26C6DA',
        self::ESTADO_DILATADO => '#00BCD4',
        self::ESTADO_EN_OPTICA => '#AB47BC',
        self::ESTADO_EN_ESTUDIO => '#EC407A',
        self::ESTADO_FINALIZADA => '#66BB6A',
        self::ESTADO_PAGADA => '#4CAF50',
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

    /**
     * Calcula el tiempo transcurrido en sala de espera
     */
    public function getTiempoSalaEsperaAttribute()
    {
        if ($this->estado === self::ESTADO_SALA_ESPERA) {
            // Usar estado_changed_at si está disponible, de lo contrario usar updated_at
            $fechaReferencia = $this->estado_changed_at ?? $this->updated_at;
            if ($fechaReferencia) {
                $minutos = \Carbon\Carbon::now()->diffInMinutes($fechaReferencia);
                // Evitar valores negativos - si es negativo, significa que la fecha es futura
                return max(0, $minutos);
            }
        }
        
        return null;
    }

    /**
     * Calcula el tiempo transcurrido en estado Gotas
     */
    public function getTiempoEnGotasAttribute()
    {
        if ($this->estado === self::ESTADO_EN_GOTAS || $this->estado === self::ESTADO_DILATADO) {
            $fechaReferencia = $this->estado_changed_at ?? $this->updated_at;
            if ($fechaReferencia) {
                $minutos = \Carbon\Carbon::now()->diffInMinutes($fechaReferencia);
                return max(0, $minutos);
            }
        }
        
        return null;
    }

    /**
     * Formatea el tiempo de espera para display
     */
    public function getTiempoEsperaFormateadoAttribute()
    {
        $minutos = $this->tiempo_sala_espera;
        
        if (!$minutos) {
            return 'No en sala de espera';
        }
        
        // Si es menos de 1 minuto, mostrar "Ahora"
        if ($minutos < 1) {
            return 'Ahora';
        }
        
        if ($minutos < 60) {
            return "{$minutos} min";
        }
        
        $horas = floor($minutos / 60);
        $minutosRestantes = $minutos % 60;
        
        if ($minutosRestantes === 0) {
            return "{$horas}h";
        }
        
        return "{$horas}h {$minutosRestantes}min";
    }

    /**
     * Formatea el tiempo en gotas para display
     */
    public function getTiempoGotasFormateadoAttribute()
    {
        $minutos = $this->tiempo_en_gotas;
        
        if ($minutos === null) {
            return null;
        }
        
        if ($minutos < 1) {
            return 'Ahora';
        }
        
        if ($minutos < 60) {
            return "{$minutos} min";
        }
        
        $horas = floor($minutos / 60);
        $minutosRestantes = $minutos % 60;
        
        if ($minutosRestantes === 0) {
            return "{$horas}h";
        }
        
        return "{$horas}h {$minutosRestantes}min";
    }

    public function getEstadoColorAttribute()
    {
        return self::ESTADO_COLORES[$this->estado] ?? '#6c757d';
    }

    public function getEstadoLabelAttribute()
    {
        return self::ESTADO_LABELS[$this->estado] ?? 'Sin estado';
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

    public function diagnosticos()
    {
        return $this->belongsToMany(Diagnostico::class, 'consulta_diagnostico')
            ->withPivot('tipo', 'orden')
            ->withTimestamps()
            ->orderBy('consulta_diagnostico.orden');
    }

    public function evaluacion()
    {
        return $this->hasOne(ConsultaEvaluacion::class);
    }

    public function estudios()
    {
        return $this->hasMany(ConsultaEstudio::class)->orderBy('orden');
    }

    public function tratamientos()
    {
        return $this->hasMany(ConsultaTratamiento::class)->orderBy('orden');
    }

    public function reposo()
    {
        return $this->hasOne(Reposo::class);
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class);
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function gotasAplicadas()
    {
        return $this->hasMany(ConsultaGota::class);
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
