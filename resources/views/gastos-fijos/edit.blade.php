<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Editar Gasto Fijo
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                @php
                    $mesesActuales = array_map('intval', old('meses_cobro', $gastoFijo->meses_cobro ?? []));
                    $esEspecifico  = !empty($mesesActuales);
                @endphp
                <form action="{{ route('gastos-fijos.update', $gastoFijo) }}" method="POST" class="space-y-5"
                      x-data="{ especifico: {{ $esEspecifico ? 'true' : 'false' }} }">
                    @csrf
                    @method('PATCH')

                    <div>
                        <x-input-label for="nombre" value="Nombre (Ej: Netflix, Seguro)" />
                        <x-text-input id="nombre" name="nombre" type="text" class="mt-1 block w-full"
                            value="{{ old('nombre', $gastoFijo->nombre) }}" required
                            placeholder="Debe coincidir con la descripción del gasto real" />
                        <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="monto_previsto" value="Monto por Cobro (€)" />
                        <x-text-input id="monto_previsto" name="monto_previsto" type="number" step="0.01"
                            class="mt-1 block w-full"
                            value="{{ old('monto_previsto', $gastoFijo->monto_previsto) }}" required />
                        <x-input-error :messages="$errors->get('monto_previsto')" class="mt-2" />
                    </div>

                    {{-- Frecuencia de cobro --}}
                    <div>
                        <x-input-label value="Frecuencia de Cobro" />
                        <div class="mt-2 flex gap-6 text-sm">
                            <label class="flex items-center gap-1.5 cursor-pointer font-medium"
                                   :class="!especifico ? 'text-indigo-700' : 'text-gray-500'">
                                <input type="radio" x-model="especifico" :value="false" class="text-indigo-600">
                                Mensual (todos los meses)
                            </label>
                            <label class="flex items-center gap-1.5 cursor-pointer font-medium"
                                   :class="especifico ? 'text-indigo-700' : 'text-gray-500'">
                                <input type="radio" x-model="especifico" :value="true" class="text-indigo-600">
                                Meses concretos
                            </label>
                        </div>

                        <div x-show="especifico" x-cloak class="mt-3 p-4 bg-indigo-50 rounded-lg">
                            <p class="text-[10px] font-bold uppercase text-indigo-500 mb-3">Selecciona los meses de cobro</p>
                            <div class="grid grid-cols-6 gap-2">
                                @foreach(['1'=>'Ene','2'=>'Feb','3'=>'Mar','4'=>'Abr','5'=>'May','6'=>'Jun','7'=>'Jul','8'=>'Ago','9'=>'Sep','10'=>'Oct','11'=>'Nov','12'=>'Dic'] as $num => $abr)
                                <label class="flex items-center gap-1.5 cursor-pointer text-sm font-semibold text-gray-700 hover:text-indigo-700">
                                    <input type="checkbox" name="meses_cobro[]" value="{{ $num }}"
                                           {{ in_array((int)$num, $mesesActuales) ? 'checked' : '' }}
                                           class="rounded border-gray-300 text-indigo-600">
                                    {{ $abr }}
                                </label>
                                @endforeach
                            </div>
                        </div>
                        <x-input-error :messages="$errors->get('meses_cobro')" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="dia_pago" value="Día de Cobro" />
                            <x-text-input id="dia_pago" name="dia_pago" type="number" min="1" max="31"
                                class="mt-1 block w-full"
                                value="{{ old('dia_pago', $gastoFijo->dia_pago) }}" required />
                            <x-input-error :messages="$errors->get('dia_pago')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="categoria_gasto_id" value="Categoría" />
                            <select name="categoria_gasto_id"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                required>
                                @foreach($categorias as $cat)
                                    <option value="{{ $cat->id }}"
                                        {{ old('categoria_gasto_id', $gastoFijo->categoria_gasto_id) == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('categoria_gasto_id')" class="mt-2" />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="fecha_inicio" value="Fecha Inicio" />
                            <x-text-input id="fecha_inicio" name="fecha_inicio" type="date"
                                class="mt-1 block w-full"
                                value="{{ old('fecha_inicio', $gastoFijo->fecha_inicio?->format('Y-m-d')) }}" required />
                            <p class="text-xs text-gray-400 mt-1">Desde cuándo pagas este gasto</p>
                            <x-input-error :messages="$errors->get('fecha_inicio')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="fecha_fin" value="Fecha Fin (opcional)" />
                            <x-text-input id="fecha_fin" name="fecha_fin" type="date"
                                class="mt-1 block w-full"
                                value="{{ old('fecha_fin', $gastoFijo->fecha_fin?->format('Y-m-d')) }}" />
                            <p class="text-xs text-gray-400 mt-1">Dejar vacío si sigue activo</p>
                            <x-input-error :messages="$errors->get('fecha_fin')" class="mt-2" />
                        </div>
                    </div>

                    <div class="flex items-center gap-3 pt-2">
                        <x-primary-button>Guardar Cambios</x-primary-button>
                        <a href="{{ route('gastos-fijos.index') }}"
                            class="inline-flex items-center px-4 py-2 bg-gray-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-600 transition ease-in-out duration-150">
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
