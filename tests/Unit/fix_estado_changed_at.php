<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Consulta;

echo "Actualizando estado_changed_at para consultas en estado 'en_gotas'...\n";

$consultas = Consulta::where('estado', Consulta::ESTADO_EN_GOTAS)
    ->whereNull('estado_changed_at')
    ->get();

foreach ($consultas as $consulta) {
    // Usar la fecha más reciente entre gota aplicada o updated_at
    $gotaAplicada = $consulta->gotasAplicadas()
        ->where('estado', 'aplicada')
        ->latest()
        ->first();

    $fechaReferencia = null;
    if ($gotaAplicada) {
        $fechaReferencia = $gotaAplicada->updated_at ?? $gotaAplicada->created_at;
    } else {
        $fechaReferencia = $consulta->updated_at;
    }

    $consulta->update(['estado_changed_at' => $fechaReferencia]);

    echo "Consulta ID {$consulta->id}: estado_changed_at actualizado a {$fechaReferencia}\n";
}

echo "Actualización completada. Total consultas actualizadas: " . $consultas->count() . "\n";