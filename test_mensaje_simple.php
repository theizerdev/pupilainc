<?php

// Inicializar Laravel
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Cita;
use App\Services\CitaNotificationService;

try {
    echo "🧪 Probando mensaje simple de WhatsApp...\n\n";
    
    // Buscar una cita existente
    $cita = Cita::with(['paciente', 'medico'])->first();
    
    if (!$cita) {
        echo "❌ No se encontró ninguna cita\n";
        exit(1);
    }
    
    echo "📋 Usando cita ID: {$cita->id}\n";
    echo "- Paciente: {$cita->paciente->nombre_completo}\n";
    echo "- Teléfono: {$cita->paciente->telefono}\n";
    echo "- Fecha: {$cita->fecha_inicio->format('d/m/Y H:i')}\n\n";
    
    // Crear un mensaje simple sin emojis ni formato
    $mensajeSimple = "Hola {$cita->paciente->nombre}, tiene una cita el {$cita->fecha_inicio->format('d/m')} a las {$cita->fecha_inicio->format('H:i')}. Saludos.";
    
    echo "📤 Enviando mensaje simple:\n";
    echo "Mensaje: {$mensajeSimple}\n\n";
    
    // Obtener el servicio y enviar directamente
    $service = CitaNotificationService::forCompany($cita->empresa_id);
    
    // Formatear el teléfono
    $telefonoFormateado = $service->formatearTelefono($cita->paciente->telefono);
    echo "📱 Teléfono formateado: {$telefonoFormateado}\n\n";
    
    // Enviar el mensaje simple
    $resultado = $service->enviar($telefonoFormateado, $mensajeSimple);
    
    echo "✅ Resultado: " . ($resultado ? 'ÉXITO' : 'FALLÓ') . "\n";
    
    if (!$resultado) {
        echo "❌ El mensaje no se pudo enviar. Revisa los logs.\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error: {$e->getMessage()}\n";
    echo "Archivo: {$e->getFile()}\n";
    echo "Línea: {$e->getLine()}\n";
    echo "Trace: {$e->getTraceAsString()}\n";
}

echo "\n✅ Prueba completada\n";