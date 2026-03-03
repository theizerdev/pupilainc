<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Consulta;
use App\Models\Cita;

echo "=== Consultas (sin global scope) ===\n";
$consultas = Consulta::withoutGlobalScopes()->get();
foreach ($consultas as $c) {
    echo "ID:{$c->id} | cita_id:{$c->cita_id} | estado:{$c->estado} | empresa:{$c->empresa_id} | sucursal:" . var_export($c->sucursal_id, true) . "\n";
}

echo "\n=== Citas confirmadas ===\n";
$citas = Cita::withoutGlobalScopes()->where('estado', 'confirmada')->get();
foreach ($citas as $cita) {
    echo "Cita ID:{$cita->id} | empresa:{$cita->empresa_id} | sucursal:" . var_export($cita->sucursal_id, true) . "\n";
}

echo "\n=== User logged in test ===\n";
auth()->loginUsingId(1);
$u = auth()->user();
echo "User: {$u->name} | empresa:{$u->empresa_id} | sucursal:" . var_export($u->sucursal_id, true) . "\n";

echo "\n=== Test: crearConsultaSiNoExiste manually ===\n";
// Delete existing consultas to test fresh
Consulta::withoutGlobalScopes()->delete();
echo "Consultas borradas. Total ahora: " . Consulta::withoutGlobalScopes()->count() . "\n";

foreach ($citas as $cita) {
    echo "Intentando crear consulta para cita #{$cita->id}...\n";
    try {
        $cita->cambiarEstado('confirmada');
        $consultaCreada = Consulta::withoutGlobalScopes()->where('cita_id', $cita->id)->first();
        if ($consultaCreada) {
            echo "  -> CREADA OK (ID: {$consultaCreada->id}, estado: {$consultaCreada->estado})\n";
        } else {
            echo "  -> NO SE CREO!\n";
        }
    } catch (\Throwable $e) {
        echo "  -> ERROR: " . $e->getMessage() . "\n";
    }
}

echo "\nTotal consultas final: " . Consulta::withoutGlobalScopes()->count() . "\n";
