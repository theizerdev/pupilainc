<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class WhatsAppPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Definir permisos específicos de WhatsApp que faltan
        $whatsappPermissions = [
            // Permisos para el dashboard
            'view whatsapp dashboard' => 'whatsapp',
            
            // Permisos para la conexión
            'view whatsapp connection' => 'whatsapp',
            'manage whatsapp connection' => 'whatsapp',
            
            // Permisos para plantillas
            'view whatsapp templates' => 'whatsapp',
            'create whatsapp templates' => 'whatsapp',
            'edit whatsapp templates' => 'whatsapp',
            'delete whatsapp templates' => 'whatsapp',
            
            // Permisos para enviar mensajes
            'view whatsapp send messages' => 'whatsapp',
            'send whatsapp messages' => 'whatsapp',
            
            // Permisos para mensajes programados
            'view whatsapp scheduled messages' => 'whatsapp',
            'create whatsapp scheduled messages' => 'whatsapp',
            'edit whatsapp scheduled messages' => 'whatsapp',
            'delete whatsapp scheduled messages' => 'whatsapp',
            
            // Permisos para historial
            'view whatsapp history' => 'whatsapp',
            'delete whatsapp history' => 'whatsapp',
            'retry whatsapp messages' => 'whatsapp',
            
            // Permisos para estadísticas
            'view whatsapp statistics' => 'whatsapp',
            'export whatsapp statistics' => 'whatsapp',

            // Permisos para configuracion de notificaciones
            'view whatsapp notifications' => 'whatsapp',
            'manage whatsapp notifications' => 'whatsapp',
            
            // Permisos generales que ya existen pero nos aseguramos
            'access whatsapp' => 'whatsapp',
            'send whatsapp messages' => 'whatsapp',
            'schedule whatsapp messages' => 'whatsapp',
            'view whatsapp statistics' => 'whatsapp',
            'export whatsapp reports' => 'whatsapp',
            'retry failed whatsapp messages' => 'whatsapp',
            'view whatsapp retry statistics' => 'whatsapp',
            'manage whatsapp auto retry' => 'whatsapp',
        ];

        // Crear permisos que no existan
        foreach ($whatsappPermissions as $permissionName => $module) {
            Permission::firstOrCreate(
                ['name' => $permissionName],
                ['module' => $module]
            );
        }

        // Obtener roles existentes
        $superAdminRole = Role::findByName('Super Administrador');
        $adminRole = Role::findByName('Administrador');
        
        // Asignar todos los permisos de WhatsApp a Super Administrador
        $allWhatsAppPermissions = Permission::where('module', 'whatsapp')->get();
        $superAdminRole->givePermissionTo($allWhatsAppPermissions);
        
        // Asignar permisos básicos de WhatsApp a Administrador (sin funciones críticas)
        $adminWhatsAppPermissions = Permission::where('module', 'whatsapp')
            ->whereNotIn('name', [
                'manage whatsapp connection',
                'delete whatsapp templates',
                'manage whatsapp auto retry',
                'delete whatsapp history'
            ])
            ->get();
        $adminRole->givePermissionTo($adminWhatsAppPermissions);
    }
}
