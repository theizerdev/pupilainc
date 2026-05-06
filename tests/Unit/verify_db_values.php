<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Verificando Consulta en BD ===\n\n";

$consulta = DB::table('consultas')->where('id', 1)->first();
echo "Consulta #1:\n";
echo "  estado: {$consulta->estado}\n";
echo "  estado_changed_at: {$consulta->estado_changed_at}\n";
echo "  updated_at: {$consulta->updated_at}\n\n";

$gota = DB::table('consulta_gotas')->where('consulta_id', 1)->where('estado', 'aplicada')->latest()->first();
if ($gota) {
    echo "Gota aplicada:\n";
    echo "  tiempo_espera: {$gota->tiempo_espera}\n";
    echo "  created_at: {$gota->created_at}\n";
    echo "  updated_at: {$gota->updated_at}\n\n";
    
    $ahora = now();
    $fechaInicio = $gota->updated_at;
    $segundosTranscurridos = \Carbon\Carbon::parse($fechaInicio)->diffInSeconds($ahora);
    $tiempoEsperaSegundos = $gota->tiempo_espera * 60;
    $segundosRestantes = max(0, $tiempoEsperaSegundos - $segundosTranscurridos);
    
    echo "Cálculo:\n";
    echo "  Fecha inicio: {$fechaInicio}\n";
    echo "  Ahora: {$ahora}\n";
    echo "  Segundos transcurridos: {$segundosTranscurridos}\n";
    echo "  Tiempo espera (seg): {$tiempoEsperaSegundos}\n";
    echo "  Segundos restantes: {$segundosRestantes}\n";
    echo "  Display: " . floor($segundosRestantes / 60) . ":" . str_pad($segundosRestantes % 60, 2, '0', STR_PAD_LEFT) . "\n";
} else {
    echo "❌ No hay gotas aplicadas\n";
}
