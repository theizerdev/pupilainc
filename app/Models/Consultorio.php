<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Multitenantable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Consultorio extends Model
{
    use HasFactory, SoftDeletes, Multitenantable;

    protected $table = 'consultorios';

    protected $fillable = [
        'nombre',
        'ubicacion',
        'descripcion',
        'status',
        'empresa_id',
        'sucursal_id',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function asignaciones(): HasMany
    {
        return $this->hasMany(ConsultorioAsignacion::class);
    }
}
