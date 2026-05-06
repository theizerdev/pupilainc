<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Verificando Notificaciones ===\n\n";

$total = App\Models\Notification::count();
$unread = App\Models\Notification::whereNull('read_at')->count();

echo "Total notificaciones: {$total}\n";
echo "No leídas: {$unread}\n\n";

$recent = App\Models\Notification::latest()->take(5)->get();

if ($recent->count() > 0) {
    echo "Últimas notificaciones:\n";
    foreach ($recent as $n) {
        echo "- User ID: {$n->user_id} | Título: {$n->title} | Tipo: {$n->type} | Leída: " . ($n->read_at ? 'Sí' : 'No') . "\n";
    }
} else {
    echo "No hay notificaciones en la base de datos.\n";
}

echo "\n=== Verificando Chat Messages ===\n\n";

$chatTotal = App\Models\ChatMessage::count();
$chatUnread = App\Models\ChatMessage::where('is_read', false)->count();

echo "Total mensajes chat: {$chatTotal}\n";
echo "No leídos: {$chatUnread}\n\n";

$recentChat = App\Models\ChatMessage::where('message', 'like', '%dilatación%')->latest()->take(3)->get();

if ($recentChat->count() > 0) {
    echo "Mensajes de dilatación recientes:\n";
    foreach ($recentChat as $msg) {
        echo "- De: {$msg->sender_id} | Para: {$msg->receiver_id} | Leído: " . ($msg->is_read ? 'Sí' : 'No') . "\n";
    }
} else {
    echo "No hay mensajes de dilatación.\n";
}
