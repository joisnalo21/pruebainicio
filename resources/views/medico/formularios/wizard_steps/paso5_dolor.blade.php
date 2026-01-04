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
    // Carga dolores (prefill)
    $dolores = old('dolor', $formulario->dolor_items ?? []);
    if (!is_array($dolores)) $dolores = [];
    if (count($dolores) === 0) $dolores = [[
        'region' => '',
        'punto' => '',
        'situacion' => null,
        'evolucion' => null,
        'tipo' => null,
        'se_modifica_con' => [],
        'alivia_con' => [],
        'intensidad' => null,
    ]];

    $situacionOpts = [
        'localizado' => 'Localizado',
        'difuso' => 'Difuso',
        'irradiado' => 'Irradiado',
        'referido' => 'Referido',
    ];

    $evolucionOpts = [
        'agudo' => 'Agudo',
        'subagudo' => 'Subagudo',
        'cronico' => 'Crónico',
    ];

    $tipoOpts = [
        'episodico' => 'Episódico',
        'continuo' => 'Continuo',
        'colico' => 'Cólico',
    ];

    $modificaOpts = [
      'posicion' => 'Posición',
      'ingesta' => 'Ingesta',
      'esfuerzo' => 'Esfuerzo',
      'digito_presion' => 'Dígito presión',
  ];

  $aliviaOpts = [
      'analgesico' => 'Analgésico',
      'anti_espasmodico' => 'Antiespasmódico',
      'opiaceo' => 'Opiáceo',
      'no_alivia' => 'No alivia',
  ];

    $noAplica = (bool) old('no_aplica_dolor', $formulario->no_aplica_dolor);
@endphp

<form method="POST"
      action="{{ route('medico.formularios.paso.store', ['formulario' => $formulario->id, 'paso' => 5]) }}"
      class="space-y-6">
    @csrf

    <div class="rounded-2xl border border-gray-200 bg-white p-5">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-1">
                    6. Características del dolor
                </h3>
                <p class="text-sm text-gray-500">
                    Registra uno o más dolores. Usa “Agregar dolor” si hay más de un punto doloroso.
                </p>
            </div>

            <label class="inline-flex items-center gap-2 text-sm text-gray-700 select-none">
                <input type="checkbox"
                       name="no_aplica_dolor"
                       id="no_aplica_dolor"
                       value="1"
                       class="rounded border-gray-300 text-gray-900 focus:ring-gray-900"
                       @checked($noAplica)>
                <span>No aplica</span>
            </label>
        </div>
    </div>

    <div id="dolor_wrapper" class="space-y-4">
        @foreach($dolores as $i => $d)
            @php
                $d = is_array($d) ? $d : [];
                $m = $d['se_modifica_con'] ?? [];
                $a = $d['alivia_con'] ?? [];
                if (!is_array($m)) $m = [];
                if (!is_array($a)) $a = [];
                $int = $d['intensidad'] ?? null;
            @endphp

            <div class="dolor-item rounded-2xl border border-gray-200 bg-white p-5">
                <div class="flex items-center justify-between gap-3 mb-4">
                    <div class="font-semibold text-gray-900">
                        Dolor #{{ $i + 1 }}
                    </div>

                    <button type="button"
                            class="btn-remove px-3 py-2 rounded-xl border bg-white hover:bg-gray-50 text-sm font-semibold">
                        Eliminar
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Región anatómica <span class="text-red-500">*</span></label>
                        <input type="text"
                               name="dolor[{{ $i }}][region]"
                               class="w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-gray-900"
                               value="{{ $d['region'] ?? '' }}"
                               placeholder="Ej: epigastrio, hemitórax izq, fosa iliaca der...">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Punto doloroso <span class="text-red-500">*</span></label>
                        <input type="text"
                               name="dolor[{{ $i }}][punto]"
                               class="w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-gray-900"
                               value="{{ $d['punto'] ?? '' }}"
                               placeholder="Ej: a la palpación en... / zona exacta...">
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mt-4">
                    <div class="rounded-xl border border-gray-200 p-4">
                        <div class="font-semibold text-gray-800 mb-2">Situación</div>
                        <div class="space-y-2">
                            @foreach($situacionOpts as $k => $lbl)
                                <label class="flex items-center gap-2 text-sm text-gray-700">
                                    <input type="radio"
                                           name="dolor[{{ $i }}][situacion]"
                                           value="{{ $k }}"
                                           class="text-gray-900 focus:ring-gray-900"
                                           {{ ($d['situacion'] ?? null) === $k ? 'checked' : '' }}>
                                    {{ $lbl }}
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="rounded-xl border border-gray-200 p-4">
                        <div class="font-semibold text-gray-800 mb-2">Evolución</div>
                        <div class="space-y-2">
                            @foreach($evolucionOpts as $k => $lbl)
                                <label class="flex items-center gap-2 text-sm text-gray-700">
                                    <input type="radio"
                                           name="dolor[{{ $i }}][evolucion]"
                                           value="{{ $k }}"
                                           class="text-gray-900 focus:ring-gray-900"
                                           {{ ($d['evolucion'] ?? null) === $k ? 'checked' : '' }}>
                                    {{ $lbl }}
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="rounded-xl border border-gray-200 p-4">
                        <div class="font-semibold text-gray-800 mb-2">Tipo</div>
                        <div class="space-y-2">
                            @foreach($tipoOpts as $k => $lbl)
                                <label class="flex items-center gap-2 text-sm text-gray-700">
                                    <input type="radio"
                                           name="dolor[{{ $i }}][tipo]"
                                           value="{{ $k }}"
                                           class="text-gray-900 focus:ring-gray-900"
                                           {{ ($d['tipo'] ?? null) === $k ? 'checked' : '' }}>
                                    {{ $lbl }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mt-4">
                    <div class="rounded-xl border border-gray-200 p-4">
                        <div class="font-semibold text-gray-800 mb-2">Se modifica con</div>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach($modificaOpts as $k => $lbl)
                                <label class="flex items-center gap-2 text-sm text-gray-700">
                                    <input type="checkbox"
                                           name="dolor[{{ $i }}][se_modifica_con][]"
                                           value="{{ $k }}"
                                           class="rounded border-gray-300 text-gray-900 focus:ring-gray-900"
                                           {{ in_array($k, $m) ? 'checked' : '' }}>
                                    {{ $lbl }}
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="rounded-xl border border-gray-200 p-4">
                        <div class="font-semibold text-gray-800 mb-2">Alivia con</div>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach($aliviaOpts as $k => $lbl)
                                <label class="flex items-center gap-2 text-sm text-gray-700">
                                    <input type="checkbox"
                                           name="dolor[{{ $i }}][alivia_con][]"
                                           value="{{ $k }}"
                                           class="rounded border-gray-300 text-gray-900 focus:ring-gray-900"
                                           {{ in_array($k, $a) ? 'checked' : '' }}>
                                    {{ $lbl }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="mt-4 rounded-xl border border-gray-200 p-4">
                    <div class="flex items-center justify-between mb-2">
                        <div class="font-semibold text-gray-800">Intensidad (0–10)</div>
                        <div class="text-sm font-semibold">
                            <span class="int-value">{{ $int !== null ? $int : '—' }}</span>
                            <span class="text-gray-500">·</span>
                            <span class="int-label text-gray-700">—</span>
                        </div>
                    </div>

                    <input type="range"
                           min="0" max="10" step="1"
                           name="dolor[{{ $i }}][intensidad]"
                           class="w-full"
                           value="{{ $int !== null ? $int : 0 }}">
                    <div class="flex justify-between text-xs text-gray-500 mt-1">
                        <span>0</span><span>5</span><span>10</span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="flex items-center justify-between">
        <button type="button"
                id="btn_add_dolor"
                class="px-4 py-2 rounded-xl border bg-white hover:bg-gray-50 font-semibold">
            + Agregar dolor
        </button>

        <div class="flex flex-col sm:flex-row gap-2 justify-end">
            <button type="submit" name="accion" value="save"
                    class="px-4 py-2 rounded-xl border bg-white hover:bg-gray-50 font-semibold">
                Guardar borrador
            </button>

            <button type="submit" name="accion" value="next"
                    class="px-4 py-2 rounded-xl bg-gray-900 hover:bg-black text-white font-semibold">
                Guardar y continuar →
            </button>
        </div>
    </div>
</form>

{{-- TEMPLATE PARA CLONAR --}}
<template id="tpl_dolor">
    <div class="dolor-item rounded-2xl border border-gray-200 bg-white p-5">
        <div class="flex items-center justify-between gap-3 mb-4">
            <div class="font-semibold text-gray-900">Dolor #__N__</div>
            <button type="button" class="btn-remove px-3 py-2 rounded-xl border bg-white hover:bg-gray-50 text-sm font-semibold">
                Eliminar
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Región anatómica <span class="text-red-500">*</span></label>
                <input type="text" name="dolor[__I__][region]"
                       class="w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-gray-900"
                       placeholder="Ej: epigastrio, hemitórax izq...">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Punto doloroso <span class="text-red-500">*</span></label>
                <input type="text" name="dolor[__I__][punto]"
                       class="w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-gray-900"
                       placeholder="Ej: a la palpación en...">
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mt-4">
            <div class="rounded-xl border border-gray-200 p-4">
                <div class="font-semibold text-gray-800 mb-2">Situación</div>
                <div class="space-y-2">
                    @foreach($situacionOpts as $k => $lbl)
                        <label class="flex items-center gap-2 text-sm text-gray-700">
                            <input type="radio" name="dolor[__I__][situacion]" value="{{ $k }}" class="text-gray-900 focus:ring-gray-900">
                            {{ $lbl }}
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 p-4">
                <div class="font-semibold text-gray-800 mb-2">Evolución</div>
                <div class="space-y-2">
                    @foreach($evolucionOpts as $k => $lbl)
                        <label class="flex items-center gap-2 text-sm text-gray-700">
                            <input type="radio" name="dolor[__I__][evolucion]" value="{{ $k }}" class="text-gray-900 focus:ring-gray-900">
                            {{ $lbl }}
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 p-4">
                <div class="font-semibold text-gray-800 mb-2">Tipo</div>
                <div class="space-y-2">
                    @foreach($tipoOpts as $k => $lbl)
                        <label class="flex items-center gap-2 text-sm text-gray-700">
                            <input type="radio" name="dolor[__I__][tipo]" value="{{ $k }}" class="text-gray-900 focus:ring-gray-900">
                            {{ $lbl }}
                        </label>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mt-4">
            <div class="rounded-xl border border-gray-200 p-4">
                <div class="font-semibold text-gray-800 mb-2">Se modifica con</div>
                <div class="grid grid-cols-2 gap-2">
                    @foreach($modificaOpts as $k => $lbl)
                        <label class="flex items-center gap-2 text-sm text-gray-700">
                            <input type="checkbox" name="dolor[__I__][se_modifica_con][]" value="{{ $k }}"
                                   class="rounded border-gray-300 text-gray-900 focus:ring-gray-900">
                            {{ $lbl }}
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 p-4">
                <div class="font-semibold text-gray-800 mb-2">Alivia con</div>
                <div class="grid grid-cols-2 gap-2">
                    @foreach($aliviaOpts as $k => $lbl)
                        <label class="flex items-center gap-2 text-sm text-gray-700">
                            <input type="checkbox" name="dolor[__I__][alivia_con][]" value="{{ $k }}"
                                   class="rounded border-gray-300 text-gray-900 focus:ring-gray-900">
                            {{ $lbl }}
                        </label>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="mt-4 rounded-xl border border-gray-200 p-4">
            <div class="flex items-center justify-between mb-2">
                <div class="font-semibold text-gray-800">Intensidad (0–10)</div>
                <div class="text-sm font-semibold">
                    <span class="int-value">—</span>
                    <span class="text-gray-500">·</span>
                    <span class="int-label text-gray-700">—</span>
                </div>
            </div>

            <input type="range" min="0" max="10" step="1" name="dolor[__I__][intensidad]" class="w-full" value="0">
            <div class="flex justify-between text-xs text-gray-500 mt-1">
                <span>0</span><span>5</span><span>10</span>
            </div>
        </div>
    </div>
</template>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const chkNoAplica = document.getElementById('no_aplica_dolor');
  const wrapper = document.getElementById('dolor_wrapper');
  const btnAdd = document.getElementById('btn_add_dolor');
  const tpl = document.getElementById('tpl_dolor');

  function severityLabel(v) {
    const n = parseInt(v ?? '', 10);
    if (Number.isNaN(n)) return '—';
    if (n <= 4) return 'Leve';
    if (n <= 7) return 'Moderado';
    return 'Grave';
  }

  function refreshNumbers() {
    const items = wrapper.querySelectorAll('.dolor-item');
    items.forEach((el, idx) => {
      const title = el.querySelector('.font-semibold.text-gray-900');
      if (title) title.textContent = `Dolor #${idx + 1}`;
    });
  }

  function wireIntensity(card) {
    const range = card.querySelector('input[type="range"]');
    const vEl = card.querySelector('.int-value');
    const lEl = card.querySelector('.int-label');
    if (!range || !vEl || !lEl) return;

    const update = () => {
      vEl.textContent = range.value;
      lEl.textContent = severityLabel(range.value);
    };
    update();
    range.addEventListener('input', update);
  }

  function wireRemove(card) {
    const btn = card.querySelector('.btn-remove');
    if (!btn) return;

    btn.addEventListener('click', () => {
      const items = wrapper.querySelectorAll('.dolor-item');
      if (items.length <= 1) {
        // si solo queda 1, lo limpiamos en vez de borrarlo
        card.querySelectorAll('input, textarea, select').forEach(el => {
          if (el.type === 'checkbox' || el.type === 'radio') el.checked = false;
          else if (el.type === 'range') el.value = 0;
          else el.value = '';
        });
        wireIntensity(card);
        return;
      }
      card.remove();
      refreshNumbers();
    });
  }

  function setDisabled(disabled) {
    wrapper.classList.toggle('opacity-50', disabled);
    wrapper.classList.toggle('pointer-events-none', disabled);

    wrapper.querySelectorAll('input, select, textarea, button').forEach(el => {
      // PERO: no queremos deshabilitar "Eliminar" y "Agregar" si está disabled, así que sí, los deshabilitamos también para evitar cambios
      el.disabled = disabled;
      if (disabled) {
        if (el.type === 'checkbox' || el.type === 'radio') el.checked = false;
        else if (el.type === 'range') el.value = 0;
        else el.value = '';
      }
    });

    // Si está disabled, también deshabilitamos el botón agregar
    btnAdd.disabled = disabled;
    btnAdd.classList.toggle('opacity-50', disabled);
    btnAdd.classList.toggle('cursor-not-allowed', disabled);
  }

  // Inicial: wire cards existentes
  wrapper.querySelectorAll('.dolor-item').forEach(card => {
    wireIntensity(card);
    wireRemove(card);
  });
  refreshNumbers();

  // No aplica
  setDisabled(!!chkNoAplica?.checked);
  chkNoAplica?.addEventListener('change', () => {
    setDisabled(chkNoAplica.checked);
  });

  // Agregar dolor
  btnAdd?.addEventListener('click', () => {
    const nextIndex = wrapper.querySelectorAll('.dolor-item').length;
    const html = tpl.innerHTML.replaceAll('__I__', String(nextIndex)).replaceAll('__N__', String(nextIndex + 1));

    const temp = document.createElement('div');
    temp.innerHTML = html.trim();
    const card = temp.firstElementChild;
    wrapper.appendChild(card);

    wireIntensity(card);
    wireRemove(card);
    refreshNumbers();
  });
});
</script>
