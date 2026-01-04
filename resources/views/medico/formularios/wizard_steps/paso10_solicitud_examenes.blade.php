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
    $noAplica = old('no_aplica_examenes', $formulario->no_aplica_examenes);

    $seleccion = old('examenes_solicitados', $formulario->examenes_solicitados ?? []);
    if (!is_array($seleccion)) $seleccion = [];

    $examenes = [
        '1_biometria' => '1. Biometría',
        '2_uroanalisis' => '2. Uroanálisis',
        '3_quimica_sanguinea' => '3. Química sanguínea',
        '4_electrolitos' => '4. Electrolitos',
        '5_gasometria' => '5. Gasometría',
        '6_electrocardiograma' => '6. Electrocardiograma',
        '7_endoscopia' => '7. Endoscopia',
        '8_rx_torax' => '8. R-X tórax',
        '9_rx_abdomen' => '9. R-X abdomen',
        '10_rx_osea' => '10. R-X ósea',
        '11_tomografia' => '11. Tomografía',
        '12_resonancia' => '12. Resonancia',
        '13_ecografia_pelvica' => '13. Ecografía pélvica',
        '14_ecografia_abdomen' => '14. Ecografía abdomen',
        '15_interconsulta' => '15. Interconsulta',
        '16_otros' => '16. Otros',
    ];
@endphp

<form method="POST"
      action="{{ route('medico.formularios.paso.store', ['formulario' => $formulario->id, 'paso' => 10]) }}"
      class="space-y-6">
    @csrf

    <div class="rounded-2xl border border-gray-200 bg-white p-5">
        <div class="flex items-start justify-between gap-4 mb-2">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">Solicitud de exámenes</h3>
                <p class="text-sm text-gray-500">
                    Selecciona los exámenes requeridos y registra abajo comentarios/resultados anotando el número.
                </p>
            </div>

            <label class="inline-flex items-center gap-2 text-sm text-gray-700 select-none">
                <input type="checkbox"
                       name="no_aplica_examenes"
                       id="no_aplica_examenes"
                       value="1"
                       class="rounded border-gray-300 text-gray-900 focus:ring-gray-900"
                       @checked($noAplica)>
                <span>No aplica</span>
            </label>
        </div>

        <div id="examenes_wrapper" class="space-y-5">
            {{-- Checkboxes --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2">
                @foreach($examenes as $key => $label)
                    <label class="cursor-pointer rounded-xl border px-3 py-3 flex items-center gap-2
                        {{ in_array($key, $seleccion) ? 'border-gray-900 bg-gray-50' : 'border-gray-200 hover:bg-gray-50' }}">
                        <input type="checkbox"
                               name="examenes_solicitados[]"
                               value="{{ $key }}"
                               class="rounded border-gray-300 text-gray-900 focus:ring-gray-900"
                               {{ in_array($key, $seleccion) ? 'checked' : '' }}>
                        <span class="text-sm font-medium text-gray-800">{{ $label }}</span>
                    </label>
                @endforeach
            </div>

            {{-- Comentarios / resultados --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Comentarios y resultados (anota el número)
                </label>
                <textarea name="examenes_comentarios"
                          rows="6"
                          class="w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-gray-900"
                          placeholder="Ej: 1) Hb 12.3... 5) pH 7.32... 11) Hallazgos...">{{ old('examenes_comentarios', $formulario->examenes_comentarios) }}</textarea>
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
    const chk = document.getElementById('no_aplica_examenes');
    const wrap = document.getElementById('examenes_wrapper');

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
    }

    setDisabled(!!chk?.checked);
    chk?.addEventListener('change', () => setDisabled(chk.checked));
});
</script>
