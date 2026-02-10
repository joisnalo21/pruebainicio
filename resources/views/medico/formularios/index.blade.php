<x-app-layout>

    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Formularios 008
                </h2>
                <p class="text-sm text-gray-500">Gestión clínica de fichas médicas.</p>
            </div>

            <div class="flex flex-wrap gap-2">
                {{-- NUEVO BOTÓN --}}
                <a href="{{ route('medico.formularios.nuevo') }}"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-blue-700 rounded-md font-semibold text-sm text-white hover:bg-blue-700">
                    Nuevo Formulario 008
                </a>

                <a href="{{ route('medico.pacientes.index') }}"
                    class="inline-flex items-center px-4 py-2 bg-white border border-gray-200 rounded-md font-semibold text-sm text-gray-700 hover:bg-gray-50">
                    Pacientes
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Flash messages --}}
            @if(session('success'))
            <div class="mb-4 p-4 rounded-lg bg-green-50 border border-green-200 text-green-800">
                {{ session('success') }}
            </div>
            @endif

            {{-- KPIs --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white shadow-sm rounded-xl p-5 border-l-4 border-blue-500">
                    <div class="text-sm text-gray-500">Formularios hoy</div>
                    <div class="text-3xl font-bold text-blue-700">{{ $stats['hoy'] ?? 0 }}</div>
                </div>

                <div class="bg-white shadow-sm rounded-xl p-5 border-l-4 border-green-500">
                    <div class="text-sm text-gray-500">Este mes</div>
                    <div class="text-3xl font-bold text-green-700">{{ $stats['mes'] ?? 0 }}</div>
                </div>

                <div class="bg-white shadow-sm rounded-xl p-5 border-l-4 border-yellow-500">
                    <div class="text-sm text-gray-500">Pendientes</div>
                    <div class="text-3xl font-bold text-yellow-700">{{ $stats['pendientes'] ?? 0 }}</div>
                </div>

                <div class="bg-white shadow-sm rounded-xl p-5 border-l-4 border-red-500">
                    <div class="text-sm text-gray-500">Trauma/Accidente (estimado)</div>
                    <div class="text-3xl font-bold text-red-700">{{ $stats['trauma'] ?? 0 }}</div>
                </div>
            </div>

            {{-- Filtros --}}
            <div class="bg-white shadow-sm rounded-xl p-5 mb-6">
                <form method="GET" action="{{ route('medico.formularios') }}" class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
                    <div class="md:col-span-5">
                        <label class="block text-sm text-gray-600 mb-1">Buscar</label>
                        <input type="text" name="q" value="{{ $q }}"
                            placeholder="Cédula, nombre del paciente o Nº 008-000123"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm text-gray-600 mb-1">Estado</label>
                        <select name="estado" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                            <option value="" {{ $estado === null || $estado === '' ? 'selected' : '' }}>Todos</option>
                            <option value="completo" {{ $estado === 'completo' ? 'selected' : '' }}>Completos</option>
                            <option value="incompleto" {{ $estado === 'incompleto' ? 'selected' : '' }}>Incompletos</option>
                            <option value="archivado" {{ $estado === 'archivado' ? 'selected' : '' }}>Archivados</option>

                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm text-gray-600 mb-1">Desde</label>
                        <input type="date" name="desde" value="{{ $desde }}"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2" />
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm text-gray-600 mb-1">Hasta</label>
                        <input type="date" name="hasta" value="{{ $hasta }}"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2" />
                    </div>

                    <div class="md:col-span-1 flex gap-2">
                        <button type="submit"
                            class="w-full bg-gray-900 hover:bg-black text-white px-4 py-2 rounded-lg font-semibold">
                            Buscar
                        </button>
                    </div>
                </form>

                <div class="mt-3 text-sm text-gray-500">
                    Tip: escribe <span class="font-semibold">008-</span> para buscar por número, o la <span class="font-semibold">cédula</span>.
                </div>
            </div>

            {{-- Tabla --}}
            <div class="bg-white shadow-sm rounded-xl overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-800">Últimos formularios</h3>
                    <div class="text-sm text-gray-500">
                        Total: <span class="font-semibold">{{ $formularios->total() }}</span>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-gray-50">
                            <tr class="text-left text-sm text-gray-600">
                                <th class="px-5 py-3">N°</th>
                                <th class="px-5 py-3">Paciente</th>
                                <th class="px-5 py-3">Cédula</th>
                                <th class="px-5 py-3">Fecha</th>
                                <th class="px-5 py-3">Registrado por</th>
                                <th class="px-5 py-3">Estado</th>
                                <th class="px-5 py-3 text-right">Acciones</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100">
                            @forelse($formularios as $form)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-4 font-semibold text-gray-800">{{ $form->numero }}</td>

                                <td class="px-5 py-4">
                                    <div class="font-semibold text-gray-800">{{ $form->paciente?->nombre_completo ?? '—' }}</div>
                                    <div class="text-xs text-gray-500">ID Paciente: {{ $form->paciente_id }}</div>
                                </td>

                                <td class="px-5 py-4 text-gray-700">{{ $form->paciente?->cedula ?? '—' }}</td>

                                <td class="px-5 py-4 text-gray-700">
                                    @php
                                    $dt = $form->created_at?->timezone('America/Guayaquil');
                                    @endphp
                                    <div class="font-semibold">{{ $dt?->format('Y-m-d') }}</div>
                                    <div class="text-xs text-gray-500">{{ $dt?->format('H:i') }}</div>
                                </td>

                                <td class="px-5 py-4 text-gray-700">
                                    {{ $form->creador?->name ?? '—' }}
                                </td>

                                <td class="px-5 py-4">
                                    @if($form->esCompleto())

                                    <span class="inline-flex items-center gap-2 bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-semibold">
                                        Completo
                                    </span>
                                    @elseif($form->esArchivado())
                                    <span class="inline-flex items-center gap-2 bg-gray-100 text-gray-700 px-3 py-1 rounded-full text-xs font-semibold">
                                        Archivado
                                    </span>
                                    @else
                                    <span class="inline-flex items-center gap-2 bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-xs font-semibold">
                                        Incompleto
                                    </span>
                                    @endif
                                </td>

                                <td class="px-5 py-4 text-right">
                                    <div class="flex justify-end gap-2">

                                        {{-- CONTINUAR (solo borrador o archivado) --}}
                                        @if($form->esBorrador() || $form->esArchivado())
                                        <a href="{{ route('medico.formularios.paso', ['formulario' => $form->id, 'paso' => $form->paso_actual]) }}"
                                            class="bg-gray-900 hover:bg-black text-white px-3 py-2 rounded-lg text-sm font-semibold">
                                            Continuar
                                        </a>
                                        @endif

                                        {{-- VER (solo completos) --}}
                                        @if($form->esCompleto())
                                        <a href="{{ route('medico.formularios.ver.paso', ['formulario' => $form->id, 'paso' => 13]) }}"
                                            class="bg-white hover:bg-gray-50 border border-gray-200 px-3 py-2 rounded-lg text-sm font-semibold">
                                            Ver
                                        </a>
                                        @endif

                                        {{-- PDF (solo completos) --}}
                                        @if($form->esCompleto())
                                        <a href="{{ route('medico.formularios.pdf', $form->id) }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="bg-white hover:bg-gray-50 border border-gray-200 px-3 py-2 rounded-lg text-sm font-semibold">
                                            PDF
                                        </a>
                                        @endif


                                        {{-- ARCHIVAR (solo borradores) --}}
                                        @if($form->esBorrador())
                                        <form method="POST" action="{{ route('medico.formularios.archivar', $form->id) }}"
                                            onsubmit="return confirm('¿Archivar este formulario incompleto? Se ocultará del listado principal.');">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                class="bg-white hover:bg-gray-50 border border-gray-200 px-3 py-2 rounded-lg text-sm font-semibold">
                                                Archivar
                                            </button>
                                        </form>
                                        @endif

                                        {{-- DESARCHIVAR (solo archivados) --}}
                                        @if($form->esArchivado())
                                        <form method="POST" action="{{ route('medico.formularios.desarchivar', $form->id) }}"
                                            onsubmit="return confirm('¿Desarchivar y volver a mostrar en el listado principal?');">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg text-sm font-semibold">
                                                Desarchivar
                                            </button>
                                        </form>
                                        @endif

                                    </div>
                                </td>




                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="px-5 py-10 text-center text-gray-500">
                                    No se encontraron formularios con esos filtros.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="px-5 py-4 border-t border-gray-100">
                    {{ $formularios->links() }}
                </div>
            </div>

        </div>
    </div>

</x-app-layout>