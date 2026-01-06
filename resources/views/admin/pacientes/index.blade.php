@extends('layouts.admin')

@section('content')
@php
  $nombre = function($p) {
      return trim(implode(' ', array_filter([
          $p->primer_nombre ?? null,
          $p->segundo_nombre ?? null,
          $p->apellido_paterno ?? null,
          $p->apellido_materno ?? null,
      ]))) ?: ($p->nombre_completo ?? '—');
  };
@endphp

<div class="p-6 space-y-6">

  <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-3">
    <div>
      <h1 class="text-2xl font-extrabold text-gray-900">Pacientes (solo lectura)</h1>
      <p class="text-sm text-gray-600 mt-1">Admin puede consultar información y navegar a Formularios 008 asociados.</p>
    </div>

    <a href="{{ route('admin.dashboard') }}"
       class="px-4 py-2 rounded-xl border bg-white hover:bg-gray-50 text-sm font-semibold">
      ← Volver al dashboard
    </a>
  </div>

  <div class="bg-white border rounded-2xl p-5">
    <form method="GET" action="{{ route('admin.pacientes.index') }}"
          class="flex flex-col md:flex-row gap-2">
      <input name="buscar" value="{{ $buscar }}"
             class="w-full md:w-[28rem] rounded-xl border-gray-300 focus:border-gray-900 focus:ring-0"
             placeholder="Buscar por cédula, nombres o apellidos">
      <button class="px-4 py-2 rounded-xl bg-gray-900 text-white hover:bg-black text-sm font-semibold">
        Buscar
      </button>
      @if($buscar)
        <a href="{{ route('admin.pacientes.index') }}"
           class="px-4 py-2 rounded-xl border bg-white hover:bg-gray-50 text-sm font-semibold">
          Limpiar
        </a>
      @endif
    </form>
    <div class="mt-2 text-xs text-gray-500">
      Tip: puedes buscar por <span class="font-semibold">cédula</span> o por el <span class="font-semibold">nombre completo</span>.
    </div>
  </div>

  <div class="bg-white border rounded-2xl overflow-hidden">
    <div class="px-5 py-4 border-b flex items-center justify-between">
      <div class="font-bold text-gray-900">Resultados</div>
      <div class="text-sm text-gray-500">
        Total: <span class="font-semibold">{{ $pacientes->total() }}</span>
      </div>
    </div>

    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-gray-50 border-b text-gray-600">
          <tr class="text-left">
            <th class="px-5 py-3">Paciente</th>
            <th class="px-5 py-3">Cédula</th>
            <th class="px-5 py-3">Teléfono</th>
            <th class="px-5 py-3">Ubicación</th>
            <th class="px-5 py-3">Creado</th>
            <th class="px-5 py-3 text-right">Acciones</th>
          </tr>
        </thead>

        <tbody class="divide-y">
          @forelse($pacientes as $p)
            <tr class="hover:bg-gray-50">
              <td class="px-5 py-3">
                <div class="font-semibold text-gray-900">{{ $nombre($p) }}</div>
                <div class="text-xs text-gray-500">
                  Sexo: {{ $p->sexo ?? '—' }} · Edad: {{ $p->edad ?? '—' }}
                </div>
              </td>

              <td class="px-5 py-3 font-semibold">{{ $p->cedula ?? '—' }}</td>

              <td class="px-5 py-3">{{ $p->telefono ?? '—' }}</td>

              <td class="px-5 py-3">
                <div class="text-gray-900">{{ $p->provincia ?? '—' }}</div>
                <div class="text-xs text-gray-500">{{ $p->canton ?? '' }} {{ $p->parroquia ? '· '.$p->parroquia : '' }}</div>
              </td>

              <td class="px-5 py-3">
                <div class="font-semibold">{{ optional($p->created_at)->format('Y-m-d') }}</div>
                <div class="text-xs text-gray-500">{{ optional($p->created_at)->format('H:i') }}</div>
              </td>

              <td class="px-5 py-3">
                <div class="flex justify-end gap-2 flex-wrap">
                  <a href="{{ route('admin.pacientes.show', $p) }}"
                     class="px-3 py-2 rounded-xl border bg-white hover:bg-gray-50 text-xs font-semibold">
                    Ver
                  </a>

                  {{-- Atajo: ver formularios del paciente (usa búsqueda por cédula) --}}
                  <a href="{{ route('admin.formularios.index', ['q' => $p->cedula]) }}"
                     class="px-3 py-2 rounded-xl bg-gray-900 text-white hover:bg-black text-xs font-semibold">
                    Formularios 008
                  </a>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="px-5 py-10 text-center text-gray-500">
                No hay pacientes para mostrar.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="p-4 border-t">
      {{ $pacientes->links() }}
    </div>
  </div>

</div>
@endsection
