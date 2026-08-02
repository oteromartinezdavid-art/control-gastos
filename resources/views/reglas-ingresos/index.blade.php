<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Reglas de Importación de Ingresos</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded">{{ session('success') }}</div>
            @endif

            {{-- Formulario nueva regla --}}
            <div class="bg-white p-6 rounded-lg shadow mb-6">
                <h3 class="text-lg font-bold mb-4">Añadir Nueva Regla</h3>
                <form action="{{ route('reglas-ingresos.store') }}" method="POST" class="flex flex-wrap gap-4 items-end">
                    @csrf
                    <div class="flex-1 min-w-[200px]">
                        <x-input-label value="Si la descripción contiene..." />
                        <x-text-input name="palabra_clave" class="w-full" placeholder="Ej: NOMINA, TRANSFERENCIA" required />
                    </div>
                    <div class="flex-1 min-w-[200px]">
                        <x-input-label value="Asignar a Fuente de Ingreso" />
                        <select name="fuente_ingreso_id" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500">
                            @foreach($fuentes as $fuente)
                                <option value="{{ $fuente->id }}">{{ $fuente->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <x-primary-button>Guardar Regla</x-primary-button>
                </form>
            </div>

            {{-- Listado --}}
            <div class="bg-white shadow overflow-hidden sm:rounded-md">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Palabra Clave</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fuente Asignada</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($reglas as $regla)
                        <tr>
                            <td class="px-6 py-4 font-bold text-emerald-600">{{ $regla->palabra_clave }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 rounded text-white text-xs font-bold"
                                      style="background-color: {{ $regla->fuente->color ?? '#059669' }}">
                                    {{ $regla->fuente->nombre ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <form action="{{ route('reglas-ingresos.destroy', $regla) }}" method="POST"
                                      onsubmit="return confirm('¿Eliminar la regla «{{ $regla->palabra_clave }}»?')">
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
                            <td colspan="3" class="px-6 py-10 text-center text-gray-400 italic">No hay reglas configuradas. Añade una para auto-asignar fuentes al importar.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
