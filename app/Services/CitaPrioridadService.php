<?php

namespace App\Services;

use App\Models\Cita;
use App\Models\Consulta;
use App\Models\LogCitasSolapadas;
use App\Models\Preconsulta;
use App\Models\CitaAuditoria;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CitaPrioridadService
{
    protected array $resultado = [];
    protected bool $rollbackOnError = false;

    public function __construct(bool $rollbackOnError = true)
    {
        $this->rollbackOnError = $rollbackOnError;
        $this->resultado = [
            'success' => false,
            'cita' => null,
            'consulta' => null,
            'preconsulta' => null,
            'whatsapp_enviado' => false,
            'solapamiento' => false,
            'errores' => [],
        ];
    }

    public function crearCitaConPrioridad(array $datos): array
    {
        DB::beginTransaction();

        try {
            $esPrioridadAltaOEmergencia = in_array($datos['prioridad'] ?? 'normal', ['alta', 'emergencia']);

            $fechaHora = Carbon::parse($datos['fecha_inicio']);
            $fechaFin = isset($datos['fecha_fin']) ? Carbon::parse($datos['fecha_fin']) : $fechaHora->copy()->addMinutes(30);

            $citaExistente = $this->verificarSolapamiento(
                $datos['medico_id'],
                $fechaHora,
                $fechaFin,
                $datos['paciente_id'] ?? null
            );

            if ($citaExistente && !$esPrioridadAltaOEmergencia) {
                throw new \Exception('Ya existe una cita en ese horario para este médico. Seleccione otro horario.');
            }

            $datosCita = [
                'paciente_id' => $datos['paciente_id'],
                'medico_id' => $datos['medico_id'],
                'especialidad_id' => $datos['especialidad_id'] ?? null,
                'subespecialidad_id' => $datos['subespecialidad_id'] ?? null,
                'fecha_inicio' => $fechaHora,
                'fecha_fin' => $fechaFin,
                'estado' => $esPrioridadAltaOEmergencia ? Cita::ESTADO_SALA_ESPERA : ($datos['estado'] ?? Cita::ESTADO_PENDIENTE),
                'motivo' => $datos['motivo'] ?? null,
                'notas' => $datos['notas'] ?? null,
                'prioridad' => $datos['prioridad'] ?? 'normal',
                'sucursal_id' => $datos['sucursal_id'] ?? null,
                'empresa_id' => $datos['empresa_id'] ?? null,
                'tipo_consulta_id' => $datos['tipo_consulta_id'] ?? null,
            ];

            $cita = Cita::create($datosCita);

            if ($citaExistente && $esPrioridadAltaOEmergencia) {
                $this->registrarSolapamiento($cita, $citaExistente, $datos['prioridad']);
            }

            $preconsulta = null;
            $whatsappEnviado = false;

            if ($esPrioridadAltaOEmergencia) {
                $preconsultaData = $this->crearPreconsulta($cita, $datos);

                $whatsappEnviado = $this->enviarCuestionarioWhatsApp($cita, $preconsultaData, $datos);

                $consulta = $this->crearConsultaSalaEspera($cita, $preconsultaData, $datos);

                $this->resultado['consulta'] = $consulta;
                $this->resultado['preconsulta_data'] = $preconsultaData;
                $this->resultado['whatsapp_enviado'] = $whatsappEnviado;
            }

            $this->registrarAuditoria($cita, $datos);

            $this->resultado['success'] = true;
            $this->resultado['cita'] = $cita;
            $this->resultado['solapamiento'] = $citaExistente !== null;

            DB::commit();

        } catch (\Exception $e) {
            if ($this->rollbackOnError) {
                DB::rollBack();
            }
            $this->resultado['errores'][] = $e->getMessage();
            Log::error('Error en crearCitaConPrioridad', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        return $this->resultado;
    }

    protected function verificarSolapamiento($medicoId, Carbon $fechaHora, Carbon $fechaFin, $pacienteId = null): ?Cita
    {
        $query = Cita::where('medico_id', $medicoId)
            ->whereIn('estado', [Cita::ESTADO_PENDIENTE, Cita::ESTADO_CONFIRMADA, Cita::ESTADO_FINALIZADA])
            ->where(function ($q) use ($fechaHora, $fechaFin) {
                $q->whereBetween('fecha_inicio', [$fechaHora, $fechaFin])
                    ->orWhereBetween('fecha_fin', [$fechaHora, $fechaFin])
                    ->orWhere(function ($q2) use ($fechaHora, $fechaFin) {
                        $q2->where('fecha_inicio', '<=', $fechaHora)
                           ->where('fecha_fin', '>=', $fechaFin);
                    });
            });

        return $query->first();
    }

    protected function registrarSolapamiento(Cita $citaNueva, Cita $citaOriginal, string $prioridad): void
    {
        LogCitasSolapadas::create([
            'usuario_id' => auth()->id(),
            'cita_original_id' => $citaOriginal->id,
            'cita_nueva_id' => $citaNueva->id,
            'tipo_prioridad' => $prioridad,
            'observacion' => "Cita {$prioridad} creada solapando con cita #{$citaOriginal->id}",
            'created_at' => now(),
        ]);
    }

    protected function crearPreconsulta(Cita $cita, array $datos): array
    {
        $token = \Illuminate\Support\Str::random(32);

        $cuestionario = \App\Models\Cuestionario::where('activo', true)->first();

        if ($cuestionario) {
            // Determinar si es cita veterinaria o humana
            $esVeterinaria = $cita->mascota_id && !$cita->paciente_id;

            foreach ($cuestionario->preguntas as $pregunta) {
                $preconsultaData = [
                    'cita_id' => $cita->id,
                    'consulta_id' => null,
                    'pregunta_id' => $pregunta->id,
                    'token_unico' => $token,
                    'empresa_id' => $cita->empresa_id,
                    'sucursal_id' => $cita->sucursal_id ?? 1,
                    'completado' => false,
                ];

                // Agregar mascota_id o paciente_id según corresponda
                if ($esVeterinaria) {
                    $preconsultaData['mascota_id'] = $cita->mascota_id;
                    $preconsultaData['created_by'] = $cita->mascota_id;
                } else {
                    $preconsultaData['paciente_id'] = $cita->paciente_id;
                    $preconsultaData['created_by'] = $cita->paciente_id;
                }

                \App\Models\RespuestaPreconsulta::create($preconsultaData);
            }
        }

        return ['token' => $token, 'cuestionario' => $cuestionario ? true : false];
    }

    protected function enviarCuestionarioWhatsApp(Cita $cita, array $preconsultaData, array $datos): bool
    {
        try {
            $paciente = $cita->paciente;
            if (!$paciente) return false;

            $telefono = $paciente->telefono;

            $edad = $paciente->fecha_nacimiento ? \Carbon\Carbon::parse($paciente->fecha_nacimiento)->age : null;
            $esMenor = $edad !== null && $edad < 18;

            if ($esMenor && $paciente->tutor && !empty($paciente->tutor->telefono)) {
                $telefono = $paciente->tutor->telefono;
            } elseif (empty($telefono) && $paciente->tutor) {
                $telefono = $paciente->tutor->telefono;
            }

            if (empty($telefono) || trim($telefono) === '') {
                return false;
            }

            $telefonoFormateado = $this->formatearTelefono($telefono, $paciente->empresa_id);
            $link = route('preconsulta.formulario', ['token' => $preconsultaData['token'] ?? '']);

            $saludo = $esMenor
                ? "Estimado representante de *{$paciente->nombre_completo}*"
                : "Hola *{$paciente->nombres}*";

            $mensaje = "🏥 *Pre-consulta Médica*\n\n"
                . "{$saludo},\n\n"
                . "Para agilizar su atención médica, le solicitamos completar el siguiente cuestionario antes de su consulta:\n\n"
                . "📋 *Cuestionario Pre-consulta*\n"
                . "🔗 {$link}\n\n"
                . "El cuestionario es confidencial y nos ayudará a brindarle una mejor atención.\n\n"
                . "⏰ Le recomendamos completarlo mientras espera.\n\n"
                . "Gracias por su confianza. 🙏";

            $whatsappService = new \App\Services\WhatsAppService($cita->empresa_id);
            $resultado = $whatsappService->sendMessage($telefonoFormateado, $mensaje);

            return $resultado !== null;
        } catch (\Exception $e) {
            Log::warning('Error enviando WhatsApp de preconsulta', ['error' => $e->getMessage()]);
            return false;
        }
    }

    protected function formatearTelefono(string $telefono, ?int $empresaId = null): string
    {
        $telefonoLimpio = preg_replace('/[^0-9]/', '', $telefono);

        try {
            if ($empresaId) {
                $empresa = \App\Models\Empresa::with('pais')->find($empresaId);

                if ($empresa && $empresa->pais && $empresa->pais->codigo_telefonico) {
                    $codigoPais = preg_replace('/[^0-9]/', '', $empresa->pais->codigo_telefonico);

                    if (!str_starts_with($telefonoLimpio, $codigoPais)) {
                        if (str_starts_with($telefonoLimpio, '0')) {
                            $telefonoLimpio = substr($telefonoLimpio, 1);
                        }
                        $telefonoLimpio = $codigoPais . $telefonoLimpio;
                    }
                    return $telefonoLimpio;
                }
            }
        } catch (\Exception $e) {
            Log::error('Error formateando teléfono', ['error' => $e->getMessage()]);
        }

        if (strlen($telefonoLimpio) === 10 && str_starts_with($telefonoLimpio, '0')) {
            return '52' . $telefonoLimpio;
        } elseif (strlen($telefonoLimpio) === 9 && !str_starts_with($telefonoLimpio, '5')) {
            return '521' . $telefonoLimpio;
        }

        return '52' . ltrim(substr($telefonoLimpio, 2), '521') ?: $telefonoLimpio;
    }

    protected function crearConsultaSalaEspera(Cita $cita, array $preconsultaData, array $datos): Consulta
    {
        return Consulta::create([
            'cita_id' => $cita->id,
            'preconsulta' => true,
            'paciente_id' => $cita->paciente_id,
            'medico_id' => $cita->medico_id,
            'especialidad_id' => $cita->especialidad_id,
            'sucursal_id' => $cita->sucursal_id,
            'fecha_consulta' => $cita->fecha_inicio,
            'estado' => Consulta::ESTADO_POR_LLEGAR,
            'notas' => $datos['notas'] ?? null,
            'empresa_id' => $cita->empresa_id,
        ]);
    }

    protected function registrarAuditoria(Cita $cita, array $datos): void
    {
        CitaAuditoria::create([
            'cita_id' => $cita->id,
            'empresa_id' => $cita->empresa_id,
            'sucursal_id' => $cita->sucursal_id,
            'usuario_id' => auth()->id(),
            'tipo' => 'creacion_prioridad',
            'datos_anteriores' => null,
            'datos_nuevos' => json_encode([
                'prioridad' => $datos['prioridad'] ?? 'normal',
                'fecha_inicio' => $cita->fecha_inicio->format('Y-m-d H:i:s'),
                'fecha_fin' => $cita->fecha_fin->format('Y-m-d H:i:s'),
                'estado' => $cita->estado,
            ]),
            'nota' => "Cita creada con prioridad {$datos['prioridad']}",
            'created_at' => now(),
        ]);
    }

    public function getResultado(): array
    {
        return $this->resultado;
    }
}
