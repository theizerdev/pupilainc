<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Paciente;
use App\Models\Empresa;
use App\Models\Sucursal;
use Carbon\Carbon;

class PacienteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $empresa = Empresa::first();
        $sucursal = Sucursal::first();

        if (!$empresa || !$sucursal) {
            $this->command->error('Empresa o Sucursal no encontradas. Ejecuta primero los seeders de Empresa y Sucursal.');
            return;
        }

        // 10 pacientes FEMENINOS
        $pacientesFemeninos = [
            [
                'nombres' => 'María Elena',
                'apellidos' => 'González Rodríguez',
                'documento_identidad' => 'V-12345678',
                'telefono' => '+58 412-1234567',
                'email' => 'maria.gonzalez@email.com',
                'direccion' => 'Av. Principal, Edif. Los Pinos, Apto 5-B',
                'fecha_nacimiento' => '1985-03-15',
                'genero' => 'femenino',
                'estado_civil' => 'casada',
                'ocupacion' => 'Profesora',
                'nacionalidad' => 'Venezolana',
            ],
            [
                'nombres' => 'Ana Carolina',
                'apellidos' => 'Martínez Pérez',
                'documento_identidad' => 'V-15678901',
                'telefono' => '+58 414-2345678',
                'email' => 'ana.martinez@email.com',
                'direccion' => 'Calle 10, Casa Nº 25, Urb. Las Flores',
                'fecha_nacimiento' => '1990-07-22',
                'genero' => 'femenino',
                'estado_civil' => 'soltera',
                'ocupacion' => 'Ingeniera',
                'nacionalidad' => 'Venezolana',
            ],
            [
                'nombres' => 'Carmen Rosa',
                'apellidos' => 'López Hernández',
                'documento_identidad' => 'V-18901234',
                'telefono' => '+58 416-3456789',
                'email' => 'carmen.lopez@email.com',
                'direccion' => 'Av. Bolívar, Centro Comercial Plaza, Local 12',
                'fecha_nacimiento' => '1978-11-08',
                'genero' => 'femenino',
                'estado_civil' => 'divorciada',
                'ocupacion' => 'Comerciante',
                'nacionalidad' => 'Venezolana',
            ],
            [
                'nombres' => 'Laura Isabel',
                'apellidos' => 'Rodríguez Silva',
                'documento_identidad' => 'V-20123456',
                'telefono' => '+58 424-4567890',
                'email' => 'laura.rodriguez@email.com',
                'direccion' => 'Calle 5, Residencias El Parque, Torre A, Piso 3',
                'fecha_nacimiento' => '1995-01-30',
                'genero' => 'femenino',
                'estado_civil' => 'soltera',
                'ocupacion' => 'Estudiante',
                'nacionalidad' => 'Venezolana',
            ],
            [
                'nombres' => 'Patricia Alejandra',
                'apellidos' => 'Fernández Díaz',
                'documento_identidad' => 'V-22345678',
                'telefono' => '+58 426-5678901',
                'email' => 'patricia.fernandez@email.com',
                'direccion' => 'Av. Libertador, Edif. Miraflores, Apto 10-C',
                'fecha_nacimiento' => '1988-05-17',
                'genero' => 'femenino',
                'estado_civil' => 'casada',
                'ocupacion' => 'Médico',
                'nacionalidad' => 'Venezolana',
            ],
            [
                'nombres' => 'Gabriela Sofía',
                'apellidos' => 'Torres Mendoza',
                'documento_identidad' => 'V-24567890',
                'telefono' => '+58 412-6789012',
                'email' => 'gabriela.torres@email.com',
                'direccion' => 'Calle 15, Urb. Santa María, Casa 8',
                'fecha_nacimiento' => '1992-09-25',
                'genero' => 'femenino',
                'estado_civil' => 'soltera',
                'ocupacion' => 'Diseñadora Gráfica',
                'nacionalidad' => 'Venezolana',
            ],
            [
                'nombres' => 'Valentina María',
                'apellidos' => 'Castro Ramírez',
                'documento_identidad' => 'V-26789012',
                'telefono' => '+58 414-7890123',
                'email' => 'valentina.castro@email.com',
                'direccion' => 'Av. Universidad, Residencias Universitarias, Bloque 2',
                'fecha_nacimiento' => '1998-12-03',
                'genero' => 'femenino',
                'estado_civil' => 'soltera',
                'ocupacion' => 'Estudiante',
                'nacionalidad' => 'Venezolana',
            ],
            [
                'nombres' => 'Daniela Fernanda',
                'apellidos' => 'Vargas Morales',
                'documento_identidad' => 'V-28901234',
                'telefono' => '+58 416-8901234',
                'email' => 'daniela.vargas@email.com',
                'direccion' => 'Calle 20, Edif. Central, Apto 7-A',
                'fecha_nacimiento' => '1982-04-14',
                'genero' => 'femenino',
                'estado_civil' => 'casada',
                'ocupacion' => 'Abogada',
                'nacionalidad' => 'Venezolana',
            ],
            [
                'nombres' => 'Isabella Cristina',
                'apellidos' => 'Jiménez Ortega',
                'documento_identidad' => 'V-30123456',
                'telefono' => '+58 424-9012345',
                'email' => 'isabella.jimenez@email.com',
                'direccion' => 'Av. Principal de Las Mercedes, Torre Europa, Piso 15',
                'fecha_nacimiento' => '1996-08-19',
                'genero' => 'femenino',
                'estado_civil' => 'soltera',
                'ocupacion' => 'Periodista',
                'nacionalidad' => 'Venezolana',
            ],
            [
                'nombres' => 'Sofía Valentina',
                'apellidos' => 'Ruíz Delgado',
                'documento_identidad' => 'V-32345678',
                'telefono' => '+58 426-0123456',
                'email' => 'sofia.ruiz@email.com',
                'direccion' => 'Calle 8, Urb. El Rosal, Casa 15',
                'fecha_nacimiento' => '1987-06-28',
                'genero' => 'femenino',
                'estado_civil' => 'viuda',
                'ocupacion' => 'Enfermera',
                'nacionalidad' => 'Venezolana',
            ],
        ];

        // 10 pacientes MASCULINOS
        $pacientesMasculinos = [
            [
                'nombres' => 'Carlos Alberto',
                'apellidos' => 'Pérez García',
                'documento_identidad' => 'V-11234567',
                'telefono' => '+58 412-1111111',
                'email' => 'carlos.perez@email.com',
                'direccion' => 'Av. Francisco de Miranda, Edif. Torre Norte, Apto 12-D',
                'fecha_nacimiento' => '1980-02-10',
                'genero' => 'masculino',
                'estado_civil' => 'casado',
                'ocupacion' => 'Arquitecto',
                'nacionalidad' => 'Venezolano',
            ],
            [
                'nombres' => 'José Luis',
                'apellidos' => 'Hernández Castro',
                'documento_identidad' => 'V-13456789',
                'telefono' => '+58 414-2222222',
                'email' => 'jose.hernandez@email.com',
                'direccion' => 'Calle 3, Residencias Los Caobos, Torre B, Piso 5',
                'fecha_nacimiento' => '1975-09-05',
                'genero' => 'masculino',
                'estado_civil' => 'casado',
                'ocupacion' => 'Contador',
                'nacionalidad' => 'Venezolano',
            ],
            [
                'nombres' => 'Miguel Ángel',
                'apellidos' => 'Sánchez Romero',
                'documento_identidad' => 'V-16789012',
                'telefono' => '+58 416-3333333',
                'email' => 'miguel.sanchez@email.com',
                'direccion' => 'Av. Sucre, Centro Empresarial Plaza, Oficina 301',
                'fecha_nacimiento' => '1993-04-18',
                'genero' => 'masculino',
                'estado_civil' => 'soltero',
                'ocupacion' => 'Programador',
                'nacionalidad' => 'Venezolano',
            ],
            [
                'nombres' => 'Roberto Carlos',
                'apellidos' => 'Díaz Moreno',
                'documento_identidad' => 'V-19012345',
                'telefono' => '+58 424-4444444',
                'email' => 'roberto.diaz@email.com',
                'direccion' => 'Calle 12, Urb. La Castellana, Casa 22',
                'fecha_nacimiento' => '1986-11-23',
                'genero' => 'masculino',
                'estado_civil' => 'soltero',
                'ocupacion' => 'Chef',
                'nacionalidad' => 'Venezolano',
            ],
            [
                'nombres' => 'Alejandro José',
                'apellidos' => 'Ramírez Flores',
                'documento_identidad' => 'V-21234567',
                'telefono' => '+58 426-5555555',
                'email' => 'alejandro.ramirez@email.com',
                'direccion' => 'Av. Principal de Chacao, Edif. Atlantic, Piso 8',
                'fecha_nacimiento' => '1991-07-07',
                'genero' => 'masculino',
                'estado_civil' => 'soltero',
                'ocupacion' => 'Marketing Digital',
                'nacionalidad' => 'Venezolano',
            ],
            [
                'nombres' => 'Fernando Andrés',
                'apellidos' => 'Navarro Guzmán',
                'documento_identidad' => 'V-23456789',
                'telefono' => '+58 412-6666666',
                'email' => 'fernando.navarro@email.com',
                'direccion' => 'Calle 18, Residencias Altamira, Torre C, Apto 9-B',
                'fecha_nacimiento' => '1984-01-12',
                'genero' => 'masculino',
                'estado_civil' => 'casado',
                'ocupacion' => 'Gerente de Ventas',
                'nacionalidad' => 'Venezolano',
            ],
            [
                'nombres' => 'Diego Sebastián',
                'apellidos' => 'Morales Herrera',
                'documento_identidad' => 'V-25678901',
                'telefono' => '+58 414-7777777',
                'email' => 'diego.morales@email.com',
                'direccion' => 'Av. Boyacá, Centro Comercial San Ignacio, Local 45',
                'fecha_nacimiento' => '1997-03-26',
                'genero' => 'masculino',
                'estado_civil' => 'soltero',
                'ocupacion' => 'Estudiante',
                'nacionalidad' => 'Venezolano',
            ],
            [
                'nombres' => 'Ricardo Antonio',
                'apellidos' => 'Silva Campos',
                'documento_identidad' => 'V-27890123',
                'telefono' => '+58 416-8888888',
                'email' => 'ricardo.silva@email.com',
                'direccion' => 'Calle 25, Urb. Prados del Este, Casa 10',
                'fecha_nacimiento' => '1979-10-15',
                'genero' => 'masculino',
                'estado_civil' => 'divorciado',
                'ocupacion' => 'Fotógrafo',
                'nacionalidad' => 'Venezolano',
            ],
            [
                'nombres' => 'Andrés Felipe',
                'apellidos' => 'Gutiérrez Reyes',
                'documento_identidad' => 'V-29012345',
                'telefono' => '+58 424-9999999',
                'email' => 'andres.gutierrez@email.com',
                'direccion' => 'Av. Las Acacias, Edif. Las Acacias, Apto 14-A',
                'fecha_nacimiento' => '1994-06-09',
                'genero' => 'masculino',
                'estado_civil' => 'soltero',
                'ocupacion' => 'Músico',
                'nacionalidad' => 'Venezolano',
            ],
            [
                'nombres' => 'Jorge Eduardo',
                'apellidos' => 'Vega Pacheco',
                'documento_identidad' => 'V-31234567',
                'telefono' => '+58 426-0000000',
                'email' => 'jorge.vega@email.com',
                'direccion' => 'Calle 30, Residencias El Conde, Torre A, Piso 11',
                'fecha_nacimiento' => '1989-12-20',
                'genero' => 'masculino',
                'estado_civil' => 'casado',
                'ocupacion' => 'Piloto',
                'nacionalidad' => 'Venezolano',
            ],
        ];

        // Insertar pacientes femeninos
        foreach ($pacientesFemeninos as $paciente) {
            Paciente::create(array_merge($paciente, [
                'empresa_id' => $empresa->id,
                'sucursal_id' => $sucursal->id,
                'status' => true,
            ]));
        }

        // Insertar pacientes masculinos
        foreach ($pacientesMasculinos as $paciente) {
            Paciente::create(array_merge($paciente, [
                'empresa_id' => $empresa->id,
                'sucursal_id' => $sucursal->id,
                'status' => true,
            ]));
        }

        $this->command->info('✅ 20 pacientes creados exitosamente (10 femeninos, 10 masculinos)');
    }
}
