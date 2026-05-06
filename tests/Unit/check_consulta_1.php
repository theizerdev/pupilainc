<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Estado de Consulta #1 ===\n\n";

$consulta = App\Models\Consulta::find(1);

echo "ID: {$consulta->id}\n";
echo "Paciente: {$consulta->paciente->nombre_completo}\n";
echo "Médico ID: {$consulta->medico_id}\n";
echo "Médico User ID: {$consulta->medico->user_id}\n";
echo "Estado: {$consulta->estado}\n";
echo "Estado Changed At: {$consulta->estado_changed_at}\n";
echo "Updated At: {$consulta->updated_at}\n\n";

// Calcular tiempo transcurrido
$fechaInicio = $consulta->estado_changed_at ?? $consulta->updated_at;
if ($fechaInicio) {
    $minutosTranscurridos = \Carbon\Carbon::parse($fechaInicio)->diffInMinutes(now());
    echo "Minutos transcurridos: {$minutosTranscurridos}\n";
}

// Verificar gotas aplicadas
echo "\n=== Gotas Aplicadas ===\n";
$gotas = $consulta->gotasAplicadas()->where('estado', 'aplicada')->get();
foreach ($gotas as $gota) {
    echo "- Gota ID: {$gota->id}\n";
    echo "  Tiempo espera: {$gota->tiempo_espera} minutos\n";
    echo "  Notificado: " . ($gota->notificado ? 'SÍ' : 'NO') . "\n";
    echo "  Created At: {$gota->created_at}\n";
}

// Verificar notificaciones existentes para el médico
echo "\n=== Notificaciones para Médico (User ID: 5) ===\n";
$notificaciones = App\Models\Notification::where('user_id', 5)
    ->where('title', 'like', '%Dilatación%')
    ->orderBy('created_at', 'desc')
    ->take(5)
    ->get();

if ($notificaciones->count() > 0) {
    foreach ($notificaciones as $notif) {
        echo "- ID: {$notif->id} | Título: {$notif->title} | Leída: " . ($notif->read_at ? 'Sí' : 'No') . " | Creada: {$notif->created_at}\n";
    }
} else {
    echo "No hay notificaciones de dilatación para este médico.\n";
}

// Verificar mensajes de chat
echo "\n=== Mensajes de Chat para Médico (User ID: 5) ===\n";
$mensajes = App\Models\ChatMessage::where('receiver_id', 5)
    ->where('message', 'like', '%dilatación%')
    ->orderBy('created_at', 'desc')
    ->take(5)
    ->get();

if ($mensajes->count() > 0) {
    foreach ($mensajes as $msg) {
        echo "- De: {$msg->sender_id} | Para: {$msg->receiver_id} | Leído: " . ($msg->is_read ? 'Sí' : 'No') . " | Creado: {$msg->created_at}\n";
    }
} else {
    echo "No hay mensajes de chat sobre dilatación para este médico.\n";
}
