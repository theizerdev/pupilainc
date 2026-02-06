<?php

namespace App\Livewire\Admin\ConceptosPago;

use App\Traits\HasDynamicLayout;
use Livewire\Component;
use App\Models\ConceptoPago;

class Edit extends Component
{
    use HasDynamicLayout;


    public $concepto;
    public $nombre;
    public $descripcion;
    public $activo;

    public function mount(ConceptoPago $concepto)
    {
        // Verificar permiso para editar conceptos de pago
        if (!auth()->user()->can('edit conceptos_pago')) {
            session()->flash('error', 'No tienes permiso para editar conceptos de pago.');
            return redirect()->route('admin.conceptos-pago.index');
        }

        $this->concepto = $concepto;
        $this->nombre = $concepto->nombre;
        $this->descripcion = $concepto->descripcion;
        $this->activo = $concepto->activo;
    }

    protected function rules()
    {
        return [
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'activo' => 'boolean'
        ];
    }

    public function update()
    {
        // Verificar permiso para editar conceptos de pago
        if (!auth()->user()->can('edit conceptos_pago')) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'No tienes permiso para editar conceptos de pago.',
                'duration' => 4000
            ]);
            return;
        }

        $this->validate();

        try {
            $this->concepto->update([
                'nombre' => $this->nombre,
                'descripcion' => $this->descripcion,
                'activo' => $this->activo
            ]);

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => "Concepto de pago '{$this->nombre}' actualizado exitosamente.",
                'duration' => 4000
            ]);
            return redirect()->route('admin.conceptos-pago.index');
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al actualizar el concepto de pago: ' . $e->getMessage(),
                'duration' => 5000
            ]);
        }
    }

    public function render()
    {
        return view('livewire.admin.conceptos-pago.edit')->layout($this->getLayout());
    }
}