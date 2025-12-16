<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Nuevo Formulario 008
                </h2>
                <p class="text-sm text-gray-500">Seleccione el paciente para iniciar la atención.</p>
            </div>

            <a href="{{ route('medico.formularios') }}"
               class="inline-flex items-center px-4 py-2 bg-white border border-gray-200 rounded-md font-semibold text-sm text-gray-700 hover:bg-gray-50">
                ← Volver a Formularios
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white shadow-sm rounded-xl p-6 mb-6">
                <form method="GET" action="{{ route('medico.formularios.nuevo') }}" class="flex flex-col sm:flex-row gap-3">
                    <input type="text"
                           name="q"
                           value="{{ $q }}"
                           placeholder="Buscar por cédula, nombres o apellidos..."
                           class="flex-1 border border-gray-300 rounded-lg px-4 py-2" />
                    <button class="bg-gray-900 text-white px-5 py-2 rounded-lg font-semibold">
                        🔎 Buscar
                    </button>
                </form>
            </div>

            <div class="bg-white shadow-sm rounded-xl overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="font-semibold text-gray-800">Pacientes</h3>
                    <span class="text-sm text-gray-500">Total: {{ $pacientes->total() }}</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-gray-50">
                        <tr class="text-left text-sm text-gray-600">
                            <th class="px-6 py-3">Paciente</th>
                            <th class="px-6 py-3">Cédula</th>
                            <th class="px-6 py-3">Edad</th>
                            <th class="px-6 py-3">Teléfono</th>
                            <th class="px-6 py-3 text-right">Acción</th>
                        </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100">
                        @forelse($pacientes as $paciente)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-gray-800">{{ $paciente->nombre_completo }}</div>
                                    <div class="text-xs text-gray-500">
                                        {{ $paciente->provincia ?? '—' }} / {{ $paciente->canton ?? '—' }} / {{ $paciente->parroquia ?? '—' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-gray-700">{{ $paciente->cedula }}</td>
                                <td class="px-6 py-4 text-gray-700">{{ $paciente->edad }}</td>
                                <td class="px-6 py-4 text-gray-700">{{ $paciente->telefono ?? '—' }}</td>
                                <td class="px-6 py-4 text-right">
                                    <form method="POST" action="{{ route('medico.formularios.iniciar') }}">
                                        @csrf
                                        <input type="hidden" name="paciente_id" value="{{ $paciente->id }}">
                                        <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold">
                                            Iniciar 008 →
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                                    No se encontraron pacientes.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $pacientes->links() }}
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
