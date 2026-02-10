<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\CitaConfirmationBotonesService;
use App\Services\WhatsAppService;
use App\Models\CitaConfirmacion;
use App\Models\User;
use Illuminate\Support\Facades\Log;

echo "=== TEST ENVÍO DE CONFIRMACIÓN CON BOTONES ===\n\n";

// Autenticar usuario para pruebas
$user = User::first();
if ($user) {
    auth()->login($user);
    echo "✅ Usuario autenticado: {$user->name}\n";
    echo "✅ Empresa: {$user->empresa->razon_social} (ID: {$user->empresa_id})\n\n";
} else {
    echo "❌ No hay usuarios disponibles\n";
    exit(1);
}

// Obtener una confirmación pendiente
echo "🔍 Buscando confirmación pendiente...\n";
$confirmacion = CitaConfirmacion::with(['cita.paciente', 'cita.medico', 'cita.especialidad'])
    ->where('estado', 'pendiente')
    ->where('metodo', 'whatsapp')
    ->whereNull('fecha_envio')
    ->latest()
    ->first();

if (!$confirmacion) {
    echo "❌ No se encontró confirmación pendiente\n";
    echo "💡 Creando una confirmación de prueba...\n\n";
    
    // Obtener cita para crear confirmación
    $cita = \App\Models\Cita::with(['paciente', 'medico', 'especialidad'])
        ->where('estado', 'pendiente')
        ->first();
        
    if (!$cita) {
        echo "❌ No hay citas disponibles\n";
        exit(1);
    }
    
    // Crear confirmación manualmente
    $confirmacion = CitaConfirmacion::create([
        'cita_id' => $cita->id,
        'metodo' => 'whatsapp',
        'destinatario' => $cita->paciente->telefono ?? '04241703465',
        'mensaje_enviado' => 'Mensaje de prueba',
        'token_confirmacion' => bin2hex(random_bytes(32)),
        'estado' => 'pendiente',
        'empresa_id' => $user->empresa_id,
        'sucursal_id' => 1,
        'intentos' => 0,
        'max_intentos' => 3,
        'created_by' => $user->id,
        'fecha_envio' =>   now(),
    ]);
    
    echo "✅ Confirmación creada: ID {$confirmacion->id}\n";
    echo "   Token: {$confirmacion->token_confirmacion}\n";
    echo "   Destinatario: {$confirmacion->destinatario}\n\n";
}

// Mostrar detalles de la confirmación
echo "📋 DETALLES DE CONFIRMACIÓN:\n";
echo "   ID: {$confirmacion->id}\n";
echo "   Cita ID: {$confirmacion->cita_id}\n";
echo "   Paciente: {$confirmacion->cita->paciente->nombre_completo}\n";
echo "   Teléfono: {$confirmacion->destinatario}\n";
echo "   Estado: {$confirmacion->estado}\n";
echo "   Fecha: {$confirmacion->cita->fecha_inicio->format('d/m/Y H:i')}\n";
echo "   Médico: Dr(a). {$confirmacion->cita->medico->nombre_completo}\n";
echo "   Especialidad: {$confirmacion->cita->especialidad->nombre}\n\n";

// Verificar servicio WhatsApp
echo "🔧 VERIFICANDO SERVICIO WHATSAPP:\n";
try {
    $whatsAppService = new WhatsAppService($user->empresa_id);
    
    if (!$whatsAppService->isConfigured()) {
        echo "❌ WhatsApp NO está configurado\n";
        echo "   Verifica: whatsapp_api_key en empresa ID: {$user->empresa_id}\n";
        exit(1);
    }
    
    echo "✅ WhatsApp está configurado\n";
    echo "   Company ID: {$whatsAppService->getCompanyId()}\n\n";
    
} catch (Exception $e) {
    echo "❌ Error verificando WhatsApp: " . $e->getMessage() . "\n";
    exit(1);
}

// Crear servicio de botones
echo "🔧 CREANDO SERVICIO DE BOTONES:\n";
try {
    $botonesService = new CitaConfirmationBotonesService($whatsAppService);
    echo "✅ Servicio de botones creado exitosamente\n\n";
} catch (Exception $e) {
    echo "❌ Error creando servicio de botones: " . $e->getMessage() . "\n";
    exit(1);
}

// Construir mensaje con botones
echo "📨 CONSTRUYENDO MENSAJE CON BOTONES:\n";
try {
    // Usar reflexión para acceder al método privado
    $reflection = new ReflectionClass($botonesService);
    $method = $reflection->getMethod('construirMensajeConBotonesTexto');
    $method->setAccessible(true);
    
    $mensaje = $method->invoke($botonesService, $confirmacion);
    
    echo "✅ Mensaje construido exitosamente\n";
    echo "   Longitud: " . strlen($mensaje) . " caracteres\n";
    echo "   Contenido:\n";
    echo str_repeat("-", 50) . "\n";
    echo $mensaje . "\n";
    echo str_repeat("-", 50) . "\n\n";
    
} catch (Exception $e) {
    echo "❌ Error construyendo mensaje: " . $e->getMessage() . "\n";
    echo "   Trace: " . substr($e->getTraceAsString(), 0, 200) . "...\n\n";
}

// Formatear número de teléfono
echo "📞 FORMATEANDO NÚMERO DE TELÉFONO:\n";
try {
    $reflection = new ReflectionClass($botonesService);
    $method = $reflection->getMethod('formatearTelefono');
    $method->setAccessible(true);
    
    $telefonoFormateado = $method->invoke($botonesService, $confirmacion->destinatario);
    
    echo "   Original: {$confirmacion->destinatario}\n";
    echo "   Formateado: {$telefonoFormateado}\n\n";
    
} catch (Exception $e) {
    echo "❌ Error formateando teléfono: " . $e->getMessage() . "\n\n";
}

// Intentar envío con botones
echo "🚀 INTENTANDO ENVÍO CON BOTONES:\n";
try {
    $resultado = $botonesService->enviarConfirmacionConBotones($confirmacion);
    
    if ($resultado) {
        echo "✅ ENVÍO EXITOSO\n";
        echo "   La confirmación fue enviada con botones\n";
        
        // Refrescar para ver cambios
        $confirmacion->refresh();
        echo "   Nuevo estado: {$confirmacion->estado}\n";
        echo "   Fecha envío: " . ($confirmacion->fecha_envio ? $confirmacion->fecha_envio->format('d/m/Y H:i:s') : 'No registrada') . "\n";
        echo "   Intentos: {$confirmacion->intentos}\n\n";
        
    } else {
        echo "❌ ENVÍO FALLIDO\n";
        echo "   El servicio de botones no pudo enviar el mensaje\n\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error en envío: " . $e->getMessage() . "\n";
    echo "   Trace: " . substr($e->getTraceAsString(), 0, 300) . "...\n\n";
}

// Verificar logs recientes
echo "📜 VERIFICANDO LOGS RECIENTES:\n";
try {
    $logFile = storage_path('logs/laravel.log');
    if (file_exists($logFile)) {
        $logs = file($logFile);
        $recentLogs = array_slice($logs, -30); // Últimas 30 líneas
        
        $relevantLogs = [];
        foreach ($recentLogs as $line) {
            if (stripos($line, 'botones') !== false || 
                stripos($line, 'confirmacion') !== false ||
                stripos($line, 'whatsapp') !== false) {
                $relevantLogs[] = $line;
            }
        }
        
        if (count($relevantLogs) > 0) {
            echo "✅ Encontrados " . count($relevantLogs) . " logs relevantes\n";
            foreach (array_slice($relevantLogs, -5) as $log) {
                echo "   📝 " . trim($log) . "\n";
            }
        } else {
            echo "⚠️  No se encontraron logs relevantes\n";
        }
    } else {
        echo "⚠️  Archivo de logs no encontrado\n";
    }
} catch (Exception $e) {
    echo "❌ Error leyendo logs: " . $e->getMessage() . "\n";
}

// Estado final
echo "\n🔄 ESTADO FINAL DE CONFIRMACIÓN:\n";
$confirmacion->refresh();
echo "   Estado: {$confirmacion->estado}\n";
echo "   Fecha envío: " . ($confirmacion->fecha_envio ? $confirmacion->fecha_envio->format('d/m/Y H:i:s') : 'No enviada') . "\n";
echo "   Intentos: {$confirmacion->intentos}\n";
echo "   Respuesta: " . ($confirmacion->respuesta_recibida ?? 'Sin respuesta') . "\n\n";

echo "=== FIN TEST BOTONES ===\n\n";

echo "💡 RECOMENDACIONES:\n";
echo "   1. Verifica los logs detallados en: storage/logs/laravel.log\n";
echo "   2. Prueba con diferentes números de teléfono\n";
echo "   3. Verifica la configuración de WhatsApp API\n";
echo "   4. Considera usar mensajes de texto como fallback\n";
echo "   5. Monitorea las respuestas de los pacientes\n";