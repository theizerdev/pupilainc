<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Actualizando Fecha de Gota para Timer Correcto ===\n\n";

// Actualizar la fecha de la gota a AHORA (hace 1 minuto para que el timer comience)
$gota = App\Models\ConsultaGota::find(1);
$nuevaFecha = now()->subMinutes(0); // Comenzar ahora mismo

$gota->update(['created_at' => $nuevaFecha, 'updated_at' => now()]);

echo "✅ Gota #1 actualizada:\n";
echo "   Nueva created_at: {$gota->created_at}\n";
echo "   Tiempo espera: {$gota->tiempo_espera} minuto(s)\n";
echo "   Notificado: " . ($gota->notificado ? 'SÍ' : 'NO') . "\n";

// También actualizar estado_changed_at de la consulta
$consulta = App\Models\Consulta::find(1);
$consulta->update(['estado_changed_at' => $nuevaFecha]);

echo "✅ Consulta #1 actualizada:\n";
echo "   estado_changed_at: {$consulta->estado_changed_at}\n";

echo "\n=== Estado del Timer ===\n";
$minutosTranscurridos = \Carbon\Carbon::parse($gota->created_at)->diffInMinutes(now());
$minutosRestantes = max(0, $gota->tiempo_espera - $minutosTranscurridos);

echo "Minutos transcurridos: {$minutosTranscurridos}\n";
echo "Minutos restantes: {$minutosRestantes}\n";

echo "\n✅ ¡Listo! El timer debería mostrar ~1:00 ahora y contar hacia atrás.\n";
echo "En 1 minuto llegará a 0:00 y enviará la notificación con sonido bip.\n";
