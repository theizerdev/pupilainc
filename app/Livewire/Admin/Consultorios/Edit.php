<?php

namespace App\Livewire\Admin\Consultorios;

use App\Models\Consultorio;
use Livewire\Component;
use App\Traits\HasDynamicLayout;

class Edit extends Component
{
    use HasDynamicLayout;

    public $consultorio;
    public $nombre;
    public $ubicacion;
    public $descripcion;
    public $status;

    protected $rules = [
        'nombre' => 'required|string|max:255',
        'ubicacion' => 'nullable|string|max:255',
        'descripcion' => 'nullable|string|max:1000',
        'status' => 'boolean',
    ];

    public function mount($id)
    {
        $this->consultorio = Consultorio::findOrFail($id);
        $this->nombre = $this->consultorio->nombre;
        $this->ubicacion = $this->consultorio->ubicacion;
        $this->descripcion = $this->consultorio->descripcion;
        $this->status = (bool) $this->consultorio->status;
    }

    public function save()
    {
        $this->validate();

        try {
            $this->consultorio->update([
                'nombre' => $this->nombre,
                'ubicacion' => $this->ubicacion,
                'descripcion' => $this->descripcion,
                'status' => $this->status,
            ]);

            $this->dispatch('show-toast', [
                'type' => 'success',
                'message' => "Consultorio actualizado exitosamente."
            ]);

            return redirect()->route('admin.consultorios.index');
        } catch (\Exception $e) {
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Error al actualizar: ' . $e->getMessage()
            ]);
        }
    }

    public function render()
    {
        return view('livewire.admin.consultorios.edit')->layout($this->getLayout());
    }
}
