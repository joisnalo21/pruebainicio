<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Panel Médico - Sistema Emergencias 008</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen font-sans">
  <!-- Header -->
  <header class="bg-blue-700 text-white py-4 shadow-md">
    <div class="container mx-auto flex justify-between items-center px-6">
      <h1 class="text-xl font-semibold">Sistema Emergencias - Hospiital de Jipijapa</h1>
      <div class="flex items-center space-x-4">
        <p>Bienvenido, {{ Auth::user()->name }}</p>
        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button type="submit" class="bg-blue-500 hover:bg-blue-600 px-3 py-1 rounded">Salir</button>
        </form>
      </div>
    </div>
  </header>

  <main class="p-6">
    <h2 class="text-2xl font-semibold text-blue-700 mb-6">Panel Médico</h2>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
      <div class="bg-white shadow-md rounded-xl p-5 border-l-4 border-blue-500">
        <h3 class="text-gray-500 text-sm">Pacientes Registrados</h3>
        <p class="text-3xl font-bold text-blue-700">{{ $totalPacientes ?? 0 }}</p>
      </div>
      <div class="bg-white shadow-md rounded-xl p-5 border-l-4 border-green-500">
        <h3 class="text-gray-500 text-sm">Formularios Completados</h3>
        <p class="text-3xl font-bold text-green-600">{{ $totalFormularios ?? 0 }}</p>
      </div>
      <div class="bg-white shadow-md rounded-xl p-5 border-l-4 border-yellow-500">
        <h3 class="text-gray-500 text-sm">Casos Pendientes</h3>
        <p class="text-3xl font-bold text-yellow-600">4</p>
      </div>
    </div>

    <div class="flex gap-4">
      <a href="{{ route('medico.pacientes.index') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold shadow"> Gestionar Pacientes</a>
      <a href="{{ route('medico.formularios') }}" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-semibold shadow"> Formularios 008</a>
      <a href="{{ route('medico.reportes') }}" class="bg-yellow-500 hover:bg-yellow-600 text-white px-6 py-3 rounded-lg font-semibold shadow"> Reportes</a>
    </div>
  </main>
</body>
</html>
