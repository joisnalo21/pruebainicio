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
    $db = $formulario->plan_tratamiento ?? [];
    $rows = [];

    for ($i = 1; $i <= 4; $i++) {
        $rows[$i] = [
            'indicaciones' => old("plan_tratamiento.$i.indicaciones", $db[$i]['indicaciones'] ?? null),
            'medicamento' => old("plan_tratamiento.$i.medicamento", $db[$i]['medicamento'] ?? null),
            'posologia' => old("plan_tratamiento.$i.posologia", $db[$i]['posologia'] ?? null),
        ];
    }
@endphp

<form method="POST"
      action="{{ route('medico.formularios.paso.store', ['formulario' => $formulario->id, 'paso' => 12]) }}"
      class="space-y-6">
    @csrf

    <div class="rounded-2xl border border-gray-200 bg-white p-5">
        <div class="mb-3">
            <h3 class="text-lg font-semibold text-gray-900">Plan de tratamiento</h3>
            <p class="text-sm text-gray-500">
                Registra indicaciones, medicamento (principio activo, concentración y presentación) y posología.
            </p>
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-200">
            <div class="grid grid-cols-12 bg-gray-50 px-4 py-2 text-xs font-semibold text-gray-700">
                <div class="col-span-1">#</div>
                <div class="col-span-5">Indicaciones</div>
                <div class="col-span-4">Medicamento</div>
                <div class="col-span-2">Posología</div>
            </div>

            @for($i=1; $i<=4; $i++)
                <div class="grid grid-cols-12 gap-2 px-4 py-3 border-t border-gray-200 items-start">
                    <div class="col-span-1 pt-1">
                        <span class="inline-flex w-8 h-8 items-center justify-center rounded-lg bg-gray-100 text-gray-800 font-bold">
                            {{ $i }}
                        </span>
                    </div>

                    <div class="col-span-5">
                        <textarea
                            name="plan_tratamiento[{{ $i }}][indicaciones]"
                            rows="3"
                            class="w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-gray-900"
                            placeholder="Ej: Hidratación IV, reposo, dieta, control signos vitales...">{{ $rows[$i]['indicaciones'] }}</textarea>
                    </div>

                    <div class="col-span-4">
                        <input
                            type="text"
                            name="plan_tratamiento[{{ $i }}][medicamento]"
                            value="{{ $rows[$i]['medicamento'] }}"
                            class="w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-gray-900"
                            placeholder="Ej: Paracetamol 500 mg tabletas">
                        <p class="mt-1 text-xs text-gray-500">
                            Principio activo + concentración + presentación.
                        </p>
                    </div>

                    <div class="col-span-2">
                        <input
                            type="text"
                            name="plan_tratamiento[{{ $i }}][posologia]"
                            value="{{ $rows[$i]['posologia'] }}"
                            class="w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-gray-900"
                            placeholder="Ej: c/8h x 3 días">
                    </div>
                </div>
            @endfor
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
