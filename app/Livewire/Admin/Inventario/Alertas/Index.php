<?php

namespace App\Livewire\Admin\Inventario\Alertas;

use App\Models\Producto;
use Livewire\Component;
use App\Traits\HasDynamicLayout;

class Index extends Component
{
    use HasDynamicLayout;

    public $tab = 'sin_stock';

    public function getAlertasProperty(): array
    {
        return [
            'sin_stock'  => Producto::forUser()->with(['categoria', 'stocks'])->sinStock()->activos()->get(),
            'critico'    => Producto::forUser()->with(['categoria', 'stocks'])
                                ->whereHas('stocks', fn($q) => $q->where('cantidad', '>', 0)
                                    ->whereColumn('cantidad', '<=', 'productos.stock_minimo'))
                                ->activos()->get(),
            'stock_bajo' => Producto::forUser()->with(['categoria', 'stocks'])->stockBajo()->activos()->get(),
            'por_vencer' => Producto::forUser()->with(['categoria', 'stocks'])->proximosAVencer(90)->activos()->get(),
            'vencidos'   => Producto::forUser()->with(['categoria', 'stocks'])->vencidos()->activos()->get(),
        ];
    }

    public function getResumenProperty(): array
    {
        return [
            'sin_stock'  => Producto::forUser()->sinStock()->activos()->count(),
            'critico'    => Producto::forUser()->whereHas('stocks', fn($q) =>
                                $q->where('cantidad', '>', 0)->whereColumn('cantidad', '<=', 'productos.stock_minimo')
                            )->activos()->count(),
            'stock_bajo' => Producto::forUser()->stockBajo()->activos()->count(),
            'por_vencer' => Producto::forUser()->proximosAVencer(90)->activos()->count(),
            'vencidos'   => Producto::forUser()->vencidos()->activos()->count(),
        ];
    }

    public function render()
    {
        return view('livewire.admin.inventario.alertas.index', [
            'alertas' => $this->alertas,
            'resumen' => $this->resumen,
        ])->layout($this->getLayout());
    }
}
