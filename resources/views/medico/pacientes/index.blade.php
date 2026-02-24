

<x-app-layout>
    <div class="p-6 max-w-7xl mx-auto">
        <!-- Encabezado -->
        <div class="flex flex-col sm:flex-row justify-between sm:items-center mb-6 gap-3">
            <h2 class="text-2xl font-semibold text-blue-700 flex items-center gap-2">
                 Gestión de Pacientes
            </h2>
            <a href="{{ route($rp.'pacientes.create') }}"
               class="bg-blue-600 text-white px-5 py-2 rounded-lg shadow hover:bg-blue-700 transition">
                + Nuevo Paciente
            </a>
        </div>

        <!-- Búsqueda -->
        <form method="GET" action="{{ route($rp.'pacientes.index') }}"
              class="flex flex-col sm:flex-row gap-3 mb-6">
            <input type="text" name="buscar" value="{{ request('buscar') }}"
                   placeholder="Buscar por nombre o cédula"
                   class="border border-gray-300 rounded-lg px-4 py-2 flex-1 focus:outline-none focus:ring focus:ring-blue-300">
            <button type="submit"
                    class="bg-blue-500 text-white px-6 py-2 rounded-lg hover:bg-blue-600 transition shadow">
                Buscar
            </button>
        </form>

        <!-- Tabla -->
        @if ($pacientes->isEmpty())
            <p class="text-gray-600 text-center py-10 text-lg">
                No hay pacientes registrados.
            </p>
        @else
            <div class="overflow-x-auto bg-white rounded-lg shadow-lg border border-gray-100">
                <table class="w-full text-sm text-left text-gray-700">
                    <thead class="bg-blue-100 text-blue-800 uppercase text-xs font-semibold">
                        <tr>
                            <th class="px-5 py-3 text-center">#</th>
                            <th class="px-5 py-3">Cédula</th>
                            <th class="px-5 py-3">Nombres</th>
                            <th class="px-5 py-3 text-center">Edad</th>
                            <th class="px-5 py-3 text-center">Sexo</th>
                            <th class="px-5 py-3">Provincia</th>
                            <th class="px-5 py-3 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pacientes as $paciente)
                            <tr class="border-b hover:bg-gray-50 transition">
                                <td class="px-5 py-3 text-center font-medium text-gray-600">{{ $loop->iteration }}</td>
                                <td class="px-5 py-3 font-semibold">{{ $paciente->cedula }}</td>
                                <td class="px-5 py-3">
                                    {{ $paciente->primer_nombre }} {{ $paciente->segundo_nombre }}
                                    {{ $paciente->apellido_paterno }} {{ $paciente->apellido_materno }}
                                </td>
                                <td class="px-5 py-3 text-center">{{ $paciente->edad ?? '-' }}</td>
                                <td class="px-5 py-3 text-center">{{ $paciente->sexo ?? '-' }}</td>
                                <td class="px-5 py-3">{{ $paciente->provincia ?? '-' }}</td>
                                <td class="px-5 py-3 text-center">
                                    <div class="flex justify-center gap-2">
                                        <a href="{{ route($rp.'pacientes.edit', $paciente->id) }}"
   class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded text-sm">Editar ✏️</a>

                                        <form action="{{ route($rp.'pacientes.destroy', $paciente->id) }}"
                                              method="POST"
                                              onsubmit="return confirm('¿Seguro que deseas eliminar este paciente?')"
                                              class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded-md text-sm shadow transition">
                                                🗑️
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</x-app-layout>
@php
  $rp = \Illuminate\Support\Str::startsWith(\Illuminate\Support\Facades\Route::currentRouteName(), 'enfermero.')
        ? 'enfermero.'
        : 'medico.';
@endphp
