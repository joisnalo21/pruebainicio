@extends('layouts.admin')

@section('content')
@php
  $nombre = trim(implode(' ', array_filter([
      $paciente->primer_nombre ?? null,
      $paciente->segundo_nombre ?? null,
      $paciente->apellido_paterno ?? null,
      $paciente->apellido_materno ?? null,
  ]))) ?: ($paciente->nombre_completo ?? '—');
@endphp

<div class="p-6 space-y-6">

  <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-3">
    <div>
      <h1 class="text-2xl font-extrabold text-gray-900">{{ $nombre }}</h1>
      <p class="text-sm text-gray-600 mt-1">
        Cédula: <span class="font-semibold text-gray-900">{{ $paciente->cedula ?? '—' }}</span>
        · ID: <span class="font-semibold text-gray-900">{{ $paciente->id }}</span>
      </p>
    </div>

    <div class="flex gap-2 flex-wrap justify-end">
      <a href="{{ route('admin.pacientes.index') }}"
         class="px-4 py-2 rounded-xl border bg-white hover:bg-gray-50 text-sm font-semibold">
        ← Volver
      </a>

      <a href="{{ route('admin.formularios.index', ['q' => $paciente->cedula]) }}"
         class="px-4 py-2 rounded-xl bg-gray-900 text-white hover:bg-black text-sm font-semibold">
        Ver Formularios 008
      </a>
    </div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
    {{-- Resumen --}}
    <div class="bg-white border rounded-2xl p-5 shadow-sm lg:col-span-1">
      <div class="text-xs uppercase tracking-wide text-gray-500">Resumen</div>

      <div class="mt-3 space-y-2 text-sm">
        <div class="flex justify-between gap-4">
          <span class="text-gray-600">Sexo</span>
          <span class="font-semibold">{{ $paciente->sexo ?? '—' }}</span>
        </div>
        <div class="flex justify-between gap-4">
          <span class="text-gray-600">Edad</span>
          <span class="font-semibold">{{ $paciente->edad ?? '—' }}</span>
        </div>
        <div class="flex justify-between gap-4">
          <span class="text-gray-600">Nacimiento</span>
          <span class="font-semibold">{{ $paciente->fecha_nacimiento ?? '—' }}</span>
        </div>
        <div class="flex justify-between gap-4">
          <span class="text-gray-600">Teléfono</span>
          <span class="font-semibold">{{ $paciente->telefono ?? '—' }}</span>
        </div>
      </div>

      <div class="mt-4 text-xs text-gray-500">
        Registrado: {{ optional($paciente->created_at)->format('Y-m-d H:i') ?? '—' }}
      </div>
    </div>

    {{-- Datos generales --}}
    <div class="bg-white border rounded-2xl p-5 shadow-sm lg:col-span-2">
      <div class="text-xs uppercase tracking-wide text-gray-500">Datos generales</div>

      <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
        <div>
          <div class="text-gray-600 text-xs">Dirección</div>
          <div class="font-semibold text-gray-900">{{ $paciente->direccion ?? '—' }}</div>
        </div>

        <div>
          <div class="text-gray-600 text-xs">Ocupación</div>
          <div class="font-semibold text-gray-900">{{ $paciente->ocupacion ?? '—' }}</div>
        </div>

        <div>
          <div class="text-gray-600 text-xs">Provincia</div>
          <div class="font-semibold text-gray-900">{{ $paciente->provincia ?? '—' }}</div>
        </div>

        <div>
          <div class="text-gray-600 text-xs">Cantón</div>
          <div class="font-semibold text-gray-900">{{ $paciente->canton ?? '—' }}</div>
        </div>

        <div>
          <div class="text-gray-600 text-xs">Parroquia</div>
          <div class="font-semibold text-gray-900">{{ $paciente->parroquia ?? '—' }}</div>
        </div>

        <div>
          <div class="text-gray-600 text-xs">Zona / Barrio</div>
          <div class="font-semibold text-gray-900">
            {{ $paciente->zona ?? '—' }}{{ $paciente->barrio ? ' · '.$paciente->barrio : '' }}
          </div>
        </div>

        <div>
          <div class="text-gray-600 text-xs">Lugar de nacimiento</div>
          <div class="font-semibold text-gray-900">{{ $paciente->lugar_nacimiento ?? '—' }}</div>
        </div>

        <div>
          <div class="text-gray-600 text-xs">Nacionalidad</div>
          <div class="font-semibold text-gray-900">{{ $paciente->nacionalidad ?? '—' }}</div>
        </div>

        <div>
          <div class="text-gray-600 text-xs">Grupo cultural</div>
          <div class="font-semibold text-gray-900">{{ $paciente->grupo_cultural ?? '—' }}</div>
        </div>

        <div>
          <div class="text-gray-600 text-xs">Estado civil</div>
          <div class="font-semibold text-gray-900">{{ $paciente->estado_civil ?? '—' }}</div>
        </div>

        <div>
          <div class="text-gray-600 text-xs">Instrucción</div>
          <div class="font-semibold text-gray-900">{{ $paciente->instruccion ?? '—' }}</div>
        </div>

        <div>
          <div class="text-gray-600 text-xs">Empresa</div>
          <div class="font-semibold text-gray-900">{{ $paciente->empresa ?? '—' }}</div>
        </div>

        <div>
          <div class="text-gray-600 text-xs">Seguro de salud</div>
          <div class="font-semibold text-gray-900">{{ $paciente->seguro_salud ?? '—' }}</div>
        </div>
      </div>
    </div>
  </div>

</div>
@endsection
