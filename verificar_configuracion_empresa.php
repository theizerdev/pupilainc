<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Empresa;

echo "=== VERIFICACIÓN DE CONFIGURACIÓN EMPRESA ===\n\n";

// Verificar usuario
$user = User::find(4);
if (!$user) {
    echo "❌ Usuario ID 4 no encontrado\n";
    exit(1);
}

echo "✅ Usuario encontrado:\n";
echo "   Nombre: {$user->name}\n";
echo "   Email: {$user->email}\n";
echo "   Teléfono: {$user->phone}\n";
echo "   Empresa ID: {$user->empresa_id}\n\n";

// Verificar empresa
if (!$user->empresa_id) {
    echo "❌ Usuario no tiene empresa asociada\n";
    exit(1);
}

$empresa = Empresa::find($user->empresa_id);
if (!$empresa) {
    echo "❌ Empresa ID {$user->empresa_id} no encontrada\n";
    exit(1);
}

echo "✅ Empresa encontrada:\n";
echo "   Nombre: {$empresa->nombre}\n";
echo "   ID: {$empresa->id}\n";
echo "   WhatsApp API Key: " . ($empresa->whatsapp_api_key ? substr($empresa->whatsapp_api_key, 0, 10) . '...' : 'NO CONFIGURADA') . "\n";
echo "   Estado: {$empresa->status}\n\n";

// Verificar servicio WhatsApp
use App\Services\WhatsAppService;

try {
    $whatsApp = new WhatsAppService($user->empresa_id);
    echo "✅ Servicio WhatsApp:\n";
    echo "   Company ID: {$whatsApp->getCompanyId()}\n";
    echo "   Configurado: " . ($whatsApp->isConfigured() ? 'SÍ' : 'NO') . "\n";
    
    if ($whatsApp->isConfigured()) {
        echo "   API Key: " . substr(config('whatsapp.api_key', 'no-config'), 0, 10) . '...\n';
    }
    
} catch (\Exception $e) {
    echo "❌ Error en servicio WhatsApp: {$e->getMessage()}\n";
}

echo "\n=== FIN VERIFICACIÓN ===\n";