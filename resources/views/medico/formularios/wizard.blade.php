<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1">
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Formulario 008 — {{ $steps[$paso] ?? "Paso $paso" }}
                </h2>

                <a href="{{ route('medico.formularios') }}"
                    class="text-sm px-3 py-2 rounded-lg border bg-white hover:bg-gray-50">
                    ← Volver a formularios
                </a>
            </div>

            <div class="text-sm text-gray-500">
                Paciente: <span class="font-semibold text-gray-700">{{ $formulario->paciente->nombre_completo }}</span>
                · Cédula: <span class="font-semibold text-gray-700">{{ $formulario->paciente->cedula }}</span>
                · Estado: <span class="font-semibold text-gray-700">{{ strtoupper($formulario->estado) }}</span>
            </div>
        </div>
    </x-slot>

    <div class="py-6 bg-gray-50 min-h-screen">

        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">

            {{-- Stepper PRO --}}
            <div class="bg-white shadow-sm rounded-2xl p-4 mb-6">
                <div class="flex items-center justify-between mb-3">
                    <div class="text-sm text-gray-600">
                        Paso <span class="font-semibold text-gray-900">{{ $paso }}</span> de {{ count($steps) }}
                    </div>
                    <div class="text-xs px-2 py-1 rounded-full
            {{ $formulario->estado === 'completo' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                        {{ strtoupper($formulario->estado) }}
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <div class="flex gap-2 min-w-max pb-1">
                        @foreach($steps as $n => $label)
                        @php
                        $isActive = $n === $paso;
                        $isDone = $n < $formulario->paso_actual;
                            $canOpen = $n <= $formulario->paso_actual;
                                @endphp

                                <a
                                    href="{{ $canOpen ? route('medico.formularios.paso', ['formulario' => $formulario->id, 'paso' => $n]) : '#' }}"
                                    class="group flex items-center gap-2 px-3 py-2 rounded-xl border transition
                        {{ $isActive ? 'bg-gray-900 text-white border-gray-900' : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50' }}
                        {{ !$canOpen ? 'opacity-40 cursor-not-allowed pointer-events-none' : '' }}">
                                    <span class="w-7 h-7 flex items-center justify-center rounded-lg text-sm font-bold
                        {{ $isActive ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-800 group-hover:bg-gray-200' }}">
                                        {{ $n }}
                                    </span>

                                    <span class="text-sm whitespace-nowrap">
                                        {{ $label }}
                                    </span>

                                    @if($isDone)
                                    <span class="ml-1 text-sm">✓</span>
                                    @endif
                                </a>
                                @endforeach
                    </div>
                </div>
            </div>

            {{-- Contenido del paso --}}
            <div class="bg-transparent">
                <div class="rounded-2xl border border-gray-200 bg-white p-6">

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

                    @elseif ($paso === 6)
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
                        <div class="font-semibold text-lg mb-2">
                            {{ $steps[$paso] ?? "Paso $paso" }}
                        </div>

                        <p class="text-gray-500">
                            Este paso aún está en construcción.
                        </p>

                        <div class="mt-4 flex gap-2">
                            <a href="{{ route('medico.formularios.paso', ['formulario' => $formulario->id, 'paso' => 1]) }}"
                                class="px-4 py-2 rounded-lg bg-gray-900 text-white">
                                Volver al Paso 1
                            </a>
                        </div>
                    </div>
                    @endif

                </div>
            </div>

        </div>
</x-app-layout>