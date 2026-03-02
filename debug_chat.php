<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;

// Check users with chat roles
$chatRoles = ['Médico', 'Enfermería', 'Recepción'];

echo "=== All Users with Roles ===\n";
$users = User::with('roles')->where('status', 'active')->get();
foreach ($users as $u) {
    $roles = $u->roles->pluck('name')->join(', ');
    echo "ID:{$u->id} | {$u->name} | empresa:{$u->empresa_id} | status:{$u->status} | roles: {$roles}\n";
}

echo "\n=== Users with Chat Roles (using ->role()) ===\n";
$chatUsers = User::role($chatRoles)->get();
foreach ($chatUsers as $u) {
    $roles = $u->roles->pluck('name')->join(', ');
    echo "ID:{$u->id} | {$u->name} | roles: {$roles}\n";
}

echo "\n=== Existing Roles in DB ===\n";
$roles = \Spatie\Permission\Models\Role::all();
foreach ($roles as $r) {
    echo "Role ID:{$r->id} | name: '{$r->name}' | guard: {$r->guard_name}\n";
}

echo "\n=== model_has_roles table ===\n";
$pivots = \DB::table('model_has_roles')->get();
foreach ($pivots as $p) {
    echo "role_id:{$p->role_id} | model_type:{$p->model_type} | model_id:{$p->model_id}\n";
}
