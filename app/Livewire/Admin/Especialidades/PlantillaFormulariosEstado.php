<?php

namespace App\Livewire\Admin\Especialidades;

use App\Models\Especialidad;
use App\Models\PlantillaEstadoFormulario;
use App\Models\PlantillaSeccion;
use App\Models\PlantillaCampo;
use App\Traits\HasPlantillaTemplate;
use Livewire\Component;

class PlantillaFormulariosEstado extends Component
{
    use HasPlantillaTemplate;

    public string $estadoFormularioActivo = '';
    public array  $estadoFormularios      = [];

    // Modal sección (reutilizado)
    public bool   $modalSeccion    = false;
    public ?int   $seccionEditId   = null;
    public ?int   $campoEstadoFormularioId = null;
    public string $seccionNombre   = '';
    public string $seccionIcono    = 'fa-stethoscope';
    public string $seccionColor    = '#3B82F6';

    // Modal campo (reutilizado)
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
        $this->cargarFormularios();
    }

    private function cargarFormularios(): void
    {
        if ($this->plantilla) {
            $this->plantilla->load('todosLosEstadoFormularios.todasLasSecciones.todosLosCampos');
            $this->estadoFormularios = [];
            foreach ($this->plantilla->todosLosEstadoFormularios as $ef) {
                $this->estadoFormularios[$ef->estado] = [
                    'id'       => $ef->id,
                    'titulo'   => $ef->titulo,
                    'activo'   => $ef->activo,
                    'secciones'=> $ef->todasLasSecciones->map(fn($s) => [
                        'id' => $s->id, 'nombre' => $s->nombre, 'icono' => $s->icono,
                        'color' => $s->color, 'activo' => $s->activo, 'orden' => $s->orden,
                        'campos' => $s->todosLosCampos->map(fn($c) => [
                            'id' => $c->id, 'etiqueta' => $c->etiqueta, 'tipo' => $c->tipo,
                            'obligatorio' => $c->obligatorio, 'unidad' => $c->unidad,
                            'ancho_columnas' => $c->ancho_columnas, 'activo' => $c->activo,
                        ])->toArray(),
                    ])->toArray(),
                ];
            }
        }
    }

    public function seleccionarEstadoFormulario(string $estado): void
    {
        $this->estadoFormularioActivo = $this->estadoFormularioActivo === $estado ? '' : $estado;
    }

    public function crearOAbrirFormularioEstado(string $estado): void
    {
        if (!$this->plantilla) $this->crearPlantilla();

        PlantillaEstadoFormulario::firstOrCreate(
            ['plantilla_id' => $this->plantilla->id, 'estado' => $estado],
            ['titulo' => \App\Models\EspecialidadPlantilla::ESTADOS_DISPONIBLES[$estado] ?? ucfirst($estado), 'activo' => true]
        );

        $this->plantilla->load('todosLosEstadoFormularios.todasLasSecciones.todosLosCampos');
        $this->cargarFormularios();
        $this->estadoFormularioActivo = $estado;
    }

    public function abrirModalSeccionEstado(string $estado, ?int $seccionId = null): void
    {
        if (!$this->plantilla) $this->crearPlantilla();

        $ef = PlantillaEstadoFormulario::firstOrCreate(
            ['plantilla_id' => $this->plantilla->id, 'estado' => $estado],
            ['titulo' => \App\Models\EspecialidadPlantilla::ESTADOS_DISPONIBLES[$estado] ?? ucfirst($estado), 'activo' => true]
        );

        $this->resetModalSeccion();
        $this->campoEstadoFormularioId = $ef->id;

        if ($seccionId) {
            $seccion = PlantillaSeccion::findOrFail($seccionId);
            $this->seccionEditId = $seccionId;
            $this->seccionNombre = $seccion->nombre;
            $this->seccionIcono  = $seccion->icono ?? 'fa-stethoscope';
            $this->seccionColor  = $seccion->color ?? '#3B82F6';
        }

        $this->modalSeccion = true;
    }

    public function guardarSeccionEstado(): void
    {
        $this->validate(['seccionNombre' => 'required|string|max:100', 'seccionColor' => 'required|string|max:7']);

        $efId = $this->campoEstadoFormularioId;
        $ef = PlantillaEstadoFormulario::findOrFail($efId);

        if ($this->seccionEditId) {
            PlantillaSeccion::findOrFail($this->seccionEditId)->update([
                'nombre' => $this->seccionNombre, 'icono' => $this->seccionIcono, 'color' => $this->seccionColor,
            ]);
        } else {
            $orden = PlantillaSeccion::where('estado_formulario_id', $efId)->count() + 1;
            PlantillaSeccion::create([
                'plantilla_id' => $this->plantilla->id, 'estado_formulario_id' => $efId,
                'nombre' => $this->seccionNombre, 'icono' => $this->seccionIcono,
                'color' => $this->seccionColor, 'orden' => $orden, 'activo' => true,
            ]);
        }

        $this->plantilla = \App\Models\EspecialidadPlantilla::with(['todosLosEstadoFormularios.todasLasSecciones.todosLosCampos'])->findOrFail($this->plantilla->id);
        $this->cargarFormularios();
        $this->estadoFormularioActivo = $ef->estado;
        $this->resetModalSeccion();
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Sección guardada.']);
    }

    public function abrirModalCampoEstado(int $seccionId, ?int $campoId = null): void
    {
        $seccion = PlantillaSeccion::findOrFail($seccionId);
        $this->resetModalCampo();
        $this->campoSeccionId = $seccionId;
        $this->campoEstadoFormularioId = $seccion->estado_formulario_id;

        if ($campoId) {
            $campo = PlantillaCampo::findOrFail($campoId);
            $this->campoEditId = $campoId;
            $this->campoNombre = $campo->nombre_campo;
            $this->campoEtiqueta = $campo->etiqueta;
            $this->campoTipo = $campo->tipo;
            $this->campoOpciones = $campo->opciones ? implode("\n", $campo->opciones) : '';
            $this->campoObligatorio = $campo->obligatorio;
            $this->campoUnidad = $campo->unidad ?? '';
            $this->campoPlaceholder = $campo->placeholder ?? '';
            $this->campoDefecto = $campo->valor_defecto ?? '';
            $this->campoAncho = $campo->ancho_columnas;
            $this->campoMin = $campo->min;
            $this->campoMax = $campo->max;
        }

        $this->modalCampo = true;
    }

    public function guardarCampoEstado(): void
    {
        $this->validate([
            'campoEtiqueta' => 'required|string|max:100',
            'campoTipo' => 'required|in:' . implode(',', array_keys(PlantillaCampo::TIPOS)),
            'campoAncho' => 'required|integer|min:1|max:12',
        ]);

        $nombreCampo = $this->campoEditId ? $this->campoNombre : \Illuminate\Support\Str::snake(\Illuminate\Support\Str::ascii($this->campoEtiqueta));
        $opciones = null;
        if (in_array($this->campoTipo, ['select', 'radio', 'checkbox']) && !empty($this->campoOpciones)) {
            $opciones = array_values(array_filter(array_map('trim', explode("\n", $this->campoOpciones))));
        }

        $data = [
            'nombre_campo' => $nombreCampo, 'etiqueta' => $this->campoEtiqueta, 'tipo' => $this->campoTipo,
            'opciones' => $opciones, 'obligatorio' => $this->campoObligatorio,
            'unidad' => $this->campoUnidad ?: null, 'placeholder' => $this->campoPlaceholder ?: null,
            'valor_defecto' => $this->campoDefecto ?: null, 'ancho_columnas' => $this->campoAncho,
            'min' => $this->campoMin, 'max' => $this->campoMax, 'activo' => true,
        ];

        if ($this->campoEditId) {
            PlantillaCampo::findOrFail($this->campoEditId)->update($data);
        } else {
            $seccion = PlantillaSeccion::findOrFail($this->campoSeccionId);
            $data['seccion_id'] = $seccion->id;
            $data['orden'] = $seccion->todosLosCampos()->count() + 1;
            PlantillaCampo::create($data);
        }

        $this->plantilla->load('todosLosEstadoFormularios.todasLasSecciones.todosLosCampos');
        $this->cargarFormularios();
        $this->resetModalCampo();
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Campo guardado.']);

        // Disparar evento global para actualizar otros componentes
        $this->dispatch('plantilla-actualizada');
    }

    private function crearPlantilla(): void
    {
        $this->plantilla = \App\Models\EspecialidadPlantilla::create([
            'especialidad_id' => $this->especialidad->id,
            'nombre' => 'Consulta de ' . $this->especialidad->nombre,
            'activo' => true,
            'empresa_id' => auth()->user()->empresa_id,
            'sucursal_id' => auth()->user()->sucursal_id,
        ]);
    }

    private function resetModalSeccion(): void
    {
        $this->modalSeccion = false;
        $this->seccionEditId = null;
        $this->campoEstadoFormularioId = null;
        $this->seccionNombre = '';
        $this->seccionIcono = 'fa-stethoscope';
        $this->seccionColor = '#3B82F6';
    }

    private function resetModalCampo(): void
    {
        $this->modalCampo = false;
        $this->campoEditId = null;
        $this->campoSeccionId = null;
        $this->campoEstadoFormularioId = null;
        $this->campoNombre = '';
        $this->campoEtiqueta = '';
        $this->campoTipo = 'text';
        $this->campoOpciones = '';
        $this->campoObligatorio = false;
        $this->campoUnidad = '';
        $this->campoPlaceholder = '';
        $this->campoDefecto = '';
        $this->campoAncho = 6;
        $this->campoMin = null;
        $this->campoMax = null;
    }

    protected function getPageTitle(): string
    {
        return 'Formularios por Estado - ' . $this->especialidad->nombre;
    }

    protected function getBreadcrumb(): array
    {
        return array_merge($this->getBreadcrumbBase(), [
            '' => 'Formularios por Estado',
        ]);
    }

    public function render()
    {
        return view('livewire.admin.especialidades.plantilla-formularios-estado', [
            'tiposCampo' => PlantillaCampo::TIPOS,
            'estadosDisponibles' => \App\Models\EspecialidadPlantilla::ESTADOS_DISPONIBLES,
        ])->layout($this->getLayout());
    }
}
