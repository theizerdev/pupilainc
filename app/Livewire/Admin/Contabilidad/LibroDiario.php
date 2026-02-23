<?php

namespace App\Livewire\Admin\Contabilidad;

use App\Traits\HasDynamicLayout;
use Livewire\Component;
use App\Models\AsientoContable;

class LibroDiario extends Component
{
    use HasDynamicLayout;

    public $fecha_desde;
    public $fecha_hasta;
    public $tipo = '';

    protected $queryString = [
        'tipo' => ['except' => ''],
    ];

    public function mount()
    {
        $this->authorize('access contabilidad');
        $this->fecha_desde = now()->startOfMonth()->format('Y-m-d');
        $this->fecha_hasta = now()->endOfMonth()->format('Y-m-d');
    }

    public function resetFilters()
    {
        $this->tipo = '';
        $this->fecha_desde = now()->startOfMonth()->format('Y-m-d');
        $this->fecha_hasta = now()->endOfMonth()->format('Y-m-d');
    }

    public function getAsientosProperty()
    {
        return AsientoContable::with(['detalles.cuenta', 'user'])
            ->where('empresa_id', auth()->user()->empresa_id)
            ->where('estado', 'aprobado')
            ->when($this->tipo, fn($q) => $q->where('tipo', $this->tipo))
            ->whereBetween('fecha', [$this->fecha_desde, $this->fecha_hasta])
            ->orderBy('fecha')
            ->orderBy('numero')
            ->get();
    }

    public function getStatsProperty()
    {
        $asientos = $this->asientos;
        return [
            'total_asientos' => $asientos->count(),
            'total_debe' => $asientos->sum(fn($a) => $a->detalles->sum('debe')),
            'total_haber' => $asientos->sum(fn($a) => $a->detalles->sum('haber')),
        ];
    }

    public function render()
    {
        return view('livewire.admin.contabilidad.libro-diario', [
            'asientos' => $this->asientos,
            'stats' => $this->stats,
        ])->layout($this->getLayout());
    }
}
