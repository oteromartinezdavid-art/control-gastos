<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Mi Control de Gastos') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                <h3 class="text-lg font-medium mb-4">Nuevo Gasto</h3>
                <form action="{{ route('gastos.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4">
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
                        <x-input-label for="categoria" value="Categoría" />
                        <select name="categoria" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="Comida">Comida</option>
                            <option value="Transporte">Transporte</option>
                            <option value="Ocio">Ocio</option>
                            <option value="Vivienda">Vivienda</option>
                        </select>
                    </div>
                    <div>
                        <x-input-label for="fecha" value="Fecha" />
                        <x-text-input id="fecha" name="fecha" type="date" class="mt-1 block w-full" value="{{ date('Y-m-d') }}" required />
                    </div>
                    <div class="md:col-span-4 mt-2">
                        <x-primary-button>Guardar Gasto</x-primary-button>
                    </div>
                </form>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium mb-4">Historial de Gastos</h3>
                <table class="w-full text-left border-collapse">
                    <thead class="bg-[#f8fafc] text-[#1e1b4b] border-b-2 border-[#f97316]">
                        <tr class="border-b">
                            <th class="py-2">Fecha</th>
                            <th class="py-2">Descripción</th>
                            <th class="py-2">Categoría</th>
                            <th class="py-2 text-right">Monto</th>
                            <th class="py-2 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($gastos as $gasto)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-3">{{ $gasto->fecha }}</td>
                            <td class="py-3 font-semibold">{{ $gasto->descripcion }}</td>
                            <td class="py-3">
                                <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs">
                                    {{ $gasto->categoria }}
                                </span>
                            </td>
                            <td class="py-3 text-right font-bold text-red-600">${{ number_format($gasto->monto, 2) }}</td>
                            <td class="py-3 text-center">
                                <form action="{{ route('gastos.destroy', $gasto) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar este gasto?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900 font-medium">
                                        Eliminar
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                        <tr class="bg-gray-100 font-bold">
                            <td colspan="4" class="py-3 text-right">TOTAL GENERAL:</td>
                            <td class="py-3 text-right text-indigo-700 text-xl">${{ number_format($total, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mb-6">
        <div class="bg-white p-6 shadow-sm sm:rounded-lg">
            <h3 class="text-lg font-medium mb-4 text-center">Gastos por Categoría</h3>
            <div style="max-width: 300px; margin: auto;">
                <canvas id="myChart"></canvas>
            </div>
        </div>
    </div>
    <script>
        const ctx = document.getElementById('myChart');
        
        // Convertimos los datos de PHP a JavaScript
        const datos = @json($gastosPorCategoria);
        
        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: datos.map(item => item.categoria),
                datasets: [{
                    label: 'Total Gastado',
                    data: datos.map(item => item.total),
                    backgroundColor: [
                        '#4F46E5', '#EF4444', '#10B981', '#F59E0B', '#6366F1'
                    ],
                }]
            }
        });
    </script>
</x-app-layout>