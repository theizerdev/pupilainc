<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('producto_variantes', function (Blueprint $table) {
            if (!Schema::hasColumn('producto_variantes', 'tipo_variante_id')) {
                $table->foreignId('tipo_variante_id')->nullable()->after('producto_id')->constrained('tipo_variantes')->nullOnDelete();
            }
            if (!Schema::hasColumn('producto_variantes', 'valor_variante_id')) {
                $table->foreignId('valor_variante_id')->nullable()->after('tipo_variante_id')->constrained('valor_variantes')->nullOnDelete();
            }
            if (!Schema::hasColumn('producto_variantes', 'tamano')) {
                $table->string('tamano')->nullable()->after('imagen');
            }
            if (!Schema::hasColumn('producto_variantes', 'peso')) {
                $table->decimal('peso', 10, 3)->nullable()->after('tamano');
            }
            if (!Schema::hasColumn('producto_variantes', 'presentacion')) {
                $table->string('presentacion')->nullable()->after('peso');
            }
            if (!Schema::hasColumn('producto_variantes', 'unidad_medida')) {
                $table->string('unidad_medida', 30)->nullable()->after('presentacion');
            }
            if (!Schema::hasColumn('producto_variantes', 'orden')) {
                $table->integer('orden')->default(0)->after('unidad_medida');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('producto_variantes', function (Blueprint $table) {
            $columns = [];
            if (Schema::hasColumn('producto_variantes', 'tipo_variante_id')) {
                $table->dropForeign(['tipo_variante_id']);
                $columns[] = 'tipo_variante_id';
            }
            if (Schema::hasColumn('producto_variantes', 'valor_variante_id')) {
                $table->dropForeign(['valor_variante_id']);
                $columns[] = 'valor_variante_id';
            }
            foreach (['tamano','peso','presentacion','unidad_medida','orden'] as $col) {
                if (Schema::hasColumn('producto_variantes', $col)) $columns[] = $col;
            }
            if (!empty($columns)) $table->dropColumn($columns);
        });
    }
};
