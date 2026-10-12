<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Agente Fiduciario · Análisis IA</h2>
            <span class="text-xs text-gray-400">Modelo: {{ $modelo }}</span>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @unless($tieneClaude)
                <div class="bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3 rounded-lg text-sm">
                    No tienes configurada la API key de Claude.
                    <a href="{{ route('configuracion.index') }}" class="font-bold underline">Configúrala aquí</a> para analizar.
                </div>
            @endunless

            @if($items->isEmpty())
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-12 text-center text-gray-400">
                    <p class="font-bold text-lg">No hay acciones en vigilancia</p>
                    <p class="text-sm mt-1">Añade acciones en <a href="{{ route('agente-fiduciario.vigilancia') }}" class="text-indigo-600 underline">Vigilancia</a> para poder analizarlas.</p>
                </div>
            @else
                {{-- Barra de acciones + progreso --}}
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4 space-y-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-bold text-gray-700">Analizar toda la cartera en vigilancia</p>
                            <p class="text-xs text-gray-400">Cada acción = 1 llamada a Claude. Coste según modelo configurado.</p>
                        </div>
                        <button id="btn-analizar-todas" @unless($tieneClaude) disabled @endunless
                            class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-md font-semibold text-sm hover:bg-indigo-700 transition disabled:opacity-40 disabled:cursor-not-allowed">
                            🧠 Analizar todas
                        </button>
                    </div>
                    <div id="progreso-wrap" class="hidden">
                        <div class="flex justify-between text-xs font-bold text-gray-500 mb-1">
                            <span id="progreso-label">Analizando…</span>
                            <span id="progreso-contador">0/0</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div id="progreso-barra" class="bg-indigo-500 h-2 rounded-full transition-all duration-300" style="width:0%"></div>
                        </div>
                        <p id="progreso-coste" class="text-[11px] text-gray-400 mt-1"></p>
                    </div>
                </div>

                {{-- Tarjetas por acción --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($items as $item)
                        @php $a = $analisis->get($item->id); @endphp
                        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-5" data-id="{{ $item->id }}">
                            <div class="flex items-start justify-between mb-3">
                                <div>
                                    <span class="font-bold text-gray-800 text-lg">{{ $item->ticker }}</span>
                                    @if($item->nombre)<span class="block text-xs text-gray-400">{{ \Illuminate\Support\Str::limit($item->nombre, 32) }}</span>@endif
                                </div>
                                <button data-analizar="{{ $item->id }}" @unless($tieneClaude) disabled @endunless
                                    class="text-xs font-semibold px-3 py-1.5 rounded-md bg-indigo-50 text-indigo-700 hover:bg-indigo-100 transition disabled:opacity-40">
                                    {{ $a ? 'Reanalizar' : 'Analizar' }}
                                </button>
                            </div>
                            <div class="resultado" id="res-{{ $item->id }}">
                                @includeWhen($a, 'agente-fiduciario._analisis', ['a' => $a])
                                @unless($a)
                                    <p class="text-sm text-gray-300 italic">Sin análisis todavía.</p>
                                @endunless
                            </div>
                        </div>
                    @endforeach
                </div>

                <p class="text-[11px] text-gray-400">Análisis informativo generado por IA sobre datos de mercado. NO es asesoramiento financiero personalizado.</p>
            @endif
        </div>
    </div>

    @if($items->isNotEmpty())
    <script>
        const CSRF = @json(csrf_token());
        const URL_BASE = @json(url('agente-fiduciario/analisis'));

        function veredictoClase(v) {
            return ({
                'Comprar': 'bg-emerald-100 text-emerald-700',
                'Vigilar': 'bg-indigo-100 text-indigo-700',
                'Mantener': 'bg-amber-100 text-amber-700',
                'Evitar': 'bg-red-100 text-red-700',
            })[v] || 'bg-gray-100 text-gray-600';
        }
        function scoreClase(s) {
            if (s >= 70) return 'text-emerald-600';
            if (s >= 45) return 'text-amber-600';
            return 'text-red-600';
        }
        function esc(s) {
            return String(s ?? '').replace(/[&<>"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c]));
        }
        function render(a) {
            const riesgos = (a.riesgos || []).map(r => `<li>${esc(r)}</li>`).join('');
            const rojas = (a.banderas_rojas || []).map(r => `<li>${esc(r)}</li>`).join('');
            return `
                <div class="flex items-center gap-3 mb-2">
                    <span class="text-3xl font-black ${scoreClase(a.score)}">${a.score ?? '—'}</span>
                    <span class="text-[10px] text-gray-400 uppercase">/100</span>
                    <span class="px-2 py-0.5 rounded-full text-xs font-bold uppercase ${veredictoClase(a.veredicto)}">${esc(a.veredicto || '—')}</span>
                    ${a.precio_objetivo ? `<span class="ml-auto text-xs text-gray-500">Obj: <b>${a.precio_objetivo}</b></span>` : ''}
                </div>
                <p class="text-sm text-gray-700 mb-2">${esc(a.tesis || '')}</p>
                ${riesgos ? `<div class="mb-2"><p class="text-[11px] font-bold uppercase text-gray-400">Riesgos</p><ul class="list-disc ml-4 text-xs text-gray-600">${riesgos}</ul></div>` : ''}
                ${rojas ? `<div class="mb-2"><p class="text-[11px] font-bold uppercase text-red-500">Banderas rojas</p><ul class="list-disc ml-4 text-xs text-red-600">${rojas}</ul></div>` : ''}
                <p class="text-[10px] text-gray-300 mt-2">${esc(a.modelo)} · $${Number(a.coste).toFixed(4)} · ${esc(a.fecha)}</p>
            `;
        }

        async function analizarUno(id) {
            const res = document.getElementById('res-' + id);
            res.innerHTML = '<p class="text-sm text-indigo-400 italic animate-pulse">Analizando…</p>';
            try {
                const r = await fetch(URL_BASE + '/' + id, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                });
                const data = await r.json();
                if (data.ok) {
                    res.innerHTML = render(data.analisis);
                    return data.analisis.coste || 0;
                }
                res.innerHTML = `<p class="text-sm text-red-500">⚠ ${esc(data.error || 'Error')}</p>`;
                return 0;
            } catch (e) {
                res.innerHTML = `<p class="text-sm text-red-500">⚠ Error de red</p>`;
                return 0;
            }
        }

        // Botones por acción
        document.querySelectorAll('[data-analizar]').forEach(btn => {
            btn.addEventListener('click', () => analizarUno(btn.dataset.analizar));
        });

        // Analizar todas (bucle secuencial con progreso)
        const btnTodas = document.getElementById('btn-analizar-todas');
        if (btnTodas) btnTodas.addEventListener('click', async () => {
            const ids = [...document.querySelectorAll('[data-id]')].map(el => el.dataset.id);
            const wrap = document.getElementById('progreso-wrap');
            const barra = document.getElementById('progreso-barra');
            const cont = document.getElementById('progreso-contador');
            const costeEl = document.getElementById('progreso-coste');
            wrap.classList.remove('hidden');
            btnTodas.disabled = true;
            let hecho = 0, costeTotal = 0;
            cont.textContent = `0/${ids.length}`;
            for (const id of ids) {
                costeTotal += await analizarUno(id);
                hecho++;
                barra.style.width = (hecho / ids.length * 100) + '%';
                cont.textContent = `${hecho}/${ids.length}`;
                costeEl.textContent = `Coste acumulado: $${costeTotal.toFixed(4)}`;
            }
            document.getElementById('progreso-label').textContent = 'Completado';
            btnTodas.disabled = false;
        });
    </script>
    @endif
</x-app-layout>
