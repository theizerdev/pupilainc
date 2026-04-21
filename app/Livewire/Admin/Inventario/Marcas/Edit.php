<?php

namespace App\Livewire\Admin\Inventario\Marcas;

use App\Models\Marca;
use Livewire\Component;
use App\Traits\HasDynamicLayout;

class Edit extends Component
{
    use HasDynamicLayout;

    public Marca $marca;

    public $nombre = '';
    public $descripcion = '';
    public $sitio_web = '';
    public $status = true;

    public function mount(Marca $marca)
    {
        $this->marca = $marca;
        $this->fill($marca->only('nombre', 'descripcion', 'sitio_web', 'status'));
    }

    protected function rules()
    {
        return [
            'nombre'      => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:500',
            'sitio_web'   => 'nullable|url|max:255',
            'status'      => 'boolean',
        ];
    }

    public function update()
    {
        $this->authorize('edit marcas');

        $validated = $this->validate();

        $this->marca->update($validated);

        $this->dispatch('notify', [
            'type'     => 'success',
            'message'  => "Marca '{$this->nombre}' actualizada exitosamente.",
            'duration' => 4000,
        ]);

        return redirect()->route('admin.inventario.marcas.index');
    }

    public function render()
    {
        return view('livewire.admin.inventario.marcas.edit')
            ->layout($this->getLayout());
    }
}
