<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Verificando Fechas de Consulta #1 ===\n\n";

$consulta = App\Models\Consulta::find(1);

echo "Consulta ID: {$consulta->id}\n";
echo "Estado: {$consulta->estado}\n";
echo "estado_changed_at: {$consulta->estado_changed_at}\n";
echo "updated_at: {$consulta->updated_at}\n";
echo "created_at: {$consulta->created_at}\n\n";

echo "=== Gotas Aplicadas ===\n";
$gotas = $consulta->gotasAplicadas()->where('estado', 'aplicada')->get();
foreach ($gotas as $gota) {
    echo "Gota ID: {$gota->id}\n";
    echo "  Tiempo espera: {$gota->tiempo_espera} minutos\n";
    echo "  Created at: {$gota->created_at}\n";
    echo "  Updated at: {$gota->updated_at}\n";

    // Calcular tiempo transcurrido desde la gota
    $minutosDesdeGota = \Carbon\Carbon::parse($gota->created_at)->diffInMinutes(now());
    echo "  Minutos transcurridos desde gota: {$minutosDesdeGota}\n";
    echo "  Minutos restantes: " . max(0, $gota->tiempo_espera - $minutosDesdeGota) . "\n";
}

echo "\n=== Cálculo Actual (INCORRECTO) ===\n";
$fechaInicio = $consulta->estado_changed_at ?? $consulta->updated_at;
$minutosTranscurridos = \Carbon\Carbon::parse($fechaInicio)->diffInMinutes(now());
echo "Usando estado_changed_at: {$fechaInicio}\n";
echo "Minutos transcurridos: {$minutosTranscurridos}\n";
echo "Tiempo espera: 1 minuto\n";
echo "Minutos restantes calculados: " . max(0, 1 - $minutosTranscurridos) . "\n";

echo "\n=== Cálculo Correcto (DEBERÍA SER) ===\n";
$gota = $gotas->first();
if ($gota) {
    $fechaInicioCorrecta = $gota->created_at;
    $minutosTranscurridosCorrecto = \Carbon\Carbon::parse($fechaInicioCorrecta)->diffInMinutes(now());
    echo "Usando gota created_at: {$fechaInicioCorrecta}\n";
    echo "Minutos transcurridos: {$minutosTranscurridosCorrecto}\n";
    echo "Tiempo espera: {$gota->tiempo_espera} minuto(s)\n";
    echo "Minutos restantes correctos: " . max(0, $gota->tiempo_espera - $minutosTranscurridosCorrecto) . "\n";
}
