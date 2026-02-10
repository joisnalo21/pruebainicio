<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Panel Médico')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 text-gray-900">

    <!-- Navbar -->
    <nav class="bg-blue-700 text-white shadow-md">
    <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
        <div class="flex items-center space-x-2">
            
            <span class="font-semibold text-lg">Hospital General de Jipijapa</span>
        </div>
        <div class="flex space-x-8">
            <a href="{{ route('medico.pacientes.index') }}"
               class="px-4 py-2 bg-green-600 text-white rounded-md border border-green-700 hover:bg-green-500 transition">
               Pacientes
            </a>
            <a href="{{ route('medico.formularios') }}"
               class="px-4 py-2 bg-green-600 text-white rounded-md border border-green-700 hover:bg-green-500 transition">
               Formularios
            </a>
            
        </div>
    </div>
</nav>


    <!-- Contenido principal -->
    <main class="p-6 max-w-7xl mx-auto">
        @yield('content')
    </main>

    <!-- Pie de página -->
    <footer class="bg-gray-200 text-center py-4 mt-8">
        <p class="text-sm text-gray-600">&copy; {{ date('Y') }} Hospital Jipijapa — Módulo Médico</p>
    </footer>

    <!-- Scripts adicionales -->
    @stack('scripts')
</body>
</html>
