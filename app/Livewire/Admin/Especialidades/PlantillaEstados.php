<?php

namespace App\Livewire\Admin\Especialidades;

use App\Models\Especialidad;
use App\Traits\HasPlantillaTemplate;
use Livewire\Component;

class PlantillaEstados extends Component
{
    use HasPlantillaTemplate;

    public array $estadosFlujo = [];

    // Modal estado
    public bool   $modalEstado      = false;
    public ?int   $estadoEditIndex  = null;
    public string $estadoKey        = '';
    public string $estadoNombre     = '';
    public string $estadoColor      = '#6B7280';
    public bool   $estadoActivo     = true;

    public function mount(Especialidad $especialidad): void
    {
        parent::mount($especialidad);
        $this->cargarEstados();
    }

    private function cargarEstados(): void
    {
        if ($this->plantilla) {
            $this->estadosFlujo = $this->plantilla->getEstadosEfectivos();
        } else {
            $this->estadosFlujo = $this->getEstadosDefecto();
        }
    }

    private function getEstadosDefecto(): array
    {
        return [
            ['key' => 'sala_espera', 'nombre' => 'Sala de Espera', 'color' => '#6B7280', 'activo' => true, 'orden' => 1],
            ['key' => 'en_enfermeria', 'nombre' => 'En Enfermería', 'color' => '#3B82F6', 'activo' => true, 'orden' => 2],
            ['key' => 'en_consultorio', 'nombre' => 'En Consultorio', 'color' => '#10B981', 'activo' => true, 'orden' => 3],
            ['key' => 'finalizada', 'nombre' => 'Finalizada', 'color' => '#8B5CF6', 'activo' => true, 'orden' => 4],
        ];
    }

    public function abrirModalEstado(?int $index = null): void
    {
        $this->resetModalEstado();

        if ($index !== null && isset($this->estadosFlujo[$index])) {
            $estado = $this->estadosFlujo[$index];
            $this->estadoEditIndex = $index;
            $this->estadoKey       = $estado['key'];
            $this->estadoNombre    = $estado['nombre'];
            $this->estadoColor     = $estado['color'] ?? '#6B7280';
            $this->estadoActivo    = $estado['activo'] ?? true;
        }

        $this->modalEstado = true;
    }

    public function guardarEstado(): void
    {
        $this->validate([
            'estadoNombre' => 'required|string|max:100',
            'estadoColor'  => 'required|string|max:7',
        ]);

        if ($this->estadoEditIndex !== null) {
            $this->estadosFlujo[$this->estadoEditIndex] = [
                'key'    => $this->estadoKey,
                'nombre' => $this->estadoNombre,
                'color'  => $this->estadoColor,
                'activo' => $this->estadoActivo,
                'orden'  => $this->estadosFlujo[$this->estadoEditIndex]['orden'],
            ];
            $msg = 'Estado actualizado.';
        } else {
            $key = \Illuminate\Support\Str::snake(\Illuminate\Support\Str::ascii($this->estadoNombre));
            $orden = count($this->estadosFlujo) + 1;
            $this->estadosFlujo[] = [
                'key'    => $key,
                'nombre' => $this->estadoNombre,
                'color'  => $this->estadoColor,
                'activo' => true,
                'orden'  => $orden,
            ];
            $msg = 'Estado agregado.';
        }

        $this->guardarConfiguracion();

        $this->resetModalEstado();
        $this->dispatch('notify', ['type' => 'success', 'message' => $msg]);
    }

    public function toggleEstado(int $index): void
    {
        if (isset($this->estadosFlujo[$index])) {
            $this->estadosFlujo[$index]['activo'] = !$this->estadosFlujo[$index]['activo'];
            $this->guardarConfiguracion();
        }
    }

    public function eliminarEstado(int $index): void
    {
        if (isset($this->estadosFlujo[$index])) {
            array_splice($this->estadosFlujo, $index, 1);
            foreach ($this->estadosFlujo as $i => &$estado) {
                $estado['orden'] = $i + 1;
            }
            $this->guardarConfiguracion();
            $this->dispatch('notify', ['type' => 'success', 'message' => 'Estado eliminado.']);
        }
    }

    public function moverEstado(int $index, string $direccion): void
    {
        $swap = $direccion === 'up' ? $index - 1 : $index + 1;
        if ($swap < 0 || $swap >= count($this->estadosFlujo)) return;

        $temp = $this->estadosFlujo[$index];
        $this->estadosFlujo[$index] = $this->estadosFlujo[$swap];
        $this->estadosFlujo[$swap] = $temp;

        $this->estadosFlujo[$index]['orden'] = $index + 1;
        $this->estadosFlujo[$swap]['orden'] = $swap + 1;

        $this->guardarConfiguracion();
    }

    private function guardarConfiguracion(): void
    {
        if (!$this->plantilla) {
            $this->crearPlantilla();
        }

        $this->plantilla->update([
            'estados_config' => $this->estadosFlujo,
        ]);

        $this->verificarEstadoConfiguracion();
    }

    private function crearPlantilla(): void
    {
        $this->plantilla = \App\Models\EspecialidadPlantilla::create([
            'especialidad_id'   => $this->especialidad->id,
            'nombre'            => 'Consulta de ' . $this->especialidad->nombre,
            'activo'            => true,
            'estados_config'    => $this->estadosFlujo,
            'empresa_id'        => auth()->user()->empresa_id,
            'sucursal_id'       => auth()->user()->sucursal_id,
        ]);
    }

    private function resetModalEstado(): void
    {
        $this->modalEstado     = false;
        $this->estadoEditIndex = null;
        $this->estadoKey       = '';
        $this->estadoNombre    = '';
        $this->estadoColor     = '#6B7280';
        $this->estadoActivo    = true;
    }

    protected function getPageTitle(): string
    {
        return 'Estados del Flujo - ' . $this->especialidad->nombre;
    }

    protected function getBreadcrumb(): array
    {
        return array_merge($this->getBreadcrumbBase(), [
            '' => 'Estados del Flujo',
        ]);
    }

    public function render()
    {
        return view('livewire.admin.especialidades.plantilla-estados')->layout($this->getLayout());
    }
}
