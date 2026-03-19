<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CitaAuditoria extends Model
{
    use HasFactory;

    protected $table = 'citas_auditoria';

    public $timestamps = false;

    protected $fillable = [
        'cita_id',
        'empresa_id',
        'sucursal_id',
        'usuario_id',
        'tipo',
        'datos_anteriores',
        'datos_nuevos',
        'nota',
        'created_at',
    ];

    protected $casts = [
        'datos_anteriores' => 'array',
        'datos_nuevos' => 'array',
        'created_at' => 'datetime',
    ];

    public function cita()
    {
        return $this->belongsTo(Cita::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}