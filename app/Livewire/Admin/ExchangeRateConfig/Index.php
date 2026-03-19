<?php

namespace App\Livewire\Admin\ExchangeRateConfig;

use App\Traits\HasDynamicLayout;
use Livewire\Component;
use App\Models\ExchangeRateConfig;
use App\Models\Pais;
use Livewire\Attributes\Validate;

class Index extends Component
{
    use HasDynamicLayout;

    public $showEditModal = false;
    public $configId;
    
    #[Validate('required|exists:pais,id')]
    public $pais_id;
    
    #[Validate('required|string|max:3')]
    public $moneda_base = 'USD';
    
    #[Validate('nullable|string|max:3')]
    public $moneda_local;
    
    #[Validate('boolean')]
    public $requiere_tasa_cambio = false;
    
    #[Validate('boolean')]
    public $usar_api_bcv = false;
    
    #[Validate('nullable|url|max:255')]
    public $api_url;
    
    #[Validate('nullable|string|max:255')]
    public $api_key;
    
    #[Validate('nullable|numeric|min:0|max:999999.9999')]
    public $tasa_fija;
    
    #[Validate('nullable|integer|min:1')]
    public $frecuencia_actualizacion_minutos = 60;
    
    #[Validate('boolean')]
    public $activo = true;

    public function mount()
    {
        abort_unless(auth()->user()->can('view exchange-rates'), 403);
    }

    public function render()
    {
        $configs = ExchangeRateConfig::with('pais')->orderBy('activo', 'desc')->get();
        $paises = Pais::where('activo', true)->orderBy('nombre')->get();
        
        return view('livewire.admin.exchange-rate-config.index', [
            'configs' => $configs,
            'paises' => $paises
        ])->layout($this->getLayout('Configuración de Tasas de Cambio'));
    }

    public function create()
    {
        abort_unless(auth()->user()->can('edit exchange-rates'), 403);
        
        $this->resetForm();
        $this->showEditModal = true;
    }

    public function edit($id)
    {
        abort_unless(auth()->user()->can('edit exchange-rates'), 403);
        
        $config = ExchangeRateConfig::findOrFail($id);
        
        $this->configId = $config->id;
        $this->pais_id = $config->pais_id;
        $this->moneda_base = $config->moneda_base;
        $this->moneda_local = $config->moneda_local;
        $this->requiere_tasa_cambio = $config->requiere_tasa_cambio;
        $this->usar_api_bcv = $config->usar_api_bcv;
        $this->api_url = $config->api_url;
        $this->api_key = $config->api_key;
        $this->tasa_fija = $config->tasa_fija;
        $this->frecuencia_actualizacion_minutos = $config->frecuencia_actualizacion_minutos;
        $this->activo = $config->activo;
        
        $this->showEditModal = true;
    }

    public function save()
    {
        abort_unless(auth()->user()->can('edit exchange-rates'), 403);
        
        $this->validate();
        
        try {
            // Validaciones adicionales
            if ($this->requiere_tasa_cambio && !$this->usar_api_bcv && !$this->tasa_fija) {
                $this->addError('tasa_fija', 'Si requiere tasa de cambio y no usa API, debe establecer una tasa fija');
                return;
            }
            
            if ($this->usar_api_bcv && empty($this->api_url)) {
                $this->addError('api_url', 'Si usa API BCV, debe proporcionar la URL de la API');
                return;
            }
            
            // Verificar unicidad del país
            $existing = ExchangeRateConfig::where('pais_id', $this->pais_id)
                ->when($this->configId, fn($q) => $q->where('id', '!=', $this->configId))
                ->first();
                
            if ($existing) {
                $this->addError('pais_id', 'Ya existe una configuración para este país');
                return;
            }
            
            ExchangeRateConfig::updateOrCreate(
                ['id' => $this->configId],
                [
                    'pais_id' => $this->pais_id,
                    'moneda_base' => $this->moneda_base,
                    'moneda_local' => $this->moneda_local,
                    'requiere_tasa_cambio' => $this->requiere_tasa_cambio,
                    'usar_api_bcv' => $this->usar_api_bcv,
                    'api_url' => $this->api_url,
                    'api_key' => $this->api_key,
                    'tasa_fija' => $this->tasa_fija ?: null,
                    'frecuencia_actualizacion_minutos' => $this->frecuencia_actualizacion_minutos,
                    'activo' => $this->activo,
                ]
            );

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Configuración guardada exitosamente',
                'duration' => 4000
            ]);

            $this->closeModal();
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al guardar: ' . $e->getMessage(),
                'duration' => 5000
            ]);
        }
    }

    public function toggleActivo($id)
    {
        abort_unless(auth()->user()->can('edit exchange-rates'), 403);
        
        $config = ExchangeRateConfig::findOrFail($id);
        $config->update(['activo' => !$config->activo]);
        
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Estado actualizado correctamente',
            'duration' => 3000
        ]);
    }

    public function closeModal()
    {
        $this->showEditModal = false;
        $this->configId = null;
        $this->resetForm();
        $this->resetValidation();
        //$this->clearErrors();
    }

    private function resetForm()
    {
        $this->pais_id = null;
        $this->moneda_base = 'USD';
        $this->moneda_local = null;
        $this->requiere_tasa_cambio = false;
        $this->usar_api_bcv = false;
        $this->api_url = null;
        $this->api_key = null;
        $this->tasa_fija = null;
        $this->frecuencia_actualizacion_minutos = 60;
        $this->activo = true;
    }
}
