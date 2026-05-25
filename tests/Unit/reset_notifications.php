<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Limpiando Notificaciones Antiguas ===\n\n";

// Marcar todas como leídas
$updated = App\Models\Notification::where('user_id', 1)->update(['read_at' => now()]);
echo "Notificaciones marcadas como leídas: {$updated}\n";

// Eliminar mensajes de chat antiguos de dilatación
$deleted = App\Models\ChatMessage::where('message', 'like', '%dilatación%')->delete();
echo "Mensajes de chat eliminados: {$deleted}\n";

echo "\n=== Estado Limpio ===\n";
echo "Ahora puedes crear una nueva consulta en estado 'en_gotas' para probar.\n";
echo "Las notificaciones aparecerán cuando el timer llegue a cero.\n";
