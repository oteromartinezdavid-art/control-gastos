<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Libro de Operaciones</h2>
            <a href="#" onclick="history.back(); return false;"
               class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">← Volver</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-lg text-sm font-medium">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg text-sm">
                    @foreach($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            {{-- Resumen --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white p-5 rounded-lg shadow-sm border-b-4 border-[#1e1b4b]">
                    <p class="text-xs font-bold text-gray-500 uppercase">Capital Comprado</p>
                    <p class="text-2xl font-black text-gray-900">{{ number_format($totalInvertido, 2, ',', '.') }}€</p>
                </div>
                <div class="bg-white p-5 rounded-lg shadow-sm border-b-4 border-blue-400">
                    <p class="text-xs font-bold text-blue-600 uppercase">Capital Vendido</p>
                    <p class="text-2xl font-black text-blue-700">{{ number_format($totalVendido, 2, ',', '.') }}€</p>
                </div>
                {{-- Comisiones (bancaria + bolsa + divisa) --}}
                @php $totalComisionesSolo = $desglosGastos['bancaria'] + $desglosGastos['bolsa'] + $desglosGastos['divisa']; @endphp
                <div class="bg-white p-5 rounded-lg shadow-sm border-b-4 border-orange-400">
                    <p class="text-xs font-bold text-orange-600 uppercase mb-1">Comisiones</p>
                    <p class="text-2xl font-black text-orange-700">{{ number_format($totalComisionesSolo, 2, ',', '.') }}€</p>
                    <div class="mt-3 space-y-1">
                        <div class="flex justify-between text-xs text-gray-500">
                            <span>Bancaria</span>
                            <span class="font-semibold text-orange-600">{{ number_format($desglosGastos['bancaria'], 2, ',', '.') }}€</span>
                        </div>
                        <div class="flex justify-between text-xs text-gray-500">
                            <span>Bolsa</span>
                            <span class="font-semibold text-orange-600">{{ number_format($desglosGastos['bolsa'], 2, ',', '.') }}€</span>
                        </div>
                        <div class="flex justify-between text-xs text-gray-500">
                            <span>Cambio divisa</span>
                            <span class="font-semibold text-orange-600">{{ number_format($desglosGastos['divisa'], 2, ',', '.') }}€</span>
                        </div>
                    </div>
                </div>
                {{-- Impuestos --}}
                <div class="bg-white p-5 rounded-lg shadow-sm border-b-4 border-red-400">
                    <p class="text-xs font-bold text-red-600 uppercase mb-1">Impuestos</p>
                    <p class="text-2xl font-black text-red-700">{{ number_format($desglosGastos['impuestos'], 2, ',', '.') }}€</p>
                    <p class="text-xs text-gray-400 mt-3">Retenciones y tributos sobre operaciones</p>
                </div>
            </div>

            {{-- Formulario nueva operación — colapsable --}}
            <div x-data="{
                    open: {{ $errors->any() ? 'true' : 'false' }},
                    tipo: '{{ old('tipo', 'compra') }}',
                    cantidad: '',
                    precio: '',
                    com_bancaria: '{{ old('comision', 0) }}',
                    com_bolsa: '{{ old('comision_bolsa', 0) }}',
                    impuestos: '{{ old('impuestos', 0) }}',
                    com_divisa: '{{ old('comision_divisa', 0) }}',
                    get bruto() { return (parseFloat(this.cantidad)||0) * (parseFloat(this.precio)||0); },
                    get totalGastos() {
                        return (parseFloat(this.com_bancaria)||0)+(parseFloat(this.com_bolsa)||0)+(parseFloat(this.impuestos)||0)+(parseFloat(this.com_divisa)||0);
                    },
                    get neto() { return this.tipo==='compra' ? this.bruto+this.totalGastos : this.bruto-this.totalGastos; }
                 }"
                 class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">

                {{-- Toggle header --}}
                <button type="button" @click="open = !open"
                    class="w-full flex items-center justify-between px-6 py-4 text-left hover:bg-gray-50 transition">
                    <div class="flex items-center gap-3">
                        <span class="text-lg font-bold text-gray-800">+ Registrar Nueva Operación</span>
                    </div>
                    <svg class="h-5 w-5 text-gray-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <div x-show="open" x-collapse class="border-t border-gray-100 px-6 pb-6 pt-4">
                    <form action="{{ route('inversiones.operaciones.store') }}" method="POST">
                        @csrf
                        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4 mb-4">

                            <div class="lg:col-span-2">
                                <x-input-label for="activo_id" value="Activo" />
                                <select name="activo_id" id="activo_id"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm"
                                    required>
                                    <option value="">— Selecciona —</option>
                                    @foreach($activos as $activo)
                                        <option value="{{ $activo->id }}" {{ old('activo_id', $request->activo_id) == $activo->id ? 'selected' : '' }}>
                                            {{ $activo->ticker }} – {{ $activo->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <x-input-label value="Tipo" />
                                <div class="mt-2 flex gap-3">
                                    <label class="flex items-center gap-1.5 text-sm font-medium cursor-pointer"
                                           :class="tipo==='compra'?'text-emerald-700':'text-gray-500'">
                                        <input type="radio" name="tipo" value="compra" x-model="tipo"
                                               {{ old('tipo','compra')==='compra'?'checked':'' }} /> Compra
                                    </label>
                                    <label class="flex items-center gap-1.5 text-sm font-medium cursor-pointer"
                                           :class="tipo==='venta'?'text-red-600':'text-gray-500'">
                                        <input type="radio" name="tipo" value="venta" x-model="tipo"
                                               {{ old('tipo')==='venta'?'checked':'' }} /> Venta
                                    </label>
                                </div>
                            </div>

                            <div>
                                <x-input-label for="fecha" value="Fecha" />
                                <x-text-input id="fecha" name="fecha" type="date" class="mt-1 block w-full text-sm"
                                    value="{{ old('fecha', date('Y-m-d')) }}" required />
                            </div>

                            <div>
                                <x-input-label for="cantidad" value="Unidades" />
                                <x-text-input id="cantidad" name="cantidad" type="number" step="0.0001" min="0.0001"
                                    class="mt-1 block w-full text-sm" x-model="cantidad" value="{{ old('cantidad') }}" required />
                            </div>

                            <div>
                                <x-input-label for="precio_unitario" value="Precio Unit. (€)" />
                                <x-text-input id="precio_unitario" name="precio_unitario" type="number" step="0.0001" min="0"
                                    class="mt-1 block w-full text-sm" x-model="precio" value="{{ old('precio_unitario') }}" required />
                            </div>
                        </div>

                        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4 mb-4">
                            <div class="col-span-2 md:col-span-4 lg:col-span-6">
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Gastos de la operación</p>
                            </div>
                            <div>
                                <x-input-label for="comision" value="Com. Bancaria (€)" />
                                <x-text-input id="comision" name="comision" type="number" step="0.01" min="0"
                                    class="mt-1 block w-full text-sm" x-model="com_bancaria" value="{{ old('comision', 0) }}" />
                            </div>
                            <div>
                                <x-input-label for="comision_bolsa" value="Com. Bolsa (€)" />
                                <x-text-input id="comision_bolsa" name="comision_bolsa" type="number" step="0.01" min="0"
                                    class="mt-1 block w-full text-sm" x-model="com_bolsa" value="{{ old('comision_bolsa', 0) }}" />
                            </div>
                            <div>
                                <x-input-label for="impuestos" value="Impuestos (€)" />
                                <x-text-input id="impuestos" name="impuestos" type="number" step="0.01" min="0"
                                    class="mt-1 block w-full text-sm" x-model="impuestos" value="{{ old('impuestos', 0) }}" />
                            </div>
                            <div>
                                <x-input-label for="comision_divisa" value="Com. Divisa (€)" />
                                <x-text-input id="comision_divisa" name="comision_divisa" type="number" step="0.01" min="0"
                                    class="mt-1 block w-full text-sm" x-model="com_divisa" value="{{ old('comision_divisa', 0) }}" />
                            </div>
                            <div>
                                <x-input-label for="notas" value="Notas" />
                                <x-text-input id="notas" name="notas" type="text"
                                    class="mt-1 block w-full text-sm" value="{{ old('notas') }}" placeholder="Broker..." />
                            </div>
                            <div class="flex flex-col justify-end">
                                <div class="text-[10px] text-gray-400 mb-1">
                                    Gastos: <span class="font-bold text-orange-600" x-text="totalGastos.toFixed(2)+'€'"></span>
                                    · Neto: <span class="font-bold text-indigo-700" x-text="neto.toFixed(2)+'€'"></span>
                                </div>
                                <button type="submit"
                                    class="w-full px-4 py-2 text-white text-sm font-bold rounded-lg transition"
                                    :class="tipo==='venta'?'bg-red-600 hover:bg-red-700':'bg-[#1e1b4b] hover:bg-indigo-800'"
                                    x-text="tipo==='compra'?'Registrar Compra':'Registrar Venta'">
                                    Registrar Compra
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Tabla operaciones — full width --}}
            <div class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-100">

                    {{-- Filtros --}}
                    <form method="GET" action="{{ route('inversiones.operaciones.index') }}"
                          class="p-4 border-b border-gray-100 flex flex-wrap gap-3 items-center">
                        <select name="anio"
                            class="border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 font-semibold">
                            <option value="">Todos los años</option>
                            @foreach($aniosDisponibles as $anio)
                                <option value="{{ $anio }}" {{ $request->anio == $anio ? 'selected' : '' }}>
                                    {{ $anio }}
                                </option>
                            @endforeach
                        </select>
                        <select name="activo_id"
                            class="border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500">
                            <option value="">Todos los activos</option>
                            @foreach($activos as $activo)
                                <option value="{{ $activo->id }}" {{ $request->activo_id == $activo->id ? 'selected' : '' }}>
                                    {{ $activo->ticker }}
                                </option>
                            @endforeach
                        </select>
                        <select name="tipo"
                            class="border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500">
                            <option value="">Todas las operaciones</option>
                            <option value="compra" {{ $request->tipo === 'compra' ? 'selected' : '' }}>Solo Compras</option>
                            <option value="venta"  {{ $request->tipo === 'venta'  ? 'selected' : '' }}>Solo Ventas</option>
                        </select>
                        <button type="submit"
                            class="px-3 py-1.5 bg-[#1e1b4b] text-white rounded-md text-sm font-medium">
                            Filtrar
                        </button>
                        @if($request->filled('activo_id') || $request->filled('tipo') || $request->filled('anio'))
                            <a href="{{ route('inversiones.operaciones.index') }}"
                               class="px-3 py-1.5 bg-gray-100 text-gray-600 rounded-md text-sm font-medium">Limpiar</a>
                        @endif
                    </form>

                    {{-- Banner fiscal (solo cuando se filtra por año) --}}
                    @if($request->filled('anio'))
                    @php
                        $pnlPos        = $pnlPeriodo >= 0;
                        $baseAhorro    = $pnlPeriodo + $totalDivBruto;
                        $retencionTotal = $totalDivRetencion;
                    @endphp
                    <div class="mx-4 my-3 rounded-xl border-2 border-indigo-200 bg-indigo-50 overflow-hidden">
                        {{-- Cabecera --}}
                        <div class="bg-[#1e1b4b] px-5 py-3 flex items-center justify-between">
                            <div>
                                <p class="text-white font-black text-sm uppercase tracking-widest">
                                    Resumen Fiscal {{ $request->anio }} · IRPF — Base del Ahorro
                                </p>
                                <p class="text-indigo-300 text-xs mt-0.5">Datos calculados con método FIFO según normativa española</p>
                            </div>
                            <button onclick="window.print()"
                                    class="flex items-center gap-1.5 px-3 py-1.5 bg-[#f97316] hover:bg-orange-500 text-white rounded-lg text-xs font-bold transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                </svg>
                                Imprimir Resumen
                            </button>
                        </div>

                        {{-- Dos columnas: ganancias + dividendos --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 divide-y md:divide-y-0 md:divide-x divide-indigo-200">

                            {{-- Columna A: Ganancias/Pérdidas Patrimoniales --}}
                            <div class="p-5">
                                <p class="text-[10px] font-black uppercase tracking-widest text-indigo-500 mb-1">
                                    A · Ganancias y Pérdidas Patrimoniales
                                </p>
                                <p class="text-xs text-gray-500 mb-3">Transmisión de valores · Casillas 1624–1631 del modelo 100</p>
                                <p class="text-3xl font-black {{ $pnlPos ? 'text-emerald-700' : 'text-red-700' }}">
                                    {{ $pnlPeriodo >= 0 ? '+' : '' }}{{ number_format($pnlPeriodo, 2, ',', '.') }}€
                                </p>
                                <p class="text-xs mt-1 {{ $pnlPos ? 'text-emerald-600' : 'text-red-600' }} font-semibold">
                                    {{ $pnlPos ? 'Ganancia patrimonial neta' : 'Pérdida patrimonial neta' }}
                                    ({{ $operaciones->where('tipo','venta')->count() }} ventas)
                                </p>
                            </div>

                            {{-- Columna B: Rendimientos del Capital Mobiliario --}}
                            <div class="p-5">
                                <p class="text-[10px] font-black uppercase tracking-widest text-indigo-500 mb-1">
                                    B · Rendimientos del Capital Mobiliario
                                </p>
                                <p class="text-xs text-gray-500 mb-3">Dividendos · Casillas 0029–0031 del modelo 100</p>
                                <p class="text-3xl font-black text-teal-700">
                                    {{ number_format($totalDivBruto, 2, ',', '.') }}€
                                    <span class="text-base font-semibold text-gray-500">bruto</span>
                                </p>
                                <div class="flex gap-4 mt-1 text-xs">
                                    <span class="text-orange-600 font-semibold">
                                        − {{ number_format($totalDivRetencion, 2, ',', '.') }}€ retención
                                    </span>
                                    <span class="text-teal-600 font-semibold">
                                        = {{ number_format($totalDivNeto, 2, ',', '.') }}€ neto
                                    </span>
                                </div>
                                <p class="text-xs mt-1 text-gray-400">({{ $dividendosAnio->count() }} cobros registrados)</p>
                            </div>
                        </div>

                        {{-- Fila de totales --}}
                        <div class="border-t border-indigo-200 bg-white px-5 py-3 flex items-center justify-between">
                            <p class="text-xs font-bold text-gray-600 uppercase tracking-wide">
                                Base del Ahorro Total (A + B bruto)
                            </p>
                            <p class="text-xl font-black {{ ($baseAhorro) >= 0 ? 'text-emerald-700' : 'text-red-700' }}">
                                {{ $baseAhorro >= 0 ? '+' : '' }}{{ number_format($baseAhorro, 2, ',', '.') }}€
                            </p>
                        </div>
                    </div>
                    @endif

                    @if($operaciones->isEmpty())
                        <div class="p-12 text-center text-gray-400">
                            <p class="font-bold">Sin operaciones registradas</p>
                            <p class="text-sm mt-1">Añade tu primera compra usando el formulario.</p>
                        </div>
                    @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead>
                                <tr class="bg-gray-50 border-b text-gray-600 text-xs uppercase tracking-widest font-bold">
                                    <th class="px-3 py-3">Fecha</th>
                                    <th class="px-3 py-3">Activo</th>
                                    <th class="px-3 py-3 text-center">Tipo</th>
                                    <th class="px-3 py-3 text-right">Uds.</th>
                                    <th class="px-3 py-3 text-right">P. Unit.</th>
                                    <th class="px-3 py-3 text-right text-orange-500">Com. Banc.</th>
                                    <th class="px-3 py-3 text-right text-orange-500">Com. Bolsa</th>
                                    <th class="px-3 py-3 text-right text-orange-500">Impuestos</th>
                                    <th class="px-3 py-3 text-right text-orange-500">Com. Divisa</th>
                                    <th class="px-3 py-3 text-right text-orange-700">Total Gastos</th>
                                    <th class="px-3 py-3 text-right">Neto</th>
                                    <th class="px-3 py-3 text-right">P&L FIFO</th>
                                    <th class="px-3 py-3 text-center">Acc.</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($operaciones as $op)
                                @php
                                    $isCompra = $op->tipo === 'compra';
                                @endphp
                                <tr class="hover:bg-gray-50/50 {{ $isCompra ? '' : 'bg-red-50/20' }}">
                                    <td class="px-3 py-3 text-gray-500 text-xs font-mono whitespace-nowrap">
                                        {{ $op->fecha->format('d/m/Y') }}
                                    </td>
                                    <td class="px-3 py-3">
                                        <a href="{{ route('inversiones.activos.show', $op->activo_id) }}"
                                           class="font-black text-indigo-700 hover:underline">{{ $op->activo->ticker }}</a>
                                        @if($op->notas)
                                            <span class="block text-[10px] text-gray-400 truncate max-w-[80px]">{{ $op->notas }}</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-3 text-center">
                                        @if($isCompra)
                                            <span class="px-2 py-0.5 bg-emerald-100 text-emerald-700 rounded-full text-[10px] font-black uppercase">Compra</span>
                                        @else
                                            <span class="px-2 py-0.5 bg-red-100 text-red-700 rounded-full text-[10px] font-black uppercase">Venta</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-3 text-right font-mono text-gray-700 text-xs">
                                        {{ number_format((float)$op->cantidad, 4, ',', '.') }}
                                    </td>
                                    <td class="px-3 py-3 text-right font-mono text-gray-700 text-xs">
                                        {{ number_format((float)$op->precio_unitario, 4, ',', '.') }}€
                                    </td>
                                    <td class="px-3 py-3 text-right text-orange-400 font-mono text-xs">
                                        {{ (float)$op->comision > 0 ? number_format((float)$op->comision,2,',','.').'€' : '—' }}
                                    </td>
                                    <td class="px-3 py-3 text-right text-orange-400 font-mono text-xs">
                                        {{ (float)$op->comision_bolsa > 0 ? number_format((float)$op->comision_bolsa,2,',','.').'€' : '—' }}
                                    </td>
                                    <td class="px-3 py-3 text-right text-orange-400 font-mono text-xs">
                                        {{ (float)$op->impuestos > 0 ? number_format((float)$op->impuestos,2,',','.').'€' : '—' }}
                                    </td>
                                    <td class="px-3 py-3 text-right text-orange-400 font-mono text-xs">
                                        {{ (float)$op->comision_divisa > 0 ? number_format((float)$op->comision_divisa,2,',','.').'€' : '—' }}
                                    </td>
                                    <td class="px-3 py-3 text-right text-orange-700 font-bold text-xs">
                                        {{ number_format($op->total_gastos,2,',','.')}}€
                                    </td>
                                    <td class="px-3 py-3 text-right font-bold text-gray-900 text-xs">
                                        {{ number_format($op->importe_neto, 2, ',', '.') }}€
                                    </td>
                                    <td class="px-3 py-3 text-right">
                                        @if($op->tipo === 'venta' && isset($op->pnl))
                                            @php $pnlSign = $op->pnl >= 0 ? '+' : ''; @endphp
                                            <span class="font-bold text-xs {{ $op->pnl >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                                                {{ $pnlSign }}{{ number_format($op->pnl, 2, ',', '.') }}€
                                            </span>
                                        @else
                                            <span class="text-gray-300 text-xs">—</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-3 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="{{ route('inversiones.operaciones.edit', $op->id) }}"
                                               class="text-indigo-400 hover:text-indigo-700 transition" title="Editar">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </a>
                                            <form action="{{ route('inversiones.operaciones.destroy', $op->id) }}" method="POST"
                                                  onsubmit="return confirm('¿Eliminar esta operación? El cálculo FIFO se recalculará.')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-red-400 hover:text-red-700 transition" title="Eliminar">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>

            {{-- Nota metodológica --}}
            <div class="bg-indigo-50 border border-indigo-100 rounded-lg p-4 text-sm text-indigo-700">
                <span class="font-bold">Método FIFO:</span>
                En ventas parciales, el coste se imputa comenzando por los lotes adquiridos en la fecha más antigua.
                El P&L FIFO = Valor de Transmisión (precio venta × unidades − comisión) − Coste de Adquisición (precio compra × unidades + comisión proporcional).
            </div>

        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- INFORME FISCAL IMPRIMIBLE (solo visible al imprimir)         --}}
    {{-- ============================================================ --}}
    @if($request->filled('anio'))
    @php
        $pnlPos     = $pnlPeriodo >= 0;
        $baseAhorro = $pnlPeriodo + $totalDivBruto;
        $nVentas    = $ventasDetalle->count();
        $nCompras   = $comprasAnio->count();
    @endphp
    <div id="informe-fiscal-print">

        {{-- ── CABECERA ── --}}
        <div class="fi-header">
            <div class="fi-header-top">
                <div>
                    <h1>Informe Fiscal · Declaración de la Renta {{ $request->anio }}</h1>
                    <p>Contribuyente: <strong>{{ Auth::user()->name }}</strong> &nbsp;·&nbsp; {{ Auth::user()->email }}</p>
                </div>
                <div class="fi-header-meta">
                    <p>Generado: {{ now()->format('d/m/Y H:i') }}</p>
                    <p>Método: <strong>FIFO</strong> (Art. 37.2 LIRPF)</p>
                    <p>Control de Gastos</p>
                </div>
            </div>
            <p class="fi-disclaimer-top">
                Documento preparado para gestoría. Los importes se calculan según el método FIFO conforme al
                Art. 37.2 de la Ley 35/2006 del IRPF. Datos orientativos — verificar con asesor fiscal.
            </p>
        </div>

        {{-- ── RESUMEN EJECUTIVO ── --}}
        <div class="fi-summary">
            <div class="fi-kpi {{ $pnlPos ? 'fi-kpi-green' : 'fi-kpi-red' }}">
                <p class="fi-kpi-label">A · Ganancias / Pérdidas Patrimoniales</p>
                <p class="fi-kpi-ref">Transmisión de valores negociados — Casillas 1624–1631 (Modelo 100)</p>
                <p class="fi-kpi-value">{{ $pnlPeriodo >= 0 ? '+' : '' }}{{ number_format($pnlPeriodo, 2, ',', '.') }}€</p>
                <p class="fi-kpi-sub">{{ $nVentas }} transmisión(es) · método FIFO</p>
            </div>
            <div class="fi-kpi fi-kpi-teal">
                <p class="fi-kpi-label">B · Rendimientos del Capital Mobiliario</p>
                <p class="fi-kpi-ref">Dividendos de acciones — Casillas 0029–0031 (Modelo 100)</p>
                <p class="fi-kpi-value">{{ number_format($totalDivBruto, 2, ',', '.') }}€ <span style="font-size:9pt;font-weight:normal">bruto</span></p>
                <p class="fi-kpi-sub">Retención: {{ number_format($totalDivRetencion, 2, ',', '.') }}€ &nbsp;·&nbsp; Neto: {{ number_format($totalDivNeto, 2, ',', '.') }}€ &nbsp;·&nbsp; {{ $dividendosAnio->count() }} cobro(s)</p>
            </div>
            <div class="fi-kpi fi-kpi-total">
                <p class="fi-kpi-label">Base del Ahorro — Total (A + B bruto)</p>
                <p class="fi-kpi-ref">Resultado combinado sujeto a tributación en IRPF</p>
                <p class="fi-kpi-value">{{ $baseAhorro >= 0 ? '+' : '' }}{{ number_format($baseAhorro, 2, ',', '.') }}€</p>
                <p class="fi-kpi-sub">Tipos aplicables: 19% hasta 6.000€ · 21% de 6.000 a 50.000€ · 23% de 50.000 a 200.000€ · 27% más de 200.000€</p>
            </div>
        </div>

        {{-- ── SECCIÓN A: VENTAS CON DESGLOSE FIFO ── --}}
        <div class="fi-section-break">
            <h2 class="fi-section-title">
                A — Ganancias y Pérdidas Patrimoniales · Transmisión de Valores
                <span class="fi-section-subtitle">Art. 35 y 37.2 LIRPF — Base del Ahorro</span>
            </h2>

            @if($ventasDetalle->isEmpty())
                <p class="fi-empty">Sin transmisiones de valores registradas en el ejercicio {{ $request->anio }}.</p>
            @else
                @foreach($ventasDetalle as $idx => $vd)
                @php
                    $op  = $vd['operacion'];
                    $isG = $vd['pnl'] >= 0;
                @endphp
                <div class="fi-op-block">
                    <div class="fi-op-header {{ $isG ? 'fi-op-gain' : 'fi-op-loss' }}">
                        <span>Transmisión {{ $idx + 1 }} · <strong>{{ $op->activo->ticker }}</strong> — {{ $op->activo->nombre }}</span>
                        <span>{{ $op->fecha->format('d/m/Y') }}</span>
                    </div>

                    {{-- Fila de transmisión --}}
                    <table class="fi-table fi-table-compact">
                        <thead>
                            <tr>
                                <th>Concepto</th>
                                <th class="tr">Unidades</th>
                                <th class="tr">Precio Unit.</th>
                                <th class="tr">Comisión Venta</th>
                                <th class="tr">Valor Transmisión</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Venta — {{ $op->notas ?: 'Transmisión de acciones' }}</td>
                                <td class="tr">{{ number_format((float)$op->cantidad, 4, ',', '.') }}</td>
                                <td class="tr">{{ number_format((float)$op->precio_unitario, 4, ',', '.') }}€</td>
                                <td class="tr fi-loss" title="Bancaria: {{ number_format((float)$op->comision,2,',','.') }}€ | Bolsa: {{ number_format((float)$op->comision_bolsa,2,',','.') }}€ | Impuestos: {{ number_format((float)$op->impuestos,2,',','.') }}€ | Divisa: {{ number_format((float)$op->comision_divisa,2,',','.') }}€">− {{ number_format($op->total_gastos, 2, ',', '.') }}€</td>
                                <td class="tr"><strong>{{ number_format($vd['valor_transmision'], 2, ',', '.') }}€</strong></td>
                            </tr>
                        </tbody>
                    </table>

                    {{-- Desglose de lotes FIFO --}}
                    <p class="fi-sublabel">Coste de adquisición — lotes consumidos por orden FIFO:</p>
                    <table class="fi-table fi-table-fifo">
                        <thead>
                            <tr>
                                <th>Fecha Compra</th>
                                <th class="tr">Unidades Imputadas</th>
                                <th class="tr">Precio Compra Unit.</th>
                                <th class="tr">Comisión Imputada</th>
                                <th class="tr">Coste Lote</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($vd['lotes_consumidos'] as $lote)
                            <tr>
                                <td>{{ $lote['fecha_compra'] }}</td>
                                <td class="tr">{{ number_format($lote['cantidad'], 4, ',', '.') }}</td>
                                <td class="tr">{{ number_format($lote['precio_unitario'], 4, ',', '.') }}€</td>
                                <td class="tr">{{ number_format($lote['comision_proporcional'], 2, ',', '.') }}€</td>
                                <td class="tr">{{ number_format($lote['coste_total'], 2, ',', '.') }}€</td>
                            </tr>
                            @endforeach
                            <tr class="fi-subtotal-row">
                                <td colspan="4"><strong>Total Coste de Adquisición (FIFO)</strong></td>
                                <td class="tr"><strong>{{ number_format($vd['coste_adquisicion'], 2, ',', '.') }}€</strong></td>
                            </tr>
                        </tbody>
                    </table>

                    {{-- Resultado de la operación --}}
                    <div class="fi-op-result {{ $isG ? 'fi-result-gain' : 'fi-result-loss' }}">
                        <span>
                            Valor Transmisión <strong>{{ number_format($vd['valor_transmision'], 2, ',', '.') }}€</strong>
                            − Coste Adquisición <strong>{{ number_format($vd['coste_adquisicion'], 2, ',', '.') }}€</strong>
                        </span>
                        <span class="fi-op-pnl">
                            {{ $isG ? 'GANANCIA' : 'PÉRDIDA' }}:
                            <strong>{{ $vd['pnl'] >= 0 ? '+' : '' }}{{ number_format($vd['pnl'], 2, ',', '.') }}€</strong>
                        </span>
                    </div>
                </div>
                @endforeach

                {{-- Total sección A --}}
                <div class="fi-section-total {{ $pnlPos ? 'fi-total-green' : 'fi-total-red' }}">
                    <span>TOTAL SECCIÓN A — Resultado Ganancias y Pérdidas Patrimoniales</span>
                    <span><strong>{{ $pnlPeriodo >= 0 ? '+' : '' }}{{ number_format($pnlPeriodo, 2, ',', '.') }}€</strong></span>
                </div>
            @endif
        </div>

        {{-- ── SECCIÓN B: COMPRAS DEL EJERCICIO (referencia gestoría) ── --}}
        @if($comprasAnio->count() > 0)
        <div class="fi-section-break">
            <h2 class="fi-section-title">
                B — Compras del Ejercicio {{ $request->anio }}
                <span class="fi-section-subtitle">Referencia para coste de adquisición de posiciones abiertas</span>
            </h2>
            <table class="fi-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Activo</th>
                        <th class="tr">Unidades</th>
                        <th class="tr">Precio Unit.</th>
                        <th class="tr">Comisión</th>
                        <th class="tr">Coste Total</th>
                        <th>Notas</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($comprasAnio as $c)
                    <tr>
                        <td>{{ $c->fecha->format('d/m/Y') }}</td>
                        <td><strong>{{ $c->activo->ticker }}</strong> — {{ $c->activo->nombre }}</td>
                        <td class="tr">{{ number_format((float)$c->cantidad, 4, ',', '.') }}</td>
                        <td class="tr">{{ number_format((float)$c->precio_unitario, 4, ',', '.') }}€</td>
                        <td class="tr">{{ number_format((float)$c->comision, 2, ',', '.') }}€</td>
                        <td class="tr"><strong>{{ number_format($c->importe_neto, 2, ',', '.') }}€</strong></td>
                        <td style="font-size:7pt;color:#666">{{ $c->notas ?? '' }}</td>
                    </tr>
                    @endforeach
                    <tr class="fi-subtotal-row">
                        <td colspan="5"><strong>Total invertido en el ejercicio</strong></td>
                        <td class="tr"><strong>{{ number_format($comprasAnio->sum('importe_neto'), 2, ',', '.') }}€</strong></td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        </div>
        @endif

        {{-- ── SECCIÓN C: DIVIDENDOS ── --}}
        <div class="fi-section-break">
            <h2 class="fi-section-title">
                C — Rendimientos del Capital Mobiliario · Dividendos
                <span class="fi-section-subtitle">Art. 25.1.a LIRPF — Base del Ahorro · Casillas 0029–0031</span>
            </h2>

            @if($dividendosAnio->isEmpty())
                <p class="fi-empty">Sin dividendos registrados en el ejercicio {{ $request->anio }}.</p>
            @else
            <table class="fi-table">
                <thead>
                    <tr>
                        <th>Fecha Cobro</th>
                        <th>Activo</th>
                        <th class="tr">Importe Bruto</th>
                        <th class="tr">Retención Fiscal</th>
                        <th class="tr">% Ret.</th>
                        <th class="tr">Importe Neto</th>
                        <th>Notas</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($dividendosAnio as $div)
                    <tr>
                        <td>{{ $div->fecha->format('d/m/Y') }}</td>
                        <td><strong>{{ $div->activo->ticker }}</strong> — {{ $div->activo->nombre }}</td>
                        <td class="tr">{{ number_format($div->monto_bruto, 2, ',', '.') }}€</td>
                        <td class="tr fi-loss">− {{ number_format($div->retencion, 2, ',', '.') }}€</td>
                        <td class="tr" style="color:#666">
                            {{ $div->monto_bruto > 0 ? number_format(($div->retencion / $div->monto_bruto) * 100, 1, ',', '.') : '0,0' }}%
                        </td>
                        <td class="tr fi-profit"><strong>{{ number_format($div->monto_neto, 2, ',', '.') }}€</strong></td>
                        <td style="font-size:7pt;color:#666">{{ $div->notas ?? '' }}</td>
                    </tr>
                    @endforeach
                    <tr class="fi-subtotal-row">
                        <td colspan="2"><strong>Totales</strong></td>
                        <td class="tr"><strong>{{ number_format($totalDivBruto, 2, ',', '.') }}€</strong></td>
                        <td class="tr fi-loss"><strong>− {{ number_format($totalDivRetencion, 2, ',', '.') }}€</strong></td>
                        <td class="tr"></td>
                        <td class="tr fi-profit"><strong>{{ number_format($totalDivNeto, 2, ',', '.') }}€</strong></td>
                        <td></td>
                    </tr>
                </tbody>
            </table>

            <div class="fi-section-total fi-total-teal" style="margin-top:6pt;">
                <span>TOTAL SECCIÓN C — Dividendos Brutos declarables (Base del Ahorro)</span>
                <span><strong>{{ number_format($totalDivBruto, 2, ',', '.') }}€</strong></span>
            </div>
            @endif
        </div>

        {{-- ── CIERRE ── --}}
        <div class="fi-closing">
            <table class="fi-table fi-table-closing">
                <tbody>
                    <tr>
                        <td><strong>A · Ganancias / Pérdidas Patrimoniales (transmisión valores)</strong></td>
                        <td class="tr {{ $pnlPos ? 'fi-profit' : 'fi-loss' }}"><strong>{{ $pnlPeriodo >= 0 ? '+' : '' }}{{ number_format($pnlPeriodo, 2, ',', '.') }}€</strong></td>
                    </tr>
                    <tr>
                        <td><strong>C · Dividendos brutos (rendimientos capital mobiliario)</strong></td>
                        <td class="tr fi-profit"><strong>+ {{ number_format($totalDivBruto, 2, ',', '.') }}€</strong></td>
                    </tr>
                    <tr class="fi-subtotal-row">
                        <td><strong>BASE DEL AHORRO TOTAL sujeta a IRPF</strong></td>
                        <td class="tr {{ $baseAhorro >= 0 ? 'fi-profit' : 'fi-loss' }}"><strong>{{ $baseAhorro >= 0 ? '+' : '' }}{{ number_format($baseAhorro, 2, ',', '.') }}€</strong></td>
                    </tr>
                    <tr style="background:#f8f7ff">
                        <td style="font-size:7.5pt;color:#555">Retenciones ya practicadas (a deducir en cuota)</td>
                        <td class="tr" style="font-size:7.5pt;color:#555">− {{ number_format($totalDivRetencion, 2, ',', '.') }}€</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="fi-footer">
            <p>
                Documento generado automáticamente con <strong>Control de Gastos</strong> el {{ now()->format('d/m/Y \a \l\a\s H:i') }}.
                Los cálculos utilizan el método FIFO (Art. 37.2 Ley 35/2006 IRPF).
                Este documento es orientativo y no sustituye el asesoramiento de un profesional fiscal.
                Verifique todos los importes con la documentación original de su broker antes de presentar la declaración.
            </p>
        </div>

    </div>
    @endif

    <style>
        #informe-fiscal-print { display: none; }

        @media print {
            /* Ocultar chrome de la página — flujo normal, sin position:fixed */
            nav,
            header,
            .py-12 { display: none !important; }

            body,
            .min-h-screen { background: white !important; }

            #informe-fiscal-print {
                display: block !important;
                position: static;
                width: 100%;
                margin: 0;
                padding: 14pt 18pt;
                font-family: Arial, sans-serif;
                font-size: 8.5pt;
                color: #111;
                background: white;
            }

            /* ── CABECERA ── */
            .fi-header { border-bottom: 2pt solid #1e1b4b; margin-bottom: 10pt; padding-bottom: 7pt; }
            .fi-header-top { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 4pt; }
            .fi-header h1 { font-size: 13pt; font-weight: bold; margin: 0 0 2pt; color: #1e1b4b; }
            .fi-header p  { font-size: 8pt; color: #444; margin: 1pt 0; }
            .fi-header-meta { text-align: right; font-size: 7.5pt; color: #666; }
            .fi-header-meta p { margin: 1pt 0; }
            .fi-disclaimer-top { font-size: 7pt; color: #888; font-style: italic; margin: 0; }

            /* ── RESUMEN ── */
            .fi-summary { display: flex; gap: 8pt; margin-bottom: 12pt; }
            .fi-kpi { flex: 1; border: 1pt solid #ddd; border-radius: 3pt; padding: 6pt; }
            .fi-kpi-green { border-left: 3.5pt solid #059669; background: #f0fdf4; }
            .fi-kpi-red   { border-left: 3.5pt solid #dc2626; background: #fef2f2; }
            .fi-kpi-teal  { border-left: 3.5pt solid #0d9488; background: #f0fdfa; }
            .fi-kpi-total { border-left: 3.5pt solid #1e1b4b; background: #f8f7ff; }
            .fi-kpi-label { font-size: 7.5pt; font-weight: bold; text-transform: uppercase; letter-spacing: 0.04em; color: #333; margin: 0; }
            .fi-kpi-ref   { font-size: 6.5pt; color: #888; margin: 1pt 0 3pt; }
            .fi-kpi-value { font-size: 13pt; font-weight: bold; margin: 0; }
            .fi-kpi-sub   { font-size: 6.5pt; color: #555; margin: 2pt 0 0; }

            /* ── SECCIONES ── */
            .fi-section-break { page-break-before: always; }
            .fi-section-title { font-size: 10pt; font-weight: bold; color: #1e1b4b; margin: 0 0 8pt; border-bottom: 1.5pt solid #1e1b4b; padding-bottom: 3pt; display: flex; justify-content: space-between; align-items: baseline; }
            .fi-section-subtitle { font-size: 7pt; font-weight: normal; color: #666; }
            .fi-sublabel { font-size: 7pt; color: #555; font-style: italic; margin: 4pt 0 2pt 2pt; }
            .fi-empty { font-size: 8pt; color: #999; font-style: italic; margin: 6pt 0; }

            /* ── BLOQUE POR OPERACIÓN ── */
            .fi-op-block { page-break-inside: avoid; margin-bottom: 10pt; border: 1pt solid #e2e8f0; border-radius: 3pt; overflow: hidden; }
            .fi-op-header { display: flex; justify-content: space-between; padding: 4pt 8pt; font-size: 8pt; font-weight: bold; }
            .fi-op-gain { background: #f0fdf4; color: #166534; border-bottom: 1pt solid #bbf7d0; }
            .fi-op-loss { background: #fef2f2; color: #991b1b; border-bottom: 1pt solid #fecaca; }
            .fi-op-result { display: flex; justify-content: space-between; align-items: center; padding: 4pt 8pt; font-size: 8pt; border-top: 1pt solid #e2e8f0; }
            .fi-result-gain { background: #f0fdf4; }
            .fi-result-loss { background: #fef2f2; }
            .fi-op-pnl { font-size: 10pt; }

            /* ── TABLAS ── */
            .fi-table { width: 100%; border-collapse: collapse; margin-bottom: 0; font-size: 7.5pt; }
            .fi-table-compact { margin-bottom: 0; }
            .fi-table-fifo { background: #fafafa; }
            .fi-table-closing { border: 1.5pt solid #1e1b4b; }
            .fi-table th { background: #334155; color: white; padding: 3pt 6pt; text-align: left; font-size: 7pt; }
            .fi-table-fifo th { background: #64748b; }
            .fi-table-closing th { background: #1e1b4b; font-size: 8pt; }
            .fi-table td { padding: 2.5pt 6pt; border-bottom: 0.5pt solid #e5e7eb; vertical-align: middle; }
            .fi-table tr:nth-child(even) td { background: #f8fafc; }
            .fi-subtotal-row td { background: #e2e8f0 !important; border-top: 1pt solid #94a3b8; font-weight: bold; }
            .tr { text-align: right; }

            /* ── TOTALES DE SECCIÓN ── */
            .fi-section-total { display: flex; justify-content: space-between; align-items: center; padding: 5pt 8pt; border-radius: 3pt; font-size: 8.5pt; margin-top: 4pt; }
            .fi-total-green { background: #dcfce7; border: 1pt solid #86efac; color: #166534; }
            .fi-total-red   { background: #fee2e2; border: 1pt solid #fca5a5; color: #991b1b; }
            .fi-total-teal  { background: #ccfbf1; border: 1pt solid #5eead4; color: #134e4a; }

            /* ── CIERRE ── */
            .fi-closing { page-break-inside: avoid; margin-top: 14pt; border: 2pt solid #1e1b4b; border-radius: 4pt; overflow: hidden; }

            /* ── COLORES ── */
            .fi-profit { color: #059669; }
            .fi-loss   { color: #dc2626; }

            /* ── PIE ── */
            .fi-footer { margin-top: 12pt; border-top: 0.5pt solid #cbd5e1; padding-top: 5pt; font-size: 6.5pt; color: #94a3b8; text-align: center; }
        }
    </style>

</x-app-layout>
