<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\WhatsAppService;
use App\Services\CitaConfirmationService;
use App\Models\Cita;
use App\Models\CitaConfirmacion;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

echo "=== TEST ENVÍO DIRECTO DE CONFIRMACIÓN ===\n\n";

// Autenticar usuario para pruebas
$user = User::first();
if ($user) {
    auth()->login($user);
    echo "✅ Usuario autenticado: {$user->name}\n";
    echo "✅ Empresa: {$user->empresa->razon_social} (ID: {$user->empresa_id})\n";
    echo "✅ Código país: +{$user->empresa->codigo_pais}\n\n";
} else {
    echo "❌ No hay usuarios disponibles\n";
    exit(1);
}

// Obtener cita de prueba (buscar la más reciente)
echo "🔍 Buscando cita de prueba...\n";
$cita = Cita::with(['paciente', 'empresa'])
    ->where('estado', 'pendiente')
    ->where('fecha_inicio', '>', now()->addHours(24))
    ->latest()
    ->first();

if (!$cita) {
    echo "⚠️  No se encontró cita pendiente, creando una...\n";
    
    // Buscar datos necesarios
    $paciente = \App\Models\Paciente::first();
    $medico = \App\Models\Medico::first();
    $especialidad = \App\Models\Especialidad::first();
    
    if (!$paciente || !$medico || !$especialidad) {
        echo "❌ Faltan datos necesarios (paciente, médico o especialidad)\n";
        exit(1);
    }
    
    $cita = Cita::create([
        'paciente_id' => $paciente->id,
        'medico_id' => $medico->id,
        'especialidad_id' => $especialidad->id,
        'fecha_inicio' => now()->addDays(2),
        'fecha_fin' => now()->addDays(2)->addHour(),
        'motivo' => 'Cita de prueba para confirmación',
        'estado' => 'pendiente',
        'empresa_id' => $user->empresa_id,
        'sucursal_id' => 1,
    ]);
    
    echo "✅ Cita creada: ID {$cita->id}\n\n";
} else {
    echo "✅ Cita encontrada: ID {$cita->id}\n\n";
}

echo "📋 DETALLES DE LA CITA:\n";
echo "   Paciente: {$cita->paciente->nombre_completo}\n";
echo "   Teléfono: {$cita->paciente->telefono}\n";
echo "   Fecha: {$cita->fecha_inicio->format('d/m/Y H:i')}\n";
echo "   Estado: {$cita->estado}\n";
echo "   Empresa: {$cita->empresa->razon_social}\n\n";

// Verificar si necesita confirmación
echo "🔍 Verificando si necesita confirmación...\n";
$necesita = $cita->necesitaConfirmacion();
echo "   Resultado: " . ($necesita ? "✅ SÍ necesita" : "❌ NO necesita") . "\n\n";

if (!$necesita) {
    echo "⚠️  La cita no cumple requisitos, actualizando...\n";
    $cita->update([
        'estado' => 'pendiente',
        'fecha_inicio' => now()->addDays(2),
        'fecha_fin' => now()->addDays(2)->addHour(),
    ]);
    echo "✅ Cita actualizada\n\n";
}

// Verificar estado del servicio WhatsApp
echo "🔧 VERIFICANDO SERVICIO WHATSAPP:\n";
try {
    $whatsappService = new WhatsAppService($user->empresa_id);
    
    // Verificar si está configurado
    if (!$whatsappService->isConfigured()) {
        echo "❌ WhatsApp NO está configurado para esta empresa\n";
        echo "   Verifica: whatsapp_api_key, whatsapp_phone en empresa ID: {$user->empresa_id}\n";
    } else {
        echo "✅ WhatsApp está configurado\n";
        
        // Intentar obtener estado
        $estadoWhatsApp = $whatsappService->getStatus();
        if ($estadoWhatsApp) {
            echo "   Estado: " . ($estadoWhatsApp['status'] ?? 'desconocido') . "\n";
            echo "   Conectado: " . ($estadoWhatsApp['connected'] ?? 'desconocido') . "\n";
            echo "   Teléfono: " . ($estadoWhatsApp['phone'] ?? 'no disponible') . "\n";
        } else {
            echo "   ⚠️  No se pudo obtener estado detallado\n";
        }
    }
} catch (Exception $e) {
    echo "❌ Error verificando WhatsApp: " . $e->getMessage() . "\n";
}
echo "\n";

// Crear servicio de confirmación
echo "🔧 Creando servicio de confirmación...\n";
try {
    $service = new CitaConfirmationService();
    echo "✅ Servicio creado exitosamente\n\n";
} catch (Exception $e) {
    echo "❌ Error creando servicio: " . $e->getMessage() . "\n";
    exit(1);
}

// Verificar si ya tiene confirmación pendiente
echo "🔍 Verificando confirmaciones existentes...\n";
$confirmacionExistente = CitaConfirmacion::where('cita_id', $cita->id)
    ->where('estado', CitaConfirmacion::ESTADO_PENDIENTE)
    ->first();

if ($confirmacionExistente) {
    echo "⚠️  Ya existe confirmación pendiente (ID: {$confirmacionExistente->id})\n";
    echo "   Fecha creación: {$confirmacionExistente->created_at->format('d/m/Y H:i:s')}\n";
    echo "   Fecha envío: " . ($confirmacionExistente->fecha_envio ? $confirmacionExistente->fecha_envio->format('d/m/Y H:i:s') : 'No enviada') . "\n\n";
    $confirmacion = $confirmacionExistente;
} else {
    echo "🆕 Creando nueva confirmación...\n";
    try {
        $confirmacion = $service->iniciarConfirmacion($cita);
        echo "✅ Confirmación creada: ID {$confirmacion->id}\n";
        echo "   Token: {$confirmacion->token_confirmacion}\n\n";
    } catch (Exception $e) {
        echo "❌ Error creando confirmación: " . $e->getMessage() . "\n";
        echo "   Trace: " . substr($e->getTraceAsString(), 0, 200) . "...\n";
        exit(1);
    }
}

// Mostrar detalles completos de la confirmación
echo "📨 DETALLES DE CONFIRMACIÓN:\n";
echo "   ID: {$confirmacion->id}\n";
echo "   Token: {$confirmacion->token_confirmacion}\n";
echo "   Destinatario: {$confirmacion->destinatario}\n";
echo "   Estado: {$confirmacion->estado}\n";
echo "   Método: {$confirmacion->metodo}\n";
echo "   Fecha creación: {$confirmacion->created_at->format('d/m/Y H:i:s')}\n";
echo "   Fecha envío: " . ($confirmacion->fecha_envio ? $confirmacion->fecha_envio->format('d/m/Y H:i:s') : 'No enviada') . "\n\n";

// Intentar envío directo (iniciarConfirmacion ya incluye el envío)
echo "🚀 VERIFICANDO ENVÍO DE CONFIRMACIÓN:\n";
try {
    // La confirmación ya fue creada y enviada en iniciarConfirmacion
    if ($confirmacion->fecha_envio) {
        echo "✅ Confirmación enviada exitosamente\n";
        echo "   Fecha de envío: {$confirmacion->fecha_envio->format('d/m/Y H:i:s')}\n";
        echo "   La confirmación se envía automáticamente con iniciarConfirmacion()\n\n";
    } else {
        echo "⚠️  La confirmación NO fue enviada\n";
        echo "   Esto podría indicar un problema en el proceso\n\n";
    }
} catch (Exception $e) {
    echo "❌ Error en envío: " . $e->getMessage() . "\n";
    echo "   Trace: " . substr($e->getTraceAsString(), 0, 300) . "...\n\n";
}

// Formatear número para búsqueda en base de datos WhatsApp
echo "\n📱 FORMATEANDO NÚMERO PARA BÚSQUEDA:\n";

// Función para formatear número al estilo de la aplicación (formato JID WhatsApp)
function formatearNumeroParaWhatsApp($telefono, $codigoPais = '58') {
    $limpio = preg_replace('/\D/', '', $telefono);
    
    // Quitar el 0 inicial si existe
    if (str_starts_with($limpio, '0')) {
        $limpio = substr($limpio, 1);
    }
    
    // Agregar código de país si no lo tiene
    if (!str_starts_with($limpio, $codigoPais) && strlen($limpio) === 10) {
        $limpio = $codigoPais . $limpio;
    }
    
    // Formato JID de WhatsApp
    return $limpio . '@s.whatsapp.net';
}

$codigoPais = $user->empresa->codigo_pais ?? '58';
$numeroFormateado = formatearNumeroParaWhatsApp($confirmacion->destinatario, $codigoPais);
echo "   Número original: {$confirmacion->destinatario}\n";
echo "   Código país: +{$codigoPais}\n";
echo "   Formato JID WhatsApp: {$numeroFormateado}\n\n";

// Verificar mensajes en base de datos
echo "🔍 VERIFICANDO MENSAJES EN BASE DE DATOS:\n";
try {
    // Buscar usando el formato JID de WhatsApp
    $mensajes = DB::connection('whatsapp_api')
        ->table('whatsapp_messages')
        ->where('to', $numeroFormateado)
        ->orWhere('from', $numeroFormateado)
        ->orderBy('id', 'desc')
        ->limit(5)
        ->get();
    
    if ($mensajes->count() > 0) {
        echo "✅ Encontrados {$mensajes->count()} mensajes para {$numeroFormateado}\n";
        foreach ($mensajes as $mensaje) {
            echo "\n   📱 Mensaje ID: {$mensaje->id}\n";
            echo "      Estado: {$mensaje->status}\n";
            echo "      Tipo: " . ($mensaje->message_type ?? 'text') . "\n";
            echo "      De: {$mensaje->from}\n";
            echo "      Para: {$mensaje->to}\n";
            echo "      Fecha: " . ($mensaje->createdAt ?? $mensaje->timestamp ?? 'N/A') . "\n";
            echo "      Contenido: " . substr($mensaje->message_content ?? '', 0, 80) . "...\n";
            
            if (isset($mensaje->error_message)) {
                echo "      Error: {$mensaje->error_message}\n";
            }
        }
    } else {
        echo "⚠️  No se encontraron mensajes para {$numeroFormateado}\n";
    }
} catch (Exception $e) {
    echo "❌ Error consultando mensajes: " . $e->getMessage() . "\n";
}

// Verificar logs recientes
echo "\n📜 VERIFICANDO LOGS RECIENTES:\n";
try {
    $logFile = storage_path('logs/laravel.log');
    if (file_exists($logFile)) {
        $logs = file_get_contents($logFile);
        $lines = explode("\n", $logs);
        $recentLogs = array_slice($lines, -20); // Últimas 20 líneas
        
        $relevantLogs = [];
        foreach ($recentLogs as $line) {
            if (stripos($line, 'confirmacion') !== false || 
                stripos($line, 'whatsapp') !== false ||
                stripos($line, 'cita') !== false) {
                $relevantLogs[] = $line;
            }
        }
        
        if (count($relevantLogs) > 0) {
            echo "✅ Encontrados " . count($relevantLogs) . " logs relevantes\n";
            foreach (array_slice($relevantLogs, -5) as $log) {
                echo "\n   📝 " . trim($log) . "\n";
            }
        } else {
            echo "⚠️  No se encontraron logs relevantes en las últimas 20 líneas\n";
        }
    } else {
        echo "⚠️  Archivo de logs no encontrado\n";
    }
} catch (Exception $e) {
    echo "❌ Error leyendo logs: " . $e->getMessage() . "\n";
}

// Verificar estado actual de la confirmación
echo "\n🔄 ESTADO ACTUAL DE CONFIRMACIÓN:\n";
$confirmacion->refresh();
echo "   Estado: {$confirmacion->estado}\n";
echo "   Fecha envío: " . ($confirmacion->fecha_envio ? $confirmacion->fecha_envio->format('d/m/Y H:i:s') : 'No enviada') . "\n";
echo "   Respuesta: " . ($confirmacion->respuesta ?? 'Sin respuesta') . "\n";
echo "   Fecha respuesta: " . ($confirmacion->fecha_respuesta ? $confirmacion->fecha_respuesta->format('d/m/Y H:i:s') : 'Sin respuesta') . "\n\n";

// Si no fue enviada, intentar reenviar
if ($confirmacion->estado === 'pendiente' && !$confirmacion->fecha_envio) {
    echo "🔄 INTENTANDO REENVÍO...\n";
    
    // Verificar si puede reintentar
    if ($confirmacion->puedeReintentar()) {
        echo "   ✅ La confirmación puede reintentarse ({$confirmacion->intentos}/{$confirmacion->max_intentos} intentos)\n";
        try {
            $resultado = $service->reintentarConfirmacion($confirmacion);
            echo "   ✅ Reenvío: " . ($resultado ? "ÉXITO" : "FALLÓ") . "\n";
            
            // Refrescar para ver cambios
            $confirmacion->refresh();
            echo "   Nuevo estado: {$confirmacion->estado}\n";
            echo "   Nueva fecha envío: " . ($confirmacion->fecha_envio ? $confirmacion->fecha_envio->format('d/m/Y H:i:s') : 'No enviada') . "\n\n";
        } catch (Exception $e) {
            echo "   ❌ Error en reenvío: " . $e->getMessage() . "\n\n";
        }
    } else {
        echo "   ⚠️  La confirmación NO puede reintentarse\n";
        echo "   Intentos: {$confirmacion->intentos}/{$confirmacion->max_intentos}\n\n";
    }
}

echo "=== FIN TEST ===\n\n";

echo "💡 RECOMENDACIONES:\n";
echo "   1. Ejecuta: php artisan queue:work --once\n";
echo "   2. Revisa logs detallados en: storage/logs/laravel.log\n";
echo "   3. Verifica WhatsApp API en: http://localhost:3000\n";
echo "   4. Prueba teléfono real con: php test_whatsapp_directo.php\n";