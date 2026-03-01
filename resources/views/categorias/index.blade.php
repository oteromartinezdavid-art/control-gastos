<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-[#1e1b4b] leading-tight">
            Gestión de Categorías de Gastos
        </h2>
    </x-slot>

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
                            <td class="px-6 py-4 text-sm text-red-600 hover:text-red-900 cursor-pointer">Eliminar</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>