@extends('layouts.enfermeria')

@section('content')
<div class="space-y-6">

    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-green-700">Formularios 008</h1>
            <p class="text-sm text-gray-600">Consulta rápida y vista resumen (solo lectura).</p>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-sm">
            <div class="bg-white border rounded-xl p-3">
                <div class="text-xs text-gray-500">Hoy</div>
                <div class="font-bold">{{ $stats['hoy'] ?? 0 }}</div>
            </div>
            <div class="bg-white border rounded-xl p-3">
                <div class="text-xs text-gray-500">Mes</div>
                <div class="font-bold">{{ $stats['mes'] ?? 0 }}</div>
            </div>
            <div class="bg-white border rounded-xl p-3">
                <div class="text-xs text-gray-500">Borrador</div>
                <div class="font-bold">{{ $stats['pendientes'] ?? 0 }}</div>
            </div>
            <div class="bg-white border rounded-xl p-3">
                <div class="text-xs text-gray-500">Completo</div>
                <div class="font-bold">{{ $stats['completos'] ?? 0 }}</div>
            </div>
        </div>
    </div>

    <div class="bg-white border rounded-2xl p-5 shadow-sm">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-3">
            <input name="q" value="{{ $q }}" placeholder="Buscar CI, nombre, 008-000123…"
                class="md:col-span-2 border rounded-xl px-3 py-2 text-sm">

            <select name="estado" class="border rounded-xl px-3 py-2 text-sm">
                <option value="" {{ $estado==='' || $estado===null ? 'selected' : '' }}>Activos (sin archivados)</option>
                <option value="incompleto" {{ $estado==='incompleto' ? 'selected' : '' }}>Borrador</option>
                <option value="completo" {{ $estado==='completo' ? 'selected' : '' }}>Completo</option>
                <option value="archivado" {{ $estado==='archivado' ? 'selected' : '' }}>Archivado</option>
            </select>

            <input type="date" name="desde" value="{{ $desde }}" class="border rounded-xl px-3 py-2 text-sm">
            <input type="date" name="hasta" value="{{ $hasta }}" class="border rounded-xl px-3 py-2 text-sm">

            <div class="md:col-span-5 flex justify-end gap-2">
                <a href="{{ route('enfermero.formularios.index') }}"
                    class="px-4 py-2 rounded-xl border text-sm hover:bg-gray-50">Limpiar</a>
                <button class="px-4 py-2 rounded-xl bg-gray-900 text-white text-sm hover:bg-black">
                    Filtrar
                </button>
            </div>
        </form>
    </div>

    <div class="bg-white border rounded-2xl p-5 shadow-sm overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-left text-gray-600 border-b">
                <tr>
                    <th class="py-2 pr-3">N°</th>
                    <th class="py-2 pr-3">Paciente</th>
                    <th class="py-2 pr-3">Estado</th>
                    <th class="py-2 pr-3">Actualizado</th>
                    <th class="py-2 pr-3 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($formularios as $f)
                <tr class="hover:bg-gray-50">
                    <td class="py-3 pr-3 font-semibold">{{ $f->numero }}</td>
                    <td class="py-3 pr-3">
                        <div class="font-semibold">{{ $f->paciente?->nombre_completo ?? '—' }}</div>
                        <div class="text-xs text-gray-500">CI: {{ $f->paciente?->cedula ?? '—' }}</div>
                    </td>
                    <td class="py-3 pr-3">
                        @if($f->esCompleto())
                        <span class="px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">Completo</span>
                        @elseif($f->esArchivado())
                        <span class="px-2 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-800">Archivado</span>
                        @else
                        <span class="px-2 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">Borrador</span>
                        @endif
                    </td>
                    <td class="py-3 pr-3 text-gray-600">{{ optional($f->updated_at)->format('d/m/Y H:i') }}</td>
                    <td class="py-3 pr-3">
                        <div class="flex justify-end gap-2">
                            <a href="{{ route('enfermero.formularios.ver.paso', ['formulario'=>$f->id, 'paso'=>1]) }}"
                                class="px-3 py-2 rounded-xl border text-sm hover:bg-white">
                                Ver 
                            </a>

                            @if($f->esCompleto())
                            <a target="_blank" rel="noopener"
                                href="{{ route('enfermero.formularios.pdf', $f->id) }}"
                                class="px-3 py-2 rounded-xl bg-gray-900 text-white text-sm hover:bg-black">
                                PDF
                            </a>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="py-6 text-center text-gray-500">No hay resultados.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <div class="mt-4">
            {{ $formularios->links() }}
        </div>
    </div>

</div>
@endsection