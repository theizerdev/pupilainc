<?php

namespace App\Livewire\Admin\Contabilidad;

use App\Traits\HasDynamicLayout;
use Livewire\Component;
use App\Models\CuentaContable;
use App\Models\AsientoDetalle;
use Carbon\Carbon;
use Livewire\Attributes\Computed;

class EstadoResultados extends Component
{
    use HasDynamicLayout;

    public $fecha_desde;
    public $fecha_hasta;
    public $mostrar_cuentas_cero = false;
    public $agrupar_por_categoria = true;
    public $comparativo = false;
    public $periodo_comparativo = 'anio_anterior';
    public $fecha_desde_comp;
    public $fecha_hasta_comp;

    protected $queryString = [
        'mostrar_cuentas_cero' => ['except' => false],
        'agrupar_por_categoria' => ['except' => true],
        'comparativo' => ['except' => false],
        'periodo_comparativo' => ['except' => 'anio_anterior'],
    ];

    public function mount()
    {
        $this->authorize('access contabilidad');
        $this->fecha_desde = now()->startOfYear()->format('Y-m-d');
        $this->fecha_hasta = now()->format('Y-m-d');
        $this->calcularPeriodoComparativo();
    }

    public function updatedPeriodoComparativo()
    {
        $this->calcularPeriodoComparativo();
    }

    private function calcularPeriodoComparativo()
    {
        $desde = Carbon::parse($this->fecha_desde);
        $hasta = Carbon::parse($this->fecha_hasta);
        
        switch ($this->periodo_comparativo) {
            case 'anio_anterior':
                $this->fecha_desde_comp = $desde->copy()->subYear()->format('Y-m-d');
                $this->fecha_hasta_comp = $hasta->copy()->subYear()->format('Y-m-d');
                break;
            case 'periodo_anterior':
                $dias = $hasta->diffInDays($desde) + 1;
                $this->fecha_hasta_comp = $desde->copy()->subDay()->format('Y-m-d');
                $this->fecha_desde_comp = $desde->copy()->subDays($dias)->format('Y-m-d');
                break;
            case 'mes_anterior':
                $this->fecha_desde_comp = $desde->copy()->subMonth()->startOfMonth()->format('Y-m-d');
                $this->fecha_hasta_comp = $desde->copy()->subMonth()->endOfMonth()->format('Y-m-d');
                break;
        }
    }

    public function resetFilters()
    {
        $this->reset(['mostrar_cuentas_cero', 'agrupar_por_categoria', 'comparativo']);
        $this->fecha_desde = now()->startOfYear()->format('Y-m-d');
        $this->fecha_hasta = now()->format('Y-m-d');
        $this->periodo_comparativo = 'anio_anterior';
        $this->calcularPeriodoComparativo();
    }

    private function getCuentasPorTipo($tipo, $fechaDesde = null, $fechaHasta = null)
    {
        $fechaDesde = $fechaDesde ?? $this->fecha_desde;
        $fechaHasta = $fechaHasta ?? $this->fecha_hasta;
        
        $cuentas = CuentaContable::where('empresa_id', auth()->user()->empresa_id)
            ->where('tipo', $tipo)
            ->where('acepta_movimientos', true)
            ->where('activo', true)
            ->orderBy('codigo')
            ->get()
            ->map(function ($cuenta) use ($tipo, $fechaDesde, $fechaHasta) {
                $query = AsientoDetalle::where('cuenta_id', $cuenta->id)
                    ->whereHas('asiento', fn($q) => $q->where('estado', 'aprobado')
                        ->whereBetween('fecha', [$fechaDesde, $fechaHasta]));
                        
                $debe = (float) $query->sum('debe');
                $haber = (float) (clone $query)->sum('haber');
                $saldo = $cuenta->naturaleza === 'deudora' ? ($debe - $haber) : ($haber - $debe);
                
                // Saldo comparativo si está habilitado
                $saldoComparativo = 0;
                if ($this->comparativo && $fechaDesde === $this->fecha_desde) {
                    $queryComp = AsientoDetalle::where('cuenta_id', $cuenta->id)
                        ->whereHas('asiento', fn($q) => $q->where('estado', 'aprobado')
                            ->whereBetween('fecha', [$this->fecha_desde_comp, $this->fecha_hasta_comp]));
                    $debeComp = (float) $queryComp->sum('debe');
                    $haberComp = (float) (clone $queryComp)->sum('haber');
                    $saldoComparativo = $cuenta->naturaleza === 'deudora' ? ($debeComp - $haberComp) : ($haberComp - $debeComp);
                }
                
                return (object) [
                    'id' => $cuenta->id,
                    'codigo' => $cuenta->codigo,
                    'nombre' => $cuenta->nombre,
                    'categoria' => $this->getCategoria($cuenta->codigo, $tipo),
                    'saldo' => $saldo,
                    'saldo_comparativo' => $saldoComparativo,
                    'variacion' => $saldo - $saldoComparativo,
                    'variacion_porcentaje' => $saldoComparativo != 0 ? 
                        (($saldo - $saldoComparativo) / abs($saldoComparativo)) * 100 : 0,
                ];
            });

        if (!$this->mostrar_cuentas_cero) {
            $cuentas = $cuentas->filter(fn($c) => abs($c->saldo) > 0.01);
        }

        return $cuentas;
    }

    private function getCategoria($codigo, $tipo)
    {
        switch ($tipo) {
            case 'ingreso':
                if (str_starts_with($codigo, '4.1')) return 'Ingresos Operacionales';
                if (str_starts_with($codigo, '4.2')) return 'Otros Ingresos';
                return 'Ingresos';
            case 'costo':
                if (str_starts_with($codigo, '5.1')) return 'Costo de Servicios';
                return 'Costos';
            case 'egreso':
                if (str_starts_with($codigo, '5.2')) return 'Gastos Administrativos';
                if (str_starts_with($codigo, '5.3')) return 'Gastos de Ventas';
                if (str_starts_with($codigo, '5.4')) return 'Gastos Financieros';
                return 'Gastos Operacionales';
        }
        return 'Otros';
    }

    #[Computed]
    public function ingresos()
    {
        return $this->getCuentasPorTipo('ingreso');
    }

    #[Computed]
    public function costos()
    {
        return $this->getCuentasPorTipo('costo');
    }

    #[Computed]
    public function egresos()
    {
        return $this->getCuentasPorTipo('egreso');
    }

    #[Computed]
    public function resultados()
    {
        $totalIngresos = $this->ingresos->sum('saldo');
        $totalCostos = $this->costos->sum('saldo');
        $totalEgresos = $this->egresos->sum('saldo');
        
        $utilidadBruta = $totalIngresos - $totalCostos;
        $utilidadOperacional = $utilidadBruta - $totalEgresos;
        $utilidadNeta = $utilidadOperacional; // Simplificado para clínicas
        
        $resultados = [
            'ingresos' => $totalIngresos,
            'costos' => $totalCostos,
            'egresos' => $totalEgresos,
            'utilidad_bruta' => $utilidadBruta,
            'utilidad_operacional' => $utilidadOperacional,
            'utilidad_neta' => $utilidadNeta,
        ];
        
        if ($this->comparativo) {
            $ingresosComp = $this->ingresos->sum('saldo_comparativo');
            $costosComp = $this->costos->sum('saldo_comparativo');
            $egresosComp = $this->egresos->sum('saldo_comparativo');
            
            $resultados['ingresos_comparativo'] = $ingresosComp;
            $resultados['costos_comparativo'] = $costosComp;
            $resultados['egresos_comparativo'] = $egresosComp;
            $resultados['utilidad_bruta_comparativo'] = $ingresosComp - $costosComp;
            $resultados['utilidad_operacional_comparativo'] = $ingresosComp - $costosComp - $egresosComp;
            $resultados['utilidad_neta_comparativo'] = $ingresosComp - $costosComp - $egresosComp;
        }
        
        return $resultados;
    }

    #[Computed]
    public function indicadores()
    {
        $resultados = $this->resultados;
        
        return [
            'margen_bruto' => $resultados['ingresos'] > 0 ? 
                ($resultados['utilidad_bruta'] / $resultados['ingresos']) * 100 : 0,
            'margen_operacional' => $resultados['ingresos'] > 0 ? 
                ($resultados['utilidad_operacional'] / $resultados['ingresos']) * 100 : 0,
            'margen_neto' => $resultados['ingresos'] > 0 ? 
                ($resultados['utilidad_neta'] / $resultados['ingresos']) * 100 : 0,
            'crecimiento_ingresos' => $this->comparativo && $resultados['ingresos_comparativo'] > 0 ? 
                (($resultados['ingresos'] - $resultados['ingresos_comparativo']) / $resultados['ingresos_comparativo']) * 100 : 0,
        ];
    }

    public function exportarPdf()
    {
        return redirect()->route('admin.contabilidad.estado-resultados.pdf', [
            'desde' => $this->fecha_desde,
            'hasta' => $this->fecha_hasta,
            'comparativo' => $this->comparativo ? 1 : 0,
            'desde_comp' => $this->fecha_desde_comp,
            'hasta_comp' => $this->fecha_hasta_comp,
        ]);
    }

    protected function getPageTitle(): string
    {
        return 'Estado de Resultados';
    }

    protected function getBreadcrumb(): array
    {
        return [
            'admin.dashboard' => 'Dashboard',
            'admin.contabilidad.estado-resultados' => 'Estado de Resultados'
        ];
    }

    public function render()
    {
        return view('livewire.admin.contabilidad.estado-resultados', [
            'ingresos' => $this->ingresos,
            'costos' => $this->costos,
            'egresos' => $this->egresos,
            'resultados' => $this->resultados,
            'indicadores' => $this->indicadores,
        ])->layout($this->getLayout());
    }
}
