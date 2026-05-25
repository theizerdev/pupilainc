<?php

namespace Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Spatie\Permission\PermissionRegistrar;

trait CreatesWhatsAppNotificationTestSchema
{
    protected function createWhatsAppNotificationTestSchema(): void
    {
        if (config('database.default') !== 'sqlite' || config('database.connections.sqlite.database') !== ':memory:') {
            throw new RuntimeException('WhatsApp notification tests must run with the in-memory sqlite database.');
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach ([
            'model_has_permissions',
            'model_has_roles',
            'role_has_permissions',
            'permissions',
            'roles',
            'whatsapp_scheduled_messages',
            'whatsapp_notification_settings',
            'configuracion_notificaciones',
            'activity_log',
            'users',
            'empresas',
        ] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::create('empresas', function (Blueprint $table) {
            $table->id();
            $table->string('razon_social');
            $table->string('documento')->nullable()->unique();
            $table->string('whatsapp_api_key')->nullable();
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('username')->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('phone')->nullable();
            $table->boolean('whatsapp_verification_enabled')->default(false);
            $table->foreignId('empresa_id')->nullable()->constrained('empresas')->nullOnDelete();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('activity_log', function (Blueprint $table) {
            $table->id();
            $table->string('log_name')->nullable();
            $table->text('description');
            $table->nullableMorphs('subject', 'subject');
            $table->string('event')->nullable();
            $table->nullableMorphs('causer', 'causer');
            $table->json('properties')->nullable();
            $table->uuid('batch_uuid')->nullable();
            $table->timestamps();
        });

        Schema::create('configuracion_notificaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->string('tipo_destinatario');
            $table->string('estado_cita');
            $table->boolean('enviar_notificacion')->default(false);
            $table->timestamps();
            $table->unique(['empresa_id', 'tipo_destinatario', 'estado_cita'], 'cfg_notif_test_unique');
        });

        Schema::create('whatsapp_notification_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->string('module_key', 80);
            $table->string('action_key', 120);
            $table->string('recipient_key', 80);
            $table->boolean('enabled')->default(true);
            $table->timestamps();
            $table->unique(['empresa_id', 'module_key', 'action_key', 'recipient_key'], 'wa_notif_settings_test_unique');
        });

        Schema::create('whatsapp_scheduled_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cita_id')->nullable();
            $table->foreignId('empresa_id')->nullable()->constrained('empresas')->nullOnDelete();
            $table->string('notification_type')->nullable();
            $table->unsignedBigInteger('template_id')->nullable();
            $table->string('recipient_phone');
            $table->string('recipient_name')->nullable();
            $table->text('message_content');
            $table->json('variables')->nullable();
            $table->timestamp('scheduled_at');
            $table->timestamp('sent_at')->nullable();
            $table->string('status')->default('pending');
            $table->text('error_message')->nullable();
            $table->unsignedInteger('attempts')->default(0);
            $table->unsignedInteger('max_attempts')->default(3);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('guard_name');
            $table->string('module')->nullable();
            $table->string('sector')->nullable();
            $table->timestamps();
            $table->unique(['name', 'guard_name']);
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
            $table->unique(['name', 'guard_name']);
        });

        Schema::create('model_has_permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('permission_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->index(['model_id', 'model_type']);
        });

        Schema::create('model_has_roles', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->index(['model_id', 'model_type']);
        });

        Schema::create('role_has_permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('permission_id');
            $table->unsignedBigInteger('role_id');
        });
    }
}
