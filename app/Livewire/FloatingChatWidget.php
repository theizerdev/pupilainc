<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class FloatingChatWidget extends Component
{
    public $isOpen = false;
    public $isMinimized = false;
    public $selectedUserId = null;
    public $selectedUser = null;
    public $messageText = '';
    public $searchUser = '';
    public $messages = [];
    public $totalUnread = 0;
    public $lastMessageId = 0;
    
    protected $listeners = ['toggle-widget' => 'toggleWidget'];

    public function mount()
    {
        $this->updateTotalUnread();
        
        if (Auth::check()) {
            $lastMsg = ChatMessage::where('receiver_id', Auth::id())
                ->where('empresa_id', Auth::user()->empresa_id)
                ->orderBy('id', 'desc')
                ->first();
            
            $this->lastMessageId = $lastMsg ? $lastMsg->id : 0;
        }
    }

    public function toggleWidget()
    {
        $this->isOpen = !$this->isOpen;
        
        if (!$this->isOpen) {
            $this->selectedUserId = null;
            $this->selectedUser = null;
            $this->messages = [];
        }
        
        if ($this->isOpen) {
            $this->updateTotalUnread();
        }
    }

    public function toggleMinimize()
    {
        $this->isMinimized = !$this->isMinimized;
    }
    
    public function closeWidget()
    {
        $this->isOpen = false;
        $this->selectedUserId = null;
        $this->selectedUser = null;
        $this->messages = [];
    }

    public function getChatUsersProperty()
    {
        $currentUser = Auth::user();

        $query = User::where('empresa_id', $currentUser->empresa_id)
            ->where('id', '!=', $currentUser->id)
            ->where('status', 1);

        if (!empty($this->searchUser)) {
            $query->where('name', 'like', '%' . $this->searchUser . '%');
        }

        $users = $query->orderBy('name')->get();

        return $users->map(function ($user) use ($currentUser) {
            $lastMessage = ChatMessage::conversation($currentUser->id, $user->id)
                ->forEmpresa($currentUser->empresa_id)
                ->latest()
                ->first();

            $unreadCount = ChatMessage::where('sender_id', $user->id)
                ->where('receiver_id', $currentUser->id)
                ->where('empresa_id', $currentUser->empresa_id)
                ->where('is_read', false)
                ->count();

            return [
                'id' => $user->id,
                'name' => $user->name,
                'avatar' => $user->avatar,
                'role' => $user->roles->first()?->name ?? 'Usuario',
                'last_message' => $lastMessage?->message ?? '',
                'last_message_time' => $lastMessage?->created_at,
                'unread_count' => $unreadCount,
                'initials' => $this->getInitials($user->name),
            ];
        })->sortByDesc('last_message_time')->values();
    }

    public function selectUser($userId)
    {
        $this->selectedUserId = $userId;
        $currentUser = Auth::user();

        $this->selectedUser = User::where('id', $userId)
            ->where('empresa_id', $currentUser->empresa_id)
            ->first();

        if ($this->selectedUser) {
            $this->loadMessages();
            
            // Marcar como leídos
            ChatMessage::where('sender_id', $userId)
                ->where('receiver_id', Auth::id())
                ->where('empresa_id', $currentUser->empresa_id)
                ->where('is_read', false)
                ->update([
                    'is_read' => true,
                    'read_at' => now(),
                ]);
            
            $this->updateTotalUnread();
        }
    }

    public function backToList()
    {
        $this->selectedUserId = null;
        $this->selectedUser = null;
        $this->messages = [];
        $this->messageText = '';
    }

    public function loadMessages()
    {
        if (!$this->selectedUserId) {
            $this->messages = [];
            return;
        }

        $currentUser = Auth::user();

        $this->messages = ChatMessage::conversation($currentUser->id, $this->selectedUserId)
            ->forEmpresa($currentUser->empresa_id)
            ->with(['sender:id,name,avatar'])
            ->latest()
            ->take(50)
            ->get()
            ->sortBy('created_at')
            ->values()
            ->map(function ($msg) use ($currentUser) {
                return [
                    'id' => $msg->id,
                    'message' => $msg->message,
                    'sender_id' => $msg->sender_id,
                    'is_mine' => $msg->sender_id === $currentUser->id,
                    'sender_name' => $msg->sender->name,
                    'sender_avatar' => $msg->sender->avatar,
                    'sender_initials' => $this->getInitials($msg->sender->name),
                    'time' => $msg->created_at->format('H:i'),
                    'date' => $msg->created_at->format('d/m/Y'),
                    'is_read' => $msg->is_read,
                ];
            })
            ->toArray();
    }

    public function sendMessage()
    {
        if (empty(trim($this->messageText)) || !$this->selectedUserId) {
            return;
        }

        $currentUser = Auth::user();

        ChatMessage::create([
            'sender_id' => $currentUser->id,
            'receiver_id' => $this->selectedUserId,
            'message' => trim($this->messageText),
            'empresa_id' => $currentUser->empresa_id,
        ]);

        $this->messageText = '';
        $this->loadMessages();
        $this->dispatch('message-sent-widget');
    }

    public function checkNewMessages()
    {
        if (!Auth::check()) return;

        // Actualizar contador total
        $this->updateTotalUnread();

        // Si hay una conversación activa, recargar mensajes
        if ($this->selectedUserId && $this->isOpen) {
            $this->loadMessages();
        }

        // Verificar nuevos mensajes globales
        $newMessages = ChatMessage::where('receiver_id', Auth::id())
            ->where('empresa_id', Auth::user()->empresa_id)
            ->where('id', '>', $this->lastMessageId)
            ->where('is_read', false)
            ->with('sender:id,name,avatar')
            ->orderBy('id', 'asc')
            ->get();

        if ($newMessages->count() > 0) {
            $this->lastMessageId = $newMessages->last()->id;
        }
    }

    public function updateTotalUnread()
    {
        if (!Auth::check()) return;

        $this->totalUnread = ChatMessage::where('receiver_id', Auth::id())
            ->where('empresa_id', Auth::user()->empresa_id)
            ->where('is_read', false)
            ->count();
    }

    private function getInitials($name)
    {
        $parts = explode(' ', trim($name));
        $initials = '';
        foreach (array_slice($parts, 0, 2) as $part) {
            $initials .= mb_strtoupper(mb_substr($part, 0, 1));
        }
        return $initials;
    }

    public function render()
    {
        return view('livewire.floating-chat-widget', [
            'chatUsers' => $this->chatUsers,
        ]);
    }
}
