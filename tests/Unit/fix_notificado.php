<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

App\Models\ConsultaGota::find(1)->update(['notificado' => false]);
echo "✅ Notificado actualizado a FALSE\n";

$gota = App\Models\ConsultaGota::find(1);
echo "Estado final: notificado = " . ($gota->notificado ? 'TRUE' : 'FALSE') . "\n";
