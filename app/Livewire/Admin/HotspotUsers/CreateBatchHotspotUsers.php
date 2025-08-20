<?php

declare(strict_types=1);

namespace App\Livewire\Admin\HotspotUsers;

use App\Models\User;
use App\Models\UserProfile;
use App\Services\Hotspot\BatchHotspotUserService;
use Livewire\Component;

class CreateBatchHotspotUsers extends Component
{
    public bool $showModal = false;

    public ?int $owner_id = null;
    public ?int $user_profile_id = null;
    public int $quantity = 10;
    public ?int $override_validity = null;
    public ?int $override_quota_mb = null;
    public string $username_prefix = 'HS';
    public int $password_length = 8;
    public ?string $batch_ref = null;
    public bool $generate_pdf = true;

    public array $owners = [];
    public array $profiles = [];

    protected function rules(): array
    {
        return [
            'owner_id' => ['required','integer','exists:users,id'],
            'user_profile_id' => ['required','integer','exists:user_profiles,id'],
            'quantity' => ['required','integer','min:1','max:500'],
            'override_validity' => ['nullable','integer','min:1'],
            'override_quota_mb' => ['nullable','integer','min:1'],
            'username_prefix' => ['required','string','max:10'],
            'password_length' => ['required','integer','min:4','max:32'],
            'batch_ref' => ['nullable','string','max:60'],
            'generate_pdf' => ['boolean'],
        ];
    }

    public function mount(): void
    {
        abort_unless(auth()->user()?->hasRole('admin'), 403);

        $this->owners = User::orderBy('name')->limit(200)->get(['id','name'])->toArray();
        $this->profiles = UserProfile::where('is_active', true)->orderBy('name')->get(['id','name','validity_minutes','data_limit_mb'])->toArray();
    }

    public function open(): void
    {
        $this->resetValidation();
        $this->showModal = true;
    }

    public function close(): void
    {
        $this->showModal = false;
    }

    public function save(BatchHotspotUserService $service)
    {
        $data = $this->validate();

        $result = $service->createBatch(
            ownerId: $this->owner_id,
            profileId: $this->user_profile_id,
            quantity: $this->quantity,
            overrideValidity: $this->override_validity,
            overrideQuotaMb: $this->override_quota_mb,
            usernamePrefix: $this->username_prefix,
            passwordLength: $this->password_length,
            batchRef: $this->batch_ref
        );

        $this->dispatch('hotspot-user-created');
        session()->flash('success', "Batch {$result['batch_ref']} created ({$this->quantity} users).");

        $redirect = null;
        if ($this->generate_pdf) {
            $ids = collect($result['users'])->pluck('id')->implode(',');
            $redirect = route('admin.hotspot-users.tickets.pdf.batch', ['ids' => $ids, 'batch' => $result['batch_ref']]);
        }

        $this->close();
        $this->resetExcept(['owners','profiles','username_prefix','password_length','generate_pdf']);
        $this->quantity = 10;

        if ($redirect) {
            return redirect()->to($redirect);
        }
    }

    public function render()
    {
        return view('livewire.admin.hotspot-users.create-batch-hotspot-users');
    }
}