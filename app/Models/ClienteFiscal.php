<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Multitenantable;

class ClienteFiscal extends Model
{
    use Multitenantable;

    protected $table = 'clientes_fiscales';

    protected $fillable = [
        'tipo_documento',
        'numero_documento',
        'razon_social',
        'nombre_comercial',
        'direccion_fiscal',
        'ciudad',
        'estado',
        'codigo_postal',
        'telefono',
        'email',
        'paciente_id',
        'empresa_id',
        'sucursal_id'
    ];

    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class);
    }

    public function getDocumentoCompletoAttribute()
    {
        return $this->tipo_documento . '-' . $this->numero_documento;
    }
}
