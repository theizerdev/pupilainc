<?php

namespace App\Livewire\Admin\Inventario\Marcas;

use App\Models\Marca;
use Livewire\Component;
use App\Traits\HasDynamicLayout;

class Create extends Component
{
    use HasDynamicLayout;

    public $nombre = '';
    public $descripcion = '';
    public $sitio_web = '';
    public $status = true;

    protected function rules()
    {
        return [
            'nombre'      => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:500',
            'sitio_web'   => 'nullable|url|max:255',
            'status'      => 'boolean',
        ];
    }

    public function store()
    {
        $this->authorize('create marcas');

        $validated = $this->validate();

        Marca::create([
            ...$validated,
            'empresa_id'  => auth()->user()->empresa_id,
            'sucursal_id' => auth()->user()->sucursal_id,
        ]);

        $this->dispatch('notify', [
            'type'     => 'success',
            'message'  => "Marca '{$this->nombre}' creada exitosamente.",
            'duration' => 4000,
        ]);

        return redirect()->route('admin.inventario.marcas.index');
    }

    public function render()
    {
        return view('livewire.admin.inventario.marcas.create')
            ->layout($this->getLayout());
    }
}
