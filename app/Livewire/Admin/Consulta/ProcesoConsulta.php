<?php

namespace App\Livewire\Admin\Consulta;

use Livewire\Component;
use App\Models\Consulta;
use App\Models\ConsultaEstudio;
use App\Models\ConsultaTratamiento;
use App\Models\ConsultaEstadoDato;
use App\Models\RespuestaPreconsulta;
use App\Models\EspecialidadPlantilla;
use App\Traits\HasDynamicLayout;

class ProcesoConsulta extends Component
{
    use HasDynamicLayout;

    public $consulta;
    public $pasoActual = 0;
    public $nuevoEstado = '';

    // Plantilla dinámica
    public $plantilla        = null;
    public $pasosHabilitados = [];
    public $estadosFlujo     = [];
    public $secciones        = [];   // secciones de la evaluación clínica

    // Formularios por estado: [estado => ['secciones' => [...]]]
    public $formulariosPorEstado = [];
    // Datos capturados en el estado actual
    public $datos_estado_actual  = [];

    // Paso: Signos Vitales
    public $presion_sistolica;
    public $presion_diastolica;
    public $frecuencia_cardiaca;
    public $frecuencia_respiratoria;
    public $temperatura;
    public $peso;
    public $talla;
    public $saturacion_oxigeno;
    public $observaciones_signos;
    public $imc_calculado;
    public $historial_signos = [];

    // Paso: Cuestionario
    public $preguntas_cuestionario = [];
    public $ultima_respuesta_pregunta = null;

    // Paso: Evaluación dinámica
    public $datos_dinamicos = [];   // ['nombre_campo' => valor]

    // Diagnósticos
    public $busqueda_diagnostico    = '';
    public $diagnosticos_disponibles = [];
    public $diagnosticos_agregados  = [];
    public $nuevo_diagnostico_codigo = '';
    public $nuevo_diagnostico_nombre = '';
    public $mostrar_form_nuevo      = false;

    // Paso: Estudios
    public $tipo_estudio       = 'imagen';
    public $nombre_estudio;
    public $indicaciones_estudio;
    public $estudios           = [];

    // Paso: Tratamientos
    public $medicamento;
    public $indicaciones_tratamiento;
    public $tratamientos       = [];

    // Paso: Reposo
    public $requiere_reposo    = false;
    public $motivo_reposo;
    public $dias_reposo;
    public $fecha_inicio_reposo;
    public $fecha_fin_reposo;
    public $observaciones_reposo;

    public function mount($consultaId)
    {
        $this->consulta = Consulta::with([
            'evaluacion', 'estudios', 'tratamientos',
            'paciente', 'medico', 'especialidad',
            'signosVitales', 'reposo', 'estadoDatos',
        ])->findOrFail($consultaId);

        $this->cargarPlantilla();
        $this->cargarSignosVitales();
        $this->cargarCuestionario();
        $this->cargarEvaluacion();
        $this->cargarDiagnosticos();
        $this->cargarEstudiosYTratamientos();
        $this->cargarReposo();
        $this->cargarDatosEstadoActual();
    }

    // ── Carga de plantilla ────────────────────────────────────────────────────

    private function cargarPlantilla(): void
    {
        $plantilla = null;

        if ($this->consulta->especialidad_id) {
            $plantilla = EspecialidadPlantilla::with(['secciones.campos', 'estadoFormularios.secciones.campos'])
                ->where('especialidad_id', $this->consulta->especialidad_id)
                ->where('activo', true)
                ->latest()
                ->first();
        }

        if ($plantilla) {
            $this->plantilla        = $plantilla->toArray();
            $this->pasosHabilitados = $plantilla->getPasosEfectivos();
            $this->estadosFlujo     = $plantilla->getEstadosEfectivos();
            $this->secciones        = [];

            // Cargar formularios por estado
            $this->formulariosPorEstado = [];
            foreach ($plantilla->estadoFormularios as $ef) {
                $this->formulariosPorEstado[$ef->estado] = [
                    'secciones' => $ef->secciones->map(fn($s) => [
                        'id'     => $s->id,
                        'nombre' => $s->nombre,
                        'icono'  => $s->icono,
                        'color'  => $s->color,
                        'campos' => $s->campos->map(fn($c) => [
                            'id'             => $c->id,
                            'nombre_campo'   => $c->nombre_campo,
                            'etiqueta'       => $c->etiqueta,
                            'tipo'           => $c->tipo,
                            'opciones'       => $c->opciones ?? [],
                            'obligatorio'    => $c->obligatorio,
                            'valor_defecto'  => $c->valor_defecto,
                            'placeholder'    => $c->placeholder,
                            'unidad'         => $c->unidad,
                            'min'            => $c->min,
                            'max'            => $c->max,
                            'ancho_columnas' => $c->ancho_columnas,
                        ])->toArray(),
                    ])->toArray(),
                ];
            }
        } else {
            // Sin plantilla: pasos y estados genéricos
            $this->pasosHabilitados      = array_keys(EspecialidadPlantilla::PASOS_DISPONIBLES);
            $this->estadosFlujo          = ['sala_espera', 'en_enfermeria', 'en_consultorio', 'finalizada'];
            $this->secciones             = [];
            $this->formulariosPorEstado  = [];
        }

        // Inicializar datos_dinamicos con valores por defecto
        foreach ($this->secciones as $seccion) {
            foreach ($seccion['campos'] as $campo) {
                if (!isset($this->datos_dinamicos[$campo['nombre_campo']])) {
                    $this->datos_dinamicos[$campo['nombre_campo']] = $campo['valor_defecto'] ?? (
                        $campo['tipo'] === 'checkbox' ? [] : null
                    );
                }
            }
        }
    }

    // ── Carga inicial de datos ────────────────────────────────────────────────

    private function cargarSignosVitales(): void
    {
        $this->historial_signos = \App\Models\SignosVitales::where('paciente_id', $this->consulta->paciente_id)
            ->orderBy('created_at', 'desc')->limit(10)
            ->get(['temperatura', 'presion_arterial_sistolica', 'presion_arterial_diastolica', 'imc', 'created_at'])
            ->reverse()->values()->toArray();

        $sv = $this->consulta->signosVitales()->latest()->first();
        if ($sv) {
            $this->presion_sistolica      = $sv->presion_arterial_sistolica;
            $this->presion_diastolica     = $sv->presion_arterial_diastolica;
            $this->frecuencia_cardiaca    = $sv->frecuencia_cardiaca;
            $this->frecuencia_respiratoria = $sv->frecuencia_respiratoria;
            $this->temperatura            = $sv->temperatura;
            $this->peso                   = $sv->peso;
            $this->talla                  = $sv->talla;
            $this->saturacion_oxigeno     = $sv->saturacion_oxigeno;
            $this->observaciones_signos   = $sv->observaciones;
            $this->imc_calculado          = $sv->imc;
        }
        $this->calcularIMC();
    }

    private function cargarCuestionario(): void
    {
        $cuestionario = \App\Models\Cuestionario::where('activo', true)
            ->where('empresa_id', auth()->user()->empresa_id)
            ->where(function ($q) {
                $q->where('especialidad_id', $this->consulta->especialidad_id)
                  ->orWhereNull('especialidad_id');
            })
            ->orderByRaw('especialidad_id IS NULL ASC') // prioriza el específico
            ->first();

        if ($cuestionario) {
            $this->preguntas_cuestionario = \App\Models\Pregunta::where('cuestionario_id', $cuestionario->id)
                ->where('activo', true)->orderBy('orden')->get()->toArray();
        }
    }

    private function cargarEvaluacion(): void
    {
        if ($this->consulta->evaluacion?->datos_dinamicos) {
            // Merge: mantiene valores por defecto para campos nuevos
            $this->datos_dinamicos = array_merge(
                $this->datos_dinamicos,
                $this->consulta->evaluacion->datos_dinamicos
            );
        }
    }

    private function cargarDiagnosticos(): void
    {
        $this->diagnosticos_agregados = $this->consulta->diagnosticos()
            ->get()
            ->map(fn($d) => [
                'id'     => $d->id,
                'codigo' => $d->codigo,
                'nombre' => $d->nombre,
                'tipo'   => $d->pivot->tipo,
            ])->toArray();
    }

    private function cargarEstudiosYTratamientos(): void
    {
        $this->estudios     = $this->consulta->estudios->toArray();
        $this->tratamientos = $this->consulta->tratamientos->toArray();
    }

    private function cargarReposo(): void
    {
        if ($this->consulta->reposo) {
            $this->requiere_reposo      = true;
            $this->motivo_reposo        = $this->consulta->reposo->motivo;
            $this->dias_reposo          = $this->consulta->reposo->dias_reposo;
            $this->fecha_inicio_reposo  = $this->consulta->reposo->fecha_inicio->format('Y-m-d');
            $this->fecha_fin_reposo     = $this->consulta->reposo->fecha_fin->format('Y-m-d');
            $this->observaciones_reposo = $this->consulta->reposo->observaciones;
        } else {
            $this->fecha_inicio_reposo = now()->format('Y-m-d');
        }
    }

    // ── Navegación ────────────────────────────────────────────────────────────

    public function irPaso($paso): void
    {
        $pasoKey = $this->pasosHabilitados[$this->pasoActual] ?? null;

        if ($pasoKey === 'signos_vitales' && $paso > $this->pasoActual) {
            $this->guardarSignosVitales();
        }

        $this->pasoActual = $paso;
    }

    // ── Signos Vitales ────────────────────────────────────────────────────────

    public function updatedPeso()    { $this->calcularIMC(); $this->skipRender(); }
    public function updatedTalla()   { $this->calcularIMC(); $this->skipRender(); }
    public function updatedPresionSistolica()  { $this->skipRender(); }
    public function updatedPresionDiastolica() { $this->skipRender(); }
    public function updatedTemperatura()       { $this->skipRender(); }

    private function calcularIMC(): void
    {
        $this->imc_calculado = ($this->peso && $this->talla)
            ? round($this->peso / (($this->talla / 100) ** 2), 1)
            : null;
    }

    public function guardarSignosVitales(): void
    {
        $this->consulta->signosVitales()->updateOrCreate(
            ['consulta_id' => $this->consulta->id],
            [
                'paciente_id'               => $this->consulta->paciente_id,
                'presion_arterial_sistolica' => $this->presion_sistolica,
                'presion_arterial_diastolica'=> $this->presion_diastolica,
                'frecuencia_cardiaca'        => $this->frecuencia_cardiaca,
                'frecuencia_respiratoria'    => $this->frecuencia_respiratoria,
                'temperatura'               => $this->temperatura,
                'peso'                      => $this->peso,
                'talla'                     => $this->talla,
                'imc'                       => $this->imc_calculado,
                'saturacion_oxigeno'        => $this->saturacion_oxigeno,
                'observaciones'             => $this->observaciones_signos,
                'empresa_id'                => auth()->user()->empresa_id,
                'sucursal_id'               => auth()->user()->sucursal_id,
                'created_by'                => auth()->id(),
            ]
        );

        $this->historial_signos = \App\Models\SignosVitales::where('paciente_id', $this->consulta->paciente_id)
            ->orderBy('created_at', 'desc')->limit(10)
            ->get(['temperatura', 'presion_arterial_sistolica', 'presion_arterial_diastolica', 'imc', 'created_at'])
            ->reverse()->values()->toArray();

        $this->dispatch('notify', ['message' => 'Signos vitales guardados', 'type' => 'success']);
        $this->dispatch('chartsUpdated', ['historial' => $this->historial_signos]);
    }

    // ── Cuestionario ──────────────────────────────────────────────────────────

    public function guardarRespuestaCuestionario($preguntaId, $respuesta, $detalle = null): void
    {
        if ($respuesta === null && $this->ultima_respuesta_pregunta === $preguntaId) {
            $existing = RespuestaPreconsulta::where('consulta_id', $this->consulta->id)
                ->where('pregunta_id', $preguntaId)->first();
            if ($existing) {
                $existing->update(['detalle' => $detalle]);
                $this->dispatch('notify', ['message' => 'Detalle guardado', 'type' => 'success']);
                return;
            }
        }

        RespuestaPreconsulta::updateOrCreate(
            ['consulta_id' => $this->consulta->id, 'pregunta_id' => $preguntaId],
            [
                'paciente_id'       => $this->consulta->paciente_id,
                'cita_id'           => $this->consulta->cita_id,
                'respuesta'         => is_array($respuesta) ? null : $respuesta,
                'respuesta_multiple'=> is_array($respuesta) ? $respuesta : null,
                'detalle'           => $detalle,
                'completado'        => true,
                'fecha_completado'  => now(),
                'token_unico'       => uniqid('cuest_', true),
                'empresa_id'        => auth()->user()->empresa_id,
                'sucursal_id'       => auth()->user()->sucursal_id,
            ]
        );

        $this->ultima_respuesta_pregunta = $preguntaId;
        $this->dispatch('notify', ['message' => 'Respuesta guardada', 'type' => 'success']);
    }

    public function toggleRespuestaMultiple($preguntaId, $opcion): void
    {
        $respuesta = RespuestaPreconsulta::where('consulta_id', $this->consulta->id)
            ->where('pregunta_id', $preguntaId)
            ->first();

        $seleccionadas = $respuesta?->respuesta_multiple ?? [];

        if (in_array($opcion, $seleccionadas)) {
            $seleccionadas = array_values(array_diff($seleccionadas, [$opcion]));
        } else {
            $seleccionadas[] = $opcion;
        }

        RespuestaPreconsulta::updateOrCreate(
            ['consulta_id' => $this->consulta->id, 'pregunta_id' => $preguntaId],
            [
                'paciente_id'       => $this->consulta->paciente_id,
                'cita_id'           => $this->consulta->cita_id,
                'respuesta'         => null,
                'respuesta_multiple'=> $seleccionadas,
                'completado'        => true,
                'fecha_completado'  => now(),
                'token_unico'       => uniqid('cuest_', true),
                'empresa_id'        => auth()->user()->empresa_id,
                'sucursal_id'       => auth()->user()->sucursal_id,
            ]
        );

        $this->dispatch('notify', ['message' => 'Respuesta actualizada', 'type' => 'success']);
    }

    // ── Evaluación Dinámica ───────────────────────────────────────────────────

    public function guardarEvaluacion(): void
    {
        $this->consulta->evaluacion()->updateOrCreate(
            ['consulta_id' => $this->consulta->id],
            [
                'datos_dinamicos'         => $this->datos_dinamicos,
                'empresa_id'              => auth()->user()->empresa_id,
                'sucursal_id'             => auth()->user()->sucursal_id,
                'created_by'              => auth()->id(),
                'updated_by'              => auth()->id(),
            ]
        );

        $this->dispatch('notify', ['message' => 'Evaluación guardada', 'type' => 'success']);
    }

    // ── Diagnósticos ──────────────────────────────────────────────────────────

    public function updatedBusquedaDiagnostico($value): void
    {
        $this->diagnosticos_disponibles = strlen($value) >= 2
            ? \App\Models\Diagnostico::activos()->buscar($value)->limit(10)->get()->toArray()
            : [];
    }

    public function agregarDiagnostico($diagnosticoId, $tipo = 'secundario'): void
    {
        $diagnostico = \App\Models\Diagnostico::find($diagnosticoId);
        if (!$diagnostico) return;

        if (collect($this->diagnosticos_agregados)->contains('id', $diagnosticoId)) {
            $this->dispatch('notify', ['message' => 'Diagnóstico ya agregado', 'type' => 'warning']);
            return;
        }

        $this->consulta->diagnosticos()->attach($diagnosticoId, [
            'tipo'  => $tipo,
            'orden' => count($this->diagnosticos_agregados),
        ]);

        $this->diagnosticos_agregados[] = [
            'id'     => $diagnostico->id,
            'codigo' => $diagnostico->codigo,
            'nombre' => $diagnostico->nombre,
            'tipo'   => $tipo,
        ];

        $this->busqueda_diagnostico    = '';
        $this->diagnosticos_disponibles = [];
        $this->dispatch('notify', ['message' => 'Diagnóstico agregado', 'type' => 'success']);
    }

    public function crearYAgregarDiagnostico(): void
    {
        $this->validate([
            'nuevo_diagnostico_codigo' => 'required|string|max:10',
            'nuevo_diagnostico_nombre' => 'required|string|max:255',
        ]);

        $diagnostico = \App\Models\Diagnostico::create([
            'codigo'     => strtoupper($this->nuevo_diagnostico_codigo),
            'nombre'     => $this->nuevo_diagnostico_nombre,
            'empresa_id' => auth()->user()->empresa_id,
            'sucursal_id'=> auth()->user()->sucursal_id,
            'created_by' => auth()->id(),
        ]);

        $this->agregarDiagnostico($diagnostico->id, 'secundario');
        $this->nuevo_diagnostico_codigo = '';
        $this->nuevo_diagnostico_nombre = '';
        $this->mostrar_form_nuevo       = false;
    }

    public function eliminarDiagnostico($index): void
    {
        if (isset($this->diagnosticos_agregados[$index]['id'])) {
            $this->consulta->diagnosticos()->detach($this->diagnosticos_agregados[$index]['id']);
        }
        unset($this->diagnosticos_agregados[$index]);
        $this->diagnosticos_agregados = array_values($this->diagnosticos_agregados);
    }

    public function cambiarTipoDiagnostico($index, $tipo): void
    {
        if (isset($this->diagnosticos_agregados[$index]['id'])) {
            $this->consulta->diagnosticos()->updateExistingPivot(
                $this->diagnosticos_agregados[$index]['id'],
                ['tipo' => $tipo]
            );
            $this->diagnosticos_agregados[$index]['tipo'] = $tipo;
        }
    }

    // ── Estudios ──────────────────────────────────────────────────────────────

    public function agregarEstudio(): void
    {
        $this->validate([
            'tipo_estudio'  => 'required|in:imagen,laboratorio,otros',
            'nombre_estudio'=> 'required|string|max:255',
        ]);

        $estudio = ConsultaEstudio::create([
            'consulta_id'   => $this->consulta->id,
            'tipo_estudio'  => $this->tipo_estudio,
            'nombre_estudio'=> $this->nombre_estudio,
            'indicaciones'  => $this->indicaciones_estudio,
            'orden'         => count($this->estudios),
            'empresa_id'    => auth()->user()->empresa_id,
            'sucursal_id'   => auth()->user()->sucursal_id,
            'created_by'    => auth()->id(),
        ]);

        $this->estudios[]        = $estudio->toArray();
        $this->nombre_estudio    = '';
        $this->indicaciones_estudio = '';
        $this->dispatch('notify', ['message' => 'Estudio agregado', 'type' => 'success']);
    }

    public function eliminarEstudio($index): void
    {
        if (isset($this->estudios[$index]['id'])) {
            ConsultaEstudio::find($this->estudios[$index]['id'])->delete();
        }
        unset($this->estudios[$index]);
        $this->estudios = array_values($this->estudios);
    }

    // ── Tratamientos ──────────────────────────────────────────────────────────

    public function agregarTratamiento(): void
    {
        $this->validate([
            'medicamento'              => 'required|string|max:255',
            'indicaciones_tratamiento' => 'required|string',
        ]);

        $tratamiento = ConsultaTratamiento::create([
            'consulta_id' => $this->consulta->id,
            'medicamento' => $this->medicamento,
            'indicaciones'=> $this->indicaciones_tratamiento,
            'orden'       => count($this->tratamientos),
            'empresa_id'  => auth()->user()->empresa_id,
            'sucursal_id' => auth()->user()->sucursal_id,
            'created_by'  => auth()->id(),
        ]);

        $this->tratamientos[]           = $tratamiento->toArray();
        $this->medicamento              = '';
        $this->indicaciones_tratamiento = '';
        $this->dispatch('notify', ['message' => 'Tratamiento agregado', 'type' => 'success']);
    }

    public function eliminarTratamiento($index): void
    {
        if (isset($this->tratamientos[$index]['id'])) {
            ConsultaTratamiento::find($this->tratamientos[$index]['id'])->delete();
        }
        unset($this->tratamientos[$index]);
        $this->tratamientos = array_values($this->tratamientos);
    }

    // ── Reposo ────────────────────────────────────────────────────────────────

    public function updatedDiasReposo(): void
    {
        if ($this->dias_reposo && $this->fecha_inicio_reposo) {
            $this->fecha_fin_reposo = \Carbon\Carbon::parse($this->fecha_inicio_reposo)
                ->addDays($this->dias_reposo - 1)->format('Y-m-d');
        }
    }

    public function updatedFechaInicioReposo(): void
    {
        if ($this->dias_reposo && $this->fecha_inicio_reposo) {
            $this->fecha_fin_reposo = \Carbon\Carbon::parse($this->fecha_inicio_reposo)
                ->addDays($this->dias_reposo - 1)->format('Y-m-d');
        }
    }

    public function guardarReposo(): void
    {
        if (!$this->requiere_reposo) {
            $this->consulta->reposo?->delete();
            $this->dispatch('notify', ['message' => 'Reposo eliminado', 'type' => 'success']);
            return;
        }

        $this->validate([
            'motivo_reposo'       => 'required|string',
            'dias_reposo'         => 'required|integer|min:1|max:365',
            'fecha_inicio_reposo' => 'required|date',
        ]);

        \App\Models\Reposo::updateOrCreate(
            ['consulta_id' => $this->consulta->id],
            [
                'paciente_id'  => $this->consulta->paciente_id,
                'medico_id'    => $this->consulta->medico_id,
                'motivo'       => $this->motivo_reposo,
                'dias_reposo'  => $this->dias_reposo,
                'fecha_inicio' => $this->fecha_inicio_reposo,
                'fecha_fin'    => $this->fecha_fin_reposo,
                'observaciones'=> $this->observaciones_reposo,
                'empresa_id'   => auth()->user()->empresa_id,
                'sucursal_id'  => auth()->user()->sucursal_id,
            ]
        );

        $this->dispatch('notify', ['message' => 'Reposo guardado', 'type' => 'success']);
    }

    // ── Datos por estado ─────────────────────────────────────────────────────

    private function cargarDatosEstadoActual(): void
    {
        $estado = $this->consulta->estado;
        $this->datos_estado_actual = $this->consulta->getDatosEstado($estado);

        // Inicializar valores por defecto para campos sin valor
        $secciones = $this->formulariosPorEstado[$estado]['secciones'] ?? [];
        foreach ($secciones as $seccion) {
            foreach ($seccion['campos'] as $campo) {
                if (!isset($this->datos_estado_actual[$campo['nombre_campo']])) {
                    $this->datos_estado_actual[$campo['nombre_campo']] = $campo['valor_defecto'] ?? (
                        $campo['tipo'] === 'checkbox' ? [] : null
                    );
                }
            }
        }
    }

    public function guardarDatosEstado(): void
    {
        $estado = $this->consulta->estado;

        ConsultaEstadoDato::updateOrCreate(
            ['consulta_id' => $this->consulta->id, 'estado' => $estado],
            [
                'datos'       => $this->datos_estado_actual,
                'empresa_id'  => auth()->user()->empresa_id,
                'sucursal_id' => auth()->user()->sucursal_id,
                'created_by'  => auth()->id(),
                'updated_by'  => auth()->id(),
            ]
        );

        $this->dispatch('notify', ['message' => 'Datos guardados', 'type' => 'success']);
    }

    // ── Estado de la consulta ─────────────────────────────────────────────────

    public function cambiarEstadoConsulta(): void
    {
        if (empty($this->nuevoEstado)) return;

        $estadosValidos = array_merge(
            $this->estadosFlujo,
            array_keys(EspecialidadPlantilla::ESTADOS_DISPONIBLES)
        );

        if (!in_array($this->nuevoEstado, $estadosValidos)) {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'Estado no válido']);
            return;
        }

        $this->consulta->update([
            'estado'            => $this->nuevoEstado,
            'estado_changed_at' => now(),
        ]);
        $this->consulta->refresh();

        // Cargar datos del nuevo estado
        $this->cargarDatosEstadoActual();

        $label = EspecialidadPlantilla::ESTADOS_DISPONIBLES[$this->nuevoEstado] ?? $this->nuevoEstado;
        $this->dispatch('show-toast', ['type' => 'success', 'message' => "Estado actualizado a {$label}"]);
        $this->nuevoEstado = '';
    }

    public function finalizarConsulta()
    {
        $this->consulta->cambiarEstado(Consulta::ESTADO_FINALIZADA);
        
        // Si la consulta tiene una cita asociada, finalizarla también
        if ($this->consulta->cita_id && $this->consulta->cita) {
            $this->consulta->cita->cambiarEstado(\App\Models\Cita::ESTADO_FINALIZADA);
        }
        
        session()->flash('success', 'Consulta finalizada exitosamente');
        return redirect()->to('/admin/gestion/consultas/en-consultorio');
    }

    // ── Render ────────────────────────────────────────────────────────────────

    public function render()
    {
        $respuestasPreconsulta = RespuestaPreconsulta::with('pregunta')
            ->where('consulta_id', $this->consulta->id)
            ->orderBy('id')->get();

        $preguntasObligatorias = collect($this->preguntas_cuestionario)->where('obligatorio', true)->pluck('id');
        $cuestionarioCompleto  = $preguntasObligatorias->every(
            fn($id) => $respuestasPreconsulta->pluck('pregunta_id')->contains($id)
        );

        $totalPasos = count($this->pasosHabilitados);

        return view('livewire.admin.consulta.proceso-consulta', [
            'respuestasPreconsulta'  => $respuestasPreconsulta,
            'cuestionarioCompleto'   => $cuestionarioCompleto,
            'totalPasos'             => $totalPasos,
            'pasoKey'                => $this->pasosHabilitados[$this->pasoActual] ?? null,
            'estadosDisponibles'     => EspecialidadPlantilla::ESTADOS_DISPONIBLES,
            'formularioEstadoActual' => $this->formulariosPorEstado[$this->consulta->estado] ?? null,
        ])->layout($this->getLayout());
    }
}
