<?php

namespace App\Livewire\Admin\Baremo;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use App\Models\Baremo;
use App\Models\Especialidad;
use App\Models\ExchangeRate;
use App\Traits\HasDynamicLayout;

class GestionBaremos extends Component
{
    use WithPagination, HasDynamicLayout;

    public $modal = false;
    public $baremo_id;
    public $especialidad_id;
    public $codigo;
    public $nombre_servicio;
    public $descripcion;
    public $costo_usd;
    public $aplica_iva = true;
    public $exento_iva = false;
    public $duracion_minutos = 30;
    public $activo = true;
    
    // Filtros
    public $search = '';
    public $filtro_especialidad = '';
    public $filtro_estado = '';
    public $filtro_iva = '';
    public $vista = 'tabla'; // tabla o tarjetas
    public $perPage = 15;

    protected $paginationTheme = 'bootstrap';

    protected function rules()
    {
        $rules = [
            'especialidad_id' => 'required',
            'codigo' => 'required|unique:baremos,codigo',
            'nombre_servicio' => 'required|min:3',
            'costo_usd' => 'required|numeric|gt:0',
            'duracion_minutos' => 'nullable|numeric|min:5'
        ];

        if ($this->baremo_id) {
            $rules['codigo'] = 'required|unique:baremos,codigo,' . $this->baremo_id;
        }

        return $rules;
    }

    protected $messages = [
        'especialidad_id.required' => 'La especialidad es obligatoria',
        'codigo.required' => 'El código es obligatorio',
        'codigo.unique' => 'Este código ya está en uso',
        'nombre_servicio.required' => 'El nombre del servicio es obligatorio',
        'nombre_servicio.min' => 'El nombre debe tener al menos 3 caracteres',
        'costo_usd.required' => 'El costo es obligatorio',
        'costo_usd.numeric' => 'El costo debe ser un número',
        'costo_usd.gt' => 'El costo debe ser mayor a 0',
        'duracion_minutos.min' => 'La duración mínima es 5 minutos'
    ];

    public function crear()
    {
        $this->resetValidation();
        $this->reset(['baremo_id', 'especialidad_id', 'codigo', 'nombre_servicio', 'descripcion', 'costo_usd', 'duracion_minutos']);
        $this->aplica_iva = true;
        $this->exento_iva = false;
        $this->activo = true;
        $this->modal = true;
    }

    public function limpiarFiltros()
    {
        $this->reset(['search', 'filtro_especialidad', 'filtro_estado', 'filtro_iva']);
        $this->resetPage();
    }

    public function cambiarVista($vista)
    {
        $this->vista = $vista;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFiltroEspecialidad()
    {
        $this->resetPage();
    }

    public function updatingFiltroEstado()
    {
        $this->resetPage();
    }

    public function updatingFiltroIva()
    {
        $this->resetPage();
    }

    public function editar($id)
    {
        $baremo = Baremo::findOrFail($id);
        $this->baremo_id = $baremo->id;
        $this->especialidad_id = $baremo->especialidad_id;
        $this->codigo = $baremo->codigo;
        $this->nombre_servicio = $baremo->nombre_servicio;
        $this->descripcion = $baremo->descripcion;
        $this->costo_usd = $baremo->costo_usd;
        $this->aplica_iva = $baremo->aplica_iva;
        $this->exento_iva = $baremo->exento_iva;
        $this->duracion_minutos = $baremo->duracion_minutos;
        $this->activo = $baremo->activo;
        $this->modal = true;
    }

    public function guardar()
    {
        $this->validate();

        $data = [
            'especialidad_id' => $this->especialidad_id,
            'codigo' => strtoupper(trim($this->codigo)),
            'nombre_servicio' => trim($this->nombre_servicio),
            'descripcion' => $this->descripcion,
            'costo_usd' => $this->costo_usd,
            'aplica_iva' => $this->aplica_iva,
            'exento_iva' => $this->exento_iva,
            'duracion_minutos' => $this->duracion_minutos ?? 30,
            'activo' => $this->activo,
            'empresa_id' => auth()->user()->empresa_id,
            'sucursal_id' => auth()->user()->sucursal_id
        ];

        try {
            if ($this->baremo_id) {
                Baremo::find($this->baremo_id)->update($data);
                $this->dispatch('notify', type: 'success', message: 'Baremo actualizado exitosamente');
            } else {
                Baremo::create($data);
                $this->dispatch('notify', type: 'success', message: 'Baremo creado exitosamente');
            }

            $this->modal = false;
            $this->reset(['baremo_id', 'especialidad_id', 'codigo', 'nombre_servicio', 'descripcion', 'costo_usd', 'duracion_minutos']);
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: 'Error al guardar: ' . $e->getMessage());
        }
    }

    #[On('confirmarEliminacion')]
    public function eliminar($id)
    {
        try {
            $baremo = Baremo::find($id);
            if ($baremo) {
                $baremo->delete();
                $this->dispatch('notify', type: 'success', message: 'Baremo eliminado exitosamente');
            }
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: 'Error al eliminar: ' . $e->getMessage());
        }
    }

    public function toggleEstado($id)
    {
        try {
            $baremo = Baremo::find($id);
            if ($baremo) {
                $baremo->activo = !$baremo->activo;
                $baremo->save();
                $estado = $baremo->activo ? 'activado' : 'desactivado';
                $this->dispatch('notify', type: 'success', message: "Baremo {$estado} exitosamente");
            }
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: 'Error al cambiar estado');
        }
    }

    public function duplicar($id)
    {
        try {
            $baremo = Baremo::find($id);
            if ($baremo) {
                $nuevo = $baremo->replicate();
                $nuevo->codigo = $baremo->codigo . '-COPIA';
                $nuevo->nombre_servicio = $baremo->nombre_servicio . ' (Copia)';
                $nuevo->save();
                $this->dispatch('notify', type: 'success', message: 'Baremo duplicado exitosamente');
            }
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: 'Error al duplicar');
        }
    }

    public function render()
    {
        $query = Baremo::with('especialidad');

        // Filtro de búsqueda
        if ($this->search) {
            $query->where(function($q) {
                $q->where('nombre_servicio', 'like', "%{$this->search}%")
                  ->orWhere('codigo', 'like', "%{$this->search}%")
                  ->orWhere('descripcion', 'like', "%{$this->search}%");
            });
        }

        // Filtro por especialidad
        if ($this->filtro_especialidad) {
            $query->where('especialidad_id', $this->filtro_especialidad);
        }

        // Filtro por estado
        if ($this->filtro_estado !== '') {
            $query->where('activo', $this->filtro_estado);
        }

        // Filtro por IVA
        if ($this->filtro_iva === 'aplica') {
            $query->where('aplica_iva', true)->where('exento_iva', false);
        } elseif ($this->filtro_iva === 'exento') {
            $query->where('exento_iva', true);
        } elseif ($this->filtro_iva === 'no_aplica') {
            $query->where('aplica_iva', false);
        }

        $baremos = $query->latest()->paginate($this->perPage);
        $especialidades = Especialidad::where('status', true)->orderBy('nombre')->get();
        $tasa_usd = ExchangeRate::getLatestRate('USD') ?? 1;

        // Estadísticas
        $stats = [
            'total' => Baremo::count(),
            'activos' => Baremo::where('activo', true)->count(),
            'inactivos' => Baremo::where('activo', false)->count(),
            'con_iva' => Baremo::where('aplica_iva', true)->where('exento_iva', false)->count(),
        ];

        return view('livewire.admin.baremo.gestion-baremos', compact('baremos', 'especialidades', 'tasa_usd', 'stats'))
            ->layout($this->getLayout());
    }
}
