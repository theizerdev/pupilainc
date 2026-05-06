<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Debugging del Timer - Valor Real ===\n\n";

$consulta = App\Models\Consulta::find(1);
$gotaAplicada = $consulta->gotasAplicadas()
    ->where('estado', 'aplicada')
    ->latest()
    ->first();

echo "Gota aplicada:\n";
echo "  ID: {$gotaAplicada->id}\n";
echo "  tiempo_espera: {$gotaAplicada->tiempo_espera}\n";
echo "  created_at: {$gotaAplicada->created_at}\n";
echo "  updated_at: {$gotaAplicada->updated_at}\n\n";

$fechaGota = $gotaAplicada ? ($gotaAplicada->updated_at ?? $gotaAplicada->created_at) : null;
$fechaEstado = $consulta->estado_changed_at ?? $consulta->updated_at;

echo "Fechas para comparación:\n";
echo "  fechaGota: {$fechaGota}\n";
echo "  fechaEstado: {$fechaEstado}\n\n";

if ($fechaGota && $fechaEstado) {
    $fechaInicio = \Carbon\Carbon::parse($fechaGota)->gt(\Carbon\Carbon::parse($fechaEstado)) 
        ? $fechaGota 
        : $fechaEstado;
} else {
    $fechaInicio = $fechaGota ?? $fechaEstado;
}

echo "Fecha seleccionada (más reciente): {$fechaInicio}\n\n";

$minutosTranscurridos = \Carbon\Carbon::parse($fechaInicio)->diffInMinutes(now());
$tiempoEspera = $gotaAplicada->tiempo_espera;
$minutosRestantes = max(0, $tiempoEspera - $minutosTranscurridos);

echo "Cálculos:\n";
echo "  minutosTranscurridos: {$minutosTranscurridos}\n";
echo "  tiempoEspera: {$tiempoEspera}\n";
echo "  minutosRestantes: {$minutosRestantes}\n\n";

// Simular lo que muestra la vista
$totalSegundos = round($minutosRestantes * 60);
$mins = floor($totalSegundos / 60);
$secs = $totalSegundos % 60;

echo "Display en vista:\n";
echo "  totalSegundos: {$totalSegundos}\n";
echo "  mins: {$mins}\n";
echo "  secs: {$secs}\n";
echo "  Muestra: {$mins}:{$secs}\n\n";

if ($mins > 100) {
    echo "❌ ERROR: El valor es demasiado alto. Algo está mal en el cálculo.\n";
    echo "Posible causa: minutosTranscurridos es negativo o tiempoEspera es incorrecto.\n";
}
