<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                <h2 class="text-lg font-bold mb-4">Editar Categoría</h2>
                
                <form action="{{ route('categorias-gastos.update', $categoriaGasto->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-4">
                        <x-input-label for="nombre" value="Nombre de la Categoría" />
                        <x-text-input id="nombre" name="nombre" type="text" class="mt-1 block w-full" :value="old('nombre', $categoriaGasto->nombre)" required />
                    </div>
                    <div class="mb-4">
                        <x-input-label for="presupuesto_mensual" value="Presupuesto Mensual (€)" />
                        <x-text-input id="presupuesto_mensual" name="presupuesto_mensual" type="number" step="0.01" class="mt-1 block w-full" :value="old('presupuesto_mensual', $categoriaGasto->presupuesto_mensual)" />
                    </div>
                    <div class="mb-4">
                        <x-input-label for="color" value="Color de la Categoría" />
                        <div class="flex items-center mt-1">
                            <input type="color" id="color" name="color" 
                                value="{{ old('color', $categoriaGasto->color) }}" 
                                class="h-10 w-20 rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <span class="ml-3 text-sm text-gray-600">Este color se usará en los gráficos del Dashboard.</span>
                        </div>
                    </div>
                    <div class="mt-4">
                        <x-primary-button>Actualizar</x-primary-button>
                        <a href="{{ route('categorias-gastos.index') }}" class="ml-4 text-gray-600">Cancelar</a>
                    </div>                    
                </form>
            </div>
        </div>
    </div>
</x-app-layout>