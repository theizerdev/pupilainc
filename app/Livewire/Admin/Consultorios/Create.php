<?php

namespace App\Livewire\Admin\Consultorios;

use App\Models\Consultorio;
use Livewire\Component;
use App\Traits\HasDynamicLayout;

class Create extends Component
{
    use HasDynamicLayout;

    public $nombre;
    public $ubicacion;
    public $descripcion;
    public $status = true;

    protected $rules = [
        'nombre' => 'required|string|max:255',
        'ubicacion' => 'nullable|string|max:255',
        'descripcion' => 'nullable|string|max:1000',
        'status' => 'boolean',
    ];

    public function save()
    {
        $this->validate();

        try {
            Consultorio::create([
                'nombre' => $this->nombre,
                'ubicacion' => $this->ubicacion,
                'descripcion' => $this->descripcion,
                'status' => $this->status,
                'empresa_id' => auth()->user()->empresa_id,
                'sucursal_id' => auth()->user()->sucursal_id,
            ]);

            $this->dispatch('show-toast', [
                'type' => 'success',
                'message' => "Consultorio '{$this->nombre}' creado exitosamente."
            ]);

            return redirect()->route('admin.consultorios.index');
        } catch (\Exception $e) {
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Error al crear: ' . $e->getMessage()
            ]);
        }
    }

    public function render()
    {
        return view('livewire.admin.consultorios.create')->layout($this->getLayout());
    }
}
