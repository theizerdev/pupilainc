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
    public bool $usarWizardEnConsultorio = true;

    // Modal paso
    public bool   $modalPaso      = false;
    public ?int   $pasoEditIndex  = null;
    public string $pasoKey        = '';
    public string $pasoNombre     = '';
    public string $pasoIcono      = 'ri-stethoscope-line';
    public bool   $pasoActivo     = true;
    public string $pasoTipo       = 'predefinido'; // predefinido | formulario

    // Modal estado
    public bool   $modalEstado      = false;
    public ?int   $estadoEditIndex  = null;
    public string $estadoKey        = '';
    public string $estadoNombre     = '';
    public string $estadoColor      = '#6B7280';
    public bool   $estadoActivo     = true;

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
            $this->pasosHabilitados = $this->plantilla->getPasosEfectivos();
            $this->estadosFlujo = $this->plantilla->getEstadosEfectivos();
            $this->usarWizardEnConsultorio = $this->plantilla->usar_wizard_en_consultorio ?? true;
            $this->sincronizarSecciones();
            $this->sincronizarEstadoFormularios();
        } else {
            $this->pasosHabilitados = $this->getPasosDefecto();
            $this->estadosFlujo     = $this->getEstadosDefecto();
            $this->usarWizardEnConsultorio = true;
            $this->secciones        = [];
            $this->estadoFormularios = [];
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

    private function getEstadosDefecto(): array
    {
        // Solo estados ESPECIALES por defecto (los base son automáticos)
        return [
            ['key' => 'en_estudio', 'nombre' => 'En Estudio', 'color' => '#EC407A', 'activo' => false, 'orden' => 1, 'tipo' => 'especial'],
        ];
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
        if (!$this->plantilla) $this->crearPlantilla();

        // Asegura que el formulario de estado existe
        $ef = PlantillaEstadoFormulario::firstOrCreate(
            ['plantilla_id' => $this->plantilla->id, 'estado' => $estado],
            ['titulo' => EspecialidadPlantilla::ESTADOS_DISPONIBLES[$estado] ?? ucfirst($estado), 'activo' => true]
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
        $this->validate([
            'seccionNombre' => 'required|string|max:100',
            'seccionColor'  => 'required|string|max:7',
        ]);

        $efId = $this->campoEstadoFormularioId;
        $ef = PlantillaEstadoFormulario::findOrFail($efId);

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

        // Recargar plantilla completa desde la base de datos
        $this->plantilla = EspecialidadPlantilla::with(['todosLosEstadoFormularios.todasLasSecciones.todosLosCampos'])
            ->findOrFail($this->plantilla->id);
        $this->sincronizarEstadoFormularios();

        // Mantener el estado abierto después de guardar
        $this->estadoFormularioActivo = $ef->estado;

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
            'pasos_config'      => $this->pasosHabilitados,
            'estados_config'    => $this->estadosFlujo,
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
            'pasos_config'               => $this->pasosHabilitados,
            'estados_config'             => $this->estadosFlujo,
            'usar_wizard_en_consultorio' => $this->usarWizardEnConsultorio,
        ]);

        $this->dispatch('notify', ['type' => 'success', 'message' => 'Configuración guardada.']);
    }

    // ── Pasos ─────────────────────────────────────────────────────────────────

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

        // Guardar automáticamente en la base de datos
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

    // ── Estados ───────────────────────────────────────────────────────────────

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
            // Editar estado existente
            $this->estadosFlujo[$this->estadoEditIndex] = [
                'key'    => $this->estadoKey,
                'nombre' => $this->estadoNombre,
                'color'  => $this->estadoColor,
                'activo' => $this->estadoActivo,
                'orden'  => $this->estadosFlujo[$this->estadoEditIndex]['orden'],
            ];
            $msg = 'Estado actualizado.';
        } else {
            // Crear nuevo estado (siempre es especial)
            $key = \Illuminate\Support\Str::snake(\Illuminate\Support\Str::ascii($this->estadoNombre));
            $orden = count($this->estadosFlujo) + 1;
            $this->estadosFlujo[] = [
                'key'    => $key,
                'nombre' => $this->estadoNombre,
                'color'  => $this->estadoColor,
                'activo' => true,
                'orden'  => $orden,
                'tipo'   => 'especial', // Marcar como estado especial
            ];
            $msg = 'Estado especial agregado.';
        }

        // Guardar automáticamente en la base de datos
        $this->guardarConfiguracion();

        $this->resetModalEstado();
        $this->dispatch('notify', ['type' => 'success', 'message' => $msg]);
    }

    // ── Estados del Flujo ────────────────────────────────────────────────────

    public function toggleEstado(int $index): void
    {
        if (isset($this->estadosFlujo[$index])) {
            // No permitir desactivar estados base
            if (($this->estadosFlujo[$index]['tipo'] ?? 'especial') === 'base') {
                $this->dispatch('notify', ['type' => 'error', 'message' => 'Los estados base no se pueden desactivar.']);
                return;
            }

            $this->estadosFlujo[$index]['activo'] = !$this->estadosFlujo[$index]['activo'];
            $this->guardarConfiguracion();
        }
    }

    public function eliminarEstado(int $index): void
    {
        if (isset($this->estadosFlujo[$index])) {
            // No permitir eliminar estados base
            if (($this->estadosFlujo[$index]['tipo'] ?? 'especial') === 'base') {
                $this->dispatch('notify', ['type' => 'error', 'message' => 'Los estados base no se pueden eliminar.']);
                return;
            }

            array_splice($this->estadosFlujo, $index, 1);
            // Reordenar
            foreach ($this->estadosFlujo as $i => &$estado) {
                $estado['orden'] = $i + 1;
            }
            $this->guardarConfiguracion();
            $this->dispatch('notify', ['type' => 'success', 'message' => 'Estado especial eliminado.']);
        }
    }

    public function moverEstado(int $index, string $direccion): void
    {
        // No permitir mover estados base
        if (isset($this->estadosFlujo[$index]) && ($this->estadosFlujo[$index]['tipo'] ?? 'especial') === 'base') {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Los estados base no se pueden reordenar.']);
            return;
        }

        $swap = $direccion === 'up' ? $index - 1 : $index + 1;
        if ($swap < 0 || $swap >= count($this->estadosFlujo)) return;

        // No permitir mover si el estado destino es base
        if (isset($this->estadosFlujo[$swap]) && ($this->estadosFlujo[$swap]['tipo'] ?? 'especial') === 'base') {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'No se puede reordenar con estados base.']);
            return;
        }

        $temp = $this->estadosFlujo[$index];
        $this->estadosFlujo[$index] = $this->estadosFlujo[$swap];
        $this->estadosFlujo[$swap] = $temp;

        // Actualizar orden
        $this->estadosFlujo[$index]['orden'] = $index + 1;
        $this->estadosFlujo[$swap]['orden'] = $swap + 1;

        $this->guardarConfiguracion();
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

        // Recargar según contexto
        if ($seccion->estado_formulario_id) {
            $this->plantilla->load('todosLosEstadoFormularios.todasLasSecciones.todosLosCampos');
            $this->sincronizarEstadoFormularios();
        } else {
            $this->plantilla->load('todasLasSecciones.todosLosCampos');
            $this->sincronizarSecciones();
        }
    }

    public function eliminarSeccion(int $seccionId): void
    {
        $seccion = PlantillaSeccion::findOrFail($seccionId);
        $esEstadoFormulario = $seccion->estado_formulario_id !== null;
        $seccion->delete();

        // Recargar según contexto
        if ($esEstadoFormulario) {
            $this->plantilla->load('todosLosEstadoFormularios.todasLasSecciones.todosLosCampos');
            $this->sincronizarEstadoFormularios();
        } else {
            $this->plantilla->load('todasLasSecciones.todosLosCampos');
            $this->sincronizarSecciones();
        }

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
            'campoSeccionId' => 'required|exists:plantilla_secciones,id',
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
            $campo = PlantillaCampo::findOrFail($this->campoEditId);
            $seccionOriginal = $campo->seccion_id;

            // Verificar si cambió de sección
            if ($seccionOriginal != $this->campoSeccionId) {
                // Mover a nueva sección
                $data['seccion_id'] = $this->campoSeccionId;

                // Calcular nuevo orden (al final de la nueva sección)
                $nuevoOrden = PlantillaCampo::where('seccion_id', $this->campoSeccionId)
                    ->where('id', '!=', $this->campoEditId)  // Excluir el campo actual
                    ->count() + 1;
                $data['orden'] = $nuevoOrden;

                // Recalcular órdenes en la sección original
                $this->recalcularOrdenCampos($seccionOriginal);

                $msg = 'Campo movido a otra sección y actualizado.';
            } else {
                // Solo actualizar datos, no cambia de sección
                $msg = 'Campo actualizado.';
            }

            $campo->update($data);
        } else {
            $seccion = PlantillaSeccion::findOrFail($this->campoSeccionId);
            $data['seccion_id'] = $seccion->id;
            $data['orden']      = $seccion->todosLosCampos()->count() + 1;
            PlantillaCampo::create($data);
            $msg = 'Campo agregado.';
        }

        // Recargar según contexto (evaluación principal o formulario de estado)
        if ($this->campoEstadoFormularioId) {
            $this->plantilla->load('todosLosEstadoFormularios.secciones.campos');
            $this->sincronizarEstadoFormularios();
        } else {
            $this->plantilla->load('todasLasSecciones.todosLosCampos');
            $this->sincronizarSecciones();
        }

        $this->resetModalCampo();
        $this->dispatch('notify', ['type' => 'success', 'message' => $msg]);
    }

    /**
     * Recalcular el orden de los campos en una sección después de mover uno
     */
    private function recalcularOrdenCampos(int $seccionId): void
    {
        $campos = PlantillaCampo::where('seccion_id', $seccionId)
            ->orderBy('orden')
            ->get();

        foreach ($campos as $index => $campo) {
            $campo->update(['orden' => $index + 1]);
        }
    }

    public function toggleCampo(int $campoId): void
    {
        $campo = PlantillaCampo::findOrFail($campoId);
        $campo->update(['activo' => !$campo->activo]);

        // Recargar según contexto
        $seccion = $campo->seccion;
        if ($seccion && $seccion->estado_formulario_id) {
            $this->plantilla->load('todosLosEstadoFormularios.todasLasSecciones.todosLosCampos');
            $this->sincronizarEstadoFormularios();
        } else {
            $this->plantilla->load('todasLasSecciones.todosLosCampos');
            $this->sincronizarSecciones();
        }
    }

    public function eliminarCampo(int $campoId): void
    {
        $campo = PlantillaCampo::findOrFail($campoId);
        $seccion = $campo->seccion;
        $esEstadoFormulario = $seccion && $seccion->estado_formulario_id !== null;
        $campo->delete();

        // Recargar según contexto
        if ($esEstadoFormulario) {
            $this->plantilla->load('todosLosEstadoFormularios.todasLasSecciones.todosLosCampos');
            $this->sincronizarEstadoFormularios();
        } else {
            $this->plantilla->load('todasLasSecciones.todosLosCampos');
            $this->sincronizarSecciones();
        }

        $this->dispatch('notify', ['type' => 'success', 'message' => 'Campo eliminado.']);
    }

    /**
     * Mover un campo dentro de su sección (para formularios por estado)
     */
    public function moverCampoEstado(int $campoId, string $direccion, int $seccionId): void
    {
        $campo   = PlantillaCampo::findOrFail($campoId);

        // Verificar que el campo pertenezca a la sección indicada
        if ($campo->seccion_id !== $seccionId) {
            return;
        }

        // Obtener todos los campos de esta sección ordenados
        $campos  = PlantillaCampo::where('seccion_id', $seccionId)
            ->orderBy('orden')
            ->get();

        // Encontrar el índice del campo actual
        $idx = $campos->search(fn($c) => $c->id === $campoId);
        if ($idx === false) {
            return;
        }

        // Calcular el índice con el que intercambiar
        $swap = $direccion === 'up' ? $idx - 1 : $idx + 1;

        // Verificar límites
        if ($swap < 0 || $swap >= $campos->count()) {
            return;
        }

        // Intercambiar órdenes
        $ordenA = $campos[$idx]->orden;
        $ordenB = $campos[$swap]->orden;

        $campos[$idx]->update(['orden' => $ordenB]);
        $campos[$swap]->update(['orden' => $ordenA]);

        // Recargar datos
        $this->plantilla->load('todosLosEstadoFormularios.secciones.campos');
        $this->sincronizarEstadoFormularios();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Campo reordenado correctamente.'
        ]);
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

    private function resetModalEstado(): void
    {
        $this->modalEstado     = false;
        $this->estadoEditIndex = null;
        $this->estadoKey       = '';
        $this->estadoNombre    = '';
        $this->estadoColor     = '#6B7280';
        $this->estadoActivo    = true;
    }

    private function resetModalSeccion(): void
    {
        $this->modalSeccion            = false;
        $this->seccionEditId           = null;
        $this->campoEstadoFormularioId = null;
        $this->seccionNombre           = '';
        $this->seccionIcono            = 'fa-stethoscope';
        $this->seccionColor            = '#3B82F6';
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
            'tiposCampo'        => PlantillaCampo::TIPOS,
            'iconosDisponibles' => $this->iconosDisponibles(),
            'iconosPasos'       => $this->iconosPasos(),
            'anchoOpciones'     => $this->anchoOpciones(),
        ])->layout($this->getLayout());
    }

    private function anchoOpciones(): array
    {
        return [
            12 => ['Completo', '12/12'],
            6  => ['Mitad', '6/12'],
            4  => ['Tercio', '4/12'],
            3  => ['Cuarto', '3/12'],
            8  => ['2/3', '8/12'],
            9  => ['3/4', '9/12'],
        ];
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
