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
    public $showModal = false;
    public $editingId = null;

    public $codigo, $nombre, $tipo_cuenta, $naturaleza, $nivel, $cuenta_padre_id, $acepta_movimientos, $descripcion, $activo = true;

    protected $paginationTheme = 'bootstrap';

    protected function rules()
    {
        return [
            'codigo' => 'required|string|max:20|unique:cuentas_contables,codigo,' . $this->editingId,
            'nombre' => 'required|string|max:255',
            'tipo_cuenta' => 'required|in:activo,pasivo,patrimonio,ingreso,egreso,costo',
            'naturaleza' => 'required|in:deudora,acreedora',
            'nivel' => 'required|integer|min:1|max:10',
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

    public function create()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit($id)
    {
        $cuenta = CuentaContable::findOrFail($id);
        $this->editingId = $cuenta->id;
        $this->codigo = $cuenta->codigo;
        $this->nombre = $cuenta->nombre;
        $this->tipo_cuenta = $cuenta->tipo;
        $this->naturaleza = $cuenta->naturaleza;
        $this->nivel = $cuenta->nivel;
        $this->cuenta_padre_id = $cuenta->cuenta_padre_id;
        $this->acepta_movimientos = $cuenta->acepta_movimientos;
        $this->descripcion = $cuenta->descripcion;
        $this->activo = $cuenta->activo;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        $data = [
            'codigo' => $this->codigo,
            'nombre' => $this->nombre,
            'tipo' => $this->tipo_cuenta,
            'naturaleza' => $this->naturaleza,
            'nivel' => $this->nivel,
            'cuenta_padre_id' => $this->cuenta_padre_id,
            'acepta_movimientos' => $this->acepta_movimientos,
            'descripcion' => $this->descripcion,
            'activo' => $this->activo,
            'empresa_id' => auth()->user()->empresa_id,
            'sucursal_id' => auth()->user()->sucursal_id
        ];

        if ($this->editingId) {
            CuentaContable::findOrFail($this->editingId)->update($data);
            session()->flash('success', 'Cuenta actualizada exitosamente');
        } else {
            CuentaContable::create($data);
            session()->flash('success', 'Cuenta creada exitosamente');
        }

        $this->closeModal();
    }

    public function delete($id)
    {
        CuentaContable::findOrFail($id)->delete();
        session()->flash('success', 'Cuenta eliminada exitosamente');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
        $this->resetValidation();
    }

    private function resetForm()
    {
        $this->reset(['editingId', 'codigo', 'nombre', 'tipo_cuenta', 'naturaleza', 'nivel', 'cuenta_padre_id', 'acepta_movimientos', 'descripcion']);
        $this->activo = true;
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
        $cuentas = CuentaContable::where('empresa_id', auth()->user()->empresa_id)
            ->when($this->search, fn($q) => $q->where('codigo', 'like', "%{$this->search}%")
                ->orWhere('nombre', 'like', "%{$this->search}%"))
            ->when($this->tipo, fn($q) => $q->where('tipo', $this->tipo))
            ->orderBy('codigo')
            ->paginate(20);

        $cuentasPadre = CuentaContable::where('empresa_id', auth()->user()->empresa_id)
            ->where('activo', true)
            ->orderBy('codigo')
            ->get();

        return view('livewire.admin.contabilidad.plan-cuentas', compact('cuentas', 'cuentasPadre'))
            ->layout($this->getLayout());
    }
}
