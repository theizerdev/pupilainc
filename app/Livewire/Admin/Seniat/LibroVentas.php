<?php

namespace App\Livewire\Admin\Seniat;

use App\Traits\HasDynamicLayout;
use Livewire\Component;
use App\Models\Pago;
use App\Models\Empresa;
use App\Services\Seniat\FiscalCalculator;

class LibroVentas extends Component
{
    use HasDynamicLayout;

    public $desde;
    public $hasta;
    public $tipo_documento = '';
    public $estado_fiscal = '';

    protected $queryString = [
        'tipo_documento' => ['except' => ''],
        'estado_fiscal' => ['except' => ''],
    ];

    public function mount()
    {
        $this->authorize('access pagos');
        $this->desde = now()->startOfMonth()->format('Y-m-d');
        $this->hasta = now()->endOfMonth()->format('Y-m-d');
    }

    public function updatingDesde()
    {
        $this->resetValidation();
    }

    public function updatingHasta()
    {
        $this->resetValidation();
    }

    protected function rules()
    {
        return [
            'desde' => 'required|date',
            'hasta' => 'required|date|after_or_equal:desde',
        ];
    }

    public function resetFilters()
    {
        $this->reset(['tipo_documento', 'estado_fiscal']);
        $this->desde = now()->startOfMonth()->format('Y-m-d');
        $this->hasta = now()->endOfMonth()->format('Y-m-d');
    }

    public function getDocumentosProperty()
    {
        $this->validate();

        return Pago::where('empresa_id', auth()->user()->empresa_id)
            ->where('es_factura_fiscal', true)
            ->whereIn('tipo_pago', ['factura', 'nota_credito', 'nota_debito'])
            ->where('estado', 'aprobado')
            ->whereBetween('fecha', [$this->desde, $this->hasta])
            ->when($this->tipo_documento, fn($q) => $q->where('tipo_pago', $this->tipo_documento))
            ->with(['clienteFiscal', 'pagoOrigen', 'consulta.paciente', 'caja', 'user'])
            ->orderBy('fecha')
            ->orderBy('numero_control_fiscal')
            ->get();
    }

    public function getTotalesProperty()
    {
        $documentos = $this->documentos;
        $isVenezuela = is_venezuela_company();

        return [
            'base_imponible' => $documentos->sum(function ($doc) use ($isVenezuela) {
                $signo = $doc->tipo_pago === 'nota_credito' ? -1 : 1;
                $usd = $signo * (float) ($doc->base_imponible ?? 0);
                return $isVenezuela ? $usd * (float) ($doc->tasa_cambio_usd ?: 1) : $usd;
            }),
            'iva_monto' => $documentos->sum(function ($doc) use ($isVenezuela) {
                $signo = $doc->tipo_pago === 'nota_credito' ? -1 : 1;
                $usd = $signo * (float) ($doc->iva_monto ?? 0);
                return $isVenezuela ? $usd * (float) ($doc->tasa_cambio_usd ?: 1) : $usd;
            }),
            'monto_exento' => $documentos->sum(function ($doc) use ($isVenezuela) {
                $signo = $doc->tipo_pago === 'nota_credito' ? -1 : 1;
                $usd = $signo * (float) ($doc->monto_exento ?? 0);
                return $isVenezuela ? $usd * (float) ($doc->tasa_cambio_usd ?: 1) : $usd;
            }),
            'total' => $documentos->sum(function ($doc) use ($isVenezuela) {
                $signo = $doc->tipo_pago === 'nota_credito' ? -1 : 1;
                $usd = $signo * (float) ($doc->total_con_impuestos ?: $doc->total_usd ?: $doc->total);
                return $isVenezuela ? $usd * (float) ($doc->tasa_cambio_usd ?: 1) : $usd;
            }),
            'igtf_monto' => $documentos->sum(function ($doc) use ($isVenezuela) {
                $signo = $doc->tipo_pago === 'nota_credito' ? -1 : 1;
                $usd = $signo * (float) ($doc->igtf_monto ?? 0);
                return $isVenezuela ? $usd * (float) ($doc->tasa_cambio_usd ?: 1) : $usd;
            }),
        ];
    }

    public function getStatsProperty()
    {
        $documentos = $this->documentos;

        return [
            'total_documentos' => $documentos->count(),
            'facturas' => $documentos->where('tipo_pago', 'factura')->count(),
            'notas_credito' => $documentos->where('tipo_pago', 'nota_credito')->count(),
            'notas_debito' => $documentos->where('tipo_pago', 'nota_debito')->count(),
        ];
    }

    public function exportarTxt()
    {
        $this->validate();

        $empresa = Empresa::find(auth()->user()->empresa_id);
        $documentos = $this->documentos;

        $lines = [];

        foreach ($documentos as $doc) {
            $rifContribuyente = str_replace('-', '', $empresa->rif_fiscal ?? $empresa->documento ?? '');
            $periodo = $doc->fecha->format('Ym');
            $fechaDoc = $doc->fecha->format('Y-m-d');

            // Tipo operación: V = Venta
            $tipoOperacion = 'V';

            // Tipo documento: 01=Factura, 02=ND, 03=NC
            $tipoDoc = $doc->seniat_tipo_documento ?? FiscalCalculator::getSeniatTipoDocumento($doc->tipo_pago);

            // RIF comprador
            $rifComprador = '0';
            if ($doc->clienteFiscal) {
                $rifComprador = str_replace('-', '', $doc->clienteFiscal->tipo_documento . $doc->clienteFiscal->numero_documento);
            }

            $numDoc = $doc->numero_completo ?? '0';
            $numControl = $doc->numero_control_fiscal ?? '0';
            $montoDoc = number_format($doc->total_con_impuestos ?? $doc->total_usd, 2, '.', '');
            $baseImponible = number_format($doc->base_imponible ?? 0, 2, '.', '');
            $montoIva = number_format($doc->iva_monto ?? 0, 2, '.', '');

            // Documento afectado (para NC/ND)
            $docAfectado = '0';
            $controlAfectado = '0';
            $fechaAfectado = '0';
            if ($doc->pagoOrigen) {
                $docAfectado = $doc->pagoOrigen->numero_completo ?? '0';
                $controlAfectado = $doc->pagoOrigen->numero_control_fiscal ?? '0';
                $fechaAfectado = $doc->pagoOrigen->fecha ? $doc->pagoOrigen->fecha->format('Y-m-d') : '0';
            }

            $montoExento = number_format($doc->monto_exento ?? 0, 2, '.', '');
            $alicuota = number_format($doc->iva_porcentaje ?? 16, 2, '.', '');
            $igtfMonto = number_format($doc->igtf_monto ?? 0, 2, '.', '');

            // Format: RIF|Periodo|Fecha|TipoOp|TipoDoc|RifComprador|NumDoc|NumControl|MontoDoc|BaseImp|MontoIVA|DocAfectado|ControlAfectado|FechaAfectado|MontoExento|Alicuota|IGTF
            $lines[] = implode("\t", [
                $rifContribuyente,
                $periodo,
                $fechaDoc,
                $tipoOperacion,
                $tipoDoc,
                $rifComprador,
                $numDoc,
                $numControl,
                $montoDoc,
                $baseImponible,
                $montoIva,
                $docAfectado,
                $controlAfectado,
                $fechaAfectado,
                $montoExento,
                $alicuota,
                $igtfMonto,
            ]);
        }

        $content = implode("\n", $lines);
        $filename = 'libro_ventas_' . str_replace('-', '', $this->desde) . '_' . str_replace('-', '', $this->hasta) . '.txt';

        $this->dispatch('download-file', [
            'content' => $content,
            'filename' => $filename,
            'contentType' => 'text/plain'
        ]);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Archivo TXT generado exitosamente',
        ]);
    }

    public function exportarExcel()
    {
        $this->validate();

        return redirect()->route('admin.seniat.libro-ventas.excel', [
            'desde' => $this->desde,
            'hasta' => $this->hasta,
            'tipo_documento' => $this->tipo_documento
        ]);
    }

    public function recalcularDocumentos()
    {
        $this->authorize('access pagos');
        $this->validate();

        $docs = Pago::where('empresa_id', auth()->user()->empresa_id)
            ->where('es_factura_fiscal', true)
            ->whereIn('tipo_pago', ['factura', 'nota_credito', 'nota_debito'])
            ->where('estado', 'aprobado')
            ->whereBetween('fecha', [$this->desde, $this->hasta])
            ->where(function ($q) {
                $q->where('total_con_impuestos', 0)
                  ->where('total', 0);
            })
            ->get();

        $recalculados = 0;
        foreach ($docs as $doc) {
            try {
                $doc->calcularTotales();
                $recalculados++;
            } catch (\Exception $e) {
                \Log::error("Error recalculando totales del pago #{$doc->id}: " . $e->getMessage());
            }
        }

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => $recalculados > 0
                ? "{$recalculados} documento(s) recalculado(s) correctamente."
                : 'No se encontraron documentos con datos fiscales incompletos en el período seleccionado.',
        ]);
    }

    protected function getPageTitle(): string
    {
        return 'Libro de Ventas SENIAT';
    }

    protected function getBreadcrumb(): array
    {
        return [
            'admin.dashboard' => 'Dashboard',
            'admin.seniat.libro-ventas' => 'Libro de Ventas SENIAT'
        ];
    }

    public function render()
    {
        return view('livewire.admin.seniat.libro-ventas', [
            'documentos' => $this->documentos,
            'totales' => $this->totales,
            'stats' => $this->stats,
        ])->layout($this->getLayout());
    }
}
