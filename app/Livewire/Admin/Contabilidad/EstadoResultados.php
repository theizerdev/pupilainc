<?php

namespace App\Livewire\Admin\Contabilidad;

use App\Traits\HasDynamicLayout;
use Livewire\Component;
use App\Models\CuentaContable;
use App\Models\AsientoDetalle;

class EstadoResultados extends Component
{
    use HasDynamicLayout;

    public $fecha_desde;
    public $fecha_hasta;

    public function mount()
    {
        $this->authorize('access contabilidad');
        $this->fecha_desde = now()->startOfYear()->format('Y-m-d');
        $this->fecha_hasta = now()->format('Y-m-d');
    }

    public function resetFilters()
    {
        $this->fecha_desde = now()->startOfYear()->format('Y-m-d');
        $this->fecha_hasta = now()->format('Y-m-d');
    }

    private function getCuentasPorTipo($tipo)
    {
        return CuentaContable::where('empresa_id', auth()->user()->empresa_id)
            ->where('tipo', $tipo)
            ->where('acepta_movimientos', true)
            ->where('activo', true)
            ->orderBy('codigo')
            ->get()
            ->map(function ($cuenta) {
                $query = AsientoDetalle::where('cuenta_id', $cuenta->id)
                    ->whereHas('asiento', fn($q) => $q->where('estado', 'aprobado')
                        ->whereBetween('fecha', [$this->fecha_desde, $this->fecha_hasta]));
                $debe = (float) $query->sum('debe');
                $haber = (float) (clone $query)->sum('haber');
                return (object) [
                    'codigo' => $cuenta->codigo,
                    'nombre' => $cuenta->nombre,
                    'saldo' => $cuenta->naturaleza === 'deudora' ? ($debe - $haber) : ($haber - $debe),
                ];
            })
            ->filter(fn($c) => abs($c->saldo) > 0.01);
    }

    public function render()
    {
        $ingresos = $this->getCuentasPorTipo('ingreso');
        $costos = $this->getCuentasPorTipo('costo');
        $egresos = $this->getCuentasPorTipo('egreso');

        $totalIngresos = $ingresos->sum('saldo');
        $totalCostos = $costos->sum('saldo');
        $utilidadBruta = $totalIngresos - $totalCostos;
        $totalEgresos = $egresos->sum('saldo');
        $utilidadNeta = $utilidadBruta - $totalEgresos;

        return view('livewire.admin.contabilidad.estado-resultados', compact(
            'ingresos', 'costos', 'egresos',
            'totalIngresos', 'totalCostos', 'utilidadBruta',
            'totalEgresos', 'utilidadNeta'
        ))->layout($this->getLayout());
    }
}
