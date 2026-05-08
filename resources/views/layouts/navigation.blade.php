<nav x-data="{ open: false }" class="bg-[#1e1b4b] border-b border-indigo-900">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-11 w-auto fill-current text-gray-800" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-6 sm:-my-px sm:ms-10 sm:flex items-center">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>

                    {{-- Finanzas dropdown --}}
                    <x-dropdown align="left" width="48" contentClasses="py-1 bg-[#1e1b4b] border border-indigo-700">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out focus:outline-none
                                {{ request()->routeIs('gastos.*') || request()->routeIs('ingresos.*') || request()->routeIs('importar.*')
                                    ? 'border-indigo-300 text-white'
                                    : 'border-transparent text-indigo-200 hover:text-white hover:border-indigo-300' }}">
                                Finanzas
                                <svg class="ms-1 h-4 w-4 fill-current" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <a href="{{ route('gastos.index') }}" class="block px-4 py-2 text-sm font-medium text-indigo-100 hover:bg-indigo-700 hover:text-white transition duration-150 ease-in-out">Mis Gastos</a>
                            <a href="{{ route('ingresos.index') }}" class="block px-4 py-2 text-sm font-medium text-indigo-100 hover:bg-indigo-700 hover:text-white transition duration-150 ease-in-out">Mis Ingresos</a>
                            <hr class="border-indigo-700 my-1">
                            <a href="{{ route('importar.index') }}" class="block px-4 py-2 text-sm font-medium text-indigo-100 hover:bg-indigo-700 hover:text-white transition duration-150 ease-in-out">Importar CSV</a>
                        </x-slot>
                    </x-dropdown>

                    {{-- Fijos & Préstamos dropdown --}}
                    <x-dropdown align="left" width="48" contentClasses="py-1 bg-[#1e1b4b] border border-indigo-700">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out focus:outline-none
                                {{ request()->routeIs('gastos-fijos.*') || request()->routeIs('financiaciones.*')
                                    ? 'border-indigo-300 text-white'
                                    : 'border-transparent text-indigo-200 hover:text-white hover:border-indigo-300' }}">
                                Fijos y Préstamos
                                <svg class="ms-1 h-4 w-4 fill-current" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <a href="{{ route('gastos-fijos.index') }}" class="block px-4 py-2 text-sm font-medium text-indigo-100 hover:bg-indigo-700 hover:text-white transition duration-150 ease-in-out">Gastos Fijos</a>
                            <a href="{{ route('financiaciones.index') }}" class="block px-4 py-2 text-sm font-medium text-indigo-100 hover:bg-indigo-700 hover:text-white transition duration-150 ease-in-out">Financiaciones</a>
                        </x-slot>
                    </x-dropdown>

                    <x-nav-link :href="route('inversiones.index')" :active="request()->routeIs('inversiones.*')">
                        {{ __('Inversiones') }}
                    </x-nav-link>

                    {{-- Configuración dropdown --}}
                    <x-dropdown align="left" width="48" contentClasses="py-1 bg-[#1e1b4b] border border-indigo-700">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out focus:outline-none
                                {{ request()->routeIs('categorias.*') || request()->routeIs('fuentes.*') || request()->routeIs('reglas.*')
                                    ? 'border-indigo-300 text-white'
                                    : 'border-transparent text-indigo-200 hover:text-white hover:border-indigo-300' }}">
                                Configuración
                                <svg class="ms-1 h-4 w-4 fill-current" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <a href="{{ route('categorias.index') }}" class="block px-4 py-2 text-sm font-medium text-indigo-100 hover:bg-indigo-700 hover:text-white transition duration-150 ease-in-out">Categorías de Gastos</a>
                            <a href="{{ route('fuentes.index') }}" class="block px-4 py-2 text-sm font-medium text-indigo-100 hover:bg-indigo-700 hover:text-white transition duration-150 ease-in-out">Fuentes de Ingresos</a>
                            <a href="{{ route('reglas.index') }}" class="block px-4 py-2 text-sm font-medium text-indigo-100 hover:bg-indigo-700 hover:text-white transition duration-150 ease-in-out">Reglas de Importación</a>
                        </x-slot>
                    </x-dropdown>
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48" contentClasses="py-1 bg-white">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-[#1e1b4b] bg-white hover:text-[#f97316] focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>

            <div class="block px-4 py-1 text-xs font-bold uppercase tracking-widest text-indigo-300">Finanzas</div>
            <x-responsive-nav-link :href="route('gastos.index')" :active="request()->routeIs('gastos.*')">
                {{ __('Mis Gastos') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('ingresos.index')" :active="request()->routeIs('ingresos.*')">
                {{ __('Mis Ingresos') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('importar.index')" :active="request()->routeIs('importar.*')">
                {{ __('Importar CSV') }}
            </x-responsive-nav-link>

            <div class="block px-4 py-1 text-xs font-bold uppercase tracking-widest text-indigo-300">Fijos y Préstamos</div>
            <x-responsive-nav-link :href="route('gastos-fijos.index')" :active="request()->routeIs('gastos-fijos.*')">
                {{ __('Gastos Fijos') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('financiaciones.index')" :active="request()->routeIs('financiaciones.*')">
                {{ __('Financiaciones') }}
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('inversiones.index')" :active="request()->routeIs('inversiones.*')">
                {{ __('Inversiones') }}
            </x-responsive-nav-link>

            <div class="block px-4 py-1 text-xs font-bold uppercase tracking-widest text-indigo-300">Configuración</div>
            <x-responsive-nav-link :href="route('categorias.index')" :active="request()->routeIs('categorias.*')">
                {{ __('Categorías de Gastos') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('fuentes.index')" :active="request()->routeIs('fuentes.*')">
                {{ __('Fuentes de Ingresos') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('reglas.index')" :active="request()->routeIs('reglas.*')">
                {{ __('Reglas de Importación') }}
            </x-responsive-nav-link>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
