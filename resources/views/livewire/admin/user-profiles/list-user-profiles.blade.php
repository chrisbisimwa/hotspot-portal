@section('page_title', 'Profiles')

@section('breadcrumb')
    <li class="breadcrumb-item active">Profiles</li>
@endsection

<div>
    <div class="card">
        <div class="card-header d-flex justify-content-between flex-wrap align-items-center">
            <h3 class="card-title mb-2 mb-sm-0">
                <i class="fas fa-layer-group mr-1"></i>
                All profiles
            </h3>
            <div class="btn-toolbar mb-2 mb-sm-0">
                <livewire:admin.user-profiles.create-user-profile :key="'create-up-component'"/>
            </div>
        </div>
        <div class="card-body">
            <x-slot name="filters">
                <!-- (Optionnel, tu peux conserver ce bloc si tu veux styliser autrement) -->
            </x-slot>
            <div class="mb-3" style="max-width:200px;">
                <select wire:model.live="publishedFilter" class="form-control form-control-sm">
                    <option value="">All Profiles</option>
                    <option value="true">Active</option>
                    <option value="false">Inactive</option>
                </select>
            </div>

            @include('livewire.shared.data-table', [
                'data' => $data,
                'columns' => $columns,
                'searchPlaceholder' => $searchPlaceholder,
            ])
        </div>
    </div>

