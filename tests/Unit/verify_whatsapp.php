<?php

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Verificación de WhatsApp ===" . PHP_EOL;

// Verificar empresa primero
echo "1. Verificando empresa..." . PHP_EOL;
$empresa = DB::table('empresas')->where('id', 1)->first();
if ($empresa) {
    echo "✅ Empresa encontrada: " . $empresa->razon_social . PHP_EOL;
    echo "   Status: " . $empresa->status . PHP_EOL;
    echo "   WhatsApp Active: " . ($empresa->whatsapp_active ? 'Sí' : 'No') . PHP_EOL;
    echo "   API Key: " . substr($empresa->api_key, 0, 30) . "..." . PHP_EOL;
    
    if (!$empresa->whatsapp_active) {
        echo "⚠️  ⚠️  ⚠️  WHATSAPP NO ESTÁ ACTIVO PARA ESTA EMPRESA ⚠️  ⚠️  ⚠️" . PHP_EOL;
    }
    if (!$empresa->status) {
        echo "⚠️  ⚠️  ⚠️  LA EMPRESA NO ESTÁ ACTIVA ⚠️  ⚠️  ⚠️" . PHP_EOL;
    }
} else {
    echo "❌ No se encontró la empresa con ID 1" . PHP_EOL;
}

echo PHP_EOL . "2. Verificando servicio WhatsApp API..." . PHP_EOL;
$service = new App\Services\WhatsAppApiService();
$result = $service->testConnection();

if ($result['success']) {
    echo "✅ Servicio funcionando correctamente" . PHP_EOL;
    echo "   Versión: " . $result['version'] . PHP_EOL;
    echo "   Tiempo activo: " . round($result['uptime'], 2) . " segundos" . PHP_EOL;
} else {
    echo "❌ Error: " . $result['message'] . PHP_EOL;
}

echo PHP_EOL . "3. Verificando rutas..." . PHP_EOL;
$routes = [
    'admin.whatsapp.dashboard',
    'admin.whatsapp.connection',
    'admin.whatsapp.templates.index',
    'admin.whatsapp.send-messages',
    'admin.whatsapp.scheduled-messages',
    'admin.whatsapp.history',
    'admin.whatsapp.statistics'
];

foreach ($routes as $route) {
    try {
        $url = route($route);
        echo "✅ Ruta $route: $url" . PHP_EOL;
    } catch (\Exception $e) {
        echo "❌ Ruta $route: " . $e->getMessage() . PHP_EOL;
    }
}

echo PHP_EOL . "=== Verificación completa ===" . PHP_EOL;