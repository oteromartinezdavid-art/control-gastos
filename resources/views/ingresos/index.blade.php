<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Mi Control de Ingresos') }}
        </h2>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mt-6">
            <div class="flex items-center justify-between bg-white p-4 shadow-sm sm:rounded-lg border-l-4 border-green-500">
                <a href="{{ route('ingresos.index', ['mes' => $fechaObjeto->copy()->subMonth()->month, 'anio' => $fechaObjeto->copy()->subMonth()->year]) }}" 
                class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg text-gray-700 font-bold">
                    ← {{ ucfirst($fechaObjeto->copy()->subMonth()->translatedFormat('F')) }}
                </a>
                
                <h3 class="text-lg font-black text-gray-800 uppercase tracking-widest">
                    {{ $fechaObjeto->translatedFormat('F Y') }}
                </h3>

                <a href="{{ route('ingresos.index', ['mes' => $fechaObjeto->copy()->addMonth()->month, 'anio' => $fechaObjeto->copy()->addMonth()->year]) }}" 
                class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg text-gray-700 font-bold">
                    {{ ucfirst($fechaObjeto->copy()->addMonth()->translatedFormat('F')) }} →
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                <h3 class="text-lg font-medium mb-4">Nuevo Ingreso</h3>
                <form action="{{ route('ingresos.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    @csrf
                    <div>
                        <x-input-label for="descripcion" value="Descripción" />
                        <x-text-input id="descripcion" name="descripcion" type="text" class="mt-1 block w-full" required />
                    </div>
                    <div>
                        <x-input-label for="monto" value="Monto ($)" />
                        <x-text-input id="monto" name="monto" type="number" step="0.01" class="mt-1 block w-full" required />
                    </div>
                    <div>
                        <x-input-label for="fuente_ingreso_id" value="Fuente" />
                        <select name="fuente_ingreso_id" id="fuente_ingreso_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                            <option value="">Selecciona una fuente</option>
                            @foreach($fuentes as $f)
                                <option value="{{ $f->id }}">{{ $f->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="fecha" value="Fecha" />
                        <x-text-input id="fecha" name="fecha" type="date" class="mt-1 block w-full" value="{{ date('Y-m-d') }}" required />
                    </div>
                    <div class="md:col-span-4 mt-2">
                        <x-primary-button>Guardar Ingreso</x-primary-button>
                    </div>
                </form>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                <h3 class="text-lg font-medium mb-4">Historial de Ingresos</h3>
                <table class="w-full text-left border-collapse">
                    <thead class="bg-[#f8fafc] text-[#1e1b4b] border-b-2 border-[#f97316]">
                        <tr>
                            <th class="px-4 py-2">Fecha</th>
                            <th class="px-4 py-2">Descripción</th>
                            <th class="px-4 py-2">Fuente</th>
                            <th class="px-4 py-2 text-right">Monto</th>
                            <th class="px-4 py-2 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($ingresos as $ingreso)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-4 py-3">{{ $ingreso->fecha }}</td>
                            <td class="px-4 py-3 font-semibold">{{ $ingreso->descripcion }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs">
                                    {{ $ingreso->fuenteIngreso->nombre ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right font-bold text-green-600">${{ number_format($ingreso->monto, 2) }}</td>
                            <td class="py-3 text-center flex justify-center items-center space-x-3">
                                <a href="{{ route('ingresos.edit', $ingreso) }}" class="text-blue-600 hover:text-blue-800 font-bold uppercase text-xs">
                                    Editar
                                </a>
                                <form action="{{ route('ingresos.destroy', $ingreso) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar este ingreso?')">
                                    @csrf
                                    @method('DELETE')
                                   <button type="submit" class="text-red-600 hover:text-red-800 font-bold uppercase text-xs">
                                        Eliminar
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                        <tr class="bg-green-50 font-bold">
                            <td colspan="3" class="px-4 py-3 text-right">TOTAL INGRESOS:</td>
                            <td class="px-4 py-3 text-right text-green-700 text-xl">
                                ${{ number_format($total_ingresos, 2) }}
                            </td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                <h3 class="text-lg font-medium mb-4 text-center">Ingresos por Fuente</h3>
                <div style="max-width: 300px; margin: auto;">
                    <canvas id="myChart"></canvas>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('myChart');
        const datos = @json($ingresosPorFuente);
        
        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: datos.map(item => item.fuente_nombre),
                datasets: [{
                    label: 'Total Ingresado',
                    data: datos.map(item => item.total),
                    backgroundColor: ['#4F46E5', '#10B981', '#F59E0B', '#EF4444', '#6366F1'],
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    </script>
</x-app-layout>