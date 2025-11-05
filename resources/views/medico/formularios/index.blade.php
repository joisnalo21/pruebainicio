<x-app-layout>
    <div class="p-6">
        <h2 class="text-2xl font-semibold text-blue-700 mb-4">Formularios 008 registrados</h2>

        @if($formularios->isEmpty())
            <p class="text-gray-500">No hay formularios registrados.</p>
        @else
            <table class="min-w-full bg-white border rounded-lg shadow-md">
                <thead class="bg-blue-100 text-gray-700">
                    <tr>
                        <th class="py-2 px-3">Paciente</th>
                        <th class="py-2 px-3">Motivo</th>
                        <th class="py-2 px-3">Diagnóstico</th>
                        <th class="py-2 px-3">Fecha</th>
                        <th class="py-2 px-3">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($formularios as $f)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-2 px-3">{{ $f->paciente->nombre }}</td>
                            <td class="py-2 px-3">{{ $f->motivo }}</td>
                            <td class="py-2 px-3">{{ $f->diagnostico ?? '-' }}</td>
                            <td class="py-2 px-3">{{ $f->created_at->format('d/m/Y') }}</td>
                            <td class="py-2 px-3">
                                <a href="{{ route('medico.formulario.editar', $f->id) }}" class="text-blue-600 hover:underline">Editar</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</x-app-layout>
