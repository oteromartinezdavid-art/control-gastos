<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Copias de Seguridad</h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-lg text-sm font-medium">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Crear backup --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-1">Crear nueva copia de seguridad</h3>
                <p class="text-sm text-gray-500 mb-4">
                    Genera un volcado completo de la base de datos. El archivo se almacena en el servidor y puedes descargarlo a tu equipo.
                </p>
                <form action="{{ route('backup.store') }}" method="POST">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center gap-2 bg-[#1e1b4b] text-white px-5 py-2.5 rounded-lg text-sm font-bold hover:bg-indigo-800 transition">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                        </svg>
                        Crear Backup Ahora
                    </button>
                </form>
            </div>

            {{-- Listado de backups --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="text-base font-bold text-gray-800">Copias almacenadas</h3>
                </div>

                @if($files->isEmpty())
                    <div class="p-12 text-center text-gray-400">
                        <svg class="h-12 w-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                        </svg>
                        <p class="font-medium">No hay copias de seguridad todavía</p>
                        <p class="text-sm mt-1">Crea tu primera copia con el botón de arriba.</p>
                    </div>
                @else
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-gray-50 border-b text-gray-600 text-xs uppercase tracking-widest font-bold">
                                <th class="px-6 py-3">Archivo</th>
                                <th class="px-6 py-3">Fecha</th>
                                <th class="px-6 py-3">Tamaño</th>
                                <th class="px-6 py-3 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($files as $file)
                            <tr class="hover:bg-gray-50/50">
                                <td class="px-6 py-4">
                                    <span class="font-mono text-sm text-gray-800">{{ $file['nombre'] }}</span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    {{ $file['fecha']->format('d/m/Y H:i:s') }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    @php
                                        $kb = $file['size'] / 1024;
                                        $size = $kb >= 1024
                                            ? number_format($kb / 1024, 2) . ' MB'
                                            : number_format($kb, 1) . ' KB';
                                    @endphp
                                    {{ $size }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-center gap-4">
                                        <a href="{{ route('backup.download', $file['nombre']) }}"
                                           class="inline-flex items-center gap-1 text-indigo-600 hover:text-indigo-800 text-sm font-medium transition">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                            </svg>
                                            Descargar
                                        </a>
                                        <a href="{{ route('backup.restore.confirm', $file['nombre']) }}"
                                           class="inline-flex items-center gap-1 text-amber-600 hover:text-amber-800 text-sm font-medium transition">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                            </svg>
                                            Restaurar
                                        </a>
                                        <form action="{{ route('backup.destroy', $file['nombre']) }}" method="POST"
                                              onsubmit="return confirm('¿Eliminar este backup? Esta acción no se puede deshacer.')">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                    class="inline-flex items-center gap-1 text-red-500 hover:text-red-700 text-sm font-medium transition">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                                Eliminar
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>

            <p class="text-xs text-gray-400 text-center">
                Los backups se guardan en <code class="bg-gray-100 px-1 rounded">storage/app/backups/</code> dentro del servidor.
                Descárgalos a un lugar seguro para garantizar la recuperación ante desastres.
            </p>

        </div>
    </div>
</x-app-layout>
