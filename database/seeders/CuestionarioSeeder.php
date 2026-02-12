<?php

namespace Database\Seeders;

use App\Models\Cuestionario;
use App\Models\Pregunta;
use App\Models\Empresa;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CuestionarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener la primera empresa (o crear una si no existe)
        $empresa = Empresa::first();
        
        if (!$empresa) {
            $this->command->info('No se encontró ninguna empresa. Por favor, crea una empresa primero.');
            return;
        }

        // Crear cuestionario de pre-consulta
        $cuestionario = Cuestionario::create([
            'empresa_id' => $empresa->id,
            'titulo' => 'Cuestionario de Pre-consulta Médica',
            'descripcion' => 'Formulario para recopilar información médica antes de la consulta',
            'tipo' => 'preconsulta',
            'activo' => true,
        ]);

        // Preguntas del cuestionario
        $preguntas = [
            [
                'titulo' => '¿Cuál es el motivo de su consulta hoy?',
                'descripcion' => 'Describa los síntomas o razones principales',
                'tipo' => 'texto',
                'obligatorio' => true,
                'orden' => 1,
            ],
            [
                'titulo' => '¿Desde cuándo presenta estos síntomas?',
                'descripcion' => 'Indique la duración aproximada',
                'tipo' => 'texto',
                'obligatorio' => true,
                'orden' => 2,
            ],
            [
                'titulo' => '¿Ha tenido fiebre en los últimos días?',
                'descripcion' => null,
                'tipo' => 'si_no',
                'obligatorio' => true,
                'orden' => 3,
            ],
            [
                'titulo' => '¿Tiene alergias a medicamentos?',
                'descripcion' => 'Si es sí, especifique cuáles',
                'tipo' => 'si_no',
                'obligatorio' => true,
                'orden' => 4,
            ],
            [
                'titulo' => '¿Qué medicamentos está tomando actualmente?',
                'descripcion' => 'Incluya medicamentos recetados y de venta libre',
                'tipo' => 'texto',
                'obligatorio' => false,
                'orden' => 5,
            ],
            [
                'titulo' => '¿Tiene alguna enfermedad crónica?',
                'descripcion' => 'Diabetes, hipertensión, asma, etc.',
                'tipo' => 'multiple',
                'opciones' => ['Diabetes', 'Hipertensión', 'Asma', 'Enfermedad cardíaca', 'Ninguna'],
                'obligatorio' => false,
                'orden' => 6,
            ],
            [
                'titulo' => '¿Ha tenido cirugías previas?',
                'descripcion' => 'Si es sí, especifique cuáles y cuándo',
                'tipo' => 'si_no',
                'obligatorio' => false,
                'orden' => 7,
            ],
            [
                'titulo' => '¿Fuma actualmente?',
                'descripcion' => null,
                'tipo' => 'opcion',
                'opciones' => ['No', 'Ocasionalmente', 'Diariamente', 'Exfumador'],
                'obligatorio' => true,
                'orden' => 8,
            ],
            [
                'titulo' => '¿Consume alcohol?',
                'descripcion' => null,
                'tipo' => 'opcion',
                'opciones' => ['No', 'Ocasionalmente', 'Semanalmente', 'Diariamente'],
                'obligatorio' => true,
                'orden' => 9,
            ],
            [
                'titulo' => '¿Hace ejercicio regularmente?',
                'descripcion' => null,
                'tipo' => 'opcion',
                'opciones' => ['No', '1-2 veces por semana', '3-4 veces por semana', '5 o más veces por semana'],
                'obligatorio' => true,
                'orden' => 10,
            ],
            [
                'titulo' => '¿Cómo calificaría su nivel de dolor actual?',
                'descripcion' => '1 = Sin dolor, 10 = Dolor insoportable',
                'tipo' => 'escala',
                'obligatorio' => false,
                'orden' => 11,
            ],
            [
                'titulo' => '¿Hay antecedentes familiares de enfermedades importantes?',
                'descripcion' => 'Cáncer, diabetes, enfermedades cardíacas, etc.',
                'tipo' => 'texto',
                'obligatorio' => false,
                'orden' => 12,
            ],
        ];

        foreach ($preguntas as $pregunta) {
            Pregunta::create([
                'cuestionario_id' => $cuestionario->id,
                'titulo' => $pregunta['titulo'],
                'descripcion' => $pregunta['descripcion'],
                'tipo' => $pregunta['tipo'],
                'opciones' => $pregunta['opciones'] ?? null,
                'obligatorio' => $pregunta['obligatorio'],
                'orden' => $pregunta['orden'],
                'activo' => true,
            ]);
        }

        $this->command->info('Cuestionario de pre-consulta creado exitosamente con ' . count($preguntas) . ' preguntas.');
    }
}
