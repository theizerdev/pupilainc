<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== EXPLORANDO BASE DE DATOS DE WHATSAPP API ===\n\n";

try {
    // Conectar a la base de datos de WhatsApp API
    $whatsappDb = DB::connection('whatsapp_api');
    
    echo "✅ Conexión a base de datos WhatsApp API exitosa\n\n";
    
    // Obtener tablas
    $tables = $whatsappDb->select('SHOW TABLES');
    echo "📊 Tablas disponibles:\n";
    
    foreach ($tables as $table) {
        $tableName = array_values((array)$table)[0];
        echo "   - {$tableName}\n";
        
        // Contar registros
        $count = $whatsappDb->table($tableName)->count();
        echo "     └─ Registros: {$count}\n";
    }
    
    echo "\n📈 ESTRUCTURA DE TABLAS PRINCIPALES:\n";
    
    // Explorar tablas principales
    $mainTables = ['messages', 'contacts', 'chats', 'message_status'];
    
    foreach ($mainTables as $tableName) {
        if ($whatsappDb->select("SHOW TABLES LIKE '{$tableName}'")) {
            echo "\n🔍 Tabla: {$tableName}\n";
            
            // Estructura de la tabla
            $columns = $whatsappDb->select("DESCRIBE {$tableName}");
            echo "   Columnas:\n";
            foreach ($columns as $column) {
                echo "   - {$column->Field} ({$column->Type})\n";
            }
            
            // Muestra de datos
            $sampleData = $whatsappDb->table($tableName)->limit(3)->get();
            if ($sampleData->isNotEmpty()) {
                echo "   ├─ Muestra de datos:\n";
                foreach ($sampleData as $row) {
                    echo "   │ " . json_encode((array)$row, JSON_PRETTY_PRINT) . "\n";
                }
            }
        }
    }
    
    // Estadísticas específicas
    echo "\n📊 ESTADÍSTICAS DE MENSAJES:\n";
    
    if ($whatsappDb->select("SHOW TABLES LIKE 'messages'")) {
        // Total de mensajes por estado
        $statusStats = $whatsappDb->table('messages')
            ->select('status', $whatsappDb->raw('COUNT(*) as total'))
            ->groupBy('status')
            ->get();
            
        echo "   Estados de mensajes:\n";
        foreach ($statusStats as $stat) {
            echo "   - {$stat->status}: {$stat->total}\n";
        }
        
        // Mensajes por día
        $dailyStats = $whatsappDb->table('messages')
            ->select($whatsappDb->raw('DATE(created_at) as date'), $whatsappDb->raw('COUNT(*) as total'))
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->get();
            
        echo "\n   Mensajes por día (últimos 7 días):\n";
        foreach ($dailyStats as $stat) {
            echo "   - {$stat->date}: {$stat->total}\n";
        }
        
        // Mensajes por tipo
        $typeStats = $whatsappDb->table('messages')
            ->select('type', $whatsappDb->raw('COUNT(*) as total'))
            ->groupBy('type')
            ->get();
            
        echo "\n   Mensajes por tipo:\n";
        foreach ($typeStats as $stat) {
            echo "   - {$stat->type}: {$stat->total}\n";
        }
    }
    
} catch (\Exception $e) {
    echo "❌ Error: {$e->getMessage()}\n";
    echo "Detalles: {$e->getTraceAsString()}\n";
}

echo "\n=== FIN EXPLORACIÓN ===\n";