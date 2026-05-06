<?php

namespace App\Livewire\Admin\Cajas;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\GastoCaja;
use App\Models\Caja;

class ListadoEgresos extends Component
{
    use WithPagination;

    public $search = '';
    public $filtro_categoria = '';
    public $filtro_metodo_pago = '';
    public $filtro_fecha_inicio = '';
    public $filtro_fecha_fin = '';
    public $caja_id = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'filtro_categoria' => ['except' => ''],
        'filtro_metodo_pago' => ['except' => ''],
    ];

    public function mount()
    {
        // Si hay una caja abierta, filtrar por ella
        $cajaAbierta = Caja::obtenerCajaAbierta(
            auth()->user()->empresa_id,
            auth()->user()->sucursal_id
        );

        if ($cajaAbierta) {
            $this->caja_id = $cajaAbierta->id;
        }
    }

    public function limpiarFiltros()
    {
        $this->reset(['search', 'filtro_categoria', 'filtro_metodo_pago', 'filtro_fecha_inicio', 'filtro_fecha_fin']);
    }

    public function eliminarEgreso($gastoId)
    {
        try {
            $gasto = GastoCaja::findOrFail($gastoId);

            // Verificar permisos
            if ($gasto->empresa_id !== auth()->user()->empresa_id) {
                $this->dispatch('notify', type: 'error', message: '❌ No tiene permisos para eliminar este egreso');
                return;
            }

            $gasto->delete();

            $this->dispatch('notify', type: 'success', message: '✅ Egreso eliminado correctamente');
            $this->dispatch('egreso-eliminado');

        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: '❌ Error al eliminar: ' . $e->getMessage());
        }
    }

    public function getEgresosProperty()
    {
        $query = GastoCaja::with(['caja', 'usuario'])
            ->where('empresa_id', auth()->user()->empresa_id)
            ->where('sucursal_id', auth()->user()->sucursal_id);

        // Filtros
        if ($this->caja_id) {
            $query->where('caja_id', $this->caja_id);
        }

        if ($this->search) {
            $query->where(function($q) {
                $q->where('concepto', 'like', '%' . $this->search . '%')
                  ->orWhere('observaciones', 'like', '%' . $this->search . '%')
                  ->orWhere('numero_referencia', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->filtro_categoria) {
            $query->where('categoria', $this->filtro_categoria);
        }

        if ($this->filtro_metodo_pago) {
            $query->where('metodo_pago', $this->filtro_metodo_pago);
        }

        if ($this->filtro_fecha_inicio) {
            $query->whereDate('fecha_gasto', '>=', $this->filtro_fecha_inicio);
        }

        if ($this->filtro_fecha_fin) {
            $query->whereDate('fecha_gasto', '<=', $this->filtro_fecha_fin);
        }

        return $query->orderBy('fecha_gasto', 'desc')->paginate(20);
    }

    public function getResumenProperty()
    {
        $query = GastoCaja::where('empresa_id', auth()->user()->empresa_id)
            ->where('sucursal_id', auth()->user()->sucursal_id)
            ->where('estado', 'aprobado');

        if ($this->caja_id) {
            $query->where('caja_id', $this->caja_id);
        }

        return [
            'total_monto' => $query->sum('monto'),
            'total_monto_bs' => $query->sum('monto_bs'),
            'cantidad' => $query->count(),
            'por_categoria' => $query->clone()
                ->selectRaw('categoria, SUM(monto) as total')
                ->groupBy('categoria')
                ->get(),
        ];
    }

    public function render()
    {
        return view('livewire.admin.cajas.listado-egresos', [
            'egresos' => $this->egresos,
            'resumen' => $this->resumen,
        ]);
    }
}
