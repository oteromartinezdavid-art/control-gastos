<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="#" onclick="history.back(); return false;" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">← Volver</a>
            <span class="text-gray-300">|</span>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Editar Operación — {{ $operacion->activo->ticker }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8">

                @if($errors->any())
                    <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4 text-sm text-red-700">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST"
                      action="{{ route('inversiones.operaciones.update', $operacion) }}"
                      x-data="{
                          tipo: '{{ old('tipo', $operacion->tipo) }}',
                          cantidad: '{{ old('cantidad', $operacion->cantidad) }}',
                          precio: '{{ old('precio_unitario', $operacion->precio_unitario) }}',
                          com_bancaria: '{{ old('comision', $operacion->comision) }}',
                          com_bolsa: '{{ old('comision_bolsa', $operacion->comision_bolsa) }}',
                          impuestos: '{{ old('impuestos', $operacion->impuestos) }}',
                          com_divisa: '{{ old('comision_divisa', $operacion->comision_divisa) }}',
                          get bruto() { return (parseFloat(this.cantidad)||0) * (parseFloat(this.precio)||0); },
                          get totalGastos() {
                              return (parseFloat(this.com_bancaria)||0)
                                   + (parseFloat(this.com_bolsa)||0)
                                   + (parseFloat(this.impuestos)||0)
                                   + (parseFloat(this.com_divisa)||0);
                          },
                          get neto() {
                              return this.tipo === 'compra'
                                  ? this.bruto + this.totalGastos
                                  : this.bruto - this.totalGastos;
                          }
                      }">
                    @csrf
                    @method('PUT')

                    {{-- Activo (solo lectura, no se cambia) --}}
                    <div class="mb-6 p-4 bg-gray-50 rounded-lg flex items-center gap-4">
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wider font-bold">Activo</p>
                            <p class="text-lg font-black text-gray-900">{{ $operacion->activo->ticker }}</p>
                            <p class="text-xs text-gray-500">{{ $operacion->activo->nombre }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-6 mb-6">
                        {{-- Tipo --}}
                        <div>
                            <x-input-label for="tipo" value="Tipo" />
                            <select id="tipo" name="tipo" x-model="tipo"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                                <option value="compra" :selected="tipo === 'compra'">Compra</option>
                                <option value="venta"  :selected="tipo === 'venta'">Venta</option>
                            </select>
                        </div>

                        {{-- Fecha --}}
                        <div>
                            <x-input-label for="fecha" value="Fecha" />
                            <x-text-input id="fecha" name="fecha" type="date"
                                class="mt-1 block w-full"
                                value="{{ old('fecha', $operacion->fecha->format('Y-m-d')) }}" required />
                        </div>

                        {{-- Cantidad --}}
                        <div>
                            <x-input-label for="cantidad" value="Unidades" />
                            <x-text-input id="cantidad" name="cantidad" type="number" step="0.0001" min="0.0001"
                                class="mt-1 block w-full" x-model="cantidad" required />
                        </div>

                        {{-- Precio unitario --}}
                        <div>
                            <x-input-label for="precio_unitario" value="Precio Unitario (€)" />
                            <x-text-input id="precio_unitario" name="precio_unitario" type="number" step="0.0001" min="0"
                                class="mt-1 block w-full" x-model="precio" required />
                        </div>
                    </div>

                    {{-- Desglose gastos --}}
                    <div class="mb-6">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Gastos de la operación</p>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="comision" value="Comisión Bancaria (€)" />
                                <x-text-input id="comision" name="comision" type="number" step="0.01" min="0"
                                    class="mt-1 block w-full" x-model="com_bancaria" />
                            </div>
                            <div>
                                <x-input-label for="comision_bolsa" value="Comisión Bolsa (€)" />
                                <x-text-input id="comision_bolsa" name="comision_bolsa" type="number" step="0.01" min="0"
                                    class="mt-1 block w-full" x-model="com_bolsa" />
                            </div>
                            <div>
                                <x-input-label for="impuestos" value="Impuestos (€)" />
                                <x-text-input id="impuestos" name="impuestos" type="number" step="0.01" min="0"
                                    class="mt-1 block w-full" x-model="impuestos" />
                            </div>
                            <div>
                                <x-input-label for="comision_divisa" value="Comisión Cambio Divisa (€)" />
                                <x-text-input id="comision_divisa" name="comision_divisa" type="number" step="0.01" min="0"
                                    class="mt-1 block w-full" x-model="com_divisa" />
                            </div>
                        </div>

                        {{-- Resumen en tiempo real --}}
                        <div class="mt-4 p-3 bg-indigo-50 rounded-lg text-sm flex justify-between items-center">
                            <div class="text-gray-600">
                                Bruto: <span class="font-bold font-mono" x-text="bruto.toFixed(4) + ' €'"></span>
                                &nbsp;·&nbsp;
                                Total gastos: <span class="font-bold text-orange-600 font-mono" x-text="totalGastos.toFixed(2) + ' €'"></span>
                            </div>
                            <div class="text-right">
                                <span class="text-xs text-gray-500 uppercase tracking-wider" x-text="tipo === 'compra' ? 'Coste total' : 'Valor transmisión'"></span><br>
                                <span class="text-xl font-black text-indigo-800 font-mono" x-text="neto.toFixed(2) + ' €'"></span>
                            </div>
                        </div>
                    </div>

                    {{-- Notas --}}
                    <div class="mb-6">
                        <x-input-label for="notas" value="Notas (opcional)" />
                        <textarea id="notas" name="notas" rows="2"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm"
                            placeholder="Broker, número de orden...">{{ old('notas', $operacion->notas) }}</textarea>
                    </div>

                    <div class="flex justify-end gap-3">
                        <a href="{{ url()->previous() }}"
                           class="px-4 py-2 text-sm text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg font-medium">
                            Cancelar
                        </a>
                        <button type="submit"
                            class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-lg">
                            Guardar cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
