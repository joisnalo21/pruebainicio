<x-app-layout>
    <div class="p-6">
        <h2 class="text-2xl font-semibold text-blue-700 mb-4">Reportes Médicos</h2>

        <form method="GET" class="mb-6 flex space-x-4">
            <input type="date" name="desde" class="border rounded p-2" value="{{ request('desde') }}">
            <input type="date" name="hasta" class="border rounded p-2" value="{{ request('hasta') }}">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Filtrar</button>
        </form>

        @if($formularios->isEmpty())
            <p class="text-gray-500">No hay datos para el rango seleccionado.</p>
        @else
            <table class="min-w-full bg-white border rounded-lg shadow-md">
                <thead class="bg-yellow-100 text-gray-700">
                    <tr>
                        <th class="py-2 px-3">Paciente</th>
                        <th class="py-2 px-3">Motivo</th>
                        <th class="py-2 px-3">Diagnóstico</th>
                        <th class="py-2 px-3">Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($formularios as $f)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-2 px-3">{{ $f->paciente->nombre }}</td>
                            <td class="py-2 px-3">{{ $f->motivo }}</td>
                            <td class="py-2 px-3">{{ $f->diagnostico ?? '-' }}</td>
                            <td class="py-2 px-3">{{ $f->created_at->format('d/m/Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</x-app-layout>
