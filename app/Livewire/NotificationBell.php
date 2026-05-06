<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Notification;
use Livewire\Attributes\On;

class NotificationBell extends Component
{
    public function markAsRead($notificationId)
    {
        $notification = Notification::where('user_id', auth()->id())->find($notificationId);
        if ($notification) {
            $notification->markAsRead();

            // Emitir evento para actualizar el contador
            $this->dispatch('notification-read');
        }
        // No re-render to maintain dropdown state
        $this->skipRender();
    }

    public function markAllAsRead()
    {
        $updated = Notification::where('user_id', auth()->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        if ($updated > 0) {
            // Emitir evento para actualizar el contador
            $this->dispatch('notification-read');
        }
    }

    public function viewAllNotifications()
    {
        return redirect()->route('admin.notifications.index');
    }

    #[On('notification-created')]
    public function refreshNotifications()
    {
        // Livewire automáticamente re-renderiza
    }

    #[On('notification-read')]
    public function refreshAfterRead()
    {
        // Livewire automáticamente re-renderiza
    }

    public function render()
    {
        // Obtener las últimas 10 notificaciones (tanto leídas como no leídas)
        $notifications = Notification::orderBy('created_at', 'desc')
            ->whereNull('read_at')
            ->take(10)
            ->get();

        // Contar solo las no leídas
        $unreadCount = Notification::whereNull('read_at')
            ->count();

        return view('livewire.notification-bell', [
            'notifications' => $notifications,
            'unreadCount' => $unreadCount
        ]);
    }
}
