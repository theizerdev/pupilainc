<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Multitenantable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsultorioAsignacion extends Model
{
    use HasFactory, Multitenantable;

    protected $table = 'consultorio_asignaciones';

    protected $fillable = [
        'consultorio_id',
        'medico_id',
        'fecha',
        'hora_inicio',
        'hora_fin',
        'empresa_id',
        'sucursal_id',
        'created_by',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function consultorio(): BelongsTo
    {
        return $this->belongsTo(Consultorio::class);
    }

    public function medico(): BelongsTo
    {
        return $this->belongsTo(Medico::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
