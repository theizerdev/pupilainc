<?php

namespace App\Livewire\Admin\Contabilidad;

use App\Traits\HasDynamicLayout;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\AsientoContable;
use Carbon\Carbon;

class Asientos extends Component
{
    use HasDynamicLayout, WithPagination;

    public $search = '';
    public $tipo = '';
    public $estado = '';
    public $fecha_desde;
    public $fecha_hasta;
    public $showDetalles = false;
    public $asientoSeleccionado;

    protected $paginationTheme = 'bootstrap';

    public function mount()
    {
        $this->fecha_desde = now()->startOfMonth()->format('Y-m-d');
        $this->fecha_hasta = now()->endOfMonth()->format('Y-m-d');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function verDetalles($id)
    {
        $this->asientoSeleccionado = AsientoContable::with(['detalles.cuenta', 'user'])
            ->findOrFail($id);
        $this->showDetalles = true;
    }

    public function closeDetalles()
    {
        $this->showDetalles = false;
        $this->asientoSeleccionado = null;
    }

    public function anular($id)
    {
        $asiento = AsientoContable::findOrFail($id);
        
        if ($asiento->estado === 'anulado') {
            session()->flash('error', 'El asiento ya está anulado');
            return;
        }

        $asiento->update(['estado' => 'anulado']);
        session()->flash('success', 'Asiento anulado exitosamente');
    }

    protected function getPageTitle(): string
    {
        return 'Asientos Contables';
    }

    protected function getBreadcrumb(): array
    {
        return [
            'admin.dashboard' => 'Dashboard',
            'admin.contabilidad.asientos' => 'Asientos Contables'
        ];
    }

    public function render()
    {
        $asientos = AsientoContable::with(['user', 'detalles'])
            ->where('empresa_id', auth()->user()->empresa_id)
            ->when($this->search, fn($q) => $q->where('numero', 'like', "%{$this->search}%")
                ->orWhere('descripcion', 'like', "%{$this->search}%"))
            ->when($this->tipo, fn($q) => $q->where('tipo', $this->tipo))
            ->when($this->estado, fn($q) => $q->where('estado', $this->estado))
            ->when($this->fecha_desde, fn($q) => $q->whereDate('fecha', '>=', $this->fecha_desde))
            ->when($this->fecha_hasta, fn($q) => $q->whereDate('fecha', '<=', $this->fecha_hasta))
            ->orderBy('fecha', 'desc')
            ->orderBy('numero', 'desc')
            ->paginate(20);

        return view('livewire.admin.contabilidad.asientos', compact('asientos'))
            ->layout($this->getLayout());
    }
}
