    <!-- Modal détail -->
    <div x-cloak x-show="@js($showDetailModal)"
         class="fixed inset-0 z-40 flex items-center justify-center bg-black/40 p-4">
        <div class="bg-white dark:bg-gray-900 rounded shadow-lg w-full max-w-3xl max-h-[90vh] flex flex-col">
            <div class="px-4 py-3 border-b flex items-center justify-between">
                <h2 class="font-semibold text-sm">
                    @if($detailLog)
                        Log #{{ $detailLog->id }}
                    @else
                        Log (chargement…)
                    @endif
                </h2>
                <div class="flex items-center gap-2">
                    @if($detailLog)
                        @if($detailLog->deleted_at)
                            <button wire:click="restore({{ $detailLog->id }})"
                                    class="px-2 py-1 text-xs rounded bg-green-600 text-white">
                                Restaurer
                            </button>
                        @else
                            <button wire:click="forceDelete({{ $detailLog->id }})"
                                    onclick="return confirm('Supprimer définitivement ?')"
                                    class="px-2 py-1 text-xs rounded bg-red-600 text-white">
                                Suppr. définitive
                            </button>
                        @endif
                    @endif
                    <button @click="$wire.closeDetail()" class="text-gray-500 hover:text-gray-700 text-sm">✕</button>
                </div>
            </div>

            <div class="overflow-y-auto p-4 space-y-4 text-sm">
                @if($detailLog)
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <div class="text-xs uppercase text-gray-500">Créé</div>
                            <div>{{ $detailLog->created_at }}</div>
                        </div>
                        <div>
                            <div class="text-xs uppercase text-gray-500">Niveau</div>
                            <div>{{ $detailLog->level }}</div>
                        </div>
                        <div>
                            <div class="text-xs uppercase text-gray-500">Action</div>
                            <div class="font-mono">{{ $detailLog->action }}</div>
                        </div>
                        <div>
                            <div class="text-xs uppercase text-gray-500">Actor</div>
                            <div>
                                @if($detailLog->actor)
                                    {{ $detailLog->actor->name }} (ID {{ $detailLog->actor->id }})
                                @elseif($detailLog->actor_id)
                                    #{{ $detailLog->actor_id }}
                                @else
                                    -
                                @endif
                            </div>
                        </div>
                        <div>
                            <div class="text-xs uppercase text-gray-500">IP</div>
                            <div>{{ $detailLog->ip_address ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="text-xs uppercase text-gray-500">User Agent</div>
                            <div class="truncate" title="{{ $detailLog->user_agent }}">
                                {{ $detailLog->user_agent ?? '-' }}
                            </div>
                        </div>
                        <div class="col-span-2">
                            <div class="text-xs uppercase text-gray-500">Message</div>
                            <pre class="mt-1 p-2 bg-gray-100 rounded max-h-48 overflow-auto whitespace-pre-wrap text-xs">{{ $detailLog->message }}</pre>
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between">
                            <h3 class="text-xs uppercase text-gray-500">Context</h3>
                            <button
                                x-data
                                x-on:click="navigator.clipboard.writeText(@js(json_encode($detailLog->context, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT)))"
                                class="text-xs text-indigo-600 hover:underline">
                                Copier JSON
                            </button>
                        </div>
                        <pre class="mt-1 p-2 bg-gray-900 text-green-200 rounded max-h-64 overflow-auto text-xs">
{{ json_encode($detailLog->context, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT) }}</pre>
                    </div>
                @else
                    <div class="text-center text-gray-500 py-8">
                        Chargement du log…
                    </div>
                @endif
            </div>

            <div class="px-4 py-3 border-t flex justify-end">
                <button @click="$wire.closeDetail()" class="px-3 py-1.5 text-sm rounded border bg-white hover:bg-gray-50">
                    Fermer
                </button>
            </div>
        </div>
    </div>