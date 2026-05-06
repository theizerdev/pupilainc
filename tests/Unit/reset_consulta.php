<?php
require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$consulta = App\Models\Consulta::find(1);
$consulta->estado = 'en_gotas';
$consulta->estado_changed_at = now();
$consulta->save();

echo 'Consulta reseteada a en_gotas' . PHP_EOL;

// Crear nueva gota con tiempo de espera corto
App\Models\ConsultaGota::create([
    'consulta_id' => 1,
    'user_id' => 1,
    'tipo_gota' => 'Tropicamida 1%',
    'gotas_od' => 1,
    'gotas_oi' => 1,
    'tiempo_espera' => 1, // 1 minuto para pruebas
    'estado' => 'aplicada',
    'notificado' => false
]);

echo 'Nueva gota creada con 1 minuto de espera' . PHP_EOL;