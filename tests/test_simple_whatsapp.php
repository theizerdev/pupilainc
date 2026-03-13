<?php

require_once __DIR__ . '/vendor/autoload.php';

// Inicializar Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;

echo "🧪 TEST SIMPLE WHATSAPP DOCUMENT\n";
echo "=================================\n\n";

// Configuración
$apiUrl = config('whatsapp.api_url', 'http://82.165.213.124:8092');
$apiKey = config('whatsapp.api_key', 'test-api-key-vargas-centro');
$phone = '584121234567';

// Crear archivo de prueba simple
$testFile = sys_get_temp_dir() . '/test_' . uniqid() . '.txt';
file_put_contents($testFile, "Este es un archivo de prueba para WhatsApp\nFecha: " . date('Y-m-d H:i:s'));

echo "📁 Archivo creado: $testFile\n";
echo "📏 Tamaño: " . number_format(filesize($testFile) / 1024, 2) . " KB\n\n";

// Intentar enviar el documento
try {
    echo "📤 Enviando documento a WhatsApp...\n";
    
    $response = Http::withHeaders([
        'X-API-Key' => $apiKey
    ])->timeout(30)->attach('document', file_get_contents($testFile), 'test.txt')
      ->post($apiUrl . '/api/whatsapp/send-document', [
          'to' => $phone . '@s.whatsapp.net',
          'caption' => 'Archivo de prueba simple'
      ]);
    
    echo "📊 Respuesta del servidor:\n";
    echo "   Código: " . $response->status() . "\n";
    echo "   Éxito: " . ($response->successful() ? 'Sí' : 'No') . "\n";
    
    if ($response->successful()) {
        $data = $response->json();
        echo "   Mensaje ID: " . ($data['messageId'] ?? 'No disponible') . "\n";
        echo "   Estado: " . ($data['status'] ?? 'No especificado') . "\n";
    } else {
        echo "   Error: " . $response->body() . "\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "   Tipo: " . get_class($e) . "\n";
}

// Limpiar
if (file_exists($testFile)) {
    unlink($testFile);
    echo "\n🗑️ Archivo temporal eliminado\n";
}

echo "\n✅ Test completado\n";