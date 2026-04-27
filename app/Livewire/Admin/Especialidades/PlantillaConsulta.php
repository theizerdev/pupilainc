<?php

namespace App\Livewire\Admin\Especialidades;

use App\Models\Especialidad;
use App\Models\EspecialidadPlantilla;
use App\Models\PlantillaEstadoFormulario;
use App\Models\PlantillaSeccion;
use App\Models\PlantillaCampo;
use App\Traits\HasDynamicLayout;
use Livewire\Component;

class PlantillaConsulta extends Component
{
    use HasDynamicLayout;

    public Especialidad $especialidad;
    public ?EspecialidadPlantilla $plantilla = null;

    // Configuración general de la plantilla
    public array $pasosHabilitados = [];
    public array $estadosFlujo     = [];

    // Secciones cargadas para la UI
    public array $secciones = [];

    // Modal sección
    public bool   $modalSeccion    = false;
    public ?int   $seccionEditId   = null;
    public string $seccionNombre   = '';
    public string $seccionIcono    = 'fa-stethoscope';
    public string $seccionColor    = '#3B82F6';

    // Formularios por estado
    public string $estadoFormularioActivo = '';  // estado seleccionado para editar su formulario
    public array  $estadoFormularios      = [];  // [estado => ['id', 'titulo', 'activo', 'secciones']]

    // Modal campo
    public bool   $modalCampo      = false;
    public ?int   $campoEditId     = null;
    public ?int   $campoSeccionId  = null;
    // Indica si el campo pertenece a un formulario de estado (no a la evaluación principal)
    public ?int   $campoEstadoFormularioId = null;
    public string $campoNombre     = '';
    public string $campoEtiqueta   = '';
    public string $campoTipo       = 'text';
    public string $campoOpciones   = '';   // JSON string para edición
    public bool   $campoObligatorio = false;
    public string $campoUnidad     = '';
    public string $campoPlaceholder = '';
    public string $campoDefecto    = '';
    public int    $campoAncho      = 6;
    public ?float $campoMin        = null;
    public ?float $campoMax        = null;

    public function mount(Especialidad $especialidad): void
    {
        $this->especialidad = $especialidad;
        $this->cargarPlantilla();
    }

    // ── Carga ─────────────────────────────────────────────────────────────────

    private function cargarPlantilla(): void
    {
        $this->plantilla = EspecialidadPlantilla::with(['todasLasSecciones.todosLosCampos'])
            ->where('especialidad_id', $this->especialidad->id)
            ->latest()
            ->first();

        if ($this->plantilla) {
            $this->pasosHabilitados = $this->plantilla->pasos_habilitados
                ?? array_keys(EspecialidadPlantilla::PASOS_DISPONIBLES);
            $this->estadosFlujo = $this->plantilla->estados_flujo
                ?? $this->plantilla->getEstadosEfectivos();
            $this->sincronizarSecciones();
            $this->sincronizarEstadoFormularios();
        } else {
            $this->pasosHabilitados = array_keys(EspecialidadPlantilla::PASOS_DISPONIBLES);
            $this->estadosFlujo     = ['sala_espera', 'en_enfermeria', 'en_consultorio', 'finalizada'];
            $this->secciones        = [];
            $this->estadoFormularios = [];
        }
    }

    private function sincronizarEstadoFormularios(): void
    {
        $this->plantilla->load('todosLosEstadoFormularios.todasLasSecciones.todosLosCampos');

        $this->estadoFormularios = [];
        foreach ($this->plantilla->todosLosEstadoFormularios as $ef) {
            $this->estadoFormularios[$ef->estado] = [
                'id'       => $ef->id,
                'titulo'   => $ef->titulo,
                'activo'   => $ef->activo,
                'secciones'=> $ef->todasLasSecciones->map(fn($s) => [
                    'id'     => $s->id,
                    'nombre' => $s->nombre,
                    'icono'  => $s->icono,
                    'color'  => $s->color,
                    'activo' => $s->activo,
                    'orden'  => $s->orden,
                    'campos' => $s->todosLosCampos->map(fn($c) => [
                        'id'             => $c->id,
                        'nombre_campo'   => $c->nombre_campo,
                        'etiqueta'       => $c->etiqueta,
                        'tipo'           => $c->tipo,
                        'opciones'       => $c->opciones ?? [],
                        'obligatorio'    => $c->obligatorio,
                        'unidad'         => $c->unidad,
                        'placeholder'    => $c->placeholder,
                        'valor_defecto'  => $c->valor_defecto,
                        'ancho_columnas' => $c->ancho_columnas,
                        'min'            => $c->min,
                        'max'            => $c->max,
                        'activo'         => $c->activo,
                        'orden'          => $c->orden,
                    ])->toArray(),
                ])->toArray(),
            ];
        }
    }

    // ── Formularios por estado ────────────────────────────────────────────────

    public function seleccionarEstadoFormulario(string $estado): void
    {
        $this->estadoFormularioActivo = $this->estadoFormularioActivo === $estado ? '' : $estado;
    }

    public function crearOAbrirFormularioEstado(string $estado): void
    {
        if (!$this->plantilla) $this->crearPlantilla();

        PlantillaEstadoFormulario::firstOrCreate(
            ['plantilla_id' => $this->plantilla->id, 'estado' => $estado],
            ['titulo' => EspecialidadPlantilla::ESTADOS_DISPONIBLES[$estado] ?? ucfirst($estado), 'activo' => true]
        );

        $this->plantilla->load('todosLosEstadoFormularios.todasLasSecciones.todosLosCampos');
        $this->sincronizarEstadoFormularios();
        $this->estadoFormularioActivo = $estado;
    }

    public function toggleEstadoFormulario(string $estado): void
    {
        $ef = PlantillaEstadoFormulario::where('plantilla_id', $this->plantilla->id)
            ->where('estado', $estado)->first();
        if ($ef) {
            $ef->update(['activo' => !$ef->activo]);
            $this->sincronizarEstadoFormularios();
        }
    }

    public function abrirModalSeccionEstado(string $estado, ?int $seccionId = null): void
    {
        // Asegura que el formulario de estado existe
        $this->crearOAbrirFormularioEstado($estado);
        $ef = PlantillaEstadoFormulario::where('plantilla_id', $this->plantilla->id)
            ->where('estado', $estado)->firstOrFail();

        $this->resetModalSeccion();
        $this->campoEstadoFormularioId = $ef->id; // reutilizamos para saber el contexto

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
        $this->validate([
            'seccionNombre' => 'required|string|max:100',
            'seccionColor'  => 'required|string|max:7',
        ]);

        $efId = $this->campoEstadoFormularioId;

        if ($this->seccionEditId) {
            PlantillaSeccion::findOrFail($this->seccionEditId)->update([
                'nombre' => $this->seccionNombre,
                'icono'  => $this->seccionIcono,
                'color'  => $this->seccionColor,
            ]);
        } else {
            $orden = PlantillaSeccion::where('estado_formulario_id', $efId)->count() + 1;
            PlantillaSeccion::create([
                'plantilla_id'          => $this->plantilla->id,
                'estado_formulario_id'  => $efId,
                'nombre'                => $this->seccionNombre,
                'icono'                 => $this->seccionIcono,
                'color'                 => $this->seccionColor,
                'orden'                 => $orden,
                'activo'                => true,
            ]);
        }

        $this->plantilla->load('todosLosEstadoFormularios.todasLasSecciones.todosLosCampos');
        $this->sincronizarEstadoFormularios();
        $this->resetModalSeccion();
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Sección guardada.']);
    }

    public function abrirModalCampoEstado(int $seccionId, ?int $campoId = null): void
    {
        $seccion = PlantillaSeccion::findOrFail($seccionId);
        $this->resetModalCampo();
        $this->campoSeccionId          = $seccionId;
        $this->campoEstadoFormularioId = $seccion->estado_formulario_id;

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

    private function sincronizarSecciones(): void
    {
        $this->secciones = $this->plantilla->todasLasSecciones
            ->map(fn($s) => [
                'id'     => $s->id,
                'nombre' => $s->nombre,
                'icono'  => $s->icono,
                'color'  => $s->color,
                'activo' => $s->activo,
                'orden'  => $s->orden,
                'campos' => $s->todosLosCampos->map(fn($c) => [
                    'id'             => $c->id,
                    'nombre_campo'   => $c->nombre_campo,
                    'etiqueta'       => $c->etiqueta,
                    'tipo'           => $c->tipo,
                    'opciones'       => $c->opciones ?? [],
                    'obligatorio'    => $c->obligatorio,
                    'unidad'         => $c->unidad,
                    'placeholder'    => $c->placeholder,
                    'valor_defecto'  => $c->valor_defecto,
                    'ancho_columnas' => $c->ancho_columnas,
                    'min'            => $c->min,
                    'max'            => $c->max,
                    'activo'         => $c->activo,
                    'orden'          => $c->orden,
                ])->toArray(),
            ])->toArray();
    }

    // ── Plantilla base ────────────────────────────────────────────────────────

    public function crearPlantilla(): void
    {
        $this->plantilla = EspecialidadPlantilla::create([
            'especialidad_id'   => $this->especialidad->id,
            'nombre'            => 'Consulta de ' . $this->especialidad->nombre,
            'activo'            => true,
            'pasos_habilitados' => $this->pasosHabilitados,
            'estados_flujo'     => $this->estadosFlujo,
            'empresa_id'        => auth()->user()->empresa_id,
            'sucursal_id'       => auth()->user()->sucursal_id,
        ]);

        $this->sincronizarSecciones();
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Plantilla creada.']);
    }

    public function guardarConfiguracion(): void
    {
        if (!$this->plantilla) {
            $this->crearPlantilla();
        }

        $this->plantilla->update([
            'pasos_habilitados' => $this->pasosHabilitados,
            'estados_flujo'     => $this->estadosFlujo,
        ]);

        $this->dispatch('notify', ['type' => 'success', 'message' => 'Configuración guardada.']);
    }

    // ── Pasos y estados ───────────────────────────────────────────────────────

    public function togglePaso(string $paso): void
    {
        if (in_array($paso, $this->pasosHabilitados)) {
            $this->pasosHabilitados = array_values(
                array_filter($this->pasosHabilitados, fn($p) => $p !== $paso)
            );
        } else {
            $this->pasosHabilitados[] = $paso;
        }
    }

    public function toggleEstado(string $estado): void
    {
        if (in_array($estado, $this->estadosFlujo)) {
            $this->estadosFlujo = array_values(
                array_filter($this->estadosFlujo, fn($e) => $e !== $estado)
            );
        } else {
            $this->estadosFlujo[] = $estado;
        }
    }

    // ── Secciones ─────────────────────────────────────────────────────────────

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
        $this->sincronizarSecciones();
        $this->resetModalSeccion();
        $this->dispatch('notify', ['type' => 'success', 'message' => $msg]);
    }

    public function toggleSeccion(int $seccionId): void
    {
        $seccion = PlantillaSeccion::findOrFail($seccionId);
        $seccion->update(['activo' => !$seccion->activo]);
        $this->plantilla->load('todasLasSecciones.todosLosCampos');
        $this->sincronizarSecciones();
    }

    public function eliminarSeccion(int $seccionId): void
    {
        PlantillaSeccion::findOrFail($seccionId)->delete();
        $this->plantilla->load('todasLasSecciones.todosLosCampos');
        $this->sincronizarSecciones();
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Sección eliminada.']);
    }

    public function moverSeccion(int $seccionId, string $direccion): void
    {
        $secciones = PlantillaSeccion::where('plantilla_id', $this->plantilla->id)
            ->orderBy('orden')->get();

        $idx = $secciones->search(fn($s) => $s->id === $seccionId);
        if ($idx === false) return;

        $swap = $direccion === 'up' ? $idx - 1 : $idx + 1;
        if ($swap < 0 || $swap >= $secciones->count()) return;

        $ordenA = $secciones[$idx]->orden;
        $ordenB = $secciones[$swap]->orden;
        $secciones[$idx]->update(['orden' => $ordenB]);
        $secciones[$swap]->update(['orden' => $ordenA]);

        $this->plantilla->load('todasLasSecciones.todosLosCampos');
        $this->sincronizarSecciones();
    }

    // ── Campos ────────────────────────────────────────────────────────────────

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
            $opciones = array_values(array_filter(
                array_map('trim', explode("\n", $this->campoOpciones))
            ));
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

        // Recargar según contexto (evaluación principal o formulario de estado)
        if ($this->campoEstadoFormularioId) {
            $this->plantilla->load('todosLosEstadoFormularios.todasLasSecciones.todosLosCampos');
            $this->sincronizarEstadoFormularios();
        } else {
            $this->plantilla->load('todasLasSecciones.todosLosCampos');
            $this->sincronizarSecciones();
        }

        $this->resetModalCampo();
        $this->dispatch('notify', ['type' => 'success', 'message' => $msg]);
    }

    public function toggleCampo(int $campoId): void
    {
        $campo = PlantillaCampo::findOrFail($campoId);
        $campo->update(['activo' => !$campo->activo]);
        $this->plantilla->load('todasLasSecciones.todosLosCampos');
        $this->sincronizarSecciones();
    }

    public function eliminarCampo(int $campoId): void
    {
        PlantillaCampo::findOrFail($campoId)->delete();
        $this->plantilla->load('todasLasSecciones.todosLosCampos');
        $this->sincronizarSecciones();
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Campo eliminado.']);
    }

    public function moverCampo(int $campoId, string $direccion): void
    {
        $campo   = PlantillaCampo::findOrFail($campoId);
        $campos  = PlantillaCampo::where('seccion_id', $campo->seccion_id)->orderBy('orden')->get();
        $idx     = $campos->search(fn($c) => $c->id === $campoId);
        if ($idx === false) return;

        $swap = $direccion === 'up' ? $idx - 1 : $idx + 1;
        if ($swap < 0 || $swap >= $campos->count()) return;

        $ordenA = $campos[$idx]->orden;
        $ordenB = $campos[$swap]->orden;
        $campos[$idx]->update(['orden' => $ordenB]);
        $campos[$swap]->update(['orden' => $ordenA]);

        $this->plantilla->load('todasLasSecciones.todosLosCampos');
        $this->sincronizarSecciones();
    }

    // ── Reset modales ─────────────────────────────────────────────────────────

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
        $this->modalCampo              = false;
        $this->campoEditId             = null;
        $this->campoSeccionId          = null;
        $this->campoEstadoFormularioId = null;
        $this->campoNombre             = '';
        $this->campoEtiqueta           = '';
        $this->campoTipo               = 'text';
        $this->campoOpciones           = '';
        $this->campoObligatorio        = false;
        $this->campoUnidad             = '';
        $this->campoPlaceholder        = '';
        $this->campoDefecto            = '';
        $this->campoAncho              = 6;
        $this->campoMin                = null;
        $this->campoMax                = null;
    }

    protected function getPageTitle(): string { return 'Plantilla: ' . $this->especialidad->nombre; }

    protected function getBreadcrumb(): array
    {
        return [
            'admin.dashboard'                                          => 'Dashboard',
            'admin.especialidades.index'                               => 'Especialidades',
            'admin.especialidades.show:' . $this->especialidad->id     => $this->especialidad->nombre,
            ''                                                         => 'Plantilla de Consulta',
        ];
    }

    public function render()
    {
        return view('livewire.admin.especialidades.plantilla-consulta', [
            'pasosDisponibles'  => EspecialidadPlantilla::PASOS_DISPONIBLES,
            'estadosDisponibles'=> EspecialidadPlantilla::ESTADOS_DISPONIBLES,
            'tiposCampo'        => PlantillaCampo::TIPOS,
            'iconosDisponibles' => $this->iconosDisponibles(),
        ])->layout($this->getLayout());
    }

    private function iconosDisponibles(): array
    {
        return [
            'fa-stethoscope', 'fa-heartbeat', 'fa-brain', 'fa-eye', 'fa-ear',
            'fa-tooth', 'fa-bone', 'fa-baby', 'fa-lungs', 'fa-kidneys',
            'fa-procedures', 'fa-flask', 'fa-microscope', 'fa-x-ray',
            'fa-syringe', 'fa-pills', 'fa-clipboard-list', 'fa-file-medical',
            'fa-notes-medical', 'fa-user-md', 'fa-heart', 'fa-thermometer',
        ];
    }
}
