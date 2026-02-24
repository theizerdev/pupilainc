<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Multitenantable;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Traits\HasSpanishActivityLog;
use App\Traits\FiscalAuditable;

class Serie extends Model
{
    use Multitenantable, LogsActivity, HasSpanishActivityLog, FiscalAuditable;

    protected $fillable = [
        'tipo_documento',
        'serie',
        'correlativo_actual',
        'control_fiscal_actual',
        'longitud_correlativo',
        'longitud_control_fiscal',
        'activo',
        'empresa_id',
        'sucursal_id'
    ];

    protected $casts = [
        'activo' => 'boolean',
        'correlativo_actual' => 'integer',
        'longitud_correlativo' => 'integer',
        'longitud_control_fiscal' => 'integer'
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function obtenerSiguienteNumero()
    {
        // Incrementar correlativo
        $this->increment('correlativo_actual');
        
        // Incrementar también el número de control fiscal
        if ($this->control_fiscal_actual !== null) {
            $controlActual = intval($this->control_fiscal_actual);
            $controlActual++;
            $this->control_fiscal_actual = str_pad(
                $controlActual, 
                $this->longitud_control_fiscal ?? 8, 
                '0', 
                STR_PAD_LEFT
            );
        } else {
            // Si no existe, inicializar con el mismo valor del correlativo
            $this->control_fiscal_actual = str_pad(
                $this->correlativo_actual, 
                $this->longitud_control_fiscal ?? 8, 
                '0', 
                STR_PAD_LEFT
            );
        }
        
        $this->save();
        $this->refresh();
        
        return str_pad(
            $this->correlativo_actual, 
            $this->longitud_correlativo, 
            '0', 
            STR_PAD_LEFT
        );
    }

    public function getNumeroControlFiscalAttribute()
    {
        if (!$this->control_fiscal_actual) {
            return null;
        }
        return str_pad($this->control_fiscal_actual, $this->longitud_control_fiscal, '0', STR_PAD_LEFT);
    }

    public function getNumeroCompletoAttribute()
    {
        return $this->serie . '-' . str_pad($this->correlativo_actual, $this->longitud_correlativo, '0', STR_PAD_LEFT);
    }

    public static function getTiposDocumento()
    {
        return [
            'factura' => 'Factura',
            'boleta' => 'Boleta',
            'nota_credito' => 'Nota de Crédito',
            'nota_debito' => 'Nota de Débito',
            'recibo' => 'Recibo'
        ];
    }

    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }

    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo_documento', $tipo);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['tipo_documento', 'serie', 'correlativo_actual', 'control_fiscal_actual', 'activo'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => static::getSpanishDescription($eventName));
    }
}
