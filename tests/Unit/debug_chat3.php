<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Simulate logged-in user ID 1
auth()->loginUsingId(1);
$currentUser = auth()->user();

$chatRoles = ['Médico', 'Enfermería', 'Recepción'];

$users = \App\Models\User::where('empresa_id', $currentUser->empresa_id)
    ->where('id', '!=', $currentUser->id)
    ->where('status', 1)
    ->role($chatRoles)
    ->get();

echo "=== Chat Users visible for user {$currentUser->name} ===\n";
foreach ($users as $u) {
    $roles = $u->roles->pluck('name')->join(', ');
    echo "ID:{$u->id} | {$u->name} | roles: {$roles}\n";
}
echo "Total: " . $users->count() . "\n";
