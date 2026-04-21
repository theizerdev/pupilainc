<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ChatNotifications extends Component
{
    public $lastMessageId = 0;
    public $unreadCount = 0;
    
    // Propiedades para el Modal de Respuesta Rápida
    public $replyingToUserId = null;
    public $replyingToUser = null;
    public $replyMessage = '';
    public $recentMessages = [];
    public $isWindowOpen = false;
    public $unreadFloatingCount = 0;
    public $lastFloatingMessageId = 0;

    public function mount()
    {
        if (Auth::check()) {
            $lastMsg = ChatMessage::where('receiver_id', Auth::id())
                ->where('empresa_id', Auth::user()->empresa_id)
                ->orderBy('id', 'desc')
                ->first();
            
            $this->lastMessageId = $lastMsg ? $lastMsg->id : 0;
            $this->updateUnreadCount();
        }
    }

    public function checkNewMessages()
    {
        if (!Auth::check()) return;

        $newMessages = ChatMessage::where('receiver_id', Auth::id())
            ->where('empresa_id', Auth::user()->empresa_id)
            ->where('id', '>', $this->lastMessageId)
            ->where('is_read', false)
            ->with('sender:id,name,avatar')
            ->orderBy('id', 'asc')
            ->get();

        if ($newMessages->count() > 0) {
            $this->lastMessageId = $newMessages->last()->id;
            
            foreach ($newMessages as $msg) {
                $this->dispatch('new-chat-message', [
                    'id' => $msg->id,
                    'sender_id' => $msg->sender_id,
                    'sender' => $msg->sender->name,
                    'avatar' => $msg->sender->avatar ?? null,
                    'message' => str()->limit($msg->message, 80),
                    'time' => $msg->created_at->format('H:i')
                ]);
            }
        }
        
        $this->updateUnreadCount();
    }

    protected $listeners = [
        'chat-marked-as-read' => 'updateUnreadCount',
        'openReplyModal' => 'openReplyModal',
        'refreshFloatingChat' => 'refreshFloatingChat'
    ];

    public function openReplyModal($userId)
    {
        $this->replyingToUserId = $userId;
        $this->replyingToUser = User::find($userId);
        $this->isWindowOpen = true;
        $this->unreadFloatingCount = 0;
        
        // Marcar como leídos los mensajes de este usuario
        ChatMessage::where('sender_id', $userId)
            ->where('receiver_id', Auth::id())
            ->where('empresa_id', Auth::user()->empresa_id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
            
        $this->updateUnreadCount();
        $this->dispatch('chat-marked-as-read'); // Notificar a otros componentes

        // Obtener los últimos mensajes para el contexto
        $this->loadRecentMessages();
        
        // Establecer el último ID de mensaje
        $lastMessage = ChatMessage::where(function($query) use ($userId) {
                $query->where('sender_id', Auth::id())->where('receiver_id', $userId);
            })
            ->orWhere(function($query) use ($userId) {
                $query->where('sender_id', $userId)->where('receiver_id', Auth::id());
            })
            ->where('empresa_id', Auth::user()->empresa_id)
            ->orderBy('id', 'desc')
            ->first();
        
        $this->lastFloatingMessageId = $lastMessage ? $lastMessage->id : 0;

        $this->dispatch('show-reply-modal');
    }

    public function refreshFloatingChat()
    {
        if ($this->isWindowOpen && $this->replyingToUserId) {
            // Verificar si hay mensajes nuevos del usuario activo
            $newMessages = ChatMessage::where('sender_id', $this->replyingToUserId)
                ->where('receiver_id', Auth::id())
                ->where('empresa_id', Auth::user()->empresa_id)
                ->where('id', '>', $this->lastFloatingMessageId)
                ->orderBy('id', 'asc')
                ->get();

            if ($newMessages->count() > 0) {
                $this->lastFloatingMessageId = $newMessages->last()->id;
                $this->loadRecentMessages();
                
                // Incrementar contador si está minimizado
                $this->dispatch('new-floating-message', [
                    'count' => $newMessages->count()
                ]);
            }
        }
    }

    private function loadRecentMessages()
    {
        if (!$this->replyingToUserId) return;

        $this->recentMessages = ChatMessage::where(function($query) {
                $query->where('sender_id', Auth::id())->where('receiver_id', $this->replyingToUserId);
            })
            ->orWhere(function($query) {
                $query->where('sender_id', $this->replyingToUserId)->where('receiver_id', Auth::id());
            })
            ->where('empresa_id', Auth::user()->empresa_id)
            ->orderBy('id', 'desc')
            ->limit(20)
            ->get()
            ->reverse()
            ->values()
            ->toArray();
    }

    public function sendReply()
    {
        $this->validate([
            'replyMessage' => 'required|string|max:1000'
        ]);

        if (!$this->replyingToUserId) return;

        $message = ChatMessage::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $this->replyingToUserId,
            'empresa_id' => Auth::user()->empresa_id,
            'message' => $this->replyMessage,
            'is_read' => false,
        ]);
        
        $this->lastFloatingMessageId = $message->id;

        $this->replyMessage = '';
        $this->loadRecentMessages(); // Recargar mensajes
        $this->dispatch('message-sent');
    }
    
    public function resetFloatingCount()
    {
        $this->unreadFloatingCount = 0;
    }

    public function updateUnreadCount()
    {
        if (!Auth::check()) return;

        $this->unreadCount = ChatMessage::where('receiver_id', Auth::id())
            ->where('empresa_id', Auth::user()->empresa_id)
            ->where('is_read', false)
            ->count();
    }

    public function render()
    {
        return view('livewire.chat-notifications');
    }
}
