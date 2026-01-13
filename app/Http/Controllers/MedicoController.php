<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use App\Models\Paciente;
use App\Models\Formulario008;
use App\Services\Formulario008PdfService;


class MedicoController extends Controller
{
    // =========================
    // DASHBOARD
    // =========================
    public function index()
    {
        $totalPacientes = Paciente::count();
        $totalFormularios = Formulario008::count();

        return view('medico.dashboard', compact('totalPacientes', 'totalFormularios'));
    }

    // =========================
    // FORMULARIOS 008 - LISTADO
    // =========================
    public function listarFormularios(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $estado = $request->query('estado'); // completo | incompleto | archivado | null
        $desde = $request->query('desde');   // YYYY-MM-DD
        $hasta = $request->query('hasta');   // YYYY-MM-DD

        $query = Formulario008::query()
            ->with(['paciente', 'creador'])
            ->latest();

        // ✅ Por defecto: ocultar archivados
        if ($estado === null || $estado === '') {
            $query->where('estado', '!=', 'archivado');
        }

        // Búsqueda
        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->whereHas('paciente', function ($p) use ($q) {
                    $p->where('cedula', 'like', "%{$q}%")
                        ->orWhere('primer_nombre', 'like', "%{$q}%")
                        ->orWhere('segundo_nombre', 'like', "%{$q}%")
                        ->orWhere('apellido_paterno', 'like', "%{$q}%")
                        ->orWhere('apellido_materno', 'like', "%{$q}%")
                        ->orWhereRaw(
                            "CONCAT_WS(' ', primer_nombre, segundo_nombre, apellido_paterno, apellido_materno) LIKE ?",
                            ["%{$q}%"]
                        );
                })
                    ->orWhere('id', $q)
                    ->orWhereRaw("CONCAT('008-', LPAD(id, 6, '0')) LIKE ?", ["%{$q}%"]);
            });
        }

        // Fechas
        if ($desde) {
            $query->whereDate('created_at', '>=', $desde);
        }
        if ($hasta) {
            $query->whereDate('created_at', '<=', $hasta);
        }

        // ✅ Filtro por estado
        if ($estado === 'completo') {
            $query->where('estado', 'completo');
        } elseif ($estado === 'incompleto') {
            $query->where('estado', 'borrador');
        } elseif ($estado === 'archivado') {
            $query->where('estado', 'archivado');
        }

        $formularios = $query->paginate(12)->withQueryString();

        // KPIs
        $hoy = Carbon::today();
        $inicioMes = Carbon::now()->startOfMonth();

        // ✅ KPIs por defecto excluyen archivados (coherente con la bandeja)
        $baseKpi = Formulario008::query()->where('estado', '!=', 'archivado');

        $stats = [
            'hoy'        => (clone $baseKpi)->whereDate('created_at', $hoy)->count(),
            'mes'        => (clone $baseKpi)->whereDate('created_at', '>=', $inicioMes)->count(),
            'pendientes' => Formulario008::where('estado', 'borrador')->count(), // NO incluye archivados
            'trauma'     => 0,
        ];

        return view('medico.formularios.index', compact('formularios', 'stats', 'q', 'estado', 'desde', 'hasta'));
    }

    // =========================
    // NUEVO 008 - SELECCIONAR PACIENTE
    // =========================
    public function seleccionarPaciente(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $pacientes = Paciente::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where('cedula', 'like', "%{$q}%")
                    ->orWhere('primer_nombre', 'like', "%{$q}%")
                    ->orWhere('segundo_nombre', 'like', "%{$q}%")
                    ->orWhere('apellido_paterno', 'like', "%{$q}%")
                    ->orWhere('apellido_materno', 'like', "%{$q}%");
            })
            ->orderBy('apellido_paterno')
            ->orderBy('primer_nombre')
            ->paginate(10)
            ->withQueryString();

        return view('medico.formularios.seleccionar_paciente', compact('pacientes', 'q'));
    }


    // =========================
    // NUEVO 008 - CREAR BORRADOR
    // =========================
    public function iniciarFormulario008(Request $request)
    {
        $request->validate([
            'paciente_id' => ['required', 'exists:pacientes,id'],
        ]);

        $formulario = Formulario008::create([
            'paciente_id' => (int) $request->input('paciente_id'),
            'created_by' => Auth::id(),
            'estado' => 'borrador',
            'paso_actual' => 1,
        ]);

        return redirect()->route('medico.formularios.paso', [
            'formulario' => $formulario->id,
            'paso' => 1,
        ])->with('success', 'Formulario 008 creado en borrador.');
    }

    // =========================
    // WIZARD - VER PASO (GET)
    // =========================
    public function wizardPaso(Request $request, $formulario, $paso)
    {
        $paso = (int) $paso;

        $steps = config('form008.wizard', []);

        if ($paso < 1 || empty($steps) || !isset($steps[$paso])) {
            abort(404);
        }

        $form = Formulario008::with(['paciente', 'creador'])->findOrFail($formulario);

        // Seguridad: solo el creador (por ahora)
        if ((int) $form->created_by !== (int) Auth::id()) {
            abort(403);
        }

        /////////////////////////////////////////////////////////////
        if ($form->esCompleto()) {
            return redirect()->route('medico.formularios.ver.paso', [
                'formulario' => $form->id,
                'paso' => $paso,
            ]);
        }


        // No permitir saltar pasos hacia adelante
        if ($paso > (int) $form->paso_actual) {
            return redirect()->route('medico.formularios.paso', [
                'formulario' => $form->id,
                'paso' => $form->paso_actual,
            ])->with('success', 'Continúa desde el paso actual.');
        }

        // Prefill paso 1: encabezado fijo del hospital
        if ($paso === 1) {
            $h = config('form008.hospital', []);

            $dirty = false;
            foreach (
                [
                    'institucion_sistema' => $h['institucion_sistema'] ?? null,
                    'unidad_operativa' => $h['unidad_operativa'] ?? null,
                    'cod_uo' => $h['cod_uo'] ?? null,
                    'cod_provincia' => $h['cod_provincia'] ?? null,
                    'cod_canton' => $h['cod_canton'] ?? null,
                    'cod_parroquia' => $h['cod_parroquia'] ?? null,
                ] as $k => $v
            ) {
                if (blank($form->{$k}) && !blank($v)) {
                    $form->{$k} = $v;
                    $dirty = true;
                }
            }
            if ($dirty) $form->save();
        }

        return view('medico.formularios.wizard', [
            'formulario' => $form,
            'paso' => $paso,
            'steps' => $steps,
        ]);
    }

    // =========================
    // WIZARD - GUARDAR PASO (POST)
    // =========================
    public function guardarPaso(Request $request, $formulario, $paso)
    {
        $paso = (int) $paso;

        $steps = config('form008.wizard', []);
        if ($paso < 1 || empty($steps) || !isset($steps[$paso])) {
            abort(404);
        }

        $form = Formulario008::with('paciente')->findOrFail($formulario);

        if ((int) $form->created_by !== (int) Auth::id()) {
            abort(403);
            abort_if($form->esCompleto(), 403, 'Este formulario está completo y es de solo lectura.');
        }

        // -------------------------
        // PASO 1: ADMISION
        // -------------------------
        if ($paso === 1) {
            $data = $request->validate([
                'cod_uo' => ['nullable', 'string', 'max:50'],
                'numero_historia_clinica' => ['nullable', 'string', 'max:50'],
                'fecha_admision' => ['required', 'date'],
                'referido_de' => ['nullable', 'string', 'max:255'],

                'avisar_nombre' => ['nullable', 'string', 'max:255'],
                'avisar_parentesco' => ['nullable', 'string', 'max:100'],
                'avisar_direccion' => ['nullable', 'string', 'max:255'],
                'avisar_telefono' => ['nullable', 'string', 'max:50'],

                'forma_llegada' => ['required', 'in:ambulatorio,ambulancia,otro'],
                'fuente_informacion' => ['nullable', 'string', 'max:255'],
                'entrega_institucion_persona' => ['nullable', 'string', 'max:255'],
                'entrega_telefono' => ['nullable', 'string', 'max:50'],
            ]);

            $form->fill($data);
            $form->estado = 'borrador';

            if ($form->paso_actual < 2) {
                $form->paso_actual = 2;
            }

            $form->save();

            $accion = $request->input('accion', 'next');

            if ($accion === 'save') {
                return back()->with('success', 'Paso 1 guardado (borrador).');
            }

            return redirect()->route('medico.formularios.paso', [
                'formulario' => $form->id,
                'paso' => 2,
            ])->with('success', 'Paso 1 guardado. Continúa al paso 2.');
        }

        // -------------------------
        // PASO 2: MOTIVO / EVENTO
        // -------------------------
        if ($paso === 2) {

            $allowedTipos = [
                'accidente_transito',
                'caida',
                'quemadura',
                'mordedura',
                'ahogamiento',
                'cuerpo_extrano',
                'aplastamiento',
                'otro_accidente',
                'violencia_arma_fuego',
                'violencia_arma_punzante',
                'violencia_rina',
                'violencia_familiar',
                'abuso_fisico',
                'abuso_psicologico',
                'abuso_sexual',
                'otra_violencia',
                'intoxicacion_alcoholica',
                'intoxicacion_alimentaria',
                'intoxicacion_drogas',
                'inhalacion_gases',
                'otra_intoxicacion',
                'envenenamiento',
                'picadura',
                'anafilaxia',
            ];

            $data = $request->validate([
                'hora_inicio_atencion' => ['required'],
                'motivo_causa' => ['required', 'in:trauma,clinica,obstetrica,quirurgica,otro'],
                'notificacion_policia' => ['nullable', 'boolean'],
                'otro_motivo_detalle' => ['nullable', 'string', 'max:255'],
                'grupo_sanguineo' => ['nullable', 'string', 'max:5'],

                'evento_fecha_hora' => ['nullable', 'date'],
                'evento_lugar' => ['nullable', 'string', 'max:255'],
                'evento_direccion' => ['nullable', 'string', 'max:255'],
                'evento_tipos' => ['nullable', 'array'],
                'evento_tipos.*' => ['in:' . implode(',', $allowedTipos)],

                'custodia_policial' => ['nullable', 'boolean'],
                'no_aplica_apartado_3' => ['nullable', 'boolean'],
                'evento_observaciones' => ['nullable', 'string'],

                'aliento_etilico' => ['nullable', 'boolean'],
                'valor_alcochek' => ['nullable', 'numeric', 'min:0', 'max:999.99'],
            ], [
                'hora_inicio_atencion.required' => 'La hora de inicio de atención es obligatoria.',
                'motivo_causa.required' => 'Debe seleccionar la causa del motivo.',
            ]);

            if (($data['motivo_causa'] ?? null) === 'otro' && blank($data['otro_motivo_detalle'] ?? null)) {
                return back()
                    ->withErrors(['otro_motivo_detalle' => 'Debe detallar el “otro motivo”.'])
                    ->withInput();
            }

            $data['notificacion_policia'] = (bool) $request->boolean('notificacion_policia');
            $data['custodia_policial'] = (bool) $request->boolean('custodia_policial');
            $data['no_aplica_apartado_3'] = (bool) $request->boolean('no_aplica_apartado_3');
            $data['aliento_etilico'] = (bool) $request->boolean('aliento_etilico');



            if ($data['no_aplica_apartado_3']) {
                $data['evento_fecha_hora'] = null;
                $data['evento_lugar'] = null;
                $data['evento_direccion'] = null;
                $data['evento_tipos'] = null;
                $data['custodia_policial'] = false;
                $data['aliento_etilico'] = false;
                $data['valor_alcochek'] = null;
                $data['evento_observaciones'] = null;
            }


            $form->fill($data);
            $form->estado = 'borrador';



            if ($form->paso_actual < 3) {
                $form->paso_actual = 3;
            }

            $form->save();

            $accion = $request->input('accion', 'next');

            if ($accion === 'save') {
                return back()->with('success', 'Paso 2 guardado (borrador).');
            }

            return redirect()->route('medico.formularios.paso', [
                'formulario' => $form->id,
                'paso' => 3,
            ])->with('success', 'Paso 2 guardado. Continúa al paso 3.');
        }
        if ($paso === 3) {

            $allowedTipos = [
                'alergico',
                'clinico',
                'ginecologico',
                'traumatologico',
                'quirurgico',
                'farmacologico',
                'otro',
            ];

            $data = $request->validate([
                'antecedentes_no_aplica' => ['nullable', 'boolean'],
                'antecedentes_tipos' => ['nullable', 'array'],
                'antecedentes_tipos.*' => ['in:' . implode(',', $allowedTipos)],
                'antecedentes_otro_texto' => ['nullable', 'string', 'max:100'],
                'antecedentes_detalle' => ['nullable', 'string'],
            ]);

            $data['antecedentes_no_aplica'] = (bool) $request->boolean('antecedentes_no_aplica');

            // Si NO aplica: limpiar todo
            if ($data['antecedentes_no_aplica']) {
                $data['antecedentes_tipos'] = null;
                $data['antecedentes_otro_texto'] = null;
                $data['antecedentes_detalle'] = null;
            } else {
                // Si marcaron "otro", exigir texto
                $tipos = $data['antecedentes_tipos'] ?? [];
                if (in_array('otro', $tipos, true) && blank($data['antecedentes_otro_texto'] ?? null)) {
                    return back()
                        ->withErrors(['antecedentes_otro_texto' => 'Especifique el antecedente en “Otro”.'])
                        ->withInput();
                }
            }

            $form->fill($data);
            $form->estado = 'borrador';

            if ($form->paso_actual < 4) {
                $form->paso_actual = 4;
            }

            $form->save();

            $accion = $request->input('accion', 'next');

            if ($accion === 'save') {
                return back()->with('success', 'Paso 3 guardado (borrador).');
            }

            return redirect()->route('medico.formularios.paso', [
                'formulario' => $form->id,
                'paso' => 4,
            ])->with('success', 'Paso 3 guardado. Continúa al paso 4.');
        }

        if ($paso === 4) {

            $data = $request->validate([
                'no_aplica_enfermedad_actual' => ['nullable', 'boolean'],

                'via_aerea' => ['nullable', 'in:libre,obstruida'],
                'condicion' => ['nullable', 'in:estable,inestable'],
                'enfermedad_actual_revision' => ['nullable', 'string'],
            ]);

            $data['no_aplica_enfermedad_actual'] = (bool) $request->boolean('no_aplica_enfermedad_actual');

            if ($data['no_aplica_enfermedad_actual']) {
                $data['via_aerea'] = null;
                $data['condicion'] = null;
                $data['enfermedad_actual_revision'] = null;
            }

            $form->fill($data);
            $form->estado = 'borrador';

            if ($form->paso_actual < 5) {
                $form->paso_actual = 5;
            }

            $form->save();

            $accion = $request->input('accion', 'next');

            if ($accion === 'save') {
                return back()->with('success', 'Paso 4 guardado (borrador).');
            }

            return redirect()->route('medico.formularios.paso', [
                'formulario' => $form->id,
                'paso' => 5,
            ])->with('success', 'Paso 4 guardado. Continúa al paso 5.');
        }
        // -------------------------
        // PASO 5: DOLOR (Sección 6 del formulario físico)
        // -------------------------
        if ($paso === 5) {

            $allowedSituacion = ['localizado', 'difuso', 'irradiado', 'referido'];
            $allowedEvolucion = ['agudo', 'subagudo', 'cronico'];
            $allowedTipo = ['episodico', 'continuo', 'colico'];

            $allowedModifica = ['posicion', 'ingesta', 'esfuerzo', 'digito_presion'];
            $allowedAlivia   = ['analgesico', 'anti_espasmodico', 'opiaceo', 'no_alivia'];

            $data = $request->validate([
                'no_aplica_dolor' => ['nullable', 'boolean'],

                'dolor' => ['nullable', 'array'],
                'dolor.*.region' => ['nullable', 'string', 'max:255'],
                'dolor.*.punto' => ['nullable', 'string', 'max:255'],

                'dolor.*.situacion' => ['nullable', 'in:' . implode(',', $allowedSituacion)],
                'dolor.*.evolucion' => ['nullable', 'in:' . implode(',', $allowedEvolucion)],
                'dolor.*.tipo' => ['nullable', 'in:' . implode(',', $allowedTipo)],

                'dolor.*.se_modifica_con' => ['nullable', 'array'],
                'dolor.*.se_modifica_con.*' => ['in:' . implode(',', $allowedModifica)],

                'dolor.*.alivia_con' => ['nullable', 'array'],
                'dolor.*.alivia_con.*' => ['in:' . implode(',', $allowedAlivia)],

                'dolor.*.intensidad' => ['nullable', 'integer', 'min:0', 'max:10'],
            ]);

            $noAplica = (bool) $request->boolean('no_aplica_dolor');

            if ($noAplica) {
                $form->no_aplica_dolor = true;
                $form->dolor_items = null;
            } else {
                $items = $data['dolor'] ?? [];

                // elimina filas totalmente vacías
                $items = array_values(array_filter($items, function ($it) {
                    $hasAny =
                        !blank($it['region'] ?? null) ||
                        !blank($it['punto'] ?? null) ||
                        !blank($it['situacion'] ?? null) ||
                        !blank($it['evolucion'] ?? null) ||
                        !blank($it['tipo'] ?? null) ||
                        !empty($it['se_modifica_con'] ?? []) ||
                        !empty($it['alivia_con'] ?? []) ||
                        isset($it['intensidad']);
                    return $hasAny;
                }));

                // regla mínima: si existe un dolor, región y punto deben estar
                foreach ($items as $i => $it) {
                    if (blank($it['region'] ?? null) || blank($it['punto'] ?? null)) {
                        return back()
                            ->withErrors(["dolor.$i.region" => "Región y punto doloroso son obligatorios si registras un dolor."])
                            ->withInput();
                    }
                }

                $form->no_aplica_dolor = false;
                $form->dolor_items = $items;
            }

            $form->estado = 'borrador';
            if ($form->paso_actual < 6) $form->paso_actual = 6; // después del paso 5
            $form->save();

            $accion = $request->input('accion', 'next');

            if ($accion === 'save') {
                return back()->with('success', 'Dolor guardado (borrador).');
            }

            return redirect()->route('medico.formularios.paso', [
                'formulario' => $form->id,
                'paso' => 6,
            ])->with('success', 'Dolor guardado. Continúa al siguiente paso.');
        }

        if ($paso === 6) {
            $data = $request->validate([
                'pa_sistolica' => ['nullable', 'integer', 'min:0', 'max:300'],
                'pa_diastolica' => ['nullable', 'integer', 'min:0', 'max:200'],

                'frecuencia_cardiaca' => ['nullable', 'integer', 'min:0', 'max:250'],
                'frecuencia_respiratoria' => ['nullable', 'integer', 'min:0', 'max:80'],

                'temp_bucal' => ['nullable', 'numeric', 'min:30', 'max:45'],
                'temp_axilar' => ['nullable', 'numeric', 'min:30', 'max:45'],

                'peso' => ['nullable', 'numeric', 'min:0', 'max:500'],
                'talla' => ['nullable', 'numeric', 'min:0', 'max:3'],

                'saturacion_oxigeno' => ['nullable', 'integer', 'min:0', 'max:100'],
                'tiempo_llenado_capilar' => ['nullable', 'numeric', 'min:0', 'max:10'],

                'glasgow_ocular' => ['nullable', 'integer', 'min:1', 'max:4'],
                'glasgow_verbal' => ['nullable', 'integer', 'min:1', 'max:5'],
                'glasgow_motora' => ['nullable', 'integer', 'min:1', 'max:6'],

                'reaccion_pupila_der' => ['nullable', 'string', 'max:30'],
                'reaccion_pupila_izq' => ['nullable', 'string', 'max:30'],
            ]);

            // Calcular Glasgow total si hay valores
            $o = $data['glasgow_ocular'] ?? null;
            $v = $data['glasgow_verbal'] ?? null;
            $m = $data['glasgow_motora'] ?? null;

            if ($o && $v && $m) {
                $data['glasgow_total'] = (int)$o + (int)$v + (int)$m;
            } else {
                $data['glasgow_total'] = null;
            }

            $form->fill($data);
            $form->estado = 'borrador';

            if ($form->paso_actual < 7) $form->paso_actual = 7; // el siguiente paso
            $form->save();

            $accion = $request->input('accion', 'next');
            if ($accion === 'save') return back()->with('success', 'Paso 6 guardado (borrador).');

            return redirect()->route('medico.formularios.paso', [
                'formulario' => $form->id,
                'paso' => 7,
            ])->with('success', 'Paso 6 guardado. Continúa al paso 7.');
        }
        if ($paso === 6) {
            $data = $request->validate([
                'pa_sistolica' => ['nullable', 'integer', 'min:0', 'max:300'],
                'pa_diastolica' => ['nullable', 'integer', 'min:0', 'max:200'],

                'frecuencia_cardiaca' => ['nullable', 'integer', 'min:0', 'max:250'],
                'frecuencia_respiratoria' => ['nullable', 'integer', 'min:0', 'max:80'],

                'temp_bucal' => ['nullable', 'numeric', 'min:30', 'max:45'],
                'temp_axilar' => ['nullable', 'numeric', 'min:30', 'max:45'],

                'peso' => ['nullable', 'numeric', 'min:0', 'max:500'],
                'talla' => ['nullable', 'numeric', 'min:0', 'max:3'],

                'saturacion_oxigeno' => ['nullable', 'integer', 'min:0', 'max:100'],
                'tiempo_llenado_capilar' => ['nullable', 'numeric', 'min:0', 'max:10'],

                'glasgow_ocular' => ['nullable', 'integer', 'min:1', 'max:4'],
                'glasgow_verbal' => ['nullable', 'integer', 'min:1', 'max:5'],
                'glasgow_motora' => ['nullable', 'integer', 'min:1', 'max:6'],

                'reaccion_pupila_der' => ['nullable', 'string', 'max:30'],
                'reaccion_pupila_izq' => ['nullable', 'string', 'max:30'],
            ]);

            // Calcular Glasgow total si hay valores
            $o = $data['glasgow_ocular'] ?? null;
            $v = $data['glasgow_verbal'] ?? null;
            $m = $data['glasgow_motora'] ?? null;

            if ($o && $v && $m) {
                $data['glasgow_total'] = (int)$o + (int)$v + (int)$m;
            } else {
                $data['glasgow_total'] = null;
            }

            $form->fill($data);
            $form->estado = 'borrador';

            if ($form->paso_actual < 7) $form->paso_actual = 7; // el siguiente paso
            $form->save();

            $accion = $request->input('accion', 'next');
            if ($accion === 'save') return back()->with('success', 'Paso 6 guardado (borrador).');

            return redirect()->route('medico.formularios.paso', [
                'formulario' => $form->id,
                'paso' => 7,
            ])->with('success', 'Paso 6 guardado. Continúa al paso 7.');
        }


        // -------------------------
        // PASO 7: EXAMEN FÍSICO (Sección 8 PDF)
        // -------------------------
        if ($paso === 7) {

            $items = [
                // Regional (R)
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

                // Sistémico (S)
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

            $data = $request->validate([
                'examen_fisico_checks' => ['nullable', 'array'],
                'examen_fisico_checks.*' => ['nullable', 'in:SP,CP'],
                'examen_fisico_descripcion' => ['nullable', 'string', 'max:5000'],
            ]);

            $accion = $request->input('accion', 'next');

            // Normaliza y filtra solo claves permitidas
            $allowedKeys = array_keys($items);
            $checks = $data['examen_fisico_checks'] ?? [];
            if (!is_array($checks)) $checks = [];

            $checks = array_intersect_key($checks, array_flip($allowedKeys));
            $checks = array_filter($checks, fn($v) => in_array($v, ['SP', 'CP'], true));

            // Si "Guardar y continuar" => exigir que TODOS estén seleccionados
            if ($accion === 'next' && count($checks) !== count($allowedKeys)) {
                return back()
                    ->withErrors(['examen_fisico_checks' => 'Debes marcar SP o CP en todos los ítems del examen físico.'])
                    ->withInput();
            }

            // Si hay CP y se quiere avanzar => exigir descripción
            $hayCP = in_array('CP', $checks, true);
            $desc = trim((string)($data['examen_fisico_descripcion'] ?? ''));

            if ($accion === 'next' && $hayCP && mb_strlen($desc) < 10) {
                return back()
                    ->withErrors(['examen_fisico_descripcion' => 'Marcaste CP en uno o más ítems. Describe los hallazgos (mínimo 10 caracteres).'])
                    ->withInput();
            }

            $form->examen_fisico_checks = $checks;
            $form->examen_fisico_descripcion = $desc !== '' ? $desc : null;

            $form->estado = 'borrador';

            // Avanza paso_actual si corresponde (paso 7 => habilita paso 8)
            if ($form->paso_actual < 8) {
                $form->paso_actual = 8;
            }

            $form->save();

            if ($accion === 'save') {
                return back()->with('success', 'Paso 7 guardado (borrador).');
            }

            return redirect()->route('medico.formularios.paso', [
                'formulario' => $form->id,
                'paso' => 8,
            ])->with('success', 'Paso 7 guardado. Continúa al paso 8.');
        }



        // -------------------------
        // PASO 8: LOCALIZACIÓN DE LESIONES (PDF #9)
        // -------------------------
        if ($paso === 8) {

            $data = $request->validate([
                'no_aplica_lesiones' => ['nullable', 'boolean'],
                'lesiones' => ['nullable', 'string'], // JSON string
            ]);

            $noAplica = (bool) $request->boolean('no_aplica_lesiones');

            $lesiones = [];
            if (!$noAplica) {
                $raw = (string) ($request->input('lesiones', ''));

                if ($raw !== '') {
                    $decoded = json_decode($raw, true);

                    if (!is_array($decoded)) {
                        return back()->withErrors(['lesiones' => 'Formato inválido de lesiones.'])->withInput();
                    }

                    // Validación manual simple (pro)
                    foreach ($decoded as $i => $p) {
                        $view = $p['view'] ?? null;
                        $x    = $p['x'] ?? null;
                        $y    = $p['y'] ?? null;
                        $tipo = $p['tipo'] ?? null;

                        if (!in_array($view, ['front', 'back'], true)) {
                            return back()->withErrors(['lesiones' => "Lesión #" . ($i + 1) . ": vista inválida."])->withInput();
                        }
                        if (!is_numeric($x) || $x < 0 || $x > 1 || !is_numeric($y) || $y < 0 || $y > 1) {
                            return back()->withErrors(['lesiones' => "Lesión #" . ($i + 1) . ": coordenadas inválidas."])->withInput();
                        }
                        if (!is_numeric($tipo) || (int)$tipo < 1 || (int)$tipo > 14) {
                            return back()->withErrors(['lesiones' => "Lesión #" . ($i + 1) . ": tipo inválido (1-14)."])->withInput();
                        }

                        $lesiones[] = [
                            'view' => $view,
                            'x'    => (float) $x,
                            'y'    => (float) $y,
                            'tipo' => (int) $tipo,
                        ];
                    }
                }
            }

            $form->no_aplica_lesiones = $noAplica;
            $form->lesiones = $noAplica ? [] : $lesiones;

            $form->estado = 'borrador';
            if ($form->paso_actual < 9) $form->paso_actual = 9;

            $form->save();

            $accion = $request->input('accion', 'next');
            if ($accion === 'save') {
                return back()->with('success', 'Paso guardado (borrador).');
            }

            return redirect()->route('medico.formularios.paso', [
                'formulario' => $form->id,
                'paso' => 9,
            ])->with('success', 'Guardado. Continúa al siguiente paso.');
        }


        if ($paso === 9) {

            $data = $request->validate([
                'no_aplica_obstetrica' => ['nullable', 'boolean'],

                'obst_gestas' => ['nullable', 'integer', 'min:0', 'max:20'],
                'obst_partos' => ['nullable', 'integer', 'min:0', 'max:20'],
                'obst_abortos' => ['nullable', 'integer', 'min:0', 'max:20'],
                'obst_cesareas' => ['nullable', 'integer', 'min:0', 'max:20'],

                'obst_fum' => ['nullable', 'date'],
                'obst_semanas_gestacion' => ['nullable', 'integer', 'min:0', 'max:45'],

                'obst_movimiento_fetal' => ['nullable', 'in:presente,ausente,no_eval'],

                'obst_frecuencia_fetal' => ['nullable', 'integer', 'min:0', 'max:300'],
                'obst_altura_uterina' => ['nullable', 'numeric', 'min:0', 'max:99.99'],

                'obst_membranas_rotas' => ['nullable', 'boolean'],
                'obst_tiempo_membranas_rotas' => ['nullable', 'string', 'max:255'],

                'obst_presentacion' => ['nullable', 'string', 'max:255'],

                'obst_dilatacion_cm' => ['nullable', 'numeric', 'min:0', 'max:10'],
                'obst_borramiento_pct' => ['nullable', 'integer', 'min:0', 'max:100'],

                'obst_plano' => ['nullable', 'string', 'max:255'],
                'obst_pelvis_util' => ['nullable', 'in:si,no,no_eval'],

                'obst_sangrado_vaginal' => ['nullable', 'boolean'],
                'obst_contracciones' => ['nullable', 'boolean'],

                'obst_texto' => ['nullable', 'string'],
            ]);

            // Booleans normalizados
            $data['no_aplica_obstetrica'] = (bool) $request->boolean('no_aplica_obstetrica');
            $data['obst_membranas_rotas'] = (bool) $request->boolean('obst_membranas_rotas');
            $data['obst_sangrado_vaginal'] = (bool) $request->boolean('obst_sangrado_vaginal');
            $data['obst_contracciones'] = (bool) $request->boolean('obst_contracciones');

            // Si “no aplica”, vaciamos todo
            if ($data['no_aplica_obstetrica']) {
                foreach (
                    [
                        'obst_gestas',
                        'obst_partos',
                        'obst_abortos',
                        'obst_cesareas',
                        'obst_fum',
                        'obst_semanas_gestacion',
                        'obst_movimiento_fetal',
                        'obst_frecuencia_fetal',
                        'obst_altura_uterina',
                        'obst_tiempo_membranas_rotas',
                        'obst_presentacion',
                        'obst_dilatacion_cm',
                        'obst_borramiento_pct',
                        'obst_plano',
                        'obst_pelvis_util',
                        'obst_texto',
                    ] as $k
                ) {
                    $data[$k] = null;
                }
                $data['obst_membranas_rotas'] = false;
                $data['obst_sangrado_vaginal'] = false;
                $data['obst_contracciones'] = false;
            } else {
                // Si membranas NO rotas, limpiamos el tiempo
                if (!$data['obst_membranas_rotas']) {
                    $data['obst_tiempo_membranas_rotas'] = null;
                }
            }

            $form->fill($data);
            $form->estado = 'borrador';

            // Avanza paso_actual
            if ($form->paso_actual < 10) {
                $form->paso_actual = 10;
            }

            $form->save();

            $accion = $request->input('accion', 'next');

            if ($accion === 'save') {
                return back()->with('success', 'Paso 9 guardado (borrador).');
            }

            return redirect()->route('medico.formularios.paso', [
                'formulario' => $form->id,
                'paso' => 10,
            ])->with('success', 'Paso 9 guardado. Continúa al paso 10.');
        }


        if ($paso === 10) {

            $allowed = [
                '1_biometria',
                '2_uroanalisis',
                '3_quimica_sanguinea',
                '4_electrolitos',
                '5_gasometria',
                '6_electrocardiograma',
                '7_endoscopia',
                '8_rx_torax',
                '9_rx_abdomen',
                '10_rx_osea',
                '11_tomografia',
                '12_resonancia',
                '13_ecografia_pelvica',
                '14_ecografia_abdomen',
                '15_interconsulta',
                '16_otros',
            ];

            $data = $request->validate([
                'no_aplica_examenes' => ['nullable', 'boolean'],
                'examenes_solicitados' => ['nullable', 'array'],
                'examenes_solicitados.*' => ['in:' . implode(',', $allowed)],
                'examenes_comentarios' => ['nullable', 'string'],
            ]);

            $data['no_aplica_examenes'] = (bool) $request->boolean('no_aplica_examenes');

            if ($data['no_aplica_examenes']) {
                $data['examenes_solicitados'] = null;
                $data['examenes_comentarios'] = null;
            }

            $form->fill($data);
            $form->estado = 'borrador';

            // Avanza paso_actual al siguiente (si tu siguiente paso es 11)
            if ($form->paso_actual < 11) {
                $form->paso_actual = 11;
            }

            $form->save();

            $accion = $request->input('accion', 'next');

            if ($accion === 'save') {
                return back()->with('success', 'Solicitud de exámenes guardada (borrador).');
            }

            return redirect()->route('medico.formularios.paso', [
                'formulario' => $form->id,
                'paso' => 11,
            ])->with('success', 'Solicitud de exámenes guardada. Continúa al paso 11.');
        }

        if ($paso === 11) {

            $data = $request->validate([
                'diagnosticos_ingreso' => ['nullable', 'array'],
                'diagnosticos_ingreso.*.dx' => ['nullable', 'string', 'max:255'],
                'diagnosticos_ingreso.*.cie' => ['nullable', 'string', 'max:20'],
                'diagnosticos_ingreso.*.tipo' => ['nullable', 'in:pre,def'],

                'diagnosticos_alta' => ['nullable', 'array'],
                'diagnosticos_alta.*.dx' => ['nullable', 'string', 'max:255'],
                'diagnosticos_alta.*.cie' => ['nullable', 'string', 'max:20'],
                'diagnosticos_alta.*.tipo' => ['nullable', 'in:pre,def'],
            ]);

            $normalize3 = function (array $rows = []) {
                $normalized = [];
                for ($i = 1; $i <= 3; $i++) {
                    $dx = trim((string)($rows[$i]['dx'] ?? ''));
                    $cie = trim((string)($rows[$i]['cie'] ?? ''));
                    $tipo = $rows[$i]['tipo'] ?? null;

                    $normalized[$i] = [
                        'n' => $i,
                        'dx' => $dx !== '' ? $dx : null,
                        'cie' => $cie !== '' ? $cie : null,
                        'tipo' => in_array($tipo, ['pre', 'def'], true) ? $tipo : null,
                    ];
                }
                return $normalized;
            };

            $form->fill([
                'diagnosticos_ingreso' => $normalize3($data['diagnosticos_ingreso'] ?? []),
                'diagnosticos_alta' => $normalize3($data['diagnosticos_alta'] ?? []),
            ]);

            $form->estado = 'borrador';

            // si este paso ahora cubre 12 y 13 del PDF, igual sigue siendo el paso 11 del wizard
            if ($form->paso_actual < 12) {
                $form->paso_actual = 12;
            }

            $form->save();

            $accion = $request->input('accion', 'next');

            if ($accion === 'save') {
                return back()->with('success', 'Diagnósticos guardados (borrador).');
            }

            return redirect()->route('medico.formularios.paso', [
                'formulario' => $form->id,
                'paso' => 12,
            ])->with('success', 'Diagnósticos guardados. Continúa al paso 12.');
        }


        if ($paso === 12) {

            $data = $request->validate([
                'plan_tratamiento' => ['nullable', 'array'],
                'plan_tratamiento.*.indicaciones' => ['nullable', 'string', 'max:2000'],
                'plan_tratamiento.*.medicamento' => ['nullable', 'string', 'max:255'],
                'plan_tratamiento.*.posologia' => ['nullable', 'string', 'max:255'],
            ]);

            $rows = $data['plan_tratamiento'] ?? [];

            $normalized = [];
            for ($i = 1; $i <= 4; $i++) {
                $indic = trim((string)($rows[$i]['indicaciones'] ?? ''));
                $med = trim((string)($rows[$i]['medicamento'] ?? ''));
                $pos = trim((string)($rows[$i]['posologia'] ?? ''));

                $normalized[$i] = [
                    'n' => $i,
                    'indicaciones' => $indic !== '' ? $indic : null,
                    'medicamento' => $med !== '' ? $med : null,
                    'posologia' => $pos !== '' ? $pos : null,
                ];
            }

            $form->fill([
                'plan_tratamiento' => $normalized,
            ]);

            $form->estado = 'borrador';

            if ($form->paso_actual < 13) {
                $form->paso_actual = 13;
            }

            $form->save();

            $accion = $request->input('accion', 'next');

            if ($accion === 'save') {
                return back()->with('success', 'Plan de tratamiento guardado (borrador).');
            }

            return redirect()->route('medico.formularios.paso', [
                'formulario' => $form->id,
                'paso' => 13,
            ])->with('success', 'Plan de tratamiento guardado. Continúa al paso 13.');
        }

        if ($paso === 13) { // <-- ajusta si tu último paso es otro

            $data = $request->validate([
                'alta_destino' => ['required', 'in:domicilio,consulta_externa,observacion,internacion,referencia'],

                'alta_servicio_referencia' => ['nullable', 'string', 'max:255'],
                'alta_establecimiento_referencia' => ['nullable', 'string', 'max:255'],

                'alta_resultado' => ['required', 'in:vivo,muerto_emergencia'],
                'alta_condicion' => ['nullable', 'in:estable,inestable'],
                'alta_causa' => ['nullable', 'string', 'max:255'],

                'alta_dias_incapacidad' => ['nullable', 'integer', 'min:0', 'max:365'],

                'alta_fecha_control' => ['nullable', 'date'],
                'alta_hora_finalizacion' => ['nullable'], // la volvemos requerida al finalizar
                'alta_profesional_codigo' => ['nullable', 'string', 'max:255'],
                'alta_numero_hoja' => ['nullable', 'integer', 'min:1', 'max:999999'],
            ]);

            // Reglas condicionales
            if (($data['alta_destino'] ?? null) === 'referencia') {
                if (blank($data['alta_servicio_referencia'] ?? null)) {
                    return back()->withErrors(['alta_servicio_referencia' => 'Requerido si es Referencia.'])->withInput();
                }
                if (blank($data['alta_establecimiento_referencia'] ?? null)) {
                    return back()->withErrors(['alta_establecimiento_referencia' => 'Requerido si es Referencia.'])->withInput();
                }
            } else {
                $data['alta_servicio_referencia'] = null;
                $data['alta_establecimiento_referencia'] = null;
            }

            if (($data['alta_resultado'] ?? null) === 'vivo') {
                if (blank($data['alta_condicion'] ?? null)) {
                    return back()->withErrors(['alta_condicion' => 'Selecciona condición (estable/inestable).'])->withInput();
                }
                $data['alta_causa'] = null; // causa se usa para muerto (recomendado)
            } else { // muerto_emergencia
                $data['alta_condicion'] = null;
                $data['alta_dias_incapacidad'] = null;
                $data['alta_fecha_control'] = null;

                if (blank($data['alta_causa'] ?? null)) {
                    return back()->withErrors(['alta_causa' => 'Indica causa (requerida si es Muerto en emergencia).'])->withInput();
                }
            }

            $accion = $request->input('accion', 'finish'); // save | finish

            // Si FINALIZA, aquí sí pones obligatorios:
            if ($accion === 'finish') {
                if (blank($data['alta_hora_finalizacion'] ?? null)) {
                    return back()->withErrors(['alta_hora_finalizacion' => 'Requerido para finalizar el formulario.'])->withInput();
                }
                if (blank($data['alta_profesional_codigo'] ?? null)) {
                    return back()->withErrors(['alta_profesional_codigo' => 'Requerido para finalizar el formulario.'])->withInput();
                }
            }

            $form->fill($data);

            if ($accion === 'finish') {
                $form->estado = 'completo';
                $form->paso_actual = max($form->paso_actual, 13); // último paso
            } else {
                $form->estado = 'borrador';
                $form->paso_actual = max($form->paso_actual, 13);
            }

            $form->save();

            if ($accion === 'save') {
                return back()->with('success', 'Alta guardada (borrador).');
            }

            return redirect()->route('medico.formularios')
                ->with('success', 'Formulario 008 finalizado y marcado como COMPLETO.');
        }


        // -------------------------
        // OTROS PASOS 
        // -------------------------
        return back()->with('warning', 'Este paso aún está en construcción.');
    }

    // =========================
    // REPORTES
    // =========================
    public function reportes()
    {
        $formularios = Formulario008::with('paciente')->get();
        return view('medico.reportes.index', compact('formularios'));
    }
    public function archivar(Formulario008 $formulario)
    {
        if ($formulario->estado === 'completo') {
            return back()->with('error', 'No se puede archivar un formulario completo.');
        }

        $formulario->estado = 'archivado';
        $formulario->archivado_en = now();
        $formulario->save();

        return back()->with('success', 'Formulario archivado.');
    }

    public function desarchivar(Formulario008 $formulario)
    {
        if ($formulario->estado !== 'archivado') {
            return back()->with('error', 'Este formulario no está archivado.');
        }

        $formulario->estado = 'borrador';
        $formulario->archivado_en = null;
        $formulario->save();

        return back()->with('success', 'Formulario restaurado a borrador.');
    }



    public function pdf(Formulario008 $formulario, Request $request, Formulario008PdfService $pdfService)
    {
        abort_unless($formulario->esCompleto(), 403, 'Solo se puede generar PDF cuando el formulario está completo.');

        $grid = $request->boolean('grid'); // modo ayuda para ubicar coordenadas
        $bytes = $pdfService->render($formulario, $grid);

        $filename = $formulario->pdfFilename();


        return response($bytes, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"{$filename}\"; filename*=UTF-8''" . rawurlencode($filename),
            'Cache-Control' => 'private, max-age=0, must-revalidate',
            'Pragma' => 'public',
        ]);
    }

    public function verPaso(Request $request, Formulario008 $formulario, $paso)
    {
        $paso = (int) $paso;

        $steps = config('form008.wizard', []);
        if ($paso < 1 || empty($steps) || !isset($steps[$paso])) {
            abort(404);
        }

        $form = $formulario->load(['paciente', 'creador']);

        if ((int) $form->created_by !== (int) Auth::id()) {
            abort(403);
        }

        // Solo permitimos completos
        if (!$form->esCompleto()) {
            return redirect()->route('medico.formularios.paso', [
                'formulario' => $form->id,
                'paso' => $form->paso_actual,
            ])->with('success', 'Este formulario aún está incompleto. Continúa editando.');
        }

        return view('medico.formularios.wizard_readonly', [
            'formulario' => $form,
            'paso' => $paso,
            'steps' => $steps,
        ]);
    }
}
