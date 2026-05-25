<?php

namespace App\Livewire\Admin\Seniat;

use App\Traits\HasDynamicLayout;
use Livewire\Component;
use App\Models\Compra;
use App\Models\Empresa;
use App\Services\Seniat\FiscalCalculator;
use Livewire\Attributes\Computed;

class LibroCompras extends Component
{
    use HasDynamicLayout;

    public $desde;
    public $hasta;
    public $tipo_documento = '';
    public $proveedor_id = '';
    public $estado = '';

    protected $queryString = [
        'tipo_documento' => ['except' => ''],
        'proveedor_id' => ['except' => ''],
        'estado' => ['except' => ''],
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
        $this->reset(['tipo_documento', 'proveedor_id', 'estado']);
        $this->desde = now()->startOfMonth()->format('Y-m-d');
        $this->hasta = now()->endOfMonth()->format('Y-m-d');
    }

    #[Computed]
    public function documentos()
    {
        $this->validate();

        return Compra::where('empresa_id', auth()->user()->empresa_id)
            ->where('es_documento_fiscal', true)
            ->whereIn('tipo_documento', ['factura', 'nota_credito', 'nota_debito'])
            ->where('estado', 'aprobado')
            ->whereBetween('fecha', [$this->desde, $this->hasta])
            ->when($this->tipo_documento, fn($q) => $q->where('tipo_documento', $this->tipo_documento))
            ->when($this->proveedor_id, fn($q) => $q->where('proveedor_id', $this->proveedor_id))
            ->with(['proveedor', 'compraOrigen', 'user'])
            ->orderBy('fecha')
            ->orderBy('numero_control_fiscal')
            ->get();
    }

    #[Computed]
    public function totales()
    {
        $documentos = $this->documentos;

        return [
            'base_imponible' => $documentos->sum(function ($doc) {
                return $doc->tipo_documento === 'nota_credito' ? -$doc->base_imponible : $doc->base_imponible;
            }),
            'iva_monto' => $documentos->sum(function ($doc) {
                return $doc->tipo_documento === 'nota_credito' ? -$doc->iva_monto : $doc->iva_monto;
            }),
            'monto_exento' => $documentos->sum(function ($doc) {
                return $doc->tipo_documento === 'nota_credito' ? -$doc->monto_exento : $doc->monto_exento;
            }),
            'total' => $documentos->sum(function ($doc) {
                return $doc->tipo_documento === 'nota_credito' ? -((float) $doc->total_con_impuestos) : ((float) $doc->total_con_impuestos);
            }),
            'igtf_monto' => $documentos->sum(function ($doc) {
                return $doc->tipo_documento === 'nota_credito' ? -$doc->igtf_monto : $doc->igtf_monto;
            }),
            'iva_retenido' => $documentos->sum('iva_retenido'),
        ];
    }

    #[Computed]
    public function stats()
    {
        $documentos = $this->documentos;
        
        return [
            'total_documentos' => $documentos->count(),
            'facturas' => $documentos->where('tipo_documento', 'factura')->count(),
            'notas_credito' => $documentos->where('tipo_documento', 'nota_credito')->count(),
            'notas_debito' => $documentos->where('tipo_documento', 'nota_debito')->count(),
            'proveedores_unicos' => $documentos->pluck('proveedor_id')->unique()->count(),
        ];
    }

    #[Computed]
    public function proveedores()
    {
        return \App\Models\Proveedor::where('empresa_id', auth()->user()->empresa_id)
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();
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

            // Tipo operación: C = Compra
            $tipoOperacion = 'C';

            // Tipo documento: 01=Factura, 02=ND, 03=NC
            $tipoDoc = $doc->seniat_tipo_documento ?? FiscalCalculator::getSeniatTipoDocumento($doc->tipo_documento);

            // RIF proveedor
            $rifProveedor = '0';
            if ($doc->proveedor) {
                $rifProveedor = str_replace('-', '', $doc->proveedor->tipo_documento . $doc->proveedor->numero_documento);
            }

            $numDoc = $doc->numero_completo ?? '0';
            $numControl = $doc->numero_control_fiscal ?? '0';
            $montoDoc = number_format($doc->total_con_impuestos ?? $doc->total, 2, '.', '');
            $baseImponible = number_format($doc->base_imponible ?? 0, 2, '.', '');
            $montoIva = number_format($doc->iva_monto ?? 0, 2, '.', '');

            // Documento afectado (para NC/ND)
            $docAfectado = '0';
            $controlAfectado = '0';
            $fechaAfectado = '0';
            if ($doc->compraOrigen) {
                $docAfectado = $doc->compraOrigen->numero_completo ?? '0';
                $controlAfectado = $doc->compraOrigen->numero_control_fiscal ?? '0';
                $fechaAfectado = $doc->compraOrigen->fecha ? $doc->compraOrigen->fecha->format('Y-m-d') : '0';
            }

            $montoExento = number_format($doc->monto_exento ?? 0, 2, '.', '');
            $alicuota = number_format($doc->iva_porcentaje ?? 16, 2, '.', '');
            $ivaRetenido = number_format($doc->iva_retenido ?? 0, 2, '.', '');

            // Format: RIF|Periodo|Fecha|TipoOp|TipoDoc|RifProveedor|NumDoc|NumControl|MontoDoc|BaseImp|MontoIVA|DocAfectado|ControlAfectado|FechaAfectado|MontoExento|Alicuota|IVARetenido
            $lines[] = implode("\t", [
                $rifContribuyente,
                $periodo,
                $fechaDoc,
                $tipoOperacion,
                $tipoDoc,
                $rifProveedor,
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
                $ivaRetenido,
            ]);
        }

        $content = implode("\n", $lines);
        $filename = 'libro_compras_' . str_replace('-', '', $this->desde) . '_' . str_replace('-', '', $this->hasta) . '.txt';

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

        return redirect()->route('admin.seniat.libro-compras.excel', [
            'desde' => $this->desde,
            'hasta' => $this->hasta,
            'tipo_documento' => $this->tipo_documento,
            'proveedor_id' => $this->proveedor_id,
        ]);
    }

    protected function getPageTitle(): string
    {
        return 'Libro de Compras SENIAT';
    }

    protected function getBreadcrumb(): array
    {
        return [
            'admin.dashboard' => 'Dashboard',
            'admin.seniat.libro-compras' => 'Libro de Compras SENIAT'
        ];
    }

    public function render()
    {
        return view('livewire.admin.seniat.libro-compras', [
            'documentos' => $this->documentos,
            'totales' => $this->totales,
            'stats' => $this->stats,
            'proveedores' => $this->proveedores,
        ])->layout($this->getLayout());
    }
}