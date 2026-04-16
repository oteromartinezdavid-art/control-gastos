<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Importar Movimientos Bankinter') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-b-4 border-orange-500">
                <div class="mb-6">
                    <h3 class="text-lg font-bold text-gray-700">Formato Detectado: Bankinter CSV</h3>
                    <p class="text-sm text-gray-500 italic">Campos: Fecha, Descripción, Importe...</p>
                </div>

                <form action="{{ route('importar.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Selecciona el archivo CSV</label>
                        <input type="file" name="archivo_csv" accept=".csv" required
                               class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                    </div>

                    <div class="flex items-center justify-end">
                        <x-primary-button>
                            {{ __('Procesar e Importar') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
            
            <div class="mt-4 p-4 bg-blue-50 text-blue-700 text-xs rounded">
                Nota: Los gastos se asignarán por defecto a la categoría con ID 1 y los ingresos a la fuente con ID 1.
            </div>
        </div>
    </div>
</x-app-layout>