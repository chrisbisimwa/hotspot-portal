@section('page_title','Edit Notification #'.$notification->id)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.notifications.index') }}">Notifications</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.notifications.show', $notification->id) }}">#{{ $notification->id }}</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

<div>
    @if(session('success'))
        <div class="alert alert-success py-2">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger py-2">{{ session('error') }}</div>
    @endif

    <form wire:submit.prevent="save">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <strong>Notification Data</strong>
                <div class="btn-group btn-group-sm">
                    <button type="button" wire:click="resendNow" class="btn btn-outline-primary" @if($notification->sent_at) disabled @endif>
                        <i class="fas fa-sync"></i> Re-Queue
                    </button>
                </div>
            </div>
            <div class="card-body">

                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>Channel *</label>
                        <select class="form-control form-control-sm" wire:model.live="channel" @if($notification->sent_at) disabled @endif>
                            <option value="sms">SMS</option>
                            <option value="email">Email</option>
                        </select>
                        @error('channel') <small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="form-group col-md-8">
                        <label>To</label>
                        <input type="text" class="form-control form-control-sm" wire:model.live="to" @if($notification->sent_at) disabled @endif>
                        @error('to') <small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                </div>

                <div class="form-group">
                    <label>Subject</label>
                    <input type="text" class="form-control form-control-sm" wire:model.live="subject" @if($notification->sent_at) disabled @endif>
                    @error('subject') <small class="text-danger">{{ $message }}</small>@enderror
                </div>

                <div class="form-group">
                    <label>Message *</label>
                    <textarea rows="6" class="form-control form-control-sm" wire:model.live="message" @if($notification->sent_at) disabled @endif></textarea>
                    @error('message') <small class="text-danger">{{ $message }}</small>@enderror
                </div>

            </div>
            <div class="card-footer text-right">
                <button class="btn btn-success btn-sm" @if($notification->sent_at) disabled @endif>
                    <i class="fas fa-save"></i> Save
                </button>
            </div>
        </div>
    </form>
</div>