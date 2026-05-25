<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Reiniciando Timer a AHORA ===\n\n";

$nuevaFecha = now();

// Actualizar gota
DB::table('consulta_gotas')->where('id', 1)->update([
    'updated_at' => $nuevaFecha,
    'tiempo_espera' => 1,
    'notificado' => false
]);

// Actualizar consulta
DB::table('consultas')->where('id', 1)->update([
    'estado_changed_at' => $nuevaFecha
]);

echo "✅ Timer reiniciado a: {$nuevaFecha}\n";
echo "✅ Tiempo de espera: 1 minuto\n";
echo "✅ Notificado: FALSE\n\n";

echo "Después del hard refresh (Ctrl+Shift+R), el timer debería mostrar ~1:00\n";
echo "y contar hacia abajo. En 1 minuto llegará a 0:00 y enviará notificación.\n";
