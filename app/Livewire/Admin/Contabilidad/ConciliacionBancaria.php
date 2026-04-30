<?php

namespace App\Livewire\Admin\Contabilidad;

use App\Traits\HasDynamicLayout;
use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\CuentaContable;
use App\Models\ConciliacionBancaria as ModelConciliacionBancaria;
use App\Models\MovimientoBancario;
use App\Models\AsientoDetalle;
use Carbon\Carbon;
use Livewire\Attributes\Computed;

class ConciliacionBancaria extends Component
{
    use HasDynamicLayout, WithFileUploads;

    public $cuenta_bancaria_id;
    public $fecha_corte;
    public $saldo_estado_cuenta;
    public $archivo_estado_cuenta;
    public $mostrar_conciliados = false;
    public $search = '';
    public $conciliacion_activa;

    protected $rules = [
        'cuenta_bancaria_id' => 'required|exists:cuentas_contables,id',
        'fecha_corte' => 'required|date',
        'saldo_estado_cuenta' => 'required|numeric|min:0',
        'archivo_estado_cuenta' => 'nullable|file|mimes:csv,txt,xlsx|max:2048',
    ];

    public function mount()
    {
        $this->authorize('access contabilidad');
        $this->fecha_corte = now()->format('Y-m-d');
    }

    public function updatedCuentaBancariaId()
    {
        $this->conciliacion_activa = null;
        $this->buscarConciliacionActiva();
    }

    private function buscarConciliacionActiva()
    {
        if ($this->cuenta_bancaria_id) {
            $this->conciliacion_activa = ModelConciliacionBancaria::where('cuenta_bancaria_id', $this->cuenta_bancaria_id)
                ->where('estado', 'en_proceso')
                ->latest()
                ->first();
        }
    }

    #[Computed]
    public function cuentasBancarias()
    {
        return CuentaContable::where('empresa_id', auth()->user()->empresa_id)
            ->where('tipo', 'activo')
            ->where('codigo', 'like', '1.1.01%') // Cuentas de bancos
            ->where('activo', true)
            ->orderBy('codigo')
            ->get();
    }

    #[Computed]
    public function movimientosContables()
    {
        if (!$this->cuenta_bancaria_id) return collect();

        return AsientoDetalle::where('cuenta_id', $this->cuenta_bancaria_id)
            ->whereHas('asiento', fn($q) => $q->where('estado', 'aprobado')
                ->whereDate('fecha', '<=', $this->fecha_corte))
            ->when($this->search, fn($q) => $q->whereHas('asiento', fn($sq) => 
                $sq->where('descripcion', 'like', "%{$this->search}%")
                  ->orWhere('numero', 'like', "%{$this->search}%")
            ))
            ->when(!$this->mostrar_conciliados, fn($q) => $q->where('conciliado', false))
            ->with(['asiento'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($detalle) {
                return (object) [
                    'id' => $detalle->id,
                    'fecha' => $detalle->asiento->fecha,
                    'numero' => $detalle->asiento->numero,
                    'descripcion' => $detalle->descripcion,
                    'debe' => $detalle->debe,
                    'haber' => $detalle->haber,
                    'monto' => $detalle->debe > 0 ? $detalle->debe : -$detalle->haber,
                    'conciliado' => $detalle->conciliado ?? false,
                    'tipo' => 'contable',
                ];
            });
    }

    #[Computed]
    public function movimientosBancarios()
    {
        if (!$this->cuenta_bancaria_id) return collect();

        return MovimientoBancario::where('cuenta_bancaria_id', $this->cuenta_bancaria_id)
            ->whereDate('fecha', '<=', $this->fecha_corte)
            ->when($this->search, fn($q) => $q->where('descripcion', 'like', "%{$this->search}%")
                ->orWhere('referencia', 'like', "%{$this->search}%"))
            ->when(!$this->mostrar_conciliados, fn($q) => $q->where('conciliado', false))
            ->orderBy('fecha', 'desc')
            ->get()
            ->map(function($mov) {
                return (object) [
                    'id' => $mov->id,
                    'fecha' => $mov->fecha,
                    'referencia' => $mov->referencia,
                    'descripcion' => $mov->descripcion,
                    'monto' => $mov->monto,
                    'conciliado' => $mov->conciliado,
                    'tipo' => 'bancario',
                ];
            });
    }

    #[Computed]
    public function resumenConciliacion()
    {
        if (!$this->cuenta_bancaria_id) {
            return [
                'saldo_contable' => 0,
                'saldo_bancario' => 0,
                'diferencia' => 0,
                'movimientos_pendientes_contables' => 0,
                'movimientos_pendientes_bancarios' => 0,
            ];
        }

        $saldoContable = $this->calcularSaldoContable();
        $movimientosPendientesContables = $this->movimientosContables->where('conciliado', false)->count();
        $movimientosPendientesBancarios = $this->movimientosBancarios->where('conciliado', false)->count();

        return [
            'saldo_contable' => $saldoContable,
            'saldo_bancario' => $this->saldo_estado_cuenta ?? 0,
            'diferencia' => $saldoContable - ($this->saldo_estado_cuenta ?? 0),
            'movimientos_pendientes_contables' => $movimientosPendientesContables,
            'movimientos_pendientes_bancarios' => $movimientosPendientesBancarios,
        ];
    }

    private function calcularSaldoContable()
    {
        if (!$this->cuenta_bancaria_id) return 0;

        $cuenta = CuentaContable::find($this->cuenta_bancaria_id);
        if (!$cuenta) return 0;

        $query = AsientoDetalle::where('cuenta_id', $this->cuenta_bancaria_id)
            ->whereHas('asiento', fn($q) => $q->where('estado', 'aprobado')
                ->whereDate('fecha', '<=', $this->fecha_corte));

        $debe = (float) $query->sum('debe');
        $haber = (float) (clone $query)->sum('haber');

        return $cuenta->naturaleza === 'deudora' ? ($debe - $haber) : ($haber - $debe);
    }

    public function iniciarConciliacion()
    {
        $this->validate();

        $this->conciliacion_activa = ModelConciliacionBancaria::create([
            'cuenta_bancaria_id' => $this->cuenta_bancaria_id,
            'fecha_corte' => $this->fecha_corte,
            'saldo_estado_cuenta' => $this->saldo_estado_cuenta,
            'saldo_contable' => $this->calcularSaldoContable(),
            'estado' => 'en_proceso',
            'user_id' => auth()->id(),
            'empresa_id' => auth()->user()->empresa_id,
        ]);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Conciliación bancaria iniciada exitosamente',
        ]);
    }

    public function conciliarMovimiento($tipo, $id)
    {
        if (!$this->conciliacion_activa) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Debe iniciar una conciliación primero',
            ]);
            return;
        }

        if ($tipo === 'contable') {
            $detalle = AsientoDetalle::find($id);
            if ($detalle) {
                $detalle->update(['conciliado' => true]);
            }
        } else {
            $movimiento = MovimientoBancario::find($id);
            if ($movimiento) {
                $movimiento->update(['conciliado' => true]);
            }
        }

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Movimiento conciliado exitosamente',
        ]);
    }

    public function desconciliarMovimiento($tipo, $id)
    {
        if ($tipo === 'contable') {
            $detalle = AsientoDetalle::find($id);
            if ($detalle) {
                $detalle->update(['conciliado' => false]);
            }
        } else {
            $movimiento = MovimientoBancario::find($id);
            if ($movimiento) {
                $movimiento->update(['conciliado' => false]);
            }
        }

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Movimiento desconciliado exitosamente',
        ]);
    }

    public function finalizarConciliacion()
    {
        if (!$this->conciliacion_activa) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'No hay conciliación activa',
            ]);
            return;
        }

        $resumen = $this->resumenConciliacion;
        
        $this->conciliacion_activa->update([
            'estado' => 'finalizada',
            'diferencia_final' => $resumen['diferencia'],
            'fecha_finalizacion' => now(),
        ]);

        $this->conciliacion_activa = null;

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Conciliación bancaria finalizada exitosamente',
        ]);
    }

    public function procesarArchivoEstadoCuenta()
    {
        if (!$this->archivo_estado_cuenta) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Debe seleccionar un archivo',
            ]);
            return;
        }

        $this->dispatch('notify', [
            'type' => 'info',
            'message' => 'Funcionalidad de importación en desarrollo',
        ]);
    }

    protected function getPageTitle(): string
    {
        return 'Conciliación Bancaria';
    }

    protected function getBreadcrumb(): array
    {
        return [
            'admin.dashboard' => 'Dashboard',
            'admin.contabilidad.conciliacion-bancaria' => 'Conciliación Bancaria'
        ];
    }

    public function render()
    {
        $this->buscarConciliacionActiva();

        return view('livewire.admin.contabilidad.conciliacion-bancaria', [
            'cuentas_bancarias' => $this->cuentasBancarias,
            'movimientos_contables' => $this->movimientosContables,
            'movimientos_bancarios' => $this->movimientosBancarios,
            'resumen' => $this->resumenConciliacion,
        ])->layout($this->getLayout());
    }
}