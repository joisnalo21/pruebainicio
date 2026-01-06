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

    {{-- Topbar --}}
    <header class="sticky top-0 z-40 bg-white/90 backdrop-blur border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-3 flex items-center justify-between gap-3">

            <div class="flex items-center gap-3 min-w-0">
                
                <div class="min-w-0">
                    <div class="font-extrabold text-gray-900 leading-tight truncate">
                        Panel Admin
                    </div>
                    <div class="text-xs text-gray-500 truncate">
                        Formulario 008 · {{ Auth::user()->name ?? '—' }}
                    </div>
                </div>
            </div>

            {{-- Right actions --}}
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.dashboard') }}"
                   class="hidden sm:inline-flex px-3 py-2 rounded-xl border bg-white hover:bg-gray-50 text-sm font-semibold">
                    Dashboard
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="px-3 py-2 rounded-xl bg-gray-900 text-white hover:bg-black text-sm font-semibold">
                        Salir
                    </button>
                </form>
            </div>
        </div>

        {{-- Nav principal --}}
        <nav class="max-w-7xl mx-auto px-4 sm:px-6 pb-3">
            <div class="flex flex-wrap gap-2">
                @php
                    $nav = [
                        ['label' => 'Dashboard',   'route' => 'admin.dashboard',        'icon' => '📊'],
                        ['label' => 'Formularios', 'route' => 'admin.formularios.index','icon' => '📄'],
                        ['label' => 'Pacientes',   'route' => 'admin.pacientes.index',  'icon' => '🧾'],
                        ['label' => 'Reportes',    'route' => 'admin.reportes.index',   'icon' => '📈'],
                        ['label' => 'Usuarios',    'route' => 'admin.usuarios.index',   'icon' => '👥'],
                    ];

                    $current = request()->route()?->getName() ?? '';
                @endphp

                @foreach($nav as $item)
                    @php
                        $isActive = str_starts_with($current, $item['route']);
                    @endphp

                    <a href="{{ route($item['route']) }}"
                       class="inline-flex items-center gap-2 px-3 py-2 rounded-xl text-sm font-semibold border transition
                              {{ $isActive ? 'bg-gray-900 text-white border-gray-900' : 'bg-white hover:bg-gray-50 border-gray-200 text-gray-700' }}">
                        <span>{{ $item['icon'] }}</span>
                        <span>{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </div>
        </nav>
    </header>

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
    <main class="max-w-7xl mx-auto px-4 sm:px-6 py-6">
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="py-8 text-center text-xs text-gray-500">
        {{ config('app.name', 'Emergencia008') }} · Admin · {{ now()->format('Y') }}
    </footer>

    @stack('scripts')
</body>
</html>
