<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div class="flex items-center gap-4">
                <a href="#" onclick="history.back(); return false;"
                   class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">← Volver</a>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Resumen Anual de Inversiones — {{ $anio }}
                </h2>
            </div>
            {{-- Selector de año --}}
            <a href="{{ route('inversiones.operaciones.index', ['anio' => $anio]) }}"
               class="flex items-center gap-1.5 px-3 py-1.5 bg-[#f97316] hover:bg-orange-500 text-white rounded-lg text-sm font-bold transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Informe Fiscal {{ $anio }}
            </a>
            <form method="GET" action="{{ route('inversiones.resumen-anual') }}" class="flex items-center gap-2">
                <label class="text-sm text-gray-600 font-medium">Año:</label>
                <select name="anio" onchange="this.form.submit()"
                        class="border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    @foreach($anos_disponibles as $a)
                        <option value="{{ $a }}" {{ $a == $anio ? 'selected' : '' }}>
                            {{ $a }}{{ $a == now()->year ? ' (actual)' : '' }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            {{-- Estado cartera inicio / fin de año --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach([['label' => '1 enero ' . $anio . ' — Cartera a inicio', 'data' => $cartera_inicio], ['label' => '31 dic ' . $anio . ' — Cartera a cierre', 'data' => $cartera_fin]] as $bloque)
                <div class="bg-white shadow-sm rounded-lg border border-gray-100">
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                        <h3 class="font-bold text-gray-800 text-sm">{{ $bloque['label'] }}</h3>
                        <span class="text-lg font-black text-indigo-700">{{ number_format($bloque['data']['total_coste'], 2, ',', '.') }}€</span>
                    </div>
                    @if(count($bloque['data']['posiciones']) > 0)
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-xs font-bold text-gray-500 uppercase">
                            <tr>
                                <th class="px-4 py-2 text-left">Activo</th>
                                <th class="px-4 py-2 text-right">Cantidad</th>
                                <th class="px-4 py-2 text-right">Coste</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($bloque['data']['posiciones'] as $pos)
                            <tr>
                                <td class="px-4 py-2 font-medium">{{ $pos['activo']->ticker }} <span class="text-gray-400 text-xs">{{ $pos['activo']->nombre }}</span></td>
                                <td class="px-4 py-2 text-right text-gray-600">{{ number_format($pos['cantidad'], 4) }}</td>
                                <td class="px-4 py-2 text-right font-bold">{{ number_format($pos['coste'], 2, ',', '.') }}€</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @else
                    <p class="px-6 py-4 text-sm text-gray-400 italic">Sin posiciones abiertas.</p>
                    @endif
                </div>
                @endforeach
            </div>

            {{-- KPIs del año --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @php
                    $kpis = [
                        ['label' => 'P&L Realizado', 'value' => $pnl_realizado, 'color' => $pnl_realizado >= 0 ? 'emerald' : 'red', 'euro' => true, 'pct' => $pnl_pct],
                        ['label' => 'Dividendos Netos', 'value' => $total_div_neto, 'color' => 'blue', 'euro' => true],
                        ['label' => 'Retención Dividendos', 'value' => $total_div_retencion, 'color' => 'amber', 'euro' => true],
                        ['label' => 'Resultado Fiscal', 'value' => $resultado_fiscal, 'color' => $resultado_fiscal >= 0 ? 'indigo' : 'red', 'euro' => true],
                    ];
                @endphp
                @foreach($kpis as $kpi)
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-5">
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">{{ $kpi['label'] }}</p>
                    <p class="mt-1 text-2xl font-black {{ $kpi['value'] >= 0 ? 'text-'.$kpi['color'].'-600' : 'text-red-600' }}">
                        {{ $kpi['value'] >= 0 ? '+' : '' }}{{ number_format($kpi['value'], 2, ',', '.') }}€
                    </p>
                    @if(!empty($kpi['pct']) && $kpi['pct'] !== null)
                        <p class="text-sm font-bold {{ $kpi['pct'] >= 0 ? 'text-emerald-500' : 'text-red-500' }}">
                            {{ $kpi['pct'] >= 0 ? '+' : '' }}{{ number_format($kpi['pct'], 2) }}%
                        </p>
                    @endif
                </div>
                @endforeach
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-5">
                    <p class="text-xs font-bold text-gray-500 uppercase">Compras realizadas</p>
                    <p class="mt-1 text-2xl font-black text-gray-800">{{ $compras->count() }}</p>
                    <p class="text-sm text-gray-500">{{ number_format($compras->sum('importe_neto'), 2, ',', '.') }}€ invertidos</p>
                </div>
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-5">
                    <p class="text-xs font-bold text-gray-500 uppercase">Ventas realizadas</p>
                    <p class="mt-1 text-2xl font-black text-gray-800">{{ $ventas->count() }}</p>
                    <p class="text-sm text-gray-500">{{ $ventas->count() > 0 ? number_format($ventas->sum(fn($v) => (float)$v['operacion']->cantidad * (float)$v['operacion']->precio_unitario), 2, ',', '.') . '€ transmitidos' : '—' }}</p>
                </div>
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-5">
                    <p class="text-xs font-bold text-gray-500 uppercase">Comisiones pagadas</p>
                    <p class="mt-1 text-2xl font-black text-red-500">{{ number_format($comisiones, 2, ',', '.') }}€</p>
                </div>
            </div>

            {{-- Ventas con P&L --}}
            @if($ventas->count() > 0)
            <div class="bg-white shadow-sm rounded-lg border border-gray-100">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="font-bold text-gray-800">Ventas — Plusvalías / Minusvalías</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50 text-xs font-bold text-gray-500 uppercase">
                            <tr>
                                <th class="px-4 py-3 text-left">Fecha</th>
                                <th class="px-4 py-3 text-left">Activo</th>
                                <th class="px-4 py-3 text-right">Cantidad</th>
                                <th class="px-4 py-3 text-right">Precio venta</th>
                                <th class="px-4 py-3 text-right">Val. transmisión</th>
                                <th class="px-4 py-3 text-right">P&L</th>
                                <th class="px-4 py-3 text-right">%</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($ventas as $item)
                            @php $op = $item['operacion']; $pnl = $item['pnl']; @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm text-gray-500">{{ $op->fecha->format('d/m/Y') }}</td>
                                <td class="px-4 py-3 font-medium">{{ $op->activo->ticker }} <span class="text-gray-400 text-xs">{{ $op->activo->nombre }}</span></td>
                                <td class="px-4 py-3 text-right text-sm">{{ number_format($op->cantidad, 4) }}</td>
                                <td class="px-4 py-3 text-right text-sm">{{ number_format($op->precio_unitario, 4) }}€</td>
                                <td class="px-4 py-3 text-right text-sm">{{ number_format($op->cantidad * $op->precio_unitario - $op->total_gastos, 2, ',', '.') }}€</td>
                                <td class="px-4 py-3 text-right font-bold {{ $pnl >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                                    {{ $pnl >= 0 ? '+' : '' }}{{ number_format($pnl, 2, ',', '.') }}€
                                </td>
                                <td class="px-4 py-3 text-right text-sm font-bold {{ ($item['pnl_pct'] ?? 0) >= 0 ? 'text-emerald-500' : 'text-red-500' }}">
                                    @if($item['pnl_pct'] !== null)
                                        {{ $item['pnl_pct'] >= 0 ? '+' : '' }}{{ number_format($item['pnl_pct'], 2) }}%
                                    @else —
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-gray-50 border-t-2 border-gray-200">
                                <td colspan="5" class="px-4 py-3 text-right font-bold text-gray-700">Total P&L Realizado:</td>
                                <td class="px-4 py-3 text-right text-lg font-black {{ $pnl_realizado >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                                    {{ $pnl_realizado >= 0 ? '+' : '' }}{{ number_format($pnl_realizado, 2, ',', '.') }}€
                                </td>
                                <td class="px-4 py-3 text-right font-black {{ ($pnl_pct ?? 0) >= 0 ? 'text-emerald-500' : 'text-red-500' }}">
                                    @if($pnl_pct !== null){{ $pnl_pct >= 0 ? '+' : '' }}{{ number_format($pnl_pct, 2) }}%@endif
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            @else
            <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-8 text-center text-gray-400 italic">
                No hubo ventas en {{ $anio }}.
            </div>
            @endif

            {{-- Dividendos por activo --}}
            @if($dividendos_por_activo->count() > 0)
            <div class="bg-white shadow-sm rounded-lg border border-gray-100">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="font-bold text-gray-800">Dividendos Cobrados</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50 text-xs font-bold text-gray-500 uppercase">
                            <tr>
                                <th class="px-4 py-3 text-left">Activo</th>
                                <th class="px-4 py-3 text-right">Pagos</th>
                                <th class="px-4 py-3 text-right">Bruto</th>
                                <th class="px-4 py-3 text-right">Retención</th>
                                <th class="px-4 py-3 text-right">Neto</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($dividendos_por_activo as $d)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium">{{ $d['activo']->ticker }} <span class="text-gray-400 text-xs">{{ $d['activo']->nombre }}</span></td>
                                <td class="px-4 py-3 text-right text-sm text-gray-500">{{ $d['count'] }}</td>
                                <td class="px-4 py-3 text-right text-sm">{{ number_format($d['bruto'], 2, ',', '.') }}€</td>
                                <td class="px-4 py-3 text-right text-sm text-red-500">-{{ number_format($d['retencion'], 2, ',', '.') }}€</td>
                                <td class="px-4 py-3 text-right font-bold text-blue-600">{{ number_format($d['neto'], 2, ',', '.') }}€</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-gray-50 border-t-2 border-gray-200">
                                <td colspan="2" class="px-4 py-3 text-right font-bold text-gray-700">Totales:</td>
                                <td class="px-4 py-3 text-right font-bold">{{ number_format($total_div_bruto, 2, ',', '.') }}€</td>
                                <td class="px-4 py-3 text-right font-bold text-red-500">-{{ number_format($total_div_retencion, 2, ',', '.') }}€</td>
                                <td class="px-4 py-3 text-right text-lg font-black text-blue-600">{{ number_format($total_div_neto, 2, ',', '.') }}€</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            @else
            <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-8 text-center text-gray-400 italic">
                No hubo dividendos en {{ $anio }}.
            </div>
            @endif

            {{-- Compras del año --}}
            @if($compras->count() > 0)
            <div class="bg-white shadow-sm rounded-lg border border-gray-100">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="font-bold text-gray-800">Compras realizadas</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50 text-xs font-bold text-gray-500 uppercase">
                            <tr>
                                <th class="px-4 py-3 text-left">Fecha</th>
                                <th class="px-4 py-3 text-left">Activo</th>
                                <th class="px-4 py-3 text-right">Cantidad</th>
                                <th class="px-4 py-3 text-right">Precio</th>
                                <th class="px-4 py-3 text-right">Comisiones</th>
                                <th class="px-4 py-3 text-right">Total invertido</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($compras as $op)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm text-gray-500">{{ $op->fecha->format('d/m/Y') }}</td>
                                <td class="px-4 py-3 font-medium">{{ $op->activo->ticker }} <span class="text-gray-400 text-xs">{{ $op->activo->nombre }}</span></td>
                                <td class="px-4 py-3 text-right text-sm">{{ number_format($op->cantidad, 4) }}</td>
                                <td class="px-4 py-3 text-right text-sm">{{ number_format($op->precio_unitario, 4) }}€</td>
                                <td class="px-4 py-3 text-right text-sm text-red-400">{{ number_format($op->total_gastos, 2, ',', '.') }}€</td>
                                <td class="px-4 py-3 text-right font-bold">{{ number_format($op->importe_neto, 2, ',', '.') }}€</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-gray-50 border-t-2 border-gray-200">
                                <td colspan="5" class="px-4 py-3 text-right font-bold text-gray-700">Total invertido:</td>
                                <td class="px-4 py-3 text-right text-lg font-black text-gray-800">{{ number_format($compras->sum('importe_neto'), 2, ',', '.') }}€</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            @endif

            {{-- Resultado fiscal resumen --}}
            <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-6">
                <h3 class="font-bold text-indigo-800 mb-4 text-lg">Resumen Fiscal {{ $anio }}</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-center">
                    <div>
                        <p class="text-xs font-bold text-indigo-500 uppercase">Plusvalías / Minusvalías</p>
                        <p class="text-2xl font-black {{ $pnl_realizado >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                            {{ $pnl_realizado >= 0 ? '+' : '' }}{{ number_format($pnl_realizado, 2, ',', '.') }}€
                        </p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-indigo-500 uppercase">Dividendos Netos</p>
                        <p class="text-2xl font-black text-blue-600">+{{ number_format($total_div_neto, 2, ',', '.') }}€</p>
                    </div>
                    <div class="border-t md:border-t-0 md:border-l border-indigo-200 md:pl-4 pt-4 md:pt-0">
                        <p class="text-xs font-bold text-indigo-500 uppercase">Resultado Total</p>
                        <p class="text-3xl font-black {{ $resultado_fiscal >= 0 ? 'text-indigo-700' : 'text-red-600' }}">
                            {{ $resultado_fiscal >= 0 ? '+' : '' }}{{ number_format($resultado_fiscal, 2, ',', '.') }}€
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
