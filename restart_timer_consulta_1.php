<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Actualizando Consulta #1 - Timer Fresh Start ===\n\n";

$consulta = App\Models\Consulta::find(1);

// Actualizar estado_changed_at a AHORA para reiniciar el timer
$consulta->update(['estado_changed_at' => now()]);

echo "✅ estado_changed_at actualizado a: {$consulta->estado_changed_at}\n";

// Verificar gota
$gota = App\Models\ConsultaGota::find(1);
echo "✅ Tiempo de espera configurado: {$gota->tiempo_espera} minutos\n";
echo "✅ Notificado: " . ($gota->notificado ? 'SÍ' : 'NO') . "\n";

$now = now();
$fechaInicio = $consulta->estado_changed_at;
$minutosTranscurridos = \Carbon\Carbon::parse($fechaInicio)->diffInMinutes($now);
$minutosRestantes = max(0, $gota->tiempo_espera - $minutosTranscurridos);

echo "\n=== Estado del Timer ===\n";
echo "Hora inicio: {$fechaInicio}\n";
echo "Hora actual: {$now}\n";
echo "Minutos transcurridos: {$minutosTranscurridos}\n";
echo "Minutos restantes: {$minutosRestantes}\n";

echo "\n=== Instrucciones ===\n";
echo "El timer comenzará desde CERO ahora.\n";
echo "Deberías recibir la notificación en {$gota->tiempo_espera} minutos.\n";
echo "Para prueba rápida, puedes cambiar el tiempo de espera a 1 minuto manualmente.\n";
