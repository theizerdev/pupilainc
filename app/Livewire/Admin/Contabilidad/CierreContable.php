<?php

namespace App\Livewire\Admin\Contabilidad;

use App\Traits\HasDynamicLayout;
use Livewire\Component;
use App\Services\ContabilidadService;
use App\Models\AsientoContable;

class CierreContable extends Component
{
    use HasDynamicLayout;

    public $mes;
    public $anio;
    public $tipo_cierre = 'mensual';

    public function mount()
    {
        $this->authorize('access contabilidad');
        $this->mes = now()->month;
        $this->anio = now()->year;
    }

    public function ejecutarCierre()
    {
        $this->authorize('create contabilidad');
        $service = new ContabilidadService();
        $user = auth()->user();

        try {
            $asiento = match ($this->tipo_cierre) {
                'mensual' => $service->cierreMensual(
                    $user->empresa_id, (int) $this->mes, (int) $this->anio,
                    $user->id, $user->sucursal_id
                ),
                'anual' => $service->cierreAnual(
                    $user->empresa_id, (int) $this->anio,
                    $user->id, $user->sucursal_id
                ),
                'apertura' => $service->generarAsientoApertura(
                    $user->empresa_id, (int) $this->anio,
                    $user->id, $user->sucursal_id
                ),
            };

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => "Asiento generado exitosamente: {$asiento->numero}",
            ]);
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error: ' . $e->getMessage(),
            ]);
        }
    }

    public function getCierresProperty()
    {
        return AsientoContable::where('empresa_id', auth()->user()->empresa_id)
            ->whereIn('tipo', ['cierre', 'apertura'])
            ->where('estado', '!=', 'anulado')
            ->orderBy('fecha', 'desc')
            ->limit(20)
            ->get();
    }

    public function render()
    {
        return view('livewire.admin.contabilidad.cierre-contable', [
            'cierresRealizados' => $this->cierres,
        ])->layout($this->getLayout());
    }
}
