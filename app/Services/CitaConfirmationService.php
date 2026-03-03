<?php

namespace App\Services;

use App\Models\Cita;
use App\Models\CitaConfirmacion;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

class CitaConfirmationService
{
    protected WhatsAppService $whatsApp;
    protected CitaConfirmationBotonesService $botonesService;
    protected ?int $empresaId;
    protected ?string $codigoPais = null;

    public function __construct()
    {
        // Dejar que WhatsAppService resuelva automáticamente la empresa del usuario
        $this->whatsApp = new WhatsAppService();
        $this->botonesService = new CitaConfirmationBotonesService($this->whatsApp);
        // Obtener el companyId que se resolvió automáticamente
        $this->empresaId = $this->whatsApp->getCompanyId();
    }

    /**
     * Inicia el proceso de confirmación para una cita
     */
    public function iniciarConfirmacion(Cita $cita): CitaConfirmacion
    {
        // Cancelar confirmaciones anteriores
        $this->cancelarConfirmacionesPendientes($cita);

        // Crear nueva confirmación
        $confirmacion = CitaConfirmacion::create([
            'cita_id' => $cita->id,
            'metodo' => CitaConfirmacion::METODO_WHATSAPP,
            'destinatario' => $this->obtenerTelefonoDestino($cita),
            'mensaje_enviado' => $this->generarMensajeConfirmacion($cita),
            'token_confirmacion' => $this->generarToken(),
            'empresa_id' => $cita->empresa_id,
            'sucursal_id' => $cita->sucursal_id,
            'created_by' => auth()->id(),
            'fecha_envio' => now()
        ]);

        // Enviar notificación inicial
        $this->enviarConfirmacion($confirmacion);

        return $confirmacion;
    }

    /**
     * Procesa la respuesta del paciente (texto o botón interactivo)
     */
    public function procesarRespuesta(string $token, string $respuesta): bool
    {
        $confirmacion = CitaConfirmacion::where('token_confirmacion', $token)
                                       ->where('estado', CitaConfirmacion::ESTADO_PENDIENTE)
                                       ->first();

        if (!$confirmacion) {
            Log::warning('Token de confirmación inválido', ['token' => $token]);
            return false;
        }

        if ($confirmacion->estaExpirada()) {
            $confirmacion->update(['estado' => CitaConfirmacion::ESTADO_SIN_RESPUESTA]);
            Log::info('Confirmación expirada', ['cita_id' => $confirmacion->cita_id]);
            return false;
        }

        $respuestaNormalizada = strtolower(trim($respuesta));

        // Procesar respuestas de botones interactivos o texto
        if (in_array($respuestaNormalizada, ['si', 'sí', 'yes', 'ok', 'confirmo']) || 
            str_contains($respuesta, 'confirmar_')) {
            $this->confirmarCita($confirmacion, $respuesta);
            return true;
        } elseif (in_array($respuestaNormalizada, ['no', 'cancelar', 'reprogramar']) || 
                  str_contains($respuesta, 'cancelar_')) {
            $this->rechazarCita($confirmacion, $respuesta);
            return true;
        }

        // Respuesta ambigua - solicitar clarificación
        $this->solicitarClarificacion($confirmacion);
        return false;
    }

    /**
     * Reintenta enviar confirmación si es necesario
     */
    public function reintentarConfirmacion(CitaConfirmacion $confirmacion): bool
    {
        if (!$confirmacion->puedeReintentar()) {
            return false;
        }

        $confirmacion->incrementarIntento();
        return $this->enviarConfirmacion($confirmacion);
    }

    /**
     * Confirma la cita y actualiza estados
     */
    protected function confirmarCita(CitaConfirmacion $confirmacion, string $respuesta): void
    {
        $confirmacion->marcarComoConfirmada($respuesta);
        
        // Actualizar estado de la cita y crear consulta "por llegar"
        $cita = $confirmacion->cita;
        if ($cita && $cita->estado === Cita::ESTADO_PENDIENTE) {
            $cita->cambiarEstado(Cita::ESTADO_CONFIRMADA);
        }

        Log::info('Cita confirmada exitosamente', [
            'cita_id' => $cita->id,
            'paciente' => $cita->paciente->nombre_completo
        ]);
    }

    /**
     * Rechaza la cita y maneja consecuencias
     */
    protected function rechazarCita(CitaConfirmacion $confirmacion, string $respuesta): void
    {
        $confirmacion->marcarComoRechazada($respuesta);
        
        $cita = $confirmacion->cita;
        if ($cita) {
            $cita->update(['estado' => Cita::ESTADO_CANCELADA]);
        }

        Log::info('Cita rechazada/cancelada', [
            'cita_id' => $cita->id,
            'respuesta' => $respuesta
        ]);
    }

    /**
     * Envía el mensaje de confirmación
     */
    protected function enviarConfirmacion(CitaConfirmacion $confirmacion): bool
    {
        Log::info('Iniciando envío de confirmación', [
            'confirmacion_id' => $confirmacion->id,
            'metodo' => $confirmacion->metodo,
            'destinatario' => $confirmacion->destinatario
        ]);
    
        try {
            // Primero intentar enviar con botones interactivos
            if ($confirmacion->metodo === CitaConfirmacion::METODO_WHATSAPP) {
                $mensajeConBotones = $this->construirMensajeConBotonesInteractivos($confirmacion);
               
                Log::info('Intentando enviar mensaje interactivo', [
                    'confirmacion_id' => $confirmacion->id,
                    'destinatario' => $confirmacion->destinatario
                ]);
                
                if ($this->enviarMensajeInteractivo($confirmacion->destinatario, $mensajeConBotones)) {
                    Log::info('Mensaje interactivo enviado exitosamente', [
                        'confirmacion_id' => $confirmacion->id
                    ]);
                    return true;
                }
                
                // Si falla, intentar con el servicio de botones alternativo
                Log::info('Botones interactivos no disponibles, intentando servicio alternativo', [
                    'confirmacion_id' => $confirmacion->id
                ]);
                
                if ($this->botonesService->enviarConfirmacionConBotones($confirmacion)) {
                    return true;
                }
                
                // Si también falla el servicio alternativo, usar mensaje de texto tradicional
                Log::info('Servicio de botones alternativo falló, usando mensaje de texto', [
                    'confirmacion_id' => $confirmacion->id
                ]);
            }
            
            $mensaje = $this->construirMensajeConEnlaces($confirmacion);
            
            Log::info('Enviando mensaje de texto', [
                'confirmacion_id' => $confirmacion->id,
                'metodo' => $confirmacion->metodo,
                'destinatario' => $confirmacion->destinatario,
                'mensaje' => $mensaje
            ]);
            
            switch ($confirmacion->metodo) {
                case CitaConfirmacion::METODO_WHATSAPP:
                    $resultado = $this->enviarPorWhatsApp($confirmacion->destinatario, $mensaje);
                    Log::info('Resultado envío WhatsApp', [
                        'confirmacion_id' => $confirmacion->id,
                        'resultado' => $resultado
                    ]);
                    return $resultado;
                
                case CitaConfirmacion::METODO_EMAIL:
                    $resultado = $this->enviarPorEmail($confirmacion->destinatario, $mensaje);
                    Log::info('Resultado envío Email', [
                        'confirmacion_id' => $confirmacion->id,
                        'resultado' => $resultado
                    ]);
                    return $resultado;
                    
                default:
                    Log::warning('Método de envío no reconocido', [
                        'confirmacion_id' => $confirmacion->id,
                        'metodo' => $confirmacion->metodo
                    ]);
                    return false;
            }
        } catch (\Exception $e) {
            Log::error('Error enviando confirmación', [
                'confirmacion_id' => $confirmacion->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Construye mensaje con botones interactivos para WhatsApp Business API
     */
    protected function construirMensajeConBotonesInteractivos(CitaConfirmacion $confirmacion): array
    {
        $cita = $confirmacion->cita;
        $fecha = $cita->fecha_inicio->format('d/m/Y');
        $hora = $cita->fecha_inicio->format('h:i A');
        
        return [
            'type' => 'interactive',
            'interactive' => [
                'type' => 'button',
                'header' => [
                    'type' => 'text',
                    'text' => 'Confirmacion de Cita Medica'
                ],
                'body' => [
                    'text' => "Estimado(a) {$cita->paciente->nombre_completo},\n\n" .
                             "Desea confirmar su cita medica?\n\n" .
                             "Fecha: {$fecha}\n" .
                             "Hora: {$hora}\n" .
                             "Medico: Dr(a). {$cita->medico->nombre_completo}\n" .
                             "Especialidad: {$cita->especialidad->nombre}"
                ],
                'footer' => [
                    'text' => 'Tiene 24 horas para responder.'
                ],
                'action' => [
                    'buttons' => [
                        [
                            'type' => 'reply',
                            'reply' => [
                                'id' => 'confirmar_' . $confirmacion->token_confirmacion,
                                'title' => 'Confirmar'
                            ]
                        ],
                        [
                            'type' => 'reply',
                            'reply' => [
                                'id' => 'cancelar_' . $confirmacion->token_confirmacion,
                                'title' => 'Cancelar'
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * Construye mensaje con enlaces interactivos
     */
    protected function construirMensajeConEnlaces(CitaConfirmacion $confirmacion): string
    {
        $cita = $confirmacion->cita;
        $fecha = $cita->fecha_inicio->format('d/m/Y');
        $hora = $cita->fecha_inicio->format('h:i A');
        
        $urlConfirmar = URL::temporarySignedRoute(
            'citas.confirmar',
            now()->addHours(24),
            ['token' => $confirmacion->token_confirmacion]
        );

        $urlCancelar = URL::temporarySignedRoute(
            'citas.cancelar',
            now()->addHours(24),
            ['token' => $confirmacion->token_confirmacion]
        );

        // Mensaje simple para evitar spam
        return "Hola {$cita->paciente->nombre_completo}, tiene una cita médica:\n\n" .
               "Fecha: {$fecha} a las {$hora}\n" .
               "Dr(a). {$cita->medico->nombre_completo} - {$cita->especialidad->nombre}\n\n" .
               "Haz click en el enlace para confirmar tu cita:\n" .
               "Confirmar: $urlConfirmar\n" .
               "Cancelar: $urlCancelar\n\n" .
               "Gracias.";
    }

    /**
     * Obtiene teléfono destino considerando tutores para menores
     */
    protected function obtenerTelefonoDestino(Cita $cita): string
    {
        if ($cita->paciente->es_menor && $cita->paciente->tutor) {
            return $cita->paciente->tutor->telefono;
        }
        
        return $cita->paciente->telefono;
    }

    /**
     * Genera el mensaje de confirmación para la cita
     */
    protected function generarMensajeConfirmacion(Cita $cita): string
    {
        $fecha = $cita->fecha_inicio->format('d/m/Y');
        $hora = $cita->fecha_inicio->format('h:i A');
        
        return "Hola {$cita->paciente->nombre_completo}, tiene una cita médica programada:\n\n" .
               "Fecha: {$fecha} a las {$hora}\n" .
               "Dr(a). {$cita->medico->nombre_completo} - {$cita->especialidad->nombre}\n\n" .
               "Por favor confirme su asistencia respondiendo:\n" .
               "SI - para confirmar\n" .
               "NO - para cancelar\n\n" .
               "Gracias.";
    }

    /**
     * Genera token único para confirmación
     */
    protected function generarToken(): string
    {
        return bin2hex(random_bytes(20));
    }

    /**
     * Obtiene el código de país de la empresa
     */
    protected function obtenerCodigoPais(): string
    {
        if ($this->codigoPais !== null) {
            return $this->codigoPais;
        }

        $this->codigoPais = '58';

        $empId = $this->empresaId;
        
        Log::info('Obteniendo código de país', [
            'empresa_id' => $empId,
            'usuario_empresa_id' => auth()->check() ? auth()->user()->empresa_id : null
        ]);

        if ($empId) {
            $empresa = \DB::table('empresas')->where('id', $empId)->first();
            if ($empresa && $empresa->pais_id) {
                $pais = \DB::table('pais')->where('id', $empresa->pais_id)->first();
                if ($pais && $pais->codigo_telefonico) {
                    $this->codigoPais = ltrim($pais->codigo_telefonico, '+');
                    Log::info('Código de país encontrado', [
                        'empresa_id' => $empId,
                        'pais_id' => $empresa->pais_id,
                        'codigo_telefonico' => $pais->codigo_telefonico,
                        'codigo_pais' => $this->codigoPais
                    ]);
                }
            }
        }

        return $this->codigoPais;
    }

    /**
     * Formatea el número de teléfono al formato internacional
     */
    protected function formatearTelefono(string $telefono): string
    {
        $limpio = preg_replace('/\D/', '', $telefono);

        if (str_starts_with($limpio, '0')) {
            $limpio = substr($limpio, 1);
        }

        $codigo = $this->obtenerCodigoPais();

        if (!str_starts_with($limpio, $codigo) && strlen($limpio) >= 7 && strlen($limpio) <= 12) {
            $limpio = $codigo . $limpio;
        }

        return '+' . $limpio;
    }

    /**
     * Cancela confirmaciones pendientes de una cita
     */
    protected function cancelarConfirmacionesPendientes(Cita $cita): void
    {
        CitaConfirmacion::where('cita_id', $cita->id)
                       ->where('estado', CitaConfirmacion::ESTADO_PENDIENTE)
                       ->update(['estado' => CitaConfirmacion::ESTADO_SIN_RESPUESTA]);
    }

    /**
     * Envía mensaje interactivo con botones para WhatsApp Business API
     */
    protected function enviarMensajeInteractivo(string $telefono, array $mensajeInteractivo): bool
    {
        if (!$this->whatsApp->isConfigured()) {
            return false;
        }

        $telefonoPaciente = $this->formatearTelefono($telefono);

        try {
            // Intentar enviar mensaje interactivo
            $result = $this->whatsApp->sendInteractiveMessage($telefonoPaciente, $mensajeInteractivo);
            
            if ($result !== null) {
                Log::info('Mensaje interactivo enviado exitosamente', [
                    'telefono' => $telefono,
                    'tipo' => 'botones_confirmacion'
                ]);
                return true;
            }
        } catch (\Exception $e) {
            Log::warning('Error al enviar mensaje interactivo', [
                'telefono' => $telefono,
                'error' => $e->getMessage()
            ]);
        }

        return false;
    }

    /**
     * Envío específico por canal
     */
    protected function enviarPorWhatsApp(string $telefono, string $mensaje): bool
    {
        if (!$this->whatsApp->isConfigured()) {
            return false;
        }

        $telefonoFormateado = $this->formatearTelefono($telefono);
        
        Log::info('Enviando mensaje WhatsApp', [
            'telefono_original' => $telefono,
            'telefono_formateado' => $telefonoFormateado,
            'mensaje' => $mensaje
        ]);

        $result = $this->whatsApp->sendMessage($telefonoFormateado, $mensaje, true);
        
        if ($result !== null) {
            Log::info('Mensaje WhatsApp enviado exitosamente', [
                'telefono' => $telefonoFormateado,
                'result' => $result
            ]);
        } else {
            Log::warning('Falló envío de mensaje WhatsApp', [
                'telefono' => $telefonoFormateado,
                'mensaje' => $mensaje
            ]);
        }
        
        return $result !== null;
    }


    protected function enviarPorEmail(string $email, string $mensaje): bool
    {
        // Implementación de envío por email - pendiente de implementar
        Log::info('EmailService no implementado aún', ['email' => $email]);
        return false;
    }

    protected function solicitarClarificacion(CitaConfirmacion $confirmacion): void
    {
        // Intentar enviar mensaje interactivo de clarificación
        $mensajeInteractivo = [
            'type' => 'interactive',
            'interactive' => [
                'type' => 'button',
                'body' => [
                    'text' => 'No entendi su respuesta. Por favor seleccione una opcion:'
                ],
                'action' => [
                    'buttons' => [
                        [
                            'type' => 'reply',
                            'reply' => [
                                'id' => 'confirmar_' . $confirmacion->token_confirmacion,
                                'title' => 'Confirmar'
                            ]
                        ],
                        [
                            'type' => 'reply',
                            'reply' => [
                                'id' => 'cancelar_' . $confirmacion->token_confirmacion,
                                'title' => 'Cancelar'
                            ]
                        ]
                    ]
                ]
            ]
        ];
        
        if (!$this->enviarMensajeInteractivo($confirmacion->destinatario, $mensajeInteractivo)) {
            // Si falla, enviar mensaje de texto
            $mensajeTexto = "No entendi su respuesta. Por favor responda:\n" .
                           "SI para confirmar\n" .
                           "NO para cancelar";
            
            $telefonoFormateado = $this->formatearTelefono($confirmacion->destinatario);
            $this->whatsApp->sendMessage($telefonoFormateado, $mensajeTexto, true);
        }
    }

    /**
     * Obtiene estadísticas de confirmaciones
     */
    public function obtenerEstadisticas(int $dias = 30): array
    {
        $confirmaciones = CitaConfirmacion::where('created_at', '>=', now()->subDays($dias))->get();
        return [
            'total' => $confirmaciones->count(),
            'confirmadas' => $confirmaciones->where('estado', CitaConfirmacion::ESTADO_CONFIRMADO)->count(),
            'rechazadas' => $confirmaciones->where('estado', CitaConfirmacion::ESTADO_RECHAZADO)->count(),
            'sin_respuesta' => $confirmaciones->where('estado', CitaConfirmacion::ESTADO_SIN_RESPUESTA)->count(),
            'tasa_confirmacion' => $confirmaciones->count() > 0 
                ? round(($confirmaciones->where('estado', CitaConfirmacion::ESTADO_CONFIRMADO)->count() / $confirmaciones->count()) * 100, 2)
                : 0
        ];
    }
}