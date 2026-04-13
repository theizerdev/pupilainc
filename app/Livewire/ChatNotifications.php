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
        'openReplyModal' => 'openReplyModal'
    ];

    public function openReplyModal($userId)
    {
        $this->replyingToUserId = $userId;
        $this->replyingToUser = User::find($userId);
        
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

        // Obtener los últimos mensajes para el contexto (ej. últimos 5)
        $this->recentMessages = ChatMessage::where(function($query) use ($userId) {
                $query->where('sender_id', Auth::id())->where('receiver_id', $userId);
            })
            ->orWhere(function($query) use ($userId) {
                $query->where('sender_id', $userId)->where('receiver_id', Auth::id());
            })
            ->where('empresa_id', Auth::user()->empresa_id)
            ->orderBy('id', 'desc')
            ->limit(5)
            ->get()
            ->reverse()
            ->values()
            ->toArray();

        $this->dispatch('show-reply-modal');
    }

    public function sendReply()
    {
        $this->validate([
            'replyMessage' => 'required|string|max:1000'
        ]);

        if (!$this->replyingToUserId) return;

        ChatMessage::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $this->replyingToUserId,
            'empresa_id' => Auth::user()->empresa_id,
            'message' => $this->replyMessage,
            'is_read' => false,
        ]);

        $this->replyMessage = '';
        $this->dispatch('hide-reply-modal');
        $this->dispatch('show-toast', [
            'type' => 'success',
            'message' => 'Respuesta enviada exitosamente.'
        ]);
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
