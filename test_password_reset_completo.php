<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Hash;

echo "=== PRUEBA DE RESET DE CONTRASEÑA COMPLETO ===\n\n";

// Buscar usuario de prueba
$user = User::find(4);
if (!$user) {
    echo "❌ Usuario no encontrado\n";
    exit(1);
}

echo "Usuario: {$user->name} (ID: {$user->id})\n";
echo "Empresa: " . ($user->empresa ? $user->empresa->nombre : 'Sin empresa') . "\n";
echo "Teléfono: {$user->phone}\n\n";

// Generar contraseña temporal
$newPassword = str_pad(mt_rand(0, 99999999), 8, '0', STR_PAD_LEFT);

// Actualizar contraseña
$user->update([
    'password' => Hash::make($newPassword)
]);

// Preparar mensaje mejorado
$message = "🏥 *Clínica Médica*\n\n";
$message .= "Hola *{$user->name}*,\n\n";
$message .= "✅ Hemos generado una nueva contraseña para tu cuenta.\n\n";
$message .= "🔑 *Contraseña temporal:* {$newPassword}\n\n";
$message .= "📋 *Importante:*\n";
$message .= "• Ingresa con esta contraseña\n";
$message .= "• Cámbiala inmediatamente en tu perfil\n";
$message .= "• Esta contraseña es temporal por seguridad\n\n";
$message .= "¿Necesitas ayuda? Contacta a soporte técnico.\n\n";
$message .= "Gracias por confiar en nosotros. 😊";

echo "Mensaje generado:\n";
echo str_repeat("-", 50) . "\n";
echo $message . "\n";
echo str_repeat("-", 50) . "\n\n";

// Formatear teléfono
$telefonoFormateado = '+58' . ltrim($user->phone, '0');

try {
    // Enviar por WhatsApp usando empresa del usuario
    $whatsApp = new WhatsAppService($user->empresa_id);
    
    echo "Enviando mensaje...\n";
    $result = $whatsApp->sendMessage($telefonoFormateado, $message, true);
    
    if ($result && isset($result['success']) && $result['success']) {
        echo "✅ ¡Mensaje enviado exitosamente!\n";
        echo "   Message ID: {$result['messageId']}\n\n";
        
        // Verificar traducción
        $mensajeExito = __('auth_ui.password_reset_success_whatsapp');
        echo "Mensaje de éxito (traducción): {$mensajeExito}\n";
        
    } else {
        echo "❌ Error al enviar mensaje\n";
        echo "   Resultado: " . json_encode($result, JSON_PRETTY_PRINT) . "\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Excepción: {$e->getMessage()}\n";
}

echo "\n=== FIN PRUEBA ===\n";