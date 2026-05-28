<?php

namespace App\Livewire\Admin\Messaging;

use App\Models\MessagingConnection;
use App\Models\ModuleNotificationChannel;
use Livewire\Attributes\On;
use Livewire\Component;
use App\Traits\HasDynamicLayout;

class ModuleNotificationChannels extends Component
{
    use HasDynamicLayout;

    public $empresaId;
    public $activeTab = 'citas';
    public $channels = [];
    public $connections = [];
    
    // Available modules and actions
    public $modules = [
        'citas' => ['label' => 'Citas', 'actions' => ['creacion', 'recordatorio', 'confirmacion', 'cancelacion', 'reagendamiento']],
        'usuarios' => ['label' => 'Usuarios', 'actions' => ['creacion', 'bienvenida', 'recordatorio_password', '2fa']],
        'consultas' => ['label' => 'Consultas', 'actions' => ['inicio', 'finalizacion', 'resultados']],
        'doctores' => ['label' => 'Doctores', 'actions' => ['creacion', 'asignacion', 'recordatorio', 'nota']],
        'enfermeros' => ['label' => 'Enfermeros', 'actions' => ['creacion', 'asignacion', 'recordatorio']],
        'pedidos' => ['label' => 'Pedidos', 'actions' => ['creacion', 'aprobacion', 'entrega']],
        'pagos' => ['label' => 'Pagos', 'actions' => ['confirmacion', 'recordatorio', 'recibo']],
    ];
    
    public $recipientTypes = [
        'paciente' => 'Paciente',
        'doctor' => 'Doctor',
        'usuario' => 'Usuario',
        'tutor' => 'Tutor',
        'enfermero' => 'Enfermero',
    ];

    // Modal
    public $showEditModal = false;
    public $editingModule = '';
    public $editingAction = '';
    public $editingRecipient = '';
    public $selectedConnectionId = '';
    public $selectedPriority = 1;

    protected $rules = [
        'channels.*.connection_id' => 'nullable|exists:messaging_connections,id',
        'channels.*.enabled' => 'boolean',
        'channels.*.priority' => 'nullable|integer|min:1|max:10',
    ];

    public function mount()
    {
        $this->empresaId = auth()->user()->empresa_id;
        $this->loadConnections();
        $this->loadChannels();
    }

    public function loadConnections()
    {

        $connections = MessagingConnection::forEmpresa($this->empresaId)
            ->active()
            ->with('provider')
            ->get();

        // Index por ID para evitar búsquedas repetitivas en la vista
        $this->connections = $connections->keyBy('id')->map(fn ($c) => [
            'id' => $c->id,
            'name' => $c->name,
            'status' => $c->status,
            'provider' => $c->provider ? [
                'name' => $c->provider->name,
            ] : null,
        ])->toArray();
    }


    public function loadChannels()
    {
        $channels = ModuleNotificationChannel::forEmpresa($this->empresaId)
            ->get()
            ->toArray();
        
        // Index by composite key for easy lookup
        $this->channels = [];
        foreach ($channels as $channel) {
            $key = "{$channel['module_key']}.{$channel['action_key']}.{$channel['recipient_type']}";
            $this->channels[$key] = $channel;
        }
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function getChannelKey($module, $action, $recipient)
    {
        return "{$module}.{$action}.{$recipient}";
    }

    public function getChannelValue($module, $action, $recipient)
    {
        $key = $this->getChannelKey($module, $action, $recipient);
        return $this->channels[$key] ?? null;
    }

public function getConnectionForChannel($module, $action, $recipient): ?array
    {
        $channel = $this->getChannelValue($module, $action, $recipient);
        if (!$channel || empty($channel['connection_id'])) {
            return null;
        }

        return $this->connections[$channel['connection_id']] ?? null;
    }


public function openEditModal($module, $action, $recipient)
    {
        $this->editingModule = $module;
        $this->editingAction = $action;
        $this->editingRecipient = $recipient;

        $channel = $this->getChannelValue($module, $action, $recipient);
        $this->selectedConnectionId = $channel['connection_id'] ?? '';
        $this->selectedPriority = $channel['priority'] ?? 1;

        $this->showEditModal = true;
    }

    public function closeEditModal()
    {
        $this->showEditModal = false;
        $this->selectedConnectionId = '';
        $this->selectedPriority = 1;
    }


    public function saveChannelFromModal()
    {
        if (!$this->selectedConnectionId) {
            ModuleNotificationChannel::forEmpresa($this->empresaId)
                ->where('module_key', $this->editingModule)
                ->where('action_key', $this->editingAction)
                ->where('recipient_type', $this->editingRecipient)
                ->delete();
            
            $this->showEditModal = false;
            $this->loadChannels();
            $this->dispatch('notify', ['type' => 'success', 'message' => 'Canal eliminado', 'duration' => 3000]);
            return;
        }

        ModuleNotificationChannel::updateOrCreate(
            [
                'empresa_id' => $this->empresaId,
                'module_key' => $this->editingModule,
                'action_key' => $this->editingAction,
                'recipient_type' => $this->editingRecipient,
            ],
            [
                'connection_id' => $this->selectedConnectionId,
                'enabled' => true,
                'priority' => $this->selectedPriority,
            ]
        );

        $this->showEditModal = false;
        $this->loadChannels();
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Canal actualizado', 'duration' => 3000]);
    }

    public function saveChannel($module, $action, $recipient)
    {
        $key = $this->getChannelKey($module, $action, $recipient);
        $data = request()->get('channel_data') ?? [];
        
        $connectionId = $data['connection_id'] ?? null;
        $enabled = $data['enabled'] ?? false;
        $priority = $data['priority'] ?? 1;

        if (!$connectionId) {
            // Delete existing if no connection selected
            ModuleNotificationChannel::forEmpresa($this->empresaId)
                ->where('module_key', $module)
                ->where('action_key', $action)
                ->where('recipient_type', $recipient)
                ->delete();
            
            $this->loadChannels();
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Canal eliminado',
                'duration' => 3000
            ]);
            return;
        }

        // Upsert channel
        ModuleNotificationChannel::updateOrCreate(
            [
                'empresa_id' => $this->empresaId,
                'module_key' => $module,
                'action_key' => $action,
                'recipient_type' => $recipient,
            ],
            [
                'connection_id' => $connectionId,
                'enabled' => $enabled,
                'priority' => $priority,
            ]
        );

        $this->loadChannels();
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => "Configuración guardada para {$module} - {$action}",
            'duration' => 3000
        ]);
    }

    public function toggleChannel($module, $action, $recipient)
    {
        $channel = ModuleNotificationChannel::forEmpresa($this->empresaId)
            ->where('module_key', $module)
            ->where('action_key', $action)
            ->where('recipient_type', $recipient)
            ->first();

        if ($channel) {
            $channel->update(['enabled' => !$channel->enabled]);
            $this->loadChannels();
        }
    }

    public function render()
    {
        return view('livewire.admin.messaging.module-channels')
        ->layout($this->getLayout());
    }
}