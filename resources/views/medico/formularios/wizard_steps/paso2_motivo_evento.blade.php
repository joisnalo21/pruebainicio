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
    $motivo = old('motivo_causa', $formulario->motivo_causa);
    $tipos = old('evento_tipos', $formulario->evento_tipos ?? []);
    if (!is_array($tipos)) $tipos = [];

    $labels = [
        'accidente_transito' => 'Accidente de tránsito',
        'caida' => 'Caída',
        'quemadura' => 'Quemadura',
        'mordedura' => 'Mordedura',
        'ahogamiento' => 'Ahogamiento',
        'cuerpo_extrano' => 'Cuerpo extraño',
        'aplastamiento' => 'Aplastamiento',
        'otro_accidente' => 'Otro accidente',

        'violencia_arma_fuego' => 'Violencia arma de fuego',
        'violencia_arma_punzante' => 'Violencia arma punzante',
        'violencia_rina' => 'Violencia x riña',
        'violencia_familiar' => 'Violencia familiar',
        'abuso_fisico' => 'Abuso físico',
        'abuso_psicologico' => 'Abuso psicológico',
        'abuso_sexual' => 'Abuso sexual',
        'otra_violencia' => 'Otra violencia',

        'intoxicacion_alcoholica' => 'Intoxicación alcohólica',
        'intoxicacion_alimentaria' => 'Intoxicación alimentaria',
        'intoxicacion_drogas' => 'Intoxicación x drogas',
        'inhalacion_gases' => 'Inhalación de gases',
        'otra_intoxicacion' => 'Otra intoxicación',

        'envenenamiento' => 'Envenenamiento',
        'picadura' => 'Picadura',
        'anafilaxia' => 'Anafilaxia',
    ];
@endphp

<form method="POST" action="{{ route('medico.formularios.paso.store', ['formulario' => $formulario->id, 'paso' => 2]) }}" class="space-y-6">
    @csrf

    {{-- Sección 2 --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-5">
        <h3 class="text-lg font-semibold text-gray-900 mb-1">Inicio de atención y motivo</h3>
        <p class="text-sm text-gray-500 mb-4">Causa principal y datos iniciales del caso.</p>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Hora inicio de atención <span class="text-red-500">*</span>
                </label>
                <input type="time" name="hora_inicio_atencion"
                       class="w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-gray-900"
                       value="{{ old('hora_inicio_atencion', $formulario->hora_inicio_atencion) }}">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Causa <span class="text-red-500">*</span>
                </label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    @foreach(['trauma'=>'Trauma','clinica'=>'Causa clínica','obstetrica'=>'Causa obstétrica','quirurgica'=>'Causa quirúrgica','otro'=>'Otro motivo'] as $k=>$txt)
                        <label class="cursor-pointer rounded-xl border px-3 py-3 flex items-center gap-2
                            {{ $motivo === $k ? 'border-gray-900 bg-gray-50' : 'border-gray-200 hover:bg-gray-50' }}">
                            <input type="radio" name="motivo_causa" value="{{ $k }}"
                                   class="text-gray-900 focus:ring-gray-900"
                                   {{ $motivo === $k ? 'checked' : '' }}>
                            <span class="text-sm font-medium text-gray-800">{{ $txt }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="md:col-span-2">
                <label class="inline-flex items-center gap-2 text-sm font-medium text-gray-700">
                    <input type="checkbox" name="notificacion_policia" value="1"
                           class="rounded border-gray-300 text-gray-900 focus:ring-gray-900"
                           {{ old('notificacion_policia', $formulario->notificacion_policia) ? 'checked' : '' }}>
                    Notificación a la policía
                </label>
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Detalle de “otro motivo”</label>
                <input type="text" name="otro_motivo_detalle"
                       class="w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-gray-900"
                       value="{{ old('otro_motivo_detalle', $formulario->otro_motivo_detalle) }}"
                       placeholder="Solo si aplica">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Grupo sanguíneo</label>
                <select name="grupo_sanguineo" class="w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-gray-900">
                    @php $gs = old('grupo_sanguineo', $formulario->grupo_sanguineo); @endphp
                    <option value="">Seleccione...</option>
                    @foreach(['O+','O-','A+','A-','B+','B-','AB+','AB-'] as $opt)
                        <option value="{{ $opt }}" {{ $gs === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- Sección 3 --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-5">
        <h3 class="text-lg font-semibold text-gray-900 mb-1">Accidente / violencia / intoxicación / otros</h3>
        <p class="text-sm text-gray-500 mb-4">Marca lo que corresponda y agrega detalles si aplica.</p>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Fecha y hora del evento</label>
                <input type="datetime-local" name="evento_fecha_hora"
                       class="w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-gray-900"
                       value="{{ old('evento_fecha_hora', $formulario->evento_fecha_hora ? \Carbon\Carbon::parse($formulario->evento_fecha_hora)->format('Y-m-d\TH:i') : '' ) }}">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Lugar del evento</label>
                <input type="text" name="evento_lugar"
                       class="w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-gray-900"
                       value="{{ old('evento_lugar', $formulario->evento_lugar) }}"
                       placeholder="Ej: domicilio, vía pública, trabajo...">
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Dirección del evento</label>
                <input type="text" name="evento_direccion"
                       class="w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-gray-900"
                       value="{{ old('evento_direccion', $formulario->evento_direccion) }}">
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <div class="rounded-xl border border-gray-200 p-4">
                <div class="font-semibold text-gray-800 mb-2">Accidente</div>
                <div class="space-y-2">
                    @foreach(['accidente_transito','caida','quemadura','mordedura','ahogamiento','cuerpo_extrano','aplastamiento','otro_accidente'] as $k)
                        <label class="flex items-center gap-2 text-sm text-gray-700">
                            <input type="checkbox" name="evento_tipos[]" value="{{ $k }}"
                                   class="rounded border-gray-300 text-gray-900 focus:ring-gray-900"
                                   {{ in_array($k, $tipos) ? 'checked' : '' }}>
                            {{ $labels[$k] }}
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 p-4">
                <div class="font-semibold text-gray-800 mb-2">Violencia</div>
                <div class="space-y-2">
                    @foreach(['violencia_arma_fuego','violencia_arma_punzante','violencia_rina','violencia_familiar','abuso_fisico','abuso_psicologico','abuso_sexual','otra_violencia'] as $k)
                        <label class="flex items-center gap-2 text-sm text-gray-700">
                            <input type="checkbox" name="evento_tipos[]" value="{{ $k }}"
                                   class="rounded border-gray-300 text-gray-900 focus:ring-gray-900"
                                   {{ in_array($k, $tipos) ? 'checked' : '' }}>
                            {{ $labels[$k] }}
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 p-4">
                <div class="font-semibold text-gray-800 mb-2">Intoxicación / Otros</div>
                <div class="space-y-2">
                    @foreach(['intoxicacion_alcoholica','intoxicacion_alimentaria','intoxicacion_drogas','inhalacion_gases','otra_intoxicacion','envenenamiento','picadura','anafilaxia'] as $k)
                        <label class="flex items-center gap-2 text-sm text-gray-700">
                            <input type="checkbox" name="evento_tipos[]" value="{{ $k }}"
                                   class="rounded border-gray-300 text-gray-900 focus:ring-gray-900"
                                   {{ in_array($k, $tipos) ? 'checked' : '' }}>
                            {{ $labels[$k] }}
                        </label>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="mt-4 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <label class="inline-flex items-center gap-2 text-sm font-medium text-gray-700">
                <input type="checkbox" name="no_aplica_custodia_policial" value="1"
                       class="rounded border-gray-300 text-gray-900 focus:ring-gray-900"
                       {{ old('no_aplica_custodia_policial', $formulario->no_aplica_custodia_policial) ? 'checked' : '' }}>
                No aplica custodia policial
            </label>

            <div class="flex items-center gap-2">
                <label class="inline-flex items-center gap-2 text-sm font-medium text-gray-700">
                    <input type="checkbox" name="aliento_etilico" value="1"
                           class="rounded border-gray-300 text-gray-900 focus:ring-gray-900"
                           {{ old('aliento_etilico', $formulario->aliento_etilico) ? 'checked' : '' }}>
                    Aliento etílico
                </label>

                <input type="number" step="0.01" name="valor_alcochek"
                       class="w-40 rounded-xl border-gray-300 focus:border-gray-900 focus:ring-gray-900"
                       value="{{ old('valor_alcochek', $formulario->valor_alcochek) }}"
                       placeholder="Valor alcochek">
            </div>
        </div>

        <div class="mt-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Observaciones</label>
            <textarea name="evento_observaciones" rows="4"
                      class="w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-gray-900"
                      placeholder="Describe brevemente lo ocurrido, cronología y detalles relevantes...">{{ old('evento_observaciones', $formulario->evento_observaciones) }}</textarea>
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
