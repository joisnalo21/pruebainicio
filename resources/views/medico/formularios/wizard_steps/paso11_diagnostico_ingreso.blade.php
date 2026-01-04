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
$dbIngreso = $formulario->diagnosticos_ingreso ?? [];
$dbAlta = $formulario->diagnosticos_alta ?? [];

$ingreso = [];
$alta = [];

for ($i = 1; $i <= 3; $i++) {
    $ingreso[$i]=[ 'dx'=> old("diagnosticos_ingreso.$i.dx", $dbIngreso[$i]['dx'] ?? null),
    'cie' => old("diagnosticos_ingreso.$i.cie", $dbIngreso[$i]['cie'] ?? null),
    'tipo' => old("diagnosticos_ingreso.$i.tipo", $dbIngreso[$i]['tipo'] ?? null),
    ];

    $alta[$i] = [
    'dx' => old("diagnosticos_alta.$i.dx", $dbAlta[$i]['dx'] ?? null),
    'cie' => old("diagnosticos_alta.$i.cie", $dbAlta[$i]['cie'] ?? null),
    'tipo' => old("diagnosticos_alta.$i.tipo", $dbAlta[$i]['tipo'] ?? null),
    ];
    }

    $renderTabla = function($title, $name, $rows) {
    ob_start();
    @endphp

    <div class="rounded-2xl border border-gray-200 bg-white p-5">
        <div class="flex items-start justify-between gap-4 mb-2">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">{{ $title }}</h3>
                <p class="text-sm text-gray-500">
                    Registra el diagnóstico, el código CIE y marca si es presuntivo (PRE) o definitivo (DEF).
                </p>
            </div>
            <div class="text-xs text-gray-500 leading-tight">
                <div><span class="font-semibold">PRE</span> = Presuntivo</div>
                <div><span class="font-semibold">DEF</span> = Definitivo</div>
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-200">
            <div class="grid grid-cols-12 bg-gray-50 px-4 py-2 text-xs font-semibold text-gray-700">
                <div class="col-span-1">#</div>
                <div class="col-span-7">Diagnóstico</div>
                <div class="col-span-2">CIE</div>
                <div class="col-span-2 text-center">Tipo</div>
            </div>

            @for ($i = 1; $i <= 3; $i++)
                <div class="grid grid-cols-12 gap-2 px-4 py-3 border-t border-gray-200 items-center">
                <div class="col-span-1">
                    <span class="inline-flex w-8 h-8 items-center justify-center rounded-lg bg-gray-100 text-gray-800 font-bold">
                        {{ $i }}
                    </span>
                </div>

                <div class="col-span-7">
                    <input type="text"
                        name="{{ $name }}[{{ $i }}][dx]"
                        value="{{ $rows[$i]['dx'] }}"
                        class="w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-gray-900"
                        placeholder="Ej: Apendicitis aguda, Neumonía, etc.">
                </div>

                <div class="col-span-2">
                    <input type="text"
                        name="{{ $name }}[{{ $i }}][cie]"
                        value="{{ $rows[$i]['cie'] }}"
                        class="w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-gray-900"
                        placeholder="Ej: K35.9">
                </div>

                <div class="col-span-2">
                    <div class="flex items-center justify-center gap-2">
                        <label class="inline-flex items-center gap-1 text-sm text-gray-700">
                            <input type="radio"
                                name="{{ $name }}[{{ $i }}][tipo]"
                                value="pre"
                                class="text-gray-900 focus:ring-gray-900"
                                {{ $rows[$i]['tipo'] === 'pre' ? 'checked' : '' }}>
                            PRE
                        </label>

                        <label class="inline-flex items-center gap-1 text-sm text-gray-700">
                            <input type="radio"
                                name="{{ $name }}[{{ $i }}][tipo]"
                                value="def"
                                class="text-gray-900 focus:ring-gray-900"
                                {{ $rows[$i]['tipo'] === 'def' ? 'checked' : '' }}>
                            DEF
                        </label>
                    </div>
                </div>
        </div>
        @endfor
    </div>
    </div>

    @php
    return ob_get_clean();
    };
    @endphp

    <form method="POST"
        action="{{ route('medico.formularios.paso.store', ['formulario' => $formulario->id, 'paso' => 11]) }}"
        class="space-y-6">
        @csrf

        {!! $renderTabla('Diagnóstico de ingreso', 'diagnosticos_ingreso', $ingreso) !!}
        {!! $renderTabla('Diagnóstico de alta', 'diagnosticos_alta', $alta) !!}

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