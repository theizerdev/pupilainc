<?php

// Script de prueba para verificar el servicio de notificaciones de citas
require_once __DIR__ . '/vendor/autoload.php';

use App\Models\Cita;
use App\Services\CitaNotificationService;
use Illuminate\Support\Facades\Log;

// Configurar el entorno
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🧪 Probando servicio de notificaciones de citas...\n\n";

// Buscar una cita reciente para probar
try {
    $cita = Cita::with(['paciente.tutor', 'medico', 'especialidad'])
        ->where('estado', 'confirmada')
        ->whereDate('fecha_inicio', '>=', now()->subDays(7))
        ->first();
    
    if (!$cita) {
        echo "❌ No se encontró ninguna cita confirmada reciente para probar\n";
        echo "Creando una cita de prueba...\n";
        
        // Crear datos de prueba
        $paciente = \App\Models\Paciente::first();
        $medico = \App\Models\Medico::first();
        $especialidad = \App\Models\Especialidad::first();
        
        if (!$paciente || !$medico || !$especialidad) {
            echo "❌ No hay datos suficientes para crear una cita de prueba\n";
            exit(1);
        }
        
        $cita = new Cita([
            'paciente_id' => $paciente->id,
            'medico_id' => $medico->id,
            'especialidad_id' => $especialidad->id,
            'fecha_inicio' => now()->addHours(2),
            'fecha_fin' => now()->addHours(3),
            'estado' => 'confirmada',
            'empresa_id' => 1,
            'motivo' => 'Cita de prueba para notificaciones',
        ]);
        $cita->save();
        
        echo "✅ Cita de prueba creada con ID: {$cita->id}\n";
    } else {
        echo "✅ Usando cita existente ID: {$cita->id}\n";
    }
    
    echo "\n📊 Información de la cita:\n";
    echo "- Paciente: {$cita->paciente->nombre} {$cita->paciente->apellido}\n";
    echo "- Es menor: " . ($cita->paciente->es_menor ? 'Sí' : 'No') . "\n";
    echo "- Teléfono paciente: " . ($cita->paciente->telefono ?: 'No tiene') . "\n";
    
    if ($cita->paciente->es_menor && $cita->paciente->tutor) {
        echo "- Tutor: {$cita->paciente->tutor->nombre} {$cita->paciente->tutor->apellido}\n";
        echo "- Teléfono tutor: " . ($cita->paciente->tutor->telefono ?: 'No tiene') . "\n";
    }
    
    echo "- Médico: {$cita->medico->nombre} {$cita->medico->apellido}\n";
    echo "- Teléfono médico: " . ($cita->medico->telefono ?: 'No tiene') . "\n";
    echo "- Empresa ID: {$cita->empresa_id}\n";
    
    // Probar el servicio de notificaciones
    echo "\n📤 Enviando notificaciones...\n";
    
    $service = CitaNotificationService::forCompany($cita->empresa_id);
    $resultado = $service->notificarNuevaCita($cita);
    
    echo "\n📊 Resultado de la notificación:\n";
    echo "- Éxito: " . ($resultado['success'] ? 'Sí' : 'No') . "\n";
    echo "- Mensaje: {$resultado['message']}\n";
    
    if (!empty($resultado['errors'])) {
        echo "- Errores:\n";
        foreach ($resultado['errors'] as $error) {
            echo "  • {$error}\n";
        }
    }
    
    if ($resultado['confirmacion_incluida']) {
        echo "- Confirmación incluida: Sí\n";
    }
    
    // Verificar logs
    echo "\n📋 Verificando logs recientes...\n";
    $logFile = storage_path('logs/laravel.log');
    if (file_exists($logFile)) {
        $logs = `tail -n 20 "{$logFile}" | grep -i "citanotificationservice"`;
        if ($logs) {
            echo "Logs encontrados:\n{$logs}\n";
        } else {
            echo "No se encontraron logs recientes de CitaNotificationService\n";
        }
    }
    
} catch (\Exception $e) {
    echo "❌ Error: {$e->getMessage()}\n";
    echo "Archivo: {$e->getFile()}\n";
    echo "Línea: {$e->getLine()}\n";
    echo "Trace:\n{$e->getTraceAsString()}\n";
}

echo "\n✅ Prueba completada\n";