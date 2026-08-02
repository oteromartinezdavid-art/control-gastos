<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Mi Cartera de Inversiones
            </h2>
            <div class="flex gap-2 text-sm">
                <a href="{{ route('inversiones.operaciones.index') }}"
                   class="px-3 py-1.5 bg-[#1e1b4b] text-white rounded-lg hover:bg-[#312e81] transition font-medium">
                    + Operación
                </a>
                <a href="{{ route('inversiones.dividendos.index') }}"
                   class="px-3 py-1.5 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition font-medium">
                    + Dividendo
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-lg text-sm font-medium">
                    {{ session('success') }}
                </div>
            @endif

            {{-- KPI Row 1 --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white p-6 rounded-lg shadow-sm border-b-4 border-[#1e1b4b]">
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-widest">Inversión Total</p>
                    <p class="text-3xl font-black text-gray-900 mt-1">{{ number_format($kpis['inversion_total'], 2, ',', '.') }}€</p>
                    <p class="text-xs text-gray-400 mt-1">capital desembolsado (incl. comisiones)</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-sm border-b-4 border-blue-500">
                    <p class="text-xs font-bold text-blue-600 uppercase tracking-widest">Valor de Cartera</p>
                    <p class="text-3xl font-black text-blue-700 mt-1">{{ number_format($kpis['valor_cartera'], 2, ',', '.') }}€</p>
                    <p class="text-xs text-gray-400 mt-1">valor a precios de mercado actuales</p>
                </div>
                @php
                    $pnlColor = $kpis['pnl_latente'] >= 0 ? 'emerald' : 'red';
                    $pnlSign  = $kpis['pnl_latente'] >= 0 ? '+' : '';
                @endphp
                <div class="bg-white p-6 rounded-lg shadow-sm border-b-4 border-{{ $pnlColor }}-500">
                    <p class="text-xs font-bold text-{{ $pnlColor }}-600 uppercase tracking-widest">P&L Latente</p>
                    <p class="text-3xl font-black text-{{ $pnlColor }}-700 mt-1">
                        {{ $pnlSign }}{{ number_format($kpis['pnl_latente'], 2, ',', '.') }}€
                    </p>
                    <p class="text-xs text-gray-400 mt-1">
                        {{ $pnlSign }}{{ number_format($kpis['pnl_latente_pct'], 2, ',', '.') }}% sobre coste
                    </p>
                </div>
            </div>

            {{-- KPI Row 2 --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @php $pnlRColor = $kpis['pnl_realizado'] >= 0 ? 'emerald' : 'red'; @endphp
                <div class="bg-white p-6 rounded-lg shadow-sm border-b-4 border-{{ $pnlRColor }}-400">
                    <p class="text-xs font-bold text-{{ $pnlRColor }}-600 uppercase tracking-widest">P&L Realizado</p>
                    <p class="text-3xl font-black text-{{ $pnlRColor }}-700 mt-1">
                        {{ $kpis['pnl_realizado'] >= 0 ? '+' : '' }}{{ number_format($kpis['pnl_realizado'], 2, ',', '.') }}€
                    </p>
                    <p class="text-xs text-gray-400 mt-1">beneficio / pérdida en posiciones cerradas</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-sm border-b-4 border-teal-500">
                    <p class="text-xs font-bold text-teal-600 uppercase tracking-widest">Dividendos Netos</p>
                    <p class="text-3xl font-black text-teal-700 mt-1">{{ number_format($kpis['total_dividendos_netos'], 2, ',', '.') }}€</p>
                    <p class="text-xs text-gray-400 mt-1">
                        YoC: <span class="font-bold text-teal-600">{{ number_format($kpis['yield_on_cost'], 2, ',', '.') }}%</span>
                        sobre coste histórico
                    </p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-sm border-b-4 border-orange-400">
                    <p class="text-xs font-bold text-orange-600 uppercase tracking-widest">Fuga por Comisiones</p>
                    <p class="text-3xl font-black text-orange-700 mt-1">{{ number_format($kpis['total_comisiones'], 2, ',', '.') }}€</p>
                    <p class="text-xs text-gray-400 mt-1">acumulado de todas las operaciones</p>
                </div>
            </div>

            {{-- Holdings table + Chart --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- Holdings table --}}
                <div class="lg:col-span-2 bg-white rounded-lg shadow-sm overflow-hidden border border-gray-100">
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                        <h3 class="font-bold text-gray-800">Posiciones Abiertas</h3>
                        <a href="{{ route('inversiones.activos.index') }}"
                           class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">Gestionar activos →</a>
                    </div>

                    @if(empty($posiciones))
                        <div class="p-12 text-center text-gray-400">
                            <p class="font-bold text-lg">Sin posiciones abiertas</p>
                            <p class="text-sm mt-1">Registra tus activos y añade operaciones de compra para empezar.</p>
                            <a href="{{ route('inversiones.activos.index') }}"
                               class="mt-4 inline-block px-4 py-2 bg-[#1e1b4b] text-white rounded-lg text-sm font-medium">
                                Añadir primer activo
                            </a>
                        </div>
                    @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead>
                                <tr class="bg-gray-50 border-b text-gray-600 text-xs uppercase tracking-widest font-bold">
                                    <th class="px-4 py-3">Activo</th>
                                    <th class="px-4 py-3 text-right">Unidades</th>
                                    <th class="px-4 py-3 text-right">P. Medio</th>
                                    <th class="px-4 py-3 text-right">Cotización</th>
                                    <th class="px-4 py-3 text-right">Valor</th>
                                    <th class="px-4 py-3 text-right">P&L €</th>
                                    <th class="px-4 py-3 text-right">P&L %</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($posiciones as $pos)
                                @php
                                    $isPositive = ($pos['pnl_latente'] ?? 0) >= 0;
                                    $pnlClass   = $isPositive ? 'text-emerald-600' : 'text-red-600';
                                @endphp
                                <tr class="hover:bg-gray-50/50">
                                    <td class="px-4 py-3">
                                        <a href="{{ route('inversiones.activos.show', $pos['activo']->id) }}"
                                           class="group">
                                            <span class="font-black text-gray-900 group-hover:text-indigo-600 transition">{{ $pos['activo']->ticker }}</span>
                                            <span class="block text-xs text-gray-400 group-hover:text-indigo-400 transition">{{ $pos['activo']->nombre }}</span>
                                        </a>
                                        @if($pos['activo']->sector)
                                            <span class="text-[10px] bg-indigo-50 text-indigo-600 px-1.5 py-0.5 rounded font-bold uppercase">
                                                {{ $pos['activo']->sector }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right font-mono text-gray-700">
                                        {{ number_format($pos['cantidad'], 4, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-3 text-right font-mono text-gray-700">
                                        {{ number_format($pos['precio_medio'], 4, ',', '.') }}€
                                    </td>
                                    <td class="px-4 py-3 text-right font-mono">
                                        @if($pos['cotizacion'] !== null)
                                            <span class="text-gray-800 font-bold">{{ number_format($pos['cotizacion'], 4, ',', '.') }}€</span>
                                            @if(($pos['moneda'] ?? 'EUR') !== 'EUR' && ($pos['cotizacion_nativa'] ?? null) !== null)
                                                <span class="block text-[10px] text-gray-400">
                                                    {{ number_format($pos['cotizacion_nativa'], 2, ',', '.') }} {{ $pos['moneda'] }}
                                                </span>
                                            @endif
                                        @else
                                            <span class="text-gray-400 italic text-xs">N/D</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right font-bold text-gray-900">
                                        @if($pos['valor_actual'] !== null)
                                            {{ number_format($pos['valor_actual'], 2, ',', '.') }}€
                                        @else
                                            {{ number_format($pos['inversion_total'], 2, ',', '.') }}€
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right font-bold {{ $pnlClass }}">
                                        @if($pos['pnl_latente'] !== null)
                                            {{ $pos['pnl_latente'] >= 0 ? '+' : '' }}{{ number_format($pos['pnl_latente'], 2, ',', '.') }}€
                                        @else
                                            <span class="text-gray-400 italic text-xs">N/D</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right font-bold {{ $pnlClass }}">
                                        @if($pos['pnl_pct'] !== null)
                                            {{ $pos['pnl_pct'] >= 0 ? '+' : '' }}{{ number_format($pos['pnl_pct'], 2, ',', '.') }}%
                                        @else
                                            <span class="text-gray-400 italic text-xs">N/D</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>

                {{-- Chart --}}
                <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100 flex flex-col">
                    <h3 class="font-bold text-gray-800 mb-4">Distribución de Cartera</h3>
                    @if(!empty($posiciones))
                        <div class="flex-1 flex items-center justify-center">
                            <canvas id="portfolioChart" height="260"></canvas>
                        </div>
                    @else
                        <div class="flex-1 flex items-center justify-center text-gray-400 text-sm">
                            Sin datos para mostrar
                        </div>
                    @endif
                </div>
            </div>

            {{-- Quick links --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <a href="{{ route('inversiones.operaciones.index') }}"
                   class="bg-white p-5 rounded-lg shadow-sm border border-gray-100 hover:border-indigo-300 transition group flex items-center gap-4">
                    <div class="w-12 h-12 bg-indigo-50 rounded-lg flex items-center justify-center group-hover:bg-indigo-100 transition">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <div>
                        <p class="font-bold text-gray-800">Libro de Operaciones</p>
                        <p class="text-xs text-gray-500">Historial completo de compras y ventas con P&L FIFO</p>
                    </div>
                </a>
                <a href="{{ route('inversiones.dividendos.index') }}"
                   class="bg-white p-5 rounded-lg shadow-sm border border-gray-100 hover:border-teal-300 transition group flex items-center gap-4">
                    <div class="w-12 h-12 bg-teal-50 rounded-lg flex items-center justify-center group-hover:bg-teal-100 transition">
                        <svg class="w-6 h-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="font-bold text-gray-800">Módulo de Dividendos</p>
                        <p class="text-xs text-gray-500">Registro de rentas pasivas brutas y netas por activo</p>
                    </div>
                </a>
            </div>

        </div>
    </div>

    @if(!empty($posiciones))
    <script>
        const ctx = document.getElementById('portfolioChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: {!! $chartLabels->toJson() !!},
                datasets: [{
                    data: {!! $chartData->toJson() !!},
                    backgroundColor: {!! $chartColors->toJson() !!},
                    borderWidth: 2,
                    borderColor: '#fff',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { font: { size: 11, weight: 'bold' }, padding: 12 }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                const pct = total > 0 ? ((ctx.parsed / total) * 100).toFixed(1) : 0;
                                return ` ${ctx.parsed.toLocaleString('es-ES', {minimumFractionDigits:2})}€ (${pct}%)`;
                            }
                        }
                    }
                },
                cutout: '60%',
            }
        });
    </script>
    @endif
</x-app-layout>
