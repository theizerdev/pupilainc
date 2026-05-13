<?php

namespace App\Livewire\Admin\Series;
use App\Traits\HasDynamicLayout;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Serie;

class Index extends Component
{
    use WithPagination, HasDynamicLayout;

    public $search = '';
    public $tipo_documento = '';
    public $filtro_activo = '';
    public $sortField = 'tipo_documento';
    public $sortDirection = 'asc';
    public $perPage = 20;

    protected $queryString = [
        'search' => ['except' => ''],
        'tipo_documento' => ['except' => ''],
        'filtro_activo' => ['except' => ''],
        'sortField' => ['except' => 'tipo_documento'],
        'sortDirection' => ['except' => 'asc'],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingTipoDocumento()
    {
        $this->resetPage();
    }

    public function updatingFiltroActivo()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }
        $this->sortField = $field;
    }

    public function resetFilters()
    {
        $this->reset(['search', 'tipo_documento', 'filtro_activo']);
        $this->resetPage();
    }

    public function toggleActivo($id)
    {
        try {
            $serie = Serie::findOrFail($id);
            $serie->update(['activo' => !$serie->activo]);

            $estado = $serie->activo ? 'activada' : 'desactivada';
            session()->flash('message', "Serie {$estado} correctamente");
        } catch (\Exception $e) {
            session()->flash('error', 'Error al actualizar el estado: ' . $e->getMessage());
        }
    }

    public function delete($id)
    {
        try {
            $serie = Serie::findOrFail($id);
            $nombreSerie = $serie->serie;
            $serie->delete();
            session()->flash('message', "Serie '{$nombreSerie}' eliminada correctamente");
        } catch (\Exception $e) {
            session()->flash('error', 'Error al eliminar la serie: ' . $e->getMessage());
        }
    }

    public function getSeriesProperty()
    {
        return Serie::query()
            ->when($this->search, fn($q) => $q->where('serie', 'like', "%{$this->search}%"))
            ->when($this->tipo_documento, fn($q) => $q->where('tipo_documento', $this->tipo_documento))
            ->when($this->filtro_activo !== '', fn($q) => $q->where('activo', $this->filtro_activo))
            ->with(['empresa', 'sucursal'])
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }

    public function getStatsProperty()
    {
        return [
            'total' => Serie::count(),
            'activas' => Serie::where('activo', true)->count(),
            'inactivas' => Serie::where('activo', false)->count(),
            'por_tipo' => Serie::selectRaw('tipo_documento, COUNT(*) as count')
                ->groupBy('tipo_documento')
                ->pluck('count', 'tipo_documento'),
        ];
    }

    protected function getPageTitle(): string
    {
        return 'Series de Documentos';
    }

    protected function getBreadcrumb(): array
    {
        return [
            'admin.dashboard' => 'Dashboard',
            'admin.series.index' => 'Series de Documentos'
        ];
    }

    public function render()
    {
        return $this->renderWithLayout('livewire.admin.series.index', [
            'series' => $this->series,
            'tipos' => Serie::getTiposDocumento(),
            'stats' => $this->stats,
        ], [
            'description' => 'Gestión de numeración de documentos',
        ]);
    }
}
