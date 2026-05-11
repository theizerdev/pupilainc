<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$esp = DB::table('especialidades')->where('nombre', 'like', '%Oftalmología%')->first();
if($esp) {
    echo "Especialidad ID: " . $esp->id . PHP_EOL;

    $plantilla = DB::table('especialidad_plantillas')
        ->where('especialidad_id', $esp->id)
        ->where('activo', true)
        ->first();

    if($plantilla) {
        echo "Plantilla ID: " . $plantilla->id . PHP_EOL;
        echo "usar_wizard_en_consultorio: " . ($plantilla->usar_wizard_en_consultorio ? 'TRUE' : 'FALSE') . PHP_EOL;

        $estados = DB::table('plantilla_estado_formularios')
            ->where('plantilla_id', $plantilla->id)
            ->where('activo', true)
            ->get();

        echo "Estados disponibles: " . json_encode($estados->pluck('estado')->toArray()) . PHP_EOL;

        $en_consultorio = $estados->where('estado', 'en_consultorio')->first();
        if($en_consultorio) {
            echo "Formulario en_consultorio existe: SI" . PHP_EOL;
            echo "ID: " . $en_consultorio->id . PHP_EOL;
        } else {
            echo "Formulario en_consultorio existe: NO" . PHP_EOL;
        }
    }
} else {
    echo "No se encontró especialidad" . PHP_EOL;
}
