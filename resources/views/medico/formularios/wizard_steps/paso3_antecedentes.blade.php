@php
  $tipos = old('antecedentes_tipos', $formulario->antecedentes_tipos ?? []);
  if (!is_array($tipos)) $tipos = [];

  $labels = [
    'alergico' => 'Alérgico',
    'clinico' => 'Clínico',
    'ginecologico' => 'Ginecológico',
    'traumatologico' => 'Traumatológico',
    'quirurgico' => 'Quirúrgico',
    'farmacologico' => 'Farmacológico',
    'otro' => 'Otro',
  ];
@endphp

<form method="POST" action="{{ route('medico.formularios.paso.store', ['formulario' => $formulario->id, 'paso' => 3]) }}" class="space-y-6">
  @csrf

  <div class="rounded-2xl border border-gray-200 bg-white p-5">
    <div class="flex items-start justify-between gap-4 mb-1">
      <h3 class="text-lg font-semibold text-gray-900">Antecedentes personales y familiares</h3>

      <label class="inline-flex items-center gap-2 text-sm text-gray-700 select-none">
        <input
          type="checkbox"
          name="antecedentes_no_aplica"
          id="antecedentes_no_aplica"
          value="1"
          class="rounded border-gray-300 text-gray-900 focus:ring-gray-900"
          @checked(old('antecedentes_no_aplica', $formulario->antecedentes_no_aplica))
        >
        <span>No aplica</span>
      </label>
    </div>

    <p class="text-sm text-gray-500 mb-4">Seleccione lo que corresponda y describa abajo.</p>

    <div id="antecedentes_wrapper" class="space-y-4">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
        @foreach(['alergico','clinico','ginecologico','traumatologico','quirurgico','farmacologico'] as $k)
          <label class="flex items-center gap-2 text-sm text-gray-700">
            <input type="checkbox" name="antecedentes_tipos[]" value="{{ $k }}"
                   class="rounded border-gray-300 text-gray-900 focus:ring-gray-900"
                   {{ in_array($k, $tipos, true) ? 'checked' : '' }}>
            {{ $labels[$k] }}
          </label>
        @endforeach

        {{-- OTRO + TEXTO --}}
        <div class="sm:col-span-2 lg:col-span-3 rounded-xl border border-gray-200 p-3">
          <label class="flex items-center gap-2 text-sm text-gray-700 mb-2">
            <input type="checkbox" name="antecedentes_tipos[]" value="otro"
                   id="antecedentes_otro_chk"
                   class="rounded border-gray-300 text-gray-900 focus:ring-gray-900"
                   {{ in_array('otro', $tipos, true) ? 'checked' : '' }}>
            <span class="font-medium">Otro</span>
          </label>

          <input type="text"
                 name="antecedentes_otro_texto"
                 id="antecedentes_otro_texto"
                 class="w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-gray-900"
                 value="{{ old('antecedentes_otro_texto', $formulario->antecedentes_otro_texto) }}"
                 placeholder="Ej: Psiquiátrico, Neurológico, etc.">
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Detalle / observación</label>
        <textarea name="antecedentes_detalle" rows="6"
                  class="w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-gray-900"
                  placeholder="Describe antecedentes relevantes, tratamientos previos, fechas, etc...">{{ old('antecedentes_detalle', $formulario->antecedentes_detalle) }}</textarea>
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
  const chkNo = document.getElementById('antecedentes_no_aplica');
  const wrap = document.getElementById('antecedentes_wrapper');

  const chkOtro = document.getElementById('antecedentes_otro_chk');
  const txtOtro = document.getElementById('antecedentes_otro_texto');

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

  function toggleOtro() {
    const activo = !!chkOtro?.checked;
    if (!txtOtro) return;
    txtOtro.disabled = !activo || !!chkNo?.checked;
    if (!activo) txtOtro.value = '';
  }

  setDisabled(!!chkNo?.checked);
  toggleOtro();

  chkNo?.addEventListener('change', () => {
    setDisabled(chkNo.checked);
    toggleOtro();
  });

  chkOtro?.addEventListener('change', toggleOtro);
});
</script>
