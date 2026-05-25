<?php

namespace App\Traits;

use App\Models\Especialidad;
use App\Models\EspecialidadPlantilla;

trait HasPlantillaTemplate
{
    public Especialidad $especialidad;
    public ?EspecialidadPlantilla $plantilla = null;

    // Estado de configuración (para badges)
    public bool $tienePasos = false;
    public bool $tieneEstados = false;
    public bool $tieneSecciones = false;
    public bool $tieneFormulariosEstado = false;

    public function mount(Especialidad $especialidad): void
    {
        $this->especialidad = $especialidad;
        $this->cargarPlantilla();
        $this->verificarEstadoConfiguracion();
    }

    private function cargarPlantilla(): void
    {
        $this->plantilla = EspecialidadPlantilla::where('especialidad_id', $this->especialidad->id)
            ->latest()
            ->first();
    }

    private function verificarEstadoConfiguracion(): void
    {
        if (!$this->plantilla) {
            $this->tienePasos = false;
            $this->tieneEstados = false;
            $this->tieneSecciones = false;
            $this->tieneFormulariosEstado = false;
            return;
        }

        // Verificar pasos
        $pasos = $this->plantilla->getPasosEfectivos();
        $this->tienePasos = !empty($pasos) && collect($pasos)->where('activo', true)->count() > 0;

        // Verificar estados
        $estados = $this->plantilla->getEstadosEfectivos();
        $this->tieneEstados = !empty($estados) && collect($estados)->where('activo', true)->count() > 0;

        // Verificar secciones
        $this->tieneSecciones = $this->plantilla->todasLasSecciones()
            ->whereNull('estado_formulario_id')
            ->where('activo', true)
            ->exists();

        // Verificar formularios por estado
        $this->tieneFormulariosEstado = $this->plantilla->todosLosEstadoFormularios()
            ->whereHas('todasLasSecciones', function ($query) {
                $query->where('activo', true);
            })
            ->exists();
    }

    protected function getBreadcrumbBase(): array
    {
        return [
            'admin.dashboard'                                          => 'Dashboard',
            'admin.especialidades.index'                               => 'Especialidades',
            'admin.especialidades.show:' . $this->especialidad->id     => $this->especialidad->nombre,
        ];
    }

    protected function getMenuLateralItems(): array
    {
        $routeName = request()->route()->getName();

        return [
            [
                'route' => 'admin.especialidades.plantilla.pasos',
                'label' => 'Pasos de Consulta',
                'icon' => 'ri-footprints-line',
                'completado' => $this->tienePasos,
                'activo' => str_contains($routeName, 'pasos'),
            ],
            [
                'route' => 'admin.especialidades.plantilla.estados',
                'label' => 'Estados del Flujo',
                'icon' => 'ri-flow-chart',
                'completado' => $this->tieneEstados,
                'activo' => str_contains($routeName, 'estados'),
            ],
            [
                'route' => 'admin.especialidades.plantilla.secciones',
                'label' => 'Secciones y Campos',
                'icon' => 'ri-layout-grid-line',
                'completado' => $this->tieneSecciones,
                'activo' => str_contains($routeName, 'secciones'),
            ],
            [
                'route' => 'admin.especialidades.plantilla.formularios-estado',
                'label' => 'Formularios x Estado',
                'icon' => 'ri-file-list-3-line',
                'completado' => $this->tieneFormulariosEstado,
                'activo' => str_contains($routeName, 'formularios-estado'),
            ],
        ];
    }

    public function getProgresoPorcentaje(): int
    {
        $completados = 0;
        if ($this->tienePasos) $completados++;
        if ($this->tieneEstados) $completados++;
        if ($this->tieneSecciones) $completados++;
        if ($this->tieneFormulariosEstado) $completados++;

        return (int) (($completados / 4) * 100);
    }

    public function getProgresoActual(): int
    {
        if (!$this->tienePasos) return 1;
        if (!$this->tieneEstados) return 2;
        if (!$this->tieneSecciones) return 3;
        if (!$this->tieneFormulariosEstado) return 4;
        return 5; // Todo completado
    }

    protected function getLayout(): string
    {
        return 'layouts.admin';
    }
}
