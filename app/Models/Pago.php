<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Multitenantable;
use App\Services\Seniat\FiscalCalculator;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Traits\HasSpanishActivityLog;
use App\Traits\FiscalAuditable;
use Illuminate\Support\Facades\DB;

class Pago extends Model
{
    use HasFactory, Multitenantable, SoftDeletes, LogsActivity, HasSpanishActivityLog, FiscalAuditable;

    const TIPO_FACTURA = 'factura';
    const TIPO_BOLETA = 'boleta';
    const TIPO_NOTA_CREDITO = 'nota_credito';
    const TIPO_NOTA_DEBITO = 'nota_debito';
    const TIPO_RECIBO = 'recibo';

    const ESTADO_PENDIENTE = 'pendiente';
    const ESTADO_APROBADO = 'aprobado';
    const ESTADO_CANCELADO = 'cancelado';

    protected $fillable = [
        'consulta_id',
        'pago_origen_id',
        'tipo_nota_credito_id',
        'tipo_nota_debito_id',
        'caja_id',
        'serie_id',
        'serie',
        'numero',
        'tipo_pago',
        'fecha',
        'user_id',
        'subtotal',
        'descuento',
        'total',
        'tasa_cambio_usd',
        'tasa_cambio_eur',
        'total_usd',
        'total_bs',
        'metodo_pago',
        'referencia',
        'es_pago_mixto',
        'detalles_pago_mixto',
        'estado',
        'observaciones',
        'motivo_nota',
        'empresa_id',
        'sucursal_id',
        'cliente_fiscal_id',
        'numero_control_fiscal',
        'es_factura_fiscal',
        'base_imponible',
        'monto_exento',
        'iva_porcentaje',
        'iva_monto',
        'igtf_porcentaje',
        'igtf_monto',
        'aplica_igtf',
        'total_con_impuestos',
        'fecha_emision_fiscal',
        'fecha_vencimiento_fiscal',
        'base_imponible_general',
        'iva_monto_general',
        'base_imponible_reducida',
        'iva_monto_reducida',
        'condicion_pago',
        'seniat_tipo_documento'
    ];

    protected $casts = [
        'fecha' => 'date',
        'subtotal' => 'decimal:2',
        'descuento' => 'decimal:2',
        'total' => 'decimal:2',
        'tasa_cambio_usd' => 'decimal:4',
        'tasa_cambio_eur' => 'decimal:4',
        'total_usd' => 'decimal:2',
        'total_bs' => 'decimal:2',
        'es_pago_mixto' => 'boolean',
        'detalles_pago_mixto' => 'array',
        'es_factura_fiscal' => 'boolean',
        'base_imponible' => 'decimal:2',
        'monto_exento' => 'decimal:2',
        'iva_porcentaje' => 'decimal:2',
        'iva_monto' => 'decimal:2',
        'igtf_porcentaje' => 'decimal:2',
        'igtf_monto' => 'decimal:2',
        'aplica_igtf' => 'boolean',
        'total_con_impuestos' => 'decimal:2',
        'fecha_emision_fiscal' => 'date',
        'fecha_vencimiento_fiscal' => 'date',
        'base_imponible_general' => 'decimal:2',
        'iva_monto_general' => 'decimal:2',
        'base_imponible_reducida' => 'decimal:2',
        'iva_monto_reducida' => 'decimal:2'
    ];

    protected $attributes = [
        'estado' => self::ESTADO_PENDIENTE,
        'subtotal' => 0,
        'descuento' => 0
    ];

    public function pagoOrigen()
    {
        return $this->belongsTo(Pago::class, 'pago_origen_id');
    }

    public function notasCredito()
    {
        return $this->hasMany(Pago::class, 'pago_origen_id')->where('tipo_pago', self::TIPO_NOTA_CREDITO);
    }

    public function notasDebito()
    {
        return $this->hasMany(Pago::class, 'pago_origen_id')->where('tipo_pago', self::TIPO_NOTA_DEBITO);
    }

    public function tipoNotaCredito()
    {
        return $this->belongsTo(TipoNotaCredito::class);
    }

    public function tipoNotaDebito()
    {
        return $this->belongsTo(TipoNotaDebito::class);
    }

    public function consulta()
    {
        return $this->belongsTo(Consulta::class);
    }

    public function clienteFiscal()
    {
        return $this->belongsTo(ClienteFiscal::class);
    }

    public function detalles()
    {
        return $this->hasMany(PagoDetalle::class);
    }

    public function ventasProductos()
    {
        return $this->hasMany(VentaProducto::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function comprobante()
    {
        return $this->morphOne(Comprobante::class, 'comprobanteable');
    }

    public function auditLogs()
    {
        return $this->morphMany(AuditLog::class, 'auditable');
    }

    public static function getTipos()
    {
        return [
            self::TIPO_FACTURA => 'Factura',
            self::TIPO_BOLETA => 'Boleta',
            self::TIPO_NOTA_CREDITO => 'Nota de Crédito',
            self::TIPO_NOTA_DEBITO => 'Nota de Débito',
            self::TIPO_RECIBO => 'Recibo'
        ];
    }

    public static function getEstados()
    {
        return [
            self::ESTADO_PENDIENTE => 'Pendiente',
            self::ESTADO_APROBADO => 'Aprobado',
            self::ESTADO_CANCELADO => 'Cancelado'
        ];
    }

    public function getNumeroCompletoAttribute()
    {
        if ($this->serieModel) {
            return str_pad($this->numero, $this->serieModel->longitud_correlativo, '0', STR_PAD_LEFT);
        }
        return $this->serie . '-' . $this->numero;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'tipo_pago', 'estado', 'total', 'total_usd', 'total_bs',
                'numero_control_fiscal', 'es_factura_fiscal',
                'base_imponible', 'iva_monto', 'igtf_monto', 'total_con_impuestos',
                'metodo_pago', 'cliente_fiscal_id',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => static::getSpanishDescription($eventName));
    }

    public function serieModel()
    {
        return $this->belongsTo(Serie::class, 'serie_id');
    }

    public function caja()
    {
        return $this->belongsTo(Caja::class);
    }

    public function conceptoPago()
    {
        return $this->belongsToMany(ConceptoPago::class, 'pago_detalles', 'pago_id', 'concepto_pago_id')
            ->withPivot('cantidad', 'precio_unitario', 'subtotal');
    }

    public static function generarNumero($tipo, $empresaId, $sucursalId, $serieId = null)
    {
        return DB::transaction(function () use ($tipo, $empresaId, $sucursalId, $serieId) {
            if ($serieId) {
                $serieModel = Serie::lockForUpdate()->find($serieId);
                if (!$serieModel) {
                    throw new \Exception("Serie con ID {$serieId} no encontrada");
                }
            } else {
                // Buscar serie existente para este tipo de documento
                $serieModel = Serie::lockForUpdate()
                    ->where('tipo_documento', $tipo)
                    ->where('empresa_id', $empresaId)
                    ->where('sucursal_id', $sucursalId)
                    ->where('activo', true)
                    ->first();

                if (!$serieModel) {
                    $prefijos = [
                        'factura' => 'F001',
                        'boleta' => 'B001',
                        'nota_credito' => 'NC01',
                        'nota_debito' => 'ND01',
                        'recibo' => 'R001'
                    ];

                    $seriePrefijo = $prefijos[$tipo] ?? 'DOC1';
                    
                    // Intentar crear la serie usando firstOrCreate para evitar duplicados
                    try {
                        $serieModel = Serie::firstOrCreate(
                            [
                                'tipo_documento' => $tipo,
                                'empresa_id' => $empresaId,
                                'sucursal_id' => $sucursalId,
                                'activo' => true
                            ],
                            [
                                'serie' => $seriePrefijo,
                                'correlativo_actual' => 0,
                                'control_fiscal_actual' => '00000000',
                                'longitud_correlativo' => 8,
                                'longitud_control_fiscal' => 8,
                            ]
                        );
                    } catch (\Exception $e) {
                        // Si falla, buscar cualquier serie existente para este tipo
                        $serieModel = Serie::where('tipo_documento', $tipo)
                            ->where('empresa_id', $empresaId)
                            ->where('sucursal_id', $sucursalId)
                            ->where('activo', true)
                            ->first();
                            
                        if (!$serieModel) {
                            throw new \Exception("No se pudo crear o encontrar una serie para {$tipo}: " . $e->getMessage());
                        }
                    }
                }
            }

            $numero = $serieModel->obtenerSiguienteNumero();
            $controlFiscal = $serieModel->numero_control_fiscal;

            return [
                'serie_id' => $serieModel->id,
                'serie' => $serieModel->serie,
                'numero' => $numero,
                'control_fiscal' => $controlFiscal
            ];
        });
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($pago) {
            if (!$pago->serie || !$pago->numero) {
                try {
                    if (class_exists('\App\Models\Serie')) {
                        $numeracion = self::generarNumero(
                            $pago->tipo_pago,
                            $pago->empresa_id,
                            $pago->sucursal_id,
                            $pago->serie_id
                        );
                        $pago->serie_id = $numeracion['serie_id'];
                        $pago->serie = $numeracion['serie'];
                        $pago->numero = $numeracion['numero'];

                        if (!$pago->numero_control_fiscal && $pago->es_factura_fiscal 
                            && in_array($pago->tipo_pago, [self::TIPO_FACTURA, self::TIPO_NOTA_CREDITO, self::TIPO_NOTA_DEBITO])) {
                            $pago->numero_control_fiscal = $numeracion['control_fiscal'];
                        }
                    } else {
                        $pago->serie = 'R001';
                        $pago->numero = 1;
                    }
                } catch (\Exception $e) {
                    $pago->serie = 'R001';
                    $pago->numero = 1;
                }
            }
        });

        static::created(function ($pago) {
            try {
                $auditService = app(\App\Services\Audit\AuditService::class);
                $action = match($pago->tipo_pago) {
                    self::TIPO_NOTA_CREDITO => 'pago.nota_credito.created',
                    self::TIPO_NOTA_DEBITO => 'pago.nota_debito.created',
                    default => 'pago.created'
                };
                $auditService->logModelEvent($action, $pago, [], $pago->getAttributes());
            } catch (\Exception $e) {
                \Log::error('Error en auditoría de pago creado: ' . $e->getMessage());
            }
        });

        static::updating(function ($pago) {
            $pago->_oldAttributes = $pago->getOriginal();
        });

        static::updated(function ($pago) {
            try {
                $auditService = app(\App\Services\Audit\AuditService::class);
                $oldAttributes = $pago->_oldAttributes ?? [];
                $newAttributes = $pago->getAttributes();
                
                $action = 'pago.updated';
                if (isset($oldAttributes['estado']) && $oldAttributes['estado'] !== $newAttributes['estado']) {
                    $action = "pago.estado.changed.{$oldAttributes['estado']}_to_{$newAttributes['estado']}";
                }
                
                $auditService->logModelEvent($action, $pago, $oldAttributes, $newAttributes);
            } catch (\Exception $e) {
                \Log::error('Error en auditoría de pago actualizado: ' . $e->getMessage());
            }
        });

        static::deleted(function ($pago) {
            try {
                $auditService = app(\App\Services\Audit\AuditService::class);
                $auditService->logModelEvent('pago.deleted', $pago, $pago->getAttributes(), []);
            } catch (\Exception $e) {
                \Log::error('Error en auditoría de pago eliminado: ' . $e->getMessage());
            }
        });

        static::saved(function ($pago) {
            if ($pago->consulta_id && $pago->estado === self::ESTADO_APROBADO) {
                $consulta = $pago->consulta;
                if ($consulta && $consulta->estado === Consulta::ESTADO_FINALIZADA) {
                    $consulta->cambiarEstado('pagada');
                }
            }
        });
    }

    public function calcularTotales()
    {
        $subtotal = $this->detalles()->sum('subtotal');
        $total = $subtotal - $this->descuento;

        $tasaUSD = ExchangeRate::getLatestRate('USD') ?? $this->tasa_cambio_usd ?? 1;

        $totalUSD = 0;
        $totalBS = 0;
        $aplicaIGTF = false;

        if ($this->es_pago_mixto && $this->detalles_pago_mixto) {
            foreach ($this->detalles_pago_mixto as $detalle) {
                $metodo = $detalle['metodo'];

                if (in_array($metodo, ['efectivo_usd', 'transferencia_usd', 'zelle', 'paypal'])) {
                    $montoUSD = $detalle['monto_usd'] ?? 0;
                    $totalUSD += $montoUSD;
                    $totalBS += $montoUSD * $tasaUSD;
                    $aplicaIGTF = true;
                }

                if (in_array($metodo, ['efectivo_bs', 'transferencia_bs', 'pago_movil'])) {
                    $montoBS = $detalle['monto_bs'] ?? 0;
                    $totalBS += $montoBS;
                    $totalUSD += $montoBS / $tasaUSD;
                }
            }
        } else {
            switch ($this->metodo_pago) {
                case 'efectivo_bs':
                case 'transferencia_bs':
                case 'pago_movil':
                    $totalBS = $total * $tasaUSD;
                    $totalUSD = $total;
                    break;

                case 'efectivo_usd':
                case 'transferencia_usd':
                case 'zelle':
                case 'paypal':
                    $totalUSD = $total;
                    $totalBS = $total * $tasaUSD;
                    $aplicaIGTF = true;
                    break;
            }
        }

        $updateData = [
            'subtotal' => $subtotal,
            'total' => $total,
            'tasa_cambio_usd' => $tasaUSD,
            'total_usd' => $totalUSD,
            'total_bs' => $totalBS,
            'aplica_igtf' => $aplicaIGTF,
        ];

        // Calcular datos fiscales si es factura fiscal
        if ($this->es_factura_fiscal) {
            $fiscal = FiscalCalculator::calcular($this);
            
            $updateData = array_merge($updateData, [
                'base_imponible' => $fiscal['base_imponible'],
                'monto_exento' => $fiscal['monto_exento'],
                'base_imponible_general' => $fiscal['base_imponible_general'],
                'iva_monto_general' => $fiscal['iva_monto_general'],
                'base_imponible_reducida' => $fiscal['base_imponible_reducida'],
                'iva_monto_reducida' => $fiscal['iva_monto_reducida'],
                'iva_porcentaje' => $fiscal['iva_porcentaje'],
                'iva_monto' => $fiscal['iva_monto'],
                'igtf_porcentaje' => $fiscal['igtf_porcentaje'],
                'igtf_monto' => $fiscal['igtf_monto'],
                'aplica_igtf' => $fiscal['aplica_igtf'],
                'total_con_impuestos' => $fiscal['total_con_impuestos'],
                'seniat_tipo_documento' => $fiscal['seniat_tipo_documento'] ?? $this->getSeniatTipoDocumentoCode(),
            ]);

            // Actualizar totales en USD y BS con impuestos incluidos
            $totalConImpuestos = $fiscal['total_con_impuestos'];
            if ($this->es_pago_mixto && $this->detalles_pago_mixto) {
                // Para pagos mixtos, mantener la distribución original pero ajustar proporcionalmente
                $factor = $totalConImpuestos / $total;
                $updateData['total_usd'] = $totalUSD * $factor;
                $updateData['total_bs'] = $totalBS * $factor;
            } else {
                switch ($this->metodo_pago) {
                    case 'efectivo_bs':
                    case 'transferencia_bs':
                    case 'pago_movil':
                        $updateData['total_bs'] = $totalConImpuestos * $tasaUSD;
                        $updateData['total_usd'] = $totalConImpuestos;
                        break;
                    case 'efectivo_usd':
                    case 'transferencia_usd':
                    case 'zelle':
                    case 'paypal':
                        $updateData['total_usd'] = $totalConImpuestos;
                        $updateData['total_bs'] = $totalConImpuestos * $tasaUSD;
                        break;
                }
            }
        } else {
            // Para documentos no fiscales, solo calcular IGTF si aplica
            $fiscal = FiscalCalculator::calcular($this);
            $updateData = array_merge($updateData, [
                'igtf_porcentaje' => $fiscal['igtf_porcentaje'],
                'igtf_monto' => $fiscal['igtf_monto'],
                'aplica_igtf' => $fiscal['aplica_igtf'],
            ]);
        }

        $this->updateQuietly($updateData);
    }

    public function getSeniatTipoDocumentoCode(): string
    {
        return FiscalCalculator::getSeniatTipoDocumento($this->tipo_pago);
    }

    public function getSaldoDisponibleAttribute(): float
    {
        $notasCredito = $this->notasCredito()->where('estado', self::ESTADO_APROBADO)->sum('total');
        $notasDebito = $this->notasDebito()->where('estado', self::ESTADO_APROBADO)->sum('total');
        return $this->total - $notasCredito + $notasDebito;
    }

    public function getAuditTrail()
    {
        return $this->auditLogs()->orderBy('created_at', 'desc')->get();
    }
}
