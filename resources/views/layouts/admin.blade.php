<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'Emergencia008') }} - Admin</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>

<body class="bg-gray-100 text-gray-900">
    <div class="min-h-screen flex">

        {{-- Sidebar --}}
        <aside class="w-72 hidden lg:flex flex-col bg-white border-r">
            <div class="px-6 py-5 border-b">
                <div class="text-lg font-extrabold text-gray-900">Panel Admin</div>
                <div class="text-xs text-gray-500 mt-1">Formulario 008</div>
            </div>

            <nav class="p-4 space-y-2">
                <a href="{{ route('admin.dashboard') }}"
                   class="flex items-center gap-2 px-4 py-3 rounded-xl border bg-white hover:bg-gray-50 text-sm font-semibold">
                    📊 Dashboard
                </a>

                <a href="{{ route('admin.formularios.index') }}"
                   class="flex items-center gap-2 px-4 py-3 rounded-xl border bg-white hover:bg-gray-50 text-sm font-semibold">
                    📄 Formularios 008
                </a>

                <a href="{{ route('admin.pacientes.index') }}"
                   class="flex items-center gap-2 px-4 py-3 rounded-xl border bg-white hover:bg-gray-50 text-sm font-semibold">
                    🧾 Pacientes (vista)
                </a>

                <a href="{{ route('admin.usuarios.index') }}"
                   class="flex items-center gap-2 px-4 py-3 rounded-xl border bg-white hover:bg-gray-50 text-sm font-semibold">
                    👥 Usuarios
                </a>
            </nav>

            <div class="mt-auto p-4 border-t">
                <div class="text-xs text-gray-500 mb-2">
                    Sesión: <span class="font-semibold text-gray-700">{{ Auth::user()->name ?? '—' }}</span>
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="w-full px-4 py-2 rounded-xl bg-gray-900 text-white hover:bg-black text-sm font-semibold">
                        Cerrar sesión
                    </button>
                </form>
            </div>
        </aside>

        {{-- Main --}}
        <main class="flex-1">
            {{-- Topbar (mobile) --}}
            <div class="lg:hidden bg-white border-b px-4 py-3 flex items-center justify-between">
                <div class="font-extrabold">Admin</div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.dashboard') }}" class="text-sm px-3 py-2 rounded-lg border bg-white">Dashboard</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-sm px-3 py-2 rounded-lg bg-gray-900 text-white">Salir</button>
                    </form>
                </div>
            </div>

            {{-- Flash messages --}}
            <div class="max-w-7xl mx-auto">
                @if(session('success'))
                    <div class="m-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-800">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="m-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-800">
                        {{ session('error') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="m-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-900">
                        <div class="font-bold mb-1">Hay errores en el formulario:</div>
                        <ul class="list-disc pl-5 text-sm">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>

            {{-- Content --}}
            <div class="max-w-7xl mx-auto">
                @yield('content')
            </div>
        </main>

    </div>

    @stack('scripts')
</body>
</html>
