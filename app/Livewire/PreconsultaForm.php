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
        // Guardar respuesta temporalmente
        $respuesta = RespuestaPreconsulta::with('pregunta')->find($key);

        if ($respuesta) {
            if ($respuesta->pregunta->tipo === 'multiple') {
                if (!is_array($value)) {
                    $value = $value ? [$value] : [];
                }
                $respuesta->update(['respuesta_multiple' => $value]);
            } else {
                $respuesta->update(['respuesta' => $value]);
            }
        }
    }

    public function siguientePaso()
    {
        $this->validate($this->rules());

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
        $this->validate($this->rules());

        // Marcar todas las respuestas como completadas
        RespuestaPreconsulta::where('token_unico', $this->token)
            ->update([
                'completado' => true,
                'fecha_completado' => now(),
            ]);

        // Actualizar el estado de la cita
        $respuesta = $this->respuestasPreconsulta->first();
        if ($respuesta->cita) {
            $respuesta->cita->update([
                'estado' => \App\Models\Cita::ESTADO_SALA_ESPERA,
                'estado_preconsulta' => 'completado',
                'fecha_completado_preconsulta' => now(),
            ]);

            // Crear/Actualizar la Consulta ligada a la cita
            $cita = $respuesta->cita->fresh();
            $consulta = \App\Models\Consulta::firstOrCreate(
                ['cita_id' => $cita->id],
                [
                    'paciente_id' => $cita->paciente_id,
                    'medico_id' => $cita->medico_id,
                    'especialidad_id' => $cita->especialidad_id,
                    'empresa_id' => $cita->empresa_id,
                    'sucursal_id' => $cita->sucursal_id,
                    'fecha_consulta' => now(),
                    'estado' => \App\Models\Consulta::ESTADO_SALA_ESPERA,
                    'preconsulta' => true,
                ]
            );

            if (empty($consulta->codigo)) {
                $unique = strtoupper(substr(md5(uniqid((string) $consulta->id, true)), 0, 8));
                $consulta->update(['codigo' => 'CONS-' . date('ymd') . '-' . $unique]);
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
