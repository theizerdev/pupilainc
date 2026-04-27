<?php

namespace Database\Seeders;

use App\Models\Empresa;
use App\Models\Especialidad;
use App\Models\EspecialidadPlantilla;

class EFCardiologiaSeeder extends BaseEstadoFormularioSeeder
{
    public function run(): void
    {
        $empresa      = Empresa::first();
        $especialidad = Especialidad::where('codigo', 'CARDIO')->where('empresa_id', $empresa->id)->first();
        $plantilla    = $especialidad
            ? EspecialidadPlantilla::where('especialidad_id', $especialidad->id)->where('activo', true)->latest()->first()
            : null;

        if (!$plantilla) { $this->command->warn('Cardiología — plantilla no encontrada.'); return; }

        // ── EN ENFERMERÍA ─────────────────────────────────────────────────────
        $ef = $this->formularioEstado($plantilla, 'en_enfermeria', 'Triage Cardiológico');

        $s = $this->seccion($plantilla, $ef, 'Síntomas Cardiovasculares', 'fa-heartbeat', '#F44336', 1);
        $this->campos($s->id, [
            ['motivo', 'Motivo de Consulta', 'select',
                ['Control rutinario', 'Dolor precordial', 'Palpitaciones', 'Disnea', 'Síncope', 'Edemas', 'HTA descompensada', 'Otro'],
                true, null, null, null, 6],
            ['escala_dolor', 'Intensidad Dolor (0-10)', 'range', null, false, '0', null, null, 6],
            ['irradiacion', 'Irradiación del Dolor', 'checkbox',
                ['Sin irradiación', 'Brazo izquierdo', 'Mandíbula', 'Espalda', 'Epigastrio'],
                false, null, null, null, 6],
            ['factores_riesgo', 'Factores de Riesgo', 'checkbox',
                ['HTA', 'Diabetes', 'Tabaquismo', 'Dislipidemia', 'Obesidad', 'Antecedente familiar', 'Sedentarismo'],
                false, null, null, null, 6],
            ['medicacion_cardiaca', 'Medicación Cardíaca Actual', 'textarea', null, false, null, null, null, 12],
        ]);

        // ── EN ESTUDIO ────────────────────────────────────────────────────────
        $ef2 = $this->formularioEstado($plantilla, 'en_estudio', 'Estudios Cardiológicos');

        $s2 = $this->seccion($plantilla, $ef2, 'Estudios Solicitados', 'fa-heartbeat', '#EC407A', 1);
        $this->campos($s2->id, [
            ['estudios', 'Estudios', 'checkbox',
                ['ECG 12 derivaciones', 'Ecocardiograma', 'Holter 24h', 'MAPA', 'Prueba de esfuerzo', 'Rx Tórax', 'Troponinas', 'BNP/NT-proBNP', 'Perfil lipídico'],
                false, null, null, null, 12],
            ['ecg_realizado', 'ECG Realizado en Sala', 'radio', ['Sí', 'No'], false, 'No', null, null, 6],
            ['resultado_ecg', 'Resultado ECG', 'select',
                ['Normal', 'Ritmo sinusal', 'FA', 'Flutter', 'Bloqueo AV', 'Bloqueo rama', 'Cambios ST-T', 'Otro'],
                false, null, null, null, 6],
            ['observaciones', 'Observaciones', 'textarea', null, false, null, null, null, 12],
        ]);

        $this->command->info('  ✓ Cardiología — 2 estados configurados');
    }
}
