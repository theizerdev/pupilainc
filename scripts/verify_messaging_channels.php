<?php

use App\Models\ModuleNotificationChannel;
use App\Models\MessagingConnection;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/../vendor/autoload.php';

// Carga el framework
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

function out($label, $value): void {
    echo $label . ': ' . (is_scalar($value) || $value === null ? ($value === null ? 'null' : (string)$value) : json_encode($value, JSON_PRETTY_PRINT)) . PHP_EOL;
}

// Ejemplo: php scripts/verify_messaging_channels.php citas estado_no_asistio paciente 1
$argv = $_SERVER['argv'] ?? [];
$empresaId = (int)($argv[4] ?? (auth()->check() ? auth()->user()->empresa_id : 1));
$module = $argv[1] ?? 'citas';
$action = $argv[2] ?? 'estado_no_asistio';
$recipient = $argv[3] ?? 'paciente';

// 1) Ver canal enabled
$channel = ModuleNotificationChannel::query()
    ->where('empresa_id', $empresaId)
    ->where('module_key', $module)
  
    ->where('recipient_type', 'paciente')
    ->first();

out('empresa_id', $empresaId);
out('module_key', $module);
out('action_key', $action);
out('recipient_type', $recipient);

if (!$channel) {
    echo 'CANAL: not found' . PHP_EOL;
} else {
    out('channel_id', $channel->id);
    out('enabled', $channel->enabled);
    out('priority', $channel->priority);
    out('connection_id', $channel->connection_id);
}

// 2) Estado de la conexión asociada
if ($channel && $channel->connection_id) {
    $conn = MessagingConnection::query()->where('id', $channel->connection_id)->first();
    if (!$conn) {
        echo 'CONEXIÓN: not found (connection_id='.$channel->connection_id.')'.PHP_EOL;
    } else {
        out('connection_name', $conn->name);
        out('connection_status', $conn->status);
        out('provider_id', $conn->provider_id);
        out('provider_slug', optional($conn->provider)->slug);
        out('is_default_for', $conn->is_default_for);
    }
}

// 3) Mostrar cuántos canales enabled existen para ese módulo/recip
$countEnabled = ModuleNotificationChannel::query()
    ->where('empresa_id', $empresaId)
    ->where('module_key', $module)
    ->where('recipient_type', $recipient)
    ->where('enabled', true)
    ->count();

out('enabled_channels_count_for_module_recipient', $countEnabled);

// 4) Listar action_key enabled (top 20 por priority asc)
$list = ModuleNotificationChannel::query()
    ->where('empresa_id', $empresaId)
    ->where('module_key', $module)
    ->where('recipient_type', $recipient)
    ->where('enabled', true)
    ->orderBy('priority', 'asc')
    ->limit(20)
    ->get(['action_key','priority','connection_id','enabled'])
    ->toArray();

echo PHP_EOL . 'Top enabled actions (module/recipient):' . PHP_EOL;
foreach ($list as $row) {
    echo '- ' . $row['action_key'] . ' | priority=' . $row['priority'] . ' | connection_id=' . $row['connection_id'] . PHP_EOL;
}

echo PHP_EOL . 'OK' . PHP_EOL;

