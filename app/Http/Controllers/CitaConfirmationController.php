<?php

namespace App\Http\Controllers;

use App\Models\CitaConfirmacion;
use App\Services\CitaConfirmationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CitaConfirmationController extends Controller
{
    protected CitaConfirmationService $confirmationService;

    public function __construct(CitaConfirmationService $confirmationService)
    {
        $this->confirmationService = $confirmationService;
    }

    /**
     * Webhook para procesar respuestas de WhatsApp
     */
    public function processWhatsAppResponse(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'phone' => 'required|string',
                'message' => 'required|string',
                'timestamp' => 'nullable|date',
                'messageId' => 'nullable|string',
                'isFromMe' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Datos inválidos',
                    'details' => $validator->errors()
                ], 400);
            }

            $telefono = $request->input('phone');
            $mensaje = trim($request->input('message'));
            $isFromMe = $request->input('isFromMe', false);
            $messageId = $request->input('messageId');

            Log::info('Procesando respuesta WhatsApp', [
                'telefono' => $telefono,
                'mensaje' => $mensaje,
                'isFromMe' => $isFromMe,
                'messageId' => $messageId,
                'request_data' => $request->all()
            ]);

            // IGNORAR MENSAJES ENVIADOS POR NOSOTROS MISMOS
            if ($isFromMe) {
                Log::info('Ignorando mensaje enviado por el sistema', [
                    'telefono' => $telefono,
                    'mensaje' => $mensaje
                ]);
                return response()->json([
                    'success' => true,
                    'message' => 'Mensaje enviado por el sistema - ignorado'
                ]);
            }

            // IGNORAR MENSAJES QUE CONTIENEN ENLACES DE CONFIRMACIÓN (MENSAJES SALIENTES)
            if (str_contains($mensaje, 'CONFIRMAR CITA') || str_contains($mensaje, 'CANCELAR CITA') || str_contains($mensaje, route('citas.confirmar', [], false))) {
                Log::info('Ignorando mensaje saliente con enlaces de confirmación', [
                    'telefono' => $telefono,
                    'mensaje' => substr($mensaje, 0, 100) . '...'
                ]);
                return response()->json([
                    'success' => true,
                    'message' => 'Mensaje saliente con enlaces - ignorado'
                ]);
            }

            // Buscar confirmación pendiente para este número
            $confirmacion = CitaConfirmacion::where('destinatario', $telefono)
                                           ->where('estado', CitaConfirmacion::ESTADO_PENDIENTE)
                                           ->latest()
                                           ->first();

            if (!$confirmacion) {
                Log::info('No se encontró confirmación pendiente', ['telefono' => $telefono]);
                return response()->json([
                    'success' => true,
                    'message' => 'No hay confirmaciones pendientes para este número'
                ]);
            }

            // Procesar la respuesta
            $procesado = $this->confirmationService->procesarRespuesta(
                $confirmacion->token_confirmacion,
                $mensaje
            );

            if ($procesado) {
                return response()->json([
                    'success' => true,
                    'message' => 'Respuesta procesada correctamente',
                    'cita_id' => $confirmacion->cita_id,
                    'estado' => $confirmacion->estado
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'error' => 'No se pudo procesar la respuesta'
                ], 400);
            }

        } catch (\Exception $e) {
            Log::error('Error procesando respuesta WhatsApp', [
                'error' => $e->getMessage(),
                'telefono' => $request->input('phone', 'unknown')
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Confirmar cita vía enlace (GET)
     */
    public function confirmar(Request $request): JsonResponse
    {
        try {
            $token = $request->query('token');

            if (!$token) {
                return response()->json([
                    'success' => false,
                    'error' => 'Token requerido'
                ], 400);
            }

            $procesado = $this->confirmationService->procesarRespuesta($token, 'SI');

            if ($procesado) {
                return response()->json([
                    'success' => true,
                    'message' => 'Cita confirmada exitosamente'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'error' => 'No se pudo confirmar la cita'
                ], 400);
            }

        } catch (\Exception $e) {
            Log::error('Error confirmando cita', [
                'error' => $e->getMessage(),
                'token' => $request->query('token')
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Cancelar cita vía enlace (GET)
     */
    public function cancelar(Request $request): JsonResponse
    {
        try {
            $token = $request->query('token');

            if (!$token) {
                return response()->json([
                    'success' => false,
                    'error' => 'Token requerido'
                ], 400);
            }

            $procesado = $this->confirmationService->procesarRespuesta($token, 'NO');

            if ($procesado) {
                return response()->json([
                    'success' => true,
                    'message' => 'Cita cancelada exitosamente'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'error' => 'No se pudo cancelar la cita'
                ], 400);
            }

        } catch (\Exception $e) {
            Log::error('Error cancelando cita', [
                'error' => $e->getMessage(),
                'token' => $request->query('token')
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Obtener estadísticas de confirmaciones
     */
    public function estadisticas(Request $request): JsonResponse
    {
        try {
            $dias = $request->query('dias', 30);
            $estadisticas = $this->confirmationService->obtenerEstadisticas($dias);

            return response()->json([
                'success' => true,
                'data' => $estadisticas
            ]);

        } catch (\Exception $e) {
            Log::error('Error obteniendo estadísticas', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Reintentar envío de confirmaciones pendientes
     */
    public function reintentarPendientes(Request $request): JsonResponse
    {
        try {
            $confirmaciones = CitaConfirmacion::pendientes()
                                             ->where('intentos', '<', 3)
                                             ->get();

            $reintentadas = 0;
            $fallidas = 0;

            foreach ($confirmaciones as $confirmacion) {
                if ($this->confirmationService->reintentarConfirmacion($confirmacion)) {
                    $reintentadas++;
                } else {
                    $fallidas++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Reintentos procesados: {$reintentadas} exitosos, {$fallidas} fallidos",
                'reintentadas' => $reintentadas,
                'fallidas' => $fallidas
            ]);

        } catch (\Exception $e) {
            Log::error('Error reintentando confirmaciones', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Error interno del servidor'
            ], 500);
        }
    }
}
