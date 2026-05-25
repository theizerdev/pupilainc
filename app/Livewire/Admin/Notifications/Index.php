<?php

namespace App\Livewire\Admin\Notifications;

use App\Traits\HasDynamicLayout;
use Livewire\Component;
use App\Models\Notification;
use Livewire\WithPagination;

class Index extends Component
{
    use HasDynamicLayout;


    use WithPagination;

    public function markAsRead($notificationId)
    {
        $notification = Notification::find($notificationId);
        if ($notification) {
            $notification->markAsRead();
        }
    }

    public function markAllAsRead()
    {
        Notification::whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function render()
    {
        return view('livewire.admin.notifications.index',[
            'notifications' => Notification::latest()->paginate(100),
        ])->layout($this->getLayout());
    }
}




