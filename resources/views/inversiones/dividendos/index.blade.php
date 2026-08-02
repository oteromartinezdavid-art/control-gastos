<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Módulo de Dividendos</h2>
            <a href="#" onclick="history.back(); return false;"
               class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">← Volver</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-lg text-sm font-medium">
                    {{ session('success') }}
                </div>
            @endif

            {{-- KPIs --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white p-5 rounded-lg shadow-sm border-b-4 border-gray-400">
                    <p class="text-xs font-bold text-gray-500 uppercase">Total Bruto</p>
                    <p class="text-2xl font-black text-gray-900">{{ number_format($totalBruto, 2, ',', '.') }}€</p>
                    <p class="text-xs text-gray-400 mt-1">antes de retenciones fiscales</p>
                </div>
                <div class="bg-white p-5 rounded-lg shadow-sm border-b-4 border-orange-400">
                    <p class="text-xs font-bold text-orange-600 uppercase">Retenciones</p>
                    <p class="text-2xl font-black text-orange-700">{{ number_format($totalRet, 2, ',', '.') }}€</p>
                    <p class="text-xs text-gray-400 mt-1">impuesto retenido en origen</p>
                </div>
                <div class="bg-white p-5 rounded-lg shadow-sm border-b-4 border-teal-500">
                    <p class="text-xs font-bold text-teal-600 uppercase">Total Neto Cobrado</p>
                    <p class="text-2xl font-black text-teal-700">{{ number_format($totalNeto, 2, ',', '.') }}€</p>
                    <p class="text-xs text-gray-400 mt-1">renta pasiva real recibida</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- Formulario --}}
                <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100"
                     x-data="{ bruto: '', retencion: 0, neto: '' }"
                     x-init="$watch('bruto', v => { neto = (parseFloat(v)||0) - (parseFloat(retencion)||0) > 0 ? ((parseFloat(v)||0) - (parseFloat(retencion)||0)).toFixed(2) : neto });
                              $watch('retencion', v => { neto = (parseFloat(bruto)||0) - (parseFloat(v)||0) > 0 ? ((parseFloat(bruto)||0) - (parseFloat(v)||0)).toFixed(2) : neto })">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 italic">Registrar Dividendo</h3>
                    <form action="{{ route('inversiones.dividendos.store') }}" method="POST" class="space-y-4">
                        @csrf

                        <div>
                            <x-input-label for="activo_id" value="Activo" />
                            <select name="activo_id" id="activo_id"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                required>
                                <option value="">— Selecciona —</option>
                                @foreach($activos as $activo)
                                    <option value="{{ $activo->id }}" {{ old('activo_id', $request->activo_id) == $activo->id ? 'selected' : '' }}>
                                        {{ $activo->ticker }} – {{ $activo->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <x-input-label for="fecha" value="Fecha de cobro" />
                            <x-text-input id="fecha" name="fecha" type="date" class="mt-1 block w-full"
                                value="{{ old('fecha', date('Y-m-d')) }}" required />
                        </div>

                        <div>
                            <x-input-label for="monto_bruto" value="Monto Bruto (€)" />
                            <x-text-input id="monto_bruto" name="monto_bruto" type="number" step="0.01" min="0"
                                class="mt-1 block w-full" x-model="bruto"
                                value="{{ old('monto_bruto') }}" required />
                        </div>

                        <div>
                            <x-input-label for="retencion" value="Retención Fiscal (€)" />
                            <x-text-input id="retencion" name="retencion" type="number" step="0.01" min="0"
                                class="mt-1 block w-full" x-model="retencion"
                                value="{{ old('retencion', 0) }}" />
                            <p class="text-[10px] text-gray-400 mt-1">España: retención típica 19% sobre dividendo bruto</p>
                        </div>

                        <div>
                            <x-input-label for="monto_neto" value="Monto Neto (€)" />
                            <x-text-input id="monto_neto" name="monto_neto" type="number" step="0.01" min="0"
                                class="mt-1 block w-full" x-model="neto"
                                value="{{ old('monto_neto') }}" required />
                            <p class="text-[10px] text-gray-400 mt-1">Se calcula automáticamente: bruto − retención</p>
                        </div>

                        <div>
                            <x-input-label for="notas" value="Notas (opcional)" />
                            <textarea id="notas" name="notas" rows="2"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">{{ old('notas') }}</textarea>
                        </div>

                        <x-primary-button class="w-full justify-center py-3">
                            Registrar Dividendo
                        </x-primary-button>
                    </form>
                </div>

                {{-- Tabla --}}
                <div class="lg:col-span-2 bg-white rounded-lg shadow-sm overflow-hidden border border-gray-100">

                    {{-- Filtro por activo --}}
                    <form method="GET" action="{{ route('inversiones.dividendos.index') }}"
                          class="p-4 border-b border-gray-100 flex gap-3">
                        <select name="activo_id"
                            class="border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500">
                            <option value="">Todos los activos</option>
                            @foreach($activos as $activo)
                                <option value="{{ $activo->id }}" {{ $request->activo_id == $activo->id ? 'selected' : '' }}>
                                    {{ $activo->ticker }}
                                </option>
                            @endforeach
                        </select>
                        <button type="submit"
                            class="px-3 py-1.5 bg-[#1e1b4b] text-white rounded-md text-sm font-medium">Filtrar</button>
                        @if($request->filled('activo_id'))
                            <a href="{{ route('inversiones.dividendos.index') }}"
                               class="px-3 py-1.5 bg-gray-100 text-gray-600 rounded-md text-sm font-medium">Limpiar</a>
                        @endif
                    </form>

                    @if($dividendos->isEmpty())
                        <div class="p-12 text-center text-gray-400">
                            <p class="font-bold">Sin dividendos registrados</p>
                            <p class="text-sm mt-1">Cuando recibas dividendos o retornos de capital, regístralos aquí.</p>
                        </div>
                    @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead>
                                <tr class="bg-gray-50 border-b text-gray-600 text-xs uppercase tracking-widest font-bold">
                                    <th class="px-4 py-3">Fecha</th>
                                    <th class="px-4 py-3">Activo</th>
                                    <th class="px-4 py-3 text-right">Bruto</th>
                                    <th class="px-4 py-3 text-right">Retención</th>
                                    <th class="px-4 py-3 text-right">Neto</th>
                                    <th class="px-4 py-3 text-right">% Ret.</th>
                                    <th class="px-4 py-3 text-center">Acc.</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($dividendos as $div)
                                <tr class="hover:bg-gray-50/50">
                                    <td class="px-4 py-3 text-gray-500 text-xs font-mono">
                                        {{ $div->fecha->format('d/m/Y') }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="font-black text-gray-900">{{ $div->activo->ticker }}</span>
                                        @if($div->notas)
                                            <span class="block text-[10px] text-gray-400 truncate max-w-[140px]">{{ $div->notas }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right font-medium text-gray-700">
                                        {{ number_format($div->monto_bruto, 2, ',', '.') }}€
                                    </td>
                                    <td class="px-4 py-3 text-right text-orange-600 font-medium">
                                        {{ number_format($div->retencion, 2, ',', '.') }}€
                                    </td>
                                    <td class="px-4 py-3 text-right font-bold text-teal-700">
                                        {{ number_format($div->monto_neto, 2, ',', '.') }}€
                                    </td>
                                    <td class="px-4 py-3 text-right text-xs text-gray-500">
                                        @if($div->monto_bruto > 0)
                                            {{ number_format(($div->retencion / $div->monto_bruto) * 100, 1, ',', '.') }}%
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <form action="{{ route('inversiones.dividendos.destroy', $div->id) }}" method="POST"
                                              onsubmit="return confirm('¿Eliminar este dividendo?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-400 hover:text-red-700 transition">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
