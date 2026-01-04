{{-- resources/views/medico/formularios/wizard_steps/paso6_signos_vitales.blade.php --}}

@if (session('success'))
    <div class="mb-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-800">
        {{ session('success') }}
    </div>
@endif

@if ($errors->any())
    <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-800">
        <div class="font-semibold mb-2">Revisa estos campos:</div>
        <ul class="list-disc ml-5 text-sm space-y-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@php
    // Valores actuales (con old() para mantener inputs si hay error)
    $paSis = old('pa_sistolica', $formulario->pa_sistolica);
    $paDia = old('pa_diastolica', $formulario->pa_diastolica);

    $fc = old('frecuencia_cardiaca', $formulario->frecuencia_cardiaca);
    $fr = old('frecuencia_respiratoria', $formulario->frecuencia_respiratoria);

    $tb = old('temp_bucal', $formulario->temp_bucal);
    $ta = old('temp_axilar', $formulario->temp_axilar);

    $peso = old('peso', $formulario->peso);
    $talla = old('talla', $formulario->talla);

    $spo2 = old('saturacion_oxigeno', $formulario->saturacion_oxigeno);
    $tlc = old('tiempo_llenado_capilar', $formulario->tiempo_llenado_capilar);

    $go = old('glasgow_ocular', $formulario->glasgow_ocular);
    $gv = old('glasgow_verbal', $formulario->glasgow_verbal);
    $gm = old('glasgow_motora', $formulario->glasgow_motora);

    $pDer = old('reaccion_pupila_der', $formulario->reaccion_pupila_der);
    $pIzq = old('reaccion_pupila_izq', $formulario->reaccion_pupila_izq);

    $pupilasOpts = [
        '' => 'Seleccione…',
        'reactiva' => 'Reactiva',
        'hiporreactiva' => 'Hiporreactiva',
        'no_reactiva' => 'No reactiva',
        'no_evaluable' => 'No evaluable',
    ];
@endphp

<form method="POST"
      action="{{ route('medico.formularios.paso.store', ['formulario' => $formulario->id, 'paso' => 6]) }}"
      class="space-y-6">
    @csrf

    {{-- Header del paso --}}
    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-3">
        <div>
            <h3 class="text-lg font-semibold text-gray-900">Signos vitales, mediciones y valores</h3>
            <p class="text-sm text-gray-500">
                Registra los valores obtenidos al ingreso o durante la atención. Glasgow se calcula automáticamente.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <span class="text-xs px-2 py-1 rounded-full bg-gray-100 text-gray-700 border border-gray-200">
                Paso 6 (Parte 7 del Formulario 008)
            </span>
        </div>
    </div>

    {{-- Card principal --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-6">
        {{-- Bloque 1: Signos vitales principales --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">

            {{-- Presión arterial --}}
            <div class="lg:col-span-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Presión arterial (mmHg)</label>
                <div class="flex items-center gap-2">
                    <input type="number" min="0" max="300" inputmode="numeric"
                           name="pa_sistolica" id="pa_sistolica"
                           class="w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-gray-900"
                           placeholder="Sistólica (ej. 120)"
                           value="{{ $paSis }}">
                    <span class="text-gray-400 font-semibold">/</span>
                    <input type="number" min="0" max="200" inputmode="numeric"
                           name="pa_diastolica" id="pa_diastolica"
                           class="w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-gray-900"
                           placeholder="Diastólica (ej. 80)"
                           value="{{ $paDia }}">
                </div>
                <p class="mt-1 text-xs text-gray-500">Tip: si solo tienes un valor, llena el que corresponda.</p>
            </div>

            {{-- FC --}}
            <div class="lg:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">F. cardíaca (lpm)</label>
                <input type="number" min="0" max="250" inputmode="numeric"
                       name="frecuencia_cardiaca"
                       class="w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-gray-900"
                       placeholder="Ej. 90"
                       value="{{ $fc }}">
            </div>

            {{-- FR --}}
            <div class="lg:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">F. respiratoria (rpm)</label>
                <input type="number" min="0" max="80" inputmode="numeric"
                       name="frecuencia_respiratoria"
                       class="w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-gray-900"
                       placeholder="Ej. 18"
                       value="{{ $fr }}">
            </div>

            {{-- SpO2 --}}
            <div class="lg:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Sat. O₂ (%)</label>
                <input type="number" min="0" max="100" inputmode="numeric"
                       name="saturacion_oxigeno"
                       class="w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-gray-900"
                       placeholder="Ej. 97"
                       value="{{ $spo2 }}">
            </div>

            {{-- TLC --}}
            <div class="lg:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Llenado capilar (seg)</label>
                <input type="number" step="0.1" min="0" max="10"
                       name="tiempo_llenado_capilar"
                       class="w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-gray-900"
                       placeholder="Ej. 2.0"
                       value="{{ $tlc }}">
            </div>
        </div>

        <div class="mt-5 grid grid-cols-1 lg:grid-cols-12 gap-4">
            {{-- Temperatura --}}
            <div class="lg:col-span-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Temperatura (°C)</label>
                <div class="grid grid-cols-2 gap-2">
                    <input type="number" step="0.1" min="30" max="45"
                           name="temp_bucal"
                           class="w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-gray-900"
                           placeholder="Bucal (ej. 36.7)"
                           value="{{ $tb }}">
                    <input type="number" step="0.1" min="30" max="45"
                           name="temp_axilar"
                           class="w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-gray-900"
                           placeholder="Axilar (ej. 36.4)"
                           value="{{ $ta }}">
                </div>
            </div>

            {{-- Peso --}}
            <div class="lg:col-span-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Peso (kg)</label>
                <input type="number" step="0.01" min="0" max="500"
                       name="peso"
                       class="w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-gray-900"
                       placeholder="Ej. 70.50"
                       value="{{ $peso }}">
            </div>

            {{-- Talla --}}
            <div class="lg:col-span-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Talla (m)</label>
                <input type="number" step="0.01" min="0" max="3"
                       name="talla"
                       class="w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-gray-900"
                       placeholder="Ej. 1.70"
                       value="{{ $talla }}">
                <p class="mt-1 text-xs text-gray-500">Si la tienes en cm, divide para 100 (170 cm → 1.70).</p>
            </div>
        </div>

        {{-- Separador --}}
        <div class="my-6 border-t border-gray-200"></div>

        {{-- Bloque 2: Glasgow + Pupilas --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            {{-- Glasgow --}}
            <div class="lg:col-span-7">
                <div class="flex items-center justify-between gap-3 mb-2">
                    <div>
                        <h4 class="text-base font-semibold text-gray-900">Escala de Glasgow</h4>
                        <p class="text-sm text-gray-500">Selecciona Ocular/Verbal/Motora. El total se calcula solo.</p>
                    </div>
                    <div class="text-sm">
                        <span class="px-2 py-1 rounded-lg border bg-gray-50 text-gray-700">
                            Total:
                            <span id="glasgow_total_badge" class="font-bold text-gray-900">—</span>
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    {{-- Ocular --}}
                    <div class="rounded-xl border border-gray-200 p-4">
                        <div class="font-semibold text-gray-800 mb-2">Ocular (1–4)</div>
                        <select name="glasgow_ocular" id="glasgow_ocular"
                                class="w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-gray-900">
                            <option value="">Seleccione…</option>
                            @foreach([1,2,3,4] as $n)
                                <option value="{{ $n }}" {{ (string)$go === (string)$n ? 'selected' : '' }}>
                                    {{ $n }}
                                </option>
                            @endforeach
                        </select>
                        <div class="mt-2 text-xs text-gray-500">
                            4 espontánea · 3 a voz · 2 al dolor · 1 ninguna
                        </div>
                    </div>

                    {{-- Verbal --}}
                    <div class="rounded-xl border border-gray-200 p-4">
                        <div class="font-semibold text-gray-800 mb-2">Verbal (1–5)</div>
                        <select name="glasgow_verbal" id="glasgow_verbal"
                                class="w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-gray-900">
                            <option value="">Seleccione…</option>
                            @foreach([1,2,3,4,5] as $n)
                                <option value="{{ $n }}" {{ (string)$gv === (string)$n ? 'selected' : '' }}>
                                    {{ $n }}
                                </option>
                            @endforeach
                        </select>
                        <div class="mt-2 text-xs text-gray-500">
                            5 orientado · 4 confuso · 3 palabras · 2 sonidos · 1 ninguna
                        </div>
                    </div>

                    {{-- Motora --}}
                    <div class="rounded-xl border border-gray-200 p-4">
                        <div class="font-semibold text-gray-800 mb-2">Motora (1–6)</div>
                        <select name="glasgow_motora" id="glasgow_motora"
                                class="w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-gray-900">
                            <option value="">Seleccione…</option>
                            @foreach([1,2,3,4,5,6] as $n)
                                <option value="{{ $n }}" {{ (string)$gm === (string)$n ? 'selected' : '' }}>
                                    {{ $n }}
                                </option>
                            @endforeach
                        </select>
                        <div class="mt-2 text-xs text-gray-500">
                            6 obedece · 5 localiza · 4 retira · 3 flexión · 2 extensión · 1 ninguna
                        </div>
                    </div>
                </div>

                {{-- Glasgow total hidden (se guarda también) --}}
                <input type="hidden" name="glasgow_total" id="glasgow_total" value="{{ old('glasgow_total', $formulario->glasgow_total) }}">
            </div>

            {{-- Pupilas --}}
            <div class="lg:col-span-5">
                <h4 class="text-base font-semibold text-gray-900 mb-2">Reacción pupilar</h4>
                <p class="text-sm text-gray-500 mb-4">Selecciona la respuesta observada en cada ojo.</p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="rounded-xl border border-gray-200 p-4">
                        <div class="text-sm font-semibold text-gray-800 mb-2">Pupila derecha</div>
                        <select name="reaccion_pupila_der"
                                class="w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-gray-900">
                            @foreach($pupilasOpts as $val => $txt)
                                <option value="{{ $val }}" {{ (string)$pDer === (string)$val ? 'selected' : '' }}>
                                    {{ $txt }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="rounded-xl border border-gray-200 p-4">
                        <div class="text-sm font-semibold text-gray-800 mb-2">Pupila izquierda</div>
                        <select name="reaccion_pupila_izq"
                                class="w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-gray-900">
                            @foreach($pupilasOpts as $val => $txt)
                                <option value="{{ $val }}" {{ (string)$pIzq === (string)$val ? 'selected' : '' }}>
                                    {{ $txt }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mt-3 text-xs text-gray-500">
                    * “No evaluable” si el paciente no permite valoración (p.ej., trauma ocular severo, vendajes, etc.).
                </div>
            </div>
        </div>
    </div>

    {{-- Botones --}}
    <div class="flex flex-col sm:flex-row gap-2 justify-end pt-2">
        <button type="submit" name="accion" value="save"
                class="px-4 py-2 rounded-xl border bg-white hover:bg-gray-50 font-semibold">
            Guardar borrador
        </button>

        <button type="submit" name="accion" value="next"
                class="px-4 py-2 rounded-xl bg-gray-900 hover:bg-black text-white font-semibold">
            Guardar y continuar →
        </button>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const go = document.getElementById('glasgow_ocular');
    const gv = document.getElementById('glasgow_verbal');
    const gm = document.getElementById('glasgow_motora');

    const totalHidden = document.getElementById('glasgow_total');
    const badge = document.getElementById('glasgow_total_badge');

    function calcGlasgow() {
        const o = parseInt(go?.value || '', 10);
        const v = parseInt(gv?.value || '', 10);
        const m = parseInt(gm?.value || '', 10);

        if (!isNaN(o) && !isNaN(v) && !isNaN(m)) {
            const t = o + v + m;
            totalHidden.value = t;
            badge.textContent = t;

            // mini ayuda visual (sin colores hardcodeados, solo texto)
            if (t <= 8) badge.textContent = t + " (grave)";
            else if (t <= 12) badge.textContent = t + " (moderado)";
            else badge.textContent = t + " (leve)";
        } else {
            totalHidden.value = '';
            badge.textContent = '—';
        }
    }

    go?.addEventListener('change', calcGlasgow);
    gv?.addEventListener('change', calcGlasgow);
    gm?.addEventListener('change', calcGlasgow);

    // Init
    calcGlasgow();
});
</script>
