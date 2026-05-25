<?php

namespace Database\Seeders;

use App\Models\ConfiguracionNotificacion;
use App\Models\Empresa;
use Illuminate\Database\Seeder;

class ConfiguracionNotificacionesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $empresas = Empresa::all();

        foreach ($empresas as $empresa) {
            // Configuración recomendada para pacientes
            $configPaciente = [
                'confirmada' => true,           // ✅ Enviar confirmación
                'sala_espera' => false,         // ❌ No enviar (interno)
                'en_enfermeria' => false,       // ❌ No enviar (interno)
                'en_consultorio' => false,      // ❌ No enviar (interno)
                'en_consultorio_optometrista' => false, // ❌ No enviar (interno)
                'en_gotas' => false,            // ❌ No enviar (interno)
                'dilatado' => false,            // ❌ No enviar (interno)
                'en_optica' => false,           // ❌ No enviar (interno)
                'en_estudio' => false,          // ❌ No enviar (interno)
                'finalizada' => false,          // ❌ No enviar
                'pagada' => true,               // ✅ Enviar confirmación de pago
                'cancelada' => true,            // ✅ Enviar cancelación
                'no_asistio' => false,          // ❌ No enviar
            ];

            // Configuración recomendada para doctores
            $configDoctor = [
                'confirmada' => true,           // ✅ Agenda confirmada
                'sala_espera' => true,          // ⚠️ Paciente llegó (opcional)
                'en_enfermeria' => false,       // ❌ No enviar
                'en_consultorio' => false,      // ❌ No enviar (ya está presente)
                'en_consultorio_optometrista' => false, // ❌ No enviar
                'en_gotas' => false,            // ❌ No enviar
                'dilatado' => false,            // ❌ No enviar
                'en_optica' => false,           // ❌ No enviar
                'en_estudio' => false,          // ❌ No enviar
                'finalizada' => false,          // ❌ No enviar
                'pagada' => false,              // ❌ No enviar (administrativo)
                'cancelada' => true,            // ✅ Liberar agenda
                'no_asistio' => false,          // ❌ No enviar
            ];

            // Guardar configuración para esta empresa
            ConfiguracionNotificacion::guardarConfiguracion($empresa->id, [
                'paciente' => $configPaciente,
                'doctor' => $configDoctor,
            ]);
        }

        $this->command->info('✅ Configuración de notificaciones inicializada correctamente.');
    }
}
