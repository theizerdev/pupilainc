<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Corrigiendo Timer - Usando updated_at ===\n\n";

// Actualizar updated_at de la gota a AHORA
$gota = App\Models\ConsultaGota::find(1);
$ahora = now();

// Forzar actualización de timestamps
DB::table('consulta_gotas')
    ->where('id', 1)
    ->update([
        'updated_at' => $ahora,
        'notificado' => false
    ]);

echo "✅ Gota #1 actualizada:\n";
echo "   updated_at: {$ahora}\n";
echo "   Tiempo espera: {$gota->tiempo_espera} minuto(s)\n";
echo "   Notificado: FALSE\n";

// También actualizar estado_changed_at de la consulta
DB::table('consultas')
    ->where('id', 1)
    ->update(['estado_changed_at' => $ahora]);

echo "✅ Consulta #1 actualizada:\n";
echo "   estado_changed_at: {$ahora}\n";

echo "\n=== Estado del Timer ===\n";
$minutosTranscurridos = \Carbon\Carbon::parse($ahora)->diffInMinutes(now());
$minutosRestantes = max(0, $gota->tiempo_espera - $minutosTranscurridos);

echo "Minutos transcurridos: {$minutosTranscurridos}\n";
echo "Minutos restantes: {$minutosRestantes}\n";

echo "\n✅ ¡Listo! El timer debería mostrar ~1:00 ahora.\n";
