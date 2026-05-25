<?php

namespace App\Livewire\Admin\Consulta;

use App\Models\Cita;
use App\Models\Cuestionario;
use App\Models\RespuestaPreconsulta;
use App\Services\WhatsAppService;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Apertura extends Component
{
    use WithPagination;

    public $citasHoy;
    public $pacientesSinCita;
    public $cuestionario;
    public $cuestionarioActivo;

    // Propiedades para el modal
    public $mostrarModalIniciar = false;
    public $citaSeleccionada;
    public $motivoConsulta = '';

    // Filtros
    public $filtroEstado = 'todos';
    public $busqueda = '';

    protected $rules = [
        'motivoConsulta' => 'required|string|max:500',
    ];

    public function mount()
    {
        $this->cargarDatos();
    }

    public function cargarDatos()
    {
        $hoy = now()->startOfDay();
        $empresaId = auth()->user()->empresa_id;

        // Cuestionario genérico (sin especialidad) como fallback
        $this->cuestionarioActivo = Cuestionario::where('empresa_id', $empresaId)
            ->where('activo', true)
            ->where('tipo', 'preconsulta')
            ->whereNull('especialidad_id')
            ->first();

        // Citas de hoy
        $query = Cita::with(['paciente', 'doctor', 'sucursal'])
            ->whereDate('created_at', $hoy)
            ->where('empresa_id', $empresaId);

        if ($this->filtroEstado !== 'todos') {
            $query->where('estado', $this->filtroEstado);
        }

        if ($this->busqueda) {
            $query->whereHas('paciente', function ($q) {
                $q->where('nombres', 'like', '%' . $this->busqueda . '%')
                    ->orWhere('apellidos', 'like', '%' . $this->busqueda . '%')
                    ->orWhere('documento_identidad', 'like', '%' . $this->busqueda . '%');
            });
        }

        $this->citasHoy = $query->orderBy('created_at')->get();

        // Pacientes sin cita hoy
        $this->pacientesSinCita = \App\Models\Paciente::with(['empresa'])
            ->where('empresa_id', $empresaId)
            ->whereDoesntHave('citas', function ($query) use ($hoy) {
                $query->whereDate('created_at', $hoy);
            })
            ->when($this->busqueda, function ($query) {
                $query->where(function ($q) {
                    $q->where('nombres', 'like', '%' . $this->busqueda . '%')
                        ->orWhere('apellidos', 'like', '%' . $this->busqueda . '%')
                        ->orWhere('documento_identidad', 'like', '%' . $this->busqueda . '%');
                });
            })
            ->orderBy('nombres')
            ->get();
    }

    public function abrirModalIniciar($citaId)
    {
        $this->citaSeleccionada = Cita::find($citaId);
        $this->motivoConsulta = $this->citaSeleccionada->motivo ?? '';
        $this->mostrarModalIniciar = true;
    }

    public function cerrarModalIniciar()
    {
        $this->mostrarModalIniciar = false;
        $this->citaSeleccionada = null;
        $this->motivoConsulta = '';
    }

    public function iniciarConsulta()
    {
        $this->validate();

        DB::transaction(function () {
            $this->citaSeleccionada->update([
                'estado' => 'finalizada',
                'motivo' => $this->motivoConsulta,
            ]);

            // Buscar cuestionario: primero por especialidad, luego genérico
            $cuestionario = null;
            if ($this->citaSeleccionada->especialidad_id) {
                $cuestionario = Cuestionario::where('empresa_id', auth()->user()->empresa_id)
                    ->where('activo', true)
                    ->where('tipo', 'preconsulta')
                    ->where('especialidad_id', $this->citaSeleccionada->especialidad_id)
                    ->first();
            }
            if (!$cuestionario) {
                $cuestionario = $this->cuestionarioActivo;
            }

            if ($cuestionario) {
                $token = Str::random(32);

                foreach ($cuestionario->preguntas as $pregunta) {
                    RespuestaPreconsulta::create([
                        'paciente_id' => $this->citaSeleccionada->paciente_id,
                        'cita_id'     => $this->citaSeleccionada->id,
                        'pregunta_id' => $pregunta->id,
                        'token_unico' => $token,
                        'empresa_id'  => auth()->user()->empresa_id,
                    ]);
                }

                $this->citaSeleccionada->update([
                    'estado_preconsulta' => 'enviado',
                    'token_preconsulta'  => $token,
                ]);
            }
        });

        $this->cerrarModalIniciar();
        $this->cargarDatos();
        session()->flash('success', 'Consulta iniciada. Se ha generado el cuestionario de pre-consulta.');
    }

    public function enviarCuestionarioWhatsApp($citaId)
    {
        $cita = Cita::find($citaId);

        if (!$cita->token_preconsulta) {
            session()->flash('error', 'No hay cuestionario disponible para esta cita.');
            return;
        }

        $paciente = $cita->paciente;
        $empresa = $paciente->empresa;

        // Construir URL del cuestionario
        $url = route('preconsulta.formulario', [
            'token' => $cita->token_preconsulta,
        ]);

        // Formatear número de WhatsApp
        $telefono = $paciente->telefono;
        if (empty($telefono)) {
            session()->flash('error', 'El paciente no tiene número de teléfono registrado.');
            return;
        }

        // Agregar código de país si es necesario
        $codigoPais = $empresa->pais?->codigo_telefonico ? ltrim($empresa->pais->codigo_telefonico, '+') : '58';
        $telefonoFormateado = $this->formatWhatsAppNumber($telefono, $codigoPais);

        // Enviar mensaje
        $mensaje = "Hola *{$paciente->nombres}*,\n\n";
        $mensaje .= "Para agilizar su atención médica, le solicitamos completar el siguiente cuestionario antes de su consulta:\n\n";
        $mensaje .= "📋 *Cuestionario Pre-consulta*\n";
        $mensaje .= "🔗 {$url}\n\n";
        $mensaje .= "El cuestionario es confidencial y nos ayudará a brindarle una mejor atención.\n\n";
        $mensaje .= "Gracias por su cooperación.";

        try {
            if (! \App\Services\WhatsAppNotificationGate::allows($empresa->id, 'consulta_apertura', 'preconsulta', 'paciente')) {
                session()->flash('error', 'El cuestionario por WhatsApp esta desactivado para esta empresa.');
                return;
            }

            $service = WhatsAppService::forCompany($empresa->id);
            $resultado = $service->sendMessage($telefonoFormateado, $mensaje);

            if ($resultado) {
                $cita->update(['fecha_envio_preconsulta' => now()]);
                session()->flash('success', 'Cuestionario enviado por WhatsApp exitosamente.');
            } else {
                session()->flash('error', 'No se pudo enviar el mensaje por WhatsApp.');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Error al enviar WhatsApp: ' . $e->getMessage());
        }
    }

    private function formatWhatsAppNumber($telefono, $codigoPais)
    {
        // Limpiar el número
        $telefono = preg_replace('/[^0-9]/', '', $telefono);

        // Si no tiene código de país, agregarlo
        if (strlen($telefono) === 10) {
            $telefono = $codigoPais . $telefono;
        }

        // Formatear para WhatsApp API
        return $telefono . '@s.whatsapp.net';
    }

    public function updatedFiltroEstado()
    {
        $this->cargarDatos();
    }

    public function updatedBusqueda()
    {
        $this->cargarDatos();
    }

    public function render()
    {
        return view('livewire.admin.consulta.apertura');
    }
}
