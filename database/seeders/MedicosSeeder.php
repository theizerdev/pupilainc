<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Medico;
use App\Models\Empresa;
use App\Models\Sucursal;
use App\Models\Especialidad;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MedicosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $empresa = Empresa::first();

        if (!$empresa) {
            $this->command->error('No se encontró ninguna empresa. Ejecute primero el seeder de empresas.');
            return;
        }

        $sucursales = Sucursal::where('empresa_id', $empresa->id)->get();

        if ($sucursales->isEmpty()) {
            $this->command->error('No se encontraron sucursales. Ejecute primero el seeder de sucursales.');
            return;
        }

        // Obtener especialidades disponibles
        $especialidades = Especialidad::where('empresa_id', $empresa->id)->get();

        if ($especialidades->isEmpty()) {
            $this->command->error('No se encontraron especialidades. Ejecute primero el seeder de especialidades.');
            return;
        }

        $medicosData = $this->getMedicosData();

        foreach ($sucursales as $sucursal) {
            $this->command->info("Creando médicos para sucursal: {$sucursal->nombre}");

            foreach ($medicosData as $index => $medicoData) {
                // Crear usuario
                $email = strtolower(str_replace(' ', '.', $medicoData['nombres'])) . '.' .
                         strtolower(str_replace(' ', '.', $medicoData['apellidos'])) . '@' .
                         config('app.name') . '.com';

                // Asegurar email único
                $emailBase = $email;
                $counter = 1;
                while (User::where('email', $email)->exists()) {
                    $email = str_replace('@', "{$counter}@", $emailBase);
                    $counter++;
                }

                $username = strtolower(str_replace(' ', '', $medicoData['nombres'])) . '.' .
                           strtolower(str_replace(' ', '', $medicoData['apellidos']));

                $usernameBase = $username;
                $counter = 1;
                while (User::where('username', $username)->exists()) {
                    $username = "{$usernameBase}{$counter}";
                    $counter++;
                }

                $user = User::create([
                    'name' => "{$medicoData['nombres']} {$medicoData['apellidos']}",
                    'username' => $username,
                    'email' => $email,
                    'email_verified_at' => now(),
                    'password' => Hash::make('password123'), // Contraseña por defecto
                    'phone' => $medicoData['telefono'],
                    'empresa_id' => $empresa->id,
                    'sucursal_id' => $sucursal->id,
                    'status' => true,
                    'whatsapp_verification_enabled' => true,
                ]);

                // Asignar rol de médico
                $user->assignRole('Medico');

                // Crear registro de médico
                $medico = Medico::create([
                    'user_id' => $user->id,
                    'nombres' => $medicoData['nombres'],
                    'apellidos' => $medicoData['apellidos'],
                    'documento_identidad' => $medicoData['documento_identidad'],
                    'telefono' => $medicoData['telefono'],
                    'direccion' => $medicoData['direccion'],
                    'licencia_medica' => $medicoData['licencia_medica'],
                    'anios_experiencia' => $medicoData['anios_experiencia'],
                    'nivel_experiencia' => $medicoData['nivel_experiencia'],
                    'status' => true,
                    'empresa_id' => $empresa->id,
                    'sucursal_id' => $sucursal->id,
                ]);

                // Asignar especialidades al médico
                $especialidadesAsignadas = $medicoData['especialidades'];

                foreach ($especialidadesAsignadas as $espCodigo) {
                    $especialidad = $especialidades->firstWhere('codigo', $espCodigo);

                    if ($especialidad) {
                        $medico->especialidades()->attach($especialidad->id, [
                            'tarifa_consulta' => $medicoData['tarifa_consulta'] ?? 50.00,
                            'horario_atencion' => json_encode([
                                'lunes' => ['inicio' => '08:00', 'fin' => '16:00'],
                                'martes' => ['inicio' => '08:00', 'fin' => '16:00'],
                                'miércoles' => ['inicio' => '08:00', 'fin' => '16:00'],
                                'jueves' => ['inicio' => '08:00', 'fin' => '16:00'],
                                'viernes' => ['inicio' => '08:00', 'fin' => '14:00'],
                            ]),
                            'status' => true,
                        ]);
                    }
                }

                $this->command->info("  ✓ Dr. {$medicoData['nombres']} {$medicoData['apellidos']} creado");
            }
        }

        $this->command->info('Médicos creados exitosamente!');
    }

    /**
     * Datos de médicos de ejemplo
     */
    private function getMedicosData(): array
    {
        return [
            // Oftalmología
            [
                'nombres' => 'Carlos Alberto',
                'apellidos' => 'Rodríguez Martínez',
                'documento_identidad' => 'V-12345678',
                'telefono' => '+584141234567',
                'direccion' => 'Av. Principal, Edif. Médico, Piso 3, Consultorio 301',
                'licencia_medica' => 'LM-2024-001',
                'anios_experiencia' => 15,
                'nivel_experiencia' => 'Avanzado',
                'especialidades' => ['OFTAL'],
                'tarifa_consulta' => 60.00,
            ],
            [
                'nombres' => 'María Elena',
                'apellidos' => 'González Pérez',
                'documento_identidad' => 'V-23456789',
                'telefono' => '+584142345678',
                'direccion' => 'Calle 5ta, Centro Médico, Piso 2, Consultorio 205',
                'licencia_medica' => 'LM-2024-002',
                'anios_experiencia' => 12,
                'nivel_experiencia' => 'Avanzado',
                'especialidades' => ['OFTAL'],
                'tarifa_consulta' => 55.00,
            ],

            // Medicina General
            [
                'nombres' => 'José Luis',
                'apellidos' => 'Hernández López',
                'documento_identidad' => 'V-34567890',
                'telefono' => '+584143456789',
                'direccion' => 'Av. Bolívar, Torre Salud, Piso 1, Consultorio 102',
                'licencia_medica' => 'LM-2024-003',
                'anios_experiencia' => 10,
                'nivel_experiencia' => 'Intermedio',
                'especialidades' => ['MED-GEN'],
                'tarifa_consulta' => 40.00,
            ],
            [
                'nombres' => 'Ana Patricia',
                'apellidos' => 'Ramírez Silva',
                'documento_identidad' => 'V-45678901',
                'telefono' => '+584144567890',
                'direccion' => 'Calle 10, Edif. San Rafael, Piso 4, Consultorio 401',
                'licencia_medica' => 'LM-2024-004',
                'anios_experiencia' => 8,
                'nivel_experiencia' => 'Intermedio',
                'especialidades' => ['MED-GEN'],
                'tarifa_consulta' => 35.00,
            ],

            // Cardiología
            [
                'nombres' => 'Roberto Carlos',
                'apellidos' => 'Díaz Morales',
                'documento_identidad' => 'V-56789012',
                'telefono' => '+584145678901',
                'direccion' => 'Av. Libertador, Centro Cardiovascular, Piso 5',
                'licencia_medica' => 'LM-2024-005',
                'anios_experiencia' => 20,
                'nivel_experiencia' => 'Avanzado',
                'especialidades' => ['CARDIO'],
                'tarifa_consulta' => 70.00,
            ],

            // Ginecología
            [
                'nombres' => 'Laura Isabel',
                'apellidos' => 'Torres Vargas',
                'documento_identidad' => 'V-67890123',
                'telefono' => '+584146789012',
                'direccion' => 'Calle 15, Clínica Femenina, Piso 3, Consultorio 302',
                'licencia_medica' => 'LM-2024-006',
                'anios_experiencia' => 14,
                'nivel_experiencia' => 'Avanzado',
                'especialidades' => ['GINECO'],
                'tarifa_consulta' => 65.00,
            ],

            // Pediatría
            [
                'nombres' => 'Francisco Javier',
                'apellidos' => 'Moreno Castillo',
                'documento_identidad' => 'V-78901234',
                'telefono' => '+584147890123',
                'direccion' => 'Av. Niños, Hospital Pediátrico, Piso 2',
                'licencia_medica' => 'LM-2024-007',
                'anios_experiencia' => 11,
                'nivel_experiencia' => 'Intermedio',
                'especialidades' => ['PEDIAT'],
                'tarifa_consulta' => 45.00,
            ],

            // Neurología
            [
                'nombres' => 'Alejandro Manuel',
                'apellidos' => 'Sánchez Ríos',
                'documento_identidad' => 'V-89012345',
                'telefono' => '+584148901234',
                'direccion' => 'Calle Neurología, Centro Neurológico, Piso 4',
                'licencia_medica' => 'LM-2024-008',
                'anios_experiencia' => 18,
                'nivel_experiencia' => 'Avanzado',
                'especialidades' => ['NEURO'],
                'tarifa_consulta' => 75.00,
            ],

            // Gastroenterología
            [
                'nombres' => 'Patricia Carolina',
                'apellidos' => 'Mendoza Flores',
                'documento_identidad' => 'V-90123456',
                'telefono' => '+584149012345',
                'direccion' => 'Av. Digestiva, Centro Gastro, Piso 3, Consultorio 305',
                'licencia_medica' => 'LM-2024-009',
                'anios_experiencia' => 13,
                'nivel_experiencia' => 'Avanzado',
                'especialidades' => ['GASTRO'],
                'tarifa_consulta' => 60.00,
            ],

            // Cirugía General
            [
                'nombres' => 'Miguel Ángel',
                'apellidos' => 'Vargas Jiménez',
                'documento_identidad' => 'V-01234567',
                'telefono' => '+584140123456',
                'direccion' => 'Av. Quirúrgica, Hospital Central, Bloque C, Piso 2',
                'licencia_medica' => 'LM-2024-010',
                'anios_experiencia' => 16,
                'nivel_experiencia' => 'Avanzado',
                'especialidades' => ['CIR-GEN'],
                'tarifa_consulta' => 80.00,
            ],

            // Medicina Interna
            [
                'nombres' => 'Carmen Rosa',
                'apellidos' => 'Navarro Pineda',
                'documento_identidad' => 'V-11223344',
                'telefono' => '+584141122334',
                'direccion' => 'Calle Interna, Centro Médico Integral, Piso 5, Consultorio 501',
                'licencia_medica' => 'LM-2024-011',
                'anios_experiencia' => 17,
                'nivel_experiencia' => 'Avanzado',
                'especialidades' => ['MED-INT'],
                'tarifa_consulta' => 65.00,
            ],

            // Nefrología
            [
                'nombres' => 'Eduardo José',
                'apellidos' => 'Castillo Reyes',
                'documento_identidad' => 'V-22334455',
                'telefono' => '+584142233445',
                'direccion' => 'Av. Renal, Centro Nefrológico, Piso 2',
                'licencia_medica' => 'LM-2024-012',
                'anios_experiencia' => 14,
                'nivel_experiencia' => 'Avanzado',
                'especialidades' => ['NEFRO'],
                'tarifa_consulta' => 70.00,
            ],

            // Otorrinolaringología
            [
                'nombres' => 'Gabriela María',
                'apellidos' => 'Ortiz Delgado',
                'documento_identidad' => 'V-33445566',
                'telefono' => '+584143344556',
                'direccion' => 'Calle ORL, Centro Auditivo, Piso 3, Consultorio 303',
                'licencia_medica' => 'LM-2024-013',
                'anios_experiencia' => 9,
                'nivel_experiencia' => 'Intermedio',
                'especialidades' => ['ORL'],
                'tarifa_consulta' => 50.00,
            ],

            // Neurocirugía
            [
                'nombres' => 'Ricardo Antonio',
                'apellidos' => 'Peña Guerrero',
                'documento_identidad' => 'V-44556677',
                'telefono' => '+584144455667',
                'direccion' => 'Av. Neurocirugía, Hospital Neuroquirúrgico, Piso 6',
                'licencia_medica' => 'LM-2024-014',
                'anios_experiencia' => 22,
                'nivel_experiencia' => 'Avanzado',
                'especialidades' => ['NEUROCI'],
                'tarifa_consulta' => 100.00,
            ],

            // Cirugía Pediátrica
            [
                'nombres' => 'Valentina Sofía',
                'apellidos' => 'Rojas Méndez',
                'documento_identidad' => 'V-55667788',
                'telefono' => '+584145566778',
                'direccion' => 'Calle Pediátrica, Hospital Infantil, Bloque B, Piso 3',
                'licencia_medica' => 'LM-2024-015',
                'anios_experiencia' => 12,
                'nivel_experiencia' => 'Intermedio',
                'especialidades' => ['CIR-PED'],
                'tarifa_consulta' => 75.00,
            ],

            // Mastología
            [
                'nombres' => 'Adriana Beatriz',
                'apellidos' => 'Campos Herrera',
                'documento_identidad' => 'V-66778899',
                'telefono' => '+584146677889',
                'direccion' => 'Av. Mastología, Centro Mamario, Piso 4, Consultorio 402',
                'licencia_medica' => 'LM-2024-016',
                'anios_experiencia' => 15,
                'nivel_experiencia' => 'Avanzado',
                'especialidades' => ['MASTOL'],
                'tarifa_consulta' => 70.00,
            ],
        ];
    }
}
