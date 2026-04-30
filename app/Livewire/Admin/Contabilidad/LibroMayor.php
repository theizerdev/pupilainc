<?php

namespace App\Livewire\Admin\Contabilidad;

use App\Traits\HasDynamicLayout;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\CuentaContable;
use App\Models\AsientoDetalle;
use App\Models\AsientoContable;

class LibroMayor extends Component
{
    use HasDynamicLayout, WithPagination;

    public $cuenta_id;
    public $fecha_desde;
    public $fecha_hasta;
    public $tipo_cuenta = '';
    public $search = '';
    public $mostrar_saldos_cero = false;
    public $perPage = 50;

    protected $queryString = [
        'cuenta_id' => ['except' => null],
        'tipo_cuenta' => ['except' => ''],
        'search' => ['except' => ''],
    ];

    protected $paginationTheme = 'bootstrap';

    public function mount()
    {
        $this->authorize('access contabilidad');
        $this->fecha_desde = now()->startOfMonth()->format('Y-m-d');
        $this->fecha_hasta = now()->endOfMonth()->format('Y-m-d');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingTipoCuenta()
    {
        $this->resetPage();
        $this->cuenta_id = null;
    }

    public function resetFilters()
    {
        $this->reset(['cuenta_id', 'tipo_cuenta', 'search', 'mostrar_saldos_cero']);
        $this->fecha_desde = now()->startOfMonth()->format('Y-m-d');
        $this->fecha_hasta = now()->endOfMonth()->format('Y-m-d');
        $this->resetPage();
    }

    public function seleccionarCuenta($cuentaId)
    {
        $this->cuenta_id = $cuentaId;
        $this->resetPage();
    }

    public function getCuentasProperty()
    {
        return CuentaContable::where('empresa_id', auth()->user()->empresa_id)
            ->where('acepta_movimientos', true)
            ->where('activo', true)
            ->when($this->tipo_cuenta, fn($q) => $q->where('tipo', $this->tipo_cuenta))
            ->when($this->search, fn($q) => $q->where('codigo', 'like', "%{$this->search}%")
                ->orWhere('nombre', 'like', "%{$this->search}%"))
            ->orderBy('codigo')
            ->get()
            ->filter(function($cuenta) {
                if ($this->mostrar_saldos_cero) {
                    return true;
                }
                return abs($this->calcularSaldoCuenta($cuenta)) > 0.01;
            });
    }

    public function getMovimientosProperty()
    {
        if (!$this->cuenta_id) {
            return collect();
        }

        $cuentaSeleccionada = CuentaContable::find($this->cuenta_id);
        if (!$cuentaSeleccionada) {
            return collect();
        }

        $saldoInicial = $this->getSaldoInicialProperty();
        $saldoActual = $saldoInicial;

        return AsientoDetalle::where('cuenta_id', $this->cuenta_id)
            ->whereHas('asiento', fn($q) => $q->where('estado', 'aprobado')
                ->whereBetween('fecha', [$this->fecha_desde, $this->fecha_hasta]))
            ->with(['asiento'])
            ->get()
            ->sortBy('asiento.fecha')
            ->values()
            ->map(function ($detalle) use (&$saldoActual, $cuentaSeleccionada) {
                $debe = (float) $detalle->debe;
                $haber = (float) $detalle->haber;
                $saldoActual += $cuentaSeleccionada->naturaleza === 'deudora' ? ($debe - $haber) : ($haber - $debe);
                return (object) [
                    'id' => $detalle->id,
                    'fecha' => $detalle->asiento->fecha,
                    'numero' => $detalle->asiento->numero,
                    'tipo' => $detalle->asiento->tipo,
                    'descripcion' => $detalle->descripcion ?: $detalle->asiento->descripcion,
                    'referencia' => $detalle->asiento->referencia_tipo,
                    'debe' => $debe,
                    'haber' => $haber,
                    'saldo' => $saldoActual,
                    'asiento_id' => $detalle->asiento->id,
                ];
            });
    }

    public function getSaldoInicialProperty()
    {
        if (!$this->cuenta_id) {
            return 0;
        }

        $cuentaSeleccionada = CuentaContable::find($this->cuenta_id);
        if (!$cuentaSeleccionada) {
            return 0;
        }

        $queryInicial = AsientoDetalle::where('cuenta_id', $this->cuenta_id)
            ->whereHas('asiento', fn($q) => $q->where('estado', 'aprobado')
                ->whereDate('fecha', '<', $this->fecha_desde));
        
        $debe = (float) $queryInicial->sum('debe');
        $haber = (float) (clone $queryInicial)->sum('haber');
        
        return $cuentaSeleccionada->naturaleza === 'deudora' ? ($debe - $haber) : ($haber - $debe);
    }

    public function getCuentaSeleccionadaProperty()
    {
        return $this->cuenta_id ? CuentaContable::find($this->cuenta_id) : null;
    }

    public function getResumenProperty()
    {
        if (!$this->cuenta_id) {
            return null;
        }

        $movimientos = $this->movimientos;
        $saldoInicial = $this->saldoInicial;
        $totalDebe = $movimientos->sum('debe');
        $totalHaber = $movimientos->sum('haber');
        $saldoFinal = $movimientos->last()?->saldo ?? $saldoInicial;

        return [
            'saldo_inicial' => $saldoInicial,
            'total_debe' => $totalDebe,
            'total_haber' => $totalHaber,
            'saldo_final' => $saldoFinal,
            'total_movimientos' => $movimientos->count(),
        ];
    }

    public function getStatsProperty()
    {
        $empresaId = auth()->user()->empresa_id;
        
        return [
            'total_cuentas' => CuentaContable::where('empresa_id', $empresaId)
                ->where('acepta_movimientos', true)
                ->where('activo', true)
                ->count(),
            'cuentas_con_movimientos' => CuentaContable::where('empresa_id', $empresaId)
                ->where('acepta_movimientos', true)
                ->where('activo', true)
                ->whereHas('asientosDetalles.asiento', fn($q) => 
                    $q->where('estado', 'aprobado')
                      ->whereBetween('fecha', [$this->fecha_desde, $this->fecha_hasta])
                )->count(),
            'total_asientos' => AsientoContable::where('empresa_id', $empresaId)
                ->where('estado', 'aprobado')
                ->whereBetween('fecha', [$this->fecha_desde, $this->fecha_hasta])
                ->count(),
        ];
    }

    private function calcularSaldoCuenta($cuenta)
    {
        $debe = AsientoDetalle::where('cuenta_id', $cuenta->id)
            ->whereHas('asiento', fn($q) => $q->where('estado', 'aprobado'))
            ->sum('debe');
        
        $haber = AsientoDetalle::where('cuenta_id', $cuenta->id)
            ->whereHas('asiento', fn($q) => $q->where('estado', 'aprobado'))
            ->sum('haber');

        return $cuenta->naturaleza === 'deudora' ? ($debe - $haber) : ($haber - $debe);
    }

    protected function getPageTitle(): string
    {
        return 'Libro Mayor';
    }

    protected function getBreadcrumb(): array
    {
        return [
            'admin.dashboard' => 'Dashboard',
            'admin.contabilidad.libro-mayor' => 'Libro Mayor'
        ];
    }

    public function render()
    {
        return view('livewire.admin.contabilidad.libro-mayor', [
            'cuentas' => $this->cuentas,
            'movimientos' => $this->movimientos,
            'cuentaSeleccionada' => $this->cuentaSeleccionada,
            'saldoInicial' => $this->saldoInicial,
            'resumen' => $this->resumen,
            'stats' => $this->stats,
        ])->layout($this->getLayout());
    }
}
