<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== COMPARACIÓN: Secciones Principales vs Formulario por Estado ===\n\n";
echo "Especialidad ID: 1 (Oftalmología)\n";
echo str_repeat('=', 80) . "\n\n";

// Obtener campos de secciones principales
$campos_principales = DB::table('especialidad_plantillas as ep')
    ->join('plantilla_secciones as ps', 'ep.id', '=', 'ps.plantilla_id')
    ->join('plantilla_campos as pc', 'ps.id', '=', 'pc.seccion_id')
    ->where('ep.especialidad_id', 1)
    ->whereNull('ps.estado_formulario_id')
    ->select(
        'ps.nombre as seccion',
        'pc.nombre_campo',
        'pc.etiqueta',
        'pc.tipo'
    )
    ->orderBy('ps.nombre')
    ->orderBy('pc.nombre_campo')
    ->get()
    ->keyBy('nombre_campo');

// Obtener campos del estado en_consultorio
$campos_estado = DB::table('especialidad_plantillas as ep')
    ->join('plantilla_estado_formularios as pef', 'ep.id', '=', 'pef.plantilla_id')
    ->join('plantilla_secciones as ps', 'pef.id', '=', 'ps.estado_formulario_id')
    ->join('plantilla_campos as pc', 'ps.id', '=', 'pc.seccion_id')
    ->where('ep.especialidad_id', 1)
    ->where('pef.estado', 'en_consultorio')
    ->select(
        'ps.nombre as seccion',
        'pc.nombre_campo',
        'pc.etiqueta',
        'pc.tipo'
    )
    ->orderBy('ps.nombre')
    ->orderBy('pc.nombre_campo')
    ->get()
    ->keyBy('nombre_campo');

echo "📋 CAMPOS EN SECCIONES PRINCIPALES: " . $campos_principales->count() . "\n";
echo str_repeat('-', 80) . "\n";
foreach ($campos_principales as $campo) {
    echo "  - {$campo->etiqueta} ({$campo->nombre_campo}) [{$campo->tipo}] en '{$campo->seccion}'\n";
}

echo "\n\n📋 CAMPOS EN FORMULARIO en_consultorio: " . $campos_estado->count() . "\n";
echo str_repeat('-', 80) . "\n";
foreach ($campos_estado as $campo) {
    echo "  - {$campo->etiqueta} ({$campo->nombre_campo}) [{$campo->tipo}] en '{$campo->seccion}'\n";
}

echo "\n\n❌ CAMPOS QUE FALTAN en en_consultorio:\n";
echo str_repeat('-', 80) . "\n";
$faltantes = $campos_principales->diffKeys($campos_estado);
if ($faltantes->isEmpty()) {
    echo "  ✅ No faltan campos - todos están sincronizados\n";
} else {
    foreach ($faltantes as $campo) {
        echo "  ❌ {$campo->etiqueta} ({$campo->nombre_campo}) [{$campo->tipo}] - está en '{$campo->seccion}' pero NO en en_consultorio\n";
    }
}

echo "\n\n✅ CAMPOS EXCLUSIVOS de en_consultorio (no en secciones principales):\n";
echo str_repeat('-', 80) . "\n";
$exclusivos = $campos_estado->diffKeys($campos_principales);
if ($exclusivos->isEmpty()) {
    echo "  ℹ️  No hay campos exclusivos\n";
} else {
    foreach ($exclusivos as $campo) {
        echo "  ➕ {$campo->etiqueta} ({$campo->nombre_campo}) [{$campo->tipo}] - solo en '{$campo->seccion}' del estado\n";
    }
}

echo "\n";
