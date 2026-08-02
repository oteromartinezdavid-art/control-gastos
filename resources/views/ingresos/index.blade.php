<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Mi Control de Ingresos') }}
        </h2>
        
        {{-- Navegación de Meses --}}
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mt-6">
            <div class="flex items-center justify-between bg-white p-4 shadow-sm sm:rounded-lg border-l-4 border-emerald-500">
                <a href="{{ route('ingresos.index', ['mes' => $fechaObjeto->copy()->subMonth()->month, 'anio' => $fechaObjeto->copy()->subMonth()->year]) }}" 
                   class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg text-gray-700 font-bold transition">
                    ← {{ ucfirst($fechaObjeto->copy()->subMonth()->translatedFormat('F')) }}
                </a>
                
                <div class="text-center">
                    <h3 class="text-lg font-black text-gray-800 uppercase tracking-widest">
                        {{ $fechaObjeto->translatedFormat('F Y') }}
                    </h3>
                </div>

                <a href="{{ route('ingresos.index', ['mes' => $fechaObjeto->copy()->addMonth()->month, 'anio' => $fechaObjeto->copy()->addMonth()->year]) }}" 
                   class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg text-gray-700 font-bold transition">
                    {{ ucfirst($fechaObjeto->copy()->addMonth()->translatedFormat('F')) }} →
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            {{-- Panel Superior: Filtros y Gráfico --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                
                {{-- Formulario de Filtros --}}
                <div class="md:col-span-2 bg-white p-6 shadow-sm sm:rounded-lg border border-gray-100">
                    <h3 class="text-lg font-bold text-gray-700 mb-4 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 8.293A1 1 0 013 7.586V4z" />
                        </svg>
                        Filtrar Ingresos
                    </h3>
                    
                    <form method="GET" action="{{ route('ingresos.index') }}" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <input type="hidden" name="mes" value="{{ $mes }}">
                        <input type="hidden" name="anio" value="{{ $anio }}">

                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase">Desde</label>
                            <input type="date" name="fecha_inicio" value="{{ $request->fecha_inicio }}" class="w-full mt-1 rounded-md border-gray-300 shadow-sm focus:ring-emerald-500 focus:border-emerald-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase">Hasta</label>
                            <input type="date" name="fecha_fin" value="{{ $request->fecha_fin }}" class="w-full mt-1 rounded-md border-gray-300 shadow-sm focus:ring-emerald-500 focus:border-emerald-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase">Fuente</label>
                            <select name="fuente_ingreso_id" class="w-full mt-1 rounded-md border-gray-300 shadow-sm focus:ring-emerald-500 focus:border-emerald-500">
                                <option value="">Todas las fuentes</option>
                                @foreach($fuentes as $f)
                                    <option value="{{ $f->id }}" {{ $request->fuente_ingreso_id == $f->id ? 'selected' : '' }}>
                                        {{ $f->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase">Buscador</label>
                            <input type="text" name="descripcion" value="{{ $request->descripcion }}" placeholder="Ej: Nómina..." class="w-full mt-1 rounded-md border-gray-300 shadow-sm focus:ring-emerald-500 focus:border-emerald-500">
                        </div>

                        <div class="sm:col-span-2 flex justify-end space-x-2 mt-2">
                            <a href="{{ route('ingresos.index') }}" class="px-4 py-2 bg-gray-100 text-gray-600 rounded-md hover:bg-gray-200 transition">Limpiar</a>
                            <button type="submit" class="px-6 py-2 bg-emerald-600 text-white rounded-md hover:bg-emerald-700 shadow-sm transition">Aplicar Filtros</button>
                        </div>
                    </form>
                </div>

                {{-- Gráfico de Rosco (Ingresos) --}}
                <div class="bg-white p-6 shadow-sm sm:rounded-lg border border-gray-100 flex flex-col items-center">
                    <h3 class="text-sm font-bold text-gray-700 mb-4 uppercase tracking-tighter text-center">Fuentes de Ingreso</h3>
                    <div class="w-full relative" style="height: 200px;">
                        <canvas id="myChart"></canvas>
                    </div>
                </div>
            </div>

            {{-- Formulario Nuevo Ingreso --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-t-4 border-emerald-500">
                <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                    <span class="bg-emerald-100 text-emerald-600 p-2 rounded-lg mr-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                        </svg>
                    </span>
                    Registrar Nuevo Ingreso
                </h3>
                <form action="{{ route('ingresos.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    @csrf
                    <div>
                        <x-input-label for="descripcion" value="Descripción" />
                        <x-text-input id="descripcion" name="descripcion" type="text" class="mt-1 block w-full" required />
                    </div>
                    <div>
                        <x-input-label for="monto" value="Monto (€)" />
                        <x-text-input id="monto" name="monto" type="number" step="0.01" class="mt-1 block w-full" required />
                    </div>
                    <div>
                        <x-input-label for="fuente_ingreso_id" value="Fuente" />
                        <select name="fuente_ingreso_id" id="fuente_ingreso_id" class="mt-1 block w-full border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-md shadow-sm" required>
                            <option value="">Selecciona fuente</option>
                            @foreach($fuentes as $f)
                                <option value="{{ $f->id }}">{{ $f->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="fecha" value="Fecha" />
                        <x-text-input id="fecha" name="fecha" type="date" class="mt-1 block w-full" value="{{ date('Y-m-d') }}" required />
                    </div>
                    <div class="md:col-span-4 flex justify-end">
                        <x-primary-button class="bg-emerald-700 hover:bg-emerald-800">
                            {{ __('Guardar Ingreso') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>

            {{-- Tabla de Historial --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                <div class="p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Detalle de Entradas</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50 text-gray-600 text-xs uppercase tracking-wider border-b">
                                    <th class="px-4 py-3 font-bold">Fecha</th>
                                    <th class="px-4 py-3 font-bold">Descripción</th>
                                    <th class="px-4 py-3 font-bold">Fuente</th>
                                    <th class="px-4 py-3 font-bold text-right">Monto</th>
                                    <th class="px-4 py-3 font-bold text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($ingresos as $ingreso)
                                <tr class="hover:bg-emerald-50 transition">
                                    <td class="px-4 py-3 text-sm text-gray-500">
                                        {{ \Carbon\Carbon::parse($ingreso->fecha)->format('d/m/Y') }}
                                    </td>
                                    <td class="px-4 py-3 font-medium text-gray-900">{{ $ingreso->descripcion }}</td>
                                    <td class="px-4 py-3">
                                        @php $color = $ingreso->fuenteIngreso->color ?? '#3B82F6'; @endphp
                                        <span class="px-3 py-1 rounded-full text-xs font-bold text-white"
                                              style="background-color: {{ $color }}">
                                            {{ $ingreso->fuenteIngreso->nombre ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right font-bold text-emerald-600">
                                        {{ number_format($ingreso->monto, 2, ',', '.') }}€
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <div class="flex justify-center items-center space-x-3">
                                            <a href="{{ route('ingresos.edit', [$ingreso, 'mes' => $mes, 'anio' => $anio]) }}" class="text-indigo-600 hover:text-indigo-900">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </a>
                                            <form action="{{ route('ingresos.destroy', $ingreso) }}" method="POST" onsubmit="return confirm('¿Eliminar este ingreso?')">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="mes"  value="{{ $mes }}">
                                                <input type="hidden" name="anio" value="{{ $anio }}">
                                                <button type="submit" class="text-red-400 hover:text-red-600 transition">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-10 text-center text-gray-400 italic">No hay ingresos que coincidan con la búsqueda.</td>
                                </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr class="bg-emerald-50 border-t-2 border-emerald-100">
                                    <td colspan="3" class="px-4 py-4 text-right font-black text-gray-700 uppercase">Total Ingresado:</td>
                                    <td class="px-4 py-4 text-right text-emerald-700 text-2xl font-black">
                                        {{ number_format($total_ingresos, 2, ',', '.') }}€
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- Script del Gráfico --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('myChart');
        const datos = @json($ingresosPorFuente);
        
        if (datos.length > 0) {
            new Chart(ctx, {
                type: 'doughnut', // Usamos doughnut para mantener la estética
                data: {
                    labels: datos.map(item => item.fuente_nombre),
                    datasets: [{
                        data: datos.map(item => item.total),
                        backgroundColor: datos.map(item => item.fuente_color || '#10B981'),
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom',
                            labels: { boxWidth: 10, font: { size: 10 } }
                        }
                    }
                }
            });
        }
    </script>
</x-app-layout>