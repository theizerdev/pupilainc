<?php

require_once __DIR__ . '/vendor/autoload.php';

// Inicializar Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Caja;
use App\Models\Empresa;
use Illuminate\Support\Facades\Http;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

echo "🧪 TEST CIERRE DE CAJA WHATSAPP\n";
echo "=================================\n\n";

// Buscar una caja cerrada reciente
try {
    $caja = Caja::with(['usuario', 'sucursal', 'empresa'])
        ->where('estado', 'cerrada')
        ->latest()
        ->first();
    
    if (!$caja) {
        echo "❌ No se encontró ninguna caja cerrada para probar\n";
        exit(1);
    }
    
    echo "📋 Caja encontrada:\n";
    echo "   ID: " . $caja->id . "\n";
    echo "   Fecha: " . $caja->fecha->format('d/m/Y') . "\n";
    echo "   Sucursal: " . $caja->sucursal->nombre . "\n";
    echo "   Usuario: " . $caja->usuario->name . "\n";
    echo "   Estado: " . $caja->estado . "\n\n";
    
    // Obtener empresa y teléfono
    $empresa = Empresa::find($caja->empresa_id);
    
    if (!$empresa || !$empresa->telefono) {
        echo "❌ La empresa no tiene teléfono registrado\n";
        exit(1);
    }
    
    echo "📱 Teléfono de empresa: " . $empresa->telefono . "\n\n";
    
    // Generar Excel de prueba
    echo "📊 Generando Excel de prueba...\n";
    
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // Encabezado
    $sheet->setCellValue('A1', 'REPORTE DE PRUEBA DE CAJA');
    $sheet->mergeCells('A1:D1');
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
    
    // Datos de prueba
    $sheet->setCellValue('A3', 'Caja ID:');
    $sheet->setCellValue('B3', $caja->id);
    $sheet->setCellValue('A4', 'Fecha:');
    $sheet->setCellValue('B4', $caja->fecha->format('d/m/Y'));
    $sheet->setCellValue('A5', 'Sucursal:');
    $sheet->setCellValue('B5', $caja->sucursal->nombre);
    $sheet->setCellValue('A6', 'Usuario:');
    $sheet->setCellValue('B6', $caja->usuario->name);
    $sheet->setCellValue('A7', 'Monto Final:');
    $sheet->setCellValue('B7', '$' . number_format($caja->monto_final, 2));
    
    // Guardar archivo
    $filename = 'test_caja_' . $caja->id . '_' . time() . '.xlsx';
    $tempPath = storage_path('app/temp/' . $filename);
    
    if (!file_exists(storage_path('app/temp'))) {
        mkdir(storage_path('app/temp'), 0755, true);
    }
    
    $writer = new Xlsx($spreadsheet);
    $writer->save($tempPath);
    
    echo "✅ Excel generado: $tempPath\n";
    echo "📏 Tamaño: " . number_format(filesize($tempPath) / 1024, 2) . " KB\n\n";
    
    // Configuración WhatsApp
    $apiUrl = config('whatsapp.api_url', 'http://82.165.213.124:8092');
    $apiKey = config('whatsapp.api_key', 'test-api-key-vargas-centro');
    
    // Formatear número
    $telefono = $empresa->telefono;
    $cleaned = preg_replace('/\D/', '', $telefono);
    if (!str_starts_with($cleaned, '58') && strlen($cleaned) === 10) {
        $cleaned = '58' . $cleaned;
    }
    $telefonoFormateado = $cleaned . '@s.whatsapp.net';
    
    echo "📤 Enviando documento a WhatsApp...\n";
    echo "   URL: $apiUrl/api/whatsapp/send-document\n";
    echo "   Teléfono: $telefonoFormateado\n\n";
    
    // Preparar mensaje
    $caption = "🧪 *TEST DE CIERRE DE CAJA* 🧪\n\n";
    $caption .= "📊 *Resumen del cierre:*\n";
    $caption .= "• Fecha: " . $caja->fecha->format('d/m/Y') . "\n";
    $caption .= "• Sucursal: " . $caja->sucursal->nombre . "\n";
    $caption .= "• Usuario: " . $caja->usuario->name . "\n";
    $caption .= "• Monto Final: $" . number_format($caja->monto_final, 2) . "\n\n";
    $caption .= "📎 Este es un archivo de prueba para verificar el envío de reportes por WhatsApp.";
    
    // Enviar documento
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
                    'contents' => fopen($tempPath, 'r'),
                    'filename' => $filename
                ],
                [
                    'name' => 'to',
                    'contents' => $telefonoFormateado
                ],
                [
                    'name' => 'caption',
                    'contents' => $caption
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
        
    } catch (\Exception $e) {
        echo "❌ Error: " . $e->getMessage() . "\n";
        echo "   Tipo: " . get_class($e) . "\n";
    }
    
    // Limpiar
    if (file_exists($tempPath)) {
        unlink($tempPath);
        echo "\n🗑️ Archivo temporal eliminado\n";
    }
    
    echo "\n✅ Test completado\n";
    
} catch (\Exception $e) {
    echo "❌ Error general: " . $e->getMessage() . "\n";
    echo "   Tipo: " . get_class($e) . "\n";
    echo "   Trace: " . $e->getTraceAsString() . "\n";
}