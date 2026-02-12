<?php

namespace App\Livewire\Admin\Pacientes;

use Livewire\Component;
use App\Models\Paciente;
use App\Models\Cita;
use App\Models\Especialidad;
use App\Models\Medico;
use App\Models\Cuestionario;
use App\Models\RespuestaPreconsulta;
use App\Models\Empresa;
use App\Models\Pais;
use App\Services\WhatsAppService;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Traits\HasDynamicLayout;

class Preconsulta extends Component
{
    use HasDynamicLayout;

    public $paciente;
    public $cita;

    // Form fields
    public $especialidad_id;
    public $medico_id;
    public $motivo_consulta;

    // UI state
    public $medicos = [];
    public $sending = false;
    public $totalCitas = 0;
    public $ultimaVisita = null;

    protected $rules = [
        'especialidad_id' => 'required|exists:especialidades,id',
        'medico_id' => 'required|exists:medicos,id',
        'motivo_consulta' => 'required|string|min:5',
    ];

    public function mount(Paciente $paciente)
    {
        $this->paciente = $paciente;
        $this->totalCitas = $paciente->citas()->where('estado', Cita::ESTADO_COMPLETADA)->count();
        $this->ultimaVisita = $paciente->citas()
            ->where('estado', Cita::ESTADO_COMPLETADA)
            ->latest('fecha_inicio')
            ->first();

        // Buscar cita de hoy (pendiente o confirmada)
        $this->cita = $paciente->citas()
            ->whereDate('fecha_inicio', Carbon::today())
            ->whereIn('estado', [Cita::ESTADO_PENDIENTE, Cita::ESTADO_CONFIRMADA, Cita::ESTADO_EN_CURSO])
            ->first();

        if ($this->cita) {
            $this->especialidad_id = $this->cita->especialidad_id;
            $this->medico_id = $this->cita->medico_id;
            $this->motivo_consulta = $this->cita->motivo;
            $this->updatedEspecialidadId($this->especialidad_id);
        }
    }

    public function updatedEspecialidadId($value)
    {
        $this->medicos = Medico::whereHas('especialidades', function($q) use ($value) {
            $q->where('especialidades.id', $value);
        })->get();

        if ($this->medico_id && !$this->medicos->contains('id', $this->medico_id)) {
            $this->medico_id = null;
        }
    }



    public function iniciarCuestionario()
    {
        $this->validate();
        $this->sending = true;

        $flashType = 'success';
        $flashMessage = 'Pre-consulta iniciada correctamente.';

        try {
            DB::transaction(function () use (&$flashType, &$flashMessage) {
                // 1. Actualizar o Crear Cita
                if (!$this->cita) {
                    $this->cita = Cita::create([
                        'paciente_id' => $this->paciente->id,
                        'medico_id' => $this->medico_id,
                        'especialidad_id' => $this->especialidad_id,
                        'fecha_inicio' => Carbon::now(),
                        'fecha_fin' => Carbon::now()->addMinutes(30),
                        'motivo' => $this->motivo_consulta,
                        'estado' => Cita::ESTADO_EN_CURSO,
                        'empresa_id' => auth()->user()->empresa_id ?? 1,
                        'sucursal_id' => auth()->user()->sucursal_id ?? 1,
                        'created_by' => auth()->id(),
                    ]);
                } else {
                    $this->cita->update([
                        'medico_id' => $this->medico_id,
                        'especialidad_id' => $this->especialidad_id,
                        'motivo' => $this->motivo_consulta,
                        'estado' => Cita::ESTADO_EN_CURSO,
                    ]);
                }

                // 2. Generar Token y Cuestionario
                $cuestionario = Cuestionario::where('activo', true)->first();

                if (!$cuestionario) {
                    throw new \Exception('No hay cuestionario activo configurado.');
                }

                $existing = RespuestaPreconsulta::where('cita_id', $this->cita->id)->exists();

                if (!$existing) {
                    $token = Str::random(32); // Generamos el token AQUÍ para asegurar que sea el mismo
                    foreach ($cuestionario->preguntas as $pregunta) {
                        RespuestaPreconsulta::create([
                            'paciente_id' => $this->paciente->id,
                            'cita_id' => $this->cita->id,
                            'pregunta_id' => $pregunta->id,
                            'token_unico' => $token, // Usamos el mismo token para todas las preguntas
                            'empresa_id' => $this->cita->empresa_id,
                            'sucursal_id' => $this->cita->sucursal_id ?? 1,
                            'completado' => false,
                        ]);
                    }
                } else {
                    $token = RespuestaPreconsulta::where('cita_id', $this->cita->id)->value('token_unico');
                }

                // 3. Enviar WhatsApp
                $link = route('preconsulta.formulario', ['token' => $token]);

                // Calcular edad explícitamente para asegurar precisión
                $edad = $this->paciente->fecha_nacimiento ? Carbon::parse($this->paciente->fecha_nacimiento)->age : null;
                $esMenor = $edad !== null && $edad < 18;

                $saludo = $esMenor
                    ? "Estimado representante de *{$this->paciente->nombre_completo}*"
                    : "Hola *{$this->paciente->nombres}*";

                $mensaje = "🏥 *Pre-consulta Médica*\n\n"
                    . "{$saludo},\n\n"
                    . "Para agilizar su atención médica, le solicitamos completar el siguiente cuestionario antes de su consulta:\n\n"
                    . "📋 *Cuestionario Pre-consulta*\n"
                    . "🔗 {$link}\n\n"
                    . "El cuestionario es confidencial y nos ayudará a brindarle una mejor atención.\n\n"
                    . "⏰ Le recomendamos completarlo mientras espera.\n\n"
                    . "Gracias por su confianza. 🙏";

                $telefono = $this->paciente->telefono;

                // Si es menor, priorizar teléfono del tutor
                if ($esMenor && $this->paciente->tutor && !empty($this->paciente->tutor->telefono)) {
                    $telefono = $this->paciente->tutor->telefono;
                }
                // Si el teléfono está vacío, intentar con el tutor (fallback)
                elseif (empty($telefono) && $this->paciente->tutor) {
                    $telefono = $this->paciente->tutor->telefono;
                }

                if (!empty($telefono)) {
                        // Validar si es una cadena vacía o nula
                        if (trim($telefono) === '') {
                            Log::warning('Pre-consulta: Teléfono vacío', ['paciente_id' => $this->paciente->id]);
                        } else {
                            $telefono = $this->formatearTelefono($telefono);
                        }

                        $whatsapp = new WhatsAppService($this->cita->empresa_id);
                    $resultado = $whatsapp->sendMessage($telefono, $mensaje);

                    Log::info('Resultado envío WhatsApp:', ['resultado' => $resultado]);

                    if ($resultado) {
                        $flashType = 'success';
                        $flashMessage = 'Cuestionario enviado por WhatsApp exitosamente';
                    } else {
                        $flashType = 'warning';
                        $flashMessage = 'No se pudo enviar por WhatsApp. Copie el link: ' . $link;
                    }
                } else {
                    Log::warning('Pre-consulta: Paciente sin teléfono', [
                        'paciente_id' => $this->paciente->id,
                    ]);
                    $flashType = 'warning';
                    $flashMessage = 'Paciente sin teléfono registrado. Copie el link: ' . $link;
                }
            });

            return redirect()->route('admin.recepcion.dashboard')->with($flashType, $flashMessage);

        } catch (\Exception $e) {
            Log::error('Error en iniciarCuestionario', [
                'paciente_id' => $this->paciente->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
        } finally {
            $this->sending = false;
        }
    }

    public function render()
    {
        return view('livewire.admin.pacientes.preconsulta', [
            'especialidades' => Especialidad::orderBy('nombre')->get(),
        ])->layout($this->getLayout());
    }

    private function formatearTelefono($telefono)
    {
        // 1. Limpiar caracteres no numéricos
        $telefonoLimpio = preg_replace('/[^0-9]/', '', $telefono);

        try {
            // 2. Obtener empresa y país
            $empresa = Empresa::with('pais')->find($this->cita->empresa_id);

            if ($empresa && $empresa->pais && $empresa->pais->codigo_telefonico) {
                $codigoPais = preg_replace('/[^0-9]/', '', $empresa->pais->codigo_telefonico);

                // 3. Verificar si ya tiene el código
                if (!str_starts_with($telefonoLimpio, $codigoPais)) {
                    // Si empieza con 0, quitarlo
                    if (str_starts_with($telefonoLimpio, '0')) {
                        $telefonoLimpio = substr($telefonoLimpio, 1);
                    }
                    $telefonoLimpio = $codigoPais . $telefonoLimpio;
                }
            }
        } catch (\Exception $e) {
            Log::error('Error formateando teléfono', ['error' => $e->getMessage()]);
        }

        return $telefonoLimpio;
    }
}
