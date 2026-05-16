<?php

namespace App\Livewire\Admin\Mascotas;

use Livewire\Component;
use App\Models\Mascota;
use App\Models\Especie;
use App\Models\Raza;
use App\Models\Propietario;
use Livewire\WithPagination;
use App\Traits\HasDynamicLayout;

class Index extends Component
{
    use WithPagination;
    use HasDynamicLayout;

    public $search = '';
    public $especieFilter = '';
    public $sexoFilter = '';
    public $showCreateModal = false;

    protected $queryString = ['search', 'especieFilter', 'sexoFilter'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingEspecieFilter()
    {
        $this->resetPage();
    }

    public function updatingSexoFilter()
    {
        $this->resetPage();
    }

    public function createMascota()
    {
        $this->showCreateModal = true;
    }

    public function closeCreateModal()
    {
        $this->showCreateModal = false;
        $this->dispatch('close-modal');
    }

    public function render()
    {
        $query = Mascota::with(['especie', 'raza', 'propietario'])
            ->forUser()
            ->activos();

        // Filtro de búsqueda
        if ($this->search) {
            $query->where(function($q) {
                $q->where('nombre', 'like', '%' . $this->search . '%')
                  ->orWhereHas('propietario', function($pq) {
                      $pq->where('nombres', 'like', '%' . $this->search . '%')
                        ->orWhere('apellidos', 'like', '%' . $this->search . '%')
                        ->orWhere('telefono', 'like', '%' . $this->search . '%');
                  })
                  ->orWhereHas('raza', function($rq) {
                      $rq->where('nombre', 'like', '%' . $this->search . '%');
                  });
            });
        }

        // Filtro por especie
        if ($this->especieFilter) {
            $query->where('especie_id', $this->especieFilter);
        }

        // Filtro por sexo
        if ($this->sexoFilter) {
            $query->where('sexo', $this->sexoFilter);
        }

        $mascotas = $query->orderBy('nombre')->paginate(15);
        // Usar withoutGlobalScopes para obtener todas las especies activas
        $especies = Especie::withoutGlobalScopes()->activas()->ordenadas()->get();

        // Estadísticas rápidas
        $stats = [
            'total' => Mascota::forUser()->activos()->count(),
            'perros' => Mascota::forUser()->activos()->porEspecie(
                Especie::where('nombre', 'Perro')->value('id')
            )->count(),
            'gatos' => Mascota::forUser()->activos()->porEspecie(
                Especie::where('nombre', 'Gato')->value('id')
            )->count(),
            'otros' => Mascota::forUser()->activos()
                ->whereNotIn('especie_id', [
                    Especie::where('nombre', 'Perro')->value('id'),
                    Especie::where('nombre', 'Gato')->value('id')
                ])
                ->count(),
        ];

        return view('livewire.admin.mascotas.index', [
            'mascotas' => $mascotas,
            'especies' => $especies,
            'stats' => $stats,
        ])->layout($this->getLayout());
    }
}
