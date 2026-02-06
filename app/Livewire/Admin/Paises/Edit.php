<?php

namespace App\Livewire\Admin\Paises;

use App\Traits\HasDynamicLayout;
use Livewire\Component;
use App\Models\Pais;
use Illuminate\Support\Facades\Auth;

class Edit extends Component
{
    use HasDynamicLayout;

    public $pais;

    // Campos del formulario
    public $nombre = '';
    public $codigo_iso2 = '';
    public $codigo_iso3 = '';
    public $codigo_telefonico = '';
    public $moneda_principal = 'USD';
    public $idioma_principal = 'es';
    public $continente = 'América';
    public $latitud = '';
    public $longitud = '';
    public $zona_horaria = 'UTC';
    public $formato_fecha = 'd/m/Y';
    public $formato_moneda = '#,##0.00';
    public $impuesto_predeterminado = 0.00;
    public $separador_miles = ',';
    public $separador_decimales = '.';
    public $decimales_moneda = 2;
    public $activo = true;

    protected function rules()
    {
        return [
            'nombre' => 'required|string|max:100',
            'codigo_iso2' => 'required|string|size:2|unique:pais,codigo_iso2,' . $this->pais->id,
            'codigo_iso3' => 'required|string|size:3|unique:pais,codigo_iso3,' . $this->pais->id,
            'codigo_telefonico' => 'nullable|string|max:10',
            'moneda_principal' => 'required|string|size:3',
            'idioma_principal' => 'required|string|size:2',
            'continente' => 'required|string|max:50',
            'latitud' => 'nullable|numeric|between:-90,90',
            'longitud' => 'nullable|numeric|between:-180,180',
            'zona_horaria' => 'required|string|max:50',
            'formato_fecha' => 'required|string|max:20',
            'formato_moneda' => 'required|string|max:20',
            'impuesto_predeterminado' => 'nullable|numeric|min:0|max:100',
            'separador_miles' => 'required|string|size:1',
            'separador_decimales' => 'required|string|size:1',
            'decimales_moneda' => 'required|integer|min:0|max:4',
            'activo' => 'boolean'
        ];
    }

    public function mount(Pais $pais)
    {
        if (!Auth::user()->can('edit paises')) {
            abort(403, 'No tienes permiso para editar países.');
        }

        $this->pais = $pais;
        $this->nombre = $pais->nombre;
        $this->codigo_iso2 = $pais->codigo_iso2;
        $this->codigo_iso3 = $pais->codigo_iso3;
        $this->codigo_telefonico = $pais->codigo_telefonico;
        $this->moneda_principal = $pais->moneda_principal;
        $this->idioma_principal = $pais->idioma_principal;
        $this->continente = $pais->continente;
        $this->latitud = $pais->latitud;
        $this->longitud = $pais->longitud;
        $this->zona_horaria = $pais->zona_horaria;
        $this->formato_fecha = $pais->formato_fecha;
        $this->formato_moneda = $pais->formato_moneda;
        $this->impuesto_predeterminado = $pais->impuesto_predeterminado;
        $this->separador_miles = $pais->separador_miles;
        $this->separador_decimales = $pais->separador_decimales;
        $this->decimales_moneda = $pais->decimales_moneda;
        $this->activo = $pais->activo;
    }

    public function save()
    {
        $this->validate();

        try {
            $this->pais->update([
                'nombre' => $this->nombre,
                'codigo_iso2' => strtoupper($this->codigo_iso2),
                'codigo_iso3' => strtoupper($this->codigo_iso3),
                'codigo_telefonico' => $this->codigo_telefonico,
                'moneda_principal' => strtoupper($this->moneda_principal),
                'idioma_principal' => $this->idioma_principal,
                'continente' => $this->continente,
                'latitud' => $this->latitud ? (float) $this->latitud : null,
                'longitud' => $this->longitud ? (float) $this->longitud : null,
                'zona_horaria' => $this->zona_horaria,
                'formato_fecha' => $this->formato_fecha,
                'formato_moneda' => $this->formato_moneda,
                'impuesto_predeterminado' => $this->impuesto_predeterminado,
                'separador_miles' => $this->separador_miles,
                'separador_decimales' => $this->separador_decimales,
                'decimales_moneda' => $this->decimales_moneda,
                'activo' => $this->activo,
            ]);

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => "País '{$this->nombre}' actualizado exitosamente.",
                'duration' => 4000
            ]);
            return redirect()->route('admin.paises.index');
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al actualizar el país: ' . $e->getMessage(),
                'duration' => 5000
            ]);
        }
    }

    public function render()
    {
        return $this->renderWithLayout('livewire.admin.paises.edit', [], [
            'title' => 'Editar País',
            'description' => 'Editar país del sistema',
            'breadcrumb' => [
                'admin.paises.index' => 'Países',
                '#' => 'Editar'
            ]
        ]);
    }
}