<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Diagnóstico de Médicos y User_ID ===\n\n";

// Verificar consultas en estado "en_gotas"
$consultasEnGotas = App\Models\Consulta::where('estado', 'en_gotas')->get();

echo "Consultas en estado 'en_gotas': {$consultasEnGotas->count()}\n\n";

foreach ($consultasEnGotas as $consulta) {
    echo "Consulta #{$consulta->id}:\n";
    $pacienteNombre = $consulta->paciente ? $consulta->paciente->nombre_completo : 'N/A';
    echo "  - Paciente: {$pacienteNombre}\n";
    echo "  - Médico ID: {$consulta->medico_id}\n";

    if ($consulta->medico) {
        echo "  - Médico Nombre: Dr(a). {$consulta->medico->nombres} {$consulta->medico->apellidos}\n";
        $medicoUserId = $consulta->medico->user_id;
        echo "  - Médico User ID: " . ($medicoUserId ? $medicoUserId : 'NULL') . "\n";

        if ($medicoUserId) {
            $user = App\Models\User::find($medicoUserId);
            if ($user) {
                echo "  - User Email: {$user->email}\n";
                echo "  - User Name: {$user->name}\n";
            } else {
                echo "  - ⚠️  WARNING: user_id existe pero no se encontró el usuario\n";
            }
        } else {
            echo "  - ❌ PROBLEM: El médico NO tiene user_id asociado\n";
        }
    } else {
        echo "  - ❌ No hay médico asignado\n";
    }

    echo "\n";
}

// Estadísticas generales
$totalMedicos = App\Models\Medico::count();
$medicosConUser = App\Models\Medico::whereNotNull('user_id')->count();
$medicosSinUser = App\Models\Medico::whereNull('user_id')->count();

echo "=== Estadísticas Generales ===\n";
echo "Total médicos: {$totalMedicos}\n";
echo "Médicos con user_id: {$medicosConUser}\n";
echo "Médicos sin user_id: {$medicosSinUser}\n";
echo "Porcentaje con cuenta: " . round(($medicosConUser / max(1, $totalMedicos)) * 100, 2) . "%\n";
