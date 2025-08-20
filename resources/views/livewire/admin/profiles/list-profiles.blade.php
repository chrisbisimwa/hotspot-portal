@section('page_title', 'Profiles')

@section('breadcrumb')
    <li class="breadcrumb-item active">Profiles</li>
@endsection

<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-shopping-cart mr-1"></i>
                All Profiles
            </h3>
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
