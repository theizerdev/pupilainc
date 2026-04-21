<?php

namespace App\Livewire\Admin\Inventario\Categorias;

use App\Models\CategoriaProducto;
use Livewire\Component;
use App\Traits\HasDynamicLayout;

class Edit extends Component
{
    use HasDynamicLayout;

    public CategoriaProducto $categoria;

    public $nombre = '';
    public $descripcion = '';
    public $color = '#3B82F6';
    public $icono = '';
    public $status = true;

    public function mount(CategoriaProducto $categoria)
    {
        $this->categoria = $categoria;
        $this->fill($categoria->only('nombre', 'descripcion', 'color', 'icono', 'status'));
    }

    protected function rules()
    {
        return [
            'nombre'      => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:500',
            'color'       => 'required|string|max:7',
            'icono'       => 'nullable|string|max:100',
            'status'      => 'boolean',
        ];
    }

    public function update()
    {
        $this->authorize('edit categorias-producto');

        $validated = $this->validate();

        $this->categoria->update($validated);

        $this->dispatch('notify', [
            'type'     => 'success',
            'message'  => "Categoría '{$this->nombre}' actualizada exitosamente.",
            'duration' => 4000,
        ]);

        return redirect()->route('admin.inventario.categorias.index');
    }

    public function render()
    {
        return view('livewire.admin.inventario.categorias.edit')
            ->layout($this->getLayout());
    }
}
