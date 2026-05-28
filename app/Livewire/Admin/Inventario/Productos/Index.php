<?php

namespace App\Livewire\Admin\Inventario\Productos;

use App\Models\Producto;
use App\Models\CategoriaProducto;
use App\Models\Marca;
use App\Models\Proveedor;
use Livewire\Component;
use Livewire\WithPagination;
use App\Traits\HasDynamicLayout;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Index extends Component
{
    use WithPagination, HasDynamicLayout;

    public $search = '';
    public $categoria_id = '';
    public $marca_id = '';
    public $proveedor_id = '';
    public $status = '';
    public $alerta = '';
    public $sortField = 'nombre';
    public $sortDirection = 'asc';
    public $selected = [];

    protected $queryString = [
        'search'       => ['except' => ''],
        'categoria_id' => ['except' => ''],
        'marca_id'     => ['except' => ''],
        'proveedor_id' => ['except' => ''],
        'status'       => ['except' => ''],
        'alerta'       => ['except' => ''],
    ];

    protected $paginationTheme = 'bootstrap';

    public function updatingSearch()      { $this->resetPage(); }
    public function updatingCategoriaId() { $this->resetPage(); }
    public function updatingAlerta()      { $this->resetPage(); }

    public function sortBy($field)
    {
        $this->sortDirection = $this->sortField === $field
            ? ($this->sortDirection === 'asc' ? 'desc' : 'asc') : 'asc';
        $this->sortField = $field;
    }

    public function resetFilters()
    {
        $this->reset(['search', 'categoria_id', 'marca_id', 'proveedor_id', 'status', 'alerta']);
        $this->resetPage();
    }

    public function toggleStatus($id)
    {
        $this->authorize('edit productos');
        $p = Producto::findOrFail($id);
        $p->update(['status' => !$p->status]);
        $this->dispatch('notify', ['type' => 'success', 'message' => "Estado de '{$p->nombre}' actualizado.", 'duration' => 3000]);
    }

    public function delete($id)
    {
        dd( $this->authorize('delete productos'));
        try {
            $this->authorize('delete productos');
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'No tienes permiso para eliminar productos.', 'duration' => 4000]);
            return;
        }

        $p = Producto::findOrFail($id);
        $stockTotal = $p->stockTotal();
        
        if ($stockTotal > 0) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => "No se puede eliminar '{$p->nombre}': tiene {$stockTotal} {$p->unidad_medida} de stock.",
                'duration' => 5000
            ]);
            return;
        }
        
        $p->delete();
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => "Producto '{$p->nombre}' eliminado correctamente.",
            'duration' => 3000
        ]);
    }

    public function deleteSelected()
    {
        
        try {
            $this->authorize('delete productos');
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'No tienes permiso para eliminar productos.', 'duration' => 4000]);
            return;
        }

        if (empty($this->selected)) {
            $this->dispatch('notify', ['type' => 'warning', 'message' => 'No hay productos seleccionados.', 'duration' => 3000]);
            return;
        }

        $ids = $this->selected;
        $deleted = 0;
        $skipped = [];
        $errors = [];

        foreach ($ids as $id) {
            $p = Producto::find($id);
            
            if (!$p) {
                continue;
            }

           
            try {
                $p->delete();
                $deleted++;
            } catch (\Exception $e) {
                dd($e);
                $errors[] = $p->nombre;
            }
        }

        $this->selected = [];

        if ($deleted > 0) {
            $message = "$deleted producto(s) eliminado(s) correctamente.";
            
            if (count($skipped) > 0) {
                $skippedNames = array_map(fn($s) => "{$s['nombre']} ({$s['stock']} {$s['unidad']})", array_slice($skipped, 0, 3));
                $message .= ' No se eliminaron: ' . implode(', ', $skippedNames);
                if (count($skipped) > 3) $message .= ' + ' . (count($skipped) - 3) . ' más';
            }
            
            if (count($errors) > 0) {
                $message .= ' Errores: ' . implode(', ', array_slice($errors, 0, 3));
            }
            
            $this->dispatch('notify', ['type' => 'success', 'message' => $message, 'duration' => 5000]);
        } else {
            $message = 'No se eliminó ningún producto.';
            
            if (count($skipped) > 0) {
                $skippedNames = array_map(fn($s) => "{$s['nombre']} ({$s['stock']} {$s['unidad']})", array_slice($skipped, 0, 3));
                $message .= ' Todos tienen stock: ' . implode(', ', $skippedNames);
                if (count($skipped) > 3) $message .= ' + ' . (count($skipped) - 3) . ' más';
            } elseif (count($errors) > 0) {
                $message .= ' Errores al eliminar.';
            }
            
            $this->dispatch('notify', ['type' => 'error', 'message' => $message, 'duration' => 5000]);
        }
    }

    public function exportarCsv(): StreamedResponse
    {
        $productos = $this->buildQuery()->get();

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="productos_' . now()->format('Ymd_His') . '.csv"',
        ];

        return response()->streamDownload(function () use ($productos) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM UTF-8

            fputcsv($handle, ['Código', 'Nombre', 'SKU', 'Categoría', 'Marca', 'Proveedor',
                'Unidad', 'Precio Costo', 'Precio Venta', 'Stock Total',
                'Stock Mínimo', 'Punto Reorden', 'Vencimiento', 'Estado']);

            foreach ($productos as $p) {
                fputcsv($handle, [
                    $p->codigo,
                    $p->nombre,
                    $p->sku ?? '',
                    $p->categoria?->nombre ?? '',
                    $p->marca?->nombre ?? '',
                    $p->proveedor?->nombre ?? '',
                    $p->unidad_medida,
                    $p->precio_costo,
                    $p->precio_venta,
                    $p->stockTotal(),
                    $p->stock_minimo,
                    $p->punto_reorden,
                    $p->fecha_vencimiento?->format('d/m/Y') ?? '',
                    $p->status ? 'Activo' : 'Inactivo',
                ]);
            }
            fclose($handle);
        }, 'productos_' . now()->format('Ymd_His') . '.csv', $headers);
    }

    private function buildQuery()
    {
        return Producto::forUser()
            ->with(['categoria', 'marca', 'proveedor', 'stocks'])
            ->when($this->search, fn($q) => $q->where(fn($s) =>
                $s->where('nombre', 'like', "%{$this->search}%")
                  ->orWhere('codigo', 'like', "%{$this->search}%")
                  ->orWhere('sku',    'like', "%{$this->search}%")
            ))
            ->when($this->categoria_id, fn($q) => $q->where('categoria_producto_id', $this->categoria_id))
            ->when($this->marca_id,     fn($q) => $q->where('marca_id', $this->marca_id))
            ->when($this->proveedor_id, fn($q) => $q->where('proveedor_id', $this->proveedor_id))
            ->when($this->status !== '', fn($q) => $q->where('status', $this->status))
            ->when($this->alerta === 'sin_stock',  fn($q) => $q->sinStock())
            ->when($this->alerta === 'stock_bajo', fn($q) => $q->stockBajo())
            ->when($this->alerta === 'vencidos',   fn($q) => $q->vencidos())
            ->when($this->alerta === 'por_vencer', fn($q) => $q->proximosAVencer(90))
            ->orderBy($this->sortField, $this->sortDirection);
    }

    public function getProductosProperty()
    {
        return $this->buildQuery()->paginate(15);
    }

    public function getStatsProperty()
    {
        $q = Producto::forUser();
        return [
            'total'      => $q->count(),
            'activos'    => $q->where('status', true)->count(),
            'sin_stock'  => Producto::forUser()->sinStock()->count(),
            'por_vencer' => Producto::forUser()->proximosAVencer(90)->count(),
            'vencidos'   => Producto::forUser()->vencidos()->count(),
        ];
    }

    public function getCategoriasProperty()  { return CategoriaProducto::forUser()->activas()->orderBy('nombre')->get(); }
    public function getMarcasProperty()      { return Marca::forUser()->activas()->orderBy('nombre')->get(); }
    public function getProveedoresProperty() { return Proveedor::forUser()->activos()->orderBy('nombre')->get(); }

    public function render()
    {
        return view('livewire.admin.inventario.productos.index', [
            'productos'   => $this->productos,
            'stats'       => $this->stats,
            'categorias'  => $this->categorias,
            'marcas'      => $this->marcas,
            'proveedores' => $this->proveedores,
        ])->layout($this->getLayout());
    }
}
