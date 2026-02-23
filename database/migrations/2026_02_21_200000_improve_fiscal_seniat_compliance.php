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
        // 1. Add fiscal fields to empresas table
        Schema::table('empresas', function (Blueprint $table) {
            if (!Schema::hasColumn('empresas', 'rif_fiscal')) {
                $table->string('rif_fiscal', 20)->nullable()->after('documento');
            }
            if (!Schema::hasColumn('empresas', 'direccion_fiscal')) {
                $table->text('direccion_fiscal')->nullable()->after('direccion');
            }
            if (!Schema::hasColumn('empresas', 'ciudad_fiscal')) {
                $table->string('ciudad_fiscal', 100)->nullable()->after('direccion_fiscal');
            }
            if (!Schema::hasColumn('empresas', 'estado_fiscal')) {
                $table->string('estado_fiscal', 100)->nullable()->after('ciudad_fiscal');
            }
            if (!Schema::hasColumn('empresas', 'codigo_postal_fiscal')) {
                $table->string('codigo_postal_fiscal', 10)->nullable()->after('estado_fiscal');
            }
            if (!Schema::hasColumn('empresas', 'telefono_fiscal')) {
                $table->string('telefono_fiscal', 30)->nullable()->after('telefono');
            }
            if (!Schema::hasColumn('empresas', 'correo_fiscal')) {
                $table->string('correo_fiscal', 255)->nullable()->after('email');
            }
        });

        // 2. Create fiscal_control_sequences table
        Schema::create('fiscal_control_sequences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->foreignId('sucursal_id')->constrained('sucursales')->onDelete('cascade');
            $table->bigInteger('correlativo_actual')->default(0);
            $table->integer('longitud')->default(8);
            $table->string('prefijo', 10)->nullable();
            $table->bigInteger('rango_inicio')->nullable();
            $table->bigInteger('rango_fin')->nullable();
            $table->timestamps();

            $table->unique(['empresa_id', 'sucursal_id']);
        });

        // 3. Add iva_alicuota to pago_detalles table
        if (Schema::hasTable('pago_detalles')) {
            Schema::table('pago_detalles', function (Blueprint $table) {
                if (!Schema::hasColumn('pago_detalles', 'iva_alicuota')) {
                    $table->decimal('iva_alicuota', 5, 2)->default(16.00)->after('exento_iva');
                }
            });
        }

        // 4. Add fiscal desglose fields to pagos table
        Schema::table('pagos', function (Blueprint $table) {
            if (!Schema::hasColumn('pagos', 'base_imponible_general')) {
                $table->decimal('base_imponible_general', 10, 2)->default(0)->after('base_imponible');
            }
            if (!Schema::hasColumn('pagos', 'iva_monto_general')) {
                $table->decimal('iva_monto_general', 10, 2)->default(0)->after('base_imponible_general');
            }
            if (!Schema::hasColumn('pagos', 'base_imponible_reducida')) {
                $table->decimal('base_imponible_reducida', 10, 2)->default(0)->after('iva_monto_general');
            }
            if (!Schema::hasColumn('pagos', 'iva_monto_reducida')) {
                $table->decimal('iva_monto_reducida', 10, 2)->default(0)->after('base_imponible_reducida');
            }
            if (!Schema::hasColumn('pagos', 'condicion_pago')) {
                $table->enum('condicion_pago', ['contado', 'credito'])->default('contado')->after('metodo_pago');
            }
            if (!Schema::hasColumn('pagos', 'seniat_tipo_documento')) {
                $table->string('seniat_tipo_documento', 2)->nullable()->after('tipo_pago');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 4. Drop fiscal desglose fields from pagos
        Schema::table('pagos', function (Blueprint $table) {
            $columns = [
                'seniat_tipo_documento',
                'condicion_pago',
                'iva_monto_reducida',
                'base_imponible_reducida',
                'iva_monto_general',
                'base_imponible_general',
            ];

            $toDrop = array_filter($columns, fn ($col) => Schema::hasColumn('pagos', $col));

            if (!empty($toDrop)) {
                $table->dropColumn($toDrop);
            }
        });

        // 3. Drop iva_alicuota from pago_detalles
        if (Schema::hasTable('pago_detalles') && Schema::hasColumn('pago_detalles', 'iva_alicuota')) {
            Schema::table('pago_detalles', function (Blueprint $table) {
                $table->dropColumn('iva_alicuota');
            });
        }

        // 2. Drop fiscal_control_sequences table
        Schema::dropIfExists('fiscal_control_sequences');

        // 1. Drop fiscal fields from empresas
        Schema::table('empresas', function (Blueprint $table) {
            $columns = [
                'correo_fiscal',
                'telefono_fiscal',
                'codigo_postal_fiscal',
                'estado_fiscal',
                'ciudad_fiscal',
                'direccion_fiscal',
                'rif_fiscal',
            ];

            $toDrop = array_filter($columns, fn ($col) => Schema::hasColumn('empresas', $col));

            if (!empty($toDrop)) {
                $table->dropColumn($toDrop);
            }
        });
    }
};
