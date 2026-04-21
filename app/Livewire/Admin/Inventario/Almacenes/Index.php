<?php

namespace App\Livewire\Admin\Inventario\Almacenes;

use App\Models\Almacen;
use Livewire\Component;
use Livewire\WithPagination;
use App\Traits\HasDynamicLayout;

class Index extends Component
{
    use WithPagination, HasDynamicLayout;

    public $search = '';
    public $status = '';
    public $sortField = 'nombre';
    public $sortDirection = 'asc';

    // Modal inline
    public $showModal = false;
    public $editingId = null;
    public $nombre = '';
    public $descripcion = '';
    public $ubicacion = '';
    public $es_principal = false;
    public $modalStatus = true;

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
    ];

    protected $paginationTheme = 'bootstrap';

    protected function rules()
    {
        return [
            'nombre'      => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:500',
            'ubicacion'   => 'nullable|string|max:255',
            'es_principal'=> 'boolean',
            'modalStatus' => 'boolean',
        ];
    }

    public function updatingSearch() { $this->resetPage(); }

    public function openModal($id = null)
    {
        $this->resetValidation();
        $this->editingId = $id;

        if ($id) {
            $a = Almacen::findOrFail($id);
            $this->nombre      = $a->nombre;
            $this->descripcion = $a->descripcion ?? '';
            $this->ubicacion   = $a->ubicacion ?? '';
            $this->es_principal= $a->es_principal;
            $this->modalStatus = $a->status;
        } else {
            $this->nombre = $this->descripcion = $this->ubicacion = '';
            $this->es_principal = false;
            $this->modalStatus  = true;
        }

        $this->showModal = true;
    }

    public function save()
    {
        $this->editingId
            ? $this->authorize('edit almacenes')
            : $this->authorize('create almacenes');

        $data = $this->validate();

        $payload = [
            'nombre'      => $data['nombre'],
            'descripcion' => $data['descripcion'],
            'ubicacion'   => $data['ubicacion'],
            'es_principal'=> $data['es_principal'],
            'status'      => $data['modalStatus'],
        ];

        if ($this->editingId) {
            Almacen::findOrFail($this->editingId)->update($payload);
            $msg = "Almacén '{$this->nombre}' actualizado.";
        } else {
            $payload['empresa_id']  = auth()->user()->empresa_id;
            $payload['sucursal_id'] = auth()->user()->sucursal_id;
            Almacen::create($payload);
            $msg = "Almacén '{$this->nombre}' creado.";
        }

        $this->showModal = false;
        $this->dispatch('notify', ['type' => 'success', 'message' => $msg, 'duration' => 3000]);
    }

    public function delete($id)
    {
        $this->authorize('delete almacenes');
        $a = Almacen::findOrFail($id);

        if ($a->stocks()->where('cantidad', '>', 0)->exists()) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'No se puede eliminar: el almacén tiene stock.', 'duration' => 4000]);
            return;
        }

        $a->delete();
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Almacén eliminado.', 'duration' => 3000]);
    }

    public function getAlmacenesProperty()
    {
        return Almacen::forUser()
            ->when($this->search, fn($q) => $q->where('nombre', 'like', "%{$this->search}%"))
            ->when($this->status !== '', fn($q) => $q->where('status', $this->status))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);
    }

    public function render()
    {
        return view('livewire.admin.inventario.almacenes.index', [
            'almacenes' => $this->almacenes,
        ])->layout($this->getLayout());
    }
}
