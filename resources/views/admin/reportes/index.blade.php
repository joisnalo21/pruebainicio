@extends('layouts.admin')

@section('content')
@php
$tipo = $filters['tipo'] ?? 'prod';
$estado = $filters['estado'] ?? 'activos';
$group = $filters['group'] ?? 'day';
@endphp

<div class="space-y-6">

    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-extrabold text-gray-900">Reportes</h1>
            <p class="text-sm text-gray-600 mt-1">Reportes profesionales + exportación PDF.</p>
        </div>

        <a href="{{ route('admin.reportes.pdf', request()->query()) }}"
            target="_blank" rel="noopener"
            class="bg-white hover:bg-gray-50 border border-gray-200 px-3 py-2 rounded-lg text-sm font-semibold">
            Ver PDF
        </a>

    </div>

    {{-- Filtros --}}
    <div class="bg-white border rounded-2xl p-5 space-y-4">

        <form method="GET" action="{{ route('admin.reportes.index') }}" class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
            <div class="md:col-span-5">
                <label class="block text-sm text-gray-600 mb-1">Tipo de reporte</label>
                <select name="tipo" class="w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-0">
                    @foreach(($ui['tipos'] ?? []) as $k => $label)
                    <option value="{{ $k }}" @selected($tipo===$k)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-3">
                <label class="block text-sm text-gray-600 mb-1">Estado</label>
                <select name="estado" class="w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-0">
                    @foreach(($ui['estado'] ?? []) as $k => $label)
                    <option value="{{ $k }}" @selected($estado===$k)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm text-gray-600 mb-1">Desde</label>
                <input type="date" name="desde" value="{{ $filters['desde'] ?? '' }}"
                    class="w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-0">
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm text-gray-600 mb-1">Hasta</label>
                <input type="date" name="hasta" value="{{ $filters['hasta'] ?? '' }}"
                    class="w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-0">
            </div>

            {{-- Group (solo tiene sentido en producción) --}}
            <div class="md:col-span-3">
                <label class="block text-sm text-gray-600 mb-1">Agrupar por (para Producción)</label>
                <select name="group" class="w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-0">
                    @foreach(($ui['group'] ?? []) as $k => $label)
                    <option value="{{ $k }}" @selected($group===$k)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-3">
                <label class="block text-sm text-gray-600 mb-1">Rol (creador)</label>
                <select name="role" class="w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-0">
                    @foreach(($ui['roles'] ?? []) as $k => $label)
                    <option value="{{ $k }}" @selected(($filters['role'] ?? '' )===$k)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-4">
                <label class="block text-sm text-gray-600 mb-1">Usuario (creador)</label>
                <select name="user_id" class="w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-0">
                    <option value="">Todos</option>
                    @foreach(($ui['users'] ?? []) as $u)
                    <option value="{{ $u->id }}" @selected((string)($filters['user_id'] ?? '' )===(string)$u->id)>
                        {{ $u->name }} ({{ strtoupper($u->role) }})
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-2">
                <button class="w-full px-4 py-2 rounded-xl bg-gray-900 text-white hover:bg-black text-sm font-semibold">
                    Generar
                </button>
            </div>

            {{-- Filtros avanzados (paciente) --}}
            <div class="md:col-span-12">
                <div x-data="{ open: false }" class="rounded-2xl border bg-gray-50">
                    <button type="button"
                        @click="open = !open"
                        class="w-full flex items-center justify-between px-4 py-3">
                        <div class="text-sm font-semibold text-gray-800">
                            Filtros avanzados (paciente)
                            <span class="text-xs text-gray-500 font-normal ml-2">Opcional</span>
                        </div>
                        <span class="text-sm text-gray-600" x-text="open ? '▲' : '▼'"></span>
                    </button>

                    <div x-show="open" x-cloak class="px-4 pb-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3">
                            <div class="lg:col-span-2">
                                <label class="block text-xs text-gray-600 mb-1">Sexo</label>
                                <input name="sexo" value="{{ $filters['sexo'] ?? '' }}"
                                    placeholder="M / F"
                                    class="w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-0 bg-white">
                            </div>

                            <div class="lg:col-span-2">
                                <label class="block text-xs text-gray-600 mb-1">Edad (min)</label>
                                <input type="number" name="edad_min" value="{{ $filters['edad_min'] ?? '' }}"
                                    placeholder="0"
                                    class="w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-0 bg-white">
                            </div>

                            <div class="lg:col-span-2">
                                <label class="block text-xs text-gray-600 mb-1">Edad (max)</label>
                                <input type="number" name="edad_max" value="{{ $filters['edad_max'] ?? '' }}"
                                    placeholder="120"
                                    class="w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-0 bg-white">
                            </div>

                            <div class="lg:col-span-2">
                                <label class="block text-xs text-gray-600 mb-1">Provincia</label>
                                <input name="provincia" value="{{ $filters['provincia'] ?? '' }}"
                                    placeholder="Manabí"
                                    class="w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-0 bg-white">
                            </div>

                            <div class="lg:col-span-2">
                                <label class="block text-xs text-gray-600 mb-1">Cantón</label>
                                <input name="canton" value="{{ $filters['canton'] ?? '' }}"
                                    placeholder="Jipijapa"
                                    class="w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-0 bg-white">
                            </div>

                            <div class="lg:col-span-2">
                                <label class="block text-xs text-gray-600 mb-1">Parroquia</label>
                                <input name="parroquia" value="{{ $filters['parroquia'] ?? '' }}"
                                    placeholder="Jipijapa"
                                    class="w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-0 bg-white">
                            </div>
                        </div>

                        <div class="mt-3 flex flex-wrap items-center gap-2 text-xs text-gray-500">
                            <span>Tip: usa estos filtros cuando generes “Demografía” o “Diagnósticos”.</span>
                            <a href="{{ route('admin.reportes.index') }}"
                                class="ml-auto inline-flex items-center px-3 py-2 rounded-xl border bg-white hover:bg-gray-100 text-xs font-semibold text-gray-700">
                                Limpiar filtros
                            </a>
                        </div>
                    </div>
                </div>
            </div>

        </form>
    </div>

    {{-- Resultado --}}
    <div class="bg-white border rounded-2xl overflow-hidden">
        <div class="px-5 py-4 border-b">
            <div class="font-bold text-gray-900">{{ $report['title'] ?? 'Resultado' }}</div>
            <div class="text-xs text-gray-500 mt-1">
                Rango: {{ $filters['desde'] ?? '—' }} a {{ $filters['hasta'] ?? '—' }}
                · Estado: {{ strtoupper($filters['estado'] ?? '—') }}
                @if(($filters['role'] ?? '') !== '') · Rol: {{ strtoupper($filters['role']) }} @endif
                @if(($filters['user_id'] ?? '') !== '') · Usuario ID: {{ $filters['user_id'] }} @endif
            </div>
            @if(!empty($report['note']))
            <div class="text-xs text-gray-500 mt-1">{{ $report['note'] }}</div>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 border-b text-gray-600">
                    <tr>
                        @foreach(($report['columns'] ?? []) as $c)
                        <th class="text-left px-5 py-3">{{ $c['label'] }}</th>
                        @endforeach
                    </tr>
                </thead>

                <tbody class="divide-y">
                    @forelse(($report['rows'] ?? []) as $r)
                    <tr class="hover:bg-gray-50">
                        @foreach(($report['columns'] ?? []) as $i => $c)
                        @php $val = $r[$i] ?? ''; @endphp
                        <td class="px-5 py-3 {{ is_numeric($val) ? 'text-right font-semibold' : '' }}">
                            {{ $val }}
                        </td>
                        @endforeach
                    </tr>
                    @empty
                    <tr>
                        <td class="px-5 py-10 text-center text-gray-500" colspan="{{ count($report['columns'] ?? []) ?: 1 }}">
                            No hay datos para mostrar con esos filtros.
                        </td>
                    </tr>
                    @endforelse
                </tbody>

                @if(is_array($report['totals'] ?? null))
                <tfoot class="bg-gray-50 border-t">
                    <tr>
                        @foreach(($report['columns'] ?? []) as $i => $c)
                        @php $val = $report['totals'][$i] ?? ''; @endphp
                        <td class="px-5 py-3 font-bold {{ $i===0 ? '' : 'text-right' }}">
                            {{ $val }}
                        </td>
                        @endforeach
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

</div>
@endsection