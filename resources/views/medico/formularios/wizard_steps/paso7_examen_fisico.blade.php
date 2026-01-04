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
    $itemsR = [
        '1-R' => 'Piel – Faneras',
        '2-R' => 'Cabeza',
        '3-R' => 'Ojos',
        '4-R' => 'Oídos',
        '5-R' => 'Nariz',
        '6-R' => 'Boca',
        '7-R' => 'Oro faringe',
        '8-R' => 'Cuello',
        '9-R' => 'Axilas – Mamas',
        '10-R' => 'Tórax',
        '11-R' => 'Abdomen',
        '12-R' => 'Columna vertebral',
        '13-R' => 'Ingle – Periné',
        '14-R' => 'Miembros superiores',
        '15-R' => 'Miembros inferiores',
    ];

    $itemsS = [
        '1-S' => 'Órganos de los sentidos',
        '2-S' => 'Respiratorio',
        '3-S' => 'Cardio vascular',
        '4-S' => 'Digestivo',
        '5-S' => 'Genital',
        '6-S' => 'Urinario',
        '7-S' => 'Músculo esquelético',
        '8-S' => 'Endocrino',
        '9-S' => 'Hemo linfático',
        '10-S' => 'Neurológico',
    ];

    $savedChecks = old('examen_fisico_checks', $formulario->examen_fisico_checks ?? []);
    if (!is_array($savedChecks)) $savedChecks = [];

    $savedDesc = old('examen_fisico_descripcion', $formulario->examen_fisico_descripcion);
@endphp

<form method="POST" action="{{ route('medico.formularios.paso.store', ['formulario' => $formulario->id, 'paso' => 7]) }}" class="space-y-6">
    @csrf

    <div class="rounded-2xl border border-gray-200 bg-white p-5">
        <div class="flex items-start justify-between gap-3">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">Examen físico (R / S)</h3>
                <p class="text-sm text-gray-500 mt-1">
                    Marca <span class="font-semibold">SP</span> (sin patología) o <span class="font-semibold">CP</span> (con patología).
                    Si marcas CP, describe abajo anotando el código (ej: 3-R).
                </p>
            </div>

            <div class="flex flex-col sm:flex-row gap-2">
                <button type="button" id="btnMarkAllSP"
                        class="px-3 py-2 rounded-xl border bg-white hover:bg-gray-50 text-sm font-semibold">
                    Marcar todo SP
                </button>

                <button type="button" id="btnClearAll"
                        class="px-3 py-2 rounded-xl border bg-white hover:bg-gray-50 text-sm font-semibold">
                    Limpiar
                </button>

                <button type="button" id="btnTemplateCP"
                        class="px-3 py-2 rounded-xl bg-gray-900 hover:bg-black text-white text-sm font-semibold">
                    Insertar plantilla CP
                </button>
            </div>
        </div>

        {{-- GRID --}}
        <div class="mt-5 grid grid-cols-1 lg:grid-cols-2 gap-4">
            {{-- REGIONAL --}}
            <div class="rounded-2xl border border-gray-200 p-4">
                <div class="flex items-center justify-between mb-3">
                    <div class="font-semibold text-gray-900">Regional (R)</div>
                    <div class="text-xs text-gray-500">CP / SP</div>
                </div>

                <div class="space-y-2">
                    @foreach($itemsR as $k => $label)
                        @php $val = $savedChecks[$k] ?? null; @endphp

                        <div class="row-examen flex items-center justify-between gap-2 rounded-xl border p-3
                                    {{ $val === 'CP' ? 'border-red-200 bg-red-50' : 'border-gray-200 bg-white' }}"
                             data-key="{{ $k }}">
                            <div class="min-w-0">
                                <div class="text-sm font-semibold text-gray-900">{{ $k }}</div>
                                <div class="text-sm text-gray-700 truncate">{{ $label }}</div>
                            </div>

                            <div class="flex items-center gap-3 shrink-0">
                                <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                    <input type="radio"
                                           name="examen_fisico_checks[{{ $k }}]"
                                           value="CP"
                                           class="text-gray-900 focus:ring-gray-900"
                                           {{ $val === 'CP' ? 'checked' : '' }}>
                                    CP
                                </label>

                                <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                    <input type="radio"
                                           name="examen_fisico_checks[{{ $k }}]"
                                           value="SP"
                                           class="text-gray-900 focus:ring-gray-900"
                                           {{ $val === 'SP' ? 'checked' : '' }}>
                                    SP
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- SISTÉMICO --}}
            <div class="rounded-2xl border border-gray-200 p-4">
                <div class="flex items-center justify-between mb-3">
                    <div class="font-semibold text-gray-900">Sistémico (S)</div>
                    <div class="text-xs text-gray-500">CP / SP</div>
                </div>

                <div class="space-y-2">
                    @foreach($itemsS as $k => $label)
                        @php $val = $savedChecks[$k] ?? null; @endphp

                        <div class="row-examen flex items-center justify-between gap-2 rounded-xl border p-3
                                    {{ $val === 'CP' ? 'border-red-200 bg-red-50' : 'border-gray-200 bg-white' }}"
                             data-key="{{ $k }}">
                            <div class="min-w-0">
                                <div class="text-sm font-semibold text-gray-900">{{ $k }}</div>
                                <div class="text-sm text-gray-700 truncate">{{ $label }}</div>
                            </div>

                            <div class="flex items-center gap-3 shrink-0">
                                <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                    <input type="radio"
                                           name="examen_fisico_checks[{{ $k }}]"
                                           value="CP"
                                           class="text-gray-900 focus:ring-gray-900"
                                           {{ $val === 'CP' ? 'checked' : '' }}>
                                    CP
                                </label>

                                <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                    <input type="radio"
                                           name="examen_fisico_checks[{{ $k }}]"
                                           value="SP"
                                           class="text-gray-900 focus:ring-gray-900"
                                           {{ $val === 'SP' ? 'checked' : '' }}>
                                    SP
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- DESCRIPCIÓN --}}
        <div class="mt-5">
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Descripción de hallazgos (solo CP)
            </label>
            <textarea id="examen_fisico_descripcion" name="examen_fisico_descripcion" rows="5"
                      class="w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-gray-900"
                      placeholder="Ej: 3-R Ojos: hiperemia conjuntival...">{{ $savedDesc }}</textarea>
            <p class="text-xs text-gray-500 mt-2">
                Si marcas CP, describe y anota el código (ej: 11-R, 3-S).
            </p>
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
  const itemsMap = @json(array_merge($itemsR, $itemsS));
  const rows = document.querySelectorAll('.row-examen');
  const desc = document.getElementById('examen_fisico_descripcion');

  function refreshRow(row) {
    const key = row.dataset.key;
    const cp = row.querySelector(`input[type="radio"][value="CP"]`);
    const isCP = cp && cp.checked;
    row.classList.toggle('border-red-200', isCP);
    row.classList.toggle('bg-red-50', isCP);
    row.classList.toggle('border-gray-200', !isCP);
    row.classList.toggle('bg-white', !isCP);
  }

  rows.forEach(row => {
    row.querySelectorAll('input[type="radio"]').forEach(r => {
      r.addEventListener('change', () => refreshRow(row));
    });
    refreshRow(row);
  });

  // Marcar todo SP
  document.getElementById('btnMarkAllSP')?.addEventListener('click', () => {
    Object.keys(itemsMap).forEach(key => {
      const sp = document.querySelector(`input[name="examen_fisico_checks[${CSS.escape(key)}]"][value="SP"]`);
      if (sp) sp.checked = true;
    });
    rows.forEach(refreshRow);
  });

  // Limpiar selección
  document.getElementById('btnClearAll')?.addEventListener('click', () => {
    rows.forEach(row => {
      row.querySelectorAll('input[type="radio"]').forEach(r => r.checked = false);
      refreshRow(row);
    });
  });

  // Insertar plantilla CP
  document.getElementById('btnTemplateCP')?.addEventListener('click', () => {
    const cps = [];
    Object.keys(itemsMap).forEach(key => {
      const cp = document.querySelector(`input[name="examen_fisico_checks[${CSS.escape(key)}]"][value="CP"]`);
      if (cp && cp.checked) cps.push(`${key} ${itemsMap[key]}: `);
    });

    if (cps.length === 0) return;

    const current = (desc.value || '').trim();
    if (current === '') {
      desc.value = cps.join('\n');
      return;
    }

    // Append sin duplicar líneas exactas
    const lines = new Set(current.split('\n').map(l => l.trim()).filter(Boolean));
    cps.forEach(line => lines.add(line.trim()));
    desc.value = Array.from(lines).join('\n');
  });
});
</script>
