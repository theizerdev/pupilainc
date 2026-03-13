<?php

namespace App\Services;

use App\Models\CitaConfirmacion;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

class CitaConfirmationBotonesService
{
    private WhatsAppService $whatsApp;
    protected ?int $empresaId;
    protected ?string $codigoPais = null;

    
    public function __construct(WhatsAppService $whatsApp)
    {
        $this->whatsApp = $whatsApp;
        $this->empresaId = $this->whatsApp->getCompanyId();
    }
    
    /**
     * Envia confirmación de cita con botones usando formato alternativo
     */
    public function enviarConfirmacionConBotones(CitaConfirmacion $confirmacion): bool
    {
        try {
            Log::info('Iniciando envío de confirmación con botones alternativos', [
                'confirmacion_id' => $confirmacion->id,
                'destinatario' => $confirmacion->destinatario
            ]);
            
            // Construir mensaje con botones en formato de texto
            $mensajeConBotones = $this->construirMensajeConBotonesTexto($confirmacion);
            
            // Formatear número para WhatsApp
            $telefonoFormateado = $this->formatearTelefono($confirmacion->destinatario);
          
            // Intentar enviar mensaje
            $resultado = $this->whatsApp->sendMessage($telefonoFormateado, $mensajeConBotones);
            
            if ($resultado && isset($resultado['success']) && $resultado['success']) {
                Log::info('Mensaje con botones enviado exitosamente', [
                    'confirmacion_id' => $confirmacion->id,
                    'message_id' => $resultado['messageId'] ?? null
                ]);
                
                // Actualizar estado de envío
                $confirmacion->update([
                    'intentos' => $confirmacion->intentos + 1,
                    'fecha_envio' => now()
                ]);
                
                return true;
            } else {
                Log::error('Falló el envío del mensaje con botones', [
                    'confirmacion_id' => $confirmacion->id,
                    'resultado' => $resultado
                ]);
                return false;
            }
            
        } catch (\Exception $e) {
            Log::error('Error al enviar confirmación con botones', [
                'confirmacion_id' => $confirmacion->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Construye mensaje con botones en formato de texto para WhatsApp
     */
    private function construirMensajeConBotonesTexto(CitaConfirmacion $confirmacion): string
    {
        $cita = $confirmacion->cita;
        $fecha = $cita->fecha_inicio->format('d/m/Y');
        $hora = $cita->fecha_inicio->format('h:i A');
        $paciente = $cita->paciente;
        $medico = $cita->medico;
        $especialidad = $cita->especialidad;
        
        // Construir enlaces de confirmación
        $urlConfirmar = URL::temporarySignedRoute(
            'citas.confirmar',
            $confirmacion->fecha_envio->addHours(24),
            ['token' => $confirmacion->token_confirmacion]
        );

        $urlCancelar = URL::temporarySignedRoute(
            'citas.cancelar',
            $confirmacion->fecha_envio->addHours(24),
            ['token' => $confirmacion->token_confirmacion]
        );
        
        // Formato con botones simulados para WhatsApp
        $mensaje = "🏥 *CONFIRMACIÓN DE CITA MÉDICA* 🏥\n\n";
        $mensaje .= "¡Hola *{$paciente->nombre_completo}*!\n\n";
        $mensaje .= "📅 *Fecha:* {$fecha}\n";
        $mensaje .= "🕐 *Hora:* {$hora}\n";
        $mensaje .= "👨‍⚕️ *Médico:* Dr(a). {$medico->nombre_completo}\n";
        $mensaje .= "🏥 *Especialidad:* {$especialidad->nombre}\n\n";
        
        $mensaje .= "¿Confirmas tu asistencia a esta cita?\n\n";
        
        // Botones simulados con emojis y enlaces
        $mensaje .= "*OPCIONES DE RESPUESTA:*\n\n";
        
        // Botón Confirmar
        $mensaje .= "✅ *CONFIRMAR CITA*\n";
        $mensaje .= "👉 Haga clic aquí para confirmar:\n";
        $mensaje .= "{$urlConfirmar}\n\n";
        
        // Botón Cancelar  
        $mensaje .= "❌ *CANCELAR CITA*\n";
        $mensaje .= "👉 Haga clic aquí para cancelar:\n";
        $mensaje .= "{$urlCancelar}\n\n";
        
        $mensaje .= "⏰ *Importante:* Tiene 24 horas para responder.\n";
        $mensaje .= "📱 *¿Problemas con los enlaces?* Responda:\n";
        $mensaje .= "*SI* - para confirmar\n";
        $mensaje .= "*NO* - para cancelar\n\n";
        $mensaje .= "¡Gracias por confiar en nosotros! 😊";
        
        return $mensaje;
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
     * Procesa respuesta por texto cuando los botones no funcionan
     */
    public function procesarRespuestaTexto(CitaConfirmacion $confirmacion, string $respuesta): bool
    {
        $respuesta = strtolower(trim($respuesta));
        
        Log::info('Procesando respuesta por texto', [
            'confirmacion_id' => $confirmacion->id,
            'respuesta' => $respuesta
        ]);
        
        if (in_array($respuesta, ['si', 'sí', 'confirmar', 'confirmo', 'asistiré'])) {
            $confirmacion->update([
                'estado' => 'confirmado',
                'fecha_respuesta' => now(),
                'respuesta_recibida' => 'SI (por mensaje de texto)'
            ]);
            
            Log::info('Cita confirmada por mensaje de texto', [
                'confirmacion_id' => $confirmacion->id
            ]);
            
            return true;
        } elseif (in_array($respuesta, ['no', 'cancelar', 'cancelo', 'no asistiré'])) {
            $confirmacion->update([
                'estado' => 'rechazado',
                'fecha_respuesta' => now(),
                'respuesta_recibida' => 'NO (por mensaje de texto)'
            ]);
            
            Log::info('Cita cancelada por mensaje de texto', [
                'confirmacion_id' => $confirmacion->id
            ]);
            
            return true;
        }
        
        Log::warning('Respuesta no reconocida', [
            'confirmacion_id' => $confirmacion->id,
            'respuesta' => $respuesta
        ]);
        
        return false;
    }
}