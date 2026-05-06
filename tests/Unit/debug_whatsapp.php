<?php

require __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\Http;

echo "=== Debug WhatsApp Connection ===\n\n";

// 1. Verificar configuración
$apiUrl = config('whatsapp.api_url');
$jwtSecret = config('whatsapp.jwt_secret');

echo "1. Configuración:\n";
echo "   API URL: " . $apiUrl . "\n";
echo "   JWT Secret: " . substr($jwtSecret, 0, 20) . "...\n\n";

// 2. Verificar token JWT
$empresa = \DB::table('empresas')->where('id', 1)->first();
echo "2. Empresa API Key:\n";
echo "   Empresa encontrada: " . ($empresa ? 'Sí' : 'No') . "\n";
if ($empresa) {
    echo "   API Key: " . substr($empresa->api_key, 0, 20) . "...\n";
}
echo "\n";

// 3. Probar conexión directa
$token = $empresa ? $empresa->api_key : $jwtSecret;
echo "3. Prueba de conexión:\n";

try {
    // Test 1: Health check
    echo "   Test 1 - Health Check:\n";
    $healthResponse = Http::timeout(5)->get($apiUrl . '/health');
    echo "   Status: " . $healthResponse->status() . "\n";
    echo "   Success: " . ($healthResponse->successful() ? 'Sí' : 'No') . "\n";
    
    if ($healthResponse->successful()) {
        echo "   Response: " . json_encode($healthResponse->json()) . "\n";
    }
    
    echo "\n   Test 2 - Status con token:\n";
    $statusResponse = Http::withHeaders([
        'X-API-Key' => $token
    ])->timeout(10)->get($apiUrl . '/api/whatsapp/status');
    
    echo "   Status: " . $statusResponse->status() . "\n";
    echo "   Success: " . ($statusResponse->successful() ? 'Sí' : 'No') . "\n";
    
    if ($statusResponse->successful()) {
        echo "   Response: " . json_encode($statusResponse->json()) . "\n";
    } else {
        echo "   Error: " . $statusResponse->body() . "\n";
    }
    
} catch (\Exception $e) {
    echo "   Error: " . $e->getMessage() . "\n";
}

echo "\n=== Fin del debug ===\n";