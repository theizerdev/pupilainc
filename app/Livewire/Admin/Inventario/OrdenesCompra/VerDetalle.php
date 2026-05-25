<?php

namespace App\Livewire\Admin\Inventario\OrdenesCompra;

use App\Models\OrdenCompra;
use Livewire\Component;
use App\Traits\HasDynamicLayout;

class VerDetalle extends Component
{
    use HasDynamicLayout;

    public $ordenId = null;
    public $showModal = false;

    protected $listeners = ['abrirModal'];

    public function mount($ordenId = null)
    {
        if ($ordenId) {
            $this->ordenId = $ordenId;
        }
    }

    public function abrirModal($ordenId)
    {
        $this->ordenId = $ordenId;
        $this->showModal = true;
    }

    public function cerrarModal()
    {
        $this->showModal = false;
        $this->ordenId = null;
    }

    public function getOrdenProperty()
    {
        if (!$this->ordenId) {
            return null;
        }

        return OrdenCompra::with(['proveedor', 'almacen', 'detalles.producto'])
            ->findOrFail($this->ordenId);
    }

    public function render()
    {
        return view('livewire.admin.inventario.ordenes-compra.ver-detalle', [
            'orden' => $this->orden,
        ])->layout($this->getLayout());
    }
}
