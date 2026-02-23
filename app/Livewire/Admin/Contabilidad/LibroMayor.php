<?php

namespace App\Livewire\Admin\Contabilidad;

use App\Traits\HasDynamicLayout;
use Livewire\Component;
use App\Models\CuentaContable;
use App\Models\AsientoDetalle;

class LibroMayor extends Component
{
    use HasDynamicLayout;

    public $cuenta_id;
    public $fecha_desde;
    public $fecha_hasta;

    protected $queryString = [
        'cuenta_id' => ['except' => null],
    ];

    public function mount()
    {
        $this->authorize('access contabilidad');
        $this->fecha_desde = now()->startOfMonth()->format('Y-m-d');
        $this->fecha_hasta = now()->endOfMonth()->format('Y-m-d');
    }

    public function resetFilters()
    {
        $this->cuenta_id = null;
        $this->fecha_desde = now()->startOfMonth()->format('Y-m-d');
        $this->fecha_hasta = now()->endOfMonth()->format('Y-m-d');
    }

    public function getCuentasProperty()
    {
        return CuentaContable::where('empresa_id', auth()->user()->empresa_id)
            ->where('acepta_movimientos', true)
            ->where('activo', true)
            ->orderBy('codigo')
            ->get();
    }

    public function render()
    {
        $movimientos = collect();
        $cuentaSeleccionada = null;
        $saldoInicial = 0;

        if ($this->cuenta_id) {
            $cuentaSeleccionada = CuentaContable::find($this->cuenta_id);

            if ($cuentaSeleccionada) {
                $queryInicial = AsientoDetalle::where('cuenta_id', $this->cuenta_id)
                    ->whereHas('asiento', fn($q) => $q->where('estado', 'aprobado')
                        ->whereDate('fecha', '<', $this->fecha_desde));
                $di = (float) $queryInicial->sum('debe');
                $hi = (float) (clone $queryInicial)->sum('haber');
                $saldoInicial = $cuentaSeleccionada->naturaleza === 'deudora' ? ($di - $hi) : ($hi - $di);

                $saldoActual = $saldoInicial;
                $movimientos = AsientoDetalle::where('cuenta_id', $this->cuenta_id)
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
                            'fecha' => $detalle->asiento->fecha,
                            'numero' => $detalle->asiento->numero,
                            'descripcion' => $detalle->descripcion ?: $detalle->asiento->descripcion,
                            'debe' => $debe,
                            'haber' => $haber,
                            'saldo' => $saldoActual,
                        ];
                    });
            }
        }

        return view('livewire.admin.contabilidad.libro-mayor', [
            'cuentas' => $this->cuentas,
            'movimientos' => $movimientos,
            'cuentaSeleccionada' => $cuentaSeleccionada,
            'saldoInicial' => $saldoInicial,
        ])->layout($this->getLayout());
    }
}
