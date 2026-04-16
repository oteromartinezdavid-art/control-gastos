<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Mi Control de Gastos') }}
        </h2>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mt-6">
            <div class="flex items-center justify-between bg-white p-4 shadow-sm sm:rounded-lg border-l-4 border-red-500">
                {{-- Botón Mes Anterior --}}
                <a href="{{ route('gastos.index', ['mes' => $fechaObjeto->copy()->subMonth()->month, 'anio' => $fechaObjeto->copy()->subMonth()->year]) }}" 
                class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg text-gray-700 font-bold">
                    ← {{ ucfirst($fechaObjeto->copy()->subMonth()->translatedFormat('F')) }}
                </a>
                
                {{-- Título Mes Actual --}}
                <div class="text-center">
                    <h3 class="text-lg font-black text-gray-800 uppercase tracking-widest">
                        {{ $fechaObjeto->translatedFormat('F Y') }}
                    </h3>
                </div>

                {{-- Botón Mes Siguiente --}}
                <a href="{{ route('gastos.index', ['mes' => $fechaObjeto->copy()->addMonth()->month, 'anio' => $fechaObjeto->copy()->addMonth()->year]) }}" 
                class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg text-gray-700 font-bold">
                    {{ ucfirst($fechaObjeto->copy()->addMonth()->translatedFormat('F')) }} →
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                <h3 class="text-lg font-medium mb-4">Nuevo Gasto</h3>
                <form action="{{ route('gastos.store') }}" method="POST">
                    @include('gastos.form-fields') {{-- Aquí incluimos los campos --}}
                    <div class="mt-4">
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
                                <span class="px-2 py-1 rounded-full text-xs font-bold text-white" 
                                    style="background-color: {{ $gasto->categoriaGasto->color ?? '#4F46E5' }}">
                                    {{ $gasto->categoriaGasto->nombre ?? 'Sin categoría' }}
                                </span>
                            </td>
                            <td class="py-3 text-right font-bold text-red-600">${{ number_format($gasto->monto, 2) }}</td>
                            <td class="py-3 text-center flex justify-center items-center space-x-4">
                                {{-- Botón Editar --}}
                                <a href="{{ route('gastos.edit', $gasto) }}" class="text-blue-600 hover:text-blue-900 font-medium">
                                    Editar
                                </a>

                                {{-- Formulario Eliminar (que ya tienes) --}}
                                <form action="{{ route('gastos.destroy', $gasto) }}" method="POST" onsubmit="return confirm('¿Estás seguro?')">
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
        // Asegúrate de que el controlador envíe 'color' en el objeto $gastosPorCategoria
        const datos = @json($gastosPorCategoria);
        
        new Chart(ctx, {
            type: 'doughnut', // Lo cambié a doughnut para que sea consistente con el dashboard, pero puedes dejar 'pie'
            data: {
                labels: datos.map(item => item.categoria),
                datasets: [{
                    label: 'Total Gastado',
                    data: datos.map(item => item.total),
                    // Mapeamos los colores que vienen de la base de datos
                    backgroundColor: datos.map(item => item.color), 
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    </script>
</x-app-layout>