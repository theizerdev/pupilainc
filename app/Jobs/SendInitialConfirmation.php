<?php

namespace App\Jobs;

use App\Models\Cita;
use App\Services\CitaConfirmationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendInitialConfirmation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [10, 30, 60]; // segundos

    protected $cita;

    /**
     * Create a new job instance.
     */
    public function __construct(Cita $cita)
    {
        $this->cita = $cita;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Verificar que la cita aún necesita confirmación
            if (!$this->cita->necesitaConfirmacion()) {
                Log::info("Cita {$this->cita->id} ya no necesita confirmación");
                return;
            }

            $service = new CitaConfirmationService();
            $confirmacion = $service->iniciarConfirmacion($this->cita);

            Log::info("Confirmación inicial enviada para cita {$this->cita->id}", [
                'confirmacion_id' => $confirmacion->id,
                'paciente' => $this->cita->paciente->nombre_completo,
                'medico' => $this->cita->medico->nombre_completo
            ]);

        } catch (\Exception $e) {
            Log::error("Error enviando confirmación inicial para cita {$this->cita->id}: {$e->getMessage()}");

            // Reintentar si es un error temporal
            if ($this->attempts() < $this->tries) {
                $delay = $this->backoff[$this->attempts() - 1] ?? 60;
                self::dispatch($this->cita)->delay(now()->addSeconds($delay));
            }
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("Job SendInitialConfirmation falló definitivamente: {$exception->getMessage()}", [
            'cita_id' => $this->cita->id,
            'exception' => $exception
        ]);
    }
}
