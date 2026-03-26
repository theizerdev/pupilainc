<?php

namespace App\Livewire\Admin\Medicos;

use App\Models\Subespecialidad;
use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class SubespecialidadModal extends Component
{
    use AuthorizesRequests;

    public $nombre;
    public $codigo;
    public $descripcion;
    public $costo_consulta = 0;
    public $duracion_consulta = 30;
    public $requiere_cita_previa = true;
    public $color = '#3B82F6';
    public $icono = 'fa-stethoscope';
    public $especialidad_id;
    public $showModal = false;

    protected function rules()
    {
        return [
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:1000',
            'codigo' => 'nullable|string|max:10|unique:subespecialidades',
            'color' => 'required|string|max:7',
            'icono' => 'required|string|max:100',
            'costo_consulta' => 'required|numeric|min:0',
            'duracion_consulta' => 'required|integer|min:15|max:240',
            'requiere_cita_previa' => 'boolean',
            'especialidad_id' => 'required|exists:especialidades,id',
        ];
    }

    public function mount($especialidadId = null)
    {
        $this->especialidad_id = $especialidadId;
    }

    #[On('open-subespecialidad-modal')]
    public function openModal($especialidadId = null)
    {
        if ($especialidadId) {
            $this->especialidad_id = $especialidadId;
        }
        $this->showModal = true;
        $this->reset([
            'nombre', 'codigo', 'descripcion', 'costo_consulta',
            'duracion_consulta', 'requiere_cita_previa', 'color', 'icono'
        ]);
        $this->costo_consulta = 0;
        $this->duracion_consulta = 30;
        $this->requiere_cita_previa = true;
        $this->color = '#3B82F6';
        $this->icono = 'fa-stethoscope';
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetValidation();
    }

    public function store()
    {
        $this->authorize('create subespecialidades');

        $validated = $this->validate();

        if (empty($validated['codigo'])) {
            $validated['codigo'] = Subespecialidad::generateCodigo();
        }

        try {
            $subespecialidad = Subespecialidad::create($validated);

            // Emitir evento global para que tanto Create como Edit lo escuchen
            $this->dispatch('subespecialidad-creada', 
                id: $subespecialidad->id,
                nombre: $subespecialidad->nombre,
                especialidad_id: $subespecialidad->especialidad_id
            );

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => "Subespecialidad '{$this->nombre}' creada exitosamente.",
                'duration' => 4000
            ]);

            $this->closeModal();
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al crear la subespecialidad: ' . $e->getMessage(),
                'duration' => 5000
            ]);
        }
    }

    public function render()
    {
        return view('livewire.admin.medicos.subespecialidad-modal');
    }
}
