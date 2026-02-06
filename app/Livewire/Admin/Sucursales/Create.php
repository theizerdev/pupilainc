<?php

namespace App\Livewire\Admin\Sucursales;

use App\Traits\HasDynamicLayout;

use Livewire\Component;
use App\Models\Sucursal;
use App\Models\Empresa;

class Create extends Component
{
        use HasDynamicLayout;

    public $empresa_id = '';
    public $nombre = '';
    public $telefono = '';
    public $direccion = '';
    public $latitud = '';
    public $longitud = '';
    public $status = true;

    public $empresas;

    protected $rules = [
        'empresa_id' => 'required|exists:empresas,id',
        'nombre' => 'required|string|max:255',
        'telefono' => 'nullable|string|max:20',
        'direccion' => 'nullable|string',
        'latitud' => 'nullable|numeric|between:-90,90',
        'longitud' => 'nullable|numeric|between:-180,180',
        'status' => 'boolean',
    ];

    public function mount()
    {
        $this->empresas = Empresa::forUser()->where('status', true)->get();
    }

    public function save()
    {
        $this->validate();

        try {
            Sucursal::create([
                'empresa_id' => $this->empresa_id,
                'nombre' => $this->nombre,
                'telefono' => $this->telefono,
                'direccion' => $this->direccion,
                'latitud' => $this->latitud ?: null,
                'longitud' => $this->longitud ?: null,
                'status' => $this->status,
            ]);

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => "Sucursal '{$this->nombre}' creada exitosamente.",
                'duration' => 4000
            ]);

            return redirect()->route('admin.sucursales.index');
            
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al crear la sucursal: ' . $e->getMessage(),
                'duration' => 5000
            ]);
        }
    }

    public function render()
    {
        return view('livewire.admin.sucursales.create')->layout($this->getLayout());
    }
}