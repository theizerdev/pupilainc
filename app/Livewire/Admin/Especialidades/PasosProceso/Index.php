<?php

namespace App\Livewire\Admin\Especialidades\PasosProceso;

use App\Models\PasoProceso;
use App\Traits\HasDynamicLayout;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use HasDynamicLayout, WithPagination;

    protected $paginationTheme = 'bootstrap';

    public string $search = '';
    public string $sortField = 'nombre';
    public string $sortDirection = 'asc';
    public int $perPage = 20;

    public bool $showModal = false;
    public ?int $editingId = null;

    public string $codigo = '';
    public string $nombre = '';
    public string $icono = 'ri-checkbox-circle-line';
    public bool $activo = true;

    protected $rules = [
        'codigo' => 'required|string|max:50|regex:/^[a-z0-9_]+$/',
        'nombre' => 'required|string|max:100',
        'icono'  => 'required|string|max:50',
        'activo' => 'boolean',
    ];

    public function mount(): void
    {
        $this->authorize('access especialidades');
    }

    public function abrirModal(?int $id = null): void
    {
        $this->resetValidation();
        $this->showModal = true;
        $this->editingId = $id;

        if ($id) {
            $paso = PasoProceso::where('empresa_id', auth()->user()->empresa_id)->findOrFail($id);
            $this->codigo = $paso->codigo;
            $this->nombre = $paso->nombre;
            $this->icono  = $paso->icono;
            $this->activo = $paso->activo;
        } else {
            $this->codigo = '';
            $this->nombre = '';
            $this->icono  = 'ri-checkbox-circle-line';
            $this->activo = true;
        }
    }

    public function guardar(): void
    {
        $this->validate();

        $data = [
            'codigo'    => $this->codigo,
            'nombre'    => $this->nombre,
            'icono'     => $this->icono,
            'activo'    => $this->activo,
            'empresa_id' => auth()->user()->empresa_id,
        ];

        if ($this->editingId) {
            PasoProceso::where('empresa_id', auth()->user()->empresa_id)
                ->findOrFail($this->editingId)
                ->update($data);
            $msg = 'Paso actualizado.';
        } else {
            // Generar código automático si vacío
            if (empty($this->codigo)) {
                $data['codigo'] = \Illuminate\Support\Str::slug($this->nombre, '_');
            }
            PasoProceso::create($data);
            $msg = 'Paso creado.';
        }

        $this->showModal = false;
        $this->editingId = null;
        $this->dispatch('notify', ['type' => 'success', 'message' => $msg]);
    }

    public function eliminar(int $id): void
    {
        $paso = PasoProceso::where('empresa_id', auth()->user()->empresa_id)->findOrFail($id);
        $paso->delete();
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Paso eliminado.']);
    }

    public function toggleActivo(int $id): void
    {
        $paso = PasoProceso::where('empresa_id', auth()->user()->empresa_id)->findOrFail($id);
        $paso->update(['activo' => !$paso->activo]);
        $msg = $paso->activo ? 'Paso activado.' : 'Paso desactivado.';
        $this->dispatch('notify', ['type' => 'success', 'message' => $msg]);
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function getItemsProperty()
    {
        return PasoProceso::where('empresa_id', auth()->user()->empresa_id)
            ->when($this->search, fn($q) =>
                $q->where('nombre', 'like', "%{$this->search}%")
                  ->orWhere('codigo', 'like', "%{$this->search}%"))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }

    public function getStatsProperty(): array
    {
        $empresaId = auth()->user()->empresa_id;
        return [
            'total'     => PasoProceso::where('empresa_id', $empresaId)->count(),
            'activos'   => PasoProceso::where('empresa_id', $empresaId)->where('activo', true)->count(),
            'inactivos' => PasoProceso::where('empresa_id', $empresaId)->where('activo', false)->count(),
        ];
    }

    public function render()
    {
        return view('livewire.admin.especialidades.pasos-proceso.index', [
            'items'  => $this->items,
            'stats'  => $this->stats,
        ])->layout($this->getLayout());
    }
}
