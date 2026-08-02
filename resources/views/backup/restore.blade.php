<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Restaurar Copia de Seguridad</h2>
            <a href="{{ route('backup.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">← Volver a Backups</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Aviso de peligro --}}
            <div class="bg-red-50 border border-red-300 rounded-lg p-5">
                <div class="flex gap-3">
                    <svg class="h-6 w-6 text-red-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                    </svg>
                    <div>
                        <h3 class="font-bold text-red-800 text-sm">Acción irreversible sobre la base de datos activa</h3>
                        <ul class="mt-2 text-sm text-red-700 space-y-1 list-disc list-inside">
                            <li>Todos los datos actuales serán <strong>reemplazados</strong> por los del backup seleccionado.</li>
                            <li>Cualquier cambio realizado después de ese backup se <strong>perderá</strong>.</li>
                            <li>Se creará automáticamente un backup previo (<code class="bg-red-100 px-1 rounded">pre-restore_...</code>) como red de seguridad.</li>
                            <li>Al finalizar serás <strong>redirigido al login</strong> porque la sesión actual no existirá en la BD restaurada.</li>
                        </ul>
                    </div>
                </div>
            </div>

            {{-- Detalle del backup --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                <h3 class="text-base font-bold text-gray-800 mb-4">Copia seleccionada para restaurar</h3>
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-gray-500 font-medium">Archivo</dt>
                        <dd class="font-mono text-gray-800">{{ $file['nombre'] }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500 font-medium">Fecha del backup</dt>
                        <dd class="text-gray-800">{{ $file['fecha']->format('d/m/Y H:i:s') }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500 font-medium">Tamaño</dt>
                        <dd class="text-gray-800">
                            @php
                                $kb = $file['size'] / 1024;
                                echo $kb >= 1024
                                    ? number_format($kb / 1024, 2) . ' MB'
                                    : number_format($kb, 1) . ' KB';
                            @endphp
                        </dd>
                    </div>
                </dl>
            </div>

            {{-- Botones --}}
            <div class="flex items-center gap-4">
                <form action="{{ route('backup.restore', $file['nombre']) }}" method="POST">
                    @csrf @method('PUT')
                    <button type="submit"
                            onclick="return confirm('¿Confirmas la restauración? Esta acción reemplazará todos los datos actuales.')"
                            class="inline-flex items-center gap-2 bg-red-600 text-white px-6 py-2.5 rounded-lg text-sm font-bold hover:bg-red-700 transition">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Sí, restaurar este backup
                    </button>
                </form>

                <a href="{{ route('backup.index') }}"
                   class="px-6 py-2.5 bg-gray-100 text-gray-700 rounded-lg text-sm font-bold hover:bg-gray-200 transition">
                    Cancelar
                </a>
            </div>

        </div>
    </div>
</x-app-layout>
