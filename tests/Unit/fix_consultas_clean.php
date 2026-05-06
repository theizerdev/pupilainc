<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Consulta;
use App\Models\Cita;

auth()->loginUsingId(1);

// Limpiar todas las consultas duplicadas (hard delete)
echo "=== Limpiando consultas duplicadas ===\n";
DB::table('consultas')->delete();
echo "Tabla consultas limpiada.\n";

// Recrear una consulta por cada cita confirmada
$citas = Cita::withoutGlobalScopes()->where('estado', 'confirmada')->get();
echo "Citas confirmadas: {$citas->count()}\n\n";

foreach ($citas as $cita) {
    $consulta = Consulta::withoutGlobalScopes()->create([
        'cita_id' => $cita->id,
        'paciente_id' => $cita->paciente_id,
        'medico_id' => $cita->medico_id,
        'especialidad_id' => $cita->especialidad_id,
        'fecha_consulta' => $cita->fecha_inicio,
        'motivo_consulta' => $cita->motivo,
        'estado' => Consulta::ESTADO_POR_LLEGAR,
        'estado_changed_at' => now(),
        'empresa_id' => $cita->empresa_id,
        'sucursal_id' => $cita->sucursal_id,
        'created_by' => auth()->id(),
    ]);
    echo "Cita #{$cita->id} -> Consulta #{$consulta->id} (estado: {$consulta->estado})\n";
}

echo "\nTotal consultas: " . DB::table('consultas')->whereNull('deleted_at')->count() . "\n";
echo "DONE\n";
