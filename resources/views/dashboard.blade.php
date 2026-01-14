<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-white border-l-4 border-green-500 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm font-medium text-gray-500 uppercase">Total Ingresos</div>
                    <div class="mt-1 text-3xl font-bold text-green-600">${{ number_format($totalIngresos, 2) }}</div>
                </div>

                <div class="bg-white border-l-4 border-red-500 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm font-medium text-gray-500 uppercase">Total Gastos</div>
                    <div class="mt-1 text-3xl font-bold text-red-600">${{ number_format($totalGastos, 2) }}</div>
                </div>

                <div class="bg-white border-l-4 border-blue-500 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm font-medium text-gray-500 uppercase">Saldo Actual</div>
                    <div class="mt-1 text-3xl font-bold {{ $saldo >= 0 ? 'text-blue-600' : 'text-orange-600' }}">
                        ${{ number_format($saldo, 2) }}
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-center">
                <p class="text-gray-600 mb-4">¿Qué deseas registrar hoy?</p>
                <div class="flex justify-center space-x-4">
                    <a href="{{ route('ingresos.index') }}" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">+ Nuevo Ingreso</a>
                    <a href="{{ route('gastos.index') }}" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">+ Nuevo Gasto</a>
                </div>
            </div>

            <div class="mt-8 bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
                        <h4 class="text-[#1e1b4b] font-bold mb-4">Distribución de Gastos</h4>
                        <div style="max-height: 300px;" class="flex justify-center">
                            <canvas id="gastosChart"></canvas>
                        </div>
                    </div>

                    <div class="bg-[#1e1b4b] p-6 rounded-lg shadow-sm text-white flex flex-col justify-center">
                        <h4 class="font-bold text-[#f97316] mb-2">Análisis del Mes</h4>
                        @if($totalGastos > 0)
                            <p class="text-sm">Este mes has registrado {{ count($movimientos) }} movimientos.</p>
                            <p class="mt-4 text-lg">
                                Tu mayor gasto este mes es en la categoría: 
                                <span class="font-bold text-[#38bdf8]">
                                    {{ $categoriasLabels[0] ?? 'N/A' }}
                                </span>
                            </p>
                        @else
                            <p>No hay gastos registrados en este mes para mostrar el análisis.</p>
                        @endif
                    </div>

                </div>
            </div>
            <script>
                const ctx = document.getElementById('gastosChart').getContext('2d');
                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: {!! json_encode($categoriasLabels) !!},
                        datasets: [{
                            data: {!! json_encode($categoriasTotales) !!},
                            backgroundColor: ['#1e1b4b', '#f97316', '#38bdf8', '#818cf8', '#fbbf24', '#f472b6'],
                            borderWidth: 2,
                            borderColor: '#ffffff'
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: { position: 'bottom' }
                        },
                        cutout: '70%' // Esto lo hace tipo Donut
                    }
                });
            </script>
            <div class="mt-8 bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-100 flex flex-col md:flex-row items-center justify-between gap-4">
                    <div class="flex items-center space-x-4">
                        <a href="{{ route('dashboard', ['mes' => $fechaAnterior->month, 'año' => $fechaAnterior->year]) }}" 
                        class="p-2 rounded-full hover:bg-gray-100 text-[#1e1b4b] transition flex items-center justify-center border border-gray-200 shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </a>

                        <h3 class="text-xl font-bold text-[#1e1b4b] min-w-[180px] text-center capitalize">
                            {{ Carbon\Carbon::create($año, $mes)->translatedFormat('F Y') }}
                        </h3>

                        <a href="{{ route('dashboard', ['mes' => $fechaSiguiente->month, 'año' => $fechaSiguiente->year]) }}" 
                        class="p-2 rounded-full hover:bg-gray-100 text-[#1e1b4b] transition flex items-center justify-center border border-gray-200 shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    </div>

                    <form action="{{ route('dashboard') }}" method="GET" class="flex items-center gap-2">
                        <select name="mes" class="rounded-md border-gray-300 text-sm focus:ring-[#f97316]">
                            @foreach(range(1, 12) as $m)
                                <option value="{{ $m }}" {{ $mes == $m ? 'selected' : '' }}>
                                    {{ Carbon\Carbon::create()->month($m)->translatedFormat('M') }}
                                </option>
                            @endforeach
                        </select>
                        <select name="año" class="rounded-md border-gray-300 text-sm focus:ring-[#f97316]">
                            @foreach(range(Carbon\Carbon::now()->year, Carbon\Carbon::now()->year - 3) as $a)
                                <option value="{{ $a }}" {{ $año == $a ? 'selected' : '' }}>{{ $a }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="bg-[#1e1b4b] text-white p-2 rounded-md hover:bg-opacity-90">
                            Ir
                        </button>
                    </form>

                </div>
            </div>
            <div class="mt-8 bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium mb-4 text-[#1e1b4b]">
                    Resumen de {{ \Carbon\Carbon::now()->translatedFormat('F Y') }}
                </h3>
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b text-gray-400 text-sm">
                            <th class="py-2">Tipo</th>
                            <th class="py-2">Descripción</th>
                            <th class="py-2 text-right">Monto</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($movimientos as $mov)
                        <tr class="border-b">
                            <td class="py-3">
                                <span class="px-2 py-1 rounded-full text-xs font-bold {{ $mov->tipo == 'ingreso' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                    {{ strtoupper($mov->tipo) }}
                                </span>
                            </td>
                            <td class="py-3">{{ $mov->descripcion }}</td>
                            <td class="py-3 text-right font-bold {{ $mov->tipo == 'ingreso' ? 'text-green-600' : 'text-red-600' }}">
                                {{ $mov->tipo == 'ingreso' ? '+' : '-' }}${{ number_format($mov->monto, 2) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>

