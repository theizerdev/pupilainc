<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Multitenantable;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Traits\HasSpanishActivityLog;
use App\Traits\FiscalAuditable;

class AnulacionTalonario extends Model
{
    use HasFactory, Multitenantable, SoftDeletes, LogsActivity, HasSpanishActivityLog, FiscalAuditable;

    protected $table = 'anulacion_talonarios';

    const MOTIVO_DANO_FISICO = 'dano_fisico';
    const MOTIVO_ROBO = 'robo';
    const MOTIVO_EXTRAVIO = 'extravio';
    const MOTIVO_ERROR_IMPRESION = 'error_impresion';
    const MOTIVO_CAMBIO_DATOS_FISCALES = 'cambio_datos_fiscales';
    const MOTIVO_FIN_ACTIVIDAD = 'fin_actividad';
    const MOTIVO_OTRO = 'otro';

    const ESTADO_REGISTRADO = 'registrado';
    const ESTADO_REPORTADO_SENIAT = 'reportado_seniat';
    const ESTADO_CONFIRMADO = 'confirmado';

    protected $fillable = [
        'empresa_id',
        'sucursal_id',
        'serie_id',
        'tipo_documento',
        'serie_afectada',
        'numero_control_desde',
        'numero_control_hasta',
        'correlativo_desde',
        'correlativo_hasta',
        'cantidad_documentos',
        'fecha_anulacion',
        'motivo',
        'descripcion_motivo',
        'estado',
        'fecha_reporte_seniat',
        'numero_reporte_seniat',
        'acta_destruccion',
        'observaciones',
        'user_id',
    ];

    protected $casts = [
        'fecha_anulacion' => 'date',
        'fecha_reporte_seniat' => 'date',
        'acta_destruccion' => 'boolean',
        'cantidad_documentos' => 'integer',
    ];

    protected $attributes = [
        'estado' => self::ESTADO_REGISTRADO,
        'acta_destruccion' => false,
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function serie()
    {
        return $this->belongsTo(Serie::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function getMotivos()
    {
        return [
            self::MOTIVO_DANO_FISICO => 'Daño Físico',
            self::MOTIVO_ROBO => 'Robo',
            self::MOTIVO_EXTRAVIO => 'Extravío',
            self::MOTIVO_ERROR_IMPRESION => 'Error de Impresión',
            self::MOTIVO_CAMBIO_DATOS_FISCALES => 'Cambio de Datos Fiscales',
            self::MOTIVO_FIN_ACTIVIDAD => 'Fin de Actividad',
            self::MOTIVO_OTRO => 'Otro',
        ];
    }

    public static function getEstados()
    {
        return [
            self::ESTADO_REGISTRADO => 'Registrado',
            self::ESTADO_REPORTADO_SENIAT => 'Reportado al SENIAT',
            self::ESTADO_CONFIRMADO => 'Confirmado',
        ];
    }

    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo_documento', $tipo);
    }

    public function scopePorEstado($query, $estado)
    {
        return $query->where('estado', $estado);
    }

    const MOTIVOS_REQUIEREN_DENUNCIA = ['robo', 'extravio'];

    public function requiereDenuncia(): bool
    {
        return in_array($this->motivo, self::MOTIVOS_REQUIEREN_DENUNCIA);
    }

    public static function tieneRangoSolapado(int $empresaId, string $tipoDocumento, string $serieAfectada, string $controlDesde, string $controlHasta, ?int $excludeId = null): bool
    {
        return static::where('empresa_id', $empresaId)
            ->where('tipo_documento', $tipoDocumento)
            ->where('serie_afectada', $serieAfectada)
            ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
            ->where(function ($q) use ($controlDesde, $controlHasta) {
                $q->whereBetween('numero_control_desde', [$controlDesde, $controlHasta])
                  ->orWhereBetween('numero_control_hasta', [$controlDesde, $controlHasta])
                  ->orWhere(function ($q2) use ($controlDesde, $controlHasta) {
                      $q2->where('numero_control_desde', '<=', $controlDesde)
                         ->where('numero_control_hasta', '>=', $controlHasta);
                  });
            })
            ->exists();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'tipo_documento', 'serie_afectada', 'numero_control_desde', 'numero_control_hasta',
                'correlativo_desde', 'correlativo_hasta', 'cantidad_documentos',
                'fecha_anulacion', 'motivo', 'estado',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => static::getSpanishDescription($eventName));
    }

    public static function documentosEnUso(int $empresaId, string $tipoDocumento, string $serieAfectada, string $controlDesde, string $controlHasta): int
    {
        return \App\Models\Pago::where('empresa_id', $empresaId)
            ->where('tipo_pago', $tipoDocumento)
            ->where('serie', $serieAfectada)
            ->where('es_factura_fiscal', true)
            ->whereBetween('numero_control_fiscal', [$controlDesde, $controlHasta])
            ->count();
    }
}
