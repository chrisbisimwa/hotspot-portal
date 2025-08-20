<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Notifications;

use App\Models\Notification;
use Livewire\Component;

class EditNotification extends Component
{
    public Notification $notification;

    public string $subject = '';
    public string $message = '';
    public ?string $to = null;
    public string $channel;

    public function mount(Notification $notification): void
    {
        abort_unless(auth()->user()?->hasRole('admin'), 403);
        $this->notification = $notification;
        $this->subject = $notification->subject ?? '';
        $this->message = $notification->message ?? '';
        $this->to = $notification->to;
        $this->channel = $notification->channel;
    }

    protected function rules(): array
    {
        return [
            'subject' => ['nullable','string','max:255'],
            'message' => ['required','string','max:2000'],
            'to' => ['nullable','string','max:255'],
            'channel' => ['required','string','in:sms,email'], // adapter
        ];
    }

    public function save(): void
    {
        $this->validate();

        if ($this->notification->sent_at) {
            session()->flash('error','Cannot edit a sent notification.');
            return;
        }

        $this->notification->update([
            'subject' => $this->subject ?: null,
            'message' => $this->message,
            'to' => $this->to,
            'channel' => $this->channel,
        ]);

        session()->flash('success','Notification updated.');
        $this->dispatch('notification-updated', id: $this->notification->id);
    }

    public function resendNow(): void
    {
        if ($this->notification->sent_at) {
            session()->flash('error','Already sent.');
            return;
        }
        $this->notification->update([
            'status' => 'queued',
            'provider_response' => null,
        ]);
        session()->flash('success','Re-queued.');
        $this->dispatch('notification-resend');
    }

    public function render()
    {
        return view('livewire.admin.notifications.edit-notification')
            ->layout('layouts.admin');
    }
}