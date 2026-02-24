<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Panel Médico</h2>
                <p class="text-sm text-gray-600">
                    {{ now()->format('l, d \d\e F \d\e Y') }} · Gestión rápida de pacientes y formularios.
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('medico.formularios.nuevo') }}"
                   class="inline-flex items-center gap-2 bg-gray-900 hover:bg-black text-white px-4 py-2 rounded-lg font-semibold text-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 5v14M5 12h14"/>
                    </svg>
                    Nuevo Formulario 008
                </a>

                <a href="{{ route('medico.formularios') }}"
                   class="inline-flex items-center gap-2 bg-white hover:bg-gray-50 border border-gray-200 px-4 py-2 rounded-lg font-semibold text-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 11h6M9 15h6M7 3h10a2 2 0 0 1 2 2v16l-3-2-3 2-3-2-3 2V5a2 2 0 0 1 2-2z"/>
                    </svg>
                    Ver Formularios
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <section class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white shadow-sm rounded-2xl p-5 border-l-4 border-blue-500">
                    <div class="text-sm text-gray-500">Pacientes registrados</div>
                    <div class="text-3xl font-bold text-blue-700">{{ $totalPacientes ?? 0 }}</div>
                    <div class="text-xs text-gray-500 mt-1">Total en el sistema</div>
                </div>

                <div class="bg-white shadow-sm rounded-2xl p-5 border-l-4 border-green-500">
                    <div class="text-sm text-gray-500">Formularios hoy</div>
                    <div class="text-3xl font-bold text-green-700">{{ $stats['hoy'] ?? 0 }}</div>
                    <div class="text-xs text-gray-500 mt-1">Registrados en el día</div>
                </div>

                <div class="bg-white shadow-sm rounded-2xl p-5 border-l-4 border-indigo-500">
                    <div class="text-sm text-gray-500">Formularios este mes</div>
                    <div class="text-3xl font-bold text-indigo-700">{{ $stats['mes'] ?? 0 }}</div>
                    <div class="text-xs text-gray-500 mt-1">Desde inicio del mes</div>
                </div>
            </section>

            <section class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <a href="{{ route('medico.pacientes.index') }}"
                   class="group bg-white border shadow-sm rounded-2xl p-5 hover:shadow-md transition">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="font-semibold text-gray-900">Pacientes</div>
                            <div class="text-sm text-gray-600">Registrar / buscar</div>
                        </div>
                        <div class="h-10 w-10 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center group-hover:bg-blue-100">
                            <span class="font-bold">P</span>
                        </div>
                    </div>
                </a>

                <a href="{{ route('medico.formularios') }}"
                   class="group bg-white border shadow-sm rounded-2xl p-5 hover:shadow-md transition">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="font-semibold text-gray-900">Formularios 008</div>
                            <div class="text-sm text-gray-600">Bandeja clínica</div>
                        </div>
                        <div class="h-10 w-10 rounded-xl bg-green-50 text-green-700 flex items-center justify-center group-hover:bg-green-100">
                            <span class="font-bold">F</span>
                        </div>
                    </div>
                </a>

                <a href="{{ route('medico.formularios.nuevo') }}"
                   class="group bg-white border shadow-sm rounded-2xl p-5 hover:shadow-md transition">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="font-semibold text-gray-900">Nuevo 008</div>
                            <div class="text-sm text-gray-600">Crear registro</div>
                        </div>
                        <div class="h-10 w-10 rounded-xl bg-gray-100 text-gray-900 flex items-center justify-center group-hover:bg-gray-200">
                            <span class="font-bold">+</span>
                        </div>
                    </div>
                </a>
            </section>

            <section class="bg-white shadow-sm rounded-2xl overflow-hidden border">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-800">Actividad reciente</h3>
                    <a href="{{ route('medico.formularios') }}" class="text-sm font-semibold text-blue-700 hover:underline">
                        Ver todo
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-gray-50">
                            <tr class="text-left text-sm text-gray-600">
                                <th class="px-5 py-3">N°</th>
                                <th class="px-5 py-3">Paciente</th>
                                <th class="px-5 py-3">Fecha</th>
                                <th class="px-5 py-3">Estado</th>
                                <th class="px-5 py-3 text-right">Acción</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100">
                            @forelse($ultimosFormularios ?? [] as $f)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-5 py-4 font-semibold text-gray-900">{{ $f->numero }}</td>

                                    <td class="px-5 py-4">
                                        <div class="font-semibold text-gray-800">{{ $f->paciente?->nombre_completo ?? '—' }}</div>
                                        <div class="text-xs text-gray-500">{{ $f->paciente?->cedula ?? '—' }}</div>
                                    </td>

                                    <td class="px-5 py-4 text-gray-700">
                                        <div class="font-semibold">{{ $f->created_at?->format('Y-m-d') }}</div>
                                        <div class="text-xs text-gray-500">{{ $f->created_at?->format('H:i') }}</div>
                                    </td>

                                    <td class="px-5 py-4">
                                        @if($f->esCompleto())
                                            <span class="inline-flex items-center bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-semibold">Completo</span>
                                        @elseif($f->esArchivado())
                                            <span class="inline-flex items-center bg-gray-100 text-gray-700 px-3 py-1 rounded-full text-xs font-semibold">Archivado</span>
                                        @else
                                            <span class="inline-flex items-center bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-xs font-semibold">Borrador</span>
                                        @endif
                                    </td>

                                    <td class="px-5 py-4 text-right">
                                        <div class="flex justify-end gap-2">
                                            @if($f->esBorrador() || $f->esArchivado())
                                                <a href="{{ route('medico.formularios.paso', ['formulario' => $f->id, 'paso' => $f->paso_actual]) }}"
                                                   class="bg-gray-900 hover:bg-black text-white px-3 py-2 rounded-lg text-sm font-semibold">
                                                    Continuar
                                                </a>
                                            @endif

                                            @if($f->esCompleto())
                                                <a href="{{ route('medico.formularios.ver.paso', ['formulario' => $f->id, 'paso' => 13]) }}"
                                                   class="bg-white hover:bg-gray-50 border border-gray-200 px-3 py-2 rounded-lg text-sm font-semibold">
                                                    Ver
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-10 text-center text-gray-500">
                                        Aún no hay actividad reciente.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
