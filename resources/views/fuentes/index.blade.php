<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-[#1e1b4b] leading-tight">
            Gestión de Fuentes de Ingresos
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100 mb-6">
                <form action="{{ route('fuentes-ingresos.store') }}" method="POST" class="flex flex-wrap gap-4 items-end">
                    @csrf
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700">Nombre de la Fuente (ej: Nómina, Alquiler, Dividendos)</label>
                        <input type="text" name="nombre" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#f97316] focus:ring-[#f97316]" required>
                    </div>
                    <button type="submit" class="bg-[#f97316] hover:bg-[#ea580c] text-white font-bold py-2 px-4 rounded-md transition duration-150">
                        + Añadir Fuente
                    </button>
                </form>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-100">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Creado el</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($fuentes as $fuente)
                        <tr>
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $fuente->nombre }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $fuente->created_at->format('d/m/Y') }}</td>
                            <td class="px-6 py-4">
                                <form action="{{ route('fuentes-ingresos.destroy', $fuente) }}" method="POST">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-6 py-4 text-center text-gray-500">No hay fuentes configuradas.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>