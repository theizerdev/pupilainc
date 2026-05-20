<?php

namespace App\Livewire;

use App\Models\RespuestaPreconsulta;
use Livewire\Component;

class PreconsultaForm extends Component
{
    public $token;
    public $respuestas = [];
    public $detalles = [];
    public $respuestasPreconsulta;
    public $cuestionario;
    public $paciente;
    public $mascota;
    public $empresa;
    public $paso = 1;
    public $totalPasos = 1;
    public $consultaCodigo = null;
    public $esVeterinaria = false;

    public function mount($token)
    {
        $this->token = $token;

        // Buscar respuestas por token con relaciones de paciente y mascota
        $this->respuestasPreconsulta = RespuestaPreconsulta::with(['pregunta', 'paciente', 'mascota', 'empresa'])
            ->where('token_unico', $token)
            ->get();

        if ($this->respuestasPreconsulta->isEmpty()) {
            abort(404, 'Cuestionario no encontrado.');
        }

        $primeraRespuesta = $this->respuestasPreconsulta->first();

        // Determinar si es veterinaria o humana
        $this->esVeterinaria = $primeraRespuesta->mascota_id && !$primeraRespuesta->paciente_id;

        if ($this->esVeterinaria) {
            $this->mascota = $primeraRespuesta->mascota;
            $this->paciente = null; // No hay paciente humano en citas veterinarias
        } else {
            $this->paciente = $primeraRespuesta->paciente;
            $this->mascota = null;
        }

        $this->empresa = $primeraRespuesta->empresa;
        $this->cuestionario = $primeraRespuesta->pregunta->cuestionario;

        // Organizar preguntas por orden
        $this->respuestasPreconsulta = $this->respuestasPreconsulta->sortBy('pregunta.orden');

        // Inicializar respuestas y detalles
        foreach ($this->respuestasPreconsulta as $respuesta) {
            if ($respuesta->pregunta->tipo === 'multiple') {
                $val = $respuesta->respuesta_multiple;
                if (is_array($val)) {
                    $this->respuestas[$respuesta->id] = $val;
                } elseif (!empty($val)) {
                    $this->respuestas[$respuesta->id] = [$val];
                } else {
                    $this->respuestas[$respuesta->id] = [];
                }
            } else {
                $this->respuestas[$respuesta->id] = $respuesta->respuesta ?? '';
            }

            // Inicializar detalles si existen
            $this->detalles[$respuesta->id] = $respuesta->detalle ?? '';
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

    public function updatedDetalles($value, $key)
    {
        // No guardar automáticamente, solo actualizar el estado local
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

            // Guardar detalles
            if (array_key_exists($rid, $this->detalles)) {
                $respuestaRow->update(['detalle' => $this->detalles[$rid]]);
            }
        }

        // Marcar todas las respuestas como completadas
        RespuestaPreconsulta::where('token_unico', $this->token)
            ->update([
                'completado' => true,
                'fecha_completado' => now(),
            ]);

        // Obtener la consulta asociada
        $primeraRespuesta = RespuestaPreconsulta::with('consulta')
            ->where('token_unico', $this->token)
            ->first();

        if ($primeraRespuesta && $primeraRespuesta->consulta) {
            $this->consultaCodigo = $primeraRespuesta->consulta->codigo;
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
