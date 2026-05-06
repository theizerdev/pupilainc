<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Obtener la plantilla de la especialidad ID 1 (ajusta según necesites)
$plantilla = \App\Models\EspecialidadPlantilla::with([
    'todasLasSecciones.todosLosCampos',
    'todosLosEstadoFormularios.todasLasSecciones.todosLosCampos'
])->where('especialidad_id', 1)->first();

if (!$plantilla) {
    echo "No se encontró plantilla para especialidad ID 1\n";
    exit;
}

echo "=== PLANTILLA ===\n";
echo "ID: {$plantilla->id}\n";
echo "Nombre: {$plantilla->nombre}\n";
echo "Activo: " . ($plantilla->activo ? 'Sí' : 'No') . "\n\n";

echo "=== PASOS CONFIG ===\n";
$pasosConfig = $plantilla->getPasosEfectivos();
echo "Total pasos: " . count($pasosConfig) . "\n";
foreach ($pasosConfig as $paso) {
    echo "  - {$paso['nombre']} ({$paso['key']}) - " . ($paso['activo'] ? 'Activo' : 'Inactivo') . " - Tipo: {$paso['tipo']}\n";
}
echo "\n";

echo "=== ESTADOS CONFIG ===\n";
$estadosConfig = $plantilla->getEstadosEfectivos();
echo "Total estados: " . count($estadosConfig) . "\n";
foreach ($estadosConfig as $estado) {
    echo "  - {$estado['nombre']} ({$estado['key']}) - " . ($estado['activo'] ? 'Activo' : 'Inactivo') . "\n";
}
echo "\n";

echo "=== SECCIONES DE EVALUACIÓN (sin estado_formulario_id) ===\n";
echo "Total secciones: " . $plantilla->todasLasSecciones->count() . "\n";
foreach ($plantilla->todasLasSecciones as $seccion) {
    echo "  - {$seccion->nombre} (ID: {$seccion->id}) - Campos: {$seccion->todosLosCampos->count()}\n";
}
echo "\n";

echo "=== FORMULARIOS POR ESTADO ===\n";
echo "Total formularios: " . $plantilla->todosLosEstadoFormularios->count() . "\n";
foreach ($plantilla->todosLosEstadoFormularios as $ef) {
    echo "  Estado: {$ef->estado} (ID: {$ef->id})\n";
    echo "    Secciones: {$ef->todasLasSecciones->count()}\n";
    foreach ($ef->todasLasSecciones as $seccion) {
        echo "      - {$seccion->nombre} (ID: {$seccion->id}) - Campos: {$seccion->todosLosCampos->count()}\n";
    }
}
echo "\n";

echo "=== PASOS_CONFIG (JSON en BD) ===\n";
echo json_encode($plantilla->pasos_config, JSON_PRETTY_PRINT) . "\n\n";

echo "=== ESTADOS_CONFIG (JSON en BD) ===\n";
echo json_encode($plantilla->estados_config, JSON_PRETTY_PRINT) . "\n";
