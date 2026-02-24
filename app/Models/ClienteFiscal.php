<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Multitenantable;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Traits\HasSpanishActivityLog;
use App\Traits\FiscalAuditable;

class ClienteFiscal extends Model
{
    use Multitenantable, LogsActivity, HasSpanishActivityLog, FiscalAuditable;

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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['tipo_documento', 'numero_documento', 'razon_social', 'direccion_fiscal', 'telefono', 'email'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => static::getSpanishDescription($eventName));
    }
}
