<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== ESTRUCTURA DETALLADA DE TABLAS WHATSAPP ===\n\n";

try {
    $whatsappDb = DB::connection('whatsapp_api');
    
    // Tabla whatsapp_messages
    echo "🔍 TABLA: whatsapp_messages\n";
    $columns = $whatsappDb->select("DESCRIBE whatsapp_messages");
    foreach ($columns as $column) {
        echo "   - {$column->Field} ({$column->Type}) - {$column->Null} - {$column->Key} - {$column->Default}\n";
    }
    
    // Muestra de datos
    echo "\n   📋 Muestra de datos:\n";
    $sampleData = $whatsappDb->table('whatsapp_messages')->limit(2)->get();
    foreach ($sampleData as $row) {
        echo "   " . json_encode((array)$row, JSON_PRETTY_PRINT) . "\n";
    }
    
    // Estadísticas por estado
    echo "\n   📊 Estadísticas por estado:\n";
    $statusStats = $whatsappDb->table('whatsapp_messages')
        ->select('status', $whatsappDb->raw('COUNT(*) as total'))
        ->groupBy('status')
        ->get();
        
    foreach ($statusStats as $stat) {
        echo "   - {$stat->status}: {$stat->total}\n";
    }
    
    // Mensajes por día (últimos 7 días)
    echo "\n   📅 Mensajes por día (últimos 7 días):\n";
    $dailyStats = $whatsappDb->table('whatsapp_messages')
        ->select($whatsappDb->raw('DATE(createdAt) as date'), $whatsappDb->raw('COUNT(*) as total'))
        ->where('createdAt', '>=', now()->subDays(7))
        ->groupBy('date')
        ->orderBy('date', 'desc')
        ->get();
        
    foreach ($dailyStats as $stat) {
        echo "   - {$stat->date}: {$stat->total}\n";
    }
    
    // Total de mensajes hoy
    echo "\n   📈 Total mensajes hoy:\n";
    $todayCount = $whatsappDb->table('whatsapp_messages')
        ->whereDate('createdAt', today())
        ->count();
    echo "   - Hoy: {$todayCount}\n";
    
    // Tabla companies
    echo "\n\n🔍 TABLA: companies\n";
    $columns = $whatsappDb->select("DESCRIBE companies");
    foreach ($columns as $column) {
        echo "   - {$column->Field} ({$column->Type}) - {$column->Null} - {$column->Key} - {$column->Default}\n";
    }
    
    // Datos de companies
    echo "\n   📋 Datos de companies:\n";
    $companies = $whatsappDb->table('companies')->get();
    foreach ($companies as $company) {
        echo "   - ID: {$company->id}, Name: {$company->name}, Token: " . substr($company->token, 0, 10) . "...\n";
    }
    
    // Tabla whatsapp_sessions
    echo "\n\n🔍 TABLA: whatsapp_sessions\n";
    $columns = $whatsappDb->select("DESCRIBE whatsapp_sessions");
    foreach ($columns as $column) {
        echo "   - {$column->Field} ({$column->Type}) - {$column->Null} - {$column->Key} - {$column->Default}\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error: {$e->getMessage()}\n";
}

echo "\n=== FIN EXPLORACIÓN DETALLADA ===\n";