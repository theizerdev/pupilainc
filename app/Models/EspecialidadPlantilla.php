<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\Multitenantable;

class EspecialidadPlantilla extends Model
{
    use Multitenantable;

    protected $table = 'especialidad_plantillas';

    protected $fillable = [
        'especialidad_id',
        'nombre',
        'descripcion',
        'activo',
        'pasos_habilitados',
        'estados_flujo',
        'empresa_id',
        'sucursal_id',
    ];

    protected $casts = [
        'activo'            => 'boolean',
        'pasos_habilitados' => 'array',
        'estados_flujo'     => 'array',
    ];

    // Pasos disponibles en el sistema
    const PASOS_DISPONIBLES = [
        'signos_vitales' => 'Signos Vitales',
        'cuestionario'   => 'Cuestionario',
        'evaluacion'     => 'Evaluación Clínica',
        'estudios'       => 'Estudios',
        'tratamientos'   => 'Tratamientos',
        'reposo'         => 'Reposo Médico',
    ];

    // Estados disponibles en el sistema
    const ESTADOS_DISPONIBLES = [
        'por_llegar'                    => 'Por Llegar',
        'sala_espera'                   => 'Sala de Espera',
        'en_enfermeria'                 => 'En Enfermería',
        'en_consultorio'                => 'En Consultorio',
        'en_consultorio_optometrista'   => 'En Consultorio Optometrista',
        'en_gotas'                      => 'En Gotas',
        'dilatado'                      => 'Dilatado',
        'en_optica'                     => 'En Óptica',
        'en_estudio'                    => 'En Estudio',
        'finalizada'                    => 'Finalizada',
        'pagada'                        => 'Pagada',
    ];

    public function especialidad(): BelongsTo
    {
        return $this->belongsTo(Especialidad::class);
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function secciones(): HasMany
    {
        return $this->hasMany(PlantillaSeccion::class, 'plantilla_id')
            ->whereNull('estado_formulario_id')
            ->where('activo', true)
            ->orderBy('orden');
    }

    public function todasLasSecciones(): HasMany
    {
        return $this->hasMany(PlantillaSeccion::class, 'plantilla_id')
            ->whereNull('estado_formulario_id')
            ->orderBy('orden');
    }

    public function estadoFormularios(): HasMany
    {
        return $this->hasMany(PlantillaEstadoFormulario::class, 'plantilla_id')
            ->where('activo', true);
    }

    public function todosLosEstadoFormularios(): HasMany
    {
        return $this->hasMany(PlantillaEstadoFormulario::class, 'plantilla_id');
    }

    public function getFormularioParaEstado(string $estado): ?PlantillaEstadoFormulario
    {
        return $this->estadoFormularios()->where('estado', $estado)->first();
    }

    // Devuelve los pasos habilitados o todos por defecto
    public function getPasosEfectivos(): array
    {
        return $this->pasos_habilitados ?? array_keys(self::PASOS_DISPONIBLES);
    }

    // Devuelve los estados del flujo o el flujo genérico por defecto
    public function getEstadosEfectivos(): array
    {
        return $this->estados_flujo ?? [
            'sala_espera',
            'en_enfermeria',
            'en_consultorio',
            'finalizada',
        ];
    }

    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Devuelve los estados del flujo para una especialidad dada.
     * Usa caché estático para no repetir queries en el mismo request.
     */
    public static function estadosFlujoParaEspecialidad(?int $especialidadId): array
    {
        static $cache = [];

        if (!$especialidadId) {
            return ['programada', 'confirmada', 'sala_espera', 'en_enfermeria', 'en_consultorio', 'finalizada'];
        }

        if (!isset($cache[$especialidadId])) {
            $plantilla = static::where('especialidad_id', $especialidadId)
                ->where('activo', true)
                ->latest()
                ->first();

            $cache[$especialidadId] = $plantilla
                ? array_merge(
                    ['programada', 'confirmada', 'cancelada', 'no_asistio'],
                    $plantilla->getEstadosEfectivos()
                  )
                : array_keys(self::ESTADOS_DISPONIBLES);
        }

        return $cache[$especialidadId];
    }
}
