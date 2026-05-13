<?php

namespace App\Livewire\Admin\Cajas;

use App\Models\Caja;
use App\Traits\HasDynamicLayout;
use App\Traits\HasRegionalFormatting;
use Livewire\Component;

class Create extends Component
{
    use HasDynamicLayout, HasRegionalFormatting;


    public $monto_inicial = 0;
    public $observaciones_apertura = '';

    protected $rules = [
        'monto_inicial' => 'required|numeric|min:0',
        'observaciones_apertura' => 'nullable|string|max:500',
    ];

    public function mount()
    {
        $user = auth()->user();
        $empresaId = $user->empresa_id ?? 1;
        $sucursalId = $user->sucursal_id ?? 1;

        // Verificar si ya existe una caja abierta
        $cajaAbierta = Caja::where('empresa_id', $empresaId)
            ->where('sucursal_id', $sucursalId)
            ->where('estado', 'abierta')
            ->first();

        if ($cajaAbierta) {
            session()->flash('warning', '⚠️ Ya existe una caja abierta (Corte #' . $cajaAbierta->numero_corte . '). Se cerrará automáticamente al abrir una nueva.');
        }
    }

    public function save()
    {
        $this->validate();

        try {
            $user = auth()->user();
            $empresaId = $user->empresa_id ?? 1;
            $sucursalId = $user->sucursal_id ?? 1;

            // Verificar si ya existe una caja abierta
            $cajaAbierta = Caja::where('empresa_id', $empresaId)
                ->where('sucursal_id', $sucursalId)
                ->where('estado', 'abierta')
                ->first();

            if ($cajaAbierta) {
                // Cerrar automáticamente la caja anterior
                $cajaAbierta->calcularTotales();
                $cajaAbierta->cerrar('Cierre automático - nueva apertura de caja');

                $this->dispatch('notify', [
                    'type' => 'warning',
                    'message' => '⚠️ Se cerró automáticamente la caja anterior (Corte #' . $cajaAbierta->numero_corte . ')',
                    'duration' => 5000
                ]);
            }

            // Verificar si ya existe alguna caja para hoy
            $cajaHoy = Caja::where('empresa_id', $empresaId)
                ->where('sucursal_id', $sucursalId)
                ->whereDate('fecha', today())
                ->exists();

            if ($cajaHoy) {
                // Es un corte de caja
                $caja = Caja::crearCorte(
                    $empresaId,
                    $sucursalId,
                    $this->monto_inicial,
                    $this->observaciones_apertura,
                    auth()->id()
                );
            } else {
                // Es la primera caja del día
                $caja = Caja::crearCajaDiaria(
                    $empresaId,
                    $sucursalId,
                    $this->monto_inicial,
                    $this->observaciones_apertura,
                    auth()->id()
                );
            }

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Caja abierta exitosamente.',
                'duration' => 4000
            ]);
            return redirect()->route('admin.cajas.show', $caja);
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al abrir la caja: ' . $e->getMessage(),
                'duration' => 5000
            ]);
        }
    }

    public function render()
    {
        return view('livewire.admin.cajas.create')->layout($this->getLayout());
    }
}
