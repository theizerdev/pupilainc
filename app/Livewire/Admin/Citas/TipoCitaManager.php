<?php

namespace App\Livewire\Admin\Citas;

use App\Models\TipoConsulta;
use Livewire\Component;
use Livewire\WithPagination;

class TipoCitaManager extends Component
{
    use WithPagination;

    // Propiedades para el formulario
    public $tipoCitaId;
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
    public $modalTitle = 'Nuevo Tipo de Cita';
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
        $tiposCita = TipoConsulta::forUser()
            ->search($this->search)
            ->when($this->filtroEstado !== '', function ($query) {
                return $query->where('status', $this->filtroEstado);
            })
            ->orderBy('nombre')
            ->paginate($this->perPage);

        return view('livewire.admin.citas.tipo-cita-manager', [
            'tiposCita' => $tiposCita,
        ])->layout('layouts.admin');
    }

    public function create()
    {
        $this->resetInputFields();
        $this->isEditMode = false;
        $this->modalTitle = 'Nuevo Tipo de Cita';
        $this->modalOpen = true;
    }

    public function edit($id)
    {
        $tipoCita = TipoConsulta::forUser()->findOrFail($id);
        
        $this->tipoCitaId = $tipoCita->id;
        $this->nombre = $tipoCita->nombre;
        $this->color = $tipoCita->color;
        $this->icono = $tipoCita->icono;
        $this->descripcion = $tipoCita->descripcion;
        $this->status = $tipoCita->status;
        
        $this->isEditMode = true;
        $this->modalTitle = 'Editar Tipo de Cita';
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
                $tipoCita = TipoConsulta::forUser()->findOrFail($this->tipoCitaId);
                $tipoCita->update($data);
                $message = 'Tipo de cita actualizado correctamente.';
            } else {
                TipoConsulta::create($data);
                $message = 'Tipo de cita creado correctamente.';
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
            $tipoCita = TipoConsulta::forUser()->findOrFail($id);
            
            if ($tipoCita->citas()->exists()) {
                $this->dispatch('notify', [
                    'type' => 'warning',
                    'message' => 'No se puede eliminar este tipo de cita porque tiene citas asociadas.',
                ]);
                return;
            }

            $tipoCita->delete();

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Tipo de cita eliminado correctamente.',
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
            $tipoCita = TipoConsulta::forUser()->findOrFail($id);
            $tipoCita->status = !$tipoCita->status;
            $tipoCita->save();

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
        $this->tipoCitaId = null;
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