<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard</h2>
            <div class="flex items-center gap-3 bg-white shadow-sm rounded-lg px-4 py-2 border border-gray-100">
                <a href="{{ route('dashboard', ['mes' => $fechaAnterior->month, 'anio' => $fechaAnterior->year]) }}"
                   class="px-3 py-1 bg-gray-100 hover:bg-gray-200 rounded text-gray-700 font-bold transition">
                    ← {{ ucfirst($fechaAnterior->translatedFormat('M')) }}
                </a>
                <span class="font-black text-gray-800 uppercase tracking-wide min-w-[130px] text-center">
                    {{ ucfirst($fechaConsulta->translatedFormat('F Y')) }}
                </span>
                <a href="{{ route('dashboard', ['mes' => $fechaSiguiente->month, 'anio' => $fechaSiguiente->year]) }}"
                   class="px-3 py-1 bg-gray-100 hover:bg-gray-200 rounded text-gray-700 font-bold transition">
                    {{ ucfirst($fechaSiguiente->translatedFormat('M')) }} →
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            {{-- 1. Fila de Tarjetas (Ahora con 4 columnas) --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                {{-- Ingresos --}}
                <div class="bg-white border-l-4 border-green-500 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-xs font-bold text-gray-500 uppercase tracking-wider">Total Ingresos</div>
                    <div class="mt-1 text-2xl font-black text-green-600">{{ number_format($totalIngresos, 2, ',', '.') }}€</div>
                </div>

                {{-- Gastos Ejecutados (Lo que ya ha salido) --}}
                <div class="bg-white border-l-4 border-red-500 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-xs font-bold text-gray-500 uppercase tracking-wider">Gastos Ejecutados</div>
                    <div class="mt-1 text-2xl font-black text-red-600">{{ number_format($totalGastosRealizados, 2, ',', '.') }}€</div>
                </div>

                {{-- Gastos Fijos Pendientes (Lo que va a salir) --}}
                <div class="bg-white border-l-4 border-orange-500 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-xs font-bold text-orange-600 uppercase tracking-wider italic">Fijos Pendientes</div>
                    <div class="mt-1 text-2xl font-black text-orange-700">{{ number_format($pendienteFijos, 2, ',', '.') }}€</div>
                </div>

                {{-- Saldo "Real" Final (Lo que te queda libre) --}}
                <div class="bg-white border-l-4 border-blue-500 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-xs font-bold text-gray-500 uppercase tracking-wider">Saldo "Real" Final</div>
                    <div class="mt-1 text-2xl font-black {{ $saldoRealFinal >= 0 ? 'text-blue-600' : 'text-red-600' }}">
                        {{ number_format($saldoRealFinal, 2, ',', '.') }}€
                    </div>
                </div>
            </div>

            {{-- Botones de acción rápida --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-center mb-6">
                <p class="text-gray-600 mb-4 font-medium italic">¿Qué deseas registrar hoy?</p>
                <div class="flex justify-center space-x-4">
                    <a href="{{ route('ingresos.index') }}" class="bg-green-600 text-white px-6 py-2 rounded-lg font-bold hover:bg-green-700 shadow-md transition-all">+ Ingreso</a>
                    <a href="{{ route('gastos.index') }}" class="bg-red-600 text-white px-6 py-2 rounded-lg font-bold hover:bg-red-700 shadow-md transition-all">+ Gasto</a>
                    <a href="{{ route('gastos-fijos.index') }}" class="bg-orange-500 text-white px-6 py-2 rounded-lg font-bold hover:bg-orange-600 shadow-md transition-all">Ver Fijos</a>
                </div>
            </div>

            {{-- Gráfica de Gasto Diario --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                <h3 class="text-lg font-bold text-[#1e1b4b] mb-4">Evolución de Gasto Diario</h3>
                <div style="height: 250px;">
                    <canvas id="diarioChart"></canvas>
                </div>
            </div>

            {{-- Resumen Financiaciones e Inversiones --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">

                {{-- Financiaciones --}}
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-[#1e1b4b] font-bold text-sm uppercase tracking-wider">Préstamos y Financiaciones</h4>
                        <a href="{{ route('financiaciones.index') }}" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">Ver detalle →</a>
                    </div>
                    @if($numFinanciaciones > 0)
                        <div class="grid grid-cols-3 gap-4">
                            <div class="text-center">
                                <div class="text-2xl font-black text-[#1e1b4b]">{{ $numFinanciaciones }}</div>
                                <div class="text-xs text-gray-500 mt-1">Activos</div>
                            </div>
                            <div class="text-center border-x border-gray-100">
                                <div class="text-2xl font-black text-orange-600">{{ number_format($totalCuotaMensual, 0, ',', '.') }}€</div>
                                <div class="text-xs text-gray-500 mt-1">Cuota/mes</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-black text-red-600">{{ number_format($totalDeudaPendiente, 0, ',', '.') }}€</div>
                                <div class="text-xs text-gray-500 mt-1">Deuda total</div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-6 text-gray-400 text-sm italic">Sin financiaciones activas</div>
                    @endif
                </div>

                {{-- Inversiones --}}
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-[#1e1b4b] font-bold text-sm uppercase tracking-wider">Cartera de Inversiones</h4>
                        <a href="{{ route('inversiones.index') }}" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">Ver cartera →</a>
                    </div>
                    @if($numActivos > 0)
                        <div class="grid grid-cols-3 gap-4">
                            <div class="text-center">
                                <div class="text-2xl font-black text-[#1e1b4b]">{{ $numActivos }}</div>
                                <div class="text-xs text-gray-500 mt-1">Activos</div>
                            </div>
                            <div class="text-center border-x border-gray-100">
                                <div class="text-2xl font-black text-indigo-600">{{ number_format($totalInvertidoCartera, 0, ',', '.') }}€</div>
                                <div class="text-xs text-gray-500 mt-1">Compras brutas</div>
                            </div>
                            <div class="text-center">
                                @php $retornoTotal = $totalVentasCartera + $totalDividendosCartera; @endphp
                                <div class="text-2xl font-black {{ $retornoTotal >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ number_format($retornoTotal, 0, ',', '.') }}€
                                </div>
                                <div class="text-xs text-gray-500 mt-1">Ventas + Divid.</div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-6 text-gray-400 text-sm italic">Sin activos registrados</div>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">

                {{-- Gráfica de Categorías --}}
                <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
                    <h4 class="text-[#1e1b4b] font-bold mb-4">Distribución por Categoría</h4>
                    <div style="max-height: 250px;" class="flex justify-center">
                        <canvas id="gastosChart"></canvas>
                    </div>
                </div>

                {{-- Análisis del Mes --}}
                <div class="bg-[#1e1b4b] p-6 rounded-2xl shadow-xl text-white relative overflow-hidden">
                    <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-[#f97316] opacity-10 rounded-full"></div>
                    <h4 class="font-bold text-[#f97316] mb-4 text-xs uppercase tracking-widest">Análisis del Mes</h4>

                    @php
                        // Obtenemos la categoría con mayor gasto directamente de la colección
                        $mayorGasto = $datosGrafico->sortByDesc('total')->first();
                    @endphp

                    @if($mayorGasto)
                        <div class="space-y-4">
                            <div>
                                <p class="text-3xl font-extrabold tracking-tight">
                                    {{ $mayorGasto->nombre }}
                                </p>
                                <p class="text-[#94a3b8] text-sm font-medium">Categoría con mayor impacto</p>
                            </div>

                            <div class="flex items-end gap-2">
                                <span class="text-2xl font-bold text-white">
                                    {{ number_format($mayorGasto->total, 2, ',', '.') }}€
                                </span>
                                <span class="text-green-400 text-sm mb-1 font-semibold">
                                    ({{ number_format(($mayorGasto->total / $totalGastosRealizados) * 100, 1) }}%)
                                </span>
                            </div>

                            <div class="pt-4 border-t border-white/10 text-gray-400 text-sm">
                                <p>Representa la mayor parte de tus salidas este mes.</p>
                            </div>
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center py-8 text-center text-gray-400">
                            <p class="text-sm italic">Sin datos de gasto suficientes para analizar.</p>
                        </div>
                    @endif
                </div>
            </div>                  
        </div>
    </div>

    {{-- Chart.js Scripts (Igual que antes) --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // --- GRÁFICA DE DONA (CATEGORÍAS) ---
        const ctxDona = document.getElementById('gastosChart').getContext('2d');
        new Chart(ctxDona, {
            type: 'doughnut',
            data: {
                // Extraemos etiquetas, totales y colores directamente de la colección $datosGrafico
                labels: {!! json_encode($datosGrafico->pluck('nombre')) !!},
                datasets: [{
                    data: {!! json_encode($datosGrafico->pluck('total')) !!},
                    backgroundColor: {!! json_encode($datosGrafico->pluck('color')) !!}, 
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { 
                    legend: { 
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 20
                        }
                    } 
                },
                cutout: '70%'
            }
        });

        // --- GRÁFICA LINEAL (GASTO DIARIO) ---
        const ctxLinea = document.getElementById('diarioChart').getContext('2d');
        new Chart(ctxLinea, {
            type: 'line',
            data: {
                labels: {!! json_encode($labelsDias) !!},
                datasets: [{
                    label: 'Gasto Diario (€)',
                    data: {!! json_encode($datosDias) !!},
                    borderColor: '#f97316',
                    backgroundColor: 'rgba(249, 115, 22, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 3,
                    pointBackgroundColor: '#1e1b4b'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { 
                        beginAtZero: true, 
                        ticks: { callback: value => value + '€' } 
                    },
                    x: { grid: { display: false } }
                },
                plugins: { legend: { display: false } }
            }
        });
    </script>
</x-app-layout>