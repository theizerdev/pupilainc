<?php

// Script de prueba mejorado para enviar documento por WhatsApp

require_once __DIR__ . '/vendor/autoload.php';

// Inicializar Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;

// Configuración desde el .env
$apiUrl = config('whatsapp.api_url', 'http://82.165.213.124:8092') . '/api/whatsapp/send-document';
$apiKey = config('whatsapp.api_key', 'test-api-key-vargas-centro');

// Número de teléfono de prueba (cambiar por un número real)
$telefono = '584121234567'; // Formato con código de país sin +

echo "🧪 Iniciando prueba de envío de documento por WhatsApp\n";
echo "📍 API URL: $apiUrl\n";
echo "🔑 API Key: " . substr($apiKey, 0, 10) . "...\n";
echo "📱 Teléfono: $telefono\n\n";

// Crear un archivo de prueba Excel
$tempFile = tempnam(sys_get_temp_dir(), 'test_excel_') . '.xlsx';

try {
    // Crear un archivo Excel simple de prueba
    echo "📊 Creando archivo Excel de prueba...\n";
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // Estilos
    $styleArray = [
        'font' => [
            'bold' => true,
            'size' => 14,
            'color' => ['rgb' => 'FFFFFF']
        ],
        'fill' => [
            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
            'startColor' => ['rgb' => '4472C4']
        ],
        'alignment' => [
            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
        ]
    ];
    
    // Encabezado
    $sheet->setCellValue('A1', 'REPORTE DE PRUEBA WHATSAPP');
    $sheet->mergeCells('A1:E1');
    $sheet->getStyle('A1')->applyFromArray($styleArray);
    $sheet->getRowDimension('1')->setRowHeight(30);
    
    // Datos de prueba
    $sheet->setCellValue('A3', 'Fecha:');
    $sheet->setCellValue('B3', date('d/m/Y H:i:s'));
    $sheet->setCellValue('A4', 'Tipo:');
    $sheet->setCellValue('B4', 'Prueba de Sistema');
    $sheet->setCellValue('A5', 'Estado:');
    $sheet->setCellValue('B5', 'Enviado desde Laravel');
    
    // Tabla de datos
    $sheet->setCellValue('A7', 'Producto');
    $sheet->setCellValue('B7', 'Cantidad');
    $sheet->setCellValue('C7', 'Precio');
    $sheet->setCellValue('D7', 'Total');
    
    $datos = [
        ['Libro de Matemáticas', 2, 25.00, 50.00],
        ['Cuaderno de Ciencias', 3, 15.00, 45.00],
        ['Lápices de Colores', 1, 12.00, 12.00],
        ['Calculadora', 1, 35.00, 35.00],
    ];
    
    $fila = 8;
    foreach ($datos as $dato) {
        $sheet->setCellValue('A' . $fila, $dato[0]);
        $sheet->setCellValue('B' . $fila, $dato[1]);
        $sheet->setCellValue('C' . $fila, $dato[2]);
        $sheet->setCellValue('D' . $fila, $dato[3]);
        $fila++;
    }
    
    // Total
    $sheet->setCellValue('C' . $fila, 'TOTAL:');
    $sheet->setCellValue('D' . $fila, '=SUM(D8:D' . ($fila-1) . ')');
    $sheet->getStyle('A' . $fila . ':D' . $fila)->getFont()->setBold(true);
    
    // Autoajustar columnas
    foreach (range('A', 'E') as $column) {
        $sheet->getColumnDimension($column)->setAutoSize(true);
    }
    
    // Guardar archivo
    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $writer->save($tempFile);
    
    echo "✅ Archivo Excel creado: " . basename($tempFile) . "\n";
    echo "📏 Tamaño: " . number_format(filesize($tempFile) / 1024, 2) . " KB\n\n";
    
    // Enviar por WhatsApp
    echo "📤 Enviando documento a WhatsApp...\n";
    
    $response = Http::withHeaders([
        'X-API-Key' => $apiKey
    ])->attach(
        'document', file_get_contents($tempFile), 'reporte_prueba_whatsapp.xlsx'
    )->timeout(60)->post($apiUrl, [
        'to' => $telefono,
        'caption' => '📊 Reporte de Prueba - ' . date('d/m/Y H:i:s') . "\n\nEste es un mensaje de prueba del sistema de envío de documentos por WhatsApp."
    ]);
    
    if ($response->successful()) {
        echo "✅ ¡Documento enviado exitosamente!\n";
        echo "📝 Respuesta: " . json_encode($response->json(), JSON_PRETTY_PRINT) . "\n";
    } else {
        echo "❌ Error al enviar documento:\n";
        echo "📊 Código: " . $response->status() . "\n";
        echo "📄 Respuesta: " . $response->body() . "\n";
        
        // Intentar obtener más detalles del error
        if ($response->json()) {
            $errorData = $response->json();
            echo "🔍 Detalles del error: " . json_encode($errorData, JSON_PRETTY_PRINT) . "\n";
        }
    }
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "📍 Archivo: " . $e->getFile() . "\n";
    echo "🔢 Línea: " . $e->getLine() . "\n";
    echo "🔍 Trace: " . $e->getTraceAsString() . "\n";
} finally {
    // Limpiar archivo temporal
    if (file_exists($tempFile)) {
        unlink($tempFile);
        echo "\n🗑️ Archivo temporal eliminado.\n";
    }
}

echo "\n🎉 Prueba completada.\n";
echo "⏰ Hora: " . date('d/m/Y H:i:s') . "\n";