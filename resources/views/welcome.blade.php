<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Control de Gastos</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,900" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-gray-50 min-h-screen">

        {{-- Nav --}}
        <nav class="bg-[#1e1b4b] shadow-lg">
            <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
                <img src="{{ asset('img/logo.png') }}" alt="Control de Gastos" class="h-14 w-auto">
                <div class="flex gap-3">
                    @auth
                        <a href="{{ route('dashboard') }}"
                           class="px-4 py-2 bg-white text-[#1e1b4b] rounded-lg font-bold text-sm hover:bg-indigo-50 transition">
                            Ir al Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}"
                           class="px-4 py-2 text-indigo-200 hover:text-white font-semibold text-sm transition">
                            Iniciar sesión
                        </a>
                        @if (Route::has('register'))
                        <a href="{{ route('register') }}"
                           class="px-4 py-2 bg-emerald-500 text-white rounded-lg font-bold text-sm hover:bg-emerald-600 transition">
                            Registrarse
                        </a>
                        @endif
                    @endauth
                </div>
            </div>
        </nav>

        {{-- Hero --}}
        <div class="bg-[#1e1b4b] pb-20 pt-16">
            <div class="max-w-4xl mx-auto px-6 text-center">
                <h1 class="text-5xl font-black text-white leading-tight">
                    Tus finanzas,<br>
                    <span class="text-emerald-400">bajo control.</span>
                </h1>
                <p class="mt-5 text-lg text-indigo-200 max-w-xl mx-auto">
                    Gestiona gastos, ingresos, presupuestos, inversiones y préstamos en un solo lugar.
                    Sin Excel, sin complicaciones.
                </p>
                <div class="mt-8 flex gap-3 justify-center">
                    @auth
                        <a href="{{ route('dashboard') }}"
                           class="px-6 py-3 bg-emerald-500 hover:bg-emerald-600 text-white font-bold rounded-xl transition shadow-lg">
                            Ir al Dashboard →
                        </a>
                    @else
                        <a href="{{ route('register') }}"
                           class="px-6 py-3 bg-emerald-500 hover:bg-emerald-600 text-white font-bold rounded-xl transition shadow-lg">
                            Empezar gratis
                        </a>
                        <a href="{{ route('login') }}"
                           class="px-6 py-3 bg-white/10 hover:bg-white/20 text-white font-semibold rounded-xl transition">
                            Ya tengo cuenta
                        </a>
                    @endauth
                </div>
            </div>
        </div>

        {{-- Features --}}
        <div class="max-w-7xl mx-auto px-6 -mt-10">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="w-11 h-11 bg-indigo-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-900 text-lg">Dashboard</h3>
                    <p class="text-gray-500 text-sm mt-1">Resumen mensual de ingresos, gastos y balance. Gráficas por categoría y evolución diaria.</p>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="w-11 h-11 bg-emerald-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-900 text-lg">Gastos e Ingresos</h3>
                    <p class="text-gray-500 text-sm mt-1">Registro manual o importación automática desde CSV de Bankinter con categorización inteligente.</p>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="w-11 h-11 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 11h.01M12 11h.01M15 11h.01M4 19h16a2 2 0 002-2V7a2 2 0 00-2-2H4a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-900 text-lg">Presupuesto Mensual</h3>
                    <p class="text-gray-500 text-sm mt-1">Asigna límites por categoría y monitoriza el progreso con alertas visuales al acercarte al límite.</p>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="w-11 h-11 bg-orange-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-900 text-lg">Gastos Fijos</h3>
                    <p class="text-gray-500 text-sm mt-1">Controla facturas y suscripciones recurrentes. Detecta automáticamente cuáles ya has pagado cada mes.</p>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="w-11 h-11 bg-purple-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-900 text-lg">Financiaciones</h3>
                    <p class="text-gray-500 text-sm mt-1">Gestiona préstamos y compras a plazos. Visualiza la deuda pendiente y el coste total de cada financiación.</p>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="w-11 h-11 bg-teal-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-900 text-lg">Cartera de Inversiones</h3>
                    <p class="text-gray-500 text-sm mt-1">Seguimiento de acciones con P&L FIFO, cotizaciones en tiempo real, conversión EUR/USD y dividendos.</p>
                </div>

            </div>
        </div>

        {{-- Footer --}}
        <div class="max-w-7xl mx-auto px-6 py-12 mt-8 text-center text-sm text-gray-400">
            Control de Gastos · Finanzas personales para humanos
        </div>

    </body>
</html>
