<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== All Users (no status filter) ===\n";
$users = \App\Models\User::select('id','name','status','empresa_id')->get();
foreach ($users as $u) {
    echo "ID:{$u->id} | {$u->name} | status:" . var_export($u->status, true) . " | empresa:{$u->empresa_id}\n";
}
