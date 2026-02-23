<?php

namespace App\Livewire\Admin\Contabilidad;

use App\Traits\HasDynamicLayout;
use Livewire\Component;
use App\Models\CuentaContable;
use App\Models\AsientoDetalle;

class BalanceGeneral extends Component
{
    use HasDynamicLayout;

    public $fecha_corte;

    public function mount()
    {
        $this->authorize('access contabilidad');
        $this->fecha_corte = now()->format('Y-m-d');
    }

    public function resetFilters()
    {
        $this->fecha_corte = now()->format('Y-m-d');
    }

    private function getSaldoCuenta($cuenta, $fechaCorte)
    {
        $query = AsientoDetalle::where('cuenta_id', $cuenta->id)
            ->whereHas('asiento', fn($q) => $q->where('estado', 'aprobado')->whereDate('fecha', '<=', $fechaCorte));
        $debe = (float) $query->sum('debe');
        $haber = (float) (clone $query)->sum('haber');
        return $cuenta->naturaleza === 'deudora' ? ($debe - $haber) : ($haber - $debe);
    }

    private function getCuentasPorTipo($tipo, $fechaCorte)
    {
        return CuentaContable::where('empresa_id', auth()->user()->empresa_id)
            ->where('tipo', $tipo)
            ->where('acepta_movimientos', true)
            ->where('activo', true)
            ->orderBy('codigo')
            ->get()
            ->map(fn($cuenta) => (object) [
                'codigo' => $cuenta->codigo,
                'nombre' => $cuenta->nombre,
                'saldo' => $this->getSaldoCuenta($cuenta, $fechaCorte),
            ])
            ->filter(fn($c) => abs($c->saldo) > 0.01);
    }

    public function render()
    {
        $activos = $this->getCuentasPorTipo('activo', $this->fecha_corte);
        $pasivos = $this->getCuentasPorTipo('pasivo', $this->fecha_corte);
        $patrimonio = $this->getCuentasPorTipo('patrimonio', $this->fecha_corte);
        $ingresos = $this->getCuentasPorTipo('ingreso', $this->fecha_corte);
        $egresos = $this->getCuentasPorTipo('egreso', $this->fecha_corte);
        $costos = $this->getCuentasPorTipo('costo', $this->fecha_corte);

        $resultadoEjercicio = $ingresos->sum('saldo') - $egresos->sum('saldo') - $costos->sum('saldo');
        $totalActivos = $activos->sum('saldo');
        $totalPasivos = $pasivos->sum('saldo');
        $totalPatrimonio = $patrimonio->sum('saldo') + $resultadoEjercicio;

        return view('livewire.admin.contabilidad.balance-general', compact(
            'activos', 'pasivos', 'patrimonio',
            'totalActivos', 'totalPasivos', 'totalPatrimonio', 'resultadoEjercicio'
        ))->layout($this->getLayout());
    }
}
