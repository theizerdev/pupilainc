<?php

namespace App\Livewire\Admin\Whatsapp;

use Livewire\Component;
use App\Traits\HasDynamicLayout;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class Index extends Component
{
    use HasDynamicLayout;

    public $status = 'disconnected';
    public $user = null;
    public $lastSeen = null;
    public $whatsappApiKey = null;
    public $companyId = null;
    public $messages = [];
    public $isLoading = false;
    public $connectionError = null;
    public $activeTab = 'dashboard';
    public $todayMessages = 0;
    public $dailyMessages = [];
    public $empresaNombre = null;
    public $whatsappPhone = null;

    public $stats = [
        'sent' => 0,
        'delivered' => 0,
        'read' => 0,
        'failed' => 0,
        'pending' => 0,
        'total' => 0,
        'today' => 0
    ];

    protected $listeners = [
        'refreshWhatsapp' => 'loadDashboard',
        'connectionUpdated' => 'handleConnectionUpdate'
    ];

    public function mount()
    {
        if (!Auth::user()->can('access whatsapp')) {
            abort(403, 'No tienes permiso para acceder a WhatsApp.');
        }

        $this->initializeWhatsApp();
        $this->loadDashboard();
    }

    /**
     * Inicializa la configuración de WhatsApp para la empresa del usuario
     */
    public function initializeWhatsApp()
    {
        $empresa = auth()->user()->empresa ?? null;
        
        if ($empresa) {
            $this->companyId = $empresa->id;
            $this->whatsappApiKey = $empresa->whatsapp_api_key;
            $this->empresaNombre = $empresa->razon_social;
            $this->whatsappPhone = $empresa->whatsapp_phone;
            
            // Si no tiene API key, mostrar mensaje
            if (empty($this->whatsappApiKey)) {
                $this->connectionError = 'Esta empresa no tiene configurada la API Key de WhatsApp. Contacte al administrador.';
            }
        } else {
            $this->connectionError = 'Usuario sin empresa asignada.';
        }
    }

    /**
     * Obtiene los headers necesarios para la API de WhatsApp
     */
    private function getApiHeaders(): array
    {
        return [
            'X-API-Key' => $this->whatsappApiKey,
            'X-Company-Id' => (string) $this->companyId,
            'Content-Type' => 'application/json'
        ];
    }

    public function loadDashboard()
    {
        $this->isLoading = true;
        $this->connectionError = null;

        $this->checkStatus();
        $this->loadStats();

        $this->isLoading = false;
    }

    public function checkStatus()
    {
        if (!$this->whatsappApiKey) {
            $this->connectionError = 'No se ha configurado la API Key de WhatsApp para esta empresa.';
            return;
        }

        try {
            // URL base del servicio de Node.js (usar config o env)
            $baseUrl = config('whatsapp.api_url', 'http://82.165.213.124:8092');
            
            $response = Http::timeout(10)
                ->withHeaders($this->getApiHeaders())
                ->get("{$baseUrl}/api/whatsapp/status");

            if ($response->successful()) {
                $data = $response->json();
                
                // Actualizar propiedades con la respuesta del endpoint multi-tenant
                $this->status = $data['connectionState'] ?? 'disconnected';
                
                // Si está conectado, obtener datos del usuario
                if ($this->status === 'connected' && isset($data['user'])) {
                    $this->user = $data['user'];
                    // Formatear ID para mostrar
                    if (isset($this->user['id'])) {
                        $this->user['formatted_id'] = explode(':', $this->user['id'])[0];
                    }
                } else {
                    $this->user = null;
                }
                
                // Manejar código QR si está disponible
                if (isset($data['qr']) && $this->status !== 'connected') {
                    $this->dispatch('qr-code-received', qr: $data['qr']);
                }
                
                $this->connectionError = null;
            } else {
                $this->status = 'error';
                $this->connectionError = 'Error en respuesta del servidor: ' . $response->status();
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            $this->status = 'service_unavailable';
            $this->connectionError = 'No se puede conectar al servicio de WhatsApp. Verifique que esté ejecutándose.';
        } catch (\Exception $e) {
            $this->status = 'error';
            $this->connectionError = 'Error al verificar estado: ' . $e->getMessage();
        }
    }

    public function loadStats()
    {
        if (!$this->whatsappApiKey) return;

        try {
            $response = Http::timeout(10)
                ->withHeaders($this->getApiHeaders())
                ->get( config('whatsapp.api_url') . '/api/whatsapp/stats');
            
            if ($response->successful()) {
                $data = $response->json();
                $statsData = $data['stats'] ?? [];

                $this->stats = [
                    'sent' => $statsData['sent'] ?? 0,
                    'delivered' => $statsData['delivered'] ?? 0,
                    'read' => $statsData['read'] ?? 0,
                    'failed' => $statsData['failed'] ?? 0,
                    'pending' => $statsData['pending'] ?? 0,
                    'total' => $statsData['total'] ?? 0,
                    'today' => $statsData['today'] ?? 0,
                ];

                $this->todayMessages = $statsData['today'] ?? 0;
                $this->dailyMessages = $statsData['dailyMessages'] ?? [];
                $this->messages = collect($statsData['recentMessages'] ?? [])->toArray();
            } else {
                $this->loadMessagesAsFallback();
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('WhatsApp stats error: ' . $e->getMessage());
            $this->loadMessagesAsFallback();
        }
    }

    public function loadMessages()
    {
        if (!$this->whatsappApiKey) return;

        try {
            $response = Http::timeout(10)
                ->withHeaders($this->getApiHeaders())
                ->get( env('WHATSAPP_API_URL', 'http://82.165.213.124:8092') . '/api/whatsapp/messages', ['limit' => 10]);

            if ($response->successful()) {
                $data = $response->json();
                $this->messages = $data['messages'] ?? [];
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('WhatsApp messages error: ' . $e->getMessage());
        }
    }

    private function loadMessagesAsFallback()
    {
        try {
            $response = Http::timeout(10)
                ->withHeaders($this->getApiHeaders())
                ->get( env('WHATSAPP_API_URL', 'http://82.165.213.124:8092') . '/api/whatsapp/messages', ['limit' => 50]);

            if ($response->successful()) {
                $data = $response->json();
                $allMessages = collect($data['messages'] ?? []);
                $this->messages = $allMessages->take(10)->toArray();

                $this->stats = [
                    'sent' => $allMessages->where('status', 'sent')->count(),
                    'delivered' => $allMessages->where('status', 'delivered')->count(),
                    'read' => $allMessages->where('status', 'read')->count(),
                    'failed' => $allMessages->where('status', 'failed')->count(),
                    'pending' => $allMessages->where('status', 'pending')->count(),
                    'total' => $data['total'] ?? $allMessages->count(),
                    'today' => $allMessages->filter(fn($m) => 
                        isset($m['createdAt']) && \Carbon\Carbon::parse($m['createdAt'])->isToday()
                    )->count(),
                ];
                $this->todayMessages = $this->stats['today'];
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('WhatsApp messages fallback error: ' . $e->getMessage());
        }
    }

    public function loadConversations()
    {
        // TODO: implementar carga de conversaciones para tab conversaciones
    }

    public function refresh()
    {
        $this->loadDashboard();
        $this->dispatch('notify', type: 'success', message: 'Dashboard actualizado correctamente.');
    }

    public function handleConnectionUpdate($newStatus)
    {
        $this->status = $newStatus;
        if ($newStatus === 'connected') {
            $this->loadDashboard();
        }
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
    }

    protected function handleApiError($response)
    {
        $statusCode = $response->status();
        $error = $response->json()['error'] ?? 'Error desconocido';

        switch ($statusCode) {
            case 401:
                $this->connectionError = 'API Key inválida o expirada.';
                break;
            case 403:
                $this->connectionError = 'No tiene permisos para acceder a este recurso.';
                break;
            case 500:
                $this->connectionError = 'Error interno del servidor de WhatsApp.';
                break;
            default:
                $this->connectionError = $error;
        }

        $this->status = 'error';
    }

    public function getStatusColorProperty()
    {
        return match ($this->status) {
            'connected' => 'success',
            'connecting', 'qr_ready' => 'warning',
            'service_unavailable' => 'secondary',
            'error' => 'danger',
            default => 'danger'
        };
    }

    public function getStatusIconProperty()
    {
        return match ($this->status) {
            'connected' => 'ri ri-checkbox-circle-fill',
            'connecting' => 'ri ri-loader-4-line',
            'qr_ready' => 'ri ri-qr-code-line',
            'service_unavailable' => 'ri ri-wifi-off-line',
            'error' => 'ri ri-error-warning-fill',
            default => 'ri ri-close-circle-fill'
        };
    }

    public function getStatusTextProperty()
    {
        return match ($this->status) {
            'connected' => 'Conectado',
            'connecting' => 'Conectando...',
            'qr_ready' => 'Esperando QR',
            'service_unavailable' => 'Servicio No Disponible',
            'error' => 'Error',
            default => 'Desconectado'
        };
    }

    protected function getPageTitle(): string
    {
        return 'WhatsApp - Panel de Control';
    }

    protected function getBreadcrumb(): array
    {
        return [
            'admin.dashboard' => 'Dashboard',
            'admin.whatsapp.index' => 'WhatsApp'
        ];
    }

    public function render()
    {
        return $this->renderWithLayout('livewire.admin.whatsapp.index', [
            'status' => $this->status,
            'statusColor' => $this->statusColor,
            'statusIcon' => $this->statusIcon,
            'statusText' => $this->statusText,
            'user' => $this->user,
            'lastSeen' => $this->lastSeen,
            'messages' => $this->messages,
            'stats' => $this->stats,
            'isLoading' => $this->isLoading,
            'connectionError' => $this->connectionError,
            'activeTab' => $this->activeTab,
            'todayMessages' => $this->todayMessages,
            'dailyMessages' => $this->dailyMessages,
        ], [
            'title' => 'WhatsApp - Panel de Control',
            'description' => 'Panel de control para gestión de WhatsApp',
            'breadcrumb' => $this->getBreadcrumb()
        ]);
    }
}