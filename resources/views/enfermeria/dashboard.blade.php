@extends('layouts.enfermeria')

@section('content')
<div class="space-y-6">

    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-green-700">Panel de Enfermería</h1>
            <p class="text-sm text-gray-600">
                Bienvenida/o, <span class="font-semibold">{{ Auth::user()->name }}</span>.
                Consulta Formularios 008 y administra pacientes.
            </p>
        </div>

        <div class="text-sm text-gray-600 bg-white border rounded-xl px-4 py-2 shadow-sm">
            <div class="font-semibold text-gray-800">{{ now()->format('d/m/Y') }}</div>
            <div class="text-xs">Hora: {{ now()->format('H:i') }}</div>
        </div>
    </div>

    {{-- KPIs --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white border rounded-2xl p-4 shadow-sm">
            <div class="text-xs text-gray-500">Formularios 008 hoy</div>
            <div class="text-2xl font-bold">{{ $stats['formularios_hoy'] ?? 0 }}</div>
            <div class="text-xs text-gray-500 mt-1">Excluye archivados</div>
        </div>

        <div class="bg-white border rounded-2xl p-4 shadow-sm">
            <div class="text-xs text-gray-500">Formularios este mes</div>
            <div class="text-2xl font-bold">{{ $stats['formularios_mes'] ?? 0 }}</div>
        </div>

        <div class="bg-white border rounded-2xl p-4 shadow-sm">
            <div class="text-xs text-gray-500">Pendientes (borrador)</div>
            <div class="text-2xl font-bold">{{ $stats['pendientes'] ?? 0 }}</div>
        </div>

        <div class="bg-white border rounded-2xl p-4 shadow-sm">
            <div class="text-xs text-gray-500">Pacientes registrados hoy</div>
            <div class="text-2xl font-bold">{{ $stats['pacientes_hoy'] ?? 0 }}</div>
        </div>
    </div>

    {{-- Acciones rápidas --}}
    <div class="bg-white border rounded-2xl p-5 shadow-sm">
        <h2 class="text-lg font-semibold text-gray-800 mb-3">Acciones rápidas</h2>

       <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            <a href="{{ route('enfermero.pacientes.create') }}"
               class="border rounded-xl p-4 hover:bg-gray-50 transition">
                <div class="text-lg">👤</div>
                <div class="font-semibold mt-1">Nuevo paciente</div>
                <div class="text-xs text-gray-600">Registro para Formulario 008</div>
            </a>

            <a href="{{ route('enfermero.pacientes.index') }}"
               class="border rounded-xl p-4 hover:bg-gray-50 transition">
                <div class="text-lg">🗂️</div>
                <div class="font-semibold mt-1">Administrar pacientes</div>
                <div class="text-xs text-gray-600">Buscar / editar / validar</div>
            </a>

            <a href="{{ route('enfermero.formularios.index') }}"
               class="border rounded-xl p-4 hover:bg-gray-50 transition">
                <div class="text-lg">📄</div>
                <div class="font-semibold mt-1">Formularios 008</div>
                <div class="text-xs text-gray-600">Ver / PDF / estados</div>
            </a>
        </div>
    </div>

    {{-- Últimos formularios --}}
    <div class="bg-white border rounded-2xl p-5 shadow-sm">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-800">Últimos Formularios 008</h2>
            <a href="{{ route('enfermero.formularios.index') }}"
               class="text-sm text-green-700 font-semibold hover:underline">
                Ver todos →
            </a>
        </div>

        <div class="overflow-x-auto mt-4">
            <table class="w-full text-sm">
                <thead class="text-left text-gray-600 border-b">
                    <tr>
                        <th class="py-2 pr-3">N°</th>
                        <th class="py-2 pr-3">Paciente</th>
                        <th class="py-2 pr-3">Estado</th>
                        <th class="py-2 pr-3">Actualización</th>
                        <th class="py-2 pr-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($recentForms as $f)
                        <tr class="hover:bg-gray-50">
                            <td class="py-3 pr-3 font-semibold">{{ $f->numero }}</td>
                            <td class="py-3 pr-3">
                                <div class="font-semibold">
                                    {{ $f->paciente?->nombre_completo ?? '—' }}
                                </div>
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
                            <td class="py-3 pr-3 text-gray-600">
                                {{ optional($f->updated_at)->format('d/m/Y H:i') }}
                            </td>
                            <td class="py-3 pr-3">
                                <div class="flex justify-end gap-2">
                                    <a class="px-3 py-2 rounded-lg border text-sm hover:bg-white"
                                       href="{{ route('enfermero.formularios.ver.paso', ['formulario'=>$f->id, 'paso'=>1]) }}">
                                        Ver
                                    </a>

                                    @if($f->esCompleto())
                                        <a class="px-3 py-2 rounded-lg bg-gray-900 text-white text-sm hover:bg-black"
                                           target="_blank" rel="noopener noreferrer"
                                           href="{{ route('enfermero.formularios.pdf', $f->id) }}">
                                            PDF
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-gray-500">
                                No hay formularios recientes.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
