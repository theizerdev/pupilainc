<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\Multitenantable;
class Subespecialidad extends Model
{
    use HasFactory, Multitenantable;


    protected $table = 'subespecialidades';


    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nombre',
        'codigo',
        'color',
        'descripcion',
        'especialidad_id',
        'costo_consulta',
        'duracion_consulta',
        'requiere_cita_previa',
        'status',
        'empresa_id',
        'sucursal_id',
        'user_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'costo_consulta' => 'decimal:2',
        'duracion_consulta' => 'integer',
        'requiere_cita_previa' => 'boolean',
        'status' => 'boolean',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($subespecialidad) {
            if (empty($subespecialidad->codigo)) {
                $subespecialidad->codigo = self::generateCodigo();
            }
        });
    }

    /**
     * Get the especialidad that owns the subespecialidad.
     */
    public function especialidad(): BelongsTo
    {
        return $this->belongsTo(Especialidad::class);
    }

    /**
     * Get the empresa that owns the subespecialidad.
     */
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    /**
     * Get the sucursal that owns the subespecialidad.
     */
    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class);
    }

    /**
     * Get the user that owns the subespecialidad.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the medicos for the subespecialidad.
     */
    public function medicos(): HasMany
    {
        return $this->hasMany(Medico::class);
    }

    /**
     * Scope a query to only include active subespecialidades.
     */
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    /**
     * Scope a query to only include subespecialidades for the current user.
     */
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

    /**
     * Get the costo_consulta, using especialidad cost if not set.
     */
    public function getCostoConsultaAttribute($value)
    {
        if (is_null($value) && $this->especialidad) {
            return $this->especialidad->costo_consulta;
        }
        return $value;
    }

    /**
     * Get the duracion_consulta, using especialidad duration if not set.
     */
    public function getDuracionConsultaAttribute($value)
    {
        if (is_null($value) && $this->especialidad) {
            return $this->especialidad->duracion_consulta;
        }
        return $value;
    }

    /**
     * Generate a unique code for the subespecialidad.
     */
    public static function generateCodigo(): string
    {
        $ultimo = self::orderBy('id', 'desc')->first();
        $numero = $ultimo ? ((int)substr($ultimo->codigo, 3)) + 1 : 1;
        return 'SUB' . str_pad($numero, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Get total medicos count for this subespecialidad.
     */
    public function getTotalMedicosAttribute(): int
    {
        return $this->medicos()->count();
    }

    /**
     * Get the display name with especialidad.
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->especialidad->nombre . ' - ' . $this->nombre;
    }
}