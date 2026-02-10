<?php

namespace App\Livewire\Admin\Whatsapp;

use Livewire\Component;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class Stats extends Component
{
    public $stats = [
        'total' => 0,
        'sent' => 0,
        'delivered' => 0,
        'read' => 0,
        'failed' => 0
    ];
    
    public $status = 'disconnected';
    public $statusText = 'Desconectado';
    public $statusColor = 'danger';
    public $statusIcon = 'ri ri-wifi-off-line';
    
    public $user = null;
    public $lastSeen = null;
    public $deviceBattery = null;
    public $connectionError = null;
    
    public $messages = [];
    public $conversations = [];
    public $unreadMessagesCount = 0;
    
    protected $apiBaseUrl;
    protected $apiKey;
    
    public function mount()
    {
        $this->apiBaseUrl = config('services.whatsapp.api_url', 'http://localhost:3000');
        $this->apiKey = config('services.whatsapp.api_key', 'your-api-key-here');
        
        $this->loadInitialData();
    }
    
    public function loadInitialData()
    {
        $this->getStatus();
        $this->getStats();
        $this->getRecentMessages();
        $this->getConversations();
    }
    
    public function getStatus()
    {
        try {
            $response = Http::timeout(5)->withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept' => 'application/json',
            ])->get($this->apiBaseUrl . '/status');
            
            if ($response->successful()) {
                $data = $response->json();
                $this->status = $data['status'] ?? 'disconnected';
                $this->user = $data['user'] ?? null;
                $this->lastSeen = $data['lastSeen'] ?? null;
                $this->deviceBattery = $data['battery'] ?? null;
                
                $this->updateStatusDisplay();
            } else {
                $this->status = 'error';
                $this->connectionError = 'No se pudo conectar con el servicio de WhatsApp';
                $this->updateStatusDisplay();
            }
        } catch (\Exception $e) {
            $this->status = 'error';
            $this->connectionError = 'Error de conexión: ' . $e->getMessage();
            $this->updateStatusDisplay();
        }
    }
    
    public function getStats()
    {
        try {
            $cacheKey = 'whatsapp_stats_' . auth()->id();
            
            $stats = Cache::remember($cacheKey, 300, function () {
                $response = Http::timeout(5)->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Accept' => 'application/json',
                ])->get($this->apiBaseUrl . '/stats');
                
                if ($response->successful()) {
                    return $response->json();
                }
                
                return [
                    'total' => 0,
                    'sent' => 0,
                    'delivered' => 0,
                    'read' => 0,
                    'failed' => 0
                ];
            });
            
            $this->stats = $stats;
        } catch (\Exception $e) {
            \Log::error('Error fetching WhatsApp stats: ' . $e->getMessage());
        }
    }
    
    public function getRecentMessages()
    {
        try {
            $response = Http::timeout(5)->withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept' => 'application/json',
            ])->get($this->apiBaseUrl . '/messages/recent?limit=10');
            
            if ($response->successful()) {
                $this->messages = $response->json()['messages'] ?? [];
            }
        } catch (\Exception $e) {
            \Log::error('Error fetching recent messages: ' . $e->getMessage());
            $this->messages = [];
        }
    }
    
    public function getConversations()
    {
        try {
            $response = Http::timeout(5)->withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept' => 'application/json',
            ])->get($this->apiBaseUrl . '/conversations');
            
            if ($response->successful()) {
                $data = $response->json();
                $this->conversations = $data['conversations'] ?? [];
                $this->unreadMessagesCount = $data['unreadCount'] ?? 0;
            }
        } catch (\Exception $e) {
            \Log::error('Error fetching conversations: ' . $e->getMessage());
            $this->conversations = [];
            $this->unreadMessagesCount = 0;
        }
    }
    
    public function refresh()
    {
        $this->loadInitialData();
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Datos actualizados correctamente'
        ]);
    }
    
    public function testConnection()
    {
        $this->getStatus();
        $this->dispatch('notify', [
            'type' => $this->status === 'connected' ? 'success' : 'warning',
            'message' => $this->status === 'connected' 
                ? 'Conexión verificada correctamente' 
                : 'No se pudo establecer conexión'
        ]);
    }
    
    private function updateStatusDisplay()
    {
        switch ($this->status) {
            case 'connected':
                $this->statusText = 'Conectado';
                $this->statusColor = 'success';
                $this->statusIcon = 'ri ri-wifi-line';
                break;
            case 'connecting':
                $this->statusText = 'Conectando';
                $this->statusColor = 'warning';
                $this->statusIcon = 'ri ri-loader-4-line';
                break;
            case 'disconnected':
                $this->statusText = 'Desconectado';
                $this->statusColor = 'danger';
                $this->statusIcon = 'ri ri-wifi-off-line';
                break;
            default:
                $this->statusText = 'Error';
                $this->statusColor = 'danger';
                $this->statusIcon = 'ri ri-error-warning-line';
                break;
        }
    }
    
    public function render()
    {
        return view('livewire.admin.whatsapp.stats');
    }
}