<?php

// Script de debugging mejorado para WhatsApp

require_once __DIR__ . '/vendor/autoload.php';

// Inicializar Laravel correctamente
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;

echo "🔍 DEBUGGING WHATSAPP API v2\n";
echo "=============================\n\n";

// Obtener configuración
try {
    $apiUrl = config('whatsapp.api_url', 'http://82.165.213.124:8092');
    $apiKey = config('whatsapp.api_key', 'test-api-key-vargas-centro');
    
    echo "📍 Configuración WhatsApp:\n";
    echo "   API URL: $apiUrl\n";
    echo "   API Key: " . substr($apiKey, 0, 15) . "...\n\n";
    
} catch (\Exception $e) {
    echo "❌ Error obteniendo configuración: " . $e->getMessage() . "\n";
    echo "   Usando valores por defecto\n\n";
    $apiUrl = 'http://82.165.213.124:8092';
    $apiKey = 'test-api-key-vargas-centro';
}

// 1. Verificar estado del servicio
echo "1. Verificando estado del servicio WhatsApp...\n";
try {
    $response = Http::withHeaders([
        'X-API-Key' => $apiKey
    ])->timeout(10)->get($apiUrl . '/api/whatsapp/status');
    
    if ($response->successful()) {
        echo "✅ Servicio WhatsApp está activo\n";
        $data = $response->json();
        echo "   Estado: " . ($data['connectionState'] ?? 'desconocido') . "\n";
        echo "   Conectado: " . (isset($data['isConnected']) && $data['isConnected'] ? 'Sí' : 'No') . "\n";
        echo "   Empresa: " . ($data['company'] ?? 'No especificada') . "\n";
    } else {
        echo "❌ Error al verificar estado:\n";
        echo "   Código: " . $response->status() . "\n";
        echo "   Respuesta: " . $response->body() . "\n";
    }
} catch (\Exception $e) {
    echo "❌ Error de conexión: " . $e->getMessage() . "\n";
    echo "   Verifica que el servicio WhatsApp esté ejecutándose en: $apiUrl\n";
}

echo "\n";

// 2. Probar envío de mensaje simple
echo "2. Probando envío de mensaje simple...\n";
try {
    $response = Http::withHeaders([
        'X-API-Key' => $apiKey,
        'Content-Type' => 'application/json'
    ])->timeout(10)->post($apiUrl . '/api/whatsapp/send', [
        'to' => '584121234567@s.whatsapp.net',
        'message' => '🧪 Mensaje de prueba desde debugging - ' . date('H:i:s'),
        'type' => 'text'
    ]);
    
    if ($response->successful()) {
        echo "✅ Mensaje enviado exitosamente\n";
        $responseData = $response->json();
        echo "   ID del mensaje: " . ($responseData['messageId'] ?? 'No disponible') . "\n";
        echo "   Empresa: " . ($responseData['company'] ?? 'No especificada') . "\n";
    } else {
        echo "❌ Error al enviar mensaje:\n";
        echo "   Código: " . $response->status() . "\n";
        echo "   Respuesta: " . $response->body() . "\n";
    }
} catch (\Exception $e) {
    echo "❌ Error de conexión: " . $e->getMessage() . "\n";
}

echo "\n";

// 3. Verificar logs del servidor
echo "3. Verificando logs del servidor WhatsApp...\n";
$logFile = __DIR__ . '/resources/js/whatsapp/logs/app.log';
if (file_exists($logFile)) {
    $logs = file_get_contents($logFile);
    $logLines = explode("\n", $logs);
    $recentLogs = array_slice(array_filter($logLines), -5); // Últimas 5 líneas no vacías
    
    if (!empty($recentLogs)) {
        echo "📋 Últimas entradas del log:\n";
        foreach ($recentLogs as $log) {
            echo "   " . $log . "\n";
        }
    } else {
        echo "⚠️ No hay entradas recientes en el log\n";
    }
} else {
    echo "⚠️ Archivo de log no encontrado en: $logFile\n";
}

echo "\n";

// 4. Verificar rutas disponibles
echo "4. Verificando rutas del API WhatsApp...\n";
$endpoints = [
    '/api/whatsapp/status' => 'GET',
    '/api/whatsapp/send' => 'POST',
    '/api/whatsapp/send-document' => 'POST',
    '/api/whatsapp/messages' => 'GET'
];

foreach ($endpoints as $endpoint => $method) {
    try {
        if ($method === 'GET') {
            $response = Http::withHeaders(['X-API-Key' => $apiKey])->timeout(5)->get($apiUrl . $endpoint);
        } else {
            // Para POST, solo verificar que el endpoint existe (no enviar datos)
            $response = Http::withHeaders(['X-API-Key' => $apiKey])->timeout(5)->post($apiUrl . $endpoint, []);
        }
        
        echo "   $method $endpoint: ";
        if ($response->status() === 404) {
            echo "❌ No encontrado\n";
        } elseif ($response->status() === 405) {
            echo "✅ Existe (método no permitido)\n";
        } elseif ($response->successful() || $response->status() < 500) {
            echo "✅ Disponible\n";
        } else {
            echo "⚠️ Error " . $response->status() . "\n";
        }
    } catch (\Exception $e) {
        echo "   $method $endpoint: ❌ Error de conexión\n";
    }
}

echo "\n";
echo "🔍 Debugging completado.\n";
echo "⏰ Hora: " . date('d/m/Y H:i:s') . "\n";
echo "💡 Sugerencias:\n";
echo "   - Verifica que el servicio WhatsApp esté ejecutándose\n";
echo "   - Asegúrate de que el número de teléfono esté registrado en WhatsApp\n";
echo "   - Revisa los logs del servidor para más detalles\n";