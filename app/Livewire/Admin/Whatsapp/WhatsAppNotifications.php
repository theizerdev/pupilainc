<?php

namespace App\Livewire\Admin\Whatsapp;

use App\Models\Empresa;
use App\Models\WhatsAppNotificationSetting;
use App\Services\WhatsAppNotificationCatalog;
use App\Services\WhatsAppNotificationGate;
use App\Traits\HasDynamicLayout;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class WhatsAppNotifications extends Component
{
    use HasDynamicLayout;

    public ?int $empresaId = null;
    public array $sectors = [];
    public array $settings = [];
    public array $recipientLabels = [];
    public string $activeSector = '';
    public bool $canManage = false;

    public function mount(): void
    {
        if (! Auth::user()->can('view whatsapp notifications') && ! Auth::user()->can('manage whatsapp notifications')) {
            abort(403, 'No tienes permiso para configurar notificaciones de WhatsApp.');
        }

        $this->canManage = Auth::user()->can('manage whatsapp notifications');
        $this->empresaId = Auth::user()->empresa_id ?? Empresa::query()->value('id');
        $this->sectors = WhatsAppNotificationCatalog::sectors();
        $this->recipientLabels = WhatsAppNotificationCatalog::recipientLabels();
        $this->activeSector = array_key_first($this->sectors) ?? '';

        $this->loadSettings();
    }

    public function setActiveSector(string $sector): void
    {
        if (isset($this->sectors[$sector])) {
            $this->activeSector = $sector;
        }
    }

    public function activateSector(string $sector): void
    {
        $this->setSectorValue($sector, true);
    }

    public function deactivateSector(string $sector): void
    {
        $this->setSectorValue($sector, false);
    }

    public function activateModule(string $sector, string $module): void
    {
        $this->setModuleValue($sector, $module, true);
    }

    public function deactivateModule(string $sector, string $module): void
    {
        $this->setModuleValue($sector, $module, false);
    }

    public function save(): void
    {
        if (! $this->canManage) {
            $this->dispatch('notify', type: 'error', message: 'No tienes permiso para modificar esta configuracion.');
            return;
        }

        if (! $this->empresaId) {
            $this->dispatch('notify', type: 'error', message: 'No se encontro una empresa para guardar la configuracion.');
            return;
        }

        foreach ($this->sectors as $sector) {
            foreach ($sector['modules'] as $moduleKey => $module) {
                foreach ($module['actions'] as $actionKey => $action) {
                    foreach ($action['recipients'] as $recipientKey) {
                        WhatsAppNotificationSetting::setValue(
                            $this->empresaId,
                            $moduleKey,
                            $actionKey,
                            $recipientKey,
                            (bool) ($this->settings[$moduleKey][$actionKey][$recipientKey] ?? false)
                        );
                    }
                }
            }
        }

        $this->dispatch('notify', type: 'success', message: 'Configuracion de WhatsApp guardada correctamente.');
        session()->flash('message', 'Configuracion de notificaciones WhatsApp guardada correctamente.');
    }

    public function stats(): array
    {
        $total = 0;
        $enabled = 0;
        $connected = 0;
        $pending = 0;

        foreach ($this->sectors as $sector) {
            foreach ($sector['modules'] as $moduleKey => $module) {
                foreach ($module['actions'] as $actionKey => $action) {
                    foreach ($action['recipients'] as $recipientKey) {
                        $total++;
                        $enabled += (bool) ($this->settings[$moduleKey][$actionKey][$recipientKey] ?? false) ? 1 : 0;
                        $connected += $action['connected'] ? 1 : 0;
                        $pending += $action['connected'] ? 0 : 1;
                    }
                }
            }
        }

        return compact('total', 'enabled', 'connected', 'pending');
    }

    public function moduleStats(string $moduleKey, array $module): array
    {
        $total = 0;
        $enabled = 0;
        $connected = 0;

        foreach ($module['actions'] as $actionKey => $action) {
            foreach ($action['recipients'] as $recipientKey) {
                $total++;
                $enabled += (bool) ($this->settings[$moduleKey][$actionKey][$recipientKey] ?? false) ? 1 : 0;
                $connected += $action['connected'] ? 1 : 0;
            }
        }

        return compact('total', 'enabled', 'connected');
    }

    private function loadSettings(): void
    {
        if (! $this->empresaId) {
            return;
        }

        $stored = WhatsAppNotificationSetting::where('empresa_id', $this->empresaId)
            ->get()
            ->keyBy(fn ($item) => $item->module_key.'.'.$item->action_key.'.'.$item->recipient_key);

        foreach ($this->sectors as $sector) {
            foreach ($sector['modules'] as $moduleKey => $module) {
                foreach ($module['actions'] as $actionKey => $action) {
                    foreach ($action['recipients'] as $recipientKey) {
                        $key = $moduleKey.'.'.$actionKey.'.'.$recipientKey;
                        $this->settings[$moduleKey][$actionKey][$recipientKey] = $stored->has($key)
                            ? (bool) $stored[$key]->enabled
                            : WhatsAppNotificationGate::allows($this->empresaId, $moduleKey, $actionKey, $recipientKey);
                    }
                }
            }
        }
    }

    private function setSectorValue(string $sector, bool $enabled): void
    {
        if (! $this->canManage || ! isset($this->sectors[$sector])) {
            return;
        }

        foreach ($this->sectors[$sector]['modules'] as $moduleKey => $module) {
            $this->setModuleValue($sector, $moduleKey, $enabled);
        }
    }

    private function setModuleValue(string $sector, string $moduleKey, bool $enabled): void
    {
        if (! $this->canManage || ! isset($this->sectors[$sector]['modules'][$moduleKey])) {
            return;
        }

        foreach ($this->sectors[$sector]['modules'][$moduleKey]['actions'] as $actionKey => $action) {
            foreach ($action['recipients'] as $recipientKey) {
                $this->settings[$moduleKey][$actionKey][$recipientKey] = $enabled;
            }
        }
    }

    public function render()
    {
        return view('livewire.admin.whatsapp.whatsapp-notifications', [
            'stats' => $this->stats(),
        ])->layout($this->getLayout());
    }
}
