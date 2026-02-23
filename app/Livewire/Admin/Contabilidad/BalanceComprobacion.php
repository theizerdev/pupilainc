<?php

namespace App\Livewire\Admin\Contabilidad;

use App\Traits\HasDynamicLayout;
use Livewire\Component;
use App\Models\CuentaContable;
use App\Models\AsientoDetalle;

class BalanceComprobacion extends Component
{
    use HasDynamicLayout;

    public $fecha_desde;
    public $fecha_hasta;

    protected $queryString = [];

    public function mount()
    {
        $this->authorize('access contabilidad');
        $this->fecha_desde = now()->startOfMonth()->format('Y-m-d');
        $this->fecha_hasta = now()->endOfMonth()->format('Y-m-d');
    }

    public function resetFilters()
    {
        $this->fecha_desde = now()->startOfMonth()->format('Y-m-d');
        $this->fecha_hasta = now()->endOfMonth()->format('Y-m-d');
    }

    public function getCuentasProperty()
    {
        return CuentaContable::where('empresa_id', auth()->user()->empresa_id)
            ->where('acepta_movimientos', true)
            ->where('activo', true)
            ->orderBy('codigo')
            ->get()
            ->map(function ($cuenta) {
                $detalles = AsientoDetalle::where('cuenta_id', $cuenta->id)
                    ->whereHas('asiento', fn($q) => $q->where('estado', 'aprobado')
                        ->whereBetween('fecha', [$this->fecha_desde, $this->fecha_hasta]));

                $debe = (float) $detalles->sum('debe');
                $haber = (float) $detalles->sum('haber');
                $saldo = $cuenta->naturaleza === 'deudora' ? ($debe - $haber) : ($haber - $debe);

                return (object) [
                    'codigo' => $cuenta->codigo,
                    'nombre' => $cuenta->nombre,
                    'tipo' => $cuenta->tipo,
                    'debe' => $debe,
                    'haber' => $haber,
                    'saldo_deudor' => $saldo >= 0 ? $saldo : 0,
                    'saldo_acreedor' => $saldo < 0 ? abs($saldo) : 0,
                ];
            })
            ->filter(fn($c) => $c->debe > 0 || $c->haber > 0);
    }

    public function getTotalesProperty()
    {
        $cuentas = $this->cuentas;
        return (object) [
            'debe' => $cuentas->sum('debe'),
            'haber' => $cuentas->sum('haber'),
            'saldo_deudor' => $cuentas->sum('saldo_deudor'),
            'saldo_acreedor' => $cuentas->sum('saldo_acreedor'),
        ];
    }

    public function render()
    {
        return view('livewire.admin.contabilidad.balance-comprobacion', [
            'cuentas' => $this->cuentas,
            'totales' => $this->totales,
        ])->layout($this->getLayout());
    }
}
