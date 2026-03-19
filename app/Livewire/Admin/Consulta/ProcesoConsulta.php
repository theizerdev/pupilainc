<?php

namespace App\Livewire\Admin\Consulta;

use Livewire\Component;
use App\Models\Consulta;
use App\Models\ConsultaEvaluacion;
use App\Models\ConsultaEstudio;
use App\Models\ConsultaTratamiento;
use App\Models\RespuestaPreconsulta;

class ProcesoConsulta extends Component
{
    public $consulta;
    public $pasoActual = 0; // Iniciar en paso 0 (Signos Vitales)
    public $nuevoEstado = '';
    
    // Paso 0: Signos Vitales
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
    
    // Paso 1: Cuestionario
    public $respuestas_cuestionario = [];
    public $preguntas_cuestionario = [];
    public $ultima_respuesta_pregunta = null;
    
    // Paso 2: Evaluación
    public $enfermedad_actual;
    public $examen_fisico;
    public $conclusion;
    public $observaciones_adicionales;
    
    // Diagnósticos
    public $busqueda_diagnostico = '';
    public $diagnosticos_disponibles = [];
    public $diagnosticos_agregados = [];
    public $nuevo_diagnostico_codigo = '';
    public $nuevo_diagnostico_nombre = '';
    public $mostrar_form_nuevo = false;
    
    // Paso 3: Estudios
    public $tipo_estudio = 'imagen';
    public $nombre_estudio;
    public $indicaciones_estudio;
    public $estudios = [];
    
    // Paso 4: Tratamientos
    public $medicamento;
    public $indicaciones_tratamiento;
    public $tratamientos = [];
    
    // Paso 5: Reposo Médico
    public $requiere_reposo = false;
    public $motivo_reposo;
    public $dias_reposo;
    public $fecha_inicio_reposo;
    public $fecha_fin_reposo;
    public $observaciones_reposo;

    public function mount($consultaId)
    {
        $this->consulta = Consulta::with(['evaluacion', 'estudios', 'tratamientos', 'paciente', 'medico', 'signosVitales', 'diagnosticos', 'reposo'])
            ->findOrFail($consultaId);
        
        // Cargar historial de signos vitales del paciente (últimos 10)
        $this->historial_signos = \App\Models\SignosVitales::where('paciente_id', $this->consulta->paciente_id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get(['temperatura', 'presion_arterial_sistolica', 'presion_arterial_diastolica', 'imc', 'created_at'])
            ->reverse()
            ->values()
            ->toArray();
        
        // Cargar signos vitales existentes
        $signosVitales = $this->consulta->signosVitales()->latest()->first();
        if ($signosVitales) {
            $this->presion_sistolica = $signosVitales->presion_arterial_sistolica;
            $this->presion_diastolica = $signosVitales->presion_arterial_diastolica;
            $this->frecuencia_cardiaca = $signosVitales->frecuencia_cardiaca;
            $this->frecuencia_respiratoria = $signosVitales->frecuencia_respiratoria;
            $this->temperatura = $signosVitales->temperatura;
            $this->peso = $signosVitales->peso;
            $this->talla = $signosVitales->talla;
            $this->saturacion_oxigeno = $signosVitales->saturacion_oxigeno;
            $this->observaciones_signos = $signosVitales->observaciones;
            $this->imc_calculado = $signosVitales->imc;
        }
        
        $this->calcularIMC();
        
        // Cargar preguntas del cuestionario activo
        $cuestionario = \App\Models\Cuestionario::where('activo', true)
            ->where('empresa_id', auth()->user()->empresa_id)
            ->first();
        
        if ($cuestionario) {
            $this->preguntas_cuestionario = \App\Models\Pregunta::where('cuestionario_id', $cuestionario->id)
                ->where('activo', true)
                ->orderBy('orden')
                ->get()
                ->toArray();
        }
        
        // Cargar datos existentes de evaluación
        if ($this->consulta->evaluacion) {
            $this->enfermedad_actual = $this->consulta->evaluacion->enfermedad_actual;
            $this->examen_fisico = $this->consulta->evaluacion->examen_fisico;
            $this->conclusion = $this->consulta->evaluacion->conclusion;
            $this->observaciones_adicionales = $this->consulta->evaluacion->observaciones_adicionales;
        }
        
        // Cargar diagnósticos existentes
        $this->diagnosticos_agregados = $this->consulta->diagnosticos ? 
            $this->consulta->diagnosticos->map(function($diag) {
                return [
                    'id' => $diag->id,
                    'codigo' => $diag->codigo,
                    'nombre' => $diag->nombre,
                    'tipo' => $diag->pivot->tipo,
                ];
            })->toArray() : [];
        
        $this->estudios = $this->consulta->estudios->toArray();
        $this->tratamientos = $this->consulta->tratamientos->toArray();
        
        // Cargar reposo existente
        if ($this->consulta->reposo) {
            $this->requiere_reposo = true;
            $this->motivo_reposo = $this->consulta->reposo->motivo;
            $this->dias_reposo = $this->consulta->reposo->dias_reposo;
            $this->fecha_inicio_reposo = $this->consulta->reposo->fecha_inicio->format('Y-m-d');
            $this->fecha_fin_reposo = $this->consulta->reposo->fecha_fin->format('Y-m-d');
            $this->observaciones_reposo = $this->consulta->reposo->observaciones;
        } else {
            $this->fecha_inicio_reposo = now()->format('Y-m-d');
        }
    }

    // Paso 1: Guardar respuesta de cuestionario
    public function guardarRespuestaCuestionario($preguntaId, $respuesta, $detalle = null)
    {
        // Si solo se está guardando detalle, usar la última respuesta
        if ($respuesta === null && $this->ultima_respuesta_pregunta === $preguntaId) {
            $respuestaExistente = RespuestaPreconsulta::where('consulta_id', $this->consulta->id)
                ->where('pregunta_id', $preguntaId)
                ->first();
            
            if ($respuestaExistente) {
                $respuestaExistente->update(['detalle' => $detalle]);
                $this->dispatch('notify', ['message' => 'Detalle guardado', 'type' => 'success']);
                return;
            }
        }

        RespuestaPreconsulta::updateOrCreate(
            [
                'consulta_id' => $this->consulta->id,
                'pregunta_id' => $preguntaId,
            ],
            [
                'paciente_id' => $this->consulta->paciente_id,
                'cita_id' => $this->consulta->cita_id,
                'respuesta' => is_array($respuesta) ? null : $respuesta,
                'respuesta_multiple' => is_array($respuesta) ? $respuesta : null,
                'detalle' => $detalle,
                'completado' => true,
                'fecha_completado' => now(),
                'token_unico' => uniqid('cuest_', true),
                'empresa_id' => auth()->user()->empresa_id,
                'sucursal_id' => auth()->user()->sucursal_id,
            ]
        );

        $this->ultima_respuesta_pregunta = $preguntaId;
        $this->dispatch('notify', ['message' => 'Respuesta guardada', 'type' => 'success']);
    }

    public function irPaso($paso)
    {
        // Si está saliendo del paso 0, guardar signos vitales automáticamente
        if ($this->pasoActual === 0 && $paso > 0) {
            $this->guardarSignosVitales();
        }

        $this->pasoActual = $paso;
    }

    public function cambiarEstadoConsulta()
    {
        if (empty($this->nuevoEstado)) {
            return;
        }

        $estadosValidos = [
            Consulta::ESTADO_POR_LLEGAR,
            Consulta::ESTADO_SALA_ESPERA,
            Consulta::ESTADO_EN_ENFERMERIA,
            Consulta::ESTADO_EN_CONSULTORIO,
            Consulta::ESTADO_EN_CONSULTORIO_OPTOMETRISTA,
            Consulta::ESTADO_EN_GOTAS,
            Consulta::ESTADO_EN_OPTICA,
            Consulta::ESTADO_EN_ESTUDIO,
            Consulta::ESTADO_FINALIZADA,
        ];

        if (!in_array($this->nuevoEstado, $estadosValidos)) {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'Estado no válido']);
            return;
        }

        $this->consulta->update([
            'estado' => $this->nuevoEstado,
            'estado_changed_at' => now(),
        ]);

        $this->consulta->refresh();

        $this->dispatch('show-toast', [
            'type' => 'success',
            'message' => 'Estado actualizado a ' . Consulta::ESTADO_LABELS[$this->nuevoEstado]
        ]);

        $this->nuevoEstado = '';
    }

    // Calcular IMC en tiempo real
    public function updatedPeso()
    {
        $this->calcularIMC();
        $this->skipRender();
    }

    public function updatedTalla()
    {
        $this->calcularIMC();
        $this->skipRender();
    }

    public function updatedPresionSistolica()
    {
        $this->skipRender();
    }

    public function updatedPresionDiastolica()
    {
        $this->skipRender();
    }

    public function updatedTemperatura()
    {
        $this->skipRender();
    }

    private function calcularIMC()
    {
        if ($this->peso && $this->talla) {
            $tallaMetros = $this->talla / 100;
            $this->imc_calculado = round($this->peso / ($tallaMetros * $tallaMetros), 1);
        } else {
            $this->imc_calculado = null;
        }
    }

    // Paso 0: Guardar signos vitales
    public function guardarSignosVitales()
    {
        $this->consulta->signosVitales()->updateOrCreate(
            ['consulta_id' => $this->consulta->id],
            [
                'paciente_id' => $this->consulta->paciente_id,
                'presion_arterial_sistolica' => $this->presion_sistolica,
                'presion_arterial_diastolica' => $this->presion_diastolica,
                'frecuencia_cardiaca' => $this->frecuencia_cardiaca,
                'frecuencia_respiratoria' => $this->frecuencia_respiratoria,
                'temperatura' => $this->temperatura,
                'peso' => $this->peso,
                'talla' => $this->talla,
                'imc' => $this->imc_calculado,
                'saturacion_oxigeno' => $this->saturacion_oxigeno,
                'observaciones' => $this->observaciones_signos,
                'empresa_id' => auth()->user()->empresa_id,
                'sucursal_id' => auth()->user()->sucursal_id,
                'created_by' => auth()->id(),
            ]
        );

        // Recargar historial
        $this->historial_signos = \App\Models\SignosVitales::where('paciente_id', $this->consulta->paciente_id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get(['temperatura', 'presion_arterial_sistolica', 'presion_arterial_diastolica', 'imc', 'created_at'])
            ->reverse()
            ->values()
            ->toArray();

        $this->dispatch('notify', ['message' => 'Signos vitales guardados', 'type' => 'success']);
        $this->dispatch('chartsUpdated', ['historial' => $this->historial_signos]);
    }

    // Paso 2: Guardar evaluación (autoguardado)
    public function guardarEvaluacion()
    {
        $this->consulta->evaluacion()->updateOrCreate(
            ['consulta_id' => $this->consulta->id],
            [
                'enfermedad_actual' => $this->enfermedad_actual,
                'examen_fisico' => $this->examen_fisico,
                'conclusion' => $this->conclusion,
                'observaciones_adicionales' => $this->observaciones_adicionales,
                'empresa_id' => auth()->user()->empresa_id,
                'sucursal_id' => auth()->user()->sucursal_id,
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]
        );

        $this->dispatch('notify', ['message' => 'Evaluación guardada automáticamente', 'type' => 'success']);
    }

    // Paso 3: Agregar estudio
    public function agregarEstudio()
    {
        $this->validate([
            'tipo_estudio' => 'required|in:imagen,laboratorio,otros',
            'nombre_estudio' => 'required|string|max:255',
        ]);

        $estudio = ConsultaEstudio::create([
            'consulta_id' => $this->consulta->id,
            'tipo_estudio' => $this->tipo_estudio,
            'nombre_estudio' => $this->nombre_estudio,
            'indicaciones' => $this->indicaciones_estudio,
            'orden' => count($this->estudios),
            'empresa_id' => auth()->user()->empresa_id,
            'sucursal_id' => auth()->user()->sucursal_id,
            'created_by' => auth()->id(),
        ]);

        $this->estudios[] = $estudio->toArray();
        
        // Limpiar campos
        $this->nombre_estudio = '';
        $this->indicaciones_estudio = '';
        
        $this->dispatch('notify', ['message' => 'Estudio agregado', 'type' => 'success']);
    }

    public function eliminarEstudio($index)
    {
        if (isset($this->estudios[$index]['id'])) {
            ConsultaEstudio::find($this->estudios[$index]['id'])->delete();
        }
        unset($this->estudios[$index]);
        $this->estudios = array_values($this->estudios);
    }

    // Paso 4: Agregar tratamiento
    public function agregarTratamiento()
    {
        $this->validate([
            'medicamento' => 'required|string|max:255',
            'indicaciones_tratamiento' => 'required|string',
        ]);

        $tratamiento = ConsultaTratamiento::create([
            'consulta_id' => $this->consulta->id,
            'medicamento' => $this->medicamento,
            'indicaciones' => $this->indicaciones_tratamiento,
            'orden' => count($this->tratamientos),
            'empresa_id' => auth()->user()->empresa_id,
            'sucursal_id' => auth()->user()->sucursal_id,
            'created_by' => auth()->id(),
        ]);

        $this->tratamientos[] = $tratamiento->toArray();
        
        // Limpiar campos
        $this->medicamento = '';
        $this->indicaciones_tratamiento = '';
        
        $this->dispatch('notify', ['message' => 'Tratamiento agregado', 'type' => 'success']);
    }

    public function eliminarTratamiento($index)
    {
        if (isset($this->tratamientos[$index]['id'])) {
            ConsultaTratamiento::find($this->tratamientos[$index]['id'])->delete();
        }
        unset($this->tratamientos[$index]);
        $this->tratamientos = array_values($this->tratamientos);
    }
    
    // Paso 5: Calcular fecha fin de reposo
    public function updatedDiasReposo()
    {
        if ($this->dias_reposo && $this->fecha_inicio_reposo) {
            $this->fecha_fin_reposo = \Carbon\Carbon::parse($this->fecha_inicio_reposo)
                ->addDays($this->dias_reposo - 1)
                ->format('Y-m-d');
        }
    }
    
    public function updatedFechaInicioReposo()
    {
        if ($this->dias_reposo && $this->fecha_inicio_reposo) {
            $this->fecha_fin_reposo = \Carbon\Carbon::parse($this->fecha_inicio_reposo)
                ->addDays($this->dias_reposo - 1)
                ->format('Y-m-d');
        }
    }
    
    public function guardarReposo()
    {
        if (!$this->requiere_reposo) {
            // Eliminar reposo si existe
            if ($this->consulta->reposo) {
                $this->consulta->reposo->delete();
            }
            $this->dispatch('notify', ['message' => 'Reposo eliminado', 'type' => 'success']);
            return;
        }
        
        $this->validate([
            'motivo_reposo' => 'required|string',
            'dias_reposo' => 'required|integer|min:1|max:365',
            'fecha_inicio_reposo' => 'required|date',
        ]);
        
        \App\Models\Reposo::updateOrCreate(
            ['consulta_id' => $this->consulta->id],
            [
                'paciente_id' => $this->consulta->paciente_id,
                'medico_id' => $this->consulta->medico_id,
                'motivo' => $this->motivo_reposo,
                'dias_reposo' => $this->dias_reposo,
                'fecha_inicio' => $this->fecha_inicio_reposo,
                'fecha_fin' => $this->fecha_fin_reposo,
                'observaciones' => $this->observaciones_reposo,
                'empresa_id' => auth()->user()->empresa_id,
                'sucursal_id' => auth()->user()->sucursal_id,
            ]
        );
        
        $this->dispatch('notify', ['message' => 'Reposo guardado', 'type' => 'success']);
    }

    // Diagnósticos
    public function updatedBusquedaDiagnostico($value)
    {
        if (strlen($value) >= 2) {
            $this->diagnosticos_disponibles = \App\Models\Diagnostico::activos()
                ->buscar($value)
                ->limit(10)
                ->get()
                ->toArray();
        } else {
            $this->diagnosticos_disponibles = [];
        }
    }

    public function agregarDiagnostico($diagnosticoId, $tipo = 'secundario')
    {
        $diagnostico = \App\Models\Diagnostico::find($diagnosticoId);
        
        if (!$diagnostico) return;
        
        // Verificar si ya está agregado
        if (collect($this->diagnosticos_agregados)->contains('id', $diagnosticoId)) {
            $this->dispatch('notify', ['message' => 'Diagnóstico ya agregado', 'type' => 'warning']);
            return;
        }

        $this->consulta->diagnosticos()->attach($diagnosticoId, [
            'tipo' => $tipo,
            'orden' => count($this->diagnosticos_agregados),
        ]);

        $this->diagnosticos_agregados[] = [
            'id' => $diagnostico->id,
            'codigo' => $diagnostico->codigo,
            'nombre' => $diagnostico->nombre,
            'tipo' => $tipo,
        ];

        $this->busqueda_diagnostico = '';
        $this->diagnosticos_disponibles = [];
        
        $this->dispatch('notify', ['message' => 'Diagnóstico agregado', 'type' => 'success']);
    }

    public function crearYAgregarDiagnostico()
    {
        $this->validate([
            'nuevo_diagnostico_codigo' => 'required|string|max:10',
            'nuevo_diagnostico_nombre' => 'required|string|max:255',
        ]);

        $diagnostico = \App\Models\Diagnostico::create([
            'codigo' => strtoupper($this->nuevo_diagnostico_codigo),
            'nombre' => $this->nuevo_diagnostico_nombre,
            'empresa_id' => auth()->user()->empresa_id,
            'sucursal_id' => auth()->user()->sucursal_id,
            'created_by' => auth()->id(),
        ]);

        $this->agregarDiagnostico($diagnostico->id, 'secundario');
        
        $this->nuevo_diagnostico_codigo = '';
        $this->nuevo_diagnostico_nombre = '';
        $this->mostrar_form_nuevo = false;
    }

    public function eliminarDiagnostico($index)
    {
        if (isset($this->diagnosticos_agregados[$index]['id'])) {
            $this->consulta->diagnosticos()->detach($this->diagnosticos_agregados[$index]['id']);
        }
        unset($this->diagnosticos_agregados[$index]);
        $this->diagnosticos_agregados = array_values($this->diagnosticos_agregados);
    }

    public function cambiarTipoDiagnostico($index, $tipo)
    {
        if (isset($this->diagnosticos_agregados[$index]['id'])) {
            $this->consulta->diagnosticos()->updateExistingPivot(
                $this->diagnosticos_agregados[$index]['id'],
                ['tipo' => $tipo]
            );
            $this->diagnosticos_agregados[$index]['tipo'] = $tipo;
        }
    }

    public function finalizarConsulta()
    {
        $this->consulta->cambiarEstado(Consulta::ESTADO_FINALIZADA);
        
        session()->flash('success', 'Consulta finalizada exitosamente');
        return redirect()->route('admin.gestion.consultas.index');
    }

    public function render()
    {
        // Cargar respuestas de preconsulta asociadas a esta consulta
        $respuestasPreconsulta = RespuestaPreconsulta::with('pregunta')
            ->where('consulta_id', $this->consulta->id)
            ->orderBy('id')
            ->get();

        // Verificar si el cuestionario está completo (todas las preguntas obligatorias respondidas)
        $preguntasObligatorias = collect($this->preguntas_cuestionario)->where('obligatorio', true)->pluck('id');
        $respuestasObligatorias = $respuestasPreconsulta->pluck('pregunta_id');
        $cuestionarioCompleto = $preguntasObligatorias->every(fn($id) => $respuestasObligatorias->contains($id));

        return view('livewire.admin.consulta.proceso-consulta', [
            'respuestasPreconsulta' => $respuestasPreconsulta,
            'cuestionarioCompleto' => $cuestionarioCompleto,
        ]);
    }
}
