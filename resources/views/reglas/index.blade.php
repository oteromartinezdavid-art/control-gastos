<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Configurar Auto-Categorización</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Formulario de nueva regla --}}
            <div class="bg-white p-6 rounded-lg shadow mb-6">
                <h3 class="text-lg font-bold mb-4">Añadir Nueva Regla</h3>
                <form action="{{ route('reglas.store') }}" method="POST" class="flex flex-wrap gap-4 items-end">
                    @csrf
                    <div class="flex-1 min-w-[200px]">
                        <x-input-label value="Si la descripción contiene..." />
                        <x-text-input name="palabra_clave" class="w-full" placeholder="Ej: MERCADONA" required />
                    </div>
                    <div class="flex-1 min-w-[200px]">
                        <x-input-label value="Asignar a Categoría" />
                        <select name="categoria_id" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500">
                            @foreach($categorias as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <x-primary-button>Guardar Regla</x-primary-button>
                </form>
            </div>

            {{-- Listado de reglas --}}
            <div class="bg-white shadow overflow-hidden sm:rounded-md">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Palabra Clave</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Categoría Asignada</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($reglas as $regla)
                        <tr>
                            <td class="px-6 py-4 font-bold text-indigo-600">{{ $regla->palabra_clave }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 rounded text-white text-xs" style="background-color: {{ $regla->categoria->color }}">
                                    {{ $regla->categoria->nombre }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <form action="{{ route('reglas.destroy', $regla) }}" method="POST">
                                    @csrf @method('DELETE')
                                    <button class="text-red-600 hover:underline text-sm font-bold">ELIMINAR</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>