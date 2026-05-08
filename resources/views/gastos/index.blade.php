<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Mi Control de Gastos') }}
        </h2>
        
        {{-- Navegación de Meses --}}
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mt-6">
            <div class="flex items-center justify-between bg-white p-4 shadow-sm sm:rounded-lg border-l-4 border-red-500">
                <a href="{{ route('gastos.index', ['mes' => $fechaObjeto->copy()->subMonth()->month, 'anio' => $fechaObjeto->copy()->subMonth()->year]) }}" 
                   class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg text-gray-700 font-bold transition">
                    ← {{ ucfirst($fechaObjeto->copy()->subMonth()->translatedFormat('F')) }}
                </a>
                
                <div class="text-center">
                    <h3 class="text-lg font-black text-gray-800 uppercase tracking-widest">
                        {{ $fechaObjeto->translatedFormat('F Y') }}
                    </h3>
                </div>

                <a href="{{ route('gastos.index', ['mes' => $fechaObjeto->copy()->addMonth()->month, 'anio' => $fechaObjeto->copy()->addMonth()->year]) }}" 
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
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 8.293A1 1 0 013 7.586V4z" />
                        </svg>
                        Filtros de Búsqueda
                    </h3>
                    
                    <form method="GET" action="{{ route('gastos.index') }}" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- Mantener mes/año si no se usan fechas específicas --}}
                        <input type="hidden" name="mes" value="{{ $mesActual }}">
                        <input type="hidden" name="anio" value="{{ $anioActual }}">

                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase">Desde</label>
                            <input type="date" name="fecha_inicio" value="{{ $request->fecha_inicio }}" class="w-full mt-1 rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase">Hasta</label>
                            <input type="date" name="fecha_fin" value="{{ $request->fecha_fin }}" class="w-full mt-1 rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase">Categoría</label>
                            <select name="categoria_id" class="w-full mt-1 rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Todas las categorías</option>
                                @foreach($categorias as $cat)
                                    <option value="{{ $cat->id }}" {{ $request->categoria_id == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase">Concepto / Descripción</label>
                            <input type="text" name="descripcion" value="{{ $request->descripcion }}" placeholder="Ej: Supermercado..." class="w-full mt-1 rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        </div>

                        <div class="sm:col-span-2 flex justify-end space-x-2 mt-2">
                            <a href="{{ route('gastos.index') }}" class="px-4 py-2 bg-gray-100 text-gray-600 rounded-md hover:bg-gray-200 transition">Limpiar</a>
                            <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 shadow-sm transition">Aplicar Filtros</button>
                        </div>
                    </form>
                </div>

                {{-- Gráfico de Rosco --}}
                <div class="bg-white p-6 shadow-sm sm:rounded-lg border border-gray-100 flex flex-col items-center">
                    <h3 class="text-sm font-bold text-gray-700 mb-4 uppercase tracking-tighter text-center">Distribución del Gasto</h3>
                    <div class="w-full relative" style="height: 200px;">
                        <canvas id="myChart"></canvas>
                    </div>
                </div>
            </div>

            {{-- Formulario Nuevo Gasto --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-t-4 border-indigo-500">
                <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                    <span class="bg-indigo-100 text-indigo-600 p-2 rounded-lg mr-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                    </span>
                    Añadir Nuevo Gasto
                </h3>
                <form action="{{ route('gastos.store') }}" method="POST">
                    @csrf
                    @include('gastos.form-fields')
                    <div class="mt-4 flex justify-end">
                        <x-primary-button class="bg-indigo-700 hover:bg-indigo-800">
                            {{ __('Registrar Gasto') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>

            {{-- Tabla de Historial --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                <div class="p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Movimientos Detallados</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50 text-gray-600 text-xs uppercase tracking-wider border-b">
                                    <th class="px-4 py-3 font-bold">Fecha</th>
                                    <th class="px-4 py-3 font-bold">Descripción</th>
                                    <th class="px-4 py-3 font-bold">Categoría</th>
                                    <th class="px-4 py-3 font-bold text-right">Monto</th>
                                    <th class="px-4 py-3 font-bold text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($gastos as $gasto)
                                <tr class="hover:bg-indigo-50 transition">
                                    <td class="px-4 py-3 text-sm text-gray-500">
                                        {{ \Carbon\Carbon::parse($gasto->fecha)->format('d/m/Y') }}
                                    </td>
                                    <td class="px-4 py-3 font-medium text-gray-900">{{ $gasto->descripcion }}</td>
                                    <td class="px-4 py-3">
                                        <span class="px-3 py-1 rounded-full text-xs font-bold text-white shadow-sm" 
                                              style="background-color: {{ $gasto->categoriaGasto->color ?? '#4F46E5' }}">
                                            {{ $gasto->categoriaGasto->nombre ?? 'Sin categoría' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right font-bold text-red-600">
                                        {{ number_format($gasto->monto, 2, ',', '.') }}€
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <div class="flex justify-center items-center space-x-3">
                                            <a href="{{ route('gastos.edit', $gasto) }}" class="text-indigo-600 hover:text-indigo-900 transition">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </a>
                                            <form action="{{ route('gastos.destroy', $gasto) }}" method="POST" onsubmit="return confirm('¿Eliminar este gasto?')">
                                                @csrf
                                                @method('DELETE')
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
                                    <td colspan="5" class="px-4 py-10 text-center text-gray-400 italic">No se encontraron gastos con los filtros aplicados.</td>
                                </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr class="bg-gray-50 border-t-2 border-indigo-100">
                                    <td colspan="3" class="px-4 py-4 text-right font-black text-gray-700 uppercase">Total en este periodo:</td>
                                    <td class="px-4 py-4 text-right text-indigo-700 text-2xl font-black">
                                        {{ number_format($total, 2, ',', '.') }}€
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

    {{-- Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('myChart');
        const datos = @json($gastosPorCategoria);
        
        if (datos.length > 0) {
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: datos.map(item => item.categoria),
                    datasets: [{
                        data: datos.map(item => item.total),
                        backgroundColor: datos.map(item => item.color), 
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
                            labels: {
                                boxWidth: 10,
                                font: { size: 10 }
                            }
                        }
                    }
                }
            });
        }
    </script>
</x-app-layout>