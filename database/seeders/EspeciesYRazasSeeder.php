<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Especie;
use App\Models\Raza;

class EspeciesYRazasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Especies comunes
        $especies = [
            [
                'nombre' => 'Perro',
                'nombre_cientifico' => 'Canis lupus familiaris',
                'descripcion' => 'Mamífero doméstico de la familia de los cánidos',
                'icono' => '🐕',
                'color' => '#F59E0B',
                'orden' => 1,
            ],
            [
                'nombre' => 'Gato',
                'nombre_cientifico' => 'Felis silvestris catus',
                'descripcion' => 'Mamífero doméstico de la familia de los félidos',
                'icono' => '🐱',
                'color' => '#8B5CF6',
                'orden' => 2,
            ],
            [
                'nombre' => 'Ave',
                'nombre_cientifico' => 'Aves',
                'descripcion' => 'Animales vertebrados de sangre caliente con plumas',
                'icono' => '🦜',
                'color' => '#10B981',
                'orden' => 3,
            ],
            [
                'nombre' => 'Conejo',
                'nombre_cientifico' => 'Oryctolagus cuniculus',
                'descripcion' => 'Mamífero lagomorfo doméstico',
                'icono' => '🐰',
                'color' => '#EC4899',
                'orden' => 4,
            ],
            [
                'nombre' => 'Hámster',
                'nombre_cientifico' => 'Cricetinae',
                'descripcion' => 'Roedor doméstico pequeño',
                'icono' => '🐹',
                'color' => '#F97316',
                'orden' => 5,
            ],
            [
                'nombre' => 'Tortuga',
                'nombre_cientifico' => 'Testudines',
                'descripcion' => 'Reptil con caparazón',
                'icono' => '🐢',
                'color' => '#14B8A6',
                'orden' => 6,
            ],
            [
                'nombre' => 'Pez',
                'nombre_cientifico' => 'Pisces',
                'descripcion' => 'Animal acuático con branquias y aletas',
                'icono' => '🐠',
                'color' => '#3B82F6',
                'orden' => 7,
            ],
            [
                'nombre' => 'Caballo',
                'nombre_cientifico' => 'Equus caballus',
                'descripcion' => 'Mamífero perisodáctilo domesticado',
                'icono' => '🐴',
                'color' => '#78716C',
                'orden' => 8,
            ],
        ];

        foreach ($especies as $especieData) {
            $especie = Especie::create($especieData);

            // Crear razas para cada especie
            $this->crearRazas($especie);
        }

        $this->command->info('✅ Especies y razas creadas exitosamente');
    }

    private function crearRazas(Especie $especie): void
    {
        $razasPorEspecie = [
            'Perro' => [
                ['nombre' => 'Labrador Retriever', 'peso_promedio_kg' => 30, 'tamano_promedio_cm' => 60, 'esperanza_vida_anios' => 12],
                ['nombre' => 'Pastor Alemán', 'peso_promedio_kg' => 35, 'tamano_promedio_cm' => 65, 'esperanza_vida_anios' => 11],
                ['nombre' => 'Golden Retriever', 'peso_promedio_kg' => 32, 'tamano_promedio_cm' => 58, 'esperanza_vida_anios' => 12],
                ['nombre' => 'Bulldog Francés', 'peso_promedio_kg' => 12, 'tamano_promedio_cm' => 33, 'esperanza_vida_anios' => 11],
                ['nombre' => 'Poodle', 'peso_promedio_kg' => 20, 'tamano_promedio_cm' => 45, 'esperanza_vida_anios' => 14],
                ['nombre' => 'Beagle', 'peso_promedio_kg' => 10, 'tamano_promedio_cm' => 40, 'esperanza_vida_anios' => 13],
                ['nombre' => 'Chihuahua', 'peso_promedio_kg' => 2, 'tamano_promedio_cm' => 20, 'esperanza_vida_anios' => 16],
                ['nombre' => 'Yorkshire Terrier', 'peso_promedio_kg' => 3, 'tamano_promedio_cm' => 23, 'esperanza_vida_anios' => 14],
                ['nombre' => 'Boxer', 'peso_promedio_kg' => 30, 'tamano_promedio_cm' => 60, 'esperanza_vida_anios' => 11],
                ['nombre' => 'Dálmata', 'peso_promedio_kg' => 25, 'tamano_promedio_cm' => 58, 'esperanza_vida_anios' => 12],
                ['nombre' => 'Mestizo', 'peso_promedio_kg' => null, 'tamano_promedio_cm' => null, 'esperanza_vida_anios' => null],
            ],
            'Gato' => [
                ['nombre' => 'Persa', 'peso_promedio_kg' => 5, 'tamano_promedio_cm' => 40, 'esperanza_vida_anios' => 15],
                ['nombre' => 'Siamés', 'peso_promedio_kg' => 4, 'tamano_promedio_cm' => 35, 'esperanza_vida_anios' => 16],
                ['nombre' => 'Maine Coon', 'peso_promedio_kg' => 8, 'tamano_promedio_cm' => 50, 'esperanza_vida_anios' => 13],
                ['nombre' => 'Bengalí', 'peso_promedio_kg' => 6, 'tamano_promedio_cm' => 45, 'esperanza_vida_anios' => 14],
                ['nombre' => 'British Shorthair', 'peso_promedio_kg' => 6, 'tamano_promedio_cm' => 40, 'esperanza_vida_anios' => 15],
                ['nombre' => 'Ragdoll', 'peso_promedio_kg' => 7, 'tamano_promedio_cm' => 45, 'esperanza_vida_anios' => 14],
                ['nombre' => 'Sphynx', 'peso_promedio_kg' => 4, 'tamano_promedio_cm' => 35, 'esperanza_vida_anios' => 13],
                ['nombre' => 'Mestizo', 'peso_promedio_kg' => null, 'tamano_promedio_cm' => null, 'esperanza_vida_anios' => null],
            ],
            'Ave' => [
                ['nombre' => 'Loro', 'peso_promedio_kg' => 1, 'tamano_promedio_cm' => 40, 'esperanza_vida_anios' => 50],
                ['nombre' => 'Canario', 'peso_promedio_kg' => 0.02, 'tamano_promedio_cm' => 13, 'esperanza_vida_anios' => 10],
                ['nombre' => 'Periquito', 'peso_promedio_kg' => 0.03, 'tamano_promedio_cm' => 18, 'esperanza_vida_anios' => 12],
                ['nombre' => 'Cacatúa', 'peso_promedio_kg' => 0.5, 'tamano_promedio_cm' => 35, 'esperanza_vida_anios' => 40],
                ['nombre' => 'Agapornis', 'peso_promedio_kg' => 0.05, 'tamano_promedio_cm' => 15, 'esperanza_vida_anios' => 15],
            ],
            'Conejo' => [
                ['nombre' => 'Holland Lop', 'peso_promedio_kg' => 1.5, 'tamano_promedio_cm' => 25, 'esperanza_vida_anios' => 10],
                ['nombre' => 'Mini Rex', 'peso_promedio_kg' => 2, 'tamano_promedio_cm' => 30, 'esperanza_vida_anios' => 8],
                ['nombre' => 'Angora', 'peso_promedio_kg' => 3, 'tamano_promedio_cm' => 35, 'esperanza_vida_anios' => 9],
                ['nombre' => 'Mestizo', 'peso_promedio_kg' => null, 'tamano_promedio_cm' => null, 'esperanza_vida_anios' => null],
            ],
            'Hámster' => [
                ['nombre' => 'Sirio', 'peso_promedio_kg' => 0.15, 'tamano_promedio_cm' => 15, 'esperanza_vida_anios' => 3],
                ['nombre' => 'Enano Ruso', 'peso_promedio_kg' => 0.05, 'tamano_promedio_cm' => 10, 'esperanza_vida_anios' => 2],
                ['nombre' => 'Roborovski', 'peso_promedio_kg' => 0.025, 'tamano_promedio_cm' => 7, 'esperanza_vida_anios' => 3],
            ],
            'Tortuga' => [
                ['nombre' => 'Tortuga Rusa', 'peso_promedio_kg' => 2, 'tamano_promedio_cm' => 20, 'esperanza_vida_anios' => 40],
                ['nombre' => 'Tortuga Griega', 'peso_promedio_kg' => 3, 'tamano_promedio_cm' => 25, 'esperanza_vida_anios' => 50],
                ['nombre' => 'Tortuga de Agua', 'peso_promedio_kg' => 1, 'tamano_promedio_cm' => 15, 'esperanza_vida_anios' => 30],
            ],
            'Pez' => [
                ['nombre' => 'Goldfish', 'peso_promedio_kg' => 0.2, 'tamano_promedio_cm' => 20, 'esperanza_vida_anios' => 10],
                ['nombre' => 'Betta', 'peso_promedio_kg' => 0.01, 'tamano_promedio_cm' => 7, 'esperanza_vida_anios' => 4],
                ['nombre' => 'Guppy', 'peso_promedio_kg' => 0.005, 'tamano_promedio_cm' => 5, 'esperanza_vida_anios' => 3],
            ],
            'Caballo' => [
                ['nombre' => 'Árabe', 'peso_promedio_kg' => 450, 'tamano_promedio_cm' => 150, 'esperanza_vida_anios' => 30],
                ['nombre' => 'Pura Sangre', 'peso_promedio_kg' => 500, 'tamano_promedio_cm' => 165, 'esperanza_vida_anios' => 28],
                ['nombre' => 'Cuarto de Milla', 'peso_promedio_kg' => 550, 'tamano_promedio_cm' => 155, 'esperanza_vida_anios' => 30],
                ['nombre' => 'Appaloosa', 'peso_promedio_kg' => 500, 'tamano_promedio_cm' => 155, 'esperanza_vida_anios' => 30],
            ],
        ];

        $nombreEspecie = $especie->nombre;

        if (isset($razasPorEspecie[$nombreEspecie])) {
            foreach ($razasPorEspecie[$nombreEspecie] as $razaData) {
                Raza::create(array_merge($razaData, [
                    'especie_id' => $especie->id,
                ]));
            }
        }
    }
}
