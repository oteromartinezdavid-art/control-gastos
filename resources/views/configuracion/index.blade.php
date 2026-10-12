<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Configuración · Claves API</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-lg text-sm font-medium">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg text-sm font-medium">{{ session('error') }}</div>
            @endif

            {{-- Finnhub --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-bold text-gray-800">Finnhub (datos de mercado)</h3>
                    @if($tieneFinnhub)
                        <span class="px-2 py-0.5 bg-emerald-100 text-emerald-700 rounded-full text-xs font-bold uppercase">Configurada</span>
                    @else
                        <span class="px-2 py-0.5 bg-gray-100 text-gray-500 rounded-full text-xs font-bold uppercase">Sin configurar</span>
                    @endif
                </div>

                <p class="text-sm text-gray-500">
                    Clave para obtener precios y fundamentales en el Agente Fiduciario.
                    Consíguela gratis en <a href="https://finnhub.io" target="_blank" rel="noopener" class="text-indigo-600 hover:underline">finnhub.io</a>.
                </p>

                @if($tieneFinnhub)
                    <div class="text-sm text-gray-600 font-mono bg-gray-50 rounded px-3 py-2">{{ $finnhubKeyMask }}</div>
                @endif

                <form action="{{ route('configuracion.update') }}" method="POST" class="space-y-3">
                    @csrf @method('PUT')
                    <div>
                        <x-input-label for="finnhub_api_key" :value="$tieneFinnhub ? 'Nueva clave (deja en blanco para no cambiar)' : 'API Key'" />
                        <x-text-input id="finnhub_api_key" name="finnhub_api_key" type="password" autocomplete="off"
                            class="mt-1 block w-full font-mono" placeholder="xxxxxxxxxxxxxxxxxxxx" />
                        <x-input-error :messages="$errors->get('finnhub_api_key')" class="mt-2" />
                    </div>
                    <div class="flex items-center gap-3">
                        <x-primary-button>Guardar</x-primary-button>
                        @if($tieneFinnhub)
                            <button form="probar-finnhub" class="inline-flex items-center px-4 py-2 bg-gray-100 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-200 transition">
                                Probar conexión
                            </button>
                        @endif
                    </div>
                </form>

                @if($tieneFinnhub)
                    <div class="flex items-center gap-3 pt-2 border-t border-gray-100">
                        <form id="probar-finnhub" action="{{ route('configuracion.finnhub.probar') }}" method="POST">@csrf</form>
                        <form action="{{ route('configuracion.finnhub.destroy') }}" method="POST"
                              onsubmit="return confirm('¿Eliminar la API key de Finnhub?')">
                            @csrf @method('DELETE')
                            <button class="text-sm text-red-500 hover:text-red-700 font-medium">Eliminar clave</button>
                        </form>
                    </div>
                @endif
            </div>

            {{-- Claude (IA Agente Fiduciario) --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-bold text-gray-800">Claude (IA del Agente Fiduciario)</h3>
                    @if($tieneClaude)
                        <span class="px-2 py-0.5 bg-emerald-100 text-emerald-700 rounded-full text-xs font-bold uppercase">Configurada</span>
                    @else
                        <span class="px-2 py-0.5 bg-gray-100 text-gray-500 rounded-full text-xs font-bold uppercase">Sin configurar</span>
                    @endif
                </div>

                <p class="text-sm text-gray-500">
                    Clave de la API de Anthropic para los agentes de análisis de inversión.
                    Consíguela en <a href="https://console.anthropic.com" target="_blank" rel="noopener" class="text-indigo-600 hover:underline">console.anthropic.com</a>.
                </p>

                @if($tieneClaude)
                    <div class="text-sm text-gray-600 font-mono bg-gray-50 rounded px-3 py-2">{{ $claudeKeyMask }}</div>
                @endif

                <form action="{{ route('configuracion.update') }}" method="POST" class="space-y-3">
                    @csrf @method('PUT')
                    <div>
                        <x-input-label for="claude_api_key" :value="$tieneClaude ? 'Nueva clave (deja en blanco para no cambiar)' : 'API Key'" />
                        <x-text-input id="claude_api_key" name="claude_api_key" type="password" autocomplete="off"
                            class="mt-1 block w-full font-mono" placeholder="sk-ant-..." />
                        <x-input-error :messages="$errors->get('claude_api_key')" class="mt-2" />
                    </div>
                    <div class="flex items-center gap-3">
                        <x-primary-button>Guardar</x-primary-button>
                        @if($tieneClaude)
                            <button form="probar-claude" class="inline-flex items-center px-4 py-2 bg-gray-100 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-200 transition">
                                Probar conexión
                            </button>
                        @endif
                    </div>
                </form>

                @if($tieneClaude)
                    <div class="flex items-center gap-3 pt-2 border-t border-gray-100">
                        <form id="probar-claude" action="{{ route('configuracion.claude.probar') }}" method="POST">@csrf</form>
                        <form action="{{ route('configuracion.claude.destroy') }}" method="POST"
                              onsubmit="return confirm('¿Eliminar la API key de Claude?')">
                            @csrf @method('DELETE')
                            <button class="text-sm text-red-500 hover:text-red-700 font-medium">Eliminar clave</button>
                        </form>
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
