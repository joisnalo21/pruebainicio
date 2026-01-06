@extends('layouts.admin')

@section('content')
@php
    $numero008 = fn($f) => $f->numero ?? ('008-' . str_pad((string)$f->id, 6, '0', STR_PAD_LEFT));
    $estado = strtoupper($formulario->estado ?? '—');
@endphp

<div class="p-6 space-y-4">

    <div class="flex flex-col gap-2">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-extrabold text-gray-900">
                    Formulario 008 — {{ $steps[$paso] ?? "Paso $paso" }}
                    <span class="ml-2 text-xs px-2 py-1 rounded-full bg-gray-100 text-gray-700 align-middle">Solo lectura</span>
                </h1>
                <div class="text-sm text-gray-600 mt-1">
                    Nº: <span class="font-semibold">{{ $numero008($formulario) }}</span>
                    · Estado: <span class="font-semibold">{{ $estado }}</span>
                    · Registrado por: <span class="font-semibold">{{ $formulario->creador?->name ?? '—' }}</span>
                </div>
            </div>

            <div class="flex gap-2 flex-wrap justify-end">
                <a href="{{ route('admin.formularios.index') }}"
                   class="px-4 py-2 rounded-xl border bg-white hover:bg-gray-50 text-sm font-semibold">
                    ← Volver
                </a>

                @if($formulario->esCompleto())
                    <a href="{{ route('admin.formularios.pdf', $formulario) }}" target="_blank" rel="noopener"
                       class="px-4 py-2 rounded-xl bg-gray-900 text-white hover:bg-black text-sm font-semibold">
                        PDF
                    </a>
                @endif
            </div>
        </div>

        <div class="text-sm text-gray-600">
            Paciente: <span class="font-semibold text-gray-900">{{ $formulario->paciente?->nombre_completo ?? $formulario->paciente?->nombre ?? '—' }}</span>
            · Cédula: <span class="font-semibold text-gray-900">{{ $formulario->paciente?->cedula ?? '—' }}</span>
        </div>
    </div>

    {{-- Stepper --}}
    <div class="bg-white border rounded-2xl p-4">
        <div class="flex items-center justify-between mb-3">
            <div class="text-sm text-gray-600">
                Paso <span class="font-semibold text-gray-900">{{ $paso }}</span> de {{ count($steps) }}
            </div>

            @if($formulario->esCompleto())
                <div class="text-xs px-2 py-1 rounded-full bg-green-100 text-green-700 font-semibold">COMPLETO</div>
            @elseif($formulario->esArchivado())
                <div class="text-xs px-2 py-1 rounded-full bg-gray-100 text-gray-700 font-semibold">ARCHIVADO</div>
            @else
                <div class="text-xs px-2 py-1 rounded-full bg-yellow-100 text-yellow-800 font-semibold">BORRADOR</div>
            @endif
        </div>

        <div class="grid grid-cols-4 md:grid-cols-6 lg:grid-cols-13 gap-2">
            @foreach($steps as $n => $label)
                @php $isActive = $n === $paso; @endphp
                <a href="{{ route('admin.formularios.ver.paso', ['formulario' => $formulario->id, 'paso' => $n]) }}"
                   class="group flex flex-col items-center gap-0.5 px-2 py-2 rounded-xl border transition
                   {{ $isActive ? 'bg-gray-900 text-white border-gray-900' : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50' }}">
                    <span class="w-6 h-6 flex items-center justify-center rounded-lg text-xs font-bold
                    {{ $isActive ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-800 group-hover:bg-gray-200' }}">
                        {{ $n }}
                    </span>
                    <span class="text-xs text-center truncate w-full">{{ $label }}</span>
                </a>
            @endforeach
        </div>
    </div>

    {{-- Aviso --}}
    <div class="rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-blue-900">
        Modo consulta: los campos se muestran en <span class="font-semibold">solo lectura</span>.
        @if(!$formulario->esCompleto())
            <span class="font-semibold">Nota:</span> este formulario aún no está finalizado.
        @endif
    </div>

    {{-- Contenido --}}
    <div id="readonly-root" class="rounded-2xl border border-gray-200 bg-white p-6">
        @if($paso === 1)
            @include('medico.formularios.wizard_steps.paso1_admision')
        @elseif($paso === 2)
            @include('medico.formularios.wizard_steps.paso2_motivo_evento')
        @elseif($paso === 3)
            @include('medico.formularios.wizard_steps.paso3_antecedentes')
        @elseif($paso === 4)
            @include('medico.formularios.wizard_steps.paso4_enfermedad_actual')
        @elseif($paso === 5)
            @include('medico.formularios.wizard_steps.paso5_dolor')
        @elseif($paso === 6)
            @include('medico.formularios.wizard_steps.paso6_signos_vitales')
        @elseif($paso === 7)
            @include('medico.formularios.wizard_steps.paso7_examen_fisico')
        @elseif($paso === 8)
            @include('medico.formularios.wizard_steps.paso8_lesiones')
        @elseif($paso === 9)
            @include('medico.formularios.wizard_steps.paso9_emergencia_obstetrica')
        @elseif($paso === 10)
            @include('medico.formularios.wizard_steps.paso10_solicitud_examenes')
        @elseif($paso === 11)
            @include('medico.formularios.wizard_steps.paso11_diagnostico_ingreso')
        @elseif($paso === 12)
            @include('medico.formularios.wizard_steps.paso12_plan_tratamiento')
        @elseif($paso === 13)
            @include('medico.formularios.wizard_steps.paso13_alta')
        @else
            <div class="text-gray-700">
                <div class="font-semibold text-lg mb-2">{{ $steps[$paso] ?? "Paso $paso" }}</div>
                <p class="text-gray-500">Este paso aún está en construcción.</p>
            </div>
        @endif
    </div>

</div>

<script>
    // Bloquear inputs del wizard en modo SOLO LECTURA.
    document.addEventListener('DOMContentLoaded', () => {
        const root = document.getElementById('readonly-root');
        if (!root) return;

        root.querySelectorAll('form').forEach(f => {
            f.addEventListener('submit', (e) => e.preventDefault());
        });

        root.querySelectorAll('input, select, textarea').forEach(el => {
            if (el.tagName === 'SELECT' || el.type === 'checkbox' || el.type === 'radio' || el.type === 'file') {
                el.disabled = true;
            } else {
                el.readOnly = true;
            }
            if (['datetime-local','date','time','number'].includes(el.type)) {
                el.disabled = true;
            }
        });

        root.querySelectorAll('button, [type="submit"]').forEach(btn => {
            btn.style.display = 'none';
        });
    });
</script>
@endsection
