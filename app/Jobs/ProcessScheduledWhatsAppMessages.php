<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\WhatsAppScheduledMessage;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ProcessScheduledWhatsAppMessages implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300; // 5 minutos
    public $tries = 3;

    /**
     * Execute the job.
     */
    public function handle()
    {
        $now = Carbon::now();
        
        // Obtener mensajes pendientes que deben ser enviados
        $messages = WhatsAppScheduledMessage::with(['template'])
            ->where('status', 'pending')
            ->where('scheduled_at', '<=', $now)
            ->where('attempts', '<', 3)
            ->take(50) // Procesar máximo 50 mensajes por job
            ->get();

        Log::info("Procesando {$messages->count()} mensajes programados de WhatsApp");

        $defaultWhatsappService = app(WhatsAppService::class);
        $processed = 0;
        $failed = 0;

        foreach ($messages as $message) {
            try {
                // Incrementar intentos
                $message->increment('attempts');

                // Procesar variables si hay una plantilla asociada
                $content = $message->message_content;
                if ($message->template && $message->variables) {
                    $variables = json_decode($message->variables, true) ?? [];
                    foreach ($variables as $key => $value) {
                        $content = str_replace("{{{$key}}}", $value, $content);
                    }
                }

                // Usar WhatsApp por empresa si está disponible
                $whatsappService = $message->empresa_id
                    ? WhatsAppService::forCompany($message->empresa_id)
                    : $defaultWhatsappService;

                // Enviar mensaje
                $result = $whatsappService->sendMessage(
                    $message->recipient_phone,
                    $content
                );

                if ($result['success']) {
                    $message->update([
                        'status' => 'sent',
                        'sent_at' => $now,
                        'error_message' => null,
                    ]);
                    
                    // Incrementar contador de uso de la plantilla
                    if ($message->template) {
                        $message->template->incrementUsage();
                    }
                    
                    $processed++;
                    Log::info("Mensaje programado enviado exitosamente: {$message->id}");
                } else {
                    throw new \Exception($result['message'] ?? 'Error desconocido');
                }

            } catch (\Exception $e) {
                $failed++;
                $errorMessage = $e->getMessage();
                
                Log::error("Error al enviar mensaje programado {$message->id}: {$errorMessage}");

                // Si se alcanzó el máximo de intentos, marcar como fallido
                if ($message->attempts >= $message->max_attempts) {
                    $message->update([
                        'status' => 'failed',
                        'error_message' => $errorMessage,
                    ]);
                } else {
                    // Reprogramar para más tarde (exponencial backoff)
                    $delay = pow(2, $message->attempts) * 5; // 5, 10, 20 minutos
                    $message->update([
                        'scheduled_at' => $now->copy()->addMinutes($delay),
                        'error_message' => $errorMessage,
                    ]);
                }
            }
        }

        Log::info("Procesamiento completado. Exitosos: {$processed}, Fallidos: {$failed}");

        // Si hay más mensajes pendientes, programar el siguiente job
        $remainingMessages = WhatsAppScheduledMessage::where('status', 'pending')
            ->where('scheduled_at', '<=', $now->copy()->addMinutes(5))
            ->count();

        if ($remainingMessages > 0) {
            // Reprogramar el job para continuar procesando
            self::dispatch()->delay($now->copy()->addMinutes(1));
            Log::info("Programando siguiente job para procesar {$remainingMessages} mensajes restantes");
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception)
    {
        Log::error('Error en job ProcessScheduledWhatsAppMessages: ' . $exception->getMessage());
    }
}