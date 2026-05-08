<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div class="flex items-center gap-3">
                <a href="{{ route('inversiones.activos.index') }}"
                   class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">← Activos</a>
                <span class="text-gray-300">|</span>
                <div>
                    <h2 class="font-black text-2xl text-[#1e1b4b] leading-none">{{ $activo->ticker }}</h2>
                    <p class="text-sm text-gray-500 mt-0.5">{{ $activo->nombre }}
                        @if($activo->sector)
                            <span class="ml-2 px-2 py-0.5 bg-indigo-100 text-indigo-700 rounded text-xs font-bold">{{ $activo->sector }}</span>
                        @endif
                        @if($activo->mercado)
                            <span class="ml-1 text-gray-400 text-xs">{{ $activo->mercado }}</span>
                        @endif
                    </p>
                </div>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('inversiones.operaciones.index', ['activo_id' => $activo->id]) }}"
                   class="px-3 py-1.5 bg-[#1e1b4b] text-white rounded-lg hover:bg-[#312e81] transition text-sm font-medium">
                    + Operación
                </a>
                <a href="{{ route('inversiones.dividendos.index', ['activo_id' => $activo->id]) }}"
                   class="px-3 py-1.5 bg-teal-600 text-white rounded-lg hover:bg-teal-700 transition text-sm font-medium">
                    + Dividendo
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- ── KPIs HISTÓRICOS ── --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white p-5 rounded-lg shadow-sm border-b-4 border-gray-400">
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-widest">Total Invertido</p>
                    <p class="text-2xl font-black text-gray-900 mt-1">{{ number_format($totalInvertido, 2, ',', '.') }}€</p>
                    <p class="text-xs text-gray-400 mt-1">coste histórico total (incl. com.)</p>
                </div>
                @php $prPos = $pnlRealizado >= 0; @endphp
                <div class="bg-white p-5 rounded-lg shadow-sm border-b-4 border-{{ $prPos ? 'emerald' : 'red' }}-400">
                    <p class="text-xs font-bold text-{{ $prPos ? 'emerald' : 'red' }}-600 uppercase tracking-widest">P&L Realizado</p>
                    <p class="text-2xl font-black text-{{ $prPos ? 'emerald' : 'red' }}-700 mt-1">
                        {{ $pnlRealizado >= 0 ? '+' : '' }}{{ number_format($pnlRealizado, 2, ',', '.') }}€
                    </p>
                    <p class="text-xs text-gray-400 mt-1">posiciones ya cerradas</p>
                </div>
                <div class="bg-white p-5 rounded-lg shadow-sm border-b-4 border-teal-400">
                    <p class="text-xs font-bold text-teal-600 uppercase tracking-widest">Dividendos Netos</p>
                    <p class="text-2xl font-black text-teal-700 mt-1">{{ number_format($totalDivNeto, 2, ',', '.') }}€</p>
                    <p class="text-xs text-gray-400 mt-1">{{ number_format($totalDivBruto, 2, ',', '.') }}€ bruto · {{ $dividendos->count() }} cobro(s)</p>
                </div>
                <div class="bg-white p-5 rounded-lg shadow-sm border-b-4 border-orange-400">
                    <p class="text-xs font-bold text-orange-600 uppercase tracking-widest">Comisiones Pagadas</p>
                    <p class="text-2xl font-black text-orange-700 mt-1">{{ number_format($totalComisiones, 2, ',', '.') }}€</p>
                    <p class="text-xs text-gray-400 mt-1">{{ $operaciones->count() }} operación(es) totales</p>
                </div>
            </div>

            {{-- ── POSICIÓN ACTUAL + TOTAL RETURN ── --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                {{-- Posición abierta --}}
                @if($cantidad > 0)
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-5">
                    <h3 class="font-bold text-gray-700 text-sm uppercase tracking-widest mb-4">Posición Abierta</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs text-gray-500">Unidades en cartera</p>
                            <p class="text-xl font-black text-gray-900">{{ number_format($cantidad, 4, ',', '.') }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Precio Medio</p>
                            <p class="text-xl font-black text-gray-900">{{ number_format($precioMedio, 4, ',', '.') }}€</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Cotización Actual</p>
                            @if($cotizacion !== null)
                                <p class="text-xl font-black text-blue-700">{{ number_format($cotizacion, 4, ',', '.') }}€</p>
                            @else
                                <p class="text-xl font-black text-gray-300">N/D</p>
                            @endif
                        </div>
                        <div>
                            @php $plPos = ($pnlLatente ?? 0) >= 0; @endphp
                            <p class="text-xs text-gray-500">P&L Latente</p>
                            @if($pnlLatente !== null)
                                <p class="text-xl font-black {{ $plPos ? 'text-emerald-600' : 'text-red-600' }}">
                                    {{ $pnlLatente >= 0 ? '+' : '' }}{{ number_format($pnlLatente, 2, ',', '.') }}€
                                    <span class="text-sm font-semibold">({{ $pnlLatente >= 0 ? '+' : '' }}{{ number_format($pnlLatentePct, 1, ',', '.') }}%)</span>
                                </p>
                            @else
                                <p class="text-xl font-black text-gray-300">N/D</p>
                            @endif
                        </div>
                    </div>
                    {{-- Lotes FIFO disponibles --}}
                    @if(!empty($lotes))
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <p class="text-xs font-bold text-gray-500 uppercase mb-2">Lotes disponibles (FIFO)</p>
                        <div class="space-y-1">
                            @foreach($lotes as $lote)
                            <div class="flex justify-between text-xs bg-gray-50 rounded px-3 py-1.5">
                                <span class="text-gray-500">Compra {{ $lote['fecha'] }}</span>
                                <span class="font-mono text-gray-700">{{ number_format($lote['cantidad_disponible'], 4, ',', '.') }} u.</span>
                                <span class="font-mono text-gray-700">{{ number_format($lote['precio_unitario'], 4, ',', '.') }}€</span>
                                @if($cotizacion)
                                @php $pnlLote = ($cotizacion - $lote['precio_unitario']) * $lote['cantidad_disponible']; @endphp
                                <span class="font-bold {{ $pnlLote >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                                    {{ $pnlLote >= 0 ? '+' : '' }}{{ number_format($pnlLote, 2, ',', '.') }}€
                                </span>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
                @else
                <div class="bg-gray-50 rounded-lg border border-dashed border-gray-200 p-5 flex items-center justify-center text-gray-400">
                    <div class="text-center">
                        <p class="font-bold">Sin posición abierta</p>
                        <p class="text-sm mt-1">Todas las unidades han sido vendidas</p>
                    </div>
                </div>
                @endif

                {{-- Total Return --}}
                @php $trPos = $totalReturn >= 0; @endphp
                <div class="bg-white rounded-lg shadow-sm border-2 {{ $trPos ? 'border-emerald-200' : 'border-red-200' }} p-5">
                    <h3 class="font-bold text-gray-700 text-sm uppercase tracking-widest mb-4">Rentabilidad Total</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between items-center py-1.5 border-b border-gray-100">
                            <span class="text-gray-600">P&L Realizado (ventas cerradas)</span>
                            <span class="font-bold {{ $prPos ? 'text-emerald-600' : 'text-red-600' }}">
                                {{ $pnlRealizado >= 0 ? '+' : '' }}{{ number_format($pnlRealizado, 2, ',', '.') }}€
                            </span>
                        </div>
                        <div class="flex justify-between items-center py-1.5 border-b border-gray-100">
                            <span class="text-gray-600">P&L Latente (posición actual)</span>
                            <span class="font-bold {{ ($pnlLatente ?? 0) >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                                @if($pnlLatente !== null)
                                    {{ $pnlLatente >= 0 ? '+' : '' }}{{ number_format($pnlLatente, 2, ',', '.') }}€
                                @else
                                    N/D
                                @endif
                            </span>
                        </div>
                        <div class="flex justify-between items-center py-1.5 border-b border-gray-100">
                            <span class="text-gray-600">Dividendos cobrados (neto)</span>
                            <span class="font-bold text-teal-600">+{{ number_format($totalDivNeto, 2, ',', '.') }}€</span>
                        </div>
                        <div class="flex justify-between items-center py-2 mt-1 rounded-lg {{ $trPos ? 'bg-emerald-50' : 'bg-red-50' }} px-3">
                            <span class="font-black text-gray-800 uppercase text-xs tracking-wide">Return Total</span>
                            <span class="text-2xl font-black {{ $trPos ? 'text-emerald-700' : 'text-red-700' }}">
                                {{ $totalReturn >= 0 ? '+' : '' }}{{ number_format($totalReturn, 2, ',', '.') }}€
                            </span>
                        </div>
                        @if($totalInvertido > 0)
                        <p class="text-xs text-gray-400 text-right">
                            ROI sobre capital invertido:
                            <span class="font-bold {{ $trPos ? 'text-emerald-600' : 'text-red-600' }}">
                                {{ $totalReturn >= 0 ? '+' : '' }}{{ number_format(($totalReturn / $totalInvertido) * 100, 2, ',', '.') }}%
                            </span>
                        </p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ── HISTORIAL DE OPERACIONES ── --}}
            <div class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-100">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="font-bold text-gray-800">Historial de Operaciones</h3>
                </div>
                @if($operaciones->isEmpty())
                    <div class="p-8 text-center text-gray-400 text-sm">Sin operaciones registradas.</div>
                @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="bg-gray-50 border-b text-gray-600 text-xs uppercase tracking-widest font-bold">
                                <th class="px-4 py-3">Fecha</th>
                                <th class="px-4 py-3 text-center">Tipo</th>
                                <th class="px-4 py-3 text-right">Unidades</th>
                                <th class="px-4 py-3 text-right">Precio Unit.</th>
                                <th class="px-4 py-3 text-right">Comisión</th>
                                <th class="px-4 py-3 text-right">Importe Neto</th>
                                <th class="px-4 py-3 text-right">P&L FIFO</th>
                                <th class="px-4 py-3">Notas</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($operaciones as $op)
                            @php $isCompra = $op->tipo === 'compra'; @endphp
                            <tr class="hover:bg-gray-50/50 {{ $isCompra ? '' : 'bg-red-50/20' }}">
                                <td class="px-4 py-3 text-gray-500 text-xs font-mono">
                                    {{ $op->fecha->format('d/m/Y') }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($isCompra)
                                        <span class="px-2 py-0.5 bg-emerald-100 text-emerald-700 rounded-full text-[10px] font-black uppercase">Compra</span>
                                    @else
                                        <span class="px-2 py-0.5 bg-red-100 text-red-700 rounded-full text-[10px] font-black uppercase">Venta</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right font-mono text-gray-700">
                                    {{ number_format((float)$op->cantidad, 4, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 text-right font-mono text-gray-700">
                                    {{ number_format((float)$op->precio_unitario, 4, ',', '.') }}€
                                </td>
                                <td class="px-4 py-3 text-right text-orange-600 font-medium">
                                    {{ number_format((float)$op->comision, 2, ',', '.') }}€
                                </td>
                                <td class="px-4 py-3 text-right font-bold text-gray-900">
                                    {{ number_format($op->importe_neto, 2, ',', '.') }}€
                                </td>
                                <td class="px-4 py-3 text-right">
                                    @if(!$isCompra && isset($op->pnl))
                                        <span class="font-bold {{ $op->pnl >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                                            {{ $op->pnl >= 0 ? '+' : '' }}{{ number_format($op->pnl, 2, ',', '.') }}€
                                        </span>
                                    @else
                                        <span class="text-gray-300 text-xs">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-400">{{ $op->notas ?? '' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>

            {{-- ── HISTORIAL DE DIVIDENDOS ── --}}
            <div class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-100">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="font-bold text-gray-800">Historial de Dividendos</h3>
                    @if($dividendos->count() > 0)
                    <span class="text-sm text-teal-600 font-semibold">
                        {{ number_format($totalDivBruto, 2, ',', '.') }}€ bruto cobrado
                    </span>
                    @endif
                </div>
                @if($dividendos->isEmpty())
                    <div class="p-8 text-center text-gray-400 text-sm">Sin dividendos registrados para este activo.</div>
                @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="bg-gray-50 border-b text-gray-600 text-xs uppercase tracking-widest font-bold">
                                <th class="px-4 py-3">Fecha</th>
                                <th class="px-4 py-3 text-right">Bruto</th>
                                <th class="px-4 py-3 text-right">Retención</th>
                                <th class="px-4 py-3 text-right">% Ret.</th>
                                <th class="px-4 py-3 text-right">Neto</th>
                                <th class="px-4 py-3">Notas</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($dividendos as $div)
                            <tr class="hover:bg-gray-50/50">
                                <td class="px-4 py-3 text-gray-500 text-xs font-mono">{{ $div->fecha->format('d/m/Y') }}</td>
                                <td class="px-4 py-3 text-right text-gray-700">{{ number_format($div->monto_bruto, 2, ',', '.') }}€</td>
                                <td class="px-4 py-3 text-right text-orange-600">− {{ number_format($div->retencion, 2, ',', '.') }}€</td>
                                <td class="px-4 py-3 text-right text-xs text-gray-500">
                                    {{ $div->monto_bruto > 0 ? number_format(($div->retencion / $div->monto_bruto) * 100, 1, ',', '.') : '0,0' }}%
                                </td>
                                <td class="px-4 py-3 text-right font-bold text-teal-700">{{ number_format($div->monto_neto, 2, ',', '.') }}€</td>
                                <td class="px-4 py-3 text-xs text-gray-400">{{ $div->notas ?? '' }}</td>
                            </tr>
                            @endforeach
                            <tr class="bg-teal-50 font-bold border-t-2 border-teal-200">
                                <td class="px-4 py-3 text-xs uppercase text-teal-700">Total</td>
                                <td class="px-4 py-3 text-right text-gray-800">{{ number_format($totalDivBruto, 2, ',', '.') }}€</td>
                                <td class="px-4 py-3 text-right text-orange-600">− {{ number_format($dividendos->sum('retencion'), 2, ',', '.') }}€</td>
                                <td class="px-4 py-3"></td>
                                <td class="px-4 py-3 text-right text-teal-700">{{ number_format($totalDivNeto, 2, ',', '.') }}€</td>
                                <td class="px-4 py-3"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
