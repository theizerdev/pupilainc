<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Configurando Prueba Rápida - 1 Minuto ===\n\n";

// Cambiar tiempo de espera a 1 minuto
$gota = App\Models\ConsultaGota::find(1);
$gota->update(['tiempo_espera' => 1, 'notificado' => false]);

echo "✅ Tiempo de espera cambiado a: {$gota->tiempo_espera} minuto(s)\n";
echo "✅ Notificado: FALSE\n";

// Reiniciar timer
$consulta = App\Models\Consulta::find(1);
$consulta->update(['estado_changed_at' => now()]);

echo "✅ Timer reiniciado a: {$consulta->estado_changed_at}\n";

echo "\n=== Estado Final ===\n";
echo "En 1 minuto deberías recibir la notificación con sonido bip!\n";
echo "Verifica:\n";
echo "  - NotificationBell (campana 🔔)\n";
echo "  - Chat interno (mensaje + sonido)\n";
