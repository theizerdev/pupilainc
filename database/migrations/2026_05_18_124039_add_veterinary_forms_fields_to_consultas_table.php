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
        Schema::table('consultas', function (Blueprint $table) {
            // Campos de tratamiento veterinario
            $table->string('tipo_tratamiento')->nullable();
            $table->string('medicamento_administrado')->nullable();
            $table->string('dosis_administrada')->nullable();
            $table->string('via_administracion')->nullable();
            $table->string('frecuencia_tratamiento')->nullable();
            $table->string('sitio_aplicacion')->nullable();
            $table->time('hora_tratamiento')->nullable();
            $table->boolean('reaccion_adversa')->default(false);

            // Campos de procedimiento veterinario
            $table->string('tipo_procedimiento')->nullable();
            $table->string('area_afectada')->nullable();
            $table->text('descripcion_herida')->nullable();
            $table->string('tipo_vendaje')->nullable();
            $table->string('material_utilizado')->nullable();
            $table->string('estado_post_procedimiento')->nullable();
            $table->date('proxima_cura')->nullable();
            $table->string('variacion_peso')->nullable();

            // Campos pre-quirúrgicos veterinarios
            $table->integer('ayuno_horas')->nullable();
            $table->integer('ayuno_agua_horas')->nullable();
            $table->boolean('premedicacion_aplicada')->default(false);
            $table->string('premedicacion_medicamento')->nullable();
            $table->time('premedicacion_hora')->nullable();
            $table->boolean('exam_hemograma')->default(false);
            $table->boolean('exam_bioquimica')->default(false);
            $table->boolean('exam_coagulacion')->default(false);
            $table->boolean('exam_electrolitos')->default(false);
            $table->boolean('exam_radiografia')->default(false);
            $table->boolean('exam_ecocardiograma')->default(false);
            $table->text('exam_observaciones')->nullable();
            $table->string('clasificacion_asa')->nullable();
            $table->string('estado_fisico')->nullable();
            $table->text('factores_riesgo')->nullable();
            $table->boolean('consentimiento_firmado')->default(false);
            $table->boolean('explicacion_riesgos')->default(false);

            // Campos quirúrgicos veterinarios
            $table->string('tipo_cirugia')->nullable();
            $table->string('cirujano_responsable')->nullable();
            $table->string('anestesiologo')->nullable();
            $table->time('hora_inicio_cirugia')->nullable();
            $table->time('hora_fin_cirugia')->nullable();
            $table->integer('duracion_cirugia_min')->nullable();
            $table->text('protocolo_anestesico')->nullable();
            $table->text('medicamentos_quirurgicos')->nullable();
            $table->text('hallazgos_quirurgicos')->nullable();
            $table->string('material_implantado')->nullable();
            $table->string('suturas_utilizadas')->nullable();
            $table->text('complicaciones_quirurgicas')->nullable();
            $table->string('estado_post_operatorio')->nullable();

            // Campos de recuperación veterinaria
            $table->time('hora_recuperacion')->nullable();
            $table->string('estado_conciencia')->nullable();
            $table->string('reflejos_post_operatorios')->nullable();
            $table->decimal('temperatura_recuperacion', 4, 1)->nullable();
            $table->integer('fc_recuperacion')->nullable();
            $table->integer('fr_recuperacion')->nullable();
            $table->string('presion_arterial_recuperacion')->nullable();
            $table->integer('spo2_recuperacion')->nullable();
            $table->string('evaluacion_dolor')->nullable();
            $table->tinyInteger('escala_dolor')->nullable();
            $table->text('analgesia_recuperacion')->nullable();
            $table->string('fluidoterapia_recuperacion')->nullable();
            $table->string('tipo_fluido_recuperacion')->nullable();
            $table->integer('volumen_fluido_ml_hr')->nullable();
            $table->boolean('miccion_recuperacion')->default(false);
            $table->boolean('deposicion_recuperacion')->default(false);
            $table->string('apetito_recuperacion')->nullable();
            $table->string('movilidad_recuperacion')->nullable();
            $table->text('estado_herida_recuperacion')->nullable();
            $table->boolean('sangrado_activo_recuperacion')->default(false);
            $table->text('complicaciones_recuperacion')->nullable();
            $table->text('observaciones_recuperacion')->nullable();

            // Campos de educación/alta veterinaria
            $table->string('diagnostico_final')->nullable();
            $table->text('tratamiento_domiciliario')->nullable();
            $table->text('receta_medicamentos')->nullable();
            $table->text('instrucciones_dosis')->nullable();
            $table->string('duracion_tratamiento_dias')->nullable();
            $table->text('cuidados_herida_alta')->nullable();
            $table->text('alimentacion_alta')->nullable();
            $table->string('restricciones_ejercicio')->nullable();
            $table->text('signos_alerta_alta')->nullable();
            $table->date('fecha_control_alta')->nullable();
            $table->boolean('vacunacion_pendiente_alta')->default(false);
            $table->boolean('desparasitacion_pendiente_alta')->default(false);
            $table->text('recomendaciones_nutricion')->nullable();
            $table->text('recomendaciones_higiene')->nullable();
            $table->text('instrucciones_emergencia')->nullable();
            $table->boolean('propietario_educado')->default(false);
            $table->string('material_educativo_entregado')->nullable();
            $table->text('observaciones_alta')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('consultas', function (Blueprint $table) {
            // Tratamiento
            $table->dropColumn([
                'tipo_tratamiento', 'medicamento_administrado', 'dosis_administrada',
                'via_administracion', 'frecuencia_tratamiento', 'sitio_aplicacion',
                'hora_tratamiento', 'reaccion_adversa'
            ]);

            // Procedimiento
            $table->dropColumn([
                'tipo_procedimiento', 'area_afectada', 'descripcion_herida',
                'tipo_vendaje', 'material_utilizado', 'estado_post_procedimiento',
                'proxima_cura', 'variacion_peso'
            ]);

            // Pre-quirúrgico
            $table->dropColumn([
                'ayuno_horas', 'ayuno_agua_horas', 'premedicacion_aplicada',
                'premedicacion_medicamento', 'premedicacion_hora',
                'exam_hemograma', 'exam_bioquimica', 'exam_coagulacion',
                'exam_electrolitos', 'exam_radiografia', 'exam_ecocardiograma',
                'exam_observaciones', 'clasificacion_asa', 'estado_fisico',
                'factores_riesgo', 'consentimiento_firmado', 'explicacion_riesgos'
            ]);

            // Cirugía
            $table->dropColumn([
                'tipo_cirugia', 'cirujano_responsable', 'anestesiologo',
                'hora_inicio_cirugia', 'hora_fin_cirugia', 'duracion_cirugia_min',
                'protocolo_anestesico', 'medicamentos_quirurgicos', 'hallazgos_quirurgicos',
                'material_implantado', 'suturas_utilizadas', 'complicaciones_quirurgicas',
                'estado_post_operatorio'
            ]);

            // Recuperación
            $table->dropColumn([
                'hora_recuperacion', 'estado_conciencia', 'reflejos_post_operatorios',
                'temperatura_recuperacion', 'fc_recuperacion', 'fr_recuperacion',
                'presion_arterial_recuperacion', 'spo2_recuperacion', 'evaluacion_dolor',
                'escala_dolor', 'analgesia_recuperacion', 'fluidoterapia_recuperacion',
                'tipo_fluido_recuperacion', 'volumen_fluido_ml_hr', 'miccion_recuperacion',
                'deposicion_recuperacion', 'apetito_recuperacion', 'movilidad_recuperacion',
                'estado_herida_recuperacion', 'sangrado_activo_recuperacion',
                'complicaciones_recuperacion', 'observaciones_recuperacion'
            ]);

            // Educación/Alta
            $table->dropColumn([
                'diagnostico_final', 'tratamiento_domiciliario', 'receta_medicamentos',
                'instrucciones_dosis', 'duracion_tratamiento_dias', 'cuidados_herida_alta',
                'alimentacion_alta', 'restricciones_ejercicio', 'signos_alerta_alta',
                'fecha_control_alta', 'vacunacion_pendiente_alta', 'desparasitacion_pendiente_alta',
                'recomendaciones_nutricion', 'recomendaciones_higiene', 'instrucciones_emergencia',
                'propietario_educado', 'material_educativo_entregado', 'observaciones_alta'
            ]);
        });
    }
};
