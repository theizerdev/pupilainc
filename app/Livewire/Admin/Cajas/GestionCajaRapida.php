<?php

namespace App\Livewire\Admin\Cajas;

use Livewire\Component;
use App\Models\Caja;
use App\Models\GastoCaja;
use App\Models\ExchangeRate;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class GestionCajaRapida extends Component
{
    // Estado de la caja
    public $caja = null;
    public $caja_abierta = false;

    // Para apertura
    public $monto_inicial = 0;
    public $observaciones_apertura = '';
    public $mostrarModalApertura = false;

    // Para egresos
    public $concepto_gasto = '';
    public $monto_gasto = 0;
    public $metodo_pago_gasto = 'efectivo';
    public $referencia_gasto = '';
    public $categoria_gasto = '';
    public $observaciones_gasto = '';
    public $mostrarModalEgreso = false;

    // Para cierre
    public $observaciones_cierre = '';
    public $mostrarModalCierre = false;

    // Resumen
    public $total_ingresos = 0;
    public $total_egresos = 0;
    public $monto_final_ajustado = 0;
    public $tasa_cambio = 1;

    // Filtros
    public $filtro_fecha = 'hoy'; // hoy, semana, mes

    protected $listeners = [
        'caja-actualizada' => '$refresh',
        'pago-registrado' => 'actualizarResumen',
    ];

    // Polling para actualización en tiempo real (cada 5 segundos)
    public function polling()
    {
        if ($this->caja_abierta && $this->caja) {
            $this->actualizarResumen();
        }
    }

    public function mount()
    {
        $this->verificarCaja();
        $this->cargarTasaCambio();

    }

    /**
     * Verificar estado de la caja
     */
    public function verificarCaja()
    {
        $this->caja = Caja::obtenerCajaAbierta(
            auth()->user()->empresa_id,
            auth()->user()->sucursal_id
        );

        $this->caja_abierta = $this->caja !== null;

        if ($this->caja_abierta) {
            $this->actualizarResumen();
        }
    }

    /**
     * Cargar tasa de cambio
     */
    public function cargarTasaCambio()
    {
        $paisId = auth()->user()->empresa?->pais_id;
        $cacheKey = "tasa_cambio_usd_{$paisId}";

        $this->tasa_cambio = Cache::remember($cacheKey, now()->addHour(), function () use ($paisId) {
            return ExchangeRate::getLatestRate('USD', $paisId) ?? 1;
        });
    }

    /**
     * Actualizar resumen de caja
     */
    public function actualizarResumen()
    {
        if (!$this->caja) {
            $this->verificarCaja();
            return;
        }

        // Recargar la caja desde la base de datos para obtener los últimos totales
        $this->caja = $this->caja->fresh();

        if ($this->caja) {
            $this->total_ingresos = $this->caja->total_ingresos ?? 0;
            $this->total_egresos = $this->caja->total_egresos ?? 0;
            $this->monto_final_ajustado = $this->caja->monto_final_ajustado ?? 0;
        }
    }

    /**
     * Mostrar modal de apertura
     */
    public function mostrarApertura()
    {
        $this->reset(['monto_inicial', 'observaciones_apertura']);
        $this->mostrarModalApertura = true;
    }

    /**
     * Aperturar caja
     */
    public function aperturarCaja()
    {
        $this->validate([
            'monto_inicial' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $this->caja = Caja::crearCorte(
                auth()->user()->empresa_id,
                auth()->user()->sucursal_id,
                $this->monto_inicial,
                $this->observaciones_apertura,
                auth()->id()
            );

            $this->caja_abierta = true;
            $this->mostrarModalApertura = false;

            DB::commit();

            $this->dispatch('notify', type: 'success', message: '✅ Caja aperturada exitosamente');
            $this->dispatch('caja-aperturada', ['caja_id' => $this->caja->id]);

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('notify', type: 'error', message: '❌ Error al aperturar caja: ' . $e->getMessage());
        }
    }

    /**
     * Mostrar modal de egreso
     */
    public function mostrarEgreso()
    {
        if (!$this->caja_abierta) {
            $this->dispatch('notify', type: 'warning', message: '⚠️ Debe aperturar una caja primero');
            return;
        }

        $this->reset(['concepto_gasto', 'monto_gasto', 'metodo_pago_gasto', 'referencia_gasto', 'categoria_gasto', 'observaciones_gasto']);
        $this->mostrarModalEgreso = true;
    }

    /**
     * Registrar egreso
     */
    public function registrarEgreso()
    {
        $this->validate([
            'concepto_gasto' => 'required|string|min:3|max:255',
            'monto_gasto' => 'required|numeric|min:0.01',
            'metodo_pago_gasto' => 'required|string',
        ]);

        try {
            DB::beginTransaction();

            $gasto = $this->caja->registrarEgreso([
                'concepto' => $this->concepto_gasto,
                'observaciones' => $this->observaciones_gasto,
                'monto' => $this->monto_gasto,
                'metodo_pago' => $this->metodo_pago_gasto,
                'numero_referencia' => $this->referencia_gasto,
                'categoria' => $this->categoria_gasto,
            ]);

            DB::commit();

            $this->mostrarModalEgreso = false;
            $this->actualizarResumen();

            $this->dispatch('notify', type: 'success', message: '✅ Egreso registrado: $' . number_format($this->monto_gasto, 2));
            $this->dispatch('egreso-registrado', ['gasto_id' => $gasto->id]);

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('notify', type: 'error', message: '❌ Error al registrar egreso: ' . $e->getMessage());
        }
    }

    /**
     * Mostrar modal de cierre
     */
    public function mostrarCierre()
    {
        if (!$this->caja_abierta) {
            $this->dispatch('notify', type: 'warning', message: '⚠️ No hay caja abierta para cerrar');
            return;
        }

        $this->observaciones_cierre = '';
        $this->mostrarModalCierre = true;
    }

    /**
     * Cerrar caja
     */
    public function cerrarCaja()
    {
        if (!$this->caja) {
            $this->dispatch('notify', type: 'error', message: '❌ No hay caja para cerrar');
            return;
        }

        try {
            DB::beginTransaction();

            // Recalcular totales antes de cerrar
            $this->caja->calcularTotales();

            // Cerrar caja
            $this->caja->cerrar($this->observaciones_cierre);

            DB::commit();

            $this->caja_abierta = false;
            $this->caja = null;
            $this->mostrarModalCierre = false;

            $this->dispatch('notify', type: 'success', message: '✅ Caja cerrada exitosamente');
            $this->dispatch('caja-cerrada', ['caja_id' => $this->caja->id ?? null]);

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('notify', type: 'error', message: '❌ Error al cerrar caja: ' . $e->getMessage());
        }
    }

    /**
     * Obtener últimos egresos
     */
    public function getUltimosEgresosProperty()
    {
        if (!$this->caja) {
            return collect();
        }

        return $this->caja->gastos()
            ->where('estado', 'aprobado')
            ->with('usuario')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
    }

    /**
     * Obtener resumen por categoría
     */
    public function getResumenPorCategoriaProperty()
    {
        if (!$this->caja) {
            return collect();
        }

        return $this->caja->gastos()
            ->where('estado', 'aprobado')
            ->selectRaw('categoria, COUNT(*) as cantidad, SUM(monto) as total')
            ->groupBy('categoria')
            ->orderByDesc('total')
            ->get();
    }

    public function getStatsProperty()
    {
        $caja = \App\Models\Caja::where('empresa_id', auth()->user()->empresa_id)
            ->where('sucursal_id', auth()->user()->sucursal_id)
            ->where('estado', 'abierta')
            ->first();



        // Calcular los ingresos de hoy basados en los pagos aprobados del día actual
        // Se cuentan todos los pagos aprobados hoy, independientemente de la caja
        $ingresosHoy = \App\Models\Pago::where('empresa_id', auth()->user()->empresa_id)
            ->where('sucursal_id', auth()->user()->sucursal_id)
            ->where('estado', 'aprobado')
            ->whereDate('fecha', today()) // Filtrar por fecha del pago
            ->where('caja_id', $caja->id)
            ->sum('total_usd');

        $gastosHoy = \App\Models\GastoCaja::where('empresa_id', auth()->user()->empresa_id)
            ->where('sucursal_id', auth()->user()->sucursal_id)
            ->where('estado', 'aprobado')
            ->whereDate('created_at', today())
            ->where('caja_id', $caja->id)
            ->sum('monto');



        $totalBalance = $ingresosHoy - $gastosHoy;

        return [
            'total' => (clone $caja)->count() ?: 0,
            'total_balance' => $totalBalance ?: 0,
            'egresos_hoy' => $gastosHoy ?: 0,
            'ingresos_hoy' => $ingresosHoy ?: 0,
        ];
    }


    public function render()
    {
        return view('livewire.admin.cajas.gestion-rapida');
    }
}
