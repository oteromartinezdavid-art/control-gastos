<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-[#1e1b4b] leading-tight">
            Gestión de Fuentes de Ingresos
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded">
                    <ul class="list-disc ml-4">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif

            {{-- Formulario nueva fuente --}}
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100 mb-6">
                <form action="{{ route('fuentes-ingresos.store') }}" method="POST" class="flex flex-wrap gap-4 items-end">
                    @csrf
                    <div class="flex-1 min-w-48">
                        <label class="block text-sm font-medium text-gray-700">Nombre (ej: Nómina, Alquiler)</label>
                        <input type="text" name="nombre" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#f97316] focus:ring-[#f97316]" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Color</label>
                        <input type="color" name="color" value="#059669" class="mt-1 h-10 w-16 rounded border border-gray-300 cursor-pointer">
                    </div>
                    <button type="submit" class="bg-[#f97316] hover:bg-[#ea580c] text-white font-bold py-2 px-4 rounded-md transition duration-150">
                        + Añadir Fuente
                    </button>
                </form>
            </div>

            {{-- Listado --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-100">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Color</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Creado el</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($fuentes as $fuente)
                        <tr>
                            <td class="px-6 py-4">
                                <span class="inline-block w-6 h-6 rounded-full border border-gray-200" style="background-color: {{ $fuente->color }}"></span>
                            </td>
                            <td class="px-6 py-4 font-medium text-gray-900">
                                <span id="nombre-text-{{ $fuente->id }}">{{ $fuente->nombre }}</span>
                                <form id="edit-form-{{ $fuente->id }}" action="{{ route('fuentes-ingresos.update', $fuente) }}" method="POST" class="hidden flex gap-2 items-center mt-1">
                                    @csrf @method('PATCH')
                                    <input type="text" name="nombre" value="{{ $fuente->nombre }}" class="rounded border-gray-300 text-sm" required>
                                    <input type="color" name="color" value="{{ $fuente->color }}" class="h-8 w-12 rounded border border-gray-300 cursor-pointer">
                                    <button type="submit" class="text-xs bg-emerald-600 text-white px-2 py-1 rounded">Guardar</button>
                                    <button type="button" onclick="cancelEdit({{ $fuente->id }})" class="text-xs bg-gray-200 text-gray-700 px-2 py-1 rounded">Cancelar</button>
                                </form>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $fuente->created_at->format('d/m/Y') }}</td>
                            <td class="px-6 py-4 flex gap-3 items-center">
                                <button onclick="startEdit({{ $fuente->id }})" class="text-indigo-500 hover:text-indigo-700 transition" title="Editar">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <form action="{{ route('fuentes-ingresos.destroy', $fuente) }}" method="POST"
                                      onsubmit="return confirm('¿Eliminar la fuente «{{ $fuente->nombre }}»?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700 transition" title="Eliminar">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center text-gray-500">No hay fuentes configuradas.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function startEdit(id) {
            document.getElementById('nombre-text-' + id).classList.add('hidden');
            document.getElementById('edit-form-' + id).classList.remove('hidden');
        }
        function cancelEdit(id) {
            document.getElementById('nombre-text-' + id).classList.remove('hidden');
            document.getElementById('edit-form-' + id).classList.add('hidden');
        }
    </script>
</x-app-layout>
