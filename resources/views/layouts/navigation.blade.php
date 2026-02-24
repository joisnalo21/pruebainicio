@php
    $role = Auth::user()->role ?? null;
@endphp

@if($role === 'medico')
    <nav x-data="{ open: false }" class="bg-blue-700 text-white border-b border-blue-800 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center gap-3">
                    <a href="{{ route('medico.dashboard') }}" class="flex items-center gap-3">
                        <div class="h-9 w-9 rounded-xl bg-white/20 flex items-center justify-center font-bold">008</div>
                        <div class="hidden sm:block">
                            <div class="text-sm font-semibold leading-tight">Sistema de Emergencias - Hospital de Jipijapa</div>
                            <div class="text-xs text-blue-100">Módulo Médico</div>
                        </div>
                    </a>

                    <div class="hidden md:flex items-center gap-1 ms-3">
                        <a href="{{ route('medico.dashboard') }}"
                           class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('medico.dashboard') ? 'bg-white/20' : 'hover:bg-white/10' }}">
                            Dashboard
                        </a>
                        <a href="{{ route('medico.pacientes.index') }}"
                           class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('medico.pacientes.*') ? 'bg-white/20' : 'hover:bg-white/10' }}">
                            Pacientes
                        </a>
                        <a href="{{ route('medico.formularios') }}"
                           class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('medico.formularios*') ? 'bg-white/20' : 'hover:bg-white/10' }}">
                            Formularios
                        </a>
                        <a href="{{ route('medico.reportes') }}"
                           class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('medico.reportes*') ? 'bg-white/20' : 'hover:bg-white/10' }}">
                            Reportes
                        </a>
                    </div>
                </div>

                <div class="hidden sm:flex sm:items-center sm:ms-6">
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-3 py-2 rounded-md text-sm font-medium text-white hover:bg-white/10 focus:outline-none transition ease-in-out duration-150">
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
                                {{ __('Perfil') }}
                            </x-dropdown-link>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                        onclick="event.preventDefault(); this.closest('form').submit();">
                                    {{ __('Salir') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                </div>

                <div class="-me-2 flex items-center sm:hidden">
                    <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-blue-100 hover:text-white hover:bg-blue-600 focus:outline-none transition duration-150 ease-in-out">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden border-t border-blue-600">
            <div class="pt-2 pb-3 space-y-1 px-3">
                <a href="{{ route('medico.dashboard') }}"
                   class="block px-3 py-2 rounded-md text-sm {{ request()->routeIs('medico.dashboard') ? 'bg-white/20 text-white' : 'text-blue-50 hover:bg-white/10' }}">
                    Dashboard
                </a>
                <a href="{{ route('medico.pacientes.index') }}"
                   class="block px-3 py-2 rounded-md text-sm {{ request()->routeIs('medico.pacientes.*') ? 'bg-white/20 text-white' : 'text-blue-50 hover:bg-white/10' }}">
                    Pacientes
                </a>
                <a href="{{ route('medico.formularios') }}"
                   class="block px-3 py-2 rounded-md text-sm {{ request()->routeIs('medico.formularios*') ? 'bg-white/20 text-white' : 'text-blue-50 hover:bg-white/10' }}">
                    Formularios
                </a>
                <a href="{{ route('medico.reportes') }}"
                   class="block px-3 py-2 rounded-md text-sm {{ request()->routeIs('medico.reportes*') ? 'bg-white/20 text-white' : 'text-blue-50 hover:bg-white/10' }}">
                    Reportes
                </a>
            </div>

            <div class="pt-4 pb-3 border-t border-blue-600">
                <div class="px-4">
                    <div class="font-medium text-base text-white">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-sm text-blue-100">{{ Auth::user()->email }}</div>
                </div>

                <div class="mt-3 space-y-1 px-3">
                    <a href="{{ route('profile.edit') }}" class="block px-3 py-2 rounded-md text-sm text-blue-50 hover:bg-white/10">
                        Perfil
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <a href="{{ route('logout') }}"
                           onclick="event.preventDefault(); this.closest('form').submit();"
                           class="block px-3 py-2 rounded-md text-sm text-blue-50 hover:bg-white/10">
                            Salir
                        </a>
                    </form>
                </div>
            </div>
        </div>
    </nav>
@else
    <nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="shrink-0 flex items-center">
                        @if($role === 'admin')
                            <a href="{{ route('admin.dashboard') }}">Inicio</a>
                        @elseif($role === 'enfermero')
                            <a href="{{ route('enfermero.dashboard') }}">Inicio</a>
                        @endif
                    </div>

                    <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex"></div>
                </div>

                <div class="hidden sm:flex sm:items-center sm:ms-6">
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
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
                                {{ __('Perfil') }}
                            </x-dropdown-link>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                        onclick="event.preventDefault(); this.closest('form').submit();">
                                    {{ __('Salir') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                </div>

                <div class="-me-2 flex items-center sm:hidden">
                    <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none transition duration-150 ease-in-out">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
            <div class="pt-2 pb-3 space-y-1">
                @if($role === 'admin')
                    <x-responsive-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">
                        {{ __('Inicio') }}
                    </x-responsive-nav-link>
                @elseif($role === 'enfermero')
                    <x-responsive-nav-link :href="route('enfermero.dashboard')" :active="request()->routeIs('enfermero.dashboard')">
                        {{ __('Inicio') }}
                    </x-responsive-nav-link>
                @endif
            </div>

            <div class="pt-4 pb-1 border-t border-gray-200">
                <div class="px-4">
                    <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
                </div>

                <div class="mt-3 space-y-1">
                    <x-responsive-nav-link :href="route('profile.edit')">
                        {{ __('Perfil') }}
                    </x-responsive-nav-link>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-responsive-nav-link :href="route('logout')"
                                onclick="event.preventDefault(); this.closest('form').submit();">
                            {{ __('Salir') }}
                        </x-responsive-nav-link>
                    </form>
                </div>
            </div>
        </div>
    </nav>
@endif
