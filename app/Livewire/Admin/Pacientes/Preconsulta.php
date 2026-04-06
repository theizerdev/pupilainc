<?php

namespace App\Livewire\Admin\Pacientes;

use Livewire\Component;
use App\Models\Paciente;
use App\Models\Cita;
use App\Models\Consulta;
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
        $this->totalCitas = $paciente->citas()->where('estado', Cita::ESTADO_FINALIZADA)->count();
        $this->ultimaVisita = $paciente->citas()
            ->where('estado', Cita::ESTADO_FINALIZADA)
            ->latest('fecha_inicio')
            ->first();

        // Buscar cita de hoy (pendiente o confirmada)
        $this->cita = $paciente->citas()
            ->whereDate('fecha_inicio', Carbon::today())
            ->whereIn('estado', [Cita::ESTADO_PENDIENTE, Cita::ESTADO_CONFIRMADA])
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
        // Validar que el paciente tenga todos los datos completos
        if (!$this->pacienteCompleto()) {
            return redirect()->route('admin.pacientes.edit', $this->paciente->id)
                ->with('warning', 'Por favor completa todos los datos del paciente antes de iniciar la pre-consulta.');
        }

        $this->validate();
        $this->sending = true;

        $flashType = 'success';
        $flashMessage = 'Pre-consulta iniciada correctamente.';

        try {
            DB::transaction(function () use (&$flashType, &$flashMessage) {
                // 1. Crear Consulta
                $consulta = Consulta::create([
                    'paciente_id' => $this->paciente->id,
                    'medico_id' => $this->medico_id,
                    'especialidad_id' => $this->especialidad_id,
                    'fecha_consulta' => Carbon::now(),
                    'preconsulta' => true,
                    'motivo_consulta' => $this->motivo_consulta,
                    'estado' => Consulta::ESTADO_SALA_ESPERA,
                    'empresa_id' => auth()->user()->empresa_id ?? 1,
                    'sucursal_id' => auth()->user()->sucursal_id ?? 1,
                    'created_by' => auth()->id(),
                ]);

                // 2. Generar Token y Cuestionario
                $cuestionario = Cuestionario::where('activo', true)->first();

                if (!$cuestionario) {
                    throw new \Exception('No hay cuestionario activo configurado.');
                }

                $token = Str::random(32);
                foreach ($cuestionario->preguntas as $pregunta) {
                    RespuestaPreconsulta::create([
                        'paciente_id' => $this->paciente->id,
                        'cita_id' => null,
                        'consulta_id' => $consulta->id,
                        'pregunta_id' => $pregunta->id,
                        'token_unico' => $token,
                        'empresa_id' => $consulta->empresa_id,
                        'sucursal_id' => $consulta->sucursal_id ?? 1,
                        'completado' => false,
                        'created_by' => $this->paciente->id,
                    ]);
                }

                // 3. Enviar WhatsApp
                $link = route('preconsulta.formulario', ['token' => $token]);

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

                if ($esMenor && $this->paciente->tutor && !empty($this->paciente->tutor->telefono)) {
                    $telefono = $this->paciente->tutor->telefono;
                } elseif (empty($telefono) && $this->paciente->tutor) {
                    $telefono = $this->paciente->tutor->telefono;
                }

                if (!empty($telefono) && trim($telefono) !== '') {
                    $telefono = $this->formatearTelefono($telefono);
                    $whatsapp = new WhatsAppService($consulta->empresa_id);
                    $resultado = $whatsapp->sendMessage($telefono, $mensaje);

                    if ($resultado) {
                        $flashType = 'success';
                        $flashMessage = 'Cuestionario enviado por WhatsApp exitosamente';
                    } else {
                        $flashType = 'warning';
                        $flashMessage = 'No se pudo enviar por WhatsApp. Copie el link: ' . $link;
                    }
                } else {
                    $flashType = 'warning';
                    $flashMessage = 'Paciente sin teléfono registrado. Copie el link: ' . $link;
                }
            });

            return redirect()->route('admin.recepcion.dashboard')->with($flashType, $flashMessage);

        } catch (\Exception $e) {
            dd($e);
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

    private function pacienteCompleto(): bool
    {
        $paciente = Paciente::with('tutor')->find($this->paciente->id);
        $required = ['nombres', 'apellidos', 'documento_identidad', 'fecha_nacimiento', 'telefono', 'genero', 'direccion'];
        
        foreach ($required as $field) {
            $value = $paciente->$field;
            if (is_string($value)) {
                $value = trim($value);
            }
            if ($value === null || $value === '') {
                return false;
            }
        }

        $edad = $paciente->fecha_nacimiento ? Carbon::parse($paciente->fecha_nacimiento)->age : null;
        if ($edad !== null && $edad < 18) {
            $tutor = $paciente->tutor;
            if (!$tutor) {
                return false;
            }
            $tutorRequired = ['nombres', 'apellidos', 'documento_identidad', 'parentesco', 'telefono'];
            foreach ($tutorRequired as $field) {
                $value = $tutor->$field ?? null;
                if (is_string($value)) {
                    $value = trim($value);
                }
                if ($value === null || $value === '') {
                    return false;
                }
            }
        }

        return true;
    }

    private function obtenerDatosFaltantes(): array
    {
        $paciente = Paciente::with('tutor')->find($this->paciente->id);
        $faltantes = [];
        $required = ['nombres', 'apellidos', 'documento_identidad', 'fecha_nacimiento', 'telefono', 'genero', 'direccion'];
        
        foreach ($required as $field) {
            $value = $paciente->$field ?? null;
            if ($value === null || (is_string($value) && trim($value) === '')) {
                $faltantes[] = $field;
            }
        }

        $edad = $paciente->fecha_nacimiento ? Carbon::parse($paciente->fecha_nacimiento)->age : null;
        if ($edad !== null && $edad < 18) {
            $tutor = $paciente->tutor;
            if (!$tutor) {
                $faltantes[] = 'tutor';
            } else {
                $tutorRequired = ['nombres', 'apellidos', 'documento_identidad', 'parentesco', 'telefono'];
                foreach ($tutorRequired as $field) {
                    $value = $tutor->$field ?? null;
                    if ($value === null || (is_string($value) && trim($value) === '')) {
                        $faltantes[] = "tutor_$field";
                    }
                }
            }
        }

        return $faltantes;
    }

    public function render()
    {
        return view('livewire.admin.pacientes.preconsulta', [
            'especialidades' => Especialidad::orderBy('nombre')->get(),
            'edadFormateada' => $this->edadFormateada,
            'datosFaltantes' => $this->obtenerDatosFaltantes(),
        ])->layout($this->getLayout());
    }

    private function formatearTelefono($telefono)
    {
        $telefonoLimpio = preg_replace('/[^0-9]/', '', $telefono);

        try {
            $empresa = Empresa::with('pais')->find($this->paciente->empresa_id);

            if ($empresa && $empresa->pais && $empresa->pais->codigo_telefonico) {
                $codigoPais = preg_replace('/[^0-9]/', '', $empresa->pais->codigo_telefonico);

                if (!str_starts_with($telefonoLimpio, $codigoPais)) {
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

    public function getEdadFormateadaProperty()
    {
        if (!$this->paciente || !$this->paciente->fecha_nacimiento) {
            return null;
        }

        $nacimiento = Carbon::parse($this->paciente->fecha_nacimiento);
        $años = $nacimiento->age;
        if ($años < 0) $años = 0;

        if ($años < 1) {
            $meses = (int) $nacimiento->diffInMonths(now());
            return $meses === 1 ? '1 mes' : "{$meses} meses";
        }

        if ($años < 2) {
            $meses = (int) $nacimiento->diffInMonths(now()) % 12;
            return "1 año" . ($meses > 0 ? " y {$meses} meses" : "");
        }

        return "{$años} años";
    }
}
