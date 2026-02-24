@extends('layouts.admin')

@section('content')
@php
    $numero008 = fn($f) => $f->numero ?? ('008-' . str_pad((string)$f->id, 6, '0', STR_PAD_LEFT));

    $badge = function($f) {
        if ($f->esCompleto()) return 'bg-green-100 text-green-800 border-green-200';
        if ($f->esArchivado()) return 'bg-gray-100 text-gray-800 border-gray-200';
        return 'bg-yellow-100 text-yellow-800 border-yellow-200';
    };

    $label = function($f) {
        if ($f->esCompleto()) return 'COMPLETO';
        if ($f->esArchivado()) return 'ARCHIVADO';
        return 'BORRADOR';
    };
@endphp

<div class="p-6 space-y-6">

    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-extrabold text-gray-900">Formularios 008 (Global)</h1>
            <p class="text-sm text-gray-600 mt-1">Admin: consulta, archiva y elimina. No edita información clínica.</p>
        </div>

        <a href="{{ route('admin.dashboard') }}"
           class="px-4 py-2 rounded-xl border bg-white hover:bg-gray-50 text-sm font-semibold">
            ← Volver al dashboard
        </a>
    </div>

    {{-- KPIs --}}
    <div class="grid grid-cols-1 md:grid-cols-3 xl:grid-cols-6 gap-4">
        <div class="bg-white border rounded-2xl p-5 shadow-sm">
            <div class="text-xs text-gray-500 uppercase">Hoy</div>
            <div class="text-3xl font-extrabold">{{ $stats['hoy'] ?? 0 }}</div>
        </div>
        <div class="bg-white border rounded-2xl p-5 shadow-sm">
            <div class="text-xs text-gray-500 uppercase">Semana</div>
            <div class="text-3xl font-extrabold">{{ $stats['semana'] ?? 0 }}</div>
        </div>
        <div class="bg-white border rounded-2xl p-5 shadow-sm">
            <div class="text-xs text-gray-500 uppercase">Mes</div>
            <div class="text-3xl font-extrabold">{{ $stats['mes'] ?? 0 }}</div>
        </div>
        <div class="bg-white border rounded-2xl p-5 shadow-sm">
            <div class="text-xs text-gray-500 uppercase">Completos</div>
            <div class="text-3xl font-extrabold text-green-700">{{ $stats['completos'] ?? 0 }}</div>
        </div>
        <div class="bg-white border rounded-2xl p-5 shadow-sm">
            <div class="text-xs text-gray-500 uppercase">Borrador</div>
            <div class="text-3xl font-extrabold text-yellow-700">{{ $stats['borrador'] ?? 0 }}</div>
        </div>
        <div class="bg-white border rounded-2xl p-5 shadow-sm">
            <div class="text-xs text-gray-500 uppercase">Archivados</div>
            <div class="text-3xl font-extrabold text-gray-700">{{ $stats['archivados'] ?? 0 }}</div>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="bg-white border rounded-2xl p-5">
        <form method="GET" action="{{ route('admin.formularios.index') }}"
              class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
            <div class="md:col-span-5">
                <label class="block text-sm text-gray-600 mb-1">Buscar</label>
                <input type="text" name="q" value="{{ $q }}"
                       placeholder="Cédula, nombre, ID o 008-000123"
                       class="w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-0"/>
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm text-gray-600 mb-1">Estado</label>
                <select name="estado" class="w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-0">
                    <option value="" {{ $estado === null || $estado === '' ? 'selected' : '' }}>Todos</option>
                    <option value="completo" {{ $estado === 'completo' ? 'selected' : '' }}>Completos</option>
                    <option value="borrador" {{ $estado === 'borrador' || $estado === 'incompleto' ? 'selected' : '' }}>Borrador</option>
                    <option value="archivado" {{ $estado === 'archivado' ? 'selected' : '' }}>Archivados</option>
                </select>
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm text-gray-600 mb-1">Desde</label>
                <input type="date" name="desde" value="{{ $desde }}"
                       class="w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-0"/>
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm text-gray-600 mb-1">Hasta</label>
                <input type="date" name="hasta" value="{{ $hasta }}"
                       class="w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-0"/>
            </div>

            <div class="md:col-span-1">
                <button class="w-full px-4 py-2 rounded-xl bg-gray-900 text-white hover:bg-black text-sm font-semibold">
                    Buscar
                </button>
            </div>
        </form>

        <div class="mt-3 text-xs text-gray-500">
            Tip: escribe <span class="font-semibold">008-</span> para buscar por número o la <span class="font-semibold">cédula</span>.
        </div>
    </div>

    {{-- Tabla --}}
    <div class="bg-white border rounded-2xl overflow-hidden">
        <div class="px-5 py-4 border-b flex items-center justify-between">
            <div class="font-bold text-gray-900">Resultados</div>
            <div class="text-sm text-gray-500">
                Total: <span class="font-semibold">{{ $formularios->total() }}</span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 border-b text-gray-600">
                    <tr class="text-left">
                        <th class="px-5 py-3">N°</th>
                        <th class="px-5 py-3">Paciente</th>
                        <th class="px-5 py-3">Cédula</th>
                        <th class="px-5 py-3">Fecha</th>
                        <th class="px-5 py-3">Registrado por</th>
                        <th class="px-5 py-3">Estado</th>
                        <th class="px-5 py-3 text-right">Acciones</th>
                    </tr>
                </thead>

                <tbody class="divide-y">
                    @forelse($formularios as $f)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-3 font-semibold text-gray-900">{{ $numero008($f) }}</td>

                            <td class="px-5 py-3">
                                <div class="font-semibold text-gray-900">{{ $f->paciente?->nombre_completo ?? $f->paciente?->nombre ?? '—' }}</div>
                                <div class="text-xs text-gray-500">ID Paciente: {{ $f->paciente_id }}</div>
                            </td>

                            <td class="px-5 py-3">{{ $f->paciente?->cedula ?? '—' }}</td>

                            <td class="px-5 py-3">
                                <div class="font-semibold">{{ optional($f->created_at)->format('Y-m-d') }}</div>
                                <div class="text-xs text-gray-500">{{ optional($f->created_at)->format('H:i') }}</div>
                            </td>

                            <td class="px-5 py-3">{{ $f->creador?->name ?? '—' }}</td>

                            <td class="px-5 py-3">
                                <span class="inline-flex items-center px-2 py-1 rounded-full border text-xs font-semibold {{ $badge($f) }}">
                                    {{ $label($f) }}
                                </span>
                            </td>

                            <td class="px-5 py-3">
                                <div class="flex justify-end gap-2 flex-wrap">

                                    <a href="{{ route('admin.formularios.show', $f) }}"
                                       class="px-3 py-2 rounded-xl border bg-white hover:bg-gray-50 text-xs font-semibold">
                                        Ver
                                    </a>

                                    @if($f->esCompleto())
                                        <a href="{{ route('admin.formularios.pdf', $f) }}" target="_blank" rel="noopener"
                                           class="px-3 py-2 rounded-xl bg-gray-900 text-white hover:bg-black text-xs font-semibold">
                                            PDF
                                        </a>
                                    @endif

                                    @if(!$f->esArchivado())
                                        <form method="POST" action="{{ route('admin.formularios.archivar', $f) }}"
                                              onsubmit="return confirm('¿Archivar este formulario?');">
                                            @csrf
                                            @method('PATCH')
                                            <button class="px-3 py-2 rounded-xl border bg-white hover:bg-gray-50 text-xs font-semibold">
                                                Archivar
                                            </button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('admin.formularios.desarchivar', $f) }}"
                                              onsubmit="return confirm('¿Desarchivar y volver a borrador?');">
                                            @csrf
                                            @method('PATCH')
                                            <button class="px-3 py-2 rounded-xl border bg-white hover:bg-gray-50 text-xs font-semibold">
                                                Desarchivar
                                            </button>
                                        </form>
                                    @endif

                                    <button type="button"
                                            class="px-3 py-2 rounded-xl bg-red-600 text-white hover:bg-red-700 text-xs font-semibold"
                                            onclick="openDeleteModal({{ $f->id }}, '{{ $numero008($f) }}')">
                                        Eliminar
                                    </button>
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

        <div class="p-4 border-t">
            {{ $formularios->links() }}
        </div>
    </div>
</div>

{{-- Modal eliminar (confirmación fuerte) --}}
<div id="deleteModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50" onclick="closeDeleteModal()"></div>

    <div class="relative max-w-lg mx-auto mt-24 bg-white rounded-2xl shadow-xl border overflow-hidden">
        <div class="p-5 border-b">
            <h3 class="text-lg font-extrabold text-gray-900">Confirmación de eliminación</h3>
            <p class="text-sm text-gray-600 mt-1">Escribe <b>ELIMINAR</b> para confirmar. Acción irreversible.</p>
        </div>

        <div class="p-5 space-y-4">
            <div class="text-sm">
                Formulario: <span id="delFormNum" class="font-bold"></span>
            </div>

            <input id="confirmText" type="text"
                   class="w-full rounded-xl border-gray-300 focus:ring-0 focus:border-gray-900"
                   placeholder="Escribe ELIMINAR"
                   oninput="toggleDeleteBtn()"/>

            <form id="deleteForm" method="POST" action="#">
                @csrf
                @method('DELETE')

                <div class="flex justify-end gap-2">
                    <button type="button"
                            class="px-4 py-2 rounded-xl border bg-white hover:bg-gray-50 text-sm font-semibold"
                            onclick="closeDeleteModal()">
                        Cancelar
                    </button>

                    <button id="deleteBtn" type="submit" disabled
                            class="px-4 py-2 rounded-xl bg-red-600 text-white text-sm font-semibold opacity-50 cursor-not-allowed">
                        Eliminar definitivamente
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openDeleteModal(id, numero) {
        const modal = document.getElementById('deleteModal');
        const delNum = document.getElementById('delFormNum');
        const form = document.getElementById('deleteForm');
        const input = document.getElementById('confirmText');
        const btn = document.getElementById('deleteBtn');

        delNum.textContent = numero;
        input.value = '';
        btn.disabled = true;
        btn.classList.add('opacity-50', 'cursor-not-allowed');

        form.action = "{{ route('admin.formularios.destroy', ':id') }}".replace(':id', id);

        modal.classList.remove('hidden');
        setTimeout(() => input.focus(), 50);
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.add('hidden');
    }

    function toggleDeleteBtn() {
        const input = document.getElementById('confirmText');
        const btn = document.getElementById('deleteBtn');
        const ok = (input.value || '').trim().toUpperCase() === 'ELIMINAR';
        btn.disabled = !ok;
        btn.classList.toggle('opacity-50', !ok);
        btn.classList.toggle('cursor-not-allowed', !ok);
    }

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeDeleteModal();
    });
</script>
@endsection
