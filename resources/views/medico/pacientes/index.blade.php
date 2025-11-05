<x-app-layout>
    <div class="p-6">
        <h2 class="text-2xl font-semibold text-blue-700 mb-4">Gestión de Pacientes</h2>

        @if($pacientes->isEmpty())
            <p class="text-gray-500">No hay pacientes registrados.</p>
        @else
            <table class="min-w-full bg-white border rounded-lg shadow-md">
                <thead class="bg-blue-100 text-gray-700">
                    <tr>
                        <th class="py-2 px-3">Cédula</th>
                        <th class="py-2 px-3">Nombre</th>
                        <th class="py-2 px-3">Edad</th>
                        <th class="py-2 px-3">Dirección</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pacientes as $p)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-2 px-3">{{ $p->cedula }}</td>
                            <td class="py-2 px-3">{{ $p->nombre }}</td>
                            <td class="py-2 px-3">{{ $p->edad }}</td>
                            <td class="py-2 px-3">{{ $p->direccion }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</x-app-layout>
