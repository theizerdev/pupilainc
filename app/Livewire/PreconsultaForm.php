<?php

namespace App\Livewire;

use App\Models\RespuestaPreconsulta;
use Livewire\Component;

class PreconsultaForm extends Component
{
    public $token;
    public $respuestas = [];
    public $respuestasPreconsulta;
    public $cuestionario;
    public $paciente;
    public $empresa;
    public $paso = 1;
    public $totalPasos = 1;
    public $consultaCodigo = null;

    public function mount($token)
    {
        $this->token = $token;

        // Buscar respuestas por token
        $this->respuestasPreconsulta = RespuestaPreconsulta::with(['pregunta', 'paciente', 'empresa'])
            ->where('token_unico', $token)
            ->get();

        if ($this->respuestasPreconsulta->isEmpty()) {
            abort(404, 'Cuestionario no encontrado.');
        }

        $this->paciente = $this->respuestasPreconsulta->first()->paciente;
        $this->empresa = $this->respuestasPreconsulta->first()->empresa;
        $this->cuestionario = $this->respuestasPreconsulta->first()->pregunta->cuestionario;

        // Organizar preguntas por orden
        $this->respuestasPreconsulta = $this->respuestasPreconsulta->sortBy('pregunta.orden');

        // Inicializar respuestas
        foreach ($this->respuestasPreconsulta as $respuesta) {
            if ($respuesta->pregunta->tipo === 'multiple') {
                $val = $respuesta->respuesta_multiple;
                // Asegurar que sea array
                if (is_array($val)) {
                    $this->respuestas[$respuesta->id] = $val;
                } elseif (!empty($val)) {
                    // Si es string (dato corrupto/antiguo), convertir a array
                    $this->respuestas[$respuesta->id] = [$val];
                } else {
                    $this->respuestas[$respuesta->id] = [];
                }
            } else {
                $this->respuestas[$respuesta->id] = $respuesta->respuesta ?? '';
            }
        }

        $this->totalPasos = ceil($this->respuestasPreconsulta->count() / 5); // 5 preguntas por paso
    }

    public function rules()
    {
        $preguntasActuales = $this->getPreguntasActuales();
        $rules = [];

        foreach ($preguntasActuales as $respuesta) {
            if ($respuesta->pregunta->obligatorio) {
                if ($respuesta->pregunta->tipo === 'multiple') {
                    $rules["respuestas.{$respuesta->id}"] = 'required|array|min:1';
                } else {
                    $rules["respuestas.{$respuesta->id}"] = 'required';
                }
            }
        }

        return $rules;
    }

    public function updatedRespuestas($value, $key)
    {
        // Guardar respuesta temporalmente (soporte para 'respuestas.{id}')
        $id = null;
        if (is_numeric($key)) {
            $id = (int) $key;
        } elseif (is_string($key) && preg_match('/^respuestas\.(\d+)$/', $key, $m)) {
            $id = (int) $m[1];
        }

        if (!$id) {
            return;
        }

        $respuesta = RespuestaPreconsulta::with('pregunta')->find($id);

        if ($respuesta) {
            if ($respuesta->pregunta->tipo === 'multiple') {
                if (!is_array($value)) {
                    $value = $value ? [$value] : [];
                }
                $respuesta->update(['respuesta_multiple' => $value]);
            } else {
                if (is_array($value)) {
                    $value = implode(', ', $value);
                }
                $respuesta->update(['respuesta' => $value]);
            }
        }
    }

    public function siguientePaso()
    {
        $stepRules = $this->rules();
        if (!empty($stepRules)) {
            $this->validate($stepRules);
        }

        if ($this->paso < $this->totalPasos) {
            $this->paso++;
        }
    }

    public function pasoAnterior()
    {
        if ($this->paso > 1) {
            $this->paso--;
        }
    }

    private function getPreguntasActuales()
    {
        $inicio = ($this->paso - 1) * 5;
        return $this->respuestasPreconsulta->slice($inicio, 5);
    }

    public function finalizarCuestionario()
    {
        $stepRules = $this->rules();
        if (!empty($stepRules)) {
            $this->validate($stepRules);
        }

        foreach ($this->respuestasPreconsulta as $respuestaRow) {
            $rid = $respuestaRow->id;
            if (array_key_exists($rid, $this->respuestas)) {
                $val = $this->respuestas[$rid];
                if ($respuestaRow->pregunta->tipo === 'multiple') {
                    if (!is_array($val)) {
                        $val = $val ? [$val] : [];
                    }
                    $respuestaRow->update(['respuesta_multiple' => $val]);
                } else {
                    if (is_array($val)) {
                        $val = implode(', ', $val);
                    }
                    $respuestaRow->update(['respuesta' => $val]);
                }
            }
        }

        // Marcar todas las respuestas como completadas
        RespuestaPreconsulta::where('token_unico', $this->token)
            ->update([
                'completado' => true,
                'fecha_completado' => now(),
            ]);

        // Actualizar el estado de la cita y crear la consulta (con o sin cita)
        $primeraRespuesta = \App\Models\RespuestaPreconsulta::with(['paciente', 'empresa', 'cita'])
            ->where('token_unico', $this->token)
            ->first();

        if ($primeraRespuesta && $primeraRespuesta->cita) {
            $primeraRespuesta->cita->update([
                'estado' => \App\Models\Cita::ESTADO_COMPLETADA,
                'estado_preconsulta' => 'completado',
                'fecha_completado_preconsulta' => now(),
            ]);

            $cita = $primeraRespuesta->cita->fresh();
            $consulta = \App\Models\Consulta::firstOrCreate(
                ['cita_id' => $cita->id],
                [
                    'paciente_id' => $cita->paciente_id,
                    'medico_id' => $cita->medico_id,
                    'especialidad_id' => $cita->especialidad_id,
                    'motivo_consulta' => $cita->motivo_consulta,
                    'empresa_id' => $cita->empresa_id,
                    'sucursal_id' => $cita->sucursal_id,
                    'fecha_consulta' => now(),
                    'estado' => \App\Models\Consulta::ESTADO_SALA_ESPERA,
                    'preconsulta' => true,
                ]
            );

            if (empty($consulta->codigo)) {
                $candidate = null;
                for ($i = 0; $i < 5; $i++) {
                    $n = random_int(10000000, 99999999);
                    if (!\App\Models\Consulta::where('codigo', (string) $n)->exists()) {
                        $candidate = (string) $n;
                        break;
                    }
                }
                $consulta->update(['codigo' => $candidate ?? (string) random_int(10000000, 99999999)]);
            }
            $this->consultaCodigo = $consulta->codigo;
        } else {
            // Sin cita: crear consulta con datos del paciente y empresa, asignando médico por defecto
            $paciente = $primeraRespuesta?->paciente ?? $this->paciente;
            $empresa = $primeraRespuesta?->empresa ?? $this->empresa;

            // Intentar usar el último médico de una cita previa del paciente
            $ultimoMedicoId = \App\Models\Cita::where('paciente_id', $paciente->id ?? null)
                ->orderBy('fecha_inicio', 'desc')
                ->value('medico_id');

            // Si no hay, buscar médico activo de la empresa (y sucursal)
            $medico = \App\Models\Medico::activos()
                ->where('empresa_id', $empresa->id ?? auth()->user()->empresa_id)
                ->when($sucursalId, fn($q) => $q->where('sucursal_id', $sucursalId))
                ->first();

            // Elegir médico: último de cita o primero activo
            $medicoId = $ultimoMedicoId ?: ($medico?->id);

            if ($paciente && $empresa && $medicoId) {
                $consulta = \App\Models\Consulta::create([
                    'cita_id' => null,
                    'paciente_id' => $paciente->id,
                    'medico_id' => $medicoId,
                    'especialidad_id' => null,
                    'empresa_id' => $empresa->id,
                    'sucursal_id' => $sucursalId,
                    'fecha_consulta' => now(),
                    'estado' => \App\Models\Consulta::ESTADO_SALA_ESPERA,
                    'preconsulta' => true,
                    'motivo_consulta' => $this->cuestionario?->titulo ?? 'Preconsulta completada',
                ]);
                $this->consultaCodigo = $consulta->codigo;
            } else {
                session()->flash('warning', 'Preconsulta completada. No se pudo crear la consulta por falta de médico activo.');
            }
        }

        // Actualizar estado local para reflejar cambios en la vista
        $this->respuestasPreconsulta->each(function ($item) {
            $item->completado = true;
        });

        session()->flash('success', '¡Gracias por completar el cuestionario! Su información ha sido registrada exitosamente.');

        $this->dispatch('cerrar-ventana');
    }

    public function render()
    {
        $preguntasActuales = $this->getPreguntasActuales();

        return view('livewire.preconsulta-form', [
            'preguntasActuales' => $preguntasActuales,
        ])->layout('layouts.preconsulta');
    }
}
