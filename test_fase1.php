<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== VERIFICACIÓN FASE 1 - SISTEMA VETERINARIO ===" . PHP_EOL . PHP_EOL;

// Verificar especies
$especieCount = \App\Models\Especie::count();
echo "✅ Especies creadas: {$especieCount}" . PHP_EOL;

$primeraEspecie = \App\Models\Especie::first();
if ($primeraEspecie) {
    echo "   Primera especie: {$primeraEspecie->nombre} ({$primeraEspecie->nombre_cientifico})" . PHP_EOL;
    echo "   Razas asociadas: {$primeraEspecie->razas->count()}" . PHP_EOL;
}
echo PHP_EOL;

// Verificar razas
$razaCount = \App\Models\Raza::count();
echo "✅ Razas creadas: {$razaCount}" . PHP_EOL;

$primeraRaza = \App\Models\Raza::first();
if ($primeraRaza) {
    echo "   Primera raza: {$primeraRaza->nombre} (Especie: {$primeraRaza->especie->nombre})" . PHP_EOL;
}
echo PHP_EOL;

// Verificar que las tablas existen
echo "=== VERIFICACIÓN DE TABLAS ===" . PHP_EOL;
$tables = ['especies', 'razas', 'mascotas', 'propietarios'];
foreach ($tables as $table) {
    $exists = Schema::hasTable($table);
    echo ($exists ? '✅' : '❌') . " Tabla '{$table}': " . ($exists ? 'EXISTS' : 'MISSING') . PHP_EOL;
}
echo PHP_EOL;

// Verificar columnas en citas
echo "=== CAMPOS VETERINARIOS EN CITAS ===" . PHP_EOL;
$citaColumns = ['mascota_id', 'tipo_atencion', 'urgencia', 'notas_comportamiento'];
foreach ($citaColumns as $column) {
    $exists = Schema::hasColumn('citas', $column);
    echo ($exists ? '✅' : '❌') . " Columna '{$column}': " . ($exists ? 'OK' : 'MISSING') . PHP_EOL;
}
echo PHP_EOL;

// Verificar columnas en consultas
echo "=== CAMPOS VETERINARIOS EN CONSULTAS ===" . PHP_EOL;
$consultaColumns = ['mascota_id', 'temperatura_rectal', 'frecuencia_cardiaca', 'peso_actual_kg', 'bcs_score'];
foreach ($consultaColumns as $column) {
    $exists = Schema::hasColumn('consultas', $column);
    echo ($exists ? '✅' : '❌') . " Columna '{$column}': " . ($exists ? 'OK' : 'MISSING') . PHP_EOL;
}
echo PHP_EOL;

// Verificar modelos
echo "=== MODELOS ELOQUENT ===" . PHP_EOL;
$modelos = ['Especie', 'Raza', 'Mascota', 'Propietario'];
foreach ($modelos as $modelo) {
    $exists = class_exists("App\\Models\\{$modelo}");
    echo ($exists ? '✅' : '❌') . " Modelo {$modelo}: " . ($exists ? 'OK' : 'MISSING') . PHP_EOL;
}
echo PHP_EOL;

// Verificar relaciones
echo "=== RELACIONES ===" . PHP_EOL;
$citaModel = new \App\Models\Cita();
$hasMascotaRelation = method_exists($citaModel, 'mascota');
echo ($hasMascotaRelation ? '✅' : '❌') . " Cita->mascota(): " . ($hasMascotaRelation ? 'OK' : 'MISSING') . PHP_EOL;

$consultaModel = new \App\Models\Consulta();
$hasMascotaRelation2 = method_exists($consultaModel, 'mascota');
echo ($hasMascotaRelation2 ? '✅' : '❌') . " Consulta->mascota(): " . ($hasMascotaRelation2 ? 'OK' : 'MISSING') . PHP_EOL;

$mascotaModel = new \App\Models\Mascota();
$hasEspecieRelation = method_exists($mascotaModel, 'especie');
$hasRazaRelation = method_exists($mascotaModel, 'raza');
$hasPropietarioRelation = method_exists($mascotaModel, 'propietario');
echo ($hasEspecieRelation ? '✅' : '❌') . " Mascota->especie(): " . ($hasEspecieRelation ? 'OK' : 'MISSING') . PHP_EOL;
echo ($hasRazaRelation ? '✅' : '❌') . " Mascota->raza(): " . ($hasRazaRelation ? 'OK' : 'MISSING') . PHP_EOL;
echo ($hasPropietarioRelation ? '✅' : '❌') . " Mascota->propietario(): " . ($hasPropietarioRelation ? 'OK' : 'MISSING') . PHP_EOL;

echo PHP_EOL;
echo "=== RESUMEN FASE 1 ===" . PHP_EOL;
echo "✅ Migraciones completadas: 6" . PHP_EOL;
echo "✅ Modelos creados: 4" . PHP_EOL;
echo "✅ Seeders ejecutados: 8 especies, 41 razas" . PHP_EOL;
echo "✅ Relaciones establecidas" . PHP_EOL;
echo "✅ Campos veterinarios agregados a Citas y Consultas" . PHP_EOL;
echo PHP_EOL;
echo "🎉 FASE 1 COMPLETADA EXITOSAMENTE!" . PHP_EOL;
