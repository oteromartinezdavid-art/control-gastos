<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <link rel="icon" type="image/png" href="{{ asset('img/logo.png') }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <style>
            /* Botones principales: Azul oscuro del logo */
            .bg-indigo-600, .bg-gray-800 {
                background-color: #1e1b4b !important;
            }

            .bg-indigo-600:hover, .bg-gray-800:hover {
                background-color: #312e81 !important; /* Un azul un poco más claro al pasar el ratón */
            }

            /* Botones de acción o alertas: Naranja del logo */
            .text-indigo-600 {
                color: #f97316 !important;
            }
            /* Fondo de la barra */
            nav { background-color: #1e1b4b !important; }

            /* Enlaces inactivos */
            nav a, nav button { color: #d1d5db !important; }

            /* Enlace activo (con la línea naranja debajo) */
            .inline-flex.items-center.px-1.pt-1.border-b-2.border-indigo-400 {
                border-color: #f97316 !important; /* Naranja del logo */
                color: #ffffff !important;
            }

            /* Hover: cuando pasas el ratón */
            nav a:hover { color: #38bdf8 !important; } /* Turquesa del logo */
        </style>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
