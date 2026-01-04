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
  $via = old('via_aerea', $formulario->via_aerea);
  $cond = old('condicion', $formulario->condicion);
@endphp

<form method="POST"
      action="{{ route('medico.formularios.paso.store', ['formulario' => $formulario->id, 'paso' => 4]) }}"
      class="space-y-6">
    @csrf

    <div class="rounded-2xl border border-gray-200 bg-white p-5">
        <div class="flex items-start justify-between gap-4 mb-1">
            <h3 class="text-lg font-semibold text-gray-900">
                Enfermedad actual y revisión de sistemas
            </h3>

            <label class="inline-flex items-center gap-2 text-sm text-gray-700 select-none">
                <input type="checkbox"
                       name="no_aplica_enfermedad_actual"
                       id="no_aplica_enfermedad_actual"
                       value="1"
                       class="rounded border-gray-300 text-gray-900 focus:ring-gray-900"
                       @checked(old('no_aplica_enfermedad_actual', $formulario->no_aplica_enfermedad_actual))>
                <span>No aplica</span>
            </label>
        </div>

        <p class="text-sm text-gray-500 mb-4">
            Cronología · Localización · Características · Intensidad · Frecuencia · Factores agravantes
        </p>

        <div id="enfermedad_wrapper" class="space-y-4">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                {{-- Vía aérea --}}
                <div class="rounded-xl border border-gray-200 p-4">
                    <div class="font-semibold text-gray-800 mb-2">Vía aérea</div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        @foreach(['libre' => 'Vía aérea libre', 'obstruida' => 'Vía aérea obstruida'] as $k => $txt)
                            <label class="cursor-pointer rounded-xl border px-3 py-3 flex items-center gap-2
                                {{ $via === $k ? 'border-gray-900 bg-gray-50' : 'border-gray-200 hover:bg-gray-50' }}">
                                <input type="radio" name="via_aerea" value="{{ $k }}"
                                       class="text-gray-900 focus:ring-gray-900"
                                       {{ $via === $k ? 'checked' : '' }}>
                                <span class="text-sm font-medium text-gray-800">{{ $txt }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Condición --}}
                <div class="rounded-xl border border-gray-200 p-4">
                    <div class="font-semibold text-gray-800 mb-2">Condición</div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        @foreach(['estable' => 'Condición estable', 'inestable' => 'Condición inestable'] as $k => $txt)
                            <label class="cursor-pointer rounded-xl border px-3 py-3 flex items-center gap-2
                                {{ $cond === $k ? 'border-gray-900 bg-gray-50' : 'border-gray-200 hover:bg-gray-50' }}">
                                <input type="radio" name="condicion" value="{{ $k }}"
                                       class="text-gray-900 focus:ring-gray-900"
                                       {{ $cond === $k ? 'checked' : '' }}>
                                <span class="text-sm font-medium text-gray-800">{{ $txt }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Texto grande --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Descripción (enfermedad actual / revisión)
                </label>

                <textarea name="enfermedad_actual_revision" rows="6"
                          class="w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-gray-900"
                          placeholder="Describe cronología, localización, características, intensidad, frecuencia, factores agravantes...">{{ old('enfermedad_actual_revision', $formulario->enfermedad_actual_revision) }}</textarea>
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
  const chk = document.getElementById('no_aplica_enfermedad_actual');
  const wrap = document.getElementById('enfermedad_wrapper');

  function setDisabled(disabled) {
    if (!wrap) return;

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
