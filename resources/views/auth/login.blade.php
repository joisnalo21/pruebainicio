<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Sistema Emergencias 008</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white shadow-xl rounded-2xl w-full max-w-md p-8 border border-gray-200">
        <h1 class="text-2xl font-semibold text-center text-blue-700 mb-6">Sistema de Emergencias del Hospital de Jipijapa</h1>

        @if ($errors->any())
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
                <ul class="list-disc pl-5 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf
            <div>
                <label for="username" class="block text-sm font-medium text-gray-700">Usuario</label>
                <input id="username" name="username" type="text" required autofocus
                       class="mt-1 block w-full rounded-lg border border-gray-300 p-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Contraseña</label>
                <div class="mt-1 relative">
                    <input id="password" name="password" type="password" required
                           class="block w-full rounded-lg border border-gray-300 p-2.5 pr-24 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <button id="toggle-password" type="button"
                            class="absolute inset-y-0 right-2 my-1 px-3 rounded-md text-sm font-medium text-blue-700 hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            aria-controls="password" aria-label="Mostrar contraseña" aria-pressed="false">
                        Mostrar
                    </button>
                </div>
            </div>

            <button type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 rounded-lg shadow-md transition">
                Iniciar sesión
            </button>
        </form>

        <p class="text-center text-sm text-gray-500 mt-6">
            Hospital General de Jipijapa - Ministerio de Salud Pública
        </p>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const passwordInput = document.getElementById('password');
            const toggleBtn = document.getElementById('toggle-password');
            if (!passwordInput || !toggleBtn) return;

            toggleBtn.addEventListener('click', () => {
                const isHidden = passwordInput.type === 'password';
                passwordInput.type = isHidden ? 'text' : 'password';
                toggleBtn.textContent = isHidden ? 'Ocultar' : 'Mostrar';
                toggleBtn.setAttribute('aria-label', isHidden ? 'Ocultar contraseña' : 'Mostrar contraseña');
                toggleBtn.setAttribute('aria-pressed', isHidden ? 'true' : 'false');
            });
        });
    </script>
</body>
</html>
