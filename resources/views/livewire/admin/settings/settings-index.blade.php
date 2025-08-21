@section('page_title', 'Paramètres')

@section('breadcrumb')
    <li class="breadcrumb-item active">Paramètres</li>
@endsection


<div>
    <div class="space-y-6 px-4 py-6">
      

        <div class="grid md:grid-cols-3 gap-4">
            @foreach ($groups as $g)
                <a href="{{ route('admin.settings.group', ['group' => $g]) }}"
                    class="border rounded p-4 bg-white shadow-sm hover:shadow transition flex flex-col gap-2"
                    wire:navigate>
                    <div class="text-sm font-medium">{{ ucfirst($g) }}</div>
                    <div class="text-xs text-gray-500">Gérer les paramètres {{ $g }}</div>
                </a>
            @endforeach
        </div>
    </div>
</div>
