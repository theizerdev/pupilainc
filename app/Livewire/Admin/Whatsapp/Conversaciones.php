<?php

namespace App\Livewire\Admin\Whatsapp;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class Conversaciones extends Component
{
    use WithPagination;

    public $searchTerm = '';
    public $filterStatus = '';
    public $sortBy = 'last_message';
    public $viewMode = 'grid';
    public $selectedConversation = null;
    public $companyId;
    public $apiKey;
    
    protected $paginationTheme = 'bootstrap';

    public function mount()
    {
        $empresa = auth()->user()->empresa;
        $this->apiKey = $empresa->whatsapp_api_key;
        $this->companyId = $empresa->id;
    }

    private function getApiBaseUrl()
    {
        return config('whatsapp.api_url');
    }

    private function getApiHeaders()
    {
        return [
            'X-API-Key' => $this->apiKey,
            'X-Company-Id' => (string) $this->companyId,
            'Content-Type' => 'application/json',
        ];
    }

    public function getConversationsProperty()
    {
        $cacheKey = 'whatsapp_conversations_' . auth()->id() . '_' . md5($this->searchTerm . $this->filterStatus . $this->sortBy);
        
        return Cache::remember($cacheKey, 30, function () {
            try {
                $params = [
                    'search' => $this->searchTerm,
                    'status' => $this->filterStatus,
                    'sort' => $this->sortBy,
                    'page' => $this->getPage(),
                    'per_page' => 12
                ];

                $response = Http::timeout(10)
                    ->withHeaders($this->getApiHeaders())
                    ->get($this->getApiBaseUrl() . '/api/whatsapp/conversations', array_filter($params));

                if ($response->successful()) {
                    $data = $response->json();
                    return collect($data['data'] ?? [])->map(function ($conv) {
                        return [
                            'id' => $conv['id'] ?? uniqid(),
                            'name' => $conv['name'] ?? 'Contacto desconocido',
                            'avatar' => $conv['avatar'] ?? null,
                            'last_message' => $conv['last_message'] ?? 'Sin mensajes',
                            'last_message_time' => $conv['last_message_time'] ?? now(),
                            'last_message_type' => $conv['last_message_type'] ?? 'text',
                            'total_messages' => $conv['total_messages'] ?? 0,
                            'unread_count' => $conv['unread_count'] ?? 0,
                            'online' => $conv['online'] ?? false,
                            'pinned' => $conv['pinned'] ?? false,
                            'muted' => $conv['muted'] ?? false,
                            'active' => false
                        ];
                    });
                }
            } catch (\Exception $e) {
                \Log::error('Error fetching conversations: ' . $e->getMessage());
            }

            return collect([]);
        });
    }

    public function refreshConversations()
    {
        Cache::forget('whatsapp_conversations_' . auth()->id());
        $this->resetPage();
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Conversaciones actualizadas'
        ]);
    }

    public function toggleView($mode)
    {
        $this->viewMode = $mode;
    }

    public function selectConversation($conversationId)
    {
        $this->selectedConversation = $conversationId;
        $this->dispatch('conversationSelected', $conversationId);
        
        if ($this->conversations->firstWhere('id', $conversationId)['unread_count'] > 0) {
            $this->markAsRead($conversationId);
        }
    }

    public function markAsRead($conversationId)
    {
        try {
            Http::timeout(5)
                ->withHeaders($this->getApiHeaders())
                ->post($this->getApiBaseUrl() . "/api/whatsapp/conversations/{$conversationId}/read");
            
            Cache::forget('whatsapp_conversations_' . auth()->id());
        } catch (\Exception $e) {
            \Log::error('Error marking conversation as read: ' . $e->getMessage());
        }
    }

    public function clearFilters()
    {
        $this->searchTerm = '';
        $this->filterStatus = '';
        $this->resetPage();
        Cache::forget('whatsapp_conversations_' . auth()->id());
    }

    public function updatedSearchTerm()
    {
        $this->resetPage();
    }

    public function updatedFilterStatus()
    {
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.admin.whatsapp.conversaciones', [
            'conversations' => $this->conversations
        ]);
    }
}
