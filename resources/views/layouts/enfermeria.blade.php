<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Sistema Médico') }} - Enfermería</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 min-h-screen">

    {{-- Barra superior --}}
    <nav class="bg-green-600 text-white shadow-md">
        <div class="max-w-7xl mx-auto px-6 py-3 flex justify-between items-center">
            <div class="flex items-center space-x-2">
                <span class="text-2xl">💉</span>
                <h1 class="font-semibold text-lg">Módulo de Enfermería</h1>
            </div>

            <div class="flex items-center space-x-6">
                <a href="{{ route('enfermero.dashboard') }}" class="hover:underline">Inicio</a>
                <a href="#" class="hover:underline">Fichas</a>
                <a href="#" class="hover:underline">Reportes</a>

                @auth
                    <span class="text-sm">👤 {{ Auth::user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="bg-red-500 hover:bg-red-600 px-3 py-1 rounded text-sm">
                            Cerrar sesión
                        </button>
                    </form>
                @endauth
            </div>
        </div>
    </nav>

    {{-- Contenido principal --}}
    <main class="max-w-6xl mx-auto py-10 px-6">
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="text-center text-gray-500 text-sm py-4 border-t mt-10">
        © {{ date('Y') }} Hospital Jipijapa – Módulo de Enfermería
    </footer>

</body>
</html>
