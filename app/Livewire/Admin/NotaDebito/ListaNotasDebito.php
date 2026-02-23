<?php

namespace App\Livewire\Admin\NotaDebito;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Pago;

class ListaNotasDebito extends Component
{
    use WithPagination;

    public $search = '';
    public $estado = '';
    public $fecha_desde = '';
    public $fecha_hasta = '';

    protected $paginationTheme = 'bootstrap';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingEstado()
    {
        $this->resetPage();
    }

    public function updatingFechaDesde()
    {
        $this->resetPage();
    }

    public function updatingFechaHasta()
    {
        $this->resetPage();
    }

    public function anular($id)
    {
        $nota = Pago::where('tipo_pago', Pago::TIPO_NOTA_DEBITO)->findOrFail($id);
        $nota->update(['estado' => Pago::ESTADO_CANCELADO]);

        if (function_exists('activity')) {
            activity('nota_debito')
                ->performedOn($nota)
                ->causedBy(auth()->user())
                ->withProperties(['serie' => $nota->serie, 'numero' => $nota->numero, 'total' => $nota->total])
                ->log('Nota de débito anulada');
        }

        $this->dispatch('notify', type: 'success', message: 'Nota de débito anulada correctamente.');
    }

    public function render()
    {
        $query = Pago::with(['consulta.paciente', 'user', 'clienteFiscal', 'pagoOrigen', 'tipoNotaDebito', 'detalles'])
            ->where('tipo_pago', Pago::TIPO_NOTA_DEBITO)
            ->when($this->search, function ($q) {
                $q->where(function ($q) {
                    $q->where('serie', 'like', "%{$this->search}%")
                        ->orWhere('numero', 'like', "%{$this->search}%")
                        ->orWhereHas('consulta.paciente', fn($q) => $q->where('nombres', 'like', "%{$this->search}%"))
                        ->orWhereHas('clienteFiscal', fn($q) => $q->where('razon_social', 'like', "%{$this->search}%"));
                });
            })
            ->when($this->estado, fn($q) => $q->where('estado', $this->estado))
            ->when($this->fecha_desde, fn($q) => $q->whereDate('fecha', '>=', $this->fecha_desde))
            ->when($this->fecha_hasta, fn($q) => $q->whereDate('fecha', '<=', $this->fecha_hasta));

        $stats = [
            'total' => (clone $query)->count(),
            'aprobadas' => (clone $query)->where('estado', 'aprobado')->count(),
            'anuladas' => (clone $query)->where('estado', 'cancelado')->count(),
            'total_bs' => (clone $query)->where('estado', 'aprobado')->sum('total_bs')
        ];

        $notas = $query->latest()->paginate(15);

        return view('livewire.admin.nota-debito.lista-notas-debito', compact('notas', 'stats'));
    }
}
