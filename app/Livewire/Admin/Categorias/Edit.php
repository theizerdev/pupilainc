<?php

namespace App\Livewire\Admin\Categorias;

use App\Models\Categoria;
use Livewire\Component;
use App\Traits\HasDynamicLayout;

class Edit extends Component
{
    use HasDynamicLayout;

    public $categoria;
    public $nombre = '';
    public $descripcion = '';
    public $color = '#3B82F6';
    public $icono = '';
    public $orden = 0;
    public $activo = true;
    
    public $empresa_id = null;
    public $sucursal_id = null;
    public $empresas = [];
    public $sucursales = [];

    protected function rules()
    {
        return [
            'nombre' => 'required|min:3|max:100|unique:categorias,nombre,' . $this->categoria->id,
            'descripcion' => 'nullable|max:255',
            'color' => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
            'icono' => 'nullable|max:50',
            'orden' => 'nullable|integer|min:0',
            'activo' => 'boolean',
            'empresa_id' => 'required_if:auth.user.role,Super Administrador|exists:empresas,id',
            'sucursal_id' => 'required_if:auth.user.role,Super Administrador|exists:sucursales,id',
        ];
    }

    public function mount(Categoria $categoria)
    {
        $this->categoria = $categoria;
        
        $this->nombre = $categoria->nombre;
        $this->descripcion = $categoria->descripcion;
        $this->color = $categoria->color;
        $this->icono = $categoria->icono;
        $this->orden = $categoria->orden;
        $this->activo = $categoria->activo;
        $this->empresa_id = $categoria->empresa_id;
        $this->sucursal_id = $categoria->sucursal_id;

        if (auth()->user()->hasRole('Super Administrador')) {
            $this->empresas = \App\Models\Empresa::all();
            if ($this->empresa_id) {
                $this->sucursales = \App\Models\Sucursal::where('empresa_id', $this->empresa_id)->get();
            }
        }
    }

    public function updatedEmpresaId()
    {
        if ($this->empresa_id) {
            $this->sucursales = \App\Models\Sucursal::where('empresa_id', $this->empresa_id)->get();
            $this->sucursal_id = null;
        } else {
            $this->sucursales = [];
        }
    }

    public function update()
    {
        $this->validate();

        try {
            $this->categoria->update([
                'nombre' => trim($this->nombre),
                'descripcion' => trim($this->descripcion),
                'color' => $this->color,
                'icono' => $this->icono,
                'orden' => $this->orden ?? 0,
                'activo' => $this->activo,
                'empresa_id' => $this->empresa_id,
                'sucursal_id' => $this->sucursal_id,
            ]);

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => "Categoría '{$this->nombre}' actualizada exitosamente.",
                'duration' => 4000
            ]);

            return redirect()->route('admin.categorias.index');
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al actualizar la categoría: ' . $e->getMessage(),
                'duration' => 5000
            ]);
        }
    }

    public function render()
    {
        return view('livewire.admin.categorias.edit')->layout($this->getLayout());
    }
}
