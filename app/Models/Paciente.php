<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Traits\Multitenantable;
use Carbon\Carbon;

class Paciente extends Model
{
    use HasFactory, LogsActivity, Multitenantable;

    protected $table = 'pacientes';

    protected $fillable = [
        'nombres',
        'apellidos',
        'documento_identidad',
        'telefono',
        'email',
        'direccion',
        'nickname',
        'fecha_nacimiento',
        'genero',
        'estado_civil',
        'ocupacion',
        'nacionalidad',
        'foto',
        'status',
        'empresa_id',
        'sucursal_id'
    ];

    protected $casts = [
        'status' => 'boolean',
        'fecha_nacimiento' => 'date',
    ];

    protected $attributes = [
        'status' => true,
    ];

    // Relaciones
    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function tutor()
    {
        return $this->hasOne(Tutor::class);
    }

    public function citas()
    {
        return $this->hasMany(Cita::class);
    }

    // Scopes
    public function scopeActivos($query)
    {
        return $query->where('status', true);
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

    // Accessores
    public function getNombreCompletoAttribute()
    {
        return "{$this->nombres} {$this->apellidos}";
    }

    public function getEdadAttribute()
    {
        if ($this->fecha_nacimiento) {
            return $this->fecha_nacimiento->diffInYears(Carbon::now());
        }
        return null;
    }

    public function isProfileComplete(): bool
    {
        $required = [
            'nombres',
            'apellidos',
            'documento_identidad',
            'fecha_nacimiento',
        ];

        foreach ($required as $field) {
            $value = $this->$field;
            if (is_string($value)) {
                $value = trim($value);
            }
            if ($value === null || $value === '') {
                return false;
            }
        }

        if ($this->es_menor) {
            $tutor = $this->relationLoaded('tutor') ? $this->tutor : $this->tutor()->first();
            if (!$tutor) {
                return false;
            }

            $tutorRequired = ['nombres', 'apellidos', 'documento_identidad', 'parentesco', 'telefono'];
            foreach ($tutorRequired as $field) {
                $value = $tutor->$field ?? null;
                if (is_string($value)) {
                    $value = trim($value);
                }
                if ($value === null || $value === '') {
                    return false;
                }
            }
        }

        return true;
    }

    public function getEsMenorAttribute()
    {
        return $this->edad !== null && $this->edad < 18;
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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'nombres',
                'apellidos',
                'documento_identidad',
                'telefono',
                'email',
                'status'
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
