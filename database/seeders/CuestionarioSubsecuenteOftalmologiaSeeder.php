<?php

namespace Database\Seeders;

use App\Models\Cuestionario;
use App\Models\Empresa;
use App\Models\Especialidad;
use App\Models\Pregunta;
use Illuminate\Database\Seeder;

class CuestionarioSubsecuenteOftalmologiaSeeder extends Seeder
{
    public function run(): void
    {
        $empresa      = Empresa::first();
        $especialidad = Especialidad::where('codigo', 'OFTAL')->where('empresa_id', $empresa->id)->first();

        if (!$especialidad) {
            $this->command->warn('Especialidad OFTAL no encontrada.');
            return;
        }

        // Eliminar cuestionarios subsecuentes existentes para esta especialidad
        Cuestionario::where('empresa_id', $empresa->id)
            ->where('especialidad_id', $especialidad->id)
            ->where('tipo', 'subsecuente')
            ->delete();

        $c = Cuestionario::create([
            'titulo'          => 'Formulario B — Paciente Subsecuente (Oftalmología)',
            'descripcion'     => 'Cuestionario breve para pacientes de seguimiento en oftalmología.',
            'tipo'            => 'subsecuente',
            'activo'          => true,
            'empresa_id'      => $empresa->id,
            'especialidad_id' => $especialidad->id,
        ]);

        // NOTA: Los campos PRE-LLENADO CRM se generan automáticamente desde el componente Livewire
        // No se crean como preguntas en la base de datos

        $this->preguntas($c->id, [
            // SECCIÓN: HOY
            ['¿Qué te trae hoy con nosotros?', 'Cuéntanos brevemente el motivo de tu visita', 'texto', null, true, 1],
            ['¿Algo nuevo sobre tu salud en general que quieras compartir con el médico?', 'Puedes dejarlo en blanco si no hay nada nuevo', 'texto', null, false, 2],

            // SECCIÓN: TU VISIÓN
            ['¿Cambió algo en tu visión desde tu última visita?', 'Si Sí, cuéntanos brevemente qué cambió', 'si_no_detalle', null, true, 3],

            // SECCIÓN: ESTUDIOS
            ['¿Traes los estudios solicitados en tu última cita?', null, 'estudios_subsecuente', null, false, 4],

            // SECCIÓN: LOGÍSTICA
            ['¿Requiere factura?', 'Si Sí, se solicitará RFC y razón social', 'si_no_factura', null, false, 5],
        ]);

        $this->command->info('✓ Cuestionario Subsecuente Oftalmología creado con 5 preguntas según Formulario B.');
    }

    private function preguntas(int $cid, array $items): void
    {
        foreach ($items as $i) {
            Pregunta::create([
                'cuestionario_id' => $cid,
                'titulo'          => $i[0],
                'descripcion'     => $i[1],
                'tipo'            => $i[2],
                'opciones'        => $i[3],
                'obligatorio'     => $i[4],
                'orden'           => $i[5],
                'activo'          => true,
            ]);
        }
    }
}
