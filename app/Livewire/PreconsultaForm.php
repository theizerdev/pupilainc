<?php

namespace App\Livewire;

use App\Models\RespuestaPreconsulta;
use Livewire\Component;
use Carbon\Carbon;

class PreconsultaForm extends Component
{
    public $token;
    public $respuestas = [];
    public $detalles = [];
    public $respuestasPreconsulta;
    public $cuestionario;
    public $paciente;
    public $empresa;
    public $paso = 1;
    public $totalPasos = 1;
    public $consultaCodigo = null;

    // Datos pre-llenados desde CRM
    public $nombre_paciente;
    public $edad_paciente;
    public $fecha_hora_cita;
    public $tipo_servicio;
    public $nombre_medico;
    public $fecha_ultima_consulta;
    
    // Tipo de formulario (primera_vez o subsecuente)
    public $tipoFormulario = 'primera_vez';
    
    // Archivo subido para estudios
    public $archivoEstudios;
    public $archivosSubidos = [];  // ['pregunta_id' => 'ruta_archivo']

    public function mount($token)
    {
        $this->token = $token;

        // Buscar respuestas por token
        $this->respuestasPreconsulta = RespuestaPreconsulta::with(['pregunta', 'paciente', 'empresa', 'cita.medico', 'cita.tipoConsulta', 'cita.especialidad'])
            ->where('token_unico', $token)
            ->get();

        if ($this->respuestasPreconsulta->isEmpty()) {
            abort(404, 'Cuestionario no encontrado.');
        }

        $this->paciente = $this->respuestasPreconsulta->first()->paciente;
        $this->empresa = $this->respuestasPreconsulta->first()->empresa;
        $this->cuestionario = $this->respuestasPreconsulta->first()->pregunta->cuestionario;

        // Cargar datos pre-llenados desde CRM
        $this->nombre_paciente = $this->paciente->nombre_completo;
        
        // Calcular edad
        if ($this->paciente->fecha_nacimiento) {
            $this->edad_paciente = Carbon::parse($this->paciente->fecha_nacimiento)->age;
        } else {
            $this->edad_paciente = null;
        }

        // Cargar datos de la cita si existe
        $cita = $this->respuestasPreconsulta->first()->cita;
        if ($cita) {
            $this->fecha_hora_cita = $cita->fecha_inicio->format('d/m/Y H:i');
            
            // Tipo de servicio (prioridad: tipoConsulta > especialidad)
            if ($cita->tipoConsulta) {
                $this->tipo_servicio = $cita->tipoConsulta->nombre;
            } elseif ($cita->especialidad) {
                $this->tipo_servicio = $cita->especialidad->nombre;
            } else {
                $this->tipo_servicio = 'N/A';
            }
            
            // Nombre del médico
            $this->nombre_medico = $cita->medico ? 'Dr(a). ' . $cita->medico->nombre_completo : 'N/A';
            
            // Fecha de última consulta (para pacientes subsecuentes)
            $ultimaConsulta = \App\Models\Consulta::where('paciente_id', $this->paciente->id)
                ->where('id', '!=', $cita->consulta_id)
                ->whereNotNull('fecha_consulta')
                ->orderBy('fecha_consulta', 'desc')
                ->first();
            
            if ($ultimaConsulta) {
                $this->fecha_ultima_consulta = $ultimaConsulta->fecha_consulta->format('d/m/Y');
            } else {
                // Si no hay consultas previas, buscar citas finalizadas
                $ultimaCitaFinalizada = \App\Models\Cita::where('paciente_id', $this->paciente->id)
                    ->where('id', '!=', $cita->id)
                    ->where('estado', \App\Models\Cita::ESTADO_FINALIZADA)
                    ->orderBy('fecha_inicio', 'desc')
                    ->first();
                
                if ($ultimaCitaFinalizada) {
                    $this->fecha_ultima_consulta = $ultimaCitaFinalizada->fecha_inicio->format('d/m/Y');
                } else {
                    $this->fecha_ultima_consulta = 'Primera visita';
                }
            }
        } else {
            $this->fecha_hora_cita = 'No disponible';
            $this->tipo_servicio = 'N/A';
            $this->nombre_medico = 'N/A';
            $this->fecha_ultima_consulta = 'N/A';
        }

        // Determinar tipo de formulario basado en el cuestionario
        if ($this->cuestionario && $this->cuestionario->tipo === 'subsecuente') {
            $this->tipoFormulario = 'subsecuente';
        } else {
            $this->tipoFormulario = 'primera_vez';
        }

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
            } elseif ($respuesta->pregunta->tipo === 'tiempo_evolucion') {
                // Separar cantidad y unidad para tiempo de evolución
                $detalle = $respuesta->detalle ?? '';
                if (preg_match('/^(\d+)\s*(Días|Semanas|Meses|Años)$/i', $detalle, $matches)) {
                    $this->respuestas[$respuesta->id . '_cantidad'] = $matches[1];
                    $this->respuestas[$respuesta->id . '_unidad'] = ucfirst(strtolower($matches[2]));
                } else {
                    $this->respuestas[$respuesta->id . '_cantidad'] = '';
                    $this->respuestas[$respuesta->id . '_unidad'] = '';
                }
            } elseif ($respuesta->pregunta->tipo === 'si_no_factura') {
                // Separar RFC y razón social
                $detalle = $respuesta->detalle ?? '';
                if ($detalle && strpos($detalle, '|') !== false) {
                    list($rfc, $razon) = explode('|', $detalle, 2);
                    $this->detalles[$respuesta->id . '_rfc'] = $rfc;
                    $this->detalles[$respuesta->id . '_razon_social'] = $razon;
                }
                $this->respuestas[$respuesta->id] = $respuesta->respuesta ?? '';
            } else {
                $this->respuestas[$respuesta->id] = $respuesta->respuesta ?? '';
            }

            // Inicializar detalles si existen (para tipos que lo necesitan)
            if (in_array($respuesta->pregunta->tipo, ['si_no_detalle', 'si_no_factura'])) {
                if ($respuesta->pregunta->tipo !== 'si_no_factura') {
                    $this->detalles[$respuesta->id] = $respuesta->detalle ?? '';
                }
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
                } elseif ($respuesta->pregunta->tipo === 'tiempo_evolucion') {
                    $rules["respuestas.{$respuesta->id}_cantidad"] = 'required|numeric|min:1';
                    $rules["respuestas.{$respuesta->id}_unidad"] = 'required|in:Días,Semanas,Meses,Años';
                } elseif ($respuesta->pregunta->tipo === 'si_no_detalle') {
                    $rules["respuestas.{$respuesta->id}"] = 'required|in:Sí,No';
                    // Si es "Sí", el detalle es obligatorio
                    if (isset($this->respuestas[$respuesta->id]) && $this->respuestas[$respuesta->id] === 'Sí') {
                        $rules["detalles.{$respuesta->id}"] = 'required|string|min:3';
                    }
                } elseif ($respuesta->pregunta->tipo === 'si_no_factura') {
                    $rules["respuestas.{$respuesta->id}"] = 'required|in:Sí,No';
                    // Si es "Sí", RFC y razón social son obligatorios
                    if (isset($this->respuestas[$respuesta->id]) && $this->respuestas[$respuesta->id] === 'Sí') {
                        $rules["detalles.{$respuesta->id}_rfc"] = 'required|string|min:12|max:18';
                        $rules["detalles.{$respuesta->id}_razon_social"] = 'required|string|min:3';
                    }
                } elseif ($respuesta->pregunta->tipo === 'estudios_subsecuente') {
                    // Campo de selección única con opción de subir archivo
                    $rules["respuestas.{$respuesta->id}"] = 'required|in:No,Sí, los traigo el día de la cita,Sí, los adjunto aquí ahora';
                    // Si selecciona "Sí, los adjunto aquí ahora", validar que haya archivo
                    if (isset($this->respuestas[$respuesta->id]) && $this->respuestas[$respuesta->id] === 'Sí, los adjunto aquí ahora') {
                        // La validación de archivo se hace en el frontend
                    }
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
        } elseif (is_string($key) && preg_match('/^respuestas\.(\d+)(?:_(.+))?$/', $key, $m)) {
            $id = (int) $m[1];
        }

        if (!$id) {
            return;
        }

        $respuesta = RespuestaPreconsulta::with('pregunta')->find($id);

        if ($respuesta) {
            // Manejar tipos especiales
            if ($respuesta->pregunta->tipo === 'multiple') {
                if (!is_array($value)) {
                    $value = $value ? [$value] : [];
                }
                $respuesta->update(['respuesta_multiple' => $value]);
            } elseif ($respuesta->pregunta->tipo === 'tiempo_evolucion') {
                // Combinar cantidad y unidad en el campo detalle
                $cantidad = $this->respuestas[$id . '_cantidad'] ?? '';
                $unidad = $this->respuestas[$id . '_unidad'] ?? '';
                
                if ($cantidad && $unidad) {
                    $valorCompleto = "{$cantidad} {$unidad}";
                    $respuesta->update([
                        'respuesta' => $valorCompleto,
                        'detalle' => $valorCompleto
                    ]);
                }
            } elseif ($respuesta->pregunta->tipo === 'si_no_detalle') {
                // Guardar respuesta principal
                if (is_array($value)) {
                    $value = implode(', ', $value);
                }
                $respuesta->update(['respuesta' => $value]);
                
                // Si cambió a "No", limpiar el detalle
                if ($value === 'No') {
                    $respuesta->update(['detalle' => null]);
                    $this->detalles[$id] = null;
                }
            } elseif ($respuesta->pregunta->tipo === 'si_no_factura') {
                // Guardar respuesta principal
                $respuesta->update(['respuesta' => $value]);
                
                // Si cambió a "No", limpiar los detalles
                if ($value === 'No') {
                    $respuesta->update(['detalle' => null]);
                    $this->detalles[$id . '_rfc'] = null;
                    $this->detalles[$id . '_razon_social'] = null;
                }
            } elseif ($respuesta->pregunta->tipo === 'estudios_subsecuente') {
                // Guardar respuesta para estudios subsecuentes
                if (is_array($value)) {
                    $value = implode(', ', $value);
                }
                $respuesta->update(['respuesta' => $value]);
                
                // Si cambia a una opción que no requiere archivo, limpiar cualquier archivo subido
                if ($value !== 'Sí, los adjunto aquí ahora') {
                    $respuesta->update(['detalle' => null]);
                }
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
        // Extraer ID del key
        if (preg_match('/^detalles\.(\d+)(?:_(.+))?$/', $key, $m)) {
            $id = (int) $m[1];
            $campo = $m[2] ?? null;
            
            $respuesta = RespuestaPreconsulta::find($id);
            
            if ($respuesta) {
                if ($respuesta->pregunta->tipo === 'si_no_factura') {
                    // Combinar RFC y razón social con separador
                    $rfc = $this->detalles[$id . '_rfc'] ?? '';
                    $razon = $this->detalles[$id . '_razon_social'] ?? '';
                    
                    if ($rfc && $razon) {
                        $respuesta->update(['detalle' => "{$rfc}|{$razon}"]);
                    }
                } else {
                    // Guardar detalle normal
                    $respuesta->update(['detalle' => $value]);
                }
            }
        }
    }

    /**
     * Subir archivo de estudios para pacientes subsecuentes
     */
    public function subirArchivoEstudios($preguntaId)
    {
        $this->validate([
            'archivoEstudios' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240', // 10MB max
        ]);

        $respuesta = RespuestaPreconsulta::find($preguntaId);
        
        if (!$respuesta) {
            return;
        }

        // Guardar archivo
        $archivo = $this->archivoEstudios;
        $nombreArchivo = 'estudios_' . $preguntaId . '_' . time() . '.' . $archivo->getClientOriginalExtension();
        $ruta = $archivo->storeAs('public/estudios_pacientes', $nombreArchivo);

        // Guardar ruta en la respuesta
        $respuesta->update(['detalle' => $ruta]);
        
        // Almacenar en array local para mostrar en vista
        $this->archivosSubidos[$preguntaId] = $nombreArchivo;

        session()->flash('success', 'Archivo subido correctamente.');
        
        // Limpiar el input de archivo
        $this->reset('archivoEstudios');
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
            
            // Manejar diferentes tipos de preguntas
            if ($respuestaRow->pregunta->tipo === 'multiple') {
                if (array_key_exists($rid, $this->respuestas)) {
                    $val = $this->respuestas[$rid];
                    if (!is_array($val)) {
                        $val = $val ? [$val] : [];
                    }
                    $respuestaRow->update(['respuesta_multiple' => $val]);
                }
            } elseif ($respuestaRow->pregunta->tipo === 'tiempo_evolucion') {
                $cantidad = $this->respuestas[$rid . '_cantidad'] ?? '';
                $unidad = $this->respuestas[$rid . '_unidad'] ?? '';
                
                if ($cantidad && $unidad) {
                    $valorCompleto = "{$cantidad} {$unidad}";
                    $respuestaRow->update([
                        'respuesta' => $valorCompleto,
                        'detalle' => $valorCompleto
                    ]);
                }
            } elseif ($respuestaRow->pregunta->tipo === 'si_no_factura') {
                if (array_key_exists($rid, $this->respuestas)) {
                    $val = $this->respuestas[$rid];
                    if (is_array($val)) {
                        $val = implode(', ', $val);
                    }
                    $respuestaRow->update(['respuesta' => $val]);
                    
                    // Guardar RFC y razón social combinados
                    if ($val === 'Sí') {
                        $rfc = $this->detalles[$rid . '_rfc'] ?? '';
                        $razon = $this->detalles[$rid . '_razon_social'] ?? '';
                        
                        if ($rfc && $razon) {
                            $respuestaRow->update(['detalle' => "{$rfc}|{$razon}"]);
                        }
                    }
                }
            } else {
                if (array_key_exists($rid, $this->respuestas)) {
                    $val = $this->respuestas[$rid];
                    if (is_array($val)) {
                        $val = implode(', ', $val);
                    }
                    $respuestaRow->update(['respuesta' => $val]);
                }
            }

            // Guardar detalles para si_no_detalle
            if ($respuestaRow->pregunta->tipo === 'si_no_detalle' && array_key_exists($rid, $this->detalles)) {
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
