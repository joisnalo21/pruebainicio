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
    $noAplica = old('no_aplica_obstetrica', $formulario->no_aplica_obstetrica);
    $mov = old('obst_movimiento_fetal', $formulario->obst_movimiento_fetal);
    $pelvis = old('obst_pelvis_util', $formulario->obst_pelvis_util);
    $membranasRotas = old('obst_membranas_rotas', $formulario->obst_membranas_rotas);
@endphp

<form method="POST"
      action="{{ route('medico.formularios.paso.store', ['formulario' => $formulario->id, 'paso' => 9]) }}"
      class="space-y-6">
    @csrf

    <div class="rounded-2xl border border-gray-200 bg-white p-5">
        <div class="flex items-start justify-between gap-4 mb-2">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">Emergencia obstétrica</h3>
                <p class="text-sm text-gray-500">
                    Completa solo si corresponde. Puedes describir detalles abajo.
                </p>
            </div>

            <label class="inline-flex items-center gap-2 text-sm text-gray-700 select-none">
                <input type="checkbox"
                       name="no_aplica_obstetrica"
                       id="no_aplica_obstetrica"
                       value="1"
                       class="rounded border-gray-300 text-gray-900 focus:ring-gray-900"
                       @checked($noAplica)>
                <span>No aplica</span>
            </label>
        </div>

        <div id="obstetrica_wrapper" class="space-y-5">

            {{-- Fila 1 --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Gestas</label>
                    <input type="number" min="0" max="20" name="obst_gestas"
                           class="w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-gray-900"
                           value="{{ old('obst_gestas', $formulario->obst_gestas) }}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Partos</label>
                    <input type="number" min="0" max="20" name="obst_partos"
                           class="w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-gray-900"
                           value="{{ old('obst_partos', $formulario->obst_partos) }}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Abortos</label>
                    <input type="number" min="0" max="20" name="obst_abortos"
                           class="w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-gray-900"
                           value="{{ old('obst_abortos', $formulario->obst_abortos) }}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cesáreas</label>
                    <input type="number" min="0" max="20" name="obst_cesareas"
                           class="w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-gray-900"
                           value="{{ old('obst_cesareas', $formulario->obst_cesareas) }}">
                </div>
            </div>

            {{-- Fila 2 --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha última menstruación (FUM)</label>
                    <input type="date" name="obst_fum"
                           class="w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-gray-900"
                           value="{{ old('obst_fum', optional($formulario->obst_fum)->format('Y-m-d')) }}">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Semanas de gestación</label>
                    <input type="number" min="0" max="45" name="obst_semanas_gestacion"
                           class="w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-gray-900"
                           value="{{ old('obst_semanas_gestacion', $formulario->obst_semanas_gestacion) }}">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Movimiento fetal</label>
                    <select name="obst_movimiento_fetal"
                            class="w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-gray-900">
                        <option value="">Seleccione...</option>
                        <option value="presente" {{ $mov === 'presente' ? 'selected' : '' }}>Presente</option>
                        <option value="ausente" {{ $mov === 'ausente' ? 'selected' : '' }}>Ausente</option>
                        <option value="no_eval" {{ $mov === 'no_eval' ? 'selected' : '' }}>No evaluado</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Frecuencia fetal</label>
                    <input type="number" min="0" max="300" name="obst_frecuencia_fetal"
                           class="w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-gray-900"
                           placeholder="lpm"
                           value="{{ old('obst_frecuencia_fetal', $formulario->obst_frecuencia_fetal) }}">
                </div>
            </div>

            {{-- Fila 3 --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Altura uterina</label>
                    <input type="number" step="0.01" min="0" max="99.99" name="obst_altura_uterina"
                           class="w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-gray-900"
                           placeholder="cm"
                           value="{{ old('obst_altura_uterina', $formulario->obst_altura_uterina) }}">
                </div>

                <div class="md:col-span-2">
                    <label class="inline-flex items-center gap-2 text-sm font-medium text-gray-700 mt-7">
                        <input type="checkbox" name="obst_membranas_rotas" id="obst_membranas_rotas" value="1"
                               class="rounded border-gray-300 text-gray-900 focus:ring-gray-900"
                               @checked($membranasRotas)>
                        Membranas rotas
                    </label>
                </div>

                <div id="wrap_tiempo_membranas">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tiempo (si rotas)</label>
                    <input type="text" name="obst_tiempo_membranas_rotas"
                           class="w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-gray-900"
                           placeholder="Ej: 2h, desde ayer..."
                           value="{{ old('obst_tiempo_membranas_rotas', $formulario->obst_tiempo_membranas_rotas) }}">
                </div>
            </div>

            {{-- Fila 4 --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Presentación</label>
                    <input type="text" name="obst_presentacion"
                           class="w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-gray-900"
                           placeholder="Ej: cefálica, podálica..."
                           value="{{ old('obst_presentacion', $formulario->obst_presentacion) }}">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Dilatación</label>
                    <input type="number" step="0.5" min="0" max="10" name="obst_dilatacion_cm"
                           class="w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-gray-900"
                           placeholder="cm"
                           value="{{ old('obst_dilatacion_cm', $formulario->obst_dilatacion_cm) }}">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Borramiento</label>
                    <input type="number" min="0" max="100" name="obst_borramiento_pct"
                           class="w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-gray-900"
                           placeholder="%"
                           value="{{ old('obst_borramiento_pct', $formulario->obst_borramiento_pct) }}">
                </div>
            </div>

            {{-- Fila 5 --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Plano</label>
                    <input type="text" name="obst_plano"
                           class="w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-gray-900"
                           placeholder="Ej: -2, 0, +1..."
                           value="{{ old('obst_plano', $formulario->obst_plano) }}">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pelvis útil</label>
                    <select name="obst_pelvis_util"
                            class="w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-gray-900">
                        <option value="">Seleccione...</option>
                        <option value="si" {{ $pelvis === 'si' ? 'selected' : '' }}>Sí</option>
                        <option value="no" {{ $pelvis === 'no' ? 'selected' : '' }}>No</option>
                        <option value="no_eval" {{ $pelvis === 'no_eval' ? 'selected' : '' }}>No evaluado</option>
                    </select>
                </div>

                <div class="md:col-span-2 flex flex-col md:flex-row md:items-center gap-4 pt-1">
                    <label class="inline-flex items-center gap-2 text-sm font-medium text-gray-700">
                        <input type="checkbox" name="obst_sangrado_vaginal" value="1"
                               class="rounded border-gray-300 text-gray-900 focus:ring-gray-900"
                               @checked(old('obst_sangrado_vaginal', $formulario->obst_sangrado_vaginal))>
                        Sangrado vaginal
                    </label>

                    <label class="inline-flex items-center gap-2 text-sm font-medium text-gray-700">
                        <input type="checkbox" name="obst_contracciones" value="1"
                               class="rounded border-gray-300 text-gray-900 focus:ring-gray-900"
                               @checked(old('obst_contracciones', $formulario->obst_contracciones))>
                        Contracciones
                    </label>
                </div>
            </div>

            {{-- Texto grande --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Describir abajo (registrar número respectivo / observaciones)
                </label>
                <textarea name="obst_texto" rows="8"
                          class="w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-gray-900"
                          placeholder="Escribe aquí la evolución, observaciones, hallazgos, etc...">{{ old('obst_texto', $formulario->obst_texto) }}</textarea>
            </div>

        </div>
    </div>

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
    const chk = document.getElementById('no_aplica_obstetrica');
    const wrap = document.getElementById('obstetrica_wrapper');

    const chkMem = document.getElementById('obst_membranas_rotas');
    const wrapTiempo = document.getElementById('wrap_tiempo_membranas');

    function setDisabled(disabled) {
        wrap.classList.toggle('opacity-50', disabled);
        wrap.classList.toggle('pointer-events-none', disabled);

        const fields = wrap.querySelectorAll('input, select, textarea');
        fields.forEach(el => {
            el.disabled = disabled;

            if (disabled) {
                if (el.type === 'checkbox' || el.type === 'radio') el.checked = false;
                else el.value = '';
            }
        });

        toggleTiempo();
    }

    function toggleTiempo() {
        const enabled = !!chkMem?.checked && !chk?.checked;
        if (!wrapTiempo) return;
        wrapTiempo.classList.toggle('opacity-50', !enabled);
        const input = wrapTiempo.querySelector('input');
        if (input) {
            input.disabled = !enabled;
            if (!enabled) input.value = '';
        }
    }

    chk?.addEventListener('change', () => setDisabled(chk.checked));
    chkMem?.addEventListener('change', toggleTiempo);

    // init
    setDisabled(!!chk?.checked);
    toggleTiempo();
});
</script>
