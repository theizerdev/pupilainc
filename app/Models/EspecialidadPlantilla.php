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
        'pasos_config',
        'estados_config',
        'empresa_id',
        'sucursal_id',
    ];

    protected $casts = [
        'activo'            => 'boolean',
        'pasos_habilitados' => 'array',
        'estados_flujo'     => 'array',
        'pasos_config'      => 'array',
        'estados_config'    => 'array',
    ];

    // Pasos predefinidos del sistema (no se pueden eliminar, solo editar nombre/icono)
    const PASOS_DISPONIBLES = [
        'signos_vitales' => 'Signos Vitales',
        'cuestionario'   => 'Cuestionario',
        'evaluacion'     => 'Evaluación Clínica',
        'estudios'       => 'Estudios',
        'tratamientos'   => 'Tratamientos',
        'reposo'         => 'Reposo Médico',
    ];

    const PASOS_ICONOS_DEFAULT = [
        'signos_vitales' => 'ri-heart-pulse-line',
        'cuestionario'   => 'ri-questionnaire-line',
        'evaluacion'     => 'ri-file-text-line',
        'estudios'       => 'ri-test-tube-line',
        'tratamientos'   => 'ri-capsule-line',
        'reposo'         => 'ri-hotel-bed-line',
    ];

    // Devuelve la config completa de pasos ordenada
    public function getPasosEfectivos(): array
    {
        if (!empty($this->pasos_config)) {
            return collect($this->pasos_config)
                ->sortBy('orden')
                ->values()
                ->toArray();
        }

        // Migrar desde pasos_habilitados legacy
        $habilitados = $this->pasos_habilitados ?? array_keys(self::PASOS_DISPONIBLES);
        $config = [];
        foreach (array_keys(self::PASOS_DISPONIBLES) as $i => $key) {
            $config[] = [
                'key'    => $key,
                'nombre' => self::PASOS_DISPONIBLES[$key],
                'icono'  => self::PASOS_ICONOS_DEFAULT[$key] ?? 'ri-circle-line',
                'orden'  => $i + 1,
                'activo' => in_array($key, $habilitados),
                'tipo'   => 'predefinido',
            ];
        }
        return $config;
    }

    // Devuelve solo las keys de los pasos activos ordenados (para el stepper)
    public function getPasosActivosKeys(): array
    {
        return collect($this->getPasosEfectivos())
            ->where('activo', true)
            ->pluck('key')
            ->values()
            ->toArray();
    }

    // Devuelve la config completa de estados ordenada
    public function getEstadosEfectivos(): array
    {
        if (!empty($this->estados_config)) {
            return collect($this->estados_config)
                ->sortBy('orden')
                ->values()
                ->toArray();
        }

        // Migrar desde estados_flujo legacy
        $flujo = $this->estados_flujo ?? ['sala_espera', 'en_enfermeria', 'en_consultorio', 'finalizada'];
        $config = [];
        foreach ($flujo as $i => $key) {
            $config[] = [
                'key'    => $key,
                'nombre' => self::ESTADOS_DISPONIBLES[$key] ?? ucfirst($key),
                'color'  => \App\Models\Consulta::ESTADO_COLORES[$key] ?? '#78909C',
                'orden'  => $i + 1,
                'activo' => true,
            ];
        }
        return $config;
    }

    // Devuelve solo las keys de los estados activos ordenados (para el kanban)
    public function getEstadosActivosKeys(): array
    {
        return collect($this->getEstadosEfectivos())
            ->where('activo', true)
            ->sortBy('orden')
            ->pluck('key')
            ->values()
            ->toArray();
    }

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
                    $plantilla->getEstadosActivosKeys()
                  )
                : array_keys(self::ESTADOS_DISPONIBLES);
        }

        return $cache[$especialidadId];
    }
}
