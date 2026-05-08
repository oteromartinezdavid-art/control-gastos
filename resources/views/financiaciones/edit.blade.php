<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Editar Financiación
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Aviso amortización --}}
            <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 text-sm text-amber-800">
                <p class="font-bold mb-1">¿Has hecho una amortización?</p>
                <p>Actualiza directamente los campos <strong>Cuota mensual</strong> y <strong>Cuotas pendientes</strong> con los nuevos valores que te haya comunicado el banco. El sistema seguirá detectando los pagos mensuales desde ese punto.</p>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form action="{{ route('financiaciones.update', $financiacion) }}" method="POST" class="space-y-5">
                    @csrf
                    @method('PATCH')

                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2">
                            <x-input-label for="nombre" value="Nombre" />
                            <x-text-input id="nombre" name="nombre" type="text" class="mt-1 block w-full"
                                value="{{ old('nombre', $financiacion->nombre) }}" required />
                            <p class="text-[10px] text-gray-400 mt-1">Debe coincidir con la descripción del gasto en el extracto</p>
                            <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
                        </div>
                        <div class="col-span-2">
                            <x-input-label for="entidad" value="Entidad (opcional)" />
                            <x-text-input id="entidad" name="entidad" type="text" class="mt-1 block w-full"
                                value="{{ old('entidad', $financiacion->entidad) }}" placeholder="Ej: CaixaBank, Cetelem..." />
                            <x-input-error :messages="$errors->get('entidad')" class="mt-2" />
                        </div>
                    </div>

                    {{-- Campos clave para amortizaciones --}}
                    <div class="grid grid-cols-2 gap-4 p-4 bg-indigo-50 rounded-lg border border-indigo-100">
                        <div class="col-span-2">
                            <p class="text-xs font-bold uppercase text-indigo-600 mb-3">Estado actual del préstamo</p>
                        </div>
                        <div>
                            <x-input-label for="cuota_mensual" value="Cuota Mensual (€)" />
                            <x-text-input id="cuota_mensual" name="cuota_mensual" type="number" step="0.01"
                                class="mt-1 block w-full bg-white"
                                value="{{ old('cuota_mensual', $financiacion->cuota_mensual) }}" required />
                            <x-input-error :messages="$errors->get('cuota_mensual')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="cuotas_pendientes" value="Cuotas Pendientes" />
                            <x-text-input id="cuotas_pendientes" name="cuotas_pendientes" type="number"
                                class="mt-1 block w-full bg-white"
                                value="{{ old('cuotas_pendientes', $financiacion->cuotas_pendientes) }}" required min="0" />
                            <x-input-error :messages="$errors->get('cuotas_pendientes')" class="mt-2" />
                        </div>
                        <div class="col-span-2 text-xs text-indigo-500">
                            Deuda restante estimada:
                            <strong>{{ number_format($financiacion->cuotas_pendientes * $financiacion->cuota_mensual, 2, ',', '.') }}€</strong>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="dia_cobro" value="Día de Cobro" />
                            <x-text-input id="dia_cobro" name="dia_cobro" type="number" min="1" max="31"
                                class="mt-1 block w-full"
                                value="{{ old('dia_cobro', $financiacion->dia_cobro) }}" required />
                            <x-input-error :messages="$errors->get('dia_cobro')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="categoria_gasto_id" value="Categoría" />
                            <select name="categoria_gasto_id"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                required>
                                @foreach($categorias as $cat)
                                    <option value="{{ $cat->id }}"
                                        {{ old('categoria_gasto_id', $financiacion->categoria_gasto_id) == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('categoria_gasto_id')" class="mt-2" />
                        </div>
                    </div>

                    <div class="flex items-center gap-3 pt-2">
                        <x-primary-button>Guardar Cambios</x-primary-button>
                        <a href="{{ route('financiaciones.index') }}"
                            class="inline-flex items-center px-4 py-2 bg-gray-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-600 transition ease-in-out duration-150">
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
