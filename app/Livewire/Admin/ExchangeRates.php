<?php

namespace App\Livewire\Admin;

use App\Traits\HasDynamicLayout;
use Livewire\Component;
use App\Models\ExchangeRate;
use App\Services\ExchangeRateService;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;

class ExchangeRates extends Component
{
    use HasDynamicLayout;

    public $lastUpdate;
    public $showEditModal = false;
    public $editingRate;
    
    #[Validate('required|numeric|min:0.0001|max:999999.9999')]
    public $usd_rate;
    
    #[Validate('required|numeric|min:0.0001|max:999999.9999')]
    public $eur_rate;
    
    #[Validate('required|string|max:255')]
    public $edit_reason;

    public function mount()
    {
        abort_unless(auth()->user()->can('view exchange-rates'), 403);
        $this->lastUpdate = now()->format('H:i:s');
    }

    #[On('refresh-rates')]
    public function refreshData()
    {
        $this->lastUpdate = now()->format('H:i:s');
    }

    public function fetchNow()
    {
        try {
            $service = new ExchangeRateService();
            $success = $service->fetchAndStoreRates();

            if ($success) {
                $todayRate = ExchangeRate::getTodayRate();
                $this->dispatch('notify', [
                    'type' => 'success',
                    'message' => "Tasa actualizada: USD = {$todayRate->usd_rate} Bs. (Fuente: {$todayRate->source})",
                    'duration' => 4000
                ]);
            } else {
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => 'No se pudo obtener la tasa. Verifique la conexión a internet.',
                    'duration' => 5000
                ]);
            }
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error técnico: ' . $e->getMessage(),
                'duration' => 5000
            ]);
        }

        $this->refreshData();
    }

    public function editRate($rateId = null)
    {
        abort_unless(auth()->user()->can('edit exchange-rates'), 403);
        
        if ($rateId) {
            $this->editingRate = ExchangeRate::findOrFail($rateId);
        } else {
            $this->editingRate = ExchangeRate::getTodayRate();
        }
        
        if ($this->editingRate) {
            $this->usd_rate = $this->editingRate->usd_rate;
            $this->eur_rate = $this->editingRate->eur_rate;
        }
        
        $this->edit_reason = '';
        $this->showEditModal = true;
    }
    
    public function saveRate()
    {
        abort_unless(auth()->user()->can('edit exchange-rates'), 403);
        
        $this->validate();
        
        if ($this->editingRate) {
            // Update existing rate
            $oldUsd = $this->editingRate->usd_rate;
            $oldEur = $this->editingRate->eur_rate;
            
            // Merge with existing raw_data to preserve history
            $newRawData = array_merge(
                $this->editingRate->raw_data ?? [],
                [
                    'edited_by' => auth()->user()->name,
                    'edit_reason' => $this->edit_reason,
                    'previous_usd' => $oldUsd,
                    'previous_eur' => $oldEur,
                    'edited_at' => now()->toISOString()
                ]
            );
            
            $this->editingRate->update([
                'usd_rate' => $this->usd_rate,
                'eur_rate' => $this->eur_rate,
                'source' => 'Modificado',
                'raw_data' => $newRawData
            ]);
        } else {
            // Create new rate for today
            ExchangeRate::create([
                'date' => today(),
                'usd_rate' => $this->usd_rate,
                'eur_rate' => $this->eur_rate,
                'source' => 'Manual',
                'fetch_time' => now(),
                'raw_data' => [
                    'created_by' => auth()->user()->name,
                    'creation_reason' => $this->edit_reason,
                    'created_at' => now()->toISOString()
                ]
            ]);
        }
        
        $this->closeEditModal();
        $this->refreshData();
        
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Tasa de cambio actualizada correctamente.',
            'duration' => 4000
        ]);
    }
    
    public function closeEditModal()
    {
        $this->showEditModal = false;
        $this->editingRate = null;
        $this->reset(['usd_rate', 'eur_rate', 'edit_reason']);
        $this->resetValidation();
    }

    public function render()
    {
        $todayRate = ExchangeRate::getTodayRate();
        $rates = ExchangeRate::orderBy('created_at', 'desc')->take(7)->get();
        $chartData = ExchangeRate::orderBy('created_at', 'desc')->take(30)->get();

        // Calcular estadísticas para la vista
        $stats = [
            'usd_rate' => $todayRate ? $todayRate->usd_rate : 0,
            'eur_rate' => $todayRate ? $todayRate->eur_rate : 0,
            'date' => $todayRate ? $todayRate->date->format('d/m/Y') : 'N/A',
            'last_fetch' => $todayRate ? $todayRate->fetch_time->format('H:i') : 'N/A',
            'source' => $todayRate ? $todayRate->source : 'N/A'
        ];

        return view('livewire.admin.exchange-rates', [
            'todayRate' => $todayRate,
            'rates' => $rates,
            'chartData' => $chartData,
            'stats' => $stats
        ])->layout($this->getLayout());
    }
}