<?php

declare(strict_types=1);

namespace App\Livewire\Admin\HotspotUsers;

use App\Models\HotspotUser;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Support\Str;
use Livewire\Component;

class CreateHotspotUser extends Component
{
    public bool $showModal = false;

    public ?int $owner_id = null;
    public ?int $user_profile_id = null;
    public string $username = '';
    public string $password = '';
    public int $validity_minutes = 60;
    public ?int $data_limit_mb = null;
    public ?string $status = 'active';

    public array $owners = [];
    public array $profiles = [];

    protected function rules(): array
    {
        return [
            'owner_id'        => ['required','integer','exists:users,id'],
            'user_profile_id' => ['required','integer','exists:user_profiles,id'],
            'username'        => ['required','string','max:50','unique:hotspot_users,username'],
            'password'        => ['required','string','min:6','max:60'],
            'validity_minutes'=> ['required','integer','min:1'],
            'data_limit_mb'   => ['nullable','integer','min:1'],
            'status'          => ['required','in:active,expired,suspended'],
        ];
    }

    public function mount(): void
    {
        abort_unless(auth()->user()?->hasRole('admin'), 403);

        $this->owners = User::orderBy('name')->limit(200)->get(['id','name'])->toArray();
        $this->profiles = UserProfile::where('is_active', true)->orderBy('name')->get(['id','name','validity_minutes','data_limit_mb'])->toArray();
        $this->generateCredentials();
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

    public function generateCredentials(): void
    {
        $this->username = 'HS'.strtoupper(Str::random(6));
        //$this->password = Str::random(10);
        $this->password = $this->username;
    }

    public function selectProfile(int $id): void
    {
        $profile = collect($this->profiles)->firstWhere('id', $id);
        if ($profile) {
            $this->validity_minutes = (int) $profile['validity_minutes'];
            $this->data_limit_mb = $profile['data_limit_mb'];
        }
    }

    public function save(): void
    {
        $data = $this->validate();

        HotspotUser::create($data);

        $this->dispatch('hotspot-user-created');
        session()->flash('success', "Hotspot user {$this->username} created.");

        $this->close();
        $this->resetExcept(['owners','profiles']);
        $this->status = 'active';
        $this->generateCredentials();
    }

    public function render()
    {
        return view('livewire.admin.hotspot-users.create-hotspot-user');
    }
}