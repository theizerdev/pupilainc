<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Diagnóstico Completo del Timer ===\n\n";

// Verificar gota
$gota = DB::table('consulta_gotas')->where('id', 1)->first();
echo "Gota #1:\n";
echo "  tiempo_espera: {$gota->tiempo_espera}\n";
echo "  created_at: {$gota->created_at}\n";
echo "  updated_at: {$gota->updated_at}\n";
echo "  notificado: {$gota->notificado}\n\n";

// Verificar consulta
$consulta = DB::table('consultas')->where('id', 1)->first();
echo "Consulta #1:\n";
echo "  estado: {$consulta->estado}\n";
echo "  estado_changed_at: {$consulta->estado_changed_at}\n";
echo "  updated_at: {$consulta->updated_at}\n\n";

// Calcular con ambas fechas
$ahora = now();
echo "Hora actual: {$ahora}\n\n";

echo "Cálculo con gota->updated_at:\n";
$minutosDesdeGota = \Carbon\Carbon::parse($gota->updated_at)->diffInMinutes($ahora);
echo "  Minutos transcurridos: {$minutosDesdeGota}\n";
echo "  Tiempo espera: {$gota->tiempo_espera}\n";
echo "  Restantes: " . max(0, $gota->tiempo_espera - $minutosDesdeGota) . "\n\n";

echo "Cálculo con consulta->estado_changed_at:\n";
$minutosDesdeEstado = \Carbon\Carbon::parse($consulta->estado_changed_at)->diffInMinutes($ahora);
echo "  Minutos transcurridos: {$minutosDesdeEstado}\n";
echo "  Tiempo espera: {$gota->tiempo_espera}\n";
echo "  Restantes: " . max(0, $gota->tiempo_espera - $minutosDesdeEstado) . "\n\n";

// Si el tiempo no es 1 minuto, forzarlo
if ($gota->tiempo_espera != 1) {
    echo "⚠️  CORREGIENDO: El tiempo de espera no es 1 minuto. Actualizando...\n";
    DB::table('consulta_gotas')->where('id', 1)->update(['tiempo_espera' => 1]);
    echo "✅ Tiempo actualizado a 1 minuto\n\n";
}

// Reiniciar el timer completamente
echo "Reiniciando timer a AHORA...\n";
$nuevaFecha = now();
DB::table('consulta_gotas')->where('id', 1)->update(['updated_at' => $nuevaFecha]);
DB::table('consultas')->where('id', 1)->update(['estado_changed_at' => $nuevaFecha]);

echo "✅ Gota updated_at: {$nuevaFecha}\n";
echo "✅ Consulta estado_changed_at: {$nuevaFecha}\n\n";

$minutosRestantes = max(0, 1 - 0);
echo "Timer debería mostrar: 1:00 (comenzando desde cero)\n";
echo "En 1 minuto llegará a 0:00 y enviará notificación.\n";
