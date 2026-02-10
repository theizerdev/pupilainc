<?php

namespace App\Jobs;

use App\Models\CitaConfirmacion;
use App\Services\CitaConfirmationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RetryFailedConfirmation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [60, 300, 600]; // segundos (1min, 5min, 10min)

    protected $confirmacionId;

    /**
     * Create a new job instance.
     *
     * @param int $confirmacionId ID de la confirmación a reintentar
     */
    public function __construct(int $confirmacionId)
    {
        $this->confirmacionId = $confirmacionId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $confirmacion = CitaConfirmacion::find($this->confirmacionId);

            if (!$confirmacion) {
                Log::warning("Confirmación no encontrada: {$this->confirmacionId}");
                return;
            }

            // Verificar que puede reintentar
            if (!$confirmacion->puedeReintentar()) {
                Log::info("Confirmación {$this->confirmacionId} no puede reintentar", [
                    'estado' => $confirmacion->estado,
                    'intentos' => $confirmacion->intentos,
                    'max_intentos' => $confirmacion->max_intentos
                ]);
                return;
            }

            $service = new CitaConfirmationService();
            $resultado = $service->reintentarConfirmacion($confirmacion);

            if ($resultado) {
                Log::info("Reintento exitoso para confirmación {$this->confirmacionId}", [
                    'intentos' => $confirmacion->fresh()->intentos
                ]);
            } else {
                Log::error("Reintento fallido para confirmación {$this->confirmacionId}");

                // Si agotó intentos, marcar como sin respuesta
                if ($confirmacion->fresh()->intentos >= $confirmacion->max_intentos) {
                    $confirmacion->update(['estado' => CitaConfirmacion::ESTADO_SIN_RESPUESTA]);
                    Log::info("Confirmación {$this->confirmacionId} marcada como sin respuesta por agotar intentos");
                }
            }

        } catch (\Exception $e) {
            Log::error("Error en RetryFailedConfirmation para confirmación {$this->confirmacionId}: {$e->getMessage()}");

            // Reintentar el job si es un error temporal
            if ($this->attempts() < $this->tries) {
                $delay = $this->backoff[$this->attempts() - 1] ?? 600;
                self::dispatch($this->confirmacionId)->delay(now()->addSeconds($delay));
            }
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("Job RetryFailedConfirmation falló definitivamente: {$exception->getMessage()}", [
            'confirmacion_id' => $this->confirmacionId,
            'exception' => $exception
        ]);
    }
}
