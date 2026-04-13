<?php

namespace App\Livewire\Admin\Chat;

use Livewire\Component;
use App\Models\ChatMessage;
use App\Models\User;
use App\Traits\HasDynamicLayout;
use Illuminate\Support\Facades\Auth;

class ChatInterno extends Component
{
    use HasDynamicLayout;

    public $selectedUserId = null;
    public $selectedUser = null;
    public $messageText = '';
    public $search = '';
    public $messages = [];

    public function mount()
    {
        $this->loadMessages();
    }

    public function getPageTitle(): string
    {
        return 'Chat Interno';
    }

    public function getBreadcrumb(): array
    {
        return [
            'admin.dashboard' => 'Dashboard',
            '' => 'Chat Interno',
        ];
    }

    public function getChatUsersProperty()
    {
        $currentUser = Auth::user();

        $query = User::where('empresa_id', $currentUser->empresa_id)
            ->where('id', '!=', $currentUser->id)
            ->where('status', 1);

        if (!empty($this->search)) {
            $query->where('name', 'like', '%' . $this->search . '%');
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
            // Ya no marcamos como leído inmediatamente. Se hará vía JS (3 seg o clic)
        }
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
            ->take(100)
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
                    'time' => $msg->created_at->format('h:i A'),
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
        $this->dispatch('scrollToBottom');
    }

    public function markAsRead()
    {
        if (!$this->selectedUserId) return;

        $currentUser = Auth::user();

        $updated = ChatMessage::where('sender_id', $this->selectedUserId)
            ->where('receiver_id', $currentUser->id)
            ->where('empresa_id', $currentUser->empresa_id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        if ($updated) {
            $this->loadMessages();
            $this->dispatch('chat-marked-as-read');
        }
    }

    public function pollMessages()
    {
        if ($this->selectedUserId) {
            $this->loadMessages();
        }
    }

    public function getUnreadTotalProperty()
    {
        return ChatMessage::where('receiver_id', Auth::id())
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
        return $this->renderWithLayout('livewire.admin.chat.chat-interno', [
            'chatUsers' => $this->chatUsers,
            'unreadTotal' => $this->unreadTotal,
        ]);
    }
}
