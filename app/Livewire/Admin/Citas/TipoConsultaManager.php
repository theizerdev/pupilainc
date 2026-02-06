<?php

namespace App\Livewire\Admin\Citas;

use App\Models\TipoConsulta;
use Livewire\Component;
use Livewire\WithPagination;

class TipoConsultaManager extends Component
{
    use WithPagination;

    // Propiedades para el formulario
    public $tipoConsultaId;
    public $nombre;
    public $color = 'primary';
    public $icono = 'fas fa-stethoscope';
    public $descripcion;
    public $status = true;

    // Propiedades para búsqueda y filtros
    public $search = '';
    public $filtroEstado = '';
    public $perPage = 10;

    // Modal y estados
    public $modalOpen = false;
    public $modalTitle = 'Nuevo Tipo de Consulta';
    public $isEditMode = false;

    protected $rules = [
        'nombre' => 'required|string|max:255',
        'color' => 'required|string|in:primary,secondary,success,danger,warning,info,light,dark',
        'icono' => 'required|string|max:100',
        'descripcion' => 'nullable|string|max:500',
        'status' => 'boolean',
    ];

    protected $messages = [
        'nombre.required' => 'El nombre es obligatorio.',
        'color.required' => 'El color es obligatorio.',
        'icono.required' => 'El icono es obligatorio.',
    ];

    public function render()
    {
        $tiposConsulta = TipoConsulta::forUser()
            ->search($this->search)
            ->when($this->filtroEstado !== '', function ($query) {
                return $query->where('status', $this->filtroEstado);
            })
            ->orderBy('nombre')
            ->paginate($this->perPage);

        return view('livewire.admin.citas.tipo-consulta-manager', [
            'tiposConsulta' => $tiposConsulta,
        ])->layout('layouts.admin');
    }

    public function create()
    {
        $this->resetInputFields();
        $this->isEditMode = false;
        $this->modalTitle = 'Nuevo Tipo de Consulta';
        $this->modalOpen = true;
    }

    public function edit($id)
    {
        $tipoConsulta = TipoConsulta::forUser()->findOrFail($id);
        
        $this->tipoConsultaId = $tipoConsulta->id;
        $this->nombre = $tipoConsulta->nombre;
        $this->color = $tipoConsulta->color;
        $this->icono = $tipoConsulta->icono;
        $this->descripcion = $tipoConsulta->descripcion;
        $this->status = $tipoConsulta->status;
        
        $this->isEditMode = true;
        $this->modalTitle = 'Editar Tipo de Consulta';
        $this->modalOpen = true;
    }

    public function save()
    {
        $this->validate();

        try {
            $data = [
                'nombre' => $this->nombre,
                'color' => $this->color,
                'icono' => $this->icono,
                'descripcion' => $this->descripcion,
                'status' => $this->status,
                'empresa_id' => auth()->user()->empresa_id,
            ];

            if ($this->isEditMode) {
                $tipoConsulta = TipoConsulta::forUser()->findOrFail($this->tipoConsultaId);
                $tipoConsulta->update($data);
                $message = 'Tipo de consulta actualizado correctamente.';
            } else {
                TipoConsulta::create($data);
                $message = 'Tipo de consulta creado correctamente.';
            }

            $this->modalOpen = false;
            $this->resetInputFields();

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => $message,
            ]);
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al guardar: ' . $e->getMessage(),
            ]);
        }
    }

    public function delete($id)
    {
        try {
            $tipoConsulta = TipoConsulta::forUser()->findOrFail($id);
            
            // Verificar si tiene citas asociadas
            if ($tipoConsulta->citas()->exists()) {
                $this->dispatch('notify', [
                    'type' => 'warning',
                    'message' => 'No se puede eliminar este tipo de consulta porque tiene citas asociadas.',
                ]);
                return;
            }

            $tipoConsulta->delete();

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Tipo de consulta eliminado correctamente.',
            ]);
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al eliminar: ' . $e->getMessage(),
            ]);
        }
    }

    public function toggleStatus($id)
    {
        try {
            $tipoConsulta = TipoConsulta::forUser()->findOrFail($id);
            $tipoConsulta->status = !$tipoConsulta->status;
            $tipoConsulta->save();

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Estado actualizado correctamente.',
            ]);
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al actualizar estado: ' . $e->getMessage(),
            ]);
        }
    }

    private function resetInputFields()
    {
        $this->tipoConsultaId = null;
        $this->nombre = '';
        $this->color = 'primary';
        $this->icono = 'fas fa-stethoscope';
        $this->descripcion = '';
        $this->status = true;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFiltroEstado()
    {
        $this->resetPage();
    }
}