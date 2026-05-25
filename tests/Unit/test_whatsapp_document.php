<?php

// Script de prueba para enviar documento por WhatsApp

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\Http;

// Configuración
$apiUrl = 'http://82.165.213.124:8092/api/whatsapp/send-document';
$apiKey = 'test-api-key-vargas-centro'; // Cambiar por el valor real del .env

// Número de teléfono de prueba (cambiar por un número real)
$telefono = '584121234567'; // Formato con código de país sin +

// Crear un archivo de prueba Excel
$tempFile = tempnam(sys_get_temp_dir(), 'test_excel_') . '.xlsx';

try {
    // Crear un archivo Excel simple de prueba
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setCellValue('A1', 'Prueba de Documento');
    $sheet->setCellValue('A2', 'Fecha: ' . date('Y-m-d H:i:s'));
    $sheet->setCellValue('A3', 'Este es un archivo de prueba para WhatsApp');
    
    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $writer->save($tempFile);
    
    echo "Archivo Excel creado: $tempFile\n";
    echo "Tamaño: " . filesize($tempFile) . " bytes\n";
    
    // Enviar por WhatsApp
    echo "Enviando documento a WhatsApp...\n";
    
    $response = Http::withHeaders([
        'X-API-Key' => $apiKey
    ])->attach(
        'document', file_get_contents($tempFile), 'test_document.xlsx'
    )->timeout(60)->post($apiUrl, [
        'to' => $telefono . '@s.whatsapp.net',
        'caption' => '📄 Documento de prueba - ' . date('Y-m-d H:i:s')
    ]);
    
    if ($response->successful()) {
        echo "✅ Documento enviado exitosamente!\n";
        echo "Respuesta: " . $response->body() . "\n";
    } else {
        echo "❌ Error al enviar documento:\n";
        echo "Código: " . $response->status() . "\n";
        echo "Respuesta: " . $response->body() . "\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
} finally {
    // Limpiar archivo temporal
    if (file_exists($tempFile)) {
        unlink($tempFile);
        echo "Archivo temporal eliminado.\n";
    }
}

echo "\nPrueba completada.\n";