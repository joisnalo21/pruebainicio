@extends('layouts.enfermeria')

@section('content')
@php
    /** @var \App\Models\Formulario008 $formulario */
    $f = $formulario;

    // Numero 008-000001 (si aún no creaste el accesor numero)
    $numero = $f->numero ?? ('008-' . str_pad((string)$f->id, 6, '0', STR_PAD_LEFT));

    $estadoText = $f->esCompleto() ? 'Completo' : ($f->esArchivado() ? 'Archivado' : 'Borrador');
    $estadoTone = $f->esCompleto() ? 'green' : ($f->esArchivado() ? 'gray' : 'yellow');

    $badgeClass = fn($tone) => match($tone){
        'green' => 'bg-green-100 text-green-800 border-green-200',
        'yellow'=> 'bg-yellow-100 text-yellow-800 border-yellow-200',
        'blue'  => 'bg-blue-100 text-blue-800 border-blue-200',
        'red'   => 'bg-red-100 text-red-800 border-red-200',
        default => 'bg-gray-100 text-gray-800 border-gray-200',
    };

    $fmt = function ($v) {
        if (is_bool($v)) return $v ? 'Sí' : 'No';
        if (is_null($v)) return null;
        if (is_string($v) && trim($v) === '') return null;
        return $v;
    };

    $isEmpty = function ($v) use ($fmt) {
        $v = $fmt($v);
        if (is_null($v)) return true;
        if (is_array($v)) return count(array_filter($v, fn($x) => !blank($x))) === 0;
        return false;
    };

    // Helper para “fila” de dato: si está vacío, lo marcamos como data-empty para poder ocultarlo por JS.
    $row = function(string $label, $value, string $span = 'col-span-1') use ($fmt, $isEmpty) {
        $value = $fmt($value);
        $empty = $isEmpty($value);
        $val = $empty ? '—' : e(is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : (string)$value);

        return <<<HTML
            <div class="{$span} js-field" data-empty="{$empty}">
                <div class="text-[11px] uppercase tracking-wide text-gray-500">{$label}</div>
                <div class="mt-0.5 text-sm font-semibold text-gray-800 break-words">{$val}</div>
            </div>
        HTML;
    };

    $chips = function($arr, string $tone = 'blue') use ($badgeClass) {
        $arr = is_array($arr) ? array_values(array_filter($arr)) : [];
        if (count($arr) === 0) return '<span class="text-sm text-gray-500">—</span>';

        $cls = $badgeClass($tone);
        $out = '<div class="flex flex-wrap gap-2">';
        foreach ($arr as $x) {
            $out .= '<span class="px-2 py-1 rounded-full border text-xs font-semibold '.$cls.'">'.e($x).'</span>';
        }
        $out .= '</div>';
        return $out;
    };

    $boolPill = function($v) use ($badgeClass){
        $tone = $v ? 'green' : 'gray';
        $cls = $badgeClass($tone);
        return '<span class="px-2 py-1 rounded-full border text-xs font-semibold '.$cls.'">'.($v ? 'Sí' : 'No').'</span>';
    };

    $cardTitle = fn($t, $id) => '<div class="flex items-center justify-between gap-2 mb-3">
        <h2 id="'.$id.'" class="text-base md:text-lg font-bold text-gray-900">'.$t.'</h2>
        <a href="#top" class="text-xs text-gray-500 hover:underline">Subir</a>
    </div>';
@endphp

<div id="top" class="space-y-5">

    {{-- Sticky Header --}}
    <div class="sticky top-0 z-10 bg-white/90 backdrop-blur border rounded-2xl shadow-sm px-4 py-3">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
            <div class="min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <h1 class="text-lg md:text-xl font-extrabold text-green-700 truncate">
                        Resumen Formulario 008 — {{ $numero }}
                    </h1>
                    <span class="px-2 py-1 rounded-full border text-xs font-semibold {{ $badgeClass($estadoTone) }}">
                        {{ $estadoText }}
                    </span>
                </div>

                <div class="mt-1 text-xs text-gray-600">
                    Paciente:
                    <span class="font-semibold text-gray-900">
                        {{ $f->paciente?->nombre_completo ?? '—' }}
                    </span>
                    · CI:
                    <span class="font-semibold text-gray-900">
                        {{ $f->paciente?->cedula ?? '—' }}
                    </span>
                    · Última actualización:
                    <span class="font-semibold text-gray-900">
                        {{ optional($f->updated_at)->format('d/m/Y H:i') ?? '—' }}
                    </span>
                </div>
            </div>

            <div class="flex items-center gap-2 flex-wrap justify-end">
                <a href="{{ route('enfermero.formularios.index') }}"
                   class="px-3 py-2 rounded-xl border text-sm hover:bg-gray-50">
                    ← Volver
                </a>

                @if($f->esCompleto())
                    <a target="_blank" rel="noopener"
                       href="{{ route('enfermero.formularios.pdf', $f->id) }}"
                       class="px-3 py-2 rounded-xl bg-gray-900 text-white text-sm hover:bg-black">
                        Ver PDF
                    </a>
                @endif

                <label class="inline-flex items-center gap-2 px-3 py-2 rounded-xl border text-sm bg-white">
                    <input id="toggleEmpty" type="checkbox" class="accent-green-700">
                    Mostrar vacíos
                </label>
            </div>
        </div>

        {{-- Quick nav --}}
        <div class="mt-3 flex flex-wrap gap-2 text-xs">
            <a href="#paciente" class="px-2 py-1 rounded-full border hover:bg-gray-50">Paciente</a>
            <a href="#admision" class="px-2 py-1 rounded-full border hover:bg-gray-50">Admisión</a>
            <a href="#evento" class="px-2 py-1 rounded-full border hover:bg-gray-50">Evento</a>
            <a href="#vitales" class="px-2 py-1 rounded-full border hover:bg-gray-50">Signos vitales</a>
            <a href="#dolor" class="px-2 py-1 rounded-full border hover:bg-gray-50">Dolor</a>
            <a href="#antecedentes" class="px-2 py-1 rounded-full border hover:bg-gray-50">Antecedentes</a>
            <a href="#examen" class="px-2 py-1 rounded-full border hover:bg-gray-50">Examen físico</a>
            <a href="#lesiones" class="px-2 py-1 rounded-full border hover:bg-gray-50">Lesiones</a>
            <a href="#obst" class="px-2 py-1 rounded-full border hover:bg-gray-50">Obstétrica</a>
            <a href="#labs" class="px-2 py-1 rounded-full border hover:bg-gray-50">Exámenes</a>
            <a href="#dx" class="px-2 py-1 rounded-full border hover:bg-gray-50">Dx/Plan/Alta</a>
        </div>
    </div>

    {{-- Paciente --}}
    <div class="bg-white border rounded-2xl p-5 shadow-sm">
        {!! $cardTitle('Datos del paciente', 'paciente') !!}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            {!! $row('Nombre', $f->paciente?->nombre_completo) !!}
            {!! $row('Cédula', $f->paciente?->cedula) !!}
            {!! $row('Edad / Sexo', trim(($f->paciente?->edad ?? '—').' / '.($f->paciente?->sexo ?? '—'))) !!}

            {!! $row('Dirección', $f->paciente?->direccion, 'md:col-span-2') !!}
            {!! $row('Teléfono', $f->paciente?->telefono) !!}
        </div>
    </div>

    {{-- Admisión --}}
    <div class="bg-white border rounded-2xl p-5 shadow-sm">
        {!! $cardTitle('Admisión / Encabezado', 'admision') !!}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            {!! $row('Institución', $f->institucion_sistema) !!}
            {!! $row('Unidad operativa', $f->unidad_operativa) !!}
            {!! $row('Código UO', $f->cod_uo) !!}

            {!! $row('Provincia / Cantón / Parroquia', trim(($f->cod_provincia ?? '—').' / '.($f->cod_canton ?? '—').' / '.($f->cod_parroquia ?? '—'))) !!}
            {!! $row('N° Historia clínica', $f->numero_historia_clinica) !!}
            {!! $row('Fecha admisión', $f->fecha_admision ? \Carbon\Carbon::parse($f->fecha_admision)->format('d/m/Y') : null) !!}

            {!! $row('Referido de', $f->referido_de) !!}
            {!! $row('Forma llegada', $f->forma_llegada) !!}
            {!! $row('Fuente información', $f->fuente_informacion) !!}

            {!! $row('Avisar: Nombre', $f->avisar_nombre) !!}
            {!! $row('Avisar: Parentesco', $f->avisar_parentesco) !!}
            {!! $row('Avisar: Teléfono', $f->avisar_telefono) !!}
            {!! $row('Avisar: Dirección', $f->avisar_direccion, 'md:col-span-3') !!}
        </div>
    </div>

    {{-- Evento / Motivo --}}
    <div class="bg-white border rounded-2xl p-5 shadow-sm">
        {!! $cardTitle('Motivo / Evento', 'evento') !!}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            {!! $row('Hora inicio atención', $f->hora_inicio_atencion) !!}
            {!! $row('Motivo / causa', $f->motivo_causa) !!}
            {!! $row('Notificación policía', $f->notificacion_policia === null ? null : ($f->notificacion_policia ? 'Sí' : 'No')) !!}

            {!! $row('Otro motivo (detalle)', $f->otro_motivo_detalle, 'md:col-span-2') !!}
            {!! $row('Grupo sanguíneo', $f->grupo_sanguineo) !!}

            {!! $row('Evento fecha/hora', $f->evento_fecha_hora ? $f->evento_fecha_hora->format('d/m/Y H:i') : null) !!}
            {!! $row('Evento lugar', $f->evento_lugar) !!}
            {!! $row('Evento dirección', $f->evento_direccion) !!}

            <div class="md:col-span-3 js-field" data-empty="{{ $isEmpty($f->evento_tipos) ? '1':'0' }}">
                <div class="text-[11px] uppercase tracking-wide text-gray-500">Tipos de evento</div>
                <div class="mt-2">{!! $chips($f->evento_tipos, 'blue') !!}</div>
            </div>

            <div class="js-field" data-empty="{{ $f->custodia_policial === null ? '1':'0' }}">
                <div class="text-[11px] uppercase tracking-wide text-gray-500">Custodia policial</div>
                <div class="mt-1">{!! $f->custodia_policial === null ? '—' : $boolPill((bool)$f->custodia_policial) !!}</div>
            </div>

            <div class="js-field" data-empty="{{ $f->aliento_etilico === null ? '1':'0' }}">
                <div class="text-[11px] uppercase tracking-wide text-gray-500">Aliento etílico</div>
                <div class="mt-1">{!! $f->aliento_etilico === null ? '—' : $boolPill((bool)$f->aliento_etilico) !!}</div>
            </div>

            {!! $row('Valor alcochek', $f->valor_alcochek) !!}

            {!! $row('Observaciones', $f->evento_observaciones, 'md:col-span-3') !!}
        </div>
    </div>

    {{-- Signos vitales (más visual) --}}
    <div class="bg-white border rounded-2xl p-5 shadow-sm">
        {!! $cardTitle('Signos vitales', 'vitales') !!}

        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <div class="border rounded-2xl p-4">
                <div class="text-xs text-gray-500">PA</div>
                <div class="text-xl font-extrabold">{{ $f->pa_sistolica ?? '—' }}/{{ $f->pa_diastolica ?? '—' }}</div>
            </div>
            <div class="border rounded-2xl p-4">
                <div class="text-xs text-gray-500">FC</div>
                <div class="text-xl font-extrabold">{{ $f->frecuencia_cardiaca ?? '—' }}</div>
            </div>
            <div class="border rounded-2xl p-4">
                <div class="text-xs text-gray-500">FR</div>
                <div class="text-xl font-extrabold">{{ $f->frecuencia_respiratoria ?? '—' }}</div>
            </div>
            <div class="border rounded-2xl p-4">
                <div class="text-xs text-gray-500">SatO2</div>
                <div class="text-xl font-extrabold">{{ $f->saturacion_oxigeno ?? '—' }}</div>
            </div>

            <div class="border rounded-2xl p-4">
                <div class="text-xs text-gray-500">Temp bucal</div>
                <div class="text-lg font-bold">{{ $f->temp_bucal ?? '—' }}</div>
            </div>
            <div class="border rounded-2xl p-4">
                <div class="text-xs text-gray-500">Temp axilar</div>
                <div class="text-lg font-bold">{{ $f->temp_axilar ?? '—' }}</div>
            </div>
            <div class="border rounded-2xl p-4">
                <div class="text-xs text-gray-500">Peso</div>
                <div class="text-lg font-bold">{{ $f->peso ?? '—' }}</div>
            </div>
            <div class="border rounded-2xl p-4">
                <div class="text-xs text-gray-500">Talla</div>
                <div class="text-lg font-bold">{{ $f->talla ?? '—' }}</div>
            </div>
        </div>

        <div class="mt-4 grid grid-cols-1 md:grid-cols-4 gap-4">
            {!! $row('Llenado capilar', $f->tiempo_llenado_capilar) !!}
            {!! $row('Glasgow total', $f->glasgow_total) !!}
            {!! $row('Pupila der', $f->reaccion_pupila_der) !!}
            {!! $row('Pupila izq', $f->reaccion_pupila_izq) !!}
        </div>
    </div>

    {{-- Dolor (compacto, sin bloques gigantes) --}}
    <div class="bg-white border rounded-2xl p-5 shadow-sm">
        {!! $cardTitle('Dolor', 'dolor') !!}

        @if($f->no_aplica_dolor)
            <div class="text-sm text-gray-600">No aplica.</div>
        @else
            @php $dol = $f->dolor_items ?? []; @endphp
            @if(empty($dol))
                <div class="text-sm text-gray-500">—</div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="text-left text-gray-600 border-b">
                            <tr>
                                <th class="py-2 pr-3">#</th>
                                <th class="py-2 pr-3">Región</th>
                                <th class="py-2 pr-3">Punto</th>
                                <th class="py-2 pr-3">Situación</th>
                                <th class="py-2 pr-3">Tipo</th>
                                <th class="py-2 pr-3">Evolución</th>
                                <th class="py-2 pr-3">Intensidad</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @foreach($dol as $i => $d)
                                <tr class="hover:bg-gray-50">
                                    <td class="py-2 pr-3 font-semibold">{{ $i+1 }}</td>
                                    <td class="py-2 pr-3">{{ $d['region'] ?? '—' }}</td>
                                    <td class="py-2 pr-3">{{ $d['punto'] ?? '—' }}</td>
                                    <td class="py-2 pr-3">{{ $d['situacion'] ?? '—' }}</td>
                                    <td class="py-2 pr-3">{{ $d['tipo'] ?? '—' }}</td>
                                    <td class="py-2 pr-3">{{ $d['evolucion'] ?? '—' }}</td>
                                    <td class="py-2 pr-3 font-bold">{{ $d['intensidad'] ?? '—' }}/10</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Detalles “modifica/alivia” colapsables por dolor --}}
                <details class="mt-4">
                    <summary class="cursor-pointer text-sm font-semibold text-gray-800">Ver detalles (modifica / alivia)</summary>
                    <div class="mt-3 space-y-3">
                        @foreach($dol as $i => $d)
                            <div class="border rounded-xl p-4">
                                <div class="font-semibold text-gray-900">Dolor {{ $i+1 }}</div>
                                <div class="mt-2 text-sm">
                                    <div class="text-xs text-gray-500">Se modifica con</div>
                                    {!! $chips($d['se_modifica_con'] ?? [], 'gray') !!}
                                </div>
                                <div class="mt-2 text-sm">
                                    <div class="text-xs text-gray-500">Alivia con</div>
                                    {!! $chips($d['alivia_con'] ?? [], 'gray') !!}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </details>
            @endif
        @endif
    </div>

    {{-- Antecedentes --}}
    <div class="bg-white border rounded-2xl p-5 shadow-sm">
        {!! $cardTitle('Antecedentes', 'antecedentes') !!}
        @if($f->antecedentes_no_aplica)
            <div class="text-sm text-gray-600">No aplica.</div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="md:col-span-3 js-field" data-empty="{{ $isEmpty($f->antecedentes_tipos) ? '1':'0' }}">
                    <div class="text-[11px] uppercase tracking-wide text-gray-500">Tipos</div>
                    <div class="mt-2">{!! $chips($f->antecedentes_tipos, 'blue') !!}</div>
                </div>

                {!! $row('Otro (texto)', $f->antecedentes_otro_texto) !!}
                {!! $row('Detalle', $f->antecedentes_detalle, 'md:col-span-2') !!}
            </div>
        @endif
    </div>

    {{-- Examen físico --}}
    <div class="bg-white border rounded-2xl p-5 shadow-sm">
        {!! $cardTitle('Examen físico', 'examen') !!}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="md:col-span-3 js-field" data-empty="{{ $isEmpty($f->examen_fisico_checks) ? '1':'0' }}">
                <div class="text-[11px] uppercase tracking-wide text-gray-500">Checks</div>
                <div class="mt-2">{!! $chips($f->examen_fisico_checks, 'blue') !!}</div>
            </div>
            {!! $row('Descripción', $f->examen_fisico_descripcion, 'md:col-span-3') !!}
        </div>
    </div>

    {{-- Lesiones --}}
    <div class="bg-white border rounded-2xl p-5 shadow-sm">
        {!! $cardTitle('Lesiones', 'lesiones') !!}
        @if($f->no_aplica_lesiones)
            <div class="text-sm text-gray-600">No aplica.</div>
        @else
            @php $les = $f->lesiones ?? []; @endphp
            @if(empty($les))
                <div class="text-sm text-gray-500">—</div>
            @else
                <div class="space-y-2">
                    @foreach($les as $idx => $l)
                        <div class="border rounded-xl p-4">
                            <div class="font-semibold">Lesión {{ $idx+1 }}</div>
                            <div class="text-sm text-gray-700 mt-1">
                                Tipo: <span class="font-semibold">{{ $l['tipo'] ?? '—' }}</span> ·
                                Localización: <span class="font-semibold">{{ $l['localizacion'] ?? '—' }}</span>
                            </div>
                            @if(!empty($l['obs']))
                                <div class="text-sm mt-2 text-gray-800 whitespace-pre-wrap">{{ $l['obs'] }}</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        @endif
    </div>

    {{-- Obstétrica --}}
    <div class="bg-white border rounded-2xl p-5 shadow-sm">
        {!! $cardTitle('Emergencia obstétrica', 'obst') !!}
        @if($f->no_aplica_obstetrica)
            <div class="text-sm text-gray-600">No aplica.</div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                {!! $row('Gestas', $f->obst_gestas) !!}
                {!! $row('Partos', $f->obst_partos) !!}
                {!! $row('Abortos', $f->obst_abortos) !!}
                {!! $row('Cesáreas', $f->obst_cesareas) !!}

                {!! $row('FUM', $f->obst_fum ? \Carbon\Carbon::parse($f->obst_fum)->format('d/m/Y') : null) !!}
                {!! $row('Semanas gestación', $f->obst_semanas_gestacion) !!}
                {!! $row('Mov. fetal', $f->obst_movimiento_fetal) !!}
                {!! $row('Frecuencia fetal', $f->obst_frecuencia_fetal) !!}

                {!! $row('Altura uterina', $f->obst_altura_uterina) !!}
                <div class="js-field" data-empty="{{ $f->obst_membranas_rotas === null ? '1':'0' }}">
                    <div class="text-[11px] uppercase tracking-wide text-gray-500">Membranas rotas</div>
                    <div class="mt-1">{!! $f->obst_membranas_rotas === null ? '—' : $boolPill((bool)$f->obst_membranas_rotas) !!}</div>
                </div>
                {!! $row('Tiempo membranas', $f->obst_tiempo_membranas_rotas) !!}
                {!! $row('Presentación', $f->obst_presentacion) !!}

                {!! $row('Dilatación (cm)', $f->obst_dilatacion_cm) !!}
                {!! $row('Borramiento (%)', $f->obst_borramiento_pct) !!}
                {!! $row('Plano', $f->obst_plano) !!}
                {!! $row('Pelvis útil', $f->obst_pelvis_util) !!}

                <div class="js-field" data-empty="{{ $f->obst_sangrado_vaginal === null ? '1':'0' }}">
                    <div class="text-[11px] uppercase tracking-wide text-gray-500">Sangrado vaginal</div>
                    <div class="mt-1">{!! $f->obst_sangrado_vaginal === null ? '—' : $boolPill((bool)$f->obst_sangrado_vaginal) !!}</div>
                </div>
                <div class="js-field" data-empty="{{ $f->obst_contracciones === null ? '1':'0' }}">
                    <div class="text-[11px] uppercase tracking-wide text-gray-500">Contracciones</div>
                    <div class="mt-1">{!! $f->obst_contracciones === null ? '—' : $boolPill((bool)$f->obst_contracciones) !!}</div>
                </div>
                {!! $row('Texto/Observación', $f->obst_texto, 'md:col-span-2') !!}
            </div>
        @endif
    </div>

    {{-- Exámenes solicitados --}}
    <div class="bg-white border rounded-2xl p-5 shadow-sm">
        {!! $cardTitle('Solicitud de exámenes', 'labs') !!}
        @if($f->no_aplica_examenes)
            <div class="text-sm text-gray-600">No aplica.</div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="md:col-span-3 js-field" data-empty="{{ $isEmpty($f->examenes_solicitados) ? '1':'0' }}">
                    <div class="text-[11px] uppercase tracking-wide text-gray-500">Exámenes solicitados</div>
                    <div class="mt-2">{!! $chips($f->examenes_solicitados, 'blue') !!}</div>
                </div>
                {!! $row('Comentarios', $f->examenes_comentarios, 'md:col-span-3') !!}
            </div>
        @endif
    </div>

    {{-- Dx / Plan / Alta --}}
    <div class="bg-white border rounded-2xl p-5 shadow-sm">
        {!! $cardTitle('Diagnósticos / Plan / Alta', 'dx') !!}

        @php
            $dxI = $f->diagnosticos_ingreso ?? [];
            $dxA = $f->diagnosticos_alta ?? [];
            $pt  = $f->plan_tratamiento ?? [];
        @endphp

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <div class="border rounded-2xl p-4">
                <div class="font-bold text-gray-900 mb-2">Dx ingreso</div>
                @if(empty($dxI))
                    <div class="text-sm text-gray-500">—</div>
                @else
                    <div class="space-y-2 text-sm">
                        @foreach($dxI as $r)
                            @continue(blank($r['dx'] ?? null) && blank($r['cie'] ?? null))
                            <div class="border rounded-xl p-3">
                                <div class="font-semibold">{{ $r['dx'] ?? '—' }}</div>
                                <div class="text-xs text-gray-600">CIE: {{ $r['cie'] ?? '—' }} · Tipo: {{ $r['tipo'] ?? '—' }}</div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="border rounded-2xl p-4">
                <div class="font-bold text-gray-900 mb-2">Dx alta</div>
                @if(empty($dxA))
                    <div class="text-sm text-gray-500">—</div>
                @else
                    <div class="space-y-2 text-sm">
                        @foreach($dxA as $r)
                            @continue(blank($r['dx'] ?? null) && blank($r['cie'] ?? null))
                            <div class="border rounded-xl p-3">
                                <div class="font-semibold">{{ $r['dx'] ?? '—' }}</div>
                                <div class="text-xs text-gray-600">CIE: {{ $r['cie'] ?? '—' }} · Tipo: {{ $r['tipo'] ?? '—' }}</div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="border rounded-2xl p-4">
                <div class="font-bold text-gray-900 mb-2">Plan tratamiento</div>
                @if(empty($pt))
                    <div class="text-sm text-gray-500">—</div>
                @else
                    <div class="space-y-2 text-sm">
                        @foreach($pt as $r)
                            @continue(blank($r['medicamento'] ?? null) && blank($r['posologia'] ?? null) && blank($r['indicaciones'] ?? null))
                            <div class="border rounded-xl p-3">
                                <div class="font-semibold">{{ $r['medicamento'] ?? '—' }}</div>
                                <div class="text-xs text-gray-600">{{ $r['posologia'] ?? '—' }}</div>
                                @if(!blank($r['indicaciones'] ?? null))
                                    <div class="mt-2 whitespace-pre-wrap">{{ $r['indicaciones'] }}</div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div class="mt-4 grid grid-cols-1 md:grid-cols-4 gap-4">
            {!! $row('Destino', $f->alta_destino) !!}
            {!! $row('Resultado', $f->alta_resultado) !!}
            {!! $row('Condición', $f->alta_condicion) !!}
            {!! $row('Causa', $f->alta_causa) !!}

            {!! $row('Días incapacidad', $f->alta_dias_incapacidad) !!}
            {!! $row('Fecha control', $f->alta_fecha_control ? \Carbon\Carbon::parse($f->alta_fecha_control)->format('d/m/Y') : null) !!}
            {!! $row('Hora finalización', $f->alta_hora_finalizacion) !!}
            {!! $row('Profesional (código)', $f->alta_profesional_codigo) !!}

            {!! $row('Servicio referencia', $f->alta_servicio_referencia, 'md:col-span-2') !!}
            {!! $row('Establecimiento ref.', $f->alta_establecimiento_referencia, 'md:col-span-2') !!}
        </div>
    </div>

</div>

{{-- JS: Ocultar/mostrar campos vacíos --}}
<script>
  (function(){
    const toggle = document.getElementById('toggleEmpty');
    const fields = () => Array.from(document.querySelectorAll('.js-field'));

    function apply() {
      const showEmpty = toggle.checked;
      fields().forEach(el => {
        const isEmpty = el.getAttribute('data-empty') === '1' || el.getAttribute('data-empty') === 'true';
        if (isEmpty && !showEmpty) el.classList.add('hidden');
        else el.classList.remove('hidden');
      });
    }

    toggle.addEventListener('change', apply);
    apply();
  })();
</script>
@endsection
