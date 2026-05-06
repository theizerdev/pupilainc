<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

DB::table('consulta_gotas')->where('id', 1)->update(['tiempo_espera' => 1]);
echo "✅ Tiempo de espera actualizado a 1 minuto\n";

$gota = App\Models\ConsultaGota::find(1);
echo "Tiempo actual: {$gota->tiempo_espera} minuto(s)\n";
echo "Updated at: {$gota->updated_at}\n";

$ahora = now();
$minutosTranscurridos = \Carbon\Carbon::parse($gota->updated_at)->diffInMinutes($ahora);
$minutosRestantes = max(0, $gota->tiempo_espera - $minutosTranscurridos);

echo "\nTimer debería mostrar:\n";
echo "Minutos transcurridos: {$minutosTranscurridos}\n";
echo "Minutos restantes: {$minutosRestantes}\n";
