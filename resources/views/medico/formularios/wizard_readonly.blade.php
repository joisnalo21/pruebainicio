<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1">
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Formulario 008 — {{ $steps[$paso] ?? "Paso $paso" }}
                    <span class="ml-2 text-xs px-2 py-1 rounded-full bg-gray-100 text-gray-700 align-middle">Solo lectura</span>
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

            {{-- Stepper --}}
            <div class="bg-white shadow-sm rounded-2xl p-4 mb-6">
                <div class="flex items-center justify-between mb-3">
                    <div class="text-sm text-gray-600">
                        Paso <span class="font-semibold text-gray-900">{{ $paso }}</span> de {{ count($steps) }}
                    </div>
                    <div class="text-xs px-2 py-1 rounded-full bg-green-100 text-green-700">
                        COMPLETO
                    </div>
                </div>

                <div class="grid grid-cols-4 md:grid-cols-6 lg:grid-cols-13 gap-2">
                    @foreach($steps as $n => $label)
                        @php $isActive = $n === $paso; @endphp

                        <a
                            href="{{ route('medico.formularios.ver.paso', ['formulario' => $formulario->id, 'paso' => $n]) }}"
                            class="group flex flex-col items-center gap-0.5 px-2 py-2 rounded-xl border transition relative
                                {{ $isActive ? 'bg-gray-900 text-white border-gray-900' : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50' }}">
                            <span class="w-6 h-6 flex items-center justify-center rounded-lg text-xs font-bold
                                {{ $isActive ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-800 group-hover:bg-gray-200' }}">
                                {{ $n }}
                            </span>

                            <span class="text-xs text-center whitespace-normal truncate w-full">
                                {{ $label }}
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- Mensaje Solo lectura --}}
            <div class="mb-4 rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-blue-900">
                Este formulario está <span class="font-semibold">finalizado</span>. Los campos se muestran solo para consulta.
            </div>

            {{-- Contenido del paso (re-usa las mismas vistas, pero se bloquea con JS) --}}
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
                        <div class="font-semibold text-lg mb-2">
                            {{ $steps[$paso] ?? "Paso $paso" }}
                        </div>
                        <p class="text-gray-500">Este paso aún está en construcción.</p>
                    </div>
                @endif

            </div>

        </div>
    </div>

    <script>
        // Bloquear inputs del wizard en modo SOLO LECTURA.
        document.addEventListener('DOMContentLoaded', () => {
            const root = document.getElementById('readonly-root');
            if (!root) return;

            // Evitar submits (por si algún botón queda visible)
            root.querySelectorAll('form').forEach(f => {
                f.addEventListener('submit', (e) => e.preventDefault());
            });

            // Deshabilitar controles
            root.querySelectorAll('input, select, textarea').forEach(el => {
                // Mantener visibles los valores pero impedir cambios
                if (el.tagName === 'SELECT' || el.type === 'checkbox' || el.type === 'radio' || el.type === 'file') {
                    el.disabled = true;
                } else {
                    el.readOnly = true;
                }

                // Para tipos que a veces ignoran readonly
                if (el.type === 'datetime-local' || el.type === 'date' || el.type === 'time' || el.type === 'number') {
                    el.disabled = true;
                }
            });

            // Ocultar botones del wizard (Guardar / Siguiente / Agregar / etc.)
            root.querySelectorAll('button, [type="submit"]').forEach(btn => {
                btn.style.display = 'none';
            });
        });
    </script>
</x-app-layout>
