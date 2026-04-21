<?php

namespace App\Livewire\Admin\Inventario\Proveedores;

use App\Models\Proveedor;
use Livewire\Component;
use Livewire\Attributes\On;
use App\Traits\HasDynamicLayout;

class Form extends Component
{
    use HasDynamicLayout;

    public ?Proveedor $proveedor = null;
    public bool $isEdit = false;

    public $nombre = '';
    public $documento = '';
    public $contacto = '';
    public $telefono = '';
    public $email = '';
    public $direccion = '';
    public $latitud = 10.4806;
    public $longitud = -66.9036;
    public $condiciones_pago = '';
    public $status = true;

    protected function rules()
    {
        return [
            'nombre'           => 'required|string|max:255',
            'documento'        => 'nullable|string|max:50',
            'contacto'         => 'nullable|string|max:255',
            'telefono'         => 'nullable|string|max:30',
            'email'            => 'nullable|email|max:255',
            'direccion'        => 'nullable|string|max:500',
            'latitud'          => 'nullable|numeric|between:-90,90',
            'longitud'         => 'nullable|numeric|between:-180,180',
            'condiciones_pago' => 'nullable|string|max:255',
            'status'           => 'boolean',
        ];
    }

    public function mount(?Proveedor $proveedor = null)
    {
        if ($proveedor && $proveedor->exists) {
            $this->proveedor = $proveedor;
            $this->isEdit    = true;
            $this->fill($proveedor->only(
                'nombre', 'documento', 'contacto', 'telefono',
                'email', 'direccion', 'latitud', 'longitud',
                'condiciones_pago', 'status'
            ));

            // Si no tiene coordenadas usar valor por defecto
            $this->latitud  = $proveedor->latitud  ?? 10.4806;
            $this->longitud = $proveedor->longitud ?? -66.9036;
        }
    }

    #[On('location-updated')]
    public function updateLocation($latitude, $longitude, $address)
    {
        $this->latitud   = $latitude;
        $this->longitud  = $longitude;
        $this->direccion = $address;
    }

    public function save()
    {
        $this->isEdit
            ? $this->authorize('edit proveedores')
            : $this->authorize('create proveedores');

        $data = $this->validate();

        if ($this->isEdit) {
            $this->proveedor->update($data);
            $msg = "Proveedor '{$this->nombre}' actualizado.";
        } else {
            Proveedor::create(array_merge($data, [
                'empresa_id'  => auth()->user()->empresa_id,
                'sucursal_id' => auth()->user()->sucursal_id,
            ]));
            $msg = "Proveedor '{$this->nombre}' creado.";
        }

        $this->dispatch('notify', ['type' => 'success', 'message' => $msg, 'duration' => 4000]);
        return redirect()->route('admin.inventario.proveedores.index');
    }

    public function render()
    {
        return view('livewire.admin.inventario.proveedores.form')
            ->layout($this->getLayout());
    }
}
