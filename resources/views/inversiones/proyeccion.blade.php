<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="#" onclick="history.back(); return false;" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">← Volver</a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Proyección de Cartera</h2>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            {{-- Formulario --}}
            <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
                <h3 class="font-bold text-gray-700 mb-6 text-lg">Parámetros de simulación</h3>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Valor cartera actual (€)</label>
                        <input id="valorInicial" type="number" step="100" min="0"
                               value="{{ $valorCarteraActual }}"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Aportación anual (€)</label>
                        <input id="aportacion" type="number" step="100" min="0" value="2000"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Dividendo anual (%)</label>
                        <input id="pctDividendo" type="number" step="0.1" min="0" max="30" value="4"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Revalorización anual (%)</label>
                        <input id="pctRevalorizacion" type="number" step="0.1" min="0" max="50" value="4"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Años a simular</label>
                        <input id="anos" type="number" step="1" min="1" max="50" value="20"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Reinvertir dividendos</label>
                        <div class="mt-2 flex items-center gap-2">
                            <input id="reinvertir" type="checkbox"
                                   class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 h-5 w-5">
                            <span class="text-sm text-gray-500">Sí</span>
                        </div>
                    </div>
                </div>
                <div class="mt-5">
                    <button onclick="calcular()"
                            class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-md shadow-sm transition">
                        Calcular proyección
                    </button>
                </div>
            </div>

            {{-- KPIs resultado --}}
            <div id="kpis" class="hidden grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-5">
                    <p class="text-xs font-bold text-gray-500 uppercase">Valor final cartera</p>
                    <p id="kpi-valor" class="mt-1 text-2xl font-black text-indigo-700">—</p>
                </div>
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-5">
                    <p class="text-xs font-bold text-gray-500 uppercase">Total aportado</p>
                    <p id="kpi-aportado" class="mt-1 text-2xl font-black text-gray-700">—</p>
                </div>
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-5">
                    <p class="text-xs font-bold text-gray-500 uppercase">Dividendos cobrados</p>
                    <p id="kpi-dividendos" class="mt-1 text-2xl font-black text-emerald-600">—</p>
                </div>
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-5">
                    <p class="text-xs font-bold text-gray-500 uppercase">Ganancia total</p>
                    <p id="kpi-ganancia" class="mt-1 text-2xl font-black text-emerald-700">—</p>
                    <p id="kpi-x" class="text-sm text-gray-400"></p>
                </div>
            </div>

            {{-- Gráfico --}}
            <div id="grafico-wrap" class="hidden bg-white shadow-sm rounded-lg border border-gray-100 p-6">
                <h3 class="font-bold text-gray-700 mb-4">Evolución del valor de cartera</h3>
                <div style="height:280px"><canvas id="graficoProyeccion"></canvas></div>
            </div>

            {{-- Tabla año a año --}}
            <div id="tabla-wrap" class="hidden bg-white shadow-sm rounded-lg border border-gray-100">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="font-bold text-gray-800">Desglose año a año</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 text-sm">
                        <thead class="bg-gray-50 text-xs font-bold text-gray-500 uppercase">
                            <tr>
                                <th class="px-4 py-3 text-left">Año</th>
                                <th class="px-4 py-3 text-right">Valor inicio</th>
                                <th class="px-4 py-3 text-right">Aportación</th>
                                <th class="px-4 py-3 text-right">Dividendos</th>
                                <th class="px-4 py-3 text-right">Revalorización</th>
                                <th class="px-4 py-3 text-right">Valor fin</th>
                                <th class="px-4 py-3 text-right">Div. acumulados</th>
                            </tr>
                        </thead>
                        <tbody id="tabla-body" class="divide-y divide-gray-100"></tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        let chart = null;

        function fmt(n) {
            return new Intl.NumberFormat('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(n) + '€';
        }

        function calcular() {
            const valorInicial   = parseFloat(document.getElementById('valorInicial').value) || 0;
            const aportacion     = parseFloat(document.getElementById('aportacion').value) || 0;
            const pctDiv         = parseFloat(document.getElementById('pctDividendo').value) / 100 || 0;
            const pctReval       = parseFloat(document.getElementById('pctRevalorizacion').value) / 100 || 0;
            const anos           = parseInt(document.getElementById('anos').value) || 20;
            const reinvertir     = document.getElementById('reinvertir').checked;
            const anoActual      = new Date().getFullYear();

            let cartera = valorInicial;
            let divAcumulados = 0;
            let totalAportado = valorInicial;
            const filas = [];
            const labelsGrafico = [];
            const datosValor = [];
            const datosDivAcum = [];

            for (let i = 1; i <= anos; i++) {
                const inicio    = cartera;
                const dividendo = inicio * pctDiv;
                const reval     = inicio * pctReval;

                cartera += reval + aportacion;
                if (reinvertir) cartera += dividendo;

                divAcumulados += dividendo;
                totalAportado += aportacion;

                filas.push({ ano: anoActual + i, inicio, aportacion, dividendo, reval, fin: cartera, divAcum: divAcumulados });
                labelsGrafico.push(anoActual + i);
                datosValor.push(Math.round(cartera));
                datosDivAcum.push(Math.round(divAcumulados));
            }

            const ganancia = cartera - totalAportado + (reinvertir ? 0 : divAcumulados);
            const multiplicador = totalAportado > 0 ? (cartera / valorInicial).toFixed(1) : '—';

            // KPIs
            document.getElementById('kpi-valor').textContent      = fmt(cartera);
            document.getElementById('kpi-aportado').textContent   = fmt(totalAportado);
            document.getElementById('kpi-dividendos').textContent = fmt(divAcumulados);
            document.getElementById('kpi-ganancia').textContent   = fmt(cartera + (reinvertir ? 0 : divAcumulados) - totalAportado);
            document.getElementById('kpi-x').textContent          = '×' + multiplicador + ' sobre inversión inicial';
            document.getElementById('kpis').classList.remove('hidden');

            // Tabla
            const tbody = document.getElementById('tabla-body');
            tbody.innerHTML = '';
            filas.forEach(f => {
                const tr = document.createElement('tr');
                tr.className = 'hover:bg-indigo-50';
                tr.innerHTML = `
                    <td class="px-4 py-2 font-bold text-indigo-700">${f.ano}</td>
                    <td class="px-4 py-2 text-right text-gray-600">${fmt(f.inicio)}</td>
                    <td class="px-4 py-2 text-right text-blue-600">+${fmt(f.aportacion)}</td>
                    <td class="px-4 py-2 text-right text-emerald-600">+${fmt(f.dividendo)}</td>
                    <td class="px-4 py-2 text-right text-indigo-600">+${fmt(f.reval)}</td>
                    <td class="px-4 py-2 text-right font-bold">${fmt(f.fin)}</td>
                    <td class="px-4 py-2 text-right text-emerald-700 font-semibold">${fmt(f.divAcum)}</td>
                `;
                tbody.appendChild(tr);
            });
            document.getElementById('tabla-wrap').classList.remove('hidden');

            // Gráfico
            document.getElementById('grafico-wrap').classList.remove('hidden');
            if (chart) chart.destroy();
            chart = new Chart(document.getElementById('graficoProyeccion'), {
                type: 'line',
                data: {
                    labels: labelsGrafico,
                    datasets: [
                        {
                            label: 'Valor cartera',
                            data: datosValor,
                            borderColor: '#6366f1',
                            backgroundColor: 'rgba(99,102,241,0.08)',
                            fill: true,
                            tension: 0.3,
                            pointRadius: anos <= 20 ? 4 : 2,
                        },
                        {
                            label: 'Dividendos acumulados',
                            data: datosDivAcum,
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16,185,129,0.06)',
                            fill: true,
                            tension: 0.3,
                            pointRadius: anos <= 20 ? 4 : 2,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'top' } },
                    scales: {
                        y: { ticks: { callback: v => new Intl.NumberFormat('es-ES').format(v) + '€' } }
                    }
                }
            });
        }

        // Auto-calcular al cargar
        document.addEventListener('DOMContentLoaded', calcular);

        // Recalcular al cambiar cualquier input
        ['valorInicial','aportacion','pctDividendo','pctRevalorizacion','anos','reinvertir'].forEach(id => {
            document.getElementById(id).addEventListener('change', calcular);
        });
    </script>
</x-app-layout>
