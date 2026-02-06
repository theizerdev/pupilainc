<?php

namespace App\Livewire\Admin\Empresas;

use App\Traits\HasDynamicLayout;
use App\Livewire\Traits\HasRegionalConfiguration;
use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\Empresa;
use App\Models\Pais;
use App\Services\RegionalConfigurationService;

class Create extends Component
{
    use HasDynamicLayout, HasRegionalConfiguration;

    public $razon_social = '';
    public $documento = '';
    public $direccion = '';
    public $latitud = -12.0464;
    public $longitud = -77.0428;
    public $address = '';
    public $representante_legal = '';
    public $status = true;
    public $telefono = '';
    public $email = '';
    public $pais_id = '';
    public $paisSeleccionado = null;

    public function mount()
    {
        // Inicializar configuración regional con valores por defecto
        $this->moneda = 'USD';
        $this->zona_horaria = 'UTC';
        $this->formato_fecha = 'd/m/Y';
        $this->formato_moneda = '#,##0.00';
        $this->simbolo_moneda = '$';
        $this->idioma = 'es';
    }

    protected $rules = [
        'razon_social' => 'required|string|max:255',
        'documento' => 'required|string|max:50',
        'direccion' => 'nullable|string|max:500',
        'latitud' => 'required|numeric',
        'longitud' => 'required|numeric',
        'representante_legal' => 'nullable|string|max:255',
        'status' => 'boolean',
        'telefono' => 'nullable|string|max:50',
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

    public function save()
    {
        $this->validate();

        // Generar API key para WhatsApp antes de crear la empresa
        $whatsappApiKey = 'wa_' . uniqid() . '_' . bin2hex(random_bytes(8));

        $empresa = Empresa::create([
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
            'whatsapp_api_key' => $whatsappApiKey,
            'whatsapp_active' => false, // Inicialmente inactivo hasta que se conecte
        ]);

        // Aplicar configuración regional para la nueva empresa
        $this->applyRegionalConfigurationToEmpresa($empresa);

        // Sincronizar automáticamente con la API de WhatsApp
        try {
            $webhookUrl = config('whatsapp.api_url') . '/api/whatsapp/webhook';
            
            \DB::connection('whatsapp_api')->table('companies')->updateOrInsert(
                ['id' => $empresa->id],
                [
                    'name' => $empresa->razon_social,
                    'apiKey' => $whatsappApiKey,
                    'webhookUrl' => $webhookUrl,
                    'rateLimitPerMinute' => 60,
                    'isActive' => 1,
                    'createdAt' => now(),
                    'updatedAt' => now()
                ]
            );

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => "Empresa '{$this->razon_social}' creada correctamente. Configuración regional aplicada y sincronizada con WhatsApp API.",
                'duration' => 5000
            ]);
            
        } catch (\Exception $e) {
            // Si falla la sincronización, igual mostrar mensaje de éxito pero con advertencia
            $this->dispatch('notify', [
                'type' => 'warning',
                'message' => "Empresa '{$this->razon_social}' creada correctamente. Configuración regional aplicada. Nota: No se pudo sincronizar automáticamente con WhatsApp API.",
                'duration' => 5000
            ]);
            \Log::error('Error sincronizando empresa nueva con WhatsApp API', [
                'empresa_id' => $empresa->id,
                'error' => $e->getMessage()
            ]);
        }

        return redirect()->route('admin.empresas.index');
    }

    public function render()
    {
        $paises = \App\Models\Pais::where('activo', true)->orderBy('nombre')->get();

        return $this->renderWithLayout('livewire.admin.empresas.create', [
            'paises' => $paises
        ], [
            'title' => 'Crear Empresa',
            'description' => 'Nueva empresa del sistema'
        ]);
    }
}