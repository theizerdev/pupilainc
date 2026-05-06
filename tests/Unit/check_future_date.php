<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Verificando Fecha de la Gota ===\n\n";

$gota = DB::table('consulta_gotas')->find(1);
$consulta = DB::table('consultas')->find(1);

echo "Gota #1:\n";
echo "  tiempo_espera: {$gota->tiempo_espera}\n";
echo "  created_at: {$gota->created_at}\n";
echo "  updated_at: {$gota->updated_at}\n\n";

echo "Consulta #1:\n";
echo "  estado_changed_at: {$consulta->estado_changed_at}\n\n";

$ahora = now();
echo "Hora actual: {$ahora}\n\n";

// Verificar cuál fecha se está usando
$fechaGota = $gota->updated_at ?? $gota->created_at;
$fechaEstado = $consulta->estado_changed_at ?? $consulta->updated_at;

echo "Comparación:\n";
echo "  fechaGota: {$fechaGota}\n";
echo "  fechaEstado: {$fechaEstado}\n\n";

if (\Carbon\Carbon::parse($fechaGota)->gt(\Carbon\Carbon::parse($fechaEstado))) {
    $fechaInicio = $fechaGota;
    echo "Usando: fechaGota (más reciente)\n";
} else {
    $fechaInicio = $fechaEstado;
    echo "Usando: fechaEstado (más reciente)\n";
}

echo "\nFecha seleccionada: {$fechaInicio}\n";

$diff = \Carbon\Carbon::parse($fechaInicio)->diffInMinutes($ahora);
echo "Diferencia en minutos: {$diff}\n";

if ($diff < 0) {
    echo "\n❌ ERROR: La fecha está en el FUTURO por " . abs($diff) . " minutos\n";
    echo "Esto explica por qué minutos_transcurridos es negativo.\n";
} else {
    echo "\n✅ La fecha está en el pasado (correcto)\n";
}
