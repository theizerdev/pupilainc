<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Reseteando Consulta #1 para Prueba ===\n\n";

// 1. Marcar la gota como NO notificada
$gota = App\Models\ConsultaGota::find(1);
if ($gota) {
    $gota->update(['notificado' => false]);
    echo "✅ Gota #1 marcada como NOTIFICADO = false\n";
}

// 2. Eliminar notificaciones antiguas de dilatación para User ID 5
$deletedNotifs = App\Models\Notification::where('user_id', 5)
    ->where('title', 'like', '%Dilatación%')
    ->delete();
echo "✅ Eliminadas {$deletedNotifs} notificaciones antiguas del médico\n";

// 3. Eliminar mensajes de chat antiguos sobre dilatación para User ID 5
$deletedChats = App\Models\ChatMessage::where('receiver_id', 5)
    ->where('message', 'like', '%dilatación%')
    ->delete();
echo "✅ Eliminados {$deletedChats} mensajes de chat antiguos del médico\n";

echo "\n=== Estado Listo para Prueba ===\n";
echo "Ahora espera ~20 minutos (tiempo de espera configurado) o cambia el tiempo a 1 minuto.\n";
echo "Cuando el timer llegue a cero, deberías recibir EXACTAMENTE 1 notificación.\n";
