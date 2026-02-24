<?php

namespace App\Livewire\Admin\Impuestos;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ImpuestoConfiguracion;
use App\Traits\HasDynamicLayout;

class Index extends Component
{
    use WithPagination, HasDynamicLayout;

    public $modal = false;
    public $impuesto_id;
    public $codigo;
    public $nombre;
    public $porcentaje;
    public $descripcion;
    public $activo = true;

    public $search = '';
    public $filtro_activo = '';

    protected $paginationTheme = 'bootstrap';

    protected function rules()
    {
        return [
            'codigo' => 'required|unique:impuestos_configuracion,codigo,' . $this->impuesto_id,
            'nombre' => 'required|min:3',
            'porcentaje' => 'required|numeric|min:0|max:100',
        ];
    }

    public function crear()
    {
        $this->authorize('access impuestos');
        $this->resetValidation();
        $this->reset(['impuesto_id', 'codigo', 'nombre', 'porcentaje', 'descripcion']);
        $this->activo = true;
        $this->modal = true;
    }

    public function editar($id)
    {
        $this->authorize('access impuestos');
        $impuesto = ImpuestoConfiguracion::findOrFail($id);
        $this->impuesto_id = $impuesto->id;
        $this->codigo = $impuesto->codigo;
        $this->nombre = $impuesto->nombre;
        $this->porcentaje = $impuesto->porcentaje;
        $this->descripcion = $impuesto->descripcion;
        $this->activo = $impuesto->activo;
        $this->modal = true;
    }

    public function guardar()
    {
        $this->authorize('access impuestos');
        $this->validate();

        $data = [
            'codigo' => strtoupper(trim($this->codigo)),
            'nombre' => trim($this->nombre),
            'porcentaje' => $this->porcentaje,
            'descripcion' => $this->descripcion,
            'activo' => $this->activo,
            'empresa_id' => auth()->user()->empresa_id,
            'sucursal_id' => auth()->user()->sucursal_id,
        ];

        if ($this->impuesto_id) {
            ImpuestoConfiguracion::find($this->impuesto_id)->update($data);
            $this->dispatch('notify', type: 'success', message: 'Impuesto actualizado exitosamente');
        } else {
            ImpuestoConfiguracion::create($data);
            $this->dispatch('notify', type: 'success', message: 'Impuesto creado exitosamente');
        }

        $this->modal = false;
        $this->reset(['impuesto_id', 'codigo', 'nombre', 'porcentaje', 'descripcion']);
    }

    public function toggleActivo($id)
    {
        $this->authorize('access impuestos');
        $impuesto = ImpuestoConfiguracion::find($id);
        if ($impuesto) {
            $impuesto->activo = !$impuesto->activo;
            $impuesto->save();
            $estado = $impuesto->activo ? 'activado' : 'desactivado';
            $this->dispatch('notify', type: 'success', message: "Impuesto {$estado} exitosamente");
        }
    }

    public function eliminar($id)
    {
        $this->authorize('access impuestos');
        ImpuestoConfiguracion::find($id)?->delete();
        $this->dispatch('notify', type: 'success', message: 'Impuesto eliminado exitosamente');
    }

    public function render()
    {
        $impuestos = ImpuestoConfiguracion::query()
            ->when($this->search, fn($q) => $q->where('codigo', 'like', "%{$this->search}%")
                ->orWhere('nombre', 'like', "%{$this->search}%"))
            ->when($this->filtro_activo !== '', fn($q) => $q->where('activo', $this->filtro_activo))
            ->latest()
            ->paginate(15);

        return view('livewire.admin.impuestos.index', compact('impuestos'))->layout($this->getLayout());
    }
}
