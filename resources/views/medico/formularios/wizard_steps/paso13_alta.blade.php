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
    $destino = old('alta_destino', $formulario->alta_destino);
    $resultado = old('alta_resultado', $formulario->alta_resultado);
@endphp

<form id="form-alta-step13"
      method="POST"
      action="{{ route('medico.formularios.paso.store', ['formulario' => $formulario->id, 'paso' => 13]) }}"
      class="space-y-6">
    @csrf

    <div class="rounded-2xl border border-gray-200 bg-white p-5">
        <div class="mb-3">
            <h3 class="text-lg font-semibold text-gray-900">Alta</h3>
            <p class="text-sm text-gray-500">
                Completa la disposición final y cierre. Al finalizar se marca como COMPLETO.
            </p>
        </div>

        {{-- Disposición / destino --}}
        <div class="rounded-2xl border border-gray-200 p-4">
            <div class="text-sm font-semibold text-gray-900 mb-2">Disposición / Destino</div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-2">
                @php
                    $optsDestino = [
                        'domicilio' => 'Domicilio',
                        'consulta_externa' => 'Consulta externa',
                        'observacion' => 'Observación',
                        'internacion' => 'Internación',
                        'referencia' => 'Referencia',
                    ];
                @endphp

                @foreach($optsDestino as $k => $lbl)
                    <label class="cursor-pointer rounded-xl border px-3 py-3 flex items-center gap-2
                        {{ $destino === $k ? 'border-gray-900 bg-gray-50' : 'border-gray-200 hover:bg-gray-50' }}">
                        <input type="radio" name="alta_destino" value="{{ $k }}"
                               class="text-gray-900 focus:ring-gray-900"
                               {{ $destino === $k ? 'checked' : '' }}>
                        <span class="text-sm font-medium text-gray-800">{{ $lbl }}</span>
                    </label>
                @endforeach
            </div>

            {{-- Referencia extra --}}
            <div id="ref_block" class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Servicio de referencia</label>
                    <input type="text" name="alta_servicio_referencia"
                           class="w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-gray-900"
                           value="{{ old('alta_servicio_referencia', $formulario->alta_servicio_referencia) }}"
                           placeholder="Ej: Ginecología, Traumatología, UCI...">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Establecimiento</label>
                    <input type="text" name="alta_establecimiento_referencia"
                           class="w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-gray-900"
                           value="{{ old('alta_establecimiento_referencia', $formulario->alta_establecimiento_referencia) }}"
                           placeholder="Ej: Hospital X, Centro Y...">
                </div>
            </div>
        </div>

        {{-- Resultado --}}
        <div class="rounded-2xl border border-gray-200 p-4 mt-4">
            <div class="text-sm font-semibold text-gray-900 mb-2">Resultado</div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                @php
                    $optsRes = [
                        'vivo' => 'Egresa vivo',
                        'muerto_emergencia' => 'Muerto en emergencia',
                    ];
                @endphp

                @foreach($optsRes as $k => $lbl)
                    <label class="cursor-pointer rounded-xl border px-3 py-3 flex items-center gap-2
                        {{ $resultado === $k ? 'border-gray-900 bg-gray-50' : 'border-gray-200 hover:bg-gray-50' }}">
                        <input type="radio" name="alta_resultado" value="{{ $k }}"
                               class="text-gray-900 focus:ring-gray-900"
                               {{ $resultado === $k ? 'checked' : '' }}>
                        <span class="text-sm font-medium text-gray-800">{{ $lbl }}</span>
                    </label>
                @endforeach
            </div>

            <div id="cond_block" class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Condición</label>
                    @php $cond = old('alta_condicion', $formulario->alta_condicion); @endphp
                    <div class="grid grid-cols-2 gap-2">
                        @foreach(['estable'=>'En condición estable','inestable'=>'En condición inestable'] as $k=>$txt)
                            <label class="cursor-pointer rounded-xl border px-3 py-3 flex items-center gap-2
                                {{ $cond === $k ? 'border-gray-900 bg-gray-50' : 'border-gray-200 hover:bg-gray-50' }}">
                                <input type="radio" name="alta_condicion" value="{{ $k }}"
                                       class="text-gray-900 focus:ring-gray-900"
                                       {{ $cond === $k ? 'checked' : '' }}>
                                <span class="text-sm font-medium text-gray-800">{{ $txt }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Días de incapacidad</label>
                    <input type="number" name="alta_dias_incapacidad" min="0" max="365"
                           class="w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-gray-900"
                           value="{{ old('alta_dias_incapacidad', $formulario->alta_dias_incapacidad) }}"
                           placeholder="0 - 365">
                    <p class="mt-1 text-xs text-gray-500">Opcional (solo si egresa vivo).</p>
                </div>
            </div>

            <div id="causa_block" class="mt-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Causa</label>
                <input type="text" name="alta_causa"
                       class="w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-gray-900"
                       value="{{ old('alta_causa', $formulario->alta_causa) }}"
                       placeholder="Causa (requerida si Muerto en emergencia)">
            </div>
        </div>

        {{-- Cierre --}}
        <div class="rounded-2xl border border-gray-200 p-4 mt-4">
            <div class="text-sm font-semibold text-gray-900 mb-2">Cierre</div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de control</label>
                    <input type="date" name="alta_fecha_control"
                           class="w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-gray-900"
                           value="{{ old('alta_fecha_control', optional($formulario->alta_fecha_control)->format('Y-m-d')) }}">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Hora finalización</label>
                    <input id="alta_hora_finalizacion" type="time" name="alta_hora_finalizacion"
                           class="w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-gray-900"
                           value="{{ old('alta_hora_finalizacion', $formulario->alta_hora_finalizacion) }}">
                    <p class="mt-1 text-xs text-gray-500">Requerido al Finalizar.</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Profesional y código</label>
                    <input id="alta_profesional_codigo" type="text" name="alta_profesional_codigo"
                           class="w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-gray-900"
                           value="{{ old('alta_profesional_codigo', $formulario->alta_profesional_codigo) }}"
                           placeholder="Nombre y código">
                    <p class="mt-1 text-xs text-gray-500">Requerido al Finalizar.</p>
                </div>

                <div class="md:col-span-1">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Número de hoja</label>
                    <input type="number" name="alta_numero_hoja" min="1"
                           class="w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-gray-900"
                           value="{{ old('alta_numero_hoja', $formulario->alta_numero_hoja) }}">
                </div>

                <div class="md:col-span-2">
                    <div class="rounded-xl border border-gray-200 bg-gray-50 p-3 text-sm text-gray-700">
                        <div class="font-semibold mb-1">Firma</div>
                        <div class="text-gray-600">
                            No se solicita aquí. Recomendado: incluirla solo en el PDF final (render) o en un módulo de firma digital.
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="flex flex-col sm:flex-row gap-2 justify-end pt-2">
        <button id="btn-save" type="submit" name="accion" value="save"
                class="px-4 py-2 rounded-xl border bg-white hover:bg-gray-50 font-semibold">
            Guardar borrador
        </button>

        <button id="btn-finish" type="submit" name="accion" value="finish"
                class="px-4 py-2 rounded-xl bg-green-700 hover:bg-green-800 text-white font-semibold">
            Finalizar y marcar COMPLETO ✓
        </button>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', () => {
  // ---- UI blocks ----
  const refBlock   = document.getElementById('ref_block');
  const causaBlock = document.getElementById('causa_block');
  const condBlock  = document.getElementById('cond_block');

  const destinoRadios   = document.querySelectorAll('input[name="alta_destino"]');
  const resultadoRadios = document.querySelectorAll('input[name="alta_resultado"]');

  // ---- Form + buttons ----
  const form      = document.getElementById('form-alta-step13');
  const btnFinish = document.getElementById('btn-finish');

  // Campos requeridos al finalizar (frontend)
  const horaFin   = document.getElementById('alta_hora_finalizacion');
  const profCod   = document.getElementById('alta_profesional_codigo');

  function getChecked(name) {
    return document.querySelector(`input[name="${name}"]:checked`)?.value || null;
  }

  function toggleRef() {
    const destino = getChecked('alta_destino');
    const isRef = destino === 'referencia';

    refBlock.classList.toggle('hidden', !isRef);
    // si no es referencia, deshabilita inputs para que no se envíen basura
    refBlock.querySelectorAll('input').forEach(i => i.disabled = !isRef);
  }

  function toggleResultado() {
    const res = getChecked('alta_resultado');
    const vivo = res === 'vivo';
    const muerto = res === 'muerto_emergencia';

    condBlock.classList.toggle('hidden', !vivo);
    condBlock.querySelectorAll('input').forEach(i => i.disabled = !vivo);

    causaBlock.classList.toggle('hidden', !muerto);
    causaBlock.querySelectorAll('input').forEach(i => i.disabled = !muerto);
  }

  // Confirmación al finalizar:
  // - Si CONFIRMA: se queda accion=finish
  // - Si CANCELA: se cambia a accion=save y se envía (guarda borrador)
  btnFinish.addEventListener('click', (e) => {
    e.preventDefault();

    const ok = window.confirm(
      '⚠️ Al finalizar, el formulario quedará COMPLETO y NO se podrá modificar.\n\n' +
      '¿Deseas finalizar ahora?\n\n' +
      '• Aceptar: finalizar\n' +
      '• Cancelar: guardar borrador'
    );

    if (ok) {
      // Validación mínima en frontend (igual valida en backend)
      if (!horaFin?.value || !profCod?.value?.trim()) {
        alert('Para finalizar debes completar: Hora finalización y Profesional y código.');
        return;
      }

      // Enviar como finish
      const accion = form.querySelector('button[name="accion"]');
      // (no hace falta cambiar nada porque el botón ya es finish)
      form.submit();
    } else {
      // Cambiar a borrador y enviar
      const hidden = document.createElement('input');
      hidden.type = 'hidden';
      hidden.name = 'accion';
      hidden.value = 'save';
      form.appendChild(hidden);

      form.submit();
    }
  });

  destinoRadios.forEach(r => r.addEventListener('change', toggleRef));
  resultadoRadios.forEach(r => r.addEventListener('change', toggleResultado));

  toggleRef();
  toggleResultado();
});
</script>
