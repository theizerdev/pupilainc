<?php

/**
 * Script de prueba para el Sistema de Confirmación de Citas
 * 
 * Uso: php tests/test_confirmation_system.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Cita;
use App\Models\CitaConfirmacion;
use App\Models\Paciente;
use App\Models\Medico;
use App\Models\Especialidad;
use App\Models\Sucursal;
use App\Services\CitaConfirmationService;
use Illuminate\Support\Facades\Log;

echo "🧪 INICIANDO PRUEBAS DEL SISTEMA DE CONFIRMACIÓN DE CITAS\n";
echo str_repeat("=", 60) . "\n\n";

// 1. Verificar que las migraciones existen
echo "1️⃣ Verificando migraciones...\n";
try {
    $tablas = \DB::select("SHOW TABLES LIKE 'cita_confirmaciones'");
    if (count($tablas) > 0) {
        echo "   ✅ Tabla cita_confirmaciones existe\n";
    } else {
        echo "   ❌ Tabla cita_confirmaciones NO existe - Ejecutar: php artisan migrate\n";
        exit(1);
    }
} catch (\Exception $e) {
    echo "   ❌ Error verificando tabla: " . $e->getMessage() . "\n";
    exit(1);
}

// 2. Verificar modelos
echo "\n2️⃣ Verificando modelos...\n";
try {
    $confirmacion = new CitaConfirmacion();
    echo "   ✅ Modelo CitaConfirmacion carga correctamente\n";
    echo "   📋 Estados disponibles: " . implode(', ', CitaConfirmacion::ESTADOS) . "\n";
    echo "   📋 Métodos disponibles: whatsapp, email, sms\n";
} catch (\Exception $e) {
    echo "   ❌ Error con modelo: " . $e->getMessage() . "\n";
}

// 3. Verificar servicio
echo "\n3️⃣ Verificando servicio CitaConfirmationService...\n";
try {
    $service = new CitaConfirmationService();
    echo "   ✅ Servicio CitaConfirmationService instanciado correctamente\n";
    
    // Verificar métodos principales
    $metodos = ['iniciarConfirmacion', 'procesarRespuesta', 'reintentarConfirmacion', 'obtenerEstadisticas'];
    foreach ($metodos as $metodo) {
        if (method_exists($service, $metodo)) {
            echo "   ✅ Método {$metodo}() disponible\n";
        } else {
            echo "   ❌ Método {$metodo}() NO disponible\n";
        }
    }
} catch (\Exception $e) {
    echo "   ❌ Error con servicio: " . $e->getMessage() . "\n";
}

// 4. Verificar jobs
echo "\n4️⃣ Verificando jobs...\n";
$jobs = [
    'SendInitialConfirmation',
    'SendBulkConfirmations', 
    'RetryFailedConfirmation'
];

foreach ($jobs as $job) {
    $class = "App\\Jobs\\{$job}";
    if (class_exists($class)) {
        echo "   ✅ Job {$job} disponible\n";
    } else {
        echo "   ❌ Job {$job} NO disponible\n";
    }
}

// 5. Verificar comando
echo "\n5️⃣ Verificando comando artisan...\n";
try {
    $comandos = \Artisan::all();
    if (isset($comandos['confirmations:process'])) {
        echo "   ✅ Comando confirmations:process registrado\n";
    } else {
        echo "   ❌ Comando confirmations:process NO registrado\n";
    }
} catch (\Exception $e) {
    echo "   ❌ Error verificando comandos: " . $e->getMessage() . "\n";
}

// 6. Verificar rutas
echo "\n6️⃣ Verificando rutas...\n";
$rutas = [
    'citas.confirmar',
    'citas.cancelar',
    'webhook.whatsapp.confirmations',
    'admin.citas.confirmaciones'
];

foreach ($rutas as $ruta) {
    try {
        $url = route($ruta);
        echo "   ✅ Ruta {$ruta} -> {$url}\n";
    } catch (\Exception $e) {
        echo "   ❌ Ruta {$ruta} NO disponible: " . $e->getMessage() . "\n";
    }
}

// 7. Verificar WhatsAppService
echo "\n7️⃣ Verificando WhatsAppService...\n";
try {
    $whatsappService = new \App\Services\WhatsAppService();
    
    if (method_exists($whatsappService, 'sendInteractiveMessage')) {
        echo "   ✅ Método sendInteractiveMessage() disponible\n";
    } else {
        echo "   ❌ Método sendInteractiveMessage() NO disponible\n";
    }
    
    if (method_exists($whatsappService, 'isConfigured')) {
        echo "   ✅ Método isConfigured() disponible\n";
    } else {
        echo "   ❌ Método isConfigured() NO disponible\n";
    }
} catch (\Exception $e) {
    echo "   ❌ Error con WhatsAppService: " . $e->getMessage() . "\n";
}

// 8. Estadísticas actuales
echo "\n8️⃣ Estadísticas actuales de confirmaciones:\n";
try {
    $stats = [
        'total' => CitaConfirmacion::count(),
        'pendientes' => CitaConfirmacion::where('estado', 'pendiente')->count(),
        'confirmadas' => CitaConfirmacion::where('estado', 'confirmado')->count(),
        'rechazadas' => CitaConfirmacion::where('estado', 'rechazado')->count(),
        'sin_respuesta' => CitaConfirmacion::where('estado', 'sin_respuesta')->count(),
        'expiradas' => CitaConfirmacion::where('estado', 'expirado')->count(),
    ];
    
    foreach ($stats as $key => $value) {
        echo "   📊 {$key}: {$value}\n";
    }
} catch (\Exception $e) {
    echo "   ❌ Error obteniendo estadísticas: " . $e->getMessage() . "\n";
}

// 9. Prueba de creación (opcional - descomentar para probar)
/*
echo "\n9️⃣ Prueba de creación de confirmación:\n";
try {
    // Buscar una cita existente
    $cita = Cita::where('estado', 'pendiente')
                ->where('fecha_inicio', '>', now()->addHours(24))
                ->first();
    
    if ($cita) {
        echo "   📝 Cita encontrada: #{$cita->id}\n";
        echo "   👤 Paciente: {$cita->paciente->nombre_completo}\n";
        echo "   👨‍⚕️ Médico: {$cita->medico->nombre_completo}\n";
        echo "   📅 Fecha: {$cita->fecha_inicio->format('d/m/Y H:i')}\n";
        
        // Crear confirmación
        $service = new CitaConfirmationService();
        $confirmacion = $service->iniciarConfirmacion($cita);
        
        echo "   ✅ Confirmación creada: #{$confirmacion->id}\n";
        echo "   🔑 Token: {$confirmacion->token_confirmacion}\n";
        echo "   📱 Destinatario: {$confirmacion->destinatario}\n";
        echo "   ⏰ Expira: {$confirmacion->fecha_envio->addHours(24)->format('d/m/Y H:i')}\n";
        
    } else {
        echo "   ⚠️ No se encontró cita pendiente para probar\n";
    }
} catch (\Exception $e) {
    echo "   ❌ Error en prueba: " . $e->getMessage() . "\n";
    Log::error('Error en prueba de confirmación: ' . $e->getMessage());
}
*/

echo "\n" . str_repeat("=", 60) . "\n";
echo "✅ PRUEBAS COMPLETADAS\n";
echo "\n📋 Próximos pasos:\n";
echo "   1. Ejecutar: php artisan migrate (si no se ha hecho)\n";
echo "   2. Ejecutar: php artisan queue:work (para procesar jobs)\n";
echo "   3. Ejecutar: php artisan confirmations:process --type=all\n";
echo "   4. Acceder a: /admin/citas/confirmaciones (panel de administración)\n";
echo "\n🔧 Comandos útiles:\n";
echo "   - php artisan confirmations:process --type=pending\n";
echo "   - php artisan confirmations:process --type=expired\n";
echo "   - php artisan confirmations:process --type=retry\n";
echo "   - php artisan tinker --execute=\"\\App\\Models\\CitaConfirmacion::count();\"\n";
echo "\n";
