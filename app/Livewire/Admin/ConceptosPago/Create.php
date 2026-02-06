<?php

namespace App\Livewire\Admin\ConceptosPago;

use App\Traits\HasDynamicLayout;
use Livewire\Component;
use App\Models\ConceptoPago;

class Create extends Component
{
    use HasDynamicLayout;


    public $nombre;
    public $descripcion;
    public $activo = true;

    protected function rules()
    {
        return [
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'activo' => 'boolean'
        ];
    }

    public function store()
    {
        // Verificar permiso para crear conceptos de pago
        if (!auth()->user()->can('create conceptos_pago')) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'No tienes permiso para crear conceptos de pago.',
                'duration' => 4000
            ]);
            return;
        }

        $this->validate();

        try {
            ConceptoPago::create([
                'nombre' => $this->nombre,
                'descripcion' => $this->descripcion,
                'activo' => $this->activo
            ]);

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => "Concepto de pago '{$this->nombre}' creado exitosamente.",
                'duration' => 4000
            ]);
            return redirect()->route('admin.conceptos-pago.index');
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al crear el concepto de pago: ' . $e->getMessage(),
                'duration' => 5000
            ]);
        }
    }

    public function render()
    {
        return view('livewire.admin.conceptos-pago.create')->layout($this->getLayout());
    }
}