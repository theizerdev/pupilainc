<?php

namespace App\Livewire\Admin\Messaging;

use App\Models\MessagingConnection;
use App\Models\MessagingProvider;
use App\Services\Messaging\MessagingConnectionManager;
use App\Services\Messaging\UnifiedNotificationService;
use Livewire\Attributes\On;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Traits\HasDynamicLayout;

class Connections extends Component
{

    use HasDynamicLayout;

    public $empresaId;
    public $connections = [];
    public $providers = [];
    public $search = '';
    public $statusFilter = '';
    
    // Modal states
    public $showModal = false;
    public $editingConnection = null;
    public $testingConnection = false;
    public $testResult = null;

    // Form fields
    public $provider_id = '';
    public $name = '';

    // WhatsApp (legacy)
    public $api_url = '';
    public $api_key = '';

    // Twilio SMS
    public $account_sid = '';
    public $auth_token = '';
    public $from = '';
    public $from_whatsapp = '';
    public $channel = 'sms';

    public $timeout = 30;
    public $is_default_for = [];

    protected function baseRules(): array
    {
        return [
            'provider_id' => 'required|exists:messaging_providers,id',
            'name' => 'required|string|max:255',
            'timeout' => 'nullable|integer|min:5|max:300',
        ];
    }

    public function updatedProviderId()
    {
        $this->api_url = '';
        $this->api_key = '';
        $this->account_sid = '';
        $this->auth_token = '';
        $this->from = '';
        $this->from_whatsapp = '';
        $this->channel = 'sms';
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->loadConnections();
    }


    public function mount()
    {
        $this->empresaId = auth()->user()->empresa_id;
        $this->loadConnections();
        $this->loadProviders();
    }

    public function loadConnections()
    {
        $query = MessagingConnection::forEmpresa($this->empresaId)->with('provider');

        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        $this->connections = $query->orderBy('created_at', 'desc')->get()->toArray();
    }

    public function loadProviders()
    {
        $this->providers = MessagingProvider::active()->get()->toArray();
    }

    public function updatedSearch()
    {
        $this->loadConnections();
    }

    public function updatedStatusFilter()
    {
        $this->loadConnections();
    }

    #[On('connection-saved')]
    public function onConnectionSaved()
    {
        $this->showModal = false;
        $this->loadConnections();
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Conexión guardada correctamente',
            'duration' => 4000
        ]);
    }

    public function openCreateModal()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEditModal($connectionId)
    {
        $connection = MessagingConnection::with('provider')->find($connectionId);

        if (!$connection) return;

        $this->editingConnection = $connection;
        $this->provider_id = $connection->provider_id;
        $this->name = $connection->name;

        // Desencriptar credenciales
        $credentials = $connection->credentials;

        $this->api_url = $credentials['api_url'] ?? '';
        $this->api_key = $credentials['api_key'] ?? '';

        $this->account_sid = $credentials['account_sid'] ?? '';
        $this->auth_token = $credentials['auth_token'] ?? '';
        $this->from = $credentials['from'] ?? '';
        $this->from_whatsapp = $credentials['from_whatsapp'] ?? '';
        $this->channel = $credentials['channel'] ?? 'sms';

        $this->timeout = $credentials['timeout'] ?? 30;
        $this->is_default_for = $connection->is_default_for ?? [];

        $this->showModal = true;
    }


    public function saveConnection()
    {
        // Validación base
        $this->validate($this->baseRules());

        $providerModel = MessagingProvider::find($this->provider_id);
        $providerSlug = $providerModel?->slug;

        // Validación por provider
        if ($providerSlug === 'twilio') {
            $rules = [
                'account_sid' => 'required|string',
                'auth_token' => 'required|string',
                'from' => 'required|string',
                'channel' => 'required|in:sms,whatsapp',
            ];

            if ($this->channel === 'whatsapp') {
                $rules['from_whatsapp'] = 'required|string';
            }

            $this->validate($rules);
        } elseif (in_array($providerSlug, ['whatsapp_lite', 'whatsapp_meta'], true)) {
            $this->validate([
                'api_url' => 'required|url',
                'api_key' => 'required|string',
            ]);
        }

        $credentials = [
            // Comunes
            'timeout' => $this->timeout,
            'empresa_id' => $this->empresaId,
        ];

        // Credenciales según provider
        if ($providerSlug === 'twilio') {
            $credentials['account_sid'] = $this->account_sid;
            $credentials['auth_token'] = $this->auth_token;
            $credentials['from'] = $this->from;
            $credentials['channel'] = $this->channel;
            $credentials['from_whatsapp'] = $this->from_whatsapp;
        } else {
            // WhatsApp legacy
            $credentials['api_url'] = $this->api_url;
            $credentials['api_key'] = $this->api_key;
        }

        $data = [
            'empresa_id' => $this->empresaId,
            'provider_id' => $this->provider_id,
            'name' => $this->name,
            'credentials' => $credentials,
            'configuration' => [],
            'status' => 'inactive',
            'is_default_for' => $this->is_default_for,
        ];

        if ($this->editingConnection) {
            $this->editingConnection->update($data);
            $message = 'Conexión actualizada';
        } else {
            MessagingConnection::create($data);
            $message = 'Conexión creada';
        }

        (new MessagingConnectionManager())->clearCache($this->empresaId);

        $this->showModal = false;
        $this->resetForm();
        $this->loadConnections();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => $message,
            'duration' => 4000
        ]);
    }

    public function testConnection($connectionId)
    {
        $this->testingConnection = true;
        $this->testResult = null;

        try {
            $connection = MessagingConnection::find($connectionId);
            
            if (!$connection) {
                $this->testResult = ['success' => false, 'message' => 'Conexión no encontrada'];
                return;
            }

            $manager = new MessagingConnectionManager();
            $this->testResult = $manager->testConnection($connection);

            // Actualizar resultado en la conexión
            $connection->markTestResult($this->testResult);
            $this->loadConnections();

        } catch (\Exception $e) {
            $this->testResult = ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }

        $this->testingConnection = false;
    }

    public function deleteConnection($connectionId)
    {
        $connection = MessagingConnection::find($connectionId);
        
        if ($connection) {
            $connection->delete();
            (new MessagingConnectionManager())->clearCache($this->empresaId);
            $this->loadConnections();
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Conexión eliminada',
                'duration' => 4000
            ]);
        }
    }

    public function setAsDefault($connectionId, $moduleKey)
    {
        $connection = MessagingConnection::find($connectionId);
        
        if (!$connection) return;

        // Quitar como default de ese módulo en otras conexiones
        MessagingConnection::forEmpresa($this->empresaId)
            ->where('id', '!=', $connectionId)
            ->get()
            ->each(function ($conn) use ($moduleKey) {
                $defaults = $conn->is_default_for ?? [];
                $defaults = array_filter($defaults, fn($m) => $m !== $moduleKey);
                $conn->update(['is_default_for' => array_values($defaults)]);
            });

        // Agregar como default en esta conexión
        $defaults = $connection->is_default_for ?? [];
        if (!in_array($moduleKey, $defaults)) {
            $defaults[] = $moduleKey;
        }
        $connection->update(['is_default_for' => $defaults]);

        $this->loadConnections();
        (new MessagingConnectionManager())->clearCache($this->empresaId);
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => "Conexión设置为默认用于 {$moduleKey}",
            'duration' => 3000
        ]);
    }

    private function resetForm()
    {
        $this->editingConnection = null;
        $this->provider_id = '';
        $this->name = '';
        $this->api_url = '';
        $this->api_key = '';
        $this->account_sid = '';
        $this->auth_token = '';
        $this->from = '';
        $this->from_whatsapp = '';
        $this->channel = 'sms';
        $this->timeout = 30;
        $this->is_default_for = [];
        $this->testResult = null;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function render()
    {
        return view('livewire.admin.messaging.connections')
        ->layout($this->getLayout());
    }
}