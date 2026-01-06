@extends('layouts.admin')

@section('content')
@php
    // Variables esperadas desde AdminController@index:
    // $kpi = [
    //   'pacientes_total' => int,
    //   'f_hoy' => int,
    //   'f_semana' => int,
    //   'f_mes' => int,
    //   'completos' => int,
    //   'borrador' => int,
    //   'archivados' => int (opcional),
    // ];
    //
    // $ultimosFormularios: collection<Formulario008> con ->paciente y (opcional) ->creador cargados
    //
    $k = $kpi ?? [];
    $badge = function(string $estado) {
        return match($estado) {
            'completo'  => 'bg-green-100 text-green-800 border-green-200',
            'borrador'  => 'bg-yellow-100 text-yellow-800 border-yellow-200',
            'archivado' => 'bg-gray-100 text-gray-800 border-gray-200',
            default     => 'bg-blue-100 text-blue-800 border-blue-200',
        };
    };

    $estadoLabel = function(string $estado) {
        return match($estado) {
            'completo'  => 'COMPLETO',
            'borrador'  => 'BORRADOR',
            'archivado' => 'ARCHIVADO',
            default     => strtoupper($estado),
        };
    };

    $numero008 = function($f) {
        // Si tienes accesor numero, úsalo. Si no, lo generamos aquí.
        return $f->numero ?? ('008-' . str_pad((string)$f->id, 6, '0', STR_PAD_LEFT));
    };
@endphp

<div class="p-6 space-y-6">

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-extrabold text-gray-900">Panel Administrador</h1>
            <p class="text-sm text-gray-600 mt-1">
                Control general del sistema (Formulario 008). Solo lectura clínica: ver, archivar, eliminar y gestionar usuarios.
            </p>
        </div>

        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.usuarios.index') }}"
               class="px-4 py-2 rounded-xl border bg-white hover:bg-gray-50 text-sm font-semibold">
                👥 Usuarios
            </a>
            <a href="{{ route('admin.formularios.index') }}"
               class="px-4 py-2 rounded-xl bg-gray-900 text-white hover:bg-black text-sm font-semibold">
                📄 Formularios 008
            </a>
            <a href="{{ route('admin.pacientes.index') }}"
               class="px-4 py-2 rounded-xl border bg-white hover:bg-gray-50 text-sm font-semibold">
                🧾 Pacientes
            </a>
        </div>
    </div>

    {{-- KPI Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">

        <div class="bg-white border rounded-2xl p-5 shadow-sm">
            <div class="text-xs uppercase tracking-wide text-gray-500">Pacientes</div>
            <div class="mt-2 flex items-end justify-between">
                <div class="text-3xl font-extrabold text-gray-900">{{ $k['pacientes_total'] ?? 0 }}</div>
                <div class="text-sm text-gray-600">total</div>
            </div>
            <div class="mt-3 text-xs text-gray-500">
                Vista global (admin no edita historia clínica).
            </div>
        </div>

        <div class="bg-white border rounded-2xl p-5 shadow-sm">
            <div class="text-xs uppercase tracking-wide text-gray-500">Formularios 008</div>
            <div class="mt-2 grid grid-cols-3 gap-2">
                <div class="border rounded-xl p-3">
                    <div class="text-xs text-gray-500">Hoy</div>
                    <div class="text-2xl font-extrabold">{{ $k['f_hoy'] ?? 0 }}</div>
                </div>
                <div class="border rounded-xl p-3">
                    <div class="text-xs text-gray-500">Semana</div>
                    <div class="text-2xl font-extrabold">{{ $k['f_semana'] ?? 0 }}</div>
                </div>
                <div class="border rounded-xl p-3">
                    <div class="text-xs text-gray-500">Mes</div>
                    <div class="text-2xl font-extrabold">{{ $k['f_mes'] ?? 0 }}</div>
                </div>
            </div>
            <div class="mt-3 text-xs text-gray-500">
                Conteo por creación. Usa filtros en “Formularios 008” para auditoría.
            </div>
        </div>

        <div class="bg-white border rounded-2xl p-5 shadow-sm">
            <div class="text-xs uppercase tracking-wide text-gray-500">Estado (activos)</div>
            <div class="mt-2 grid grid-cols-2 gap-2">
                <div class="border rounded-xl p-3">
                    <div class="text-xs text-gray-500">Completos</div>
                    <div class="text-2xl font-extrabold text-green-700">{{ $k['completos'] ?? 0 }}</div>
                </div>
                <div class="border rounded-xl p-3">
                    <div class="text-xs text-gray-500">Borrador</div>
                    <div class="text-2xl font-extrabold text-yellow-700">{{ $k['borrador'] ?? 0 }}</div>
                </div>
            </div>
            <div class="mt-3 text-xs text-gray-500">
                El PDF solo está disponible si el formulario está completo.
            </div>
        </div>

        <div class="bg-white border rounded-2xl p-5 shadow-sm">
            <div class="text-xs uppercase tracking-wide text-gray-500">Acciones rápidas</div>

            <div class="mt-3 space-y-2">
                <a href="{{ route('admin.formularios.index', ['estado' => 'borrador']) }}"
                   class="block px-4 py-2 rounded-xl border bg-white hover:bg-gray-50 text-sm font-semibold">
                    🟡 Ver borradores
                </a>
                <a href="{{ route('admin.formularios.index', ['estado' => 'completo']) }}"
                   class="block px-4 py-2 rounded-xl border bg-white hover:bg-gray-50 text-sm font-semibold">
                    🟢 Ver completos
                </a>
                <a href="{{ route('admin.formularios.index', ['estado' => 'archivado']) }}"
                   class="block px-4 py-2 rounded-xl border bg-white hover:bg-gray-50 text-sm font-semibold">
                    📦 Ver archivados
                </a>
            </div>

            <div class="mt-3 text-xs text-gray-500">
                “Eliminar” requiere confirmación fuerte.
            </div>
        </div>

    </div>

    {{-- Últimos formularios --}}
    <div class="bg-white border rounded-2xl shadow-sm overflow-hidden">
        <div class="p-5 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div>
                <h2 class="text-lg font-bold text-gray-900">Últimos formularios 008</h2>
                <p class="text-sm text-gray-600">Acceso rápido para auditoría y acciones administrativas.</p>
            </div>

            <div class="flex gap-2">
                <a href="{{ route('admin.formularios.index') }}"
                   class="px-4 py-2 rounded-xl border bg-white hover:bg-gray-50 text-sm font-semibold">
                    Ver todo →
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 border-t border-b text-gray-600">
                    <tr>
                        <th class="text-left px-5 py-3">Formulario</th>
                        <th class="text-left px-5 py-3">Paciente</th>
                        <th class="text-left px-5 py-3">Estado</th>
                        <th class="text-left px-5 py-3">Fecha</th>
                        <th class="text-right px-5 py-3">Acciones</th>
                    </tr>
                </thead>

                <tbody class="divide-y">
                    @forelse($ultimosFormularios ?? [] as $f)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-3">
                                <div class="font-semibold text-gray-900">{{ $numero008($f) }}</div>
                                <div class="text-xs text-gray-500">
                                    ID: {{ $f->id }}
                                    @if(isset($f->creador))
                                        · Creado por: {{ $f->creador->name }}
                                    @endif
                                </div>
                            </td>

                            <td class="px-5 py-3">
                                <div class="font-semibold text-gray-900">
                                    {{ $f->paciente?->nombre_completo ?? '—' }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    CI: {{ $f->paciente?->cedula ?? '—' }}
                                </div>
                            </td>

                            <td class="px-5 py-3">
                                <span class="inline-flex items-center px-2 py-1 rounded-full border text-xs font-semibold {{ $badge($f->estado ?? 'borrador') }}">
                                    {{ $estadoLabel($f->estado ?? 'borrador') }}
                                </span>
                            </td>

                            <td class="px-5 py-3">
                                <div class="text-gray-900 font-semibold">
                                    {{ optional($f->created_at)->format('d/m/Y') }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ optional($f->created_at)->format('H:i') }}
                                </div>
                            </td>

                            <td class="px-5 py-3">
                                <div class="flex items-center justify-end gap-2 flex-wrap">

                                    <a href="{{ route('admin.formularios.show', $f->id) }}"
                                       class="px-3 py-2 rounded-xl border bg-white hover:bg-gray-50 text-xs font-semibold">
                                        Ver
                                    </a>

                                    @if(($f->estado ?? null) === 'completo')
                                        <a target="_blank" rel="noopener"
                                           href="{{ route('admin.formularios.pdf', $f->id) }}"
                                           class="px-3 py-2 rounded-xl bg-gray-900 text-white hover:bg-black text-xs font-semibold">
                                            PDF
                                        </a>
                                    @endif

                                    @if(($f->estado ?? null) !== 'archivado')
                                        <form method="POST" action="{{ route('admin.formularios.archivar', $f->id) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                class="px-3 py-2 rounded-xl border bg-white hover:bg-gray-50 text-xs font-semibold">
                                                Archivar
                                            </button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('admin.formularios.desarchivar', $f->id) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                class="px-3 py-2 rounded-xl border bg-white hover:bg-gray-50 text-xs font-semibold">
                                                Desarchivar
                                            </button>
                                        </form>
                                    @endif

                                    {{-- Eliminar con confirmación fuerte --}}
                                    <button
                                        type="button"
                                        class="px-3 py-2 rounded-xl bg-red-600 text-white hover:bg-red-700 text-xs font-semibold"
                                        onclick="openDeleteModal({{ $f->id }}, '{{ $numero008($f) }}')">
                                        Eliminar
                                    </button>

                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-8 text-center text-gray-500">
                                No hay formularios recientes.
                            </td>
                        </tr>
                    @endforelse
                </tbody>

            </table>
        </div>
    </div>

</div>

{{-- Modal eliminar --}}
<div id="deleteModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50" onclick="closeDeleteModal()"></div>

    <div class="relative max-w-lg mx-auto mt-24 bg-white rounded-2xl shadow-xl border overflow-hidden">
        <div class="p-5 border-b">
            <h3 class="text-lg font-extrabold text-gray-900">Confirmación de eliminación</h3>
            <p class="text-sm text-gray-600 mt-1">
                Esto eliminará el formulario del sistema. Es una acción irreversible.
            </p>
        </div>

        <div class="p-5 space-y-4">
            <div class="text-sm text-gray-700">
                Formulario: <span id="delFormNum" class="font-bold text-gray-900"></span>
            </div>

            <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-900">
                Escribe <span class="font-extrabold">ELIMINAR</span> para confirmar.
            </div>

            <input id="confirmText"
                   type="text"
                   class="w-full rounded-xl border-gray-300 focus:ring-0 focus:border-gray-900"
                   placeholder="Escribe ELIMINAR"
                   oninput="toggleDeleteBtn()"/>

            <form id="deleteForm" method="POST" action="#">
                @csrf
                @method('DELETE')

                <div class="flex items-center justify-end gap-2">
                    <button type="button"
                            class="px-4 py-2 rounded-xl border bg-white hover:bg-gray-50 text-sm font-semibold"
                            onclick="closeDeleteModal()">
                        Cancelar
                    </button>

                    <button id="deleteBtn"
                            type="submit"
                            disabled
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
        btn.classList.remove('opacity-100', 'cursor-pointer');

        // action dinámica
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

        if (ok) {
            btn.classList.remove('opacity-50', 'cursor-not-allowed');
            btn.classList.add('opacity-100', 'cursor-pointer');
        } else {
            btn.classList.add('opacity-50', 'cursor-not-allowed');
            btn.classList.remove('opacity-100', 'cursor-pointer');
        }
    }

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeDeleteModal();
    });
</script>
@endsection
