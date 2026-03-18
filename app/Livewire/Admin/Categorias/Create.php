<?php

namespace App\Livewire\Admin\Categorias;

use App\Models\Categoria;
use Livewire\Component;
use Illuminate\Support\Str;
use App\Traits\HasDynamicLayout;

class Create extends Component
{
    use HasDynamicLayout;
    
    public $nombre = '';
    public $descripcion = '';
    public $color = '#3B82F6';
    public $icono = 'ri-price-tag-3-line';
    public $orden = 0;
    public $activo = true;
    
    public $empresa_id = null;
    public $sucursal_id = null;
    public $empresas = [];
    public $sucursales = [];

    protected function rules()
    {
        return [
            'nombre' => 'required|min:3|max:100|unique:categorias,nombre',
            'descripcion' => 'nullable|max:255',
            'color' => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
            'icono' => 'nullable|max:50',
            'orden' => 'nullable|integer|min:0',
            'activo' => 'boolean',
            'empresa_id' => 'required_if:auth.user.role,Super Administrador|exists:empresas,id',
            'sucursal_id' => 'required_if:auth.user.role,Super Administrador|exists:sucursales,id',
        ];
    }

    public function mount()
    {
        if (auth()->user()->hasRole('Super Administrador')) {
            $this->empresas = \App\Models\Empresa::all();
            if ($this->empresa_id) {
                $this->sucursales = \App\Models\Sucursal::where('empresa_id', $this->empresa_id)->get();
            }
        } else {
            $this->empresa_id = auth()->user()->empresa_id;
            $this->sucursal_id = auth()->user()->sucursal_id;
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

    public function save()
    {
        $this->validate();

        try {
            Categoria::create([
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
                'message' => "Categoría '{$this->nombre}' creada exitosamente.",
                'duration' => 4000
            ]);

            return redirect()->route('admin.categorias.index');
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al crear la categoría: ' . $e->getMessage(),
                'duration' => 5000
            ]);
        }
    }

    public function render()
    {
        return view('livewire.admin.categorias.create')->layout($this->getLayout());
    }
}
