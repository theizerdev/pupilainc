<?php

namespace App\Http\Controllers;

use App\Models\CitaConfirmacion;
use App\Services\CitaConfirmationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CitaConfirmationController extends Controller
{
    protected CitaConfirmationService $confirmationService;

    public function __construct(CitaConfirmationService $confirmationService)
    {
        $this->confirmationService = $confirmationService;
    }

    /**
     * Confirma una cita mediante enlace web
     */
    public function confirmar(Request $request, string $token)
    {
        try {
            $resultado = $this->confirmationService->procesarRespuesta($token, 'si');
            
            if ($resultado) {
                return view('citas.confirmacion-exitosa', [
                    'mensaje' => '¡Su cita ha sido confirmada exitosamente!'
                ]);
            } else {
                return view('citas.confirmacion-error', [
                    'mensaje' => 'La confirmación ya expiró o no es válida.'
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error confirmando cita web', [
                'token' => $token,
                'error' => $e->getMessage()
            ]);
            
            return view('citas.confirmacion-error', [
                'mensaje' => 'Ocurrió un error al procesar su confirmación.'
            ]);
        }
    }

    /**
     * Cancela una cita mediante enlace web
     */
    public function cancelar(Request $request, string $token)
    {
        try {
            $resultado = $this->confirmationService->procesarRespuesta($token, 'no');
            
            if ($resultado) {
                return view('citas.cancelacion-exitosa', [
                    'mensaje' => 'Su cita ha sido cancelada. Puede reprogramar llamando a nuestra central.'
                ]);
            } else {
                return view('citas.confirmacion-error', [
                    'mensaje' => 'La solicitud ya expiró o no es válida.'
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error cancelando cita web', [
                'token' => $token,
                'error' => $e->getMessage()
            ]);
            
            return view('citas.confirmacion-error', [
                'mensaje' => 'Ocurrió un error al procesar su solicitud.'
            ]);
        }
    }

    /**
     * Endpoint para recibir respuestas por WhatsApp
     */
    public function webhookWhatsapp(Request $request)
    {
        try {
            $data = $request->all();
            
            // Validar que es un mensaje de WhatsApp
            if (!isset($data['messages']) || !is_array($data['messages'])) {
                return response()->json(['status' => 'ignored']);
            }

            foreach ($data['messages'] as $message) {
                $this->procesarMensajeWhatsApp($message);
            }

            return response()->json(['status' => 'processed']);
        } catch (\Exception $e) {
            Log::error('Error procesando webhook WhatsApp', [
                'data' => $request->all(),
                'error' => $e->getMessage()
            ]);
            
            return response()->json(['status' => 'error'], 500);
        }
    }

    /**
     * Procesa mensajes individuales de WhatsApp
     */
    protected function procesarMensajeWhatsApp(array $message): void
    {
        $telefono = $message['from'] ?? null;
        $texto = trim(strtolower($message['text']['body'] ?? ''));

        if (!$telefono || !$texto) {
            return;
        }

        // Buscar confirmación pendiente para este número
        $confirmacion = CitaConfirmacion::where('destinatario', $telefono)
                                       ->where('estado', CitaConfirmacion::ESTADO_PENDIENTE)
                                       ->latest()
                                       ->first();

        if ($confirmacion) {
            $this->confirmationService->procesarRespuesta($confirmacion->token_confirmacion, $texto);
        }
    }

    /**
     * Reintenta envío de confirmaciones pendientes
     */
    public function reintentarPendientes()
    {
        $confirmaciones = CitaConfirmacion::pendientes()
                                         ->where('intentos', '<', 3)
                                         ->where('fecha_envio', '<', now()->subHours(2))
                                         ->get();

        $reintentados = 0;
        foreach ($confirmaciones as $confirmacion) {
            if ($this->confirmationService->reintentarConfirmacion($confirmacion)) {
                $reintentados++;
            }
        }

        return response()->json([
            'message' => "Se reintentaron {$reintentados} confirmaciones",
            'total_procesadas' => $confirmaciones->count()
        ]);
    }

    /**
     * Marca confirmaciones expiradas
     */
    public function procesarExpiradas()
    {
        $expiradas = CitaConfirmacion::expiradas()->get();
        
        foreach ($expiradas as $confirmacion) {
            $confirmacion->update(['estado' => CitaConfirmacion::ESTADO_SIN_RESPUESTA]);
            
            // Opcional: cancelar la cita asociada
            $cita = $confirmacion->cita;
            if ($cita && $cita->estado === Cita::ESTADO_PENDIENTE) {
                $cita->update(['estado' => Cita::ESTADO_CANCELADA]);
            }
        }

        return response()->json([
            'message' => "Se procesaron {$expiradas->count()} confirmaciones expiradas"
        ]);
    }

    /**
     * Obtiene estadísticas de confirmaciones
     */
    public function estadisticas(Request $request)
    {
        $dias = $request->get('dias', 30);
        $estadisticas = $this->confirmationService->obtenerEstadisticas($dias);

        return response()->json($estadisticas);
    }
}