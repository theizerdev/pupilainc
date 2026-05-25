<?php

namespace App\Livewire\Admin\Especialidades;

use App\Models\Especialidad;
use App\Traits\HasPlantillaTemplate;
use Livewire\Component;

class PlantillaPasos extends Component
{
    use HasPlantillaTemplate;

    public array $pasosHabilitados = [];

    // Modal paso
    public bool   $modalPaso      = false;
    public ?int   $pasoEditIndex  = null;
    public string $pasoKey        = '';
    public string $pasoNombre     = '';
    public string $pasoIcono      = 'ri-stethoscope-line';
    public bool   $pasoActivo     = true;
    public string $pasoTipo       = 'predefinido';

    public function mount(Especialidad $especialidad): void
    {
        parent::mount($especialidad);
        $this->cargarPasos();
    }

    private function cargarPasos(): void
    {
        if ($this->plantilla) {
            $this->pasosHabilitados = $this->plantilla->getPasosEfectivos();
        } else {
            $this->pasosHabilitados = $this->getPasosDefecto();
        }
    }

    private function getPasosDefecto(): array
    {
        return [
            ['key' => 'signos_vitales', 'nombre' => 'Signos Vitales', 'icono' => 'ri-heart-pulse-line', 'activo' => true, 'tipo' => 'predefinido', 'orden' => 1],
            ['key' => 'cuestionario', 'nombre' => 'Cuestionario', 'icono' => 'ri-questionnaire-line', 'activo' => true, 'tipo' => 'predefinido', 'orden' => 2],
            ['key' => 'evaluacion', 'nombre' => 'Evaluación', 'icono' => 'ri-file-list-3-line', 'activo' => true, 'tipo' => 'predefinido', 'orden' => 3],
            ['key' => 'estudios', 'nombre' => 'Estudios', 'icono' => 'ri-microscope-line', 'activo' => true, 'tipo' => 'predefinido', 'orden' => 4],
            ['key' => 'tratamiento', 'nombre' => 'Tratamiento', 'icono' => 'ri-medicine-bottle-line', 'activo' => true, 'tipo' => 'predefinido', 'orden' => 5],
            ['key' => 'reposo', 'nombre' => 'Reposo', 'icono' => 'ri-hotel-bed-line', 'activo' => true, 'tipo' => 'predefinido', 'orden' => 6],
        ];
    }

    public function abrirModalPaso(?int $index = null): void
    {
        $this->resetModalPaso();

        if ($index !== null && isset($this->pasosHabilitados[$index])) {
            $paso = $this->pasosHabilitados[$index];
            $this->pasoEditIndex = $index;
            $this->pasoKey       = $paso['key'];
            $this->pasoNombre    = $paso['nombre'];
            $this->pasoIcono     = $paso['icono'] ?? 'ri-stethoscope-line';
            $this->pasoActivo    = $paso['activo'] ?? true;
            $this->pasoTipo      = $paso['tipo'] ?? 'predefinido';
        }

        $this->modalPaso = true;
    }

    public function guardarPaso(): void
    {
        $this->validate([
            'pasoNombre' => 'required|string|max:100',
            'pasoIcono'  => 'required|string|max:50',
            'pasoTipo'   => 'required|in:predefinido,formulario',
        ]);

        if ($this->pasoEditIndex !== null) {
            // Editar paso existente
            $this->pasosHabilitados[$this->pasoEditIndex] = [
                'key'    => $this->pasoKey,
                'nombre' => $this->pasoNombre,
                'icono'  => $this->pasoIcono,
                'activo' => $this->pasoActivo,
                'tipo'   => $this->pasoTipo,
                'orden'  => $this->pasosHabilitados[$this->pasoEditIndex]['orden'],
            ];
            $msg = 'Paso actualizado.';
        } else {
            // Crear nuevo paso
            $key = \Illuminate\Support\Str::snake(\Illuminate\Support\Str::ascii($this->pasoNombre));
            $orden = count($this->pasosHabilitados) + 1;
            $this->pasosHabilitados[] = [
                'key'    => $key,
                'nombre' => $this->pasoNombre,
                'icono'  => $this->pasoIcono,
                'activo' => true,
                'tipo'   => $this->pasoTipo,
                'orden'  => $orden,
            ];
            $msg = 'Paso agregado.';
        }

        $this->guardarConfiguracion();

        $this->resetModalPaso();
        $this->dispatch('notify', ['type' => 'success', 'message' => $msg]);
    }

    public function togglePaso(int $index): void
    {
        if (isset($this->pasosHabilitados[$index])) {
            $this->pasosHabilitados[$index]['activo'] = !$this->pasosHabilitados[$index]['activo'];
            $this->guardarConfiguracion();
        }
    }

    public function eliminarPaso(int $index): void
    {
        if (isset($this->pasosHabilitados[$index])) {
            array_splice($this->pasosHabilitados, $index, 1);
            // Reordenar
            foreach ($this->pasosHabilitados as $i => &$paso) {
                $paso['orden'] = $i + 1;
            }
            $this->guardarConfiguracion();
            $this->dispatch('notify', ['type' => 'success', 'message' => 'Paso eliminado.']);
        }
    }

    public function moverPaso(int $index, string $direccion): void
    {
        $swap = $direccion === 'up' ? $index - 1 : $index + 1;
        if ($swap < 0 || $swap >= count($this->pasosHabilitados)) return;

        $temp = $this->pasosHabilitados[$index];
        $this->pasosHabilitados[$index] = $this->pasosHabilitados[$swap];
        $this->pasosHabilitados[$swap] = $temp;

        // Actualizar orden
        $this->pasosHabilitados[$index]['orden'] = $index + 1;
        $this->pasosHabilitados[$swap]['orden'] = $swap + 1;

        $this->guardarConfiguracion();
    }

    private function guardarConfiguracion(): void
    {
        if (!$this->plantilla) {
            $this->crearPlantilla();
        }

        $this->plantilla->update([
            'pasos_config' => $this->pasosHabilitados,
        ]);

        // Recargar estado
        $this->verificarEstadoConfiguracion();
    }

    private function crearPlantilla(): void
    {
        $this->plantilla = \App\Models\EspecialidadPlantilla::create([
            'especialidad_id'   => $this->especialidad->id,
            'nombre'            => 'Consulta de ' . $this->especialidad->nombre,
            'activo'            => true,
            'pasos_config'      => $this->pasosHabilitados,
            'empresa_id'        => auth()->user()->empresa_id,
            'sucursal_id'       => auth()->user()->sucursal_id,
        ]);
    }

    private function resetModalPaso(): void
    {
        $this->modalPaso     = false;
        $this->pasoEditIndex = null;
        $this->pasoKey       = '';
        $this->pasoNombre    = '';
        $this->pasoIcono     = 'ri-stethoscope-line';
        $this->pasoActivo    = true;
        $this->pasoTipo      = 'predefinido';
    }

    protected function getPageTitle(): string
    {
        return 'Pasos de Consulta - ' . $this->especialidad->nombre;
    }

    protected function getBreadcrumb(): array
    {
        return array_merge($this->getBreadcrumbBase(), [
            '' => 'Pasos de Consulta',
        ]);
    }

    public function render()
    {
        return view('livewire.admin.especialidades.plantilla-pasos', [
            'iconosDisponibles' => $this->iconosPasos(),
        ])->layout($this->getLayout());
    }

    private function iconosPasos(): array
    {
        return [
            'ri-stethoscope-line', 'ri-heart-pulse-line', 'ri-questionnaire-line',
            'ri-file-list-3-line', 'ri-microscope-line', 'ri-medicine-bottle-line',
            'ri-hotel-bed-line', 'ri-eye-line', 'ri-ear-line', 'ri-tooth-line',
            'ri-lungs-line', 'ri-brain-line', 'ri-heart-line', 'ri-pulse-line',
            'ri-syringe-line', 'ri-capsule-line', 'ri-test-tube-line',
            'ri-flask-line', 'ri-thermometer-line', 'ri-mental-health-line',
        ];
    }
}
