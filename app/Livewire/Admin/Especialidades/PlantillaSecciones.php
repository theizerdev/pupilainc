<?php

namespace App\Livewire\Admin\Especialidades;

use App\Models\Especialidad;
use App\Models\PlantillaSeccion;
use App\Models\PlantillaCampo;
use App\Traits\HasPlantillaTemplate;
use Livewire\Component;

class PlantillaSecciones extends Component
{
    use HasPlantillaTemplate;

    public array $secciones = [];

    // Modal sección
    public bool   $modalSeccion    = false;
    public ?int   $seccionEditId   = null;
    public string $seccionNombre   = '';
    public string $seccionIcono    = 'fa-stethoscope';
    public string $seccionColor    = '#3B82F6';

    // Modal campo
    public bool   $modalCampo      = false;
    public ?int   $campoEditId     = null;
    public ?int   $campoSeccionId  = null;
    public string $campoNombre     = '';
    public string $campoEtiqueta   = '';
    public string $campoTipo       = 'text';
    public string $campoOpciones   = '';
    public bool   $campoObligatorio = false;
    public string $campoUnidad     = '';
    public string $campoPlaceholder = '';
    public string $campoDefecto    = '';
    public int    $campoAncho      = 6;
    public ?float $campoMin        = null;
    public ?float $campoMax        = null;

    public function mount(Especialidad $especialidad): void
    {
        parent::mount($especialidad);
        $this->cargarSecciones();
    }

    private function cargarSecciones(): void
    {
        if ($this->plantilla) {
            $this->plantilla->load('todasLasSecciones.todosLosCampos');
            $this->secciones = $this->plantilla->todasLasSecciones
                ->whereNull('estado_formulario_id')
                ->map(fn($s) => [
                    'id'     => $s->id,
                    'nombre' => $s->nombre,
                    'icono'  => $s->icono,
                    'color'  => $s->color,
                    'activo' => $s->activo,
                    'orden'  => $s->orden,
                    'campos' => $s->todosLosCampos->map(fn($c) => [
                        'id' => $c->id, 'nombre_campo' => $c->nombre_campo,
                        'etiqueta' => $c->etiqueta, 'tipo' => $c->tipo,
                        'opciones' => $c->opciones ?? [], 'obligatorio' => $c->obligatorio,
                        'unidad' => $c->unidad, 'placeholder' => $c->placeholder,
                        'valor_defecto' => $c->valor_defecto, 'ancho_columnas' => $c->ancho_columnas,
                        'min' => $c->min, 'max' => $c->max, 'activo' => $c->activo, 'orden' => $c->orden,
                    ])->toArray(),
                ])->toArray();
        } else {
            $this->secciones = [];
        }
    }

    public function abrirModalSeccion(?int $seccionId = null): void
    {
        $this->resetModalSeccion();
        if ($seccionId) {
            $seccion = PlantillaSeccion::findOrFail($seccionId);
            $this->seccionEditId = $seccionId;
            $this->seccionNombre = $seccion->nombre;
            $this->seccionIcono  = $seccion->icono ?? 'fa-stethoscope';
            $this->seccionColor  = $seccion->color ?? '#3B82F6';
        }
        $this->modalSeccion = true;
    }

    public function guardarSeccion(): void
    {
        $this->validate([
            'seccionNombre' => 'required|string|max:100',
            'seccionColor'  => 'required|string|max:7',
        ]);

        if (!$this->plantilla) $this->crearPlantilla();

        if ($this->seccionEditId) {
            PlantillaSeccion::findOrFail($this->seccionEditId)->update([
                'nombre' => $this->seccionNombre,
                'icono'  => $this->seccionIcono,
                'color'  => $this->seccionColor,
            ]);
            $msg = 'Sección actualizada.';
        } else {
            $orden = count($this->secciones) + 1;
            PlantillaSeccion::create([
                'plantilla_id' => $this->plantilla->id,
                'nombre'       => $this->seccionNombre,
                'icono'        => $this->seccionIcono,
                'color'        => $this->seccionColor,
                'orden'        => $orden,
                'activo'       => true,
            ]);
            $msg = 'Sección agregada.';
        }

        $this->plantilla->load('todasLasSecciones.todosLosCampos');
        $this->cargarSecciones();
        $this->resetModalSeccion();
        $this->dispatch('notify', ['type' => 'success', 'message' => $msg]);
    }

    public function toggleSeccion(int $seccionId): void
    {
        $seccion = PlantillaSeccion::findOrFail($seccionId);
        $seccion->update(['activo' => !$seccion->activo]);
        $this->plantilla->load('todasLasSecciones.todosLosCampos');
        $this->cargarSecciones();
    }

    public function eliminarSeccion(int $seccionId): void
    {
        PlantillaSeccion::findOrFail($seccionId)->delete();
        $this->plantilla->load('todasLasSecciones.todosLosCampos');
        $this->cargarSecciones();
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Sección eliminada.']);
    }

    public function abrirModalCampo(int $seccionId, ?int $campoId = null): void
    {
        $this->resetModalCampo();
        $this->campoSeccionId = $seccionId;

        if ($campoId) {
            $campo = PlantillaCampo::findOrFail($campoId);
            $this->campoEditId      = $campoId;
            $this->campoNombre      = $campo->nombre_campo;
            $this->campoEtiqueta    = $campo->etiqueta;
            $this->campoTipo        = $campo->tipo;
            $this->campoOpciones    = $campo->opciones ? implode("\n", $campo->opciones) : '';
            $this->campoObligatorio = $campo->obligatorio;
            $this->campoUnidad      = $campo->unidad ?? '';
            $this->campoPlaceholder = $campo->placeholder ?? '';
            $this->campoDefecto     = $campo->valor_defecto ?? '';
            $this->campoAncho       = $campo->ancho_columnas;
            $this->campoMin         = $campo->min;
            $this->campoMax         = $campo->max;
        }

        $this->modalCampo = true;
    }

    public function guardarCampo(): void
    {
        $this->validate([
            'campoEtiqueta' => 'required|string|max:100',
            'campoTipo'     => 'required|in:' . implode(',', array_keys(PlantillaCampo::TIPOS)),
            'campoAncho'    => 'required|integer|min:1|max:12',
        ]);

        $nombreCampo = $this->campoEditId
            ? $this->campoNombre
            : \Illuminate\Support\Str::snake(\Illuminate\Support\Str::ascii($this->campoEtiqueta));

        $opciones = null;
        if (in_array($this->campoTipo, ['select', 'radio', 'checkbox']) && !empty($this->campoOpciones)) {
            $opciones = array_values(array_filter(array_map('trim', explode("\n", $this->campoOpciones))));
        }

        $data = [
            'nombre_campo'   => $nombreCampo,
            'etiqueta'       => $this->campoEtiqueta,
            'tipo'           => $this->campoTipo,
            'opciones'       => $opciones,
            'obligatorio'    => $this->campoObligatorio,
            'unidad'         => $this->campoUnidad ?: null,
            'placeholder'    => $this->campoPlaceholder ?: null,
            'valor_defecto'  => $this->campoDefecto ?: null,
            'ancho_columnas' => $this->campoAncho,
            'min'            => $this->campoMin,
            'max'            => $this->campoMax,
            'activo'         => true,
        ];

        if ($this->campoEditId) {
            PlantillaCampo::findOrFail($this->campoEditId)->update($data);
            $msg = 'Campo actualizado.';
        } else {
            $seccion = PlantillaSeccion::findOrFail($this->campoSeccionId);
            $data['seccion_id'] = $seccion->id;
            $data['orden']      = $seccion->todosLosCampos()->count() + 1;
            PlantillaCampo::create($data);
            $msg = 'Campo agregado.';
        }

        $this->plantilla->load('todasLasSecciones.todosLosCampos');
        $this->cargarSecciones();
        $this->resetModalCampo();
        $this->dispatch('notify', ['type' => 'success', 'message' => $msg]);
    }

    public function toggleCampo(int $campoId): void
    {
        $campo = PlantillaCampo::findOrFail($campoId);
        $campo->update(['activo' => !$campo->activo]);
        $this->plantilla->load('todasLasSecciones.todosLosCampos');
        $this->cargarSecciones();
    }

    public function eliminarCampo(int $campoId): void
    {
        PlantillaCampo::findOrFail($campoId)->delete();
        $this->plantilla->load('todasLasSecciones.todosLosCampos');
        $this->cargarSecciones();
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Campo eliminado.']);
    }

    private function crearPlantilla(): void
    {
        $this->plantilla = \App\Models\EspecialidadPlantilla::create([
            'especialidad_id'   => $this->especialidad->id,
            'nombre'            => 'Consulta de ' . $this->especialidad->nombre,
            'activo'            => true,
            'empresa_id'        => auth()->user()->empresa_id,
            'sucursal_id'       => auth()->user()->sucursal_id,
        ]);
    }

    private function resetModalSeccion(): void
    {
        $this->modalSeccion  = false;
        $this->seccionEditId = null;
        $this->seccionNombre = '';
        $this->seccionIcono  = 'fa-stethoscope';
        $this->seccionColor  = '#3B82F6';
    }

    private function resetModalCampo(): void
    {
        $this->modalCampo     = false;
        $this->campoEditId    = null;
        $this->campoSeccionId = null;
        $this->campoNombre    = '';
        $this->campoEtiqueta  = '';
        $this->campoTipo      = 'text';
        $this->campoOpciones  = '';
        $this->campoObligatorio = false;
        $this->campoUnidad    = '';
        $this->campoPlaceholder = '';
        $this->campoDefecto   = '';
        $this->campoAncho     = 6;
        $this->campoMin       = null;
        $this->campoMax       = null;
    }

    protected function getPageTitle(): string
    {
        return 'Secciones y Campos - ' . $this->especialidad->nombre;
    }

    protected function getBreadcrumb(): array
    {
        return array_merge($this->getBreadcrumbBase(), [
            '' => 'Secciones y Campos',
        ]);
    }

    public function render()
    {
        return view('livewire.admin.especialidades.plantilla-secciones', [
            'tiposCampo' => PlantillaCampo::TIPOS,
        ])->layout($this->getLayout());
    }
}
