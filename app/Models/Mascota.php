<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Traits\Multitenantable;
use App\Traits\HasSpanishActivityLog;
use Carbon\Carbon;

class Mascota extends Model
{
    use HasFactory, SoftDeletes, LogsActivity, Multitenantable, HasSpanishActivityLog;

    protected $table = 'mascotas';

    protected $fillable = [
        'nombre',
        'especie_id',
        'raza_id',
        'sexo',
        'fecha_nacimiento',
        'peso_actual_kg',
        'color_pelaje',
        'marcas_distintivas',
        'microchip',
        'numero_registro',
        'foto',
        'esterilizado',
        'fecha_esterilizacion',
        'activo',
        'propietario_id',
        'notas_generales',
        'alergias_conocidas',
        'condiciones_cronicas',
        'nivel_agresividad',
        'empresa_id',
        'sucursal_id',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'fecha_esterilizacion' => 'date',
        'esterilizado' => 'boolean',
        'activo' => 'boolean',
        'peso_actual_kg' => 'decimal:2',
    ];

    protected $attributes = [
        'activo' => true,
        'sexo' => 'macho',
        'nivel_agresividad' => 'tranquilo',
        'esterilizado' => false,
    ];

    // Relaciones
    public function especie(): BelongsTo
    {
        return $this->belongsTo(Especie::class);
    }

    public function raza(): BelongsTo
    {
        return $this->belongsTo(Raza::class);
    }

    public function propietario(): BelongsTo
    {
        return $this->belongsTo(Propietario::class, 'propietario_id');
    }

    public function citas(): HasMany
    {
        return $this->hasMany(Cita::class, 'mascota_id');
    }

    public function consultas(): HasMany
    {
        return $this->hasMany(Consulta::class, 'mascota_id');
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    // Scopes
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopeForUser($query)
    {
        if (auth()->check() && !auth()->user()->hasRole('Super Administrador')) {
            if (auth()->user()->empresa_id) {
                $query->where('empresa_id', auth()->user()->empresa_id);
            }
            if (auth()->user()->sucursal_id) {
                $query->where('sucursal_id', auth()->user()->sucursal_id);
            }
        }
        return $query;
    }

    public function scopePorEspecie($query, $especieId)
    {
        return $query->where('especie_id', $especieId);
    }

    public function scopePorPropietario($query, $propietarioId)
    {
        return $query->where('propietario_id', $propietarioId);
    }

    // Accessores
    public function getNombreCompletoAttribute()
    {
        return $this->nombre;
    }

    public function getEdadAttribute()
    {
        if ($this->fecha_nacimiento) {
            return (int) $this->fecha_nacimiento->diffInYears(Carbon::now());
        }
        return null;
    }

    public function getEdadFormateadaAttribute()
    {
        if (!$this->fecha_nacimiento) {
            return null;
        }

        $nacimiento = Carbon::parse($this->fecha_nacimiento);
        $años = $nacimiento->age;
        if ($años < 0) $años = 0;

        if ($años < 1) {
            $meses = (int) $nacimiento->diffInMonths(now());
            return $meses === 1 ? '1 mes' : "{$meses} meses";
        }

        if ($años < 2) {
            $meses = (int) $nacimiento->diffInMonths(now()) % 12;
            return "1 año" . ($meses > 0 ? " y {$meses} meses" : "");
        }

        return "{$años} años";
    }

    public function getInformacionEspecieRazaAttribute()
    {
        $info = $this->especie->nombre ?? 'Sin especie';
        if ($this->raza) {
            $info .= " - {$this->raza->nombre}";
        }
        return $info;
    }

    public function getSexoLabelAttribute()
    {
        return $this->sexo === 'macho' ? 'Macho' : 'Hembra';
    }

    public function getNivelAgresividadLabelAttribute()
    {
        $labels = [
            'tranquilo' => 'Tranquilo',
            'nervioso' => 'Nervioso',
            'agresivo_leve' => 'Agresividad Leve',
            'agresivo' => 'Agresivo',
        ];
        return $labels[$this->nivel_agresividad] ?? $this->nivel_agresividad;
    }

    public function isProfileComplete(): bool
    {
        $required = ['nombre', 'especie_id', 'sexo'];

        foreach ($required as $field) {
            $value = $this->$field ?? null;
            if (is_string($value)) {
                $value = trim($value);
            }
            if ($value === null || $value === '') {
                return false;
            }
        }

        return true;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'nombre',
                'especie_id',
                'raza_id',
                'sexo',
                'peso_actual_kg',
                'microchip',
                'activo'
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => static::getSpanishDescription($eventName));
    }
}
