<div class="space-y-6 px-4 py-6" x-data="{ saved:false }" x-on:settings-saved.window="saved=true; setTimeout(()=>saved=false,3000)">
    <div class="flex items-center justify-between">
        <h1 class="text-xl font-semibold">Paramètres: {{ ucfirst($group) }}</h1>
        <a href="{{ route('admin.settings.index') }}" class="text-sm text-indigo-600 hover:underline" wire:navigate>Retour</a>
    </div>

    <form wire:submit.prevent="save" class="space-y-6">
        <div class="space-y-4 bg-white border rounded p-4">
            @foreach($items as $item)
                <div class="flex flex-col gap-1">
                    <label class="text-sm font-medium" for="s_{{ $item['key'] }}">
                        {{ $item['label'] }}
                    </label>
                    @php $inputType = 'text'; @endphp
                    @if($item['type']==='int' || $item['type']==='float')
                        @php $inputType='number'; @endphp
                    @elseif($item['type']==='bool')
                        <div>
                            <label class="inline-flex items-center gap-2 text-sm">
                                <input type="checkbox"
                                       id="s_{{ $item['key'] }}"
                                       wire:model.defer="form.{{ $item['key'] }}"
                                       @if($item['type']==='bool') value="1" @endif
                                       {{ $form[$item['key']] ? 'checked' : '' }}>
                                <span>Activer</span>
                            </label>
                        </div>
                    @elseif(in_array($item['type'], ['json','array']))
                        <textarea id="s_{{ $item['key'] }}"
                                  wire:model.defer="form.{{ $item['key'] }}"
                                  class="w-full border rounded px-2 py-1.5 text-xs font-mono h-32"></textarea>
                        @if($item['description']) <p class="text-xs text-gray-500">{{ $item['description'] }}</p> @endif
                        @continue
                    @endif

                    @if($item['type']!=='bool' && !in_array($item['type'], ['json','array']))
                        <input type="{{ $inputType }}"
                               id="s_{{ $item['key'] }}"
                               wire:model.defer="form.{{ $item['key'] }}"
                               class="w-full border rounded px-2 py-1.5 text-sm"/>
                    @endif

                    @if($item['description'])
                        <p class="text-xs text-gray-500">{{ $item['description'] }}</p>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="flex items-center gap-3">
            <button type="submit"
                    class="px-4 py-2 rounded bg-indigo-600 text-white text-sm hover:bg-indigo-500">
                Enregistrer
            </button>
            <div x-show="saved" class="text-sm text-green-600">Sauvegardé ✅</div>
        </div>
    </form>
</div>