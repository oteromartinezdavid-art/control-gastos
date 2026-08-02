<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Editar Gasto') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form action="{{ route('gastos.update', $gasto) }}" method="POST">
                    @method('PATCH')
                    <input type="hidden" name="_mes"  value="{{ $mes }}">
                    <input type="hidden" name="_anio" value="{{ $anio }}">
                    @include('gastos.form-fields') {{-- Reutilizamos los mismos campos --}}

                    <div class="mt-4 flex space-x-2">
                        <x-primary-button>Actualizar Gasto</x-primary-button>
                        <a href="{{ route('gastos.index', ['mes' => $mes, 'anio' => $anio]) }}"
                           class="inline-flex items-center px-4 py-2 bg-gray-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-600 active:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>