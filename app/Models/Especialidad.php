<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Traits\Multitenantable;

class Especialidad extends Model
{
    use HasFactory, LogsActivity, Multitenantable;

    protected $table = 'especialidades';

    protected $fillable = [
        'nombre',
        'descripcion',
        'codigo',
        'color',
        'icono',
        'status',
        'empresa_id',
        'sucursal_id',
        'costo_consulta',
        'duracion_consulta',
        'requiere_cita_previa'
    ];

    protected $casts = [
        'status' => 'boolean',
        'costo_consulta' => 'decimal:2',
        'duracion_consulta' => 'integer',
        'requiere_cita_previa' => 'boolean'
    ];

    protected $attributes = [
        'status' => true,
        'requiere_cita_previa' => true,
        'duracion_consulta' => 30,
        'color' => '#3B82F6',
        'icono' => 'fa-stethoscope'
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function medicos()
    {
        return $this->belongsToMany(User::class, 'medico_especialidad', 'especialidad_id', 'medico_id')
            ->withPivot('tarifa_consulta', 'horario_atencion', 'status')
            ->withTimestamps();
    }

    /**
     * Get the subespecialidades for the especialidad.
     */
    public function subespecialidades(): HasMany
    {
        return $this->hasMany(Subespecialidad::class);
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

    public function scopeActivas($query)
    {
        return $query->where('status', true);
    }

    public function getTotalMedicosAttribute()
    {
        return $this->medicos()->where('status', true)->count();
    }

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($especialidad) {
            if (!$especialidad->codigo) {
                $especialidad->codigo = self::generateCodigo();
            }
        });
    }

    public static function generateCodigo(): string
    {
        $ultimo = self::orderBy('id', 'desc')->first();
        $numero = $ultimo ? ((int)substr($ultimo->codigo, 3)) + 1 : 1;
        return 'ESP' . str_pad($numero, 3, '0', STR_PAD_LEFT);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['nombre', 'descripcion', 'codigo', 'status', 'costo_consulta'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}