<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Control de Gastos Fijos') }}
        </h2>

        {{-- Navegación de Meses --}}
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mt-6">
            <div class="flex items-center justify-between bg-white p-4 shadow-sm sm:rounded-lg border-l-4 border-indigo-500">
                <a href="{{ route('gastos-fijos.index', ['mes' => $fechaObjeto->copy()->subMonth()->month, 'anio' => $fechaObjeto->copy()->subMonth()->year]) }}" 
                   class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg text-gray-700 font-bold transition">
                    ← {{ ucfirst($fechaObjeto->copy()->subMonth()->translatedFormat('F')) }}
                </a>
                
                <h3 class="text-lg font-black text-gray-800 uppercase tracking-widest">
                    {{ $fechaObjeto->translatedFormat('F Y') }}
                </h3>

                <a href="{{ route('gastos-fijos.index', ['mes' => $fechaObjeto->copy()->addMonth()->month, 'anio' => $fechaObjeto->copy()->addMonth()->year]) }}" 
                   class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg text-gray-700 font-bold transition">
                    {{ ucfirst($fechaObjeto->copy()->addMonth()->translatedFormat('F')) }} →
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Resumen de Gastos Fijos --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white p-6 rounded-lg shadow-sm border-b-4 border-gray-400">
                    <p class="text-sm font-bold text-gray-500 uppercase">Total Comprometido</p>
                    <p class="text-3xl font-black text-gray-800">{{ number_format($totalPrevisto, 2, ',', '.') }}€</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-sm border-b-4 border-emerald-500">
                    <p class="text-sm font-bold text-emerald-600 uppercase">Ya Procesado</p>
                    <p class="text-3xl font-black text-emerald-700">{{ number_format($totalPagado, 2, ',', '.') }}€</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-sm border-b-4 border-orange-500">
                    <p class="text-sm font-bold text-orange-600 uppercase">Pendiente de Cobro</p>
                    <p class="text-3xl font-black text-orange-700">{{ number_format($pendienteCobro, 2, ',', '.') }}€</p>
                </div>
            </div>

            {{-- Barra de Progreso Mensual --}}
            <div class="bg-white p-4 rounded-lg shadow-sm">
                @php 
                    $porcentaje = $totalPrevisto > 0 ? ($totalPagado / $totalPrevisto) * 100 : 0;
                @endphp
                <div class="flex justify-between mb-2">
                    <span class="text-xs font-bold uppercase text-gray-600">Progreso de pagos mensuales</span>
                    <span class="text-xs font-bold text-gray-600">{{ round($porcentaje) }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-4">
                    <div class="bg-emerald-500 h-4 rounded-full transition-all duration-500" style="width: {{ $porcentaje }}%"></div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Formulario Nuevo Gasto Fijo --}}
                <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 italic">Configurar Gasto Fijo</h3>
                    @php $oldMeses = array_map('intval', old('meses_cobro', [])); @endphp
                    <form action="{{ route('gastos-fijos.store') }}" method="POST" class="space-y-4"
                          x-data="{ especifico: {{ !empty($oldMeses) ? 'true' : 'false' }} }">
                        @csrf
                        <div>
                            <x-input-label for="nombre" value="Nombre (Ej: Netflix, Seguro)" />
                            <x-text-input id="nombre" name="nombre" type="text" class="mt-1 block w-full" required
                                placeholder="Debe coincidir con la descripción del gasto real" />
                        </div>
                        <div>
                            <x-input-label for="monto_previsto" value="Monto por Cobro (€)" />
                            <x-text-input id="monto_previsto" name="monto_previsto" type="number" step="0.01"
                                class="mt-1 block w-full" required />
                        </div>

                        {{-- Frecuencia de cobro --}}
                        <div>
                            <x-input-label value="Frecuencia de Cobro" />
                            <div class="mt-2 flex gap-4 text-sm">
                                <label class="flex items-center gap-1.5 cursor-pointer font-medium"
                                       :class="!especifico ? 'text-indigo-700' : 'text-gray-500'">
                                    <input type="radio" name="_frecuencia" value="mensual"
                                           x-model="especifico" :value="false"
                                           class="text-indigo-600">
                                    Mensual
                                </label>
                                <label class="flex items-center gap-1.5 cursor-pointer font-medium"
                                       :class="especifico ? 'text-indigo-700' : 'text-gray-500'">
                                    <input type="radio" name="_frecuencia" value="especifico"
                                           x-model="especifico" :value="true"
                                           class="text-indigo-600">
                                    Meses concretos
                                </label>
                            </div>

                            <div x-show="especifico" x-cloak class="mt-3 p-3 bg-indigo-50 rounded-lg">
                                <p class="text-[10px] font-bold uppercase text-indigo-500 mb-2">Selecciona los meses de cobro</p>
                                <div class="grid grid-cols-4 gap-1.5">
                                    @foreach(['1'=>'Ene','2'=>'Feb','3'=>'Mar','4'=>'Abr','5'=>'May','6'=>'Jun','7'=>'Jul','8'=>'Ago','9'=>'Sep','10'=>'Oct','11'=>'Nov','12'=>'Dic'] as $num => $abr)
                                    <label class="flex items-center gap-1 cursor-pointer text-xs font-semibold text-gray-700 hover:text-indigo-700">
                                        <input type="checkbox" name="meses_cobro[]" value="{{ $num }}"
                                               {{ in_array((int)$num, $oldMeses) ? 'checked' : '' }}
                                               class="rounded border-gray-300 text-indigo-600 h-3.5 w-3.5">
                                        {{ $abr }}
                                    </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="dia_pago" value="Día de Cobro" />
                                <x-text-input id="dia_pago" name="dia_pago" type="number" min="1" max="31"
                                    class="mt-1 block w-full" required />
                            </div>
                            <div>
                                <x-input-label for="categoria_gasto_id" value="Categoría" />
                                <select name="categoria_gasto_id"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                    @foreach($categorias as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="fecha_inicio" value="Fecha Inicio" />
                                <x-text-input id="fecha_inicio" name="fecha_inicio" type="date"
                                    class="mt-1 block w-full"
                                    value="{{ old('fecha_inicio', now()->format('Y-m-d')) }}" required />
                                <p class="text-xs text-gray-400 mt-1">Desde cuándo pagas este gasto</p>
                            </div>
                            <div>
                                <x-input-label for="fecha_fin" value="Fecha Fin (opcional)" />
                                <x-text-input id="fecha_fin" name="fecha_fin" type="date"
                                    class="mt-1 block w-full"
                                    value="{{ old('fecha_fin') }}" />
                                <p class="text-xs text-gray-400 mt-1">Dejar vacío si sigue activo</p>
                            </div>
                        </div>
                        <x-primary-button class="w-full justify-center py-3">
                            Añadir a la lista
                        </x-primary-button>
                    </form>
                </div>

                {{-- Listado de Seguimiento --}}
                <div class="lg:col-span-2 bg-white rounded-lg shadow-sm overflow-hidden border border-gray-100">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-gray-50 border-b text-gray-600 text-xs uppercase tracking-widest font-bold">
                                <th class="px-6 py-4 text-center">Día</th>
                                <th class="px-6 py-4">Concepto</th>
                                <th class="px-6 py-4">Categoría</th>
                                <th class="px-6 py-4 text-right">Monto</th>
                                <th class="px-6 py-4 text-center">Estado</th>
                                <th class="px-6 py-4 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($listadoFinal as $item)
                            <tr class="{{ $item->pagado ? 'bg-emerald-50/30' : '' }}">
                                <td class="px-6 py-4 text-center font-bold text-gray-400">
                                    {{ $item->dia_pago }}
                                </td>
                                <td class="px-6 py-4">
                                    <span class="font-bold text-gray-800 block">{{ $item->nombre }}</span>
                                    @if($item->etiqueta_meses)
                                        <span class="text-[10px] text-indigo-500 font-bold uppercase">{{ $item->etiqueta_meses }}</span>
                                    @endif
                                    @if($item->pagado)
                                        <span class="text-[10px] text-emerald-600 font-bold uppercase italic block">
                                            Cobrado el {{ \Carbon\Carbon::parse($item->fecha_pago_real)->format('d/m') }}
                                        </span>
                                    @endif
                                    @if($item->dado_de_baja)
                                        <span class="text-[10px] text-red-500 font-bold uppercase block">
                                            Baja: {{ $item->fecha_fin->format('d/m/Y') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 bg-gray-100 text-gray-600 rounded text-xs">
                                        {{ $item->categoria->nombre ?? 'Sin Categoria' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <span class="font-bold {{ $item->pagado ? 'text-gray-400 line-through' : 'text-gray-900' }}">
                                        {{ number_format($item->monto_previsto, 2, ',', '.') }}€
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($item->pagado)
                                        <div class="flex justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </div>
                                    @else
                                        <span class="px-3 py-1 bg-orange-100 text-orange-700 rounded-full text-[10px] font-black uppercase">
                                            Pendiente
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-3">
                                        <a href="{{ route('gastos-fijos.edit', $item->id) }}"
                                           class="text-indigo-600 hover:text-indigo-900 transition" title="Editar">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>
                                        @if(!$item->dado_de_baja)
                                        <form action="{{ route('gastos-fijos.dar-de-baja', $item->id) }}" method="POST"
                                              onsubmit="return confirm('¿Dar de baja «{{ $item->nombre }}»? El histórico se conservará.')">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="text-orange-500 hover:text-orange-700 transition" title="Dar de baja (conserva histórico)">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                                </svg>
                                            </button>
                                        </form>
                                        @endif
                                        <form action="{{ route('gastos-fijos.destroy', $item->id) }}" method="POST"
                                              onsubmit="return confirm('¿Eliminar DEFINITIVAMENTE «{{ $item->nombre }}»? Se borrará todo el histórico.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:text-red-700 transition" title="Eliminar definitivamente">
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
                </div>
            </div>
        </div>
    </div>
</x-app-layout>