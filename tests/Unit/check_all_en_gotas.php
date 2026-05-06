<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Verificando Todas las Consultas en 'en_gotas' ===\n\n";

$consultas = App\Models\Consulta::where('estado', 'en_gotas')->get();

echo "Total consultas en estado 'en_gotas': {$consultas->count()}\n\n";

foreach ($consultas as $i => $consulta) {
    echo "Consulta #" . ($i + 1) . " (ID: {$consulta->id}):\n";
    $pacienteNombre = $consulta->paciente ? $consulta->paciente->nombre_completo : 'N/A';
    echo "  Paciente: {$pacienteNombre}\n";
    
    $gotaAplicada = $consulta->gotasAplicadas()
        ->where('estado', 'aplicada')
        ->latest()
        ->first();
    
    if ($gotaAplicada) {
        echo "  Gota ID: {$gotaAplicada->id}\n";
        echo "  tiempo_espera: {$gotaAplicada->tiempo_espera}\n";
        echo "  gota->updated_at: {$gotaAplicada->updated_at}\n";
        
        $fechaGota = $gotaAplicada->updated_at ?? $gotaAplicada->created_at;
        $fechaEstado = $consulta->estado_changed_at ?? $consulta->updated_at;
        
        if ($fechaGota && $fechaEstado) {
            $fechaInicio = \Carbon\Carbon::parse($fechaGota)->gt(\Carbon\Carbon::parse($fechaEstado)) 
                ? $fechaGota 
                : $fechaEstado;
        } else {
            $fechaInicio = $fechaGota ?? $fechaEstado;
        }
        
        $minutosTranscurridos = \Carbon\Carbon::parse($fechaInicio)->diffInMinutes(now());
        $minutosRestantes = max(0, $gotaAplicada->tiempo_espera - $minutosTranscurridos);
        
        echo "  fechaInicio usada: {$fechaInicio}\n";
        echo "  minutosTranscurridos: {$minutosTranscurridos}\n";
        echo "  minutosRestantes: {$minutosRestantes}\n";
        
        // Calcular display
        $totalSegundos = round($minutosRestantes * 60);
        $mins = floor($totalSegundos / 60);
        $secs = $totalSegundos % 60;
        echo "  Display: {$mins}:{$secs}\n";
    } else {
        echo "  ❌ No tiene gotas aplicadas\n";
    }
    
    echo "\n";
}
