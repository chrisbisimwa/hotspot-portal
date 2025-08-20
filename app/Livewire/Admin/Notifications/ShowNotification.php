<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Notifications;

use App\Models\Notification;
use Livewire\Component;

class ShowNotification extends Component
{
    public Notification $notification;
    public bool $showRaw = false;

    public function mount(Notification $notification): void
    {
        abort_unless(auth()->user()?->hasRole('admin'), 403);
        $this->notification = $notification->load(['user','order','hotspotUser']);
    }

    public function toggleRaw(): void
    {
        $this->showRaw = !$this->showRaw;
    }

    public function markRead(): void
    {
        if (!$this->notification->read_at) {
            $this->notification->update(['read_at' => now()]);
            session()->flash('success','Notification marked as read.');
            $this->dispatch('notification-marked-read');
        }
    }

    public function resend(): void
    {
        if (in_array($this->notification->status, ['failed','queued','retrying'])) {
            $this->notification->update([
                'status' => 'queued',
                'provider_response' => null,
                'sent_at' => null,
            ]);
            session()->flash('success','Notification re-queued.');
            $this->dispatch('notification-resend');
        }
    }

    public function cancel(): void
    {
        if ($this->notification->status !== 'sent') {
            $this->notification->update(['status' => 'cancelled']);
            session()->flash('success','Notification cancelled.');
            $this->dispatch('notification-updated');
        }
    }

    public function render()
    {
        return view('livewire.admin.notifications.show-notification')
            ->layout('layouts.admin');
    }
}