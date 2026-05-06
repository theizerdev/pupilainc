<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Cita;
use App\Models\Consulta;

$citas = Cita::where('estado', 'confirmada')->get();
echo "Citas confirmadas: " . $citas->count() . "\n";

$creadas = 0;
foreach ($citas as $cita) {
    $existe = Consulta::where('cita_id', $cita->id)->exists();
    echo "Cita #{$cita->id} - Paciente: {$cita->paciente->nombre_completo} -> Consulta: " . ($existe ? 'YA EXISTE' : 'CREANDO...') . "\n";
    
    if (!$existe) {
        Consulta::create([
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
        ]);
        $creadas++;
    }
}

echo "\nConsultas creadas: {$creadas}\n";
echo "Total consultas ahora: " . Consulta::count() . "\n";
