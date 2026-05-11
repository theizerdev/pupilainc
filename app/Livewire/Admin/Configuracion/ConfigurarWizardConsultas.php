<?php

namespace App\Livewire\Admin\Configuracion;

use App\Models\Especialidad;
use App\Models\EspecialidadPlantilla;
use Livewire\Component;
use App\Traits\HasDynamicLayout;

class ConfigurarWizardConsultas extends Component
{
    use HasDynamicLayout;

    public $especialidades = [];
    public $search = '';

    protected $rules = [
        'especialidades.*.usar_wizard_en_consultorio' => 'boolean',
    ];

    public function mount()
    {
        $this->cargarEspecialidades();
    }

    public function cargarEspecialidades()
    {
        $query = Especialidad::with(['plantillaActiva'])
            ->where('empresa_id', auth()->user()->empresa_id ?? 1)
            ->orderBy('nombre');

        if ($this->search) {
            $query->where('nombre', 'like', '%' . $this->search . '%');
        }

        $especialidades = $query->get();

        $this->especialidades = $especialidades->map(function ($especialidad) {
            $plantilla = $especialidad->plantillaActiva;

            return [
                'id' => $especialidad->id,
                'nombre' => $especialidad->nombre,
                'codigo' => $especialidad->codigo,
                'color' => $especialidad->color ?? '#3B82F6',
                'icono' => $especialidad->icono ?? 'fa-stethoscope',
                'tiene_plantilla' => !is_null($plantilla),
                'plantilla_id' => $plantilla?->id,
                'usar_wizard_en_consultorio' => $plantilla?->usar_wizard_en_consultorio ?? true,
            ];
        })->toArray();
    }

    public function updatedSearch()
    {
        $this->cargarEspecialidades();
    }

    public function toggleWizard($especialidadId)
    {
        $index = array_search($especialidadId, array_column($this->especialidades, 'id'));

        if ($index !== false) {
            $this->especialidades[$index]['usar_wizard_en_consultorio'] =
                !$this->especialidades[$index]['usar_wizard_en_consultorio'];

            $this->guardarConfiguracion($especialidadId);
        }
    }

    public function guardarConfiguracion($especialidadId = null)
    {
        try {
            if ($especialidadId) {
                // Guardar una especialidad específica
                $index = array_search($especialidadId, array_column($this->especialidades, 'id'));

                if ($index !== false) {
                    $especialidadData = $this->especialidades[$index];

                    if ($especialidadData['tiene_plantilla']) {
                        EspecialidadPlantilla::where('id', $especialidadData['plantilla_id'])
                            ->update([
                                'usar_wizard_en_consultorio' => $especialidadData['usar_wizard_en_consultorio']
                            ]);
                    }
                }
            } else {
                // Guardar todas las especialidades
                foreach ($this->especialidades as $especialidadData) {
                    if ($especialidadData['tiene_plantilla']) {
                        EspecialidadPlantilla::where('id', $especialidadData['plantilla_id'])
                            ->update([
                                'usar_wizard_en_consultorio' => $especialidadData['usar_wizard_en_consultorio']
                            ]);
                    }
                }
            }

            $this->dispatch('show-toast', [
                'type' => 'success',
                'message' => 'Configuración guardada exitosamente.'
            ]);

        } catch (\Exception $e) {
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Error al guardar: ' . $e->getMessage()
            ]);
        }
    }

    public function aplicarRecomendacion($tipo)
    {
        foreach ($this->especialidades as &$especialidad) {
            if ($tipo === 'wizard') {
                $especialidad['usar_wizard_en_consultorio'] = true;
            } elseif ($tipo === 'formulario') {
                $especialidad['usar_wizard_en_consultorio'] = false;
            }
        }
        unset($especialidad);

        $this->guardarConfiguracion();
    }

    public function render()
    {
        return view('livewire.admin.configuracion.configurar-wizard-consultas', [
            'totalEspecialidades' => count($this->especialidades),
            'conWizard' => count(array_filter($this->especialidades, fn($e) => $e['usar_wizard_en_consultorio'])),
            'conFormulario' => count(array_filter($this->especialidades, fn($e) => !$e['usar_wizard_en_consultorio'])),
        ])->layout($this->getLayout());
    }
}
