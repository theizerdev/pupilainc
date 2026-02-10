<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\CitaConfirmationBotonesService;
use App\Services\WhatsAppService;
use App\Models\CitaConfirmacion;
use App\Models\User;

echo "=== TEST MENSAJE INTERACTIVO ===\n\n";

// Autenticar usuario
$user = User::first();
auth()->login($user);
echo "✅ Usuario autenticado: {$user->name}\n\n";

// Obtener confirmación
$confirmacion = CitaConfirmacion::with(['cita.paciente', 'cita.medico', 'cita.especialidad'])->find(3);

if (!$confirmacion) {
    echo "❌ No se encontró confirmación\n";
    exit(1);
}

echo "📋 Confirmación ID: {$confirmacion->id}\n";
echo "   Paciente: {$confirmacion->cita->paciente->nombre_completo}\n\n";

// Crear servicios
$whatsAppService = new WhatsAppService($user->empresa_id);
$botonesService = new CitaConfirmationBotonesService($whatsAppService);

// Usar reflexión para acceder al método privado
$reflection = new ReflectionClass($botonesService);
$method = $reflection->getMethod('construirMensajeInteractivo');
$method->setAccessible(true);

$mensajeInteractivo = $method->invoke($botonesService, $confirmacion);

echo "🔧 ESTRUCTURA DEL MENSAJE INTERACTIVO:\n";
echo json_encode($mensajeInteractivo, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

// Probar envío directo
echo "🚀 PROBANDO ENVÍO DIRECTO:\n";
$telefonoFormateado = '+584241703465'; // Número de prueba

$resultado = $whatsAppService->sendInteractiveMessage($telefonoFormateado, $mensajeInteractivo);

if ($resultado) {
    echo "✅ Mensaje interactivo enviado exitosamente\n";
    echo "   Message ID: " . ($resultado['messageId'] ?? 'N/A') . "\n";
} else {
    echo "❌ Falló el envío del mensaje interactivo\n";
    echo "   Resultado: " . json_encode($resultado) . "\n";
}

echo "\n=== FIN TEST INTERACTIVO ===\n";
