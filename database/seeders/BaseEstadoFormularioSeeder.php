<?php

namespace Database\Seeders;

use App\Models\EspecialidadPlantilla;
use App\Models\PlantillaEstadoFormulario;
use App\Models\PlantillaSeccion;
use App\Models\PlantillaCampo;
use Illuminate\Database\Seeder;

abstract class BaseEstadoFormularioSeeder extends Seeder
{
    /**
     * Crea o reemplaza el formulario de un estado dentro de una plantilla.
     * Retorna el PlantillaEstadoFormulario creado.
     */
    protected function formularioEstado(
        EspecialidadPlantilla $plantilla,
        string $estado,
        string $titulo
    ): PlantillaEstadoFormulario {
        // Eliminar si ya existe (idempotente)
        PlantillaEstadoFormulario::where('plantilla_id', $plantilla->id)
            ->where('estado', $estado)
            ->delete();

        return PlantillaEstadoFormulario::create([
            'plantilla_id' => $plantilla->id,
            'estado'       => $estado,
            'titulo'       => $titulo,
            'activo'       => true,
        ]);
    }

    /**
     * Crea una sección dentro de un formulario de estado.
     */
    protected function seccion(
        EspecialidadPlantilla $plantilla,
        PlantillaEstadoFormulario $ef,
        string $nombre,
        string $icono,
        string $color,
        int $orden
    ): PlantillaSeccion {
        return PlantillaSeccion::create([
            'plantilla_id'         => $plantilla->id,
            'estado_formulario_id' => $ef->id,
            'nombre'               => $nombre,
            'icono'                => $icono,
            'color'                => $color,
            'orden'                => $orden,
            'activo'               => true,
        ]);
    }

    /**
     * Crea campos en una sección.
     * Cada campo: [nombre_campo, etiqueta, tipo, opciones, obligatorio, valor_defecto, unidad, placeholder, ancho]
     */
    protected function campos(int $seccionId, array $campos): void
    {
        foreach ($campos as $orden => $c) {
            PlantillaCampo::create([
                'seccion_id'     => $seccionId,
                'nombre_campo'   => $c[0],
                'etiqueta'       => $c[1],
                'tipo'           => $c[2],
                'opciones'       => $c[3],
                'obligatorio'    => $c[4],
                'valor_defecto'  => $c[5],
                'unidad'         => $c[6],
                'placeholder'    => $c[7],
                'ancho_columnas' => $c[8],
                'min'            => $c[9] ?? null,
                'max'            => $c[10] ?? null,
                'orden'          => $orden + 1,
                'activo'         => true,
            ]);
        }
    }
}
