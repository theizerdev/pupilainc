<?php
require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$consultas = App\Models\Consulta::where('estado', 'en_gotas')->get();
echo 'Consultas en en_gotas: ' . $consultas->count() . PHP_EOL;

foreach($consultas as $c) {
    echo 'ID: ' . $c->id . ', Gotas: ' . $c->gotasAplicadas->count() . PHP_EOL;

    if ($c->gotasAplicadas->count() > 0) {
        $gota = $c->gotasAplicadas->first();
        echo '  Gota ID: ' . $gota->id . ', Tiempo espera: ' . $gota->tiempo_espera . ', Created: ' . $gota->created_at . PHP_EOL;

        $fechaInicio = $gota->updated_at ?? $gota->created_at;
        $segundosTotales = $gota->tiempo_espera * 60;
        $segundosTranscurridos = $fechaInicio->diffInSeconds(now());
        $segundosRestantes = max(0, $segundosTotales - $segundosTranscurridos);

        echo '  Segundos restantes: ' . $segundosRestantes . PHP_EOL;
        echo '  Expirado: ' . ($segundosRestantes <= 0 ? 'SÍ' : 'NO') . PHP_EOL;

        // Si expiró, procesar
        if ($segundosRestantes <= 0) {
            echo '  Procesando dilatación...' . PHP_EOL;
            try {
                $dilatacionService = new App\Services\DilatacionService();
                $processed = $dilatacionService->processPendingDilataciones();
                echo '  Procesadas: ' . $processed . PHP_EOL;

                // Recargar y verificar
                $c->refresh();
                echo '  Nuevo estado: ' . $c->estado . PHP_EOL;
            } catch (Exception $e) {
                echo '  Error: ' . $e->getMessage() . PHP_EOL;
            }
        }
    }
}

echo PHP_EOL;
echo '=== VERIFICACIÓN FINAL ===' . PHP_EOL;
echo 'Notificaciones totales: ' . App\Models\Notification::count() . PHP_EOL;
echo 'Mensajes de chat totales: ' . App\Models\ChatMessage::count() . PHP_EOL;

$consulta = App\Models\Consulta::find(1);
echo 'Consulta ID 1 - Estado: ' . $consulta->estado . PHP_EOL;
if ($consulta->gotasAplicadas->count() > 0) {
    echo 'Gota notificada: ' . ($consulta->gotasAplicadas->first()->notificado ? 'SÍ' : 'NO') . PHP_EOL;
}