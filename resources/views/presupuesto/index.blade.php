<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Presupuesto Mensual</h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-lg text-sm font-medium">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Navegación de mes --}}
            <div class="flex items-center justify-between bg-white rounded-lg shadow-sm border border-gray-100 px-6 py-3">
                <a href="{{ route('presupuesto.index', ['mes' => $fechaAnterior->month, 'anio' => $fechaAnterior->year]) }}"
                   class="flex items-center gap-1 text-indigo-600 hover:text-indigo-800 font-medium text-sm transition">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    {{ ucfirst($fechaAnterior->isoFormat('MMMM YYYY')) }}
                </a>

                <h3 class="text-base font-bold text-[#1e1b4b]">
                    {{ ucfirst($fechaConsulta->isoFormat('MMMM YYYY')) }}
                </h3>

                <a href="{{ route('presupuesto.index', ['mes' => $fechaSiguiente->month, 'anio' => $fechaSiguiente->year]) }}"
                   class="flex items-center gap-1 text-indigo-600 hover:text-indigo-800 font-medium text-sm transition">
                    {{ ucfirst($fechaSiguiente->isoFormat('MMMM YYYY')) }}
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>

            {{-- KPIs resumen --}}
            <div class="grid grid-cols-3 gap-4">
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-5 text-center">
                    <div class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Presupuesto Total</div>
                    <div class="text-2xl font-black text-[#1e1b4b]">{{ number_format($totalPresupuesto, 2, ',', '.') }}€</div>
                </div>
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-5 text-center">
                    <div class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Gastado</div>
                    <div class="text-2xl font-black {{ $totalGastado > $totalPresupuesto ? 'text-red-600' : 'text-gray-800' }}">
                        {{ number_format($totalGastado, 2, ',', '.') }}€
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-5 text-center">
                    <div class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Restante</div>
                    <div class="text-2xl font-black {{ $totalRestante >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                        {{ number_format($totalRestante, 2, ',', '.') }}€
                    </div>
                </div>
            </div>

            {{-- Barra de progreso global --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 px-6 py-4">
                @php
                    $pctGlobal = min($totalPct, 100);
                    $colorGlobal = $totalPct >= 100 ? 'bg-red-500' : ($totalPct >= 75 ? 'bg-amber-400' : 'bg-emerald-500');
                @endphp
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Progreso global del mes</span>
                    <span class="text-sm font-bold {{ $totalPct >= 100 ? 'text-red-600' : ($totalPct >= 75 ? 'text-amber-600' : 'text-emerald-600') }}">
                        {{ number_format($totalPct, 1) }}%
                    </span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-3">
                    <div class="{{ $colorGlobal }} h-3 rounded-full transition-all duration-500"
                         style="width: {{ $pctGlobal }}%"></div>
                </div>
            </div>

            {{-- Tabla de categorías --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
                @if($lineas->isEmpty())
                    <div class="p-12 text-center text-gray-400">
                        <svg class="h-12 w-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 11h.01M12 11h.01M15 11h.01M4 19h16a2 2 0 002-2V7a2 2 0 00-2-2H4a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <p class="font-medium text-base">No hay categorías en este mes</p>
                        <p class="text-sm mt-1">Añade categorías o copia del mes anterior.</p>
                    </div>
                @else
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 border-b text-gray-500 text-xs uppercase tracking-widest font-bold">
                                <th class="px-6 py-3 text-left">Categoría</th>
                                <th class="px-6 py-3 text-right">Presupuesto</th>
                                <th class="px-6 py-3 text-right">Gastado</th>
                                <th class="px-6 py-3 text-right">Restante</th>
                                <th class="px-6 py-3 w-40">Progreso</th>
                                <th class="px-6 py-3 text-center">Estado</th>
                                <th class="px-6 py-3 text-center w-20">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($lineas as $linea)
                            @php
                                $excedido   = $linea['pctReal'] > 100;
                                $completo   = round($linea['pctReal'], 1) == 100;
                                $colorBarra = $excedido ? 'bg-red-500' : ($linea['pctReal'] >= 75 ? 'bg-amber-400' : 'bg-emerald-500');
                            @endphp
                            <tr class="hover:bg-gray-50/50" id="row-{{ $linea['id'] }}">
                                <td class="px-6 py-3">
                                    <div class="flex items-center gap-2">
                                        <span class="inline-block w-2.5 h-2.5 rounded-full shrink-0"
                                              style="background-color: {{ $linea['color'] }}"></span>
                                        <span class="font-semibold text-gray-800">{{ $linea['nombre'] }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-3 text-right font-mono text-gray-600" id="presupuesto-{{ $linea['id'] }}">
                                    {{ number_format($linea['presupuesto'], 2, ',', '.') }}€
                                </td>
                                <td class="px-6 py-3 text-right font-mono font-bold text-gray-900">
                                    {{ number_format($linea['gastado'], 2, ',', '.') }}€
                                </td>
                                <td class="px-6 py-3 text-right font-mono font-bold {{ $linea['restante'] >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                                    {{ $linea['restante'] >= 0 ? '' : '-' }}{{ number_format(abs($linea['restante']), 2, ',', '.') }}€
                                </td>
                                <td class="px-6 py-3">
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 bg-gray-100 rounded-full h-2">
                                            <div class="{{ $colorBarra }} h-2 rounded-full transition-all duration-500"
                                                 style="width: {{ $linea['pctBarra'] }}%"></div>
                                        </div>
                                        <span class="text-xs font-bold w-10 text-right {{ $excedido ? 'text-red-600' : ($linea['pctReal'] >= 75 ? 'text-amber-600' : 'text-emerald-600') }}">
                                            {{ number_format($linea['pctReal'], 1) }}%
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-3 text-center">
                                    @if($excedido)
                                        <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-red-100 text-red-700">Excedido</span>
                                    @elseif($completo)
                                        <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700">Completo</span>
                                    @elseif($linea['pctReal'] >= 75)
                                        <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-amber-100 text-amber-700">Alerta</span>
                                    @else
                                        <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-gray-100 text-gray-500">OK</span>
                                    @endif
                                </td>
                                <td class="px-6 py-3 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        {{-- Editar --}}
                                        <button onclick="abrirEditar({{ $linea['id'] }}, {{ $linea['presupuesto'] }})"
                                                class="text-indigo-500 hover:text-indigo-700 p-1 rounded hover:bg-indigo-50 transition" title="Editar importe">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </button>
                                        {{-- Eliminar --}}
                                        <form method="POST" action="{{ route('presupuesto.destroy', $linea['id']) }}"
                                              onsubmit="return confirm('¿Eliminar {{ $linea['nombre'] }} del presupuesto de este mes?')">
                                            @csrf @method('DELETE')
                                            <input type="hidden" name="mes" value="{{ $mes }}">
                                            <input type="hidden" name="anio" value="{{ $anio }}">
                                            <button type="submit" class="text-red-400 hover:text-red-600 p-1 rounded hover:bg-red-50 transition" title="Eliminar">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
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

            {{-- Acciones: añadir + copiar mes anterior --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                {{-- Añadir categoría --}}
                @if($categoriasDisponibles->count() > 0)
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-5">
                    <h4 class="text-sm font-bold text-gray-700 mb-3">Añadir categoría a este mes</h4>
                    <form method="POST" action="{{ route('presupuesto.store') }}" class="flex items-end gap-3">
                        @csrf
                        <input type="hidden" name="mes" value="{{ $mes }}">
                        <input type="hidden" name="anio" value="{{ $anio }}">
                        <div class="flex-1">
                            <label class="text-xs text-gray-500 font-medium block mb-1">Categoría</label>
                            <select name="categoria_id" required
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                                <option value="">Selecciona...</option>
                                @foreach($categoriasDisponibles as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="w-32">
                            <label class="text-xs text-gray-500 font-medium block mb-1">Importe (€)</label>
                            <input type="number" name="importe" step="0.01" min="0.01" required placeholder="0.00"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                        </div>
                        <button type="submit"
                                class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-4 py-2 rounded-lg text-sm transition shrink-0">
                            Añadir
                        </button>
                    </form>
                </div>
                @else
                <div class="bg-gray-50 rounded-lg border border-dashed border-gray-200 p-5 flex items-center justify-center text-gray-400 text-sm">
                    Todas las categorías ya están en este mes.
                </div>
                @endif

                {{-- Copiar mes anterior --}}
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-5">
                    <h4 class="text-sm font-bold text-gray-700 mb-1">Copiar del mes anterior</h4>
                    <p class="text-xs text-gray-400 mb-3">Copia las categorías de {{ ucfirst($fechaAnterior->isoFormat('MMMM YYYY')) }} que aún no estén en este mes.</p>
                    <form method="POST" action="{{ route('presupuesto.copiar') }}">
                        @csrf
                        <input type="hidden" name="mes" value="{{ $mes }}">
                        <input type="hidden" name="anio" value="{{ $anio }}">
                        <button type="submit"
                                class="w-full bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold px-4 py-2 rounded-lg text-sm transition">
                            Copiar de {{ ucfirst($fechaAnterior->isoFormat('MMMM')) }}
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>

    {{-- Modal editar importe --}}
    <div id="modal-editar" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/40">
        <div class="bg-white rounded-xl shadow-xl p-6 w-80">
            <h3 class="text-base font-bold text-gray-800 mb-4">Editar presupuesto</h3>
            <form id="form-editar" method="POST">
                @csrf @method('PUT')
                <input type="hidden" name="mes" value="{{ $mes }}">
                <input type="hidden" name="anio" value="{{ $anio }}">
                <div class="mb-4">
                    <label class="text-xs text-gray-500 font-medium block mb-1">Nuevo importe (€)</label>
                    <input id="input-importe-editar" type="number" name="importe" step="0.01" min="0.01" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                </div>
                <div class="flex gap-2">
                    <button type="button" onclick="cerrarEditar()"
                            class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold px-4 py-2 rounded-lg text-sm transition">
                        Cancelar
                    </button>
                    <button type="submit"
                            class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-4 py-2 rounded-lg text-sm transition">
                        Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function abrirEditar(id, importeActual) {
            document.getElementById('form-editar').action = '/presupuesto/' + id;
            document.getElementById('input-importe-editar').value = importeActual;
            document.getElementById('modal-editar').classList.remove('hidden');
            document.getElementById('input-importe-editar').focus();
        }
        function cerrarEditar() {
            document.getElementById('modal-editar').classList.add('hidden');
        }
        document.getElementById('modal-editar').addEventListener('click', function(e) {
            if (e.target === this) cerrarEditar();
        });
    </script>
</x-app-layout>
