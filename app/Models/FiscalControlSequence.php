<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Multitenantable;

class FiscalControlSequence extends Model
{
    use Multitenantable;

    protected $table = 'fiscal_control_sequences';

    protected $fillable = [
        'empresa_id',
        'sucursal_id',
        'correlativo_actual',
        'longitud',
        'prefijo',
        'rango_inicio',
        'rango_fin',
    ];

    protected $casts = [
        'correlativo_actual' => 'integer',
        'longitud' => 'integer',
        'rango_inicio' => 'integer',
        'rango_fin' => 'integer',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function obtenerSiguienteNumero(): string
    {
        // Validar que no se exceda el rango autorizado por el SENIAT
        if ($this->rango_fin && ($this->correlativo_actual + 1) > $this->rango_fin) {
            throw new \RuntimeException(
                "Se agotó el rango de números de control fiscal autorizados (hasta {$this->rango_fin}). " .
                "Debe solicitar un nuevo rango al SENIAT."
            );
        }

        $this->increment('correlativo_actual');
        $this->refresh();

        return $this->prefijo . str_pad($this->correlativo_actual, $this->longitud, '0', STR_PAD_LEFT);
    }
}
