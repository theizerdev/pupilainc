<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Traits\Multitenantable;

class TipoConsulta extends Model
{
    use HasFactory, LogsActivity, Multitenantable;

    protected $table = 'tipo_consultas';

    protected $fillable = [
        'nombre',
        'descripcion',
        'color',
        'icono',
        'status',
        'empresa_id',
        'sucursal_id',
        'codigo'
    ];

    protected $casts = [
        'status' => 'boolean',
      
      
    ];

    protected $attributes = [
        'status' => true,
      
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

    public function citas()
    {
        return $this->hasMany(Cita::class);
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

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('nombre', 'like', "%{$search}%")
              ->orWhere('descripcion', 'like', "%{$search}%")
              ->orWhere('codigo', 'like', "%{$search}%");
        });
    }

    public function getTotalCitasAttribute()
    {
        return $this->citas()->count();
    }

    public function getEstadoColorAttribute()
    {
        return $this->status ? 'success' : 'danger';
    }

    public function getEstadoTextoAttribute()
    {
        return $this->status ? 'Activo' : 'Inactivo';
    }

    public function getColorClassAttribute()
    {
        $colors = [
            'primary' => 'bg-primary',
            'secondary' => 'bg-secondary',
            'success' => 'bg-success',
            'danger' => 'bg-danger',
            'warning' => 'bg-warning',
            'info' => 'bg-info',
            'light' => 'bg-light',
            'dark' => 'bg-dark',
        ];

        return $colors[$this->color] ?? 'bg-primary';
    }

    public function getIconHtmlAttribute()
    {
        return $this->icono ? "<i class='{$this->icono}'></i>" : '<i class="fas fa-stethoscope"></i>';
    }

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($tipoConsulta) {
            if (!$tipoConsulta->codigo) {
                $tipoConsulta->codigo = self::generateCodigo();
            }
        });
    }

    public static function generateCodigo(): string
    {
        $ultimo = self::orderBy('id', 'desc')->first();
        $numero = $ultimo ? ((int)substr($ultimo->codigo, 8)) + 1 : 1;
        return  str_pad($numero, 3, '0', STR_PAD_LEFT);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['nombre', 'descripcion', 'codigo', 'status', 'costo_consulta', 'duracion_consulta'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

      public function scopeActivos($query)
    {
        return $query->where('status', true);
    }

}