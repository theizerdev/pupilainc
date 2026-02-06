<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('whatsapp_scheduled_messages', function (Blueprint $table) {
            $table->unsignedBigInteger('cita_id')->nullable()->after('id');
            $table->unsignedBigInteger('empresa_id')->nullable()->after('cita_id');
            $table->string('notification_type')->nullable()->after('empresa_id');

            $table->foreign('cita_id')->references('id')->on('citas')->onDelete('set null');
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('set null');

            $table->unique(['cita_id', 'notification_type', 'recipient_phone'], 'wsm_cita_type_phone_unique');
        });
    }

    public function down(): void
    {
        Schema::table('whatsapp_scheduled_messages', function (Blueprint $table) {
            $table->dropUnique('wsm_cita_type_phone_unique');
            $table->dropForeign(['empresa_id']);
            $table->dropForeign(['cita_id']);
            $table->dropColumn(['notification_type', 'empresa_id', 'cita_id']);
        });
    }
};
