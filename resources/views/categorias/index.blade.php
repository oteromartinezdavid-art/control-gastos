<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-[#1e1b4b] leading-tight">
            Gestión de Categorías de Gastos
        </h2>
    </x-slot>
     @if(session('error'))
        <div class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 shadow-sm" role="alert">
            <p class="font-bold">Error</p>
            <p>{{ session('error') }}</p>
        </div>
    @endif

    @if(session('success'))
        <div class="mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 shadow-sm" role="alert">
            <p class="font-bold">Éxito</p>
            <p>{{ session('success') }}</p>
        </div>
    @endif
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100 mb-6">
                <form action="{{ route('categorias-gastos.store') }}" method="POST" class="flex flex-wrap gap-4 items-end">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nombre</label>
                        <input type="text" name="nombre" class="mt-1 block rounded-md border-gray-300 shadow-sm focus:border-[#f97316] focus:ring-[#f97316]" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Presupuesto Mensual (€)</label>
                        <input type="number" step="0.01" name="presupuesto_mensual" class="mt-1 block rounded-md border-gray-300 shadow-sm focus:border-[#f97316] focus:ring-[#f97316]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Color</label>
                        <input type="color" name="color" value="#1e1b4b" class="mt-1 block w-16 h-10 p-1 rounded-md border-gray-300 shadow-sm">
                    </div>
                    <button type="submit" class="bg-[#f97316] hover:bg-[#ea580c] text-white font-bold py-2 px-4 rounded-md transition duration-150">
                        + Añadir Categoría
                    </button>
                </form>
            </div>
           
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-100">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Color</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Presupuesto</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($categorias as $cat)
                        <tr>
                            <td class="px-6 py-4"><div class="w-6 h-6 rounded-full" style="background-color: {{ $cat->color }}"></div></td>
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $cat->nombre }}</td>
                            <td class="px-6 py-4">{{ number_format($cat->presupuesto_mensual, 2) }}€</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <a href="{{ route('categorias.edit', $cat) }}"
                                       class="text-indigo-600 hover:text-indigo-900 transition" title="Editar">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>
                                    <form action="{{ route('categorias.destroy', $cat->id) }}" method="POST"
                                          onsubmit="return confirm('¿Eliminar la categoría «{{ $cat->nombre }}»?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:text-red-700 transition" title="Eliminar">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
        </div>
    </div>
</x-app-layout>