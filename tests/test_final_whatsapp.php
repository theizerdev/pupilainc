<?php

require_once __DIR__ . '/vendor/autoload.php';

// Inicializar Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;

echo "🧪 TEST FINAL WHATSAPP DOCUMENT\n";
echo "=================================\n\n";

// Configuración
$apiUrl = config('whatsapp.api_url', 'http://82.165.213.124:8092');
$apiKey = config('whatsapp.api_key', 'test-api-key-vargas-centro');
$phone = '584121234567';

// Crear archivo de prueba
$testFile = sys_get_temp_dir() . '/test_' . uniqid() . '.txt';
$content = "PRUEBA DE DOCUMENTO WHATSAPP\n";
$content .= "============================\n";
$content .= "Fecha: " . date('Y-m-d H:i:s') . "\n";
$content .= "Test ID: " . uniqid() . "\n";
$content .= "Este es un archivo de prueba para verificar el envío de documentos por WhatsApp.\n";

file_put_contents($testFile, $content);

echo "📁 Archivo creado: $testFile\n";
echo "📏 Tamaño: " . number_format(filesize($testFile) / 1024, 2) . " KB\n";
echo "📄 Contenido: " . substr($content, 0, 50) . "...\n\n";

// Intentar enviar el documento
echo "📤 Enviando documento a WhatsApp...\n";

// Usar Guzzle directamente para mejor control
try {
    $client = new \GuzzleHttp\Client([
        'timeout' => 60,
        'connect_timeout' => 10,
        'verify' => false
    ]);
    
    $response = $client->request('POST', $apiUrl . '/api/whatsapp/send-document', [
        'headers' => [
            'X-API-Key' => $apiKey
        ],
        'multipart' => [
            [
                'name' => 'document',
                'contents' => fopen($testFile, 'r'),
                'filename' => 'test_documento.txt'
            ],
            [
                'name' => 'to',
                'contents' => $phone . '@s.whatsapp.net'
            ],
            [
                'name' => 'caption',
                'contents' => '📄 Documento de prueba - ' . date('H:i:s')
            ]
        ]
    ]);
    
    $statusCode = $response->getStatusCode();
    $body = $response->getBody()->getContents();
    $data = json_decode($body, true);
    
    echo "📊 Resultado:\n";
    echo "   Código HTTP: $statusCode\n";
    echo "   Éxito: " . ($statusCode === 200 ? 'Sí' : 'No') . "\n";
    
    if ($statusCode === 200) {
        echo "   ✅ Documento enviado exitosamente!\n";
        echo "   ID del mensaje: " . ($data['messageId'] ?? 'No disponible') . "\n";
        echo "   Empresa: " . ($data['company'] ?? 'No especificada') . "\n";
        echo "   Archivo: " . ($data['fileName'] ?? 'No especificado') . "\n";
    } else {
        echo "   ❌ Error: " . $body . "\n";
    }
    
} catch (\GuzzleHttp\Exception\RequestException $e) {
    echo "❌ Error de solicitud: " . $e->getMessage() . "\n";
    if ($e->hasResponse()) {
        echo "   Código: " . $e->getResponse()->getStatusCode() . "\n";
        echo "   Respuesta: " . $e->getResponse()->getBody()->getContents() . "\n";
    }
} catch (\Exception $e) {
    echo "❌ Error general: " . $e->getMessage() . "\n";
    echo "   Tipo: " . get_class($e) . "\n";
}

// Limpiar
if (file_exists($testFile)) {
    unlink($testFile);
    echo "\n🗑️ Archivo temporal eliminado\n";
}

echo "\n✅ Test finalizado\n";
echo "⏰ Hora: " . date('d/m/Y H:i:s') . "\n";