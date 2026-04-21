<?php

namespace App\Livewire\Admin\Inventario\Categorias;

use App\Models\CategoriaProducto;
use Livewire\Component;
use App\Traits\HasDynamicLayout;

class Create extends Component
{
    use HasDynamicLayout;

    public $nombre = '';
    public $descripcion = '';
    public $color = '#3B82F6';
    public $icono = '';
    public $status = true;

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

    public function store()
    {
        $this->authorize('create categorias-producto');

        $validated = $this->validate();

        CategoriaProducto::create([
            ...$validated,
            'empresa_id'  => auth()->user()->empresa_id,
            'sucursal_id' => auth()->user()->sucursal_id,
        ]);

        $this->dispatch('notify', [
            'type'     => 'success',
            'message'  => "Categoría '{$this->nombre}' creada exitosamente.",
            'duration' => 4000,
        ]);

        return redirect()->route('admin.inventario.categorias.index');
    }

    public function render()
    {
        return view('livewire.admin.inventario.categorias.create')
            ->layout($this->getLayout());
    }
}
