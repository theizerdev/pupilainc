<?php

namespace App\Livewire\Admin\Cajas;

use App\Models\Caja;
use App\Models\Pago;
use App\Traits\Exportable;
use App\Traits\HasDynamicLayout;
use App\Traits\HasRegionalFormatting;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination, Exportable, HasDynamicLayout, HasRegionalFormatting;

    public $search = '';
    public $status = '';
    public $perPage = 20;
    public $sortBy = 'fecha';
    public $sortDirection = 'desc';

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
        'perPage' => ['except' => 20],
        'sortBy' => ['except' => 'fecha'],
        'sortDirection' => ['except' => 'desc'],
    ];

    public function sortBy($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->reset(['search', 'status', 'perPage']);
        $this->resetPage();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatus()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function cerrarCaja($cajaId)
    {
        $caja = Caja::findOrFail($cajaId);

        if ($caja->cerrar()) {
            session()->flash('message', 'Caja cerrada exitosamente.');
        } else {
            session()->flash('error', 'No se pudo cerrar la caja.');
        }
    }

    public function getStatsProperty()
    {
        $baseQuery = Caja::where('empresa_id', auth()->user()->empresa_id)
            ->where('sucursal_id', auth()->user()->sucursal_id);

        // Calcular los ingresos de hoy basados en los pagos aprobados del día actual
        $ingresosHoy = Pago::where('empresa_id', auth()->user()->empresa_id)
            ->where('sucursal_id', auth()->user()->sucursal_id)
            ->where('estado', 'aprobado')
            ->whereDate('fecha', today())
            ->sum('total_usd');

        return [
            'total' => (clone $baseQuery)->count() ?: 0,
            'abiertas' => (clone $baseQuery)->where('estado', 'abierta')->count() ?: 0,
            'cerradas' => (clone $baseQuery)->where('estado', 'cerrada')->count() ?: 0,
            'ingresos_hoy' => $ingresosHoy ?: 0,
        ];
    }

    public function getExportQuery()
    {
        $query = Caja::with(['usuario', 'sucursal'])
            ->where('empresa_id', auth()->user()->empresa_id)
            ->where('sucursal_id', auth()->user()->sucursal_id);

        if ($this->search) {
            $query->where(function ($q) {
                $q->whereDate('fecha', 'like', '%' . $this->search . '%')
                  ->orWhereHas('usuario', function ($q) {
                      $q->where('name', 'like', '%' . $this->search . '%');
                  });
            });
        }

        if ($this->status) {
            $query->where('estado', $this->status);
        }

        return $query->orderBy('created_at', 'desc');
    }

    public function getExportHeaders()
    {
        return [
            'Fecha',
            'Usuario',
            'Monto Inicial',
            'Total Efectivo',
            'Total Transferencias',
            'Total Tarjetas',
            'Total Ingresos',
            'Monto Final',
            'Estado',
            'Fecha Apertura',
            'Fecha Cierre'
        ];
    }

    public function formatExportRow($caja)
    {
        return [
            format_date($caja->fecha),
            $caja->usuario->name ?? '',
            format_money($caja->monto_inicial),
            format_money($caja->total_efectivo),
            format_money($caja->total_transferencias),
            format_money($caja->total_tarjetas),
            format_money($caja->total_ingresos),
            format_money($caja->monto_final),
            ucfirst($caja->estado),
            format_datetime($caja->fecha_apertura),
            $caja->fecha_cierre ? format_datetime($caja->fecha_cierre) : ''
        ];
    }

    protected function getPageTitle(): string
    {
        return 'Gestión de Cajas';
    }

    protected function getBreadcrumb(): array
    {
        return [
            'admin.dashboard' => 'Dashboard',
            'admin.cajas.index' => 'Cajas'
        ];
    }

    public function render()
    {
        $cajas = $this->getExportQuery()->paginate($this->perPage);
        return $this->renderWithLayout('livewire.admin.cajas.index', compact('cajas'), [
            'description' => 'Gestión de cajas diarias',
        ]);
    }
}
