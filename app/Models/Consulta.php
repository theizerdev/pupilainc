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

    // Estados comunes (humanos y veterinarios)
    const ESTADO_EN_ENFERMERIA = 'en_enfermeria';
    const ESTADO_EN_CONSULTORIO = 'en_consultorio';

    // Estados específicos oftalmología humana
    const ESTADO_EN_CONSULTORIO_OPTOMETRISTA = 'en_consultorio_optometrista';
    const ESTADO_EN_GOTAS = 'en_gotas';
    const ESTADO_DILATADO = 'dilatado';
    const ESTADO_EN_OPTICA = 'en_optica';
    const ESTADO_EN_ESTUDIO = 'en_estudio';

    // Estados específicos veterinarios
    const ESTADO_EN_TRIAGE = 'en_triage';
    const ESTADO_EN_TRATAMIENTO = 'en_tratamiento';
    const ESTADO_EN_PROCEDIMIENTO = 'en_procedimiento';
    const ESTADO_PRE_QUIRURGICO = 'pre_quirurgico';
    const ESTADO_EN_CIRUGIA = 'en_cirugia';
    const ESTADO_RECUPERACION = 'recuperacion';
    const ESTADO_EDUCACION_PROPIETARIO = 'educacion_propietario';

    const ESTADOS = [
        self::ESTADO_POR_LLEGAR,
        self::ESTADO_SALA_ESPERA,
        self::ESTADO_EN_ENFERMERIA,
        self::ESTADO_EN_CONSULTORIO,
        // Estados oftalmología
        self::ESTADO_EN_CONSULTORIO_OPTOMETRISTA,
        self::ESTADO_EN_GOTAS,
        self::ESTADO_DILATADO,
        self::ESTADO_EN_OPTICA,
        self::ESTADO_EN_ESTUDIO,
        // Estados veterinarios
        self::ESTADO_EN_TRIAGE,
        self::ESTADO_EN_TRATAMIENTO,
        self::ESTADO_EN_PROCEDIMIENTO,
        self::ESTADO_PRE_QUIRURGICO,
        self::ESTADO_EN_CIRUGIA,
        self::ESTADO_RECUPERACION,
        self::ESTADO_EDUCACION_PROPIETARIO,
        // Estados finales
        self::ESTADO_FINALIZADA,
        self::ESTADO_PAGADA,
        self::ESTADO_BORRADOR,
    ];

    const ESTADO_LABELS = [
        self::ESTADO_POR_LLEGAR => 'Por llegar',
        self::ESTADO_SALA_ESPERA => 'Sala de Espera',
        self::ESTADO_EN_ENFERMERIA => 'En Enfermería',
        self::ESTADO_EN_CONSULTORIO => 'En Consultorio',
        // Estados oftalmología
        self::ESTADO_EN_CONSULTORIO_OPTOMETRISTA => 'En Consultorio Optometrista',
        self::ESTADO_EN_GOTAS => 'En Gotas',
        self::ESTADO_DILATADO => 'Dilatado',
        self::ESTADO_EN_OPTICA => 'En Óptica',
        self::ESTADO_EN_ESTUDIO => 'En Estudio',
        // Estados veterinarios
        self::ESTADO_EN_TRIAGE => 'En Triaje/Urgencias',
        self::ESTADO_EN_TRATAMIENTO => 'En Tratamiento',
        self::ESTADO_EN_PROCEDIMIENTO => 'En Procedimiento/Curas',
        self::ESTADO_PRE_QUIRURGICO => 'Pre-Quirúrgico',
        self::ESTADO_EN_CIRUGIA => 'En Cirugía',
        self::ESTADO_RECUPERACION => 'En Recuperación',
        self::ESTADO_EDUCACION_PROPIETARIO => 'Educación/Alta',
        // Estados finales
        self::ESTADO_FINALIZADA => 'Finalizada',
        self::ESTADO_PAGADA => 'Pagada',
        self::ESTADO_BORRADOR => 'Borrador',
    ];

    const ESTADO_COLORES = [
        self::ESTADO_POR_LLEGAR => '#9E9E9E',
        self::ESTADO_SALA_ESPERA => '#FFA726',
        self::ESTADO_EN_ENFERMERIA => '#EF5350',
        self::ESTADO_EN_CONSULTORIO => '#42A5F5',
        // Estados oftalmología
        self::ESTADO_EN_CONSULTORIO_OPTOMETRISTA => '#7E57C2',
        self::ESTADO_EN_GOTAS => '#26C6DA',
        self::ESTADO_DILATADO => '#00BCD4',
        self::ESTADO_EN_OPTICA => '#AB47BC',
        self::ESTADO_EN_ESTUDIO => '#EC407A',
        // Estados veterinarios
        self::ESTADO_EN_TRIAGE => '#FF5722',      // Naranja rojizo - urgencia
        self::ESTADO_EN_TRATAMIENTO => '#2196F3',  // Azul - tratamiento
        self::ESTADO_EN_PROCEDIMIENTO => '#009688',// Verde azulado - procedimientos
        self::ESTADO_PRE_QUIRURGICO => '#FFC107',  // Amarillo - preparación
        self::ESTADO_EN_CIRUGIA => '#F44336',      // Rojo - cirugía
        self::ESTADO_RECUPERACION => '#8BC34A',    // Verde claro - recuperación
        self::ESTADO_EDUCACION_PROPIETARIO => '#9C27B0', // Púrpura - educación
        // Estados finales
        self::ESTADO_FINALIZADA => '#66BB6A',
        self::ESTADO_PAGADA => '#4CAF50',
        self::ESTADO_BORRADOR => '#BDBDBD',
    ];

    protected $fillable = [
        'codigo',
        'cita_id',
        'paciente_id',
        'mascota_id',
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
        'plan_tratamiento',
        'medicamentos',
        'observaciones',
        'observaciones_enfermeria',
        'observaciones_triage',
        'estado',
        'estado_changed_at',
        'empresa_id',
        'sucursal_id',
        'created_by',
        'updated_by',
        // Campos veterinarios
        'temperatura_rectal',
        'frecuencia_cardiaca',
        'frecuencia_respiratoria',
        'peso_actual_kg',
        'peso_historico_kg',
        'bcs_score',
        'examen_fisico_general',
        'sistema_cardiovascular',
        'sistema_respiratorio',
        'sistema_digestivo',
        'sistema_urinario',
        'sistema_nervioso',
        'piel_y_pelaje',
        'ojos_oidos_boca',
        'sistema_locomotor',
        // Campos de tratamiento veterinario
        'tipo_tratamiento',
        'medicamento_administrado',
        'dosis_administrada',
        'via_administracion',
        'frecuencia_tratamiento',
        'sitio_aplicacion',
        'hora_tratamiento',
        'reaccion_adversa',
        // Campos de procedimiento veterinario
        'tipo_procedimiento',
        'area_afectada',
        'descripcion_herida',
        'tipo_vendaje',
        'material_utilizado',
        'estado_post_procedimiento',
        'proxima_cura',
        'variacion_peso',
        // Campos pre-quirúrgicos veterinarios
        'ayuno_horas',
        'ayuno_agua_horas',
        'premedicacion_aplicada',
        'premedicacion_medicamento',
        'premedicacion_hora',
        'exam_hemograma',
        'exam_bioquimica',
        'exam_coagulacion',
        'exam_electrolitos',
        'exam_radiografia',
        'exam_ecocardiograma',
        'exam_observaciones',
        'clasificacion_asa',
        'estado_fisico',
        'factores_riesgo',
        'consentimiento_firmado',
        'explicacion_riesgos',
        // Campos quirúrgicos veterinarios
        'tipo_cirugia',
        'cirujano_responsable',
        'anestesiologo',
        'hora_inicio_cirugia',
        'hora_fin_cirugia',
        'duracion_cirugia_min',
        'protocolo_anestesico',
        'medicamentos_quirurgicos',
        'hallazgos_quirurgicos',
        'material_implantado',
        'suturas_utilizadas',
        'complicaciones_quirurgicas',
        'estado_post_operatorio',
        // Campos de recuperación veterinaria
        'hora_recuperacion',
        'estado_conciencia',
        'reflejos_post_operatorios',
        'temperatura_recuperacion',
        'fc_recuperacion',
        'fr_recuperacion',
        'presion_arterial_recuperacion',
        'spo2_recuperacion',
        'evaluacion_dolor',
        'escala_dolor',
        'analgesia_recuperacion',
        'fluidoterapia_recuperacion',
        'tipo_fluido_recuperacion',
        'volumen_fluido_ml_hr',
        'miccion_recuperacion',
        'deposicion_recuperacion',
        'apetito_recuperacion',
        'movilidad_recuperacion',
        'estado_herida_recuperacion',
        'sangrado_activo_recuperacion',
        'complicaciones_recuperacion',
        'observaciones_recuperacion',
        // Campos de educación/alta veterinaria
        'diagnostico_final',
        'tratamiento_domiciliario',
        'receta_medicamentos',
        'instrucciones_dosis',
        'duracion_tratamiento_dias',
        'cuidados_herida_alta',
        'alimentacion_alta',
        'restricciones_ejercicio',
        'signos_alerta_alta',
        'fecha_control_alta',
        'vacunacion_pendiente_alta',
        'desparasitacion_pendiente_alta',
        'recomendaciones_nutricion',
        'recomendaciones_higiene',
        'instrucciones_emergencia',
        'propietario_educado',
        'material_educativo_entregado',
        'observaciones_alta',
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

        static::updating(function (self $consulta) {
            if ($consulta->isDirty('estado')) {
                $consulta->estado_changed_at = now();
            }
        });
    }

    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    public function mascota()
    {
        return $this->belongsTo(Mascota::class, 'mascota_id');
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

    public function estadoDatos()
    {
        return $this->hasMany(ConsultaEstadoDato::class);
    }

    public function notas()
    {
        return $this->hasMany(ConsultaNota::class)->orderBy('created_at', 'desc');
    }

    public function getDatosEstado(string $estado): array
    {
        return $this->estadoDatos()->where('estado', $estado)->first()?->datos ?? [];
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
