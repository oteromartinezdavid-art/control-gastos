<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Mis Financiaciones') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-lg text-sm font-medium">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Tarjetas de resumen --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white p-6 rounded-lg shadow-sm border-b-4 border-gray-400">
                    <p class="text-sm font-bold text-gray-500 uppercase">Deuda Total Restante</p>
                    <p class="text-3xl font-black text-gray-800">{{ number_format($totalDeuda, 2, ',', '.') }}€</p>
                    <p class="text-xs text-gray-400 mt-1">suma de todas las financiaciones activas</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-sm border-b-4 border-red-400">
                    <p class="text-sm font-bold text-red-600 uppercase">Cuota Mensual Total</p>
                    <p class="text-3xl font-black text-red-700">{{ number_format($totalCuotaMensual, 2, ',', '.') }}€</p>
                    <p class="text-xs text-gray-400 mt-1">compromiso fijo este mes</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-sm border-b-4 border-{{ $pagadasEsteMes === $totalFinanciaciones && $totalFinanciaciones > 0 ? 'emerald' : 'orange' }}-500">
                    <p class="text-sm font-bold text-{{ $pagadasEsteMes === $totalFinanciaciones && $totalFinanciaciones > 0 ? 'emerald' : 'orange' }}-600 uppercase">Cuotas Este Mes</p>
                    <p class="text-3xl font-black text-{{ $pagadasEsteMes === $totalFinanciaciones && $totalFinanciaciones > 0 ? 'emerald' : 'orange' }}-700">
                        {{ $pagadasEsteMes }} / {{ $totalFinanciaciones }}
                    </p>
                    <p class="text-xs text-gray-400 mt-1">detectadas en el extracto bancario</p>
                </div>
            </div>

            {{-- Barra de progreso mensual --}}
            @if($totalFinanciaciones > 0)
            <div class="bg-white p-4 rounded-lg shadow-sm">
                @php $pct = round(($pagadasEsteMes / $totalFinanciaciones) * 100); @endphp
                <div class="flex justify-between mb-2">
                    <span class="text-xs font-bold uppercase text-gray-600">Cuotas cobradas este mes</span>
                    <span class="text-xs font-bold text-gray-600">{{ $pct }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-4">
                    <div class="bg-emerald-500 h-4 rounded-full transition-all duration-500" style="width: {{ $pct }}%"></div>
                </div>
            </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- Formulario nueva financiación --}}
                <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 italic">Registrar Financiación</h3>
                    <form action="{{ route('financiaciones.store') }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <x-input-label for="nombre" value="Nombre" />
                            <x-text-input id="nombre" name="nombre" type="text" class="mt-1 block w-full"
                                value="{{ old('nombre') }}" required
                                placeholder="Ej: Coche, TV Samsung..." />
                            <p class="text-[10px] text-gray-400 mt-1">Debe coincidir con la descripción del gasto en el extracto</p>
                        </div>
                        <div>
                            <x-input-label for="entidad" value="Entidad (opcional)" />
                            <x-text-input id="entidad" name="entidad" type="text" class="mt-1 block w-full"
                                value="{{ old('entidad') }}" placeholder="Ej: CaixaBank, Cetelem..." />
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="cuota_mensual" value="Cuota (€)" />
                                <x-text-input id="cuota_mensual" name="cuota_mensual" type="number" step="0.01"
                                    class="mt-1 block w-full" value="{{ old('cuota_mensual') }}" required />
                            </div>
                            <div>
                                <x-input-label for="cuotas_pendientes" value="Cuotas Pendientes" />
                                <x-text-input id="cuotas_pendientes" name="cuotas_pendientes" type="number"
                                    class="mt-1 block w-full" value="{{ old('cuotas_pendientes') }}" required min="1" />
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="dia_cobro" value="Día de Cobro" />
                                <x-text-input id="dia_cobro" name="dia_cobro" type="number" min="1" max="31"
                                    class="mt-1 block w-full" value="{{ old('dia_cobro') }}" required />
                            </div>
                            <div>
                                <x-input-label for="categoria_gasto_id" value="Categoría" />
                                <select name="categoria_gasto_id"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                    @foreach($categorias as $cat)
                                        <option value="{{ $cat->id }}" {{ old('categoria_gasto_id') == $cat->id ? 'selected' : '' }}>
                                            {{ $cat->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <x-primary-button class="w-full justify-center py-3">
                            Añadir Financiación
                        </x-primary-button>
                    </form>
                </div>

                {{-- Tabla de financiaciones --}}
                <div class="lg:col-span-2 bg-white rounded-lg shadow-sm overflow-hidden border border-gray-100">
                    @if($financiaciones->isEmpty())
                        <div class="p-12 text-center text-gray-400">
                            <p class="font-bold text-lg">Sin financiaciones registradas</p>
                            <p class="text-sm mt-1">Añade préstamos, aplazamientos o cualquier pago a plazos.</p>
                        </div>
                    @else
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-gray-50 border-b text-gray-600 text-xs uppercase tracking-widest font-bold">
                                <th class="px-4 py-4 text-center">Día</th>
                                <th class="px-4 py-4">Concepto</th>
                                <th class="px-4 py-4 text-right">Cuota</th>
                                <th class="px-4 py-4 text-center">Cuotas</th>
                                <th class="px-4 py-4 text-right">Deuda Restante</th>
                                <th class="px-4 py-4 text-center">Este Mes</th>
                                <th class="px-4 py-4 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($financiaciones as $item)
                            <tr class="{{ $item->pagado_este_mes ? 'bg-emerald-50/30' : '' }}">
                                <td class="px-4 py-4 text-center font-bold text-gray-400 text-sm">
                                    {{ $item->dia_cobro }}
                                </td>
                                <td class="px-4 py-4">
                                    <span class="font-bold text-gray-800 block">{{ $item->nombre }}</span>
                                    @if($item->entidad)
                                        <span class="text-xs text-gray-400">{{ $item->entidad }}</span>
                                    @endif
                                    @if($item->pagado_este_mes)
                                        <span class="text-[10px] text-emerald-600 font-bold uppercase italic block">
                                            Cobrado el {{ \Carbon\Carbon::parse($item->fecha_pago_real)->format('d/m') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-right">
                                    <span class="font-bold text-gray-800">{{ number_format($item->cuota_mensual, 2, ',', '.') }}€</span>
                                </td>
                                <td class="px-4 py-4 text-center min-w-[110px]">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-[10px] text-gray-400">{{ $item->cuotas_pagadas }}/{{ $item->cuotas_total }}</span>
                                        @if($item->cuotas_pendientes <= 3)
                                            <span class="px-1.5 py-0.5 bg-amber-100 text-amber-700 rounded text-[10px] font-black">
                                                {{ $item->cuotas_pendientes }} restantes
                                            </span>
                                        @else
                                            <span class="font-bold text-gray-700 text-xs">{{ $item->cuotas_pendientes }} rest.</span>
                                        @endif
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="h-2 rounded-full transition-all duration-500
                                            {{ $item->porcentaje >= 80 ? 'bg-emerald-500' : ($item->porcentaje >= 40 ? 'bg-indigo-500' : 'bg-blue-400') }}"
                                            style="width: {{ $item->porcentaje }}%">
                                        </div>
                                    </div>
                                    <span class="text-[10px] text-gray-400">{{ $item->porcentaje }}% pagado</span>
                                </td>
                                <td class="px-4 py-4 text-right">
                                    <span class="font-black text-gray-900">{{ number_format($item->monto_pendiente, 2, ',', '.') }}€</span>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    @if($item->pagado_este_mes)
                                        <div class="flex justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </div>
                                    @else
                                        <span class="px-2 py-1 bg-orange-100 text-orange-700 rounded-full text-[10px] font-black uppercase">
                                            Pendiente
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <div class="flex items-center justify-center gap-3">
                                        <a href="{{ route('financiaciones.edit', $item->id) }}"
                                           class="text-indigo-600 hover:text-indigo-900 transition" title="Editar / Registrar amortización">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>
                                        <form action="{{ route('financiaciones.destroy', $item->id) }}" method="POST"
                                              onsubmit="return confirm('¿Eliminar la financiación «{{ $item->nombre }}»?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:text-red-700 transition" title="Eliminar">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
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

            </div>
        </div>
    </div>
</x-app-layout>
