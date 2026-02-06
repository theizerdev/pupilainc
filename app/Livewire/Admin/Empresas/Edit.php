<?php

namespace App\Livewire\Admin\Empresas;

use App\Traits\HasDynamicLayout;
use App\Livewire\Traits\HasRegionalConfiguration;
use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\Empresa;
use App\Models\Pais;
use App\Services\RegionalConfigurationService;

class Edit extends Component
{
    use HasDynamicLayout, HasRegionalConfiguration;

    public $empresa;
    public $razon_social = '';
    public $documento = '';
    public $direccion = '';
    public $latitud = '';
    public $longitud = '';
    public $address = '';
    public $representante_legal = '';
    public $status = true;
    public $telefono = '';
    public $email = '';
    public $pais_id = '';

    protected $rules = [
        'razon_social' => 'required|string|max:255',
        'documento' => 'required|string',
        'direccion' => 'nullable|string',
        'latitud' => 'required|numeric|between:-90,90',
        'longitud' => 'required|numeric|between:-180,180',
        'address' => 'nullable|string',
        'representante_legal' => 'nullable|string|max:255',
        'status' => 'boolean',
        'telefono' => 'nullable|string|max:20',
        'email' => 'nullable|email|max:255',
        'pais_id' => 'required|exists:pais,id',
    ];

    #[On('location-updated')]
    public function updateLocation($latitude, $longitude, $address)
    {
        $this->latitud = $latitude;
        $this->longitud = $longitude;
        $this->address = $address;
        $this->direccion = $address;
    }

    public function updatedPaisId($value)
    {
        if ($value) {
            $pais = Pais::find($value);
            if ($pais) {
                // Actualizar configuración regional
                $this->moneda = $pais->moneda_principal ?? 'USD';
                $this->zona_horaria = $pais->zona_horaria ?? 'UTC';
                $this->formato_fecha = $pais->formato_fecha ?? 'd/m/Y';
                $this->formato_moneda = $pais->formato_moneda ?? '#,##0.00';
                $this->simbolo_moneda = $pais->simbolo_moneda ?? '$';
                $this->idioma = $pais->idioma_principal ?? 'es';

                // Actualizar coordenadas si el país las tiene
                if ($pais->tieneCoordenadas()) {
                    $this->latitud = $pais->latitud;
                    $this->longitud = $pais->longitud;
                    $this->dispatch('map-center-changed', latitud: $pais->latitud, longitud: $pais->longitud);
                }

                // Disparar evento de configuración regional actualizada
                $this->dispatch('regional-configuration-updated', [
                    'currency' => $this->moneda,
                    'timezone' => $this->zona_horaria,
                    'date_format' => $this->formato_fecha,
                    'currency_format' => $this->formato_moneda,
                    'currency_symbol' => $this->simbolo_moneda,
                    'locale' => $this->idioma,
                ]);
            }
        } else {
            // Restablecer valores por defecto si no hay país seleccionado
            $this->moneda = 'USD';
            $this->zona_horaria = 'UTC';
            $this->formato_fecha = 'd/m/Y';
            $this->formato_moneda = '#,##0.00';
            $this->simbolo_moneda = '$';
            $this->idioma = 'es';
        }
    }

    public function mount(Empresa $empresa)
    {
        $this->empresa = $empresa;
        $this->razon_social = $empresa->razon_social;
        $this->documento = $empresa->documento;
        $this->direccion = $empresa->direccion;
        $this->latitud = $empresa->latitud ?: -12.0464;
        $this->longitud = $empresa->longitud ?: -77.0428;
        $this->address = $empresa->direccion;
        $this->representante_legal = $empresa->representante_legal;
        $this->status = $empresa->status;
        $this->telefono = $empresa->telefono;
        $this->email = $empresa->email;
        $this->pais_id = $empresa->pais_id;

        // Inicializar configuración regional si hay país seleccionado
        if ($empresa->pais) {
            $this->initializeRegionalConfiguration($empresa->pais);
        } else {
            // Inicializar con valores por defecto si no hay país
            $this->moneda = 'USD';
            $this->zona_horaria = 'UTC';
            $this->formato_fecha = 'd/m/Y';
            $this->formato_moneda = '#,##0.00';
            $this->simbolo_moneda = '$';
            $this->idioma = 'es';
        }

        // Actualizar la regla de validación para permitir el documento actual
        $this->rules['documento'] = 'required|string|unique:empresas,documento,' . $empresa->id;
    }

    public function save()
    {
        $this->validate();

        $this->empresa->update([
            'razon_social' => $this->razon_social,
            'documento' => $this->documento,
            'direccion' => $this->address ?: $this->direccion,
            'latitud' => $this->latitud,
            'longitud' => $this->longitud,
            'representante_legal' => $this->representante_legal,
            'status' => $this->status,
            'telefono' => $this->telefono,
            'email' => $this->email,
            'pais_id' => $this->pais_id,
        ]);

        // Recargar la empresa con el país para aplicar configuración regional
        $this->empresa->load('pais');

        // Aplicar configuración regional para la empresa actualizada
        $this->applyRegionalConfigurationToEmpresa($this->empresa);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => "Empresa '{$this->razon_social}' actualizada correctamente. Configuración regional actualizada.",
            'duration' => 4000
        ]);

        return redirect()->route('admin.empresas.index');
    }



    public function render()
    {
        $paises = \App\Models\Pais::where('activo', true)->orderBy('nombre')->get();

        return $this->renderWithLayout('livewire.admin.empresas.edit', [
            'paises' => $paises
        ], [
            'title' => 'Editar Empresa',
            'description' => 'Modificar empresa del sistema'
        ]);
    }
}