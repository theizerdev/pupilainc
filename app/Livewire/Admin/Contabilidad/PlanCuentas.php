<?php

namespace App\Livewire\Admin\Contabilidad;

use App\Traits\HasDynamicLayout;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\CuentaContable;

class PlanCuentas extends Component
{
    use HasDynamicLayout, WithPagination;

    public $search = '';
    public $tipo = '';
    public $naturaleza = '';
    public $nivel = '';
    public $status = '';
    public $acepta_movimientos_filter = '';
    public $showModal = false;
    public $editingId = null;
    public $sortField = 'codigo';
    public $sortDirection = 'asc';
    public $perPage = 20;

    public $codigo, $nombre, $tipo_cuenta, $naturaleza_cuenta, $nivel_cuenta, $cuenta_padre_id, $acepta_movimientos, $descripcion, $activo = true;

    protected $queryString = [
        'search' => ['except' => ''],
        'tipo' => ['except' => ''],
        'naturaleza' => ['except' => ''],
        'nivel' => ['except' => ''],
        'status' => ['except' => ''],
        'sortField' => ['except' => 'codigo'],
        'sortDirection' => ['except' => 'asc'],
    ];

    protected $paginationTheme = 'bootstrap';

    public function mount()
    {
        $this->authorize('access contabilidad');
    }

    protected function rules()
    {
        return [
            'codigo' => 'required|string|max:20|unique:cuentas_contables,codigo,' . $this->editingId,
            'nombre' => 'required|string|max:255',
            'tipo_cuenta' => 'required|in:activo,pasivo,patrimonio,ingreso,egreso,costo',
            'naturaleza_cuenta' => 'required|in:deudora,acreedora',
            'nivel_cuenta' => 'required|integer|min:1|max:10',
            'cuenta_padre_id' => 'nullable|exists:cuentas_contables,id',
            'acepta_movimientos' => 'boolean',
            'descripcion' => 'nullable|string',
            'activo' => 'boolean'
        ];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingTipo()
    {
        $this->resetPage();
    }

    public function updatingNaturaleza()
    {
        $this->resetPage();
    }

    public function updatingNivel()
    {
        $this->resetPage();
    }

    public function updatingStatus()
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
        $this->reset(['search', 'tipo', 'naturaleza', 'nivel', 'status', 'acepta_movimientos_filter']);
        $this->resetPage();
    }

    public function create()
    {
        $this->authorize('create contabilidad');
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit($id)
    {
        $this->authorize('edit contabilidad');
        $cuenta = CuentaContable::findOrFail($id);
        $this->editingId = $cuenta->id;
        $this->codigo = $cuenta->codigo;
        $this->nombre = $cuenta->nombre;
        $this->tipo_cuenta = $cuenta->tipo;
        $this->naturaleza_cuenta = $cuenta->naturaleza;
        $this->nivel_cuenta = $cuenta->nivel;
        $this->cuenta_padre_id = $cuenta->cuenta_padre_id;
        $this->acepta_movimientos = $cuenta->acepta_movimientos;
        $this->descripcion = $cuenta->descripcion;
        $this->activo = $cuenta->activo;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        try {
            $data = [
                'codigo' => $this->codigo,
                'nombre' => $this->nombre,
                'tipo' => $this->tipo_cuenta,
                'naturaleza' => $this->naturaleza_cuenta,
                'nivel' => $this->nivel_cuenta,
                'cuenta_padre_id' => $this->cuenta_padre_id,
                'acepta_movimientos' => $this->acepta_movimientos,
                'descripcion' => $this->descripcion,
                'activo' => $this->activo,
                'empresa_id' => auth()->user()->empresa_id,
                'sucursal_id' => auth()->user()->sucursal_id
            ];

            if ($this->editingId) {
                CuentaContable::findOrFail($this->editingId)->update($data);
                $mensaje = 'Cuenta actualizada exitosamente';
            } else {
                CuentaContable::create($data);
                $mensaje = 'Cuenta creada exitosamente';
            }

            $this->closeModal();
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => $mensaje,
            ]);
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al guardar la cuenta: ' . $e->getMessage(),
            ]);
        }
    }

    public function toggleStatus($id)
    {
        $this->authorize('edit contabilidad');

        try {
            $cuenta = CuentaContable::findOrFail($id);
            $cuenta->activo = !$cuenta->activo;
            $cuenta->save();

            $mensaje = $cuenta->activo
                ? 'Cuenta activada exitosamente.'
                : 'Cuenta desactivada exitosamente.';

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => $mensaje,
            ]);
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al actualizar el estado: ' . $e->getMessage(),
            ]);
        }
    }

    public function delete($id)
    {
        $this->authorize('delete contabilidad');

        try {
            $cuenta = CuentaContable::findOrFail($id);
            $nombreCuenta = $cuenta->codigo . ' - ' . $cuenta->nombre;
            $cuenta->delete();

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => "Cuenta '{$nombreCuenta}' eliminada exitosamente.",
            ]);
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al eliminar la cuenta: ' . $e->getMessage(),
            ]);
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
        $this->resetValidation();
    }

    private function resetForm()
    {
        $this->reset(['editingId', 'codigo', 'nombre', 'tipo_cuenta', 'naturaleza_cuenta', 'nivel_cuenta', 'cuenta_padre_id', 'acepta_movimientos', 'descripcion']);
        $this->activo = true;
    }

    public function getCuentasProperty()
    {
        return CuentaContable::where('empresa_id', auth()->user()->empresa_id)
            ->when($this->search, fn($q) => $q->where('codigo', 'like', "%{$this->search}%")
                ->orWhere('nombre', 'like', "%{$this->search}%"))
            ->when($this->tipo, fn($q) => $q->where('tipo', $this->tipo))
            ->when($this->naturaleza, fn($q) => $q->where('naturaleza', $this->naturaleza))
            ->when($this->nivel, fn($q) => $q->where('nivel', $this->nivel))
            ->when($this->status !== '', fn($q) => $q->where('activo', $this->status))
            ->when($this->acepta_movimientos_filter !== '', fn($q) => $q->where('acepta_movimientos', $this->acepta_movimientos_filter))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }

    public function getStatsProperty()
    {
        $empresaId = auth()->user()->empresa_id;
        return [
            'total' => CuentaContable::where('empresa_id', $empresaId)->count(),
            'activas' => CuentaContable::where('empresa_id', $empresaId)->where('activo', true)->count(),
            'inactivas' => CuentaContable::where('empresa_id', $empresaId)->where('activo', false)->count(),
            'con_movimientos' => CuentaContable::where('empresa_id', $empresaId)->where('acepta_movimientos', true)->count(),
        ];
    }

    public function getCuentasPadreProperty()
    {
        return CuentaContable::where('empresa_id', auth()->user()->empresa_id)
            ->where('activo', true)
            ->orderBy('codigo')
            ->get();
    }

    protected function getPageTitle(): string
    {
        return 'Plan de Cuentas';
    }

    protected function getBreadcrumb(): array
    {
        return [
            'admin.dashboard' => 'Dashboard',
            'admin.contabilidad.plan-cuentas' => 'Plan de Cuentas'
        ];
    }

    public function render()
    {
        return view('livewire.admin.contabilidad.plan-cuentas', [
            'cuentas' => $this->cuentas,
            'cuentasPadre' => $this->cuentasPadre,
            'stats' => $this->stats,
        ])->layout($this->getLayout());
    }
}
