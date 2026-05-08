<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Activos Financieros</h2>
            <a href="{{ route('inversiones.index') }}"
               class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">← Volver a la Cartera</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-lg text-sm font-medium">
                    {{ session('success') }}
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- Formulario añadir activo --}}
                <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 italic">Registrar Activo</h3>
                    <form action="{{ route('inversiones.activos.store') }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <x-input-label for="ticker" value="Ticker (Ej: SAN.MC, AAPL)" />
                            <x-text-input id="ticker" name="ticker" type="text" class="mt-1 block w-full uppercase"
                                value="{{ old('ticker') }}" required placeholder="SAN.MC" />
                            <x-input-error :messages="$errors->get('ticker')" class="mt-1" />
                            <p class="text-[10px] text-gray-400 mt-1">Bolsa española: añade .MC (Ej: BBVA.MC)</p>
                        </div>
                        <div>
                            <x-input-label for="nombre" value="Nombre de la empresa" />
                            <x-text-input id="nombre" name="nombre" type="text" class="mt-1 block w-full"
                                value="{{ old('nombre') }}" required placeholder="Banco Santander" />
                        </div>
                        <div>
                            <x-input-label for="sector" value="Sector (opcional)" />
                            <x-text-input id="sector" name="sector" type="text" class="mt-1 block w-full"
                                value="{{ old('sector') }}" placeholder="Banca, Tecnología..." />
                        </div>
                        <div>
                            <x-input-label for="mercado" value="Mercado (opcional)" />
                            <x-text-input id="mercado" name="mercado" type="text" class="mt-1 block w-full"
                                value="{{ old('mercado') }}" placeholder="BME, NASDAQ, NYSE..." />
                        </div>
                        <x-primary-button class="w-full justify-center py-3">
                            Añadir Activo
                        </x-primary-button>
                    </form>
                </div>

                {{-- Listado de activos --}}
                <div class="lg:col-span-2 bg-white rounded-lg shadow-sm overflow-hidden border border-gray-100"
                     x-data="{ editando: null, editTicker: '', editNombre: '', editSector: '', editMercado: '' }">

                    @if($activos->isEmpty())
                        <div class="p-12 text-center text-gray-400">
                            <p class="font-bold text-lg">Sin activos registrados</p>
                            <p class="text-sm mt-1">Añade los tickers que quieres seguir en tu cartera.</p>
                        </div>
                    @else
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-gray-50 border-b text-gray-600 text-xs uppercase tracking-widest font-bold">
                                <th class="px-6 py-4">Ticker</th>
                                <th class="px-6 py-4">Nombre</th>
                                <th class="px-6 py-4">Sector</th>
                                <th class="px-6 py-4">Mercado</th>
                                <th class="px-6 py-4 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($activos as $activo)
                            <tr class="hover:bg-gray-50/50">
                                {{-- View row --}}
                                <template x-if="editando !== {{ $activo->id }}">
                                    <td class="px-6 py-4">
                                        <span class="font-black text-[#1e1b4b] tracking-wide">{{ $activo->ticker }}</span>
                                    </td>
                                </template>
                                <template x-if="editando !== {{ $activo->id }}">
                                    <td class="px-6 py-4 text-gray-700 font-medium">{{ $activo->nombre }}</td>
                                </template>
                                <template x-if="editando !== {{ $activo->id }}">
                                    <td class="px-6 py-4">
                                        @if($activo->sector)
                                            <span class="px-2 py-1 bg-indigo-50 text-indigo-700 rounded text-xs font-bold">{{ $activo->sector }}</span>
                                        @else
                                            <span class="text-gray-300 text-xs">—</span>
                                        @endif
                                    </td>
                                </template>
                                <template x-if="editando !== {{ $activo->id }}">
                                    <td class="px-6 py-4 text-xs text-gray-500">{{ $activo->mercado ?? '—' }}</td>
                                </template>
                                <template x-if="editando !== {{ $activo->id }}">
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex items-center justify-center gap-3">
                                            <a href="{{ route('inversiones.activos.show', $activo->id) }}"
                                               class="text-blue-500 hover:text-blue-700 transition" title="Ver detalle">
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                            </a>
                                            <button type="button"
                                                @click="editando = {{ $activo->id }}; editTicker = '{{ $activo->ticker }}'; editNombre = '{{ addslashes($activo->nombre) }}'; editSector = '{{ addslashes($activo->sector ?? '') }}'; editMercado = '{{ addslashes($activo->mercado ?? '') }}'"
                                                class="text-indigo-600 hover:text-indigo-900 transition">
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </button>
                                            <form action="{{ route('inversiones.activos.destroy', $activo->id) }}" method="POST"
                                                  onsubmit="return confirm('¿Eliminar {{ $activo->ticker }}? Se borrarán todas sus operaciones y dividendos.')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-red-500 hover:text-red-700 transition">
                                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </template>

                                {{-- Edit row --}}
                                <template x-if="editando === {{ $activo->id }}">
                                    <td colspan="5" class="px-4 py-3 bg-indigo-50">
                                        <form action="{{ route('inversiones.activos.update', $activo->id) }}" method="POST">
                                            @csrf @method('PATCH')
                                            <div class="flex flex-wrap gap-2 items-end">
                                                <div>
                                                    <label class="text-[10px] font-bold uppercase text-indigo-600">Ticker</label>
                                                    <input type="text" name="ticker" x-model="editTicker"
                                                           class="block border-gray-300 rounded-md shadow-sm text-sm uppercase w-28" required />
                                                </div>
                                                <div>
                                                    <label class="text-[10px] font-bold uppercase text-indigo-600">Nombre</label>
                                                    <input type="text" name="nombre" x-model="editNombre"
                                                           class="block border-gray-300 rounded-md shadow-sm text-sm w-48" required />
                                                </div>
                                                <div>
                                                    <label class="text-[10px] font-bold uppercase text-indigo-600">Sector</label>
                                                    <input type="text" name="sector" x-model="editSector"
                                                           class="block border-gray-300 rounded-md shadow-sm text-sm w-32" />
                                                </div>
                                                <div>
                                                    <label class="text-[10px] font-bold uppercase text-indigo-600">Mercado</label>
                                                    <input type="text" name="mercado" x-model="editMercado"
                                                           class="block border-gray-300 rounded-md shadow-sm text-sm w-24" />
                                                </div>
                                                <div class="flex gap-2">
                                                    <button type="submit"
                                                            class="px-3 py-1.5 bg-[#1e1b4b] text-white rounded text-sm font-bold hover:bg-[#312e81]">
                                                        Guardar
                                                    </button>
                                                    <button type="button" @click="editando = null"
                                                            class="px-3 py-1.5 bg-gray-200 text-gray-700 rounded text-sm font-bold hover:bg-gray-300">
                                                        Cancelar
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </td>
                                </template>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
