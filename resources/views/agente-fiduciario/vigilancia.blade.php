<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Agente Fiduciario · Vigilancia</h2>
            <span class="text-xs text-gray-400">Datos: {{ $proveedor }}</span>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-lg text-sm font-medium">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg text-sm font-medium">{{ session('error') }}</div>
            @endif

            @unless($tieneApiKey)
                <div class="bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3 rounded-lg text-sm">
                    No tienes configurada la API key de datos de mercado.
                    <a href="{{ route('configuracion.index') }}" class="font-bold underline">Configúrala aquí</a> para traer precios y fundamentales.
                </div>
            @endunless

            {{-- Barra de acciones --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4 flex flex-col md:flex-row md:items-end gap-4 justify-between">
                <form action="{{ route('agente-fiduciario.vigilancia.store') }}" method="POST" class="flex items-end gap-3">
                    @csrf
                    <div>
                        <x-input-label for="ticker" value="Añadir ticker" />
                        <x-text-input id="ticker" name="ticker" type="text" class="mt-1 block w-40 uppercase"
                            placeholder="Ej: KO, JNJ, MSFT" required />
                    </div>
                    <x-primary-button>➕ Añadir</x-primary-button>
                </form>

                @if($items->isNotEmpty())
                    <form action="{{ route('agente-fiduciario.vigilancia.refrescar') }}" method="POST">
                        @csrf
                        <button class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-md font-semibold text-sm hover:bg-indigo-700 transition">
                            🔄 Actualizar datos
                        </button>
                    </form>
                @endif
            </div>

            {{-- Tabla --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-x-auto">
                @if($items->isEmpty())
                    <div class="p-12 text-center text-gray-400">
                        <p class="font-bold text-lg">Sin acciones en vigilancia</p>
                        <p class="text-sm mt-1">Añade un ticker para empezar a seguir sus datos.</p>
                    </div>
                @else
                    <table class="w-full text-sm whitespace-nowrap">
                        <thead>
                            <tr class="bg-gray-50 border-b text-gray-500 text-xs uppercase tracking-wider font-bold">
                                <th class="px-4 py-3 text-left">Ticker</th>
                                <th class="px-4 py-3 text-right">Precio</th>
                                <th class="px-4 py-3 text-right" title="Rentabilidad por dividendo">Yield</th>
                                <th class="px-4 py-3 text-right" title="% del beneficio destinado a dividendo">Payout</th>
                                <th class="px-4 py-3 text-right" title="Años consecutivos subiendo dividendo">Racha</th>
                                <th class="px-4 py-3 text-right">PER</th>
                                <th class="px-4 py-3 text-right" title="Margen neto">Margen</th>
                                <th class="px-4 py-3 text-right" title="Crecimiento BPA">Crec. BPA</th>
                                <th class="px-4 py-3 text-right" title="Precio objetivo manual">Objetivo</th>
                                <th class="px-4 py-3 text-center">Señal</th>
                                <th class="px-4 py-3 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($items as $item)
                            <tr class="hover:bg-gray-50/50 {{ $item->en_alerta ? 'bg-emerald-50/40' : '' }}">
                                <td class="px-4 py-3">
                                    <span class="font-bold text-gray-800">{{ $item->ticker }}</span>
                                    @if($item->nombre)
                                        <span class="block text-[11px] text-gray-400 font-normal">{{ \Illuminate\Support\Str::limit($item->nombre, 24) }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right font-mono font-bold text-gray-900">
                                    @if(!is_null($item->precio_actual))
                                        {{ number_format($item->precio_actual, 2, ',', '.') }} {{ $item->moneda }}
                                    @else <span class="text-gray-300">—</span> @endif
                                </td>
                                <td class="px-4 py-3 text-right font-mono {{ $item->yield_pct >= 3 ? 'text-emerald-600 font-bold' : 'text-gray-600' }}">
                                    {{ !is_null($item->yield_pct) ? number_format($item->yield_pct, 2, ',', '.').'%' : '—' }}
                                </td>
                                <td class="px-4 py-3 text-right font-mono {{ $item->payout_ratio > 80 ? 'text-amber-600 font-bold' : 'text-gray-600' }}">
                                    {{ !is_null($item->payout_ratio) ? number_format($item->payout_ratio, 1, ',', '.').'%' : '—' }}
                                </td>
                                <td class="px-4 py-3 text-right font-mono text-gray-600">
                                    {{ !is_null($item->racha_dividendo) ? $item->racha_dividendo.' a' : '—' }}
                                </td>
                                <td class="px-4 py-3 text-right font-mono text-gray-600">
                                    {{ !is_null($item->per_actual) ? number_format($item->per_actual, 1, ',', '.') : '—' }}
                                </td>
                                <td class="px-4 py-3 text-right font-mono text-gray-600">
                                    {{ !is_null($item->margen_neto) ? number_format($item->margen_neto, 1, ',', '.').'%' : '—' }}
                                </td>
                                <td class="px-4 py-3 text-right font-mono {{ $item->crecimiento_bpa < 0 ? 'text-red-600' : 'text-gray-600' }}">
                                    {{ !is_null($item->crecimiento_bpa) ? number_format($item->crecimiento_bpa, 1, ',', '.').'%' : '—' }}
                                </td>
                                <td class="px-4 py-3 text-right font-mono text-gray-600">
                                    @if(!is_null($item->precio_objetivo_manual))
                                        {{ number_format($item->precio_objetivo_manual, 2, ',', '.') }}
                                        @if(!is_null($item->descuento_objetivo))
                                            <span class="block text-[10px] {{ $item->descuento_objetivo >= 0 ? 'text-emerald-600' : 'text-red-500' }}">
                                                {{ $item->descuento_objetivo >= 0 ? '▼' : '▲' }} {{ number_format(abs($item->descuento_objetivo), 1, ',', '.') }}%
                                            </span>
                                        @endif
                                    @else <span class="text-gray-300">—</span> @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($item->en_alerta)
                                        <span class="px-2 py-0.5 bg-emerald-100 text-emerald-700 rounded-full text-[10px] font-black uppercase">Compra</span>
                                    @elseif(!is_null($item->precio_objetivo_manual) && !is_null($item->precio_actual) && $item->precio_actual <= $item->precio_objetivo_manual)
                                        <span class="px-2 py-0.5 bg-indigo-100 text-indigo-700 rounded-full text-[10px] font-black uppercase">En objetivo</span>
                                    @else
                                        <span class="text-gray-300 text-xs">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <button onclick='abrirEditar(@json($item->id), @json($item->ticker), @json($item->precio_objetivo_manual), @json($item->precio_alerta), @json($item->notas))'
                                                class="text-indigo-500 hover:text-indigo-700 p-1" title="Editar objetivo/alerta/notas">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>
                                        <form action="{{ route('agente-fiduciario.vigilancia.destroy', $item->id) }}" method="POST"
                                              onsubmit="return confirm('¿Quitar {{ $item->ticker }} de vigilancia?')">
                                            @csrf @method('DELETE')
                                            <button class="text-red-400 hover:text-red-600 p-1" title="Eliminar">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>

            @if($items->isNotEmpty())
                <p class="text-[11px] text-gray-400">
                    Última actualización: {{ optional($items->max('actualizado_en'))?->diffForHumans() ?? 'nunca' }}.
                    Análisis informativo, no asesoramiento financiero.
                </p>
            @endif

        </div>
    </div>

    {{-- Modal editar manual --}}
    <div id="modal-editar" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/40">
        <div class="bg-white rounded-xl shadow-xl p-6 w-96">
            <h3 class="text-base font-bold text-gray-800 mb-4">Editar <span id="modal-ticker"></span></h3>
            <form id="form-editar" method="POST">
                @csrf @method('PUT')
                <div class="space-y-4">
                    <div>
                        <x-input-label for="precio_objetivo_manual" value="Precio objetivo (compra ideal)" />
                        <x-text-input id="precio_objetivo_manual" name="precio_objetivo_manual" type="number" step="0.01" min="0" class="mt-1 block w-full" />
                    </div>
                    <div>
                        <x-input-label for="precio_alerta" value="Precio de alerta (aviso al llegar)" />
                        <x-text-input id="precio_alerta" name="precio_alerta" type="number" step="0.01" min="0" class="mt-1 block w-full" />
                    </div>
                    <div>
                        <x-input-label for="notas" value="Notas" />
                        <textarea id="notas" name="notas" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"></textarea>
                    </div>
                </div>
                <div class="flex gap-2 mt-5">
                    <button type="button" onclick="cerrarEditar()" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold px-4 py-2 rounded-lg text-sm">Cancelar</button>
                    <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-4 py-2 rounded-lg text-sm">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function abrirEditar(id, ticker, objetivo, alerta, notas) {
            document.getElementById('form-editar').action = '/agente-fiduciario/vigilancia/' + id;
            document.getElementById('modal-ticker').textContent = ticker;
            document.getElementById('precio_objetivo_manual').value = objetivo ?? '';
            document.getElementById('precio_alerta').value = alerta ?? '';
            document.getElementById('notas').value = notas ?? '';
            document.getElementById('modal-editar').classList.remove('hidden');
        }
        function cerrarEditar() {
            document.getElementById('modal-editar').classList.add('hidden');
        }
        document.getElementById('modal-editar').addEventListener('click', function (e) {
            if (e.target === this) cerrarEditar();
        });
    </script>
</x-app-layout>
